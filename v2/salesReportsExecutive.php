<?php
/**
 * Sales Executive Report (live ERP data)
 * -------------------------------------------------------------------------
 * One page that brings together the figures from Sales Data Analysis, Sales Data
 * Analysis (New), the Director Reports hub and the Sales Main Dashboard: target,
 * invoices, shop sales (CRV/CSV), PO received, OC, DC raised, pending DC,
 * outstanding, cart, sales-case outcomes, team results, items and exceptions.
 *
 * Every figure comes straight from ERP documents for the chosen dates; the rules
 * live in includes/salesExecutiveData.php and match the ERP's own reports.
 * Uses the v3 shell and the sp-* components from the Sales Performance report.
 *
 * Data is fetched from ?data=1 as JSON and cached for 15 minutes per date range
 * and salesperson. "Recalculate" bypasses the cache.
 */

$active = 'sales_executive';
$title = 'Sales Executive Report';
include_once('config.php'); // -> ../includes/session.inc : $db, $_SESSION, $NewRootPath, ecif()
include_once(__DIR__ . '/includes/salesExecutiveData.php');

// Directors only for now. userHasPermission() also passes holders of '*' (admins).
// The sidebar link and the Reports hub link use the same check.
if (!userHasPermission($db, 'directorreports')) {
	if (isset($_GET['data'])) {
		header('Content-Type: application/json; charset=utf-8');
		http_response_code(403);
		echo json_encode(['error' => 'forbidden']);
	} else {
		header('Location: ' . $NewRootPath . 'v2/reportLinks.php');
	}
	exit;
}

const SX_CACHE_MINUTES = 15;

$P = sxPeriod($_GET['range'] ?? 'ytd', $_GET['from'] ?? '', $_GET['to'] ?? '');
$S = sxSalesperson($db, $_GET['sp'] ?? 'all');

/* ======================================================= JSON data endpoint */
if (isset($_GET['data'])) {
	header('Content-Type: application/json; charset=utf-8');
	// v3: v1 stored raw JSON, which the 64 KB TEXT column cut off; v2 still counted grouped DCs as pending.
	$cacheKey = 'salesExec-v3-' . md5(json_encode([$P['start'], $P['end'], $S ? $S['code'] : 'all', date('Y-m-d')]));
	if (!isset($_GET['nocache'])) {
		$hit = @mysqli_query($db, "SELECT value FROM `" . sxCacheTable($db) . "` WHERE unique_key = '" . sxEsc($db, $cacheKey) . "'
			AND refreshed_at > DATE_SUB(NOW(), INTERVAL " . SX_CACHE_MINUTES . " MINUTE) LIMIT 1");
		$cached = ($hit && ($row = mysqli_fetch_assoc($hit))) ? sxCacheUnpack($row['value']) : null;
		if ($cached !== null) { echo $cached; exit; }
	}
	$payload = sxCompute($db, $P, $S);
	$payload['generatedAt'] = date('Y-m-d H:i:s');
	$json = json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);
	if ($json === false) {
		http_response_code(500);
		echo json_encode(['error' => 'The report data could not be encoded.']);
		exit;
	}
	// Only a complete result is cached (a failed query is retried next time), and only when the
	// packed value fits the cache column; otherwise the report is simply recalculated each load.
	$packed = empty($payload['errors']) ? sxCachePack($json) : null;
	if ($packed !== null && strlen($packed) <= sxCacheCapacity($db)) {
		@mysqli_query($db, "INSERT INTO `" . sxCacheTable($db) . "` (unique_key, value, refreshed_at)
			VALUES ('" . sxEsc($db, $cacheKey) . "', '" . sxEsc($db, $packed) . "', NOW())
			ON DUPLICATE KEY UPDATE value = VALUES(value), refreshed_at = VALUES(refreshed_at)");
	}
	echo $json;
	exit;
}

/* ====================================================== page */
$salesmen = [];
$res = mysqli_query($db, "SELECT salesmancode, salesmanname FROM salesman WHERE `current` = 1 ORDER BY salesmanname");
if ($res) while ($row = mysqli_fetch_assoc($res)) $salesmen[] = $row;
$h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };

include_once('../v3/includes/header.php');
include_once('../v3/includes/sidebar.php');
?>
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v3/assets/salesPerformanceReport.css">
<style>
	.sx-section-title { font-size: 15px; font-weight: 600; color: #3d4b57; margin: 6px 0 9px; }
	.sx-section-title small { font-weight: 400; color: #7a8792; margin-left: 6px; }
	.sx-kpi-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
	.sx-kpi-grid.sx-six { grid-template-columns: repeat(6, minmax(0, 1fr)); }
	.sx-kpi-grid .sp-kpi { min-height: 104px; }
	.sx-kpi-grid .sp-kpi.sx-warn { border-top-color: #dd4b39; }
	.sx-calc { display: none; margin-top: 8px; padding-top: 7px; border-top: 1px dashed #dfe5ea; font-size: 11.5px; line-height: 1.45; color: #5f6d79; }
	.sx-show-calc .sx-calc { display: block; }
	.sx-card-calc { display: none; font-size: 12px; color: #5f6d79; background: #f6f8fa; border-radius: 6px; padding: 8px 10px; margin: -4px 0 10px; }
	.sx-show-calc .sx-card-calc { display: block; }
	.sx-grid-2 { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; }
	.sx-grid-2 > .sp-card { margin-bottom: 0; }
	.sx-row { margin-bottom: 16px; }
	.sx-chart { height: 300px; }
	.sx-chart-sm { height: 260px; }
	.sx-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; border: 1px solid currentColor; white-space: nowrap; }
	.sx-st-po { color: #00875a; } .sx-st-pipe { color: #2f6fa3; } .sx-st-lost { color: #c23321; }
	.sx-st-cancel { color: #b26a00; } .sx-st-scope { color: #6b7783; } .sx-st-other { color: #6b7783; }
	.sx-meta { font-size: 12px; color: #6b7883; margin: -4px 0 12px; }
	.btn.btn-default.sx-toggle.active, .btn.btn-default.sx-toggle.active:hover, .btn.btn-default.sx-toggle.active:focus { background: #3c8dbc; color: #fff; border-color: #367fa9; box-shadow: none; }
	.sx-empty { color: #8a96a0; padding: 14px 0; text-align: center; font-size: 12.5px; }
	.sx-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
	.sx-total td { font-weight: 700; background: #f7f9fb; }
	.sx-err { background: #fdf3f2; border: 1px solid #f1c7c2; color: #8f2a1d; border-radius: 7px; padding: 10px 13px; margin-bottom: 14px; font-size: 13px; }
	.sx-note { font-size: 12px; color: #6b7883; margin: 4px 0 0; }
	.sx-filters .sp-filter-grid { grid-template-columns: minmax(170px, .8fr) minmax(300px, 1.2fr) minmax(200px, .9fr); align-items: end; }
	/* sp-filter-select is sized for the multi-selects on the Sales Performance report; these are single selects. */
	.sx-filters select.form-control { height: 34px; min-height: 0; padding: 6px 10px; }
	@media (max-width: 1200px) { .sx-kpi-grid.sx-six { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
	@media (max-width: 991px) { .sx-grid-2 { grid-template-columns: 1fr; } .sx-grid-2 > .sp-card { margin-bottom: 16px; } .sx-filters .sp-filter-grid { grid-template-columns: 1fr; } }
	@media (max-width: 640px) { .sx-kpi-grid, .sx-kpi-grid.sx-six { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>

<div class="content-wrapper sp-page" id="sx-app">
	<section class="content-header sp-header">
		<h1>Sales Executive Report <small>Live ERP figures for the dates you choose</small></h1>
		<p>Invoices, shop sales, POs, OCs, DCs, receivables and sales-case results in one place. Each figure follows the ERP report named in <em>How calculated</em>, so the numbers agree with those reports.</p>
	</section>

	<section class="content">
		<div class="sp-filter-card sx-filters">
			<form id="sx-filters" autocomplete="off">
				<div class="sp-filter-grid">
					<div class="sp-filter-select">
						<label for="sx-range">Period</label>
						<select id="sx-range" class="form-control">
							<?php foreach (['ytd' => 'This year', 'mtd' => 'This month', 'qtd' => 'This quarter', 'last12' => 'Last 12 months', 'lastyear' => 'Last year', 'custom' => 'Custom dates'] as $k => $v) { ?>
								<option value="<?php echo $k; ?>" <?php echo $P['preset'] === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="sp-filter-period">
						<strong>Dates</strong>
						<div class="sp-date-pair">
							<div><label for="sx-from">From</label><input type="date" id="sx-from" class="form-control" value="<?php echo $h($P['start']); ?>"></div>
							<div><label for="sx-to">To</label><input type="date" id="sx-to" class="form-control" value="<?php echo $h($P['end']); ?>"></div>
						</div>
					</div>
					<div class="sp-filter-select">
						<label for="sx-sp">Salesperson</label>
						<select id="sx-sp" class="form-control">
							<option value="all">All salespeople</option>
							<?php foreach ($salesmen as $m) { ?>
								<option value="<?php echo $h($m['salesmancode']); ?>" <?php echo ($S && $S['code'] === $m['salesmancode']) ? 'selected' : ''; ?>><?php echo $h($m['salesmanname']); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="sp-filter-actions">
					<div class="sp-filter-hint"><i class="fa fa-info-circle"></i> Figures marked <strong>today</strong> are the current position and ignore the dates.</div>
					<div>
						<button type="button" class="btn btn-default sx-toggle" id="sx-calc" aria-pressed="false"><i class="fa fa-calculator"></i> How calculated</button>
						<button type="button" class="btn btn-default" id="sx-refresh" title="Recalculate from the ERP, ignoring the 15-minute cache"><i class="fa fa-refresh"></i> Recalculate</button>
						<button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Show report</button>
					</div>
				</div>
			</form>
		</div>

		<div id="sx-errors"></div>
		<div id="sx-loading" class="sp-loading"><i class="fa fa-circle-o-notch fa-spin"></i> Reading ERP data…</div>

		<div id="sx-content" style="display:none">
			<div class="sx-meta" id="sx-meta"></div>

			<div class="sx-section-title">Sales in the period</div>
			<div class="sx-kpi-grid sx-six" id="sx-kpi-sales"></div>

			<div class="sx-section-title">Orders and delivery</div>
			<div class="sx-kpi-grid sx-six" id="sx-kpi-orders"></div>

			<div class="sx-section-title">Receivables and stock <small>today</small></div>
			<div class="sx-kpi-grid" id="sx-kpi-today"></div>

			<div class="sp-card sx-row">
				<div class="sp-card-heading"><div><h3>Month by month</h3><p>Invoiced, PO received and DC raised in each month of the period, against the monthly target.</p></div></div>
				<div class="sx-card-calc">Invoiced uses the invoice date, PO received the date the customer PO arrived, DC raised the DC date. Monthly target is the annual salesperson target divided by 12.</div>
				<div id="sx-trend" class="sx-chart"></div>
			</div>

			<div class="sp-card sx-row">
				<div class="sp-card-heading"><div><h3>Sales cases opened in the period</h3><p>What happened to each sales case started between the chosen dates (Sales Data Analysis).</p></div></div>
				<div class="sx-card-calc">Each case gets exactly one result, checked in this order: PO received, Lost, Cancelled, Out of scope, In pipeline, Quoted at zero, No quotation, Closed for another reason. Lost, Cancelled and Out of scope use the closing reasons from Sales Data Analysis. Quotation value is the latest quotation on the case, first option only.</div>
				<div class="sx-kpi-grid sx-six" id="sx-kpi-cases"></div>
				<div class="sx-grid-2">
					<div>
						<h4 class="sp-subtable-title">Result of each case</h4>
						<div class="table-responsive"><table class="table sp-table" id="sx-status"></table></div>
					</div>
					<div>
						<h4 class="sp-subtable-title">From enquiry to invoice</h4>
						<div id="sx-funnel" class="sx-chart-sm"></div>
					</div>
				</div>
			</div>

			<div class="sp-card sx-row">
				<div class="sp-card-heading"><div><h3>Salesperson results</h3><p>Sorted by target achievement. Pending DC, outstanding and cart are today’s position.</p></div></div>
				<div class="sx-card-calc">Invoiced is credited to the salesperson on the customer branch (as in SalesPerson Monthly Invoices). PO received, quotations, pending DC and outstanding are credited to the salesperson on the sales case.</div>
				<div class="table-responsive"><table class="table sp-table" id="sx-team"></table></div>
			</div>

			<div class="sx-grid-2 sx-row">
				<div class="sp-card">
					<div class="sp-card-heading"><div><h3>Pending DC by age <small>today</small></h3><p>Delivered but not yet invoiced (Pending DC List).</p></div></div>
					<div class="table-responsive"><table class="table sp-table" id="sx-ageing"></table></div>
					<h4 class="sp-subtable-title">Largest DCs pending over 30 days</h4>
					<div class="table-responsive"><table class="table sp-table" id="sx-oldpending"></table></div>
				</div>
				<div class="sp-card">
					<div class="sp-card-heading"><div><h3>Needs attention</h3><p>Cases that break the normal sequence or have stalled.</p></div></div>
					<h4 class="sp-subtable-title">PO received without a quotation</h4>
					<div class="table-responsive"><table class="table sp-table" id="sx-anomalies"></table></div>
					<h4 class="sp-subtable-title">Quotations open for more than 90 days</h4>
					<div class="table-responsive"><table class="table sp-table" id="sx-stale"></table></div>
				</div>
			</div>

			<div class="sx-grid-2 sx-row">
				<div class="sp-card">
					<div class="sp-card-heading"><div><h3>Sales by brand</h3><p>Invoice line value of items invoiced in the period.</p></div></div>
					<div id="sx-brands" class="sx-chart-sm"></div>
					<div class="table-responsive"><table class="table sp-table" id="sx-self"></table></div>
				</div>
				<div class="sp-card">
					<div class="sp-card-heading"><div><h3>Top items</h3><p>By invoice line value in the period.</p></div></div>
					<div class="table-responsive"><table class="table sp-table" id="sx-items"></table></div>
				</div>
			</div>

			<div class="sp-card sx-row">
				<div class="sp-card-heading"><div><h3>Sales case list</h3><p>Every case opened in the period, with its quotation, PO, OC, DC and invoices. Search, sort or export.</p></div></div>
				<div class="form-inline" style="margin-bottom:8px">
					<label for="sx-status-filter" style="font-size:12px;font-weight:600;color:#586774;margin-right:6px">Result</label>
					<select id="sx-status-filter" class="form-control input-sm"><option value="">All results</option></select>
				</div>
				<div class="table-responsive"><table class="table sp-table" id="sx-cases" style="width:100%"></table></div>
			</div>
		</div>
	</section>
</div>

<script>
(function () {
	'use strict';
	var ROOT = <?php echo json_encode($NewRootPath); ?>;
	var C = { blue: '#3c8dbc', green: '#00a65a', orange: '#f39c12', red: '#dd4b39', purple: '#605ca8', grey: '#b5bfc8', teal: '#39cccc', navy: '#2c3e50' };
	var charts = {}, casesTable = null, data = null;

	// How each figure is calculated; shown under the figure when "How calculated" is on.
	var CALC = {
		target: 'Annual target from Salesperson setup ÷ 12 × months in the period (current salespeople when "All").',
		invoiced: 'Posted invoice amount (debtortrans type 10, not reversed) for invoices dated in the period, not returned, not in progress. Same as SalesPerson Monthly Invoices.',
		achievement: 'Invoiced ÷ sales target for the period.',
		crv: 'Completed shop sales paid by CRV, dated in the period: posted amount (debtortrans type 750, not reversed). Same as SalesPerson Monthly CRV.',
		csv: 'Completed shop sales paid by CSV, dated in the period: posted amount (debtortrans type 750, not reversed).',
		shop: 'All completed shop sales dated in the period (Shop DC on the Sales Main Dashboard).',
		po: 'Sales cases whose customer PO arrived in the period: PO file uploaded on the case, or a PO recorded in My Sales Activity if there is no file. Value is the OC value of those cases.',
		oc: 'Order confirmations dated in the period, first option only (as in Sales Data Analysis).',
		dc: 'Delivery challans dated in the period, first option only.',
		pending: 'DCs not yet invoiced, grouped for invoice, couriered or returned (GRB), and not listed in a DC group, whatever their date. Same as the Pending DC List.',
		salesOrders: 'Sales orders (not quotations) dated in the period. Shown as "Total OC Value" on the Sales Main Dashboard.',
		outstanding: 'Unsettled invoice balances less GST and WHT withheld, for invoices on a sales case and an invoice group. Same as Outstanding Invoices.',
		overdue: 'The part of outstanding where the invoice due date has passed.',
		cart: 'Stock issued to salespeople and not yet delivered, at material cost (Cart report).',
		invoiceLine: 'Invoice line value: unit price × (1 − discount) × quantity × option quantity, for the same invoices as Invoiced. This is "Total Invoice Value" and "Total Proper Sale" on the Sales Main Dashboard, except that the dashboard also counts invoices whose posting was reversed. It differs from Invoiced by GST on tax-exclusive invoices.',
		businessVolume: 'Invoice line value + shop sales + pending DCs dated in the period ("Total Business Volume" on the Sales Main Dashboard).',
		casesOpened: 'Sales cases with a start date in the period.',
		quoted: 'Cases with a quotation above zero; value is the latest quotation, first option only (Total Proper Quotations).',
		casesPo: 'Cases opened in the period that now have a customer PO; value is their OC value.',
		success: 'OC value of cases with a PO ÷ quotation value of all quoted cases. The second figure compares case counts.',
		casesDc: 'All DCs raised on these cases, whatever the DC date.',
		casesInv: 'Posted invoice amount on these cases, whatever the invoice date.'
	};

	function el(id) { return document.getElementById(id); }
	function esc(s) { return (s == null ? '' : String(s)).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
	function rs(n) { return n == null ? '—' : 'Rs ' + Math.round(n).toLocaleString('en-US'); }
	function num(n) { return n == null ? '—' : Math.round(n).toLocaleString('en-US'); }
	function pct(n) { return n == null ? '—' : (n * 100).toFixed(1) + '%'; }
	function dmy(d) { if (!d) return ''; var p = String(d).substr(0, 10).split('-'); return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : d; }
	function v(x) { return x == null ? null : (typeof x === 'object' ? x.v : x); }
	function n(x) { return x == null ? null : x.n; }

	function kpi(label, value, sub, calcKey, warn) {
		return '<div class="sp-kpi' + (warn ? ' sx-warn' : '') + '"><div class="sp-kpi-label">' + esc(label) + '</div>' +
			'<div class="sp-kpi-count">' + value + '</div><div class="sp-kpi-value">' + sub + '</div>' +
			(calcKey ? '<div class="sx-calc">' + esc(CALC[calcKey]) + '</div>' : '') + '</div>';
	}
	function docs(x, word) { return x == null ? 'not available' : num(x.n) + ' ' + word + (x.n === 1 ? '' : 's'); }

	function statusClass(s) {
		return { 'PO received': 'sx-st-po', 'In pipeline': 'sx-st-pipe', 'Lost': 'sx-st-lost', 'Cancelled': 'sx-st-cancel', 'Out of scope': 'sx-st-scope' }[s] || 'sx-st-other';
	}
	function table(id, head, rows, foot, emptyText) {
		var t = el(id);
		if (!rows.length) { t.innerHTML = '<tbody><tr><td class="sx-empty">' + esc(emptyText || 'Nothing to show for these dates.') + '</td></tr></tbody>'; return; }
		var hh = '<thead><tr>' + head.map(function (c) { return '<th' + (c.num ? ' class="sx-num"' : '') + '>' + esc(c.t) + '</th>'; }).join('') + '</tr></thead>';
		var bb = '<tbody>' + rows.map(function (r) { return '<tr>' + r.map(function (c, i) { return '<td' + (head[i].num ? ' class="sx-num"' : '') + '>' + c + '</td>'; }).join('') + '</tr>'; }).join('') + '</tbody>';
		var ff = foot ? '<tfoot><tr class="sx-total">' + foot.map(function (c, i) { return '<td' + (head[i].num ? ' class="sx-num"' : '') + '>' + c + '</td>'; }).join('') + '</tr></tfoot>' : '';
		t.innerHTML = hh + bb + ff;
	}
	function chart(id, cfg) {
		if (charts[id]) { charts[id].destroy(); }
		if (!window.Highcharts) { el(id).innerHTML = '<div class="sx-empty">Charts are not available on this page.</div>'; return; }
		charts[id] = Highcharts.chart(id, cfg);
	}

	function render(d) {
		data = d;
		var K = d.kpi;
		el('sx-meta').innerHTML = esc(d.scope) + ' · ' + dmy(d.period.start) + ' to ' + dmy(d.period.end) +
			' · read from the ERP at ' + esc(d.generatedAt) + (d.poReceiptsInstalled ? '' : ' · recorded POs not installed, PO received uses PO files only');

		el('sx-kpi-sales').innerHTML = [
			kpi('Sales target', rs(K.target), 'Annual ' + rs(K.annualTarget), 'target'),
			kpi('Invoiced', rs(v(K.invoiced)), docs(K.invoiced, 'invoice'), 'invoiced'),
			kpi('Target achieved', pct(K.achievement), 'Invoiced ÷ target', 'achievement', K.achievement != null && K.achievement < 0.7),
			kpi('CRV', rs(v(K.crv)), docs(K.crv, 'shop sale'), 'crv'),
			kpi('CSV', rs(v(K.csv)), docs(K.csv, 'shop sale'), 'csv'),
			kpi('Shop sales total', rs(v(K.shop)), docs(K.shop, 'shop sale'), 'shop')
		].join('');

		el('sx-kpi-orders').innerHTML = [
			kpi('PO received', rs(v(K.poReceived)), docs(K.poReceived, 'case'), 'po'),
			kpi('OC value', rs(v(K.oc)), docs(K.oc, 'OC'), 'oc'),
			kpi('DC raised', rs(v(K.dcRaised)), docs(K.dcRaised, 'DC'), 'dc'),
			kpi('Pending DC (today)', rs(v(K.pending)), K.pending == null ? 'not available' : num(K.pending.n) + ' DCs · ' + rs(K.pending.inPeriodV) + ' dated in period', 'pending'),
			kpi('Invoice line value', rs(K.invoiceLine), 'Sales Main Dashboard measure', 'invoiceLine'),
			kpi('Business volume', rs(K.businessVolume), 'Sales orders ' + rs(K.salesOrders), 'businessVolume')
		].join('');

		el('sx-kpi-today').innerHTML = [
			kpi('Outstanding', rs(v(K.outstanding)), K.outstanding == null ? 'not available' : num(K.outstanding.n) + ' open invoices', 'outstanding'),
			kpi('Overdue', rs(K.outstanding ? K.outstanding.overdueV : null), K.outstanding == null ? 'not available' : num(K.outstanding.overdueN) + ' invoices past due date', 'overdue', K.outstanding && K.outstanding.overdueV > 0),
			kpi('Cart value', rs(K.cart), 'Issued to salespeople, at cost', 'cart')
		].join('');

		renderTrend(d);
		renderCases(d);
		renderTeam(d);
		renderPending(d);
		renderExceptions(d);
		renderItems(d);
		renderCaseList(d);
	}

	function renderTrend(d) {
		var labels = d.period.monthLabels;
		chart('sx-trend', {
			chart: { type: 'column' }, title: { text: null }, credits: { enabled: false },
			xAxis: { categories: labels }, yAxis: { title: { text: null }, labels: { formatter: function () { return (this.value / 1e6).toFixed(1) + 'M'; } } },
			tooltip: { shared: true, pointFormatter: function () { return '<span style="color:' + this.color + '">●</span> ' + this.series.name + ': <b>' + rs(this.y) + '</b><br/>'; } },
			plotOptions: { column: { borderWidth: 0, borderRadius: 2 } },
			series: [
				{ name: 'Invoiced', data: d.trend.invoiced, color: C.blue },
				{ name: 'PO received', data: d.trend.po, color: C.green },
				{ name: 'DC raised', data: d.trend.dc, color: C.orange },
				{ name: 'Monthly target', type: 'line', data: d.trend.target, color: C.navy, dashStyle: 'Dash', marker: { enabled: false } }
			]
		});
	}

	function renderCases(d) {
		var c = d.cases;
		if (!c) { el('sx-kpi-cases').innerHTML = '<div class="sx-empty">Sales cases could not be read.</div>'; return; }
		el('sx-kpi-cases').innerHTML = [
			kpi('Cases opened', num(c.total), 'Started in the period', 'casesOpened'),
			kpi('Quoted', rs(c.quotedV), num(c.quotedN) + ' cases with a quotation', 'quoted'),
			kpi('PO received', rs(c.poOcV), num(c.poN) + ' cases · quoted ' + rs(c.poQuoteV), 'casesPo'),
			kpi('Success ratio', pct(c.successValue), 'By count ' + pct(c.successCount), 'success'),
			kpi('DC on these cases', rs(c.dcV), 'Any DC date', 'casesDc'),
			kpi('Invoiced on these cases', rs(c.invV), 'Any invoice date', 'casesInv')
		].join('');
		var totalQ = c.status.reduce(function (s, x) { return s + x.v; }, 0);
		table('sx-status', [{ t: 'Result' }, { t: 'Cases', num: 1 }, { t: 'Quotation value', num: 1 }, { t: 'Share of value', num: 1 }],
			c.status.filter(function (x) { return x.n > 0; }).map(function (x) {
				return ['<span class="sx-pill ' + statusClass(x.name) + '">' + esc(x.name) + '</span>', num(x.n), rs(x.v), totalQ > 0 ? pct(x.v / totalQ) : '—'];
			}), ['Total', num(c.total), rs(totalQ), totalQ > 0 ? '100.0%' : '—'], 'No sales cases were opened in these dates.');

		var list = d.caseList;
		var steps = [
			['Opened', list.length],
			['Quoted', list.filter(function (r) { return r.quote > 0; }).length],
			['PO received', list.filter(function (r) { return r.po; }).length],
			['DC raised', list.filter(function (r) { return r.dc > 0; }).length],
			['Invoiced', list.filter(function (r) { return r.invoiced > 0; }).length]
		];
		chart('sx-funnel', {
			chart: { type: 'bar' }, title: { text: null }, credits: { enabled: false }, legend: { enabled: false },
			xAxis: { categories: steps.map(function (s) { return s[0]; }) }, yAxis: { title: { text: 'Cases' }, allowDecimals: false },
			tooltip: { pointFormat: '<b>{point.y}</b> cases' },
			plotOptions: { bar: { borderWidth: 0, dataLabels: { enabled: true } } },
			series: [{ name: 'Cases', data: steps.map(function (s) { return s[1]; }), colorByPoint: true, colors: [C.grey, C.blue, C.green, C.orange, C.purple] }]
		});
	}

	function renderTeam(d) {
		var t = d.team, sum = function (k) { return t.reduce(function (s, r) { return s + (r[k] || 0); }, 0); };
		var totTarget = sum('target'), totInv = sum('invoiced'), totQ = sum('quoted');
		table('sx-team', [
			{ t: 'Salesperson' }, { t: 'Target', num: 1 }, { t: 'Invoiced', num: 1 }, { t: 'Achieved', num: 1 }, { t: 'PO received', num: 1 },
			{ t: 'Quoted', num: 1 }, { t: 'Win rate', num: 1 }, { t: 'In pipeline', num: 1 }, { t: 'Pending DC', num: 1 }, { t: 'Outstanding', num: 1 }, { t: 'Cart', num: 1 }
		], t.map(function (r) {
			var a = r.achievement == null ? '—' : '<span style="color:' + (r.achievement >= 1 ? C.green : (r.achievement < 0.7 ? C.red : '#273744')) + ';font-weight:600">' + pct(r.achievement) + '</span>';
			return [esc(r.name), rs(r.target), rs(r.invoiced), a, rs(r.po), rs(r.quoted), pct(r.win), rs(r.pipeline), rs(r.pendingDc), rs(r.outstanding), rs(r.cart)];
		}), ['Total', rs(totTarget), rs(totInv), totTarget > 0 ? pct(totInv / totTarget) : '—', rs(sum('po')), rs(totQ), '', rs(sum('pipeline')), rs(sum('pendingDc')), rs(sum('outstanding')), rs(sum('cart'))]);
	}

	function renderPending(d) {
		var tot = d.ageing.reduce(function (s, x) { return s + x.v; }, 0);
		table('sx-ageing', [{ t: 'Age of DC' }, { t: 'DCs', num: 1 }, { t: 'Value', num: 1 }, { t: 'Share', num: 1 }],
			d.ageing.map(function (x) { return [esc(x.name), num(x.n), rs(x.v), tot > 0 ? pct(x.v / tot) : '—']; }),
			['Total', num(d.ageing.reduce(function (s, x) { return s + x.n; }, 0)), rs(tot), tot > 0 ? '100.0%' : '—']);
		table('sx-oldpending', [{ t: 'DC' }, { t: 'Date' }, { t: 'Days', num: 1 }, { t: 'Client' }, { t: 'Salesperson' }, { t: 'Value', num: 1 }],
			d.oldPending.map(function (r) { return [esc(r.dc), dmy(r.date), num(r.age), esc(r.client), esc(r.salesperson), rs(r.v)]; }), null, 'No DC has been pending for more than 30 days.');
	}

	function caseLink(ref) { return '<a href="' + ROOT + 'salescase/salescaseview.php?salescaseref=' + encodeURIComponent(ref) + '" target="_blank">' + esc(ref) + '</a>'; }

	function renderExceptions(d) {
		table('sx-anomalies', [{ t: 'Case' }, { t: 'Client' }, { t: 'Salesperson' }, { t: 'PO date' }, { t: 'OC value', num: 1 }],
			d.anomalies.map(function (r) { return [caseLink(r.case), esc(r.client), esc(r.salesperson), dmy(r.po), rs(r.oc)]; }), null, 'Every case with a PO has a quotation.');
		table('sx-stale', [{ t: 'Case' }, { t: 'Client' }, { t: 'Salesperson' }, { t: 'Opened' }, { t: 'Quotation', num: 1 }],
			d.stale.map(function (r) { return [caseLink(r.case), esc(r.client), esc(r.salesperson), dmy(r.opened), rs(r.quote)]; }), null, 'No quotation has been open for more than 90 days.');
	}

	function renderItems(d) {
		chart('sx-brands', {
			chart: { type: 'bar' }, title: { text: null }, credits: { enabled: false }, legend: { enabled: false },
			xAxis: { categories: d.brands.map(function (b) { return b.name; }) },
			yAxis: { title: { text: null }, labels: { formatter: function () { return (this.value / 1e6).toFixed(1) + 'M'; } } },
			tooltip: { pointFormatter: function () { return '<b>' + rs(this.y) + '</b>'; } },
			plotOptions: { bar: { borderWidth: 0, color: C.blue } },
			series: [{ name: 'Invoice line value', data: d.brands.map(function (b) { return b.v; }) }]
		});
		var tot = d.self.reduce(function (s, x) { return s + x.v; }, 0);
		table('sx-self', [{ t: 'Brand type' }, { t: 'Value', num: 1 }, { t: 'Share', num: 1 }],
			d.self.filter(function (x) { return x.v > 0; }).map(function (x) { return [esc(x.name), rs(x.v), tot > 0 ? pct(x.v / tot) : '—']; }), null, 'No items invoiced in these dates.');
		table('sx-items', [{ t: 'Item' }, { t: 'Description' }, { t: 'Brand' }, { t: 'Qty', num: 1 }, { t: 'Value', num: 1 }],
			d.topItems.map(function (r) { return [esc(r.code), esc(r.description), esc(r.brand), num(r.qty), rs(r.v)]; }), null, 'No items invoiced in these dates.');
	}

	function renderCaseList(d) {
		var sel = el('sx-status-filter'), keep = sel.value;
		var statuses = {}; d.caseList.forEach(function (r) { statuses[r.status] = 1; });
		sel.innerHTML = '<option value="">All results</option>' + Object.keys(statuses).map(function (s) { return '<option>' + esc(s) + '</option>'; }).join('');
		sel.value = statuses[keep] ? keep : '';
		var rows = d.caseList.map(function (r) {
			return [r.case, r.client || '', r.salesperson || '', r.opened || '', r.quoteNo || '', r.quote, r.status, r.po || '', r.poSource || '', r.oc, r.dc, r.invoiced];
		});
		if (casesTable) { casesTable.destroy(); el('sx-cases').innerHTML = ''; }
		if (!window.jQuery || !jQuery.fn.DataTable) { el('sx-cases').innerHTML = '<tbody><tr><td class="sx-empty">The case table could not load.</td></tr></tbody>'; return; }
		var money = function (x, type) { return type === 'display' ? rs(x) : x; };
		casesTable = jQuery('#sx-cases').DataTable({
			data: rows, pageLength: 25, order: [[5, 'desc']], deferRender: true,
			dom: 'Bfrtip', buttons: [{ extend: 'copyHtml5', text: 'Copy' }, { extend: 'csvHtml5', text: 'CSV', title: 'Sales cases ' + d.period.start + ' to ' + d.period.end }],
			columns: [
				{ title: 'Case', render: function (x, type) { return type === 'display' ? caseLink(x) : x; } },
				{ title: 'Client', render: function (x, type) { return type === 'display' ? esc(x) : x; } },
				{ title: 'Salesperson', render: function (x, type) { return type === 'display' ? esc(x) : x; } },
				{ title: 'Opened', render: function (x, type) { return type === 'display' ? dmy(x) : x; } },
				{ title: 'Quotation no.', render: function (x, type) { return type === 'display' && x ? '<a href="' + ROOT + 'PDFQuotation.php?QuotationNo=' + encodeURIComponent(x) + '" target="_blank">' + esc(x) + '</a>' : x; } },
				{ title: 'Quotation', className: 'sx-num', render: money },
				{ title: 'Result', render: function (x, type) { return type === 'display' ? '<span class="sx-pill ' + statusClass(x) + '">' + esc(x) + '</span>' : x; } },
				{ title: 'PO date', render: function (x, type) { return type === 'display' ? dmy(x) : x; } },
				{ title: 'PO from', render: function (x, type) { return type === 'display' ? esc(x) : x; } },
				{ title: 'OC', className: 'sx-num', render: money },
				{ title: 'DC', className: 'sx-num', render: money },
				{ title: 'Invoiced', className: 'sx-num', render: money }
			]
		});
		filterCases();
	}
	function filterCases() {
		if (!casesTable) return;
		var val = el('sx-status-filter').value;
		casesTable.column(6).search(val ? '^' + val.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '$' : '', true, false).draw();
	}

	function query(nocache) {
		var range = el('sx-range').value;
		var p = 'data=1&range=' + encodeURIComponent(range) + '&sp=' + encodeURIComponent(el('sx-sp').value);
		if (range === 'custom') p += '&from=' + encodeURIComponent(el('sx-from').value) + '&to=' + encodeURIComponent(el('sx-to').value);
		return p + (nocache ? '&nocache=1' : '');
	}
	function syncUrl() {
		var q = query(false).replace(/^data=1&/, '');
		if (window.history && history.replaceState) history.replaceState(null, '', 'salesReportsExecutive.php?' + q);
	}
	function syncDates() {
		var custom = el('sx-range').value === 'custom';
		el('sx-from').disabled = !custom; el('sx-to').disabled = !custom;
	}
	function load(nocache) {
		el('sx-loading').style.display = '';
		el('sx-errors').innerHTML = '';
		syncUrl();
		fetch('salesReportsExecutive.php?' + query(nocache), { credentials: 'same-origin' })
			.then(function (r) { if (!r.ok) throw new Error('The server answered ' + r.status + '.'); return r.json(); })
			.then(function (d) {
				if (d.error) throw new Error(d.error);
				// The case list arrives as rows of values with the field names once; rebuild objects.
				if (d.caseList && d.caseList.cols) {
					var cols = d.caseList.cols;
					d.caseList = d.caseList.rows.map(function (r) { var o = {}; cols.forEach(function (c, i) { o[c] = r[i]; }); return o; });
				}
				el('sx-loading').style.display = 'none';
				el('sx-content').style.display = '';
				if (d.errors && d.errors.length) {
					el('sx-errors').innerHTML = '<div class="sx-err"><strong>Some figures could not be read and show “—”.</strong> ' +
						d.errors.map(function (x) { return esc(x.figure); }).join(', ') + '. Recalculate to try again; if it persists, the ERP database needs checking.</div>';
				}
				el('sx-from').value = d.period.start; el('sx-to').value = d.period.end;
				render(d);
			})
			.catch(function (err) {
				el('sx-loading').style.display = 'none';
				el('sx-errors').innerHTML = '<div class="sx-err"><strong>The report could not load.</strong> ' + esc(err.message) + '</div>';
			});
	}

	el('sx-filters').addEventListener('submit', function (e) { e.preventDefault(); load(false); });
	el('sx-range').addEventListener('change', syncDates);
	el('sx-refresh').addEventListener('click', function () { load(true); });
	el('sx-status-filter').addEventListener('change', filterCases);
	el('sx-calc').addEventListener('click', function () {
		var on = el('sx-app').classList.toggle('sx-show-calc');
		this.classList.toggle('active', on);
		this.setAttribute('aria-pressed', String(on));
	});
	syncDates();
	load(false);
})();
</script>
<?php include_once('../v3/includes/footer.php'); ?>
