<?php

/**
 * BI-native Invoice Value Analysis workspace.
 *
 * The legacy invoice value report remains available from the library; this
 * page is an additive, read-only workspace over the published metric formula.
 */

$active = 'bi_reports';
$AllowAnyone = true;
include_once(dirname(__DIR__) . '/config.php');

if (!userHasPermission($db, 'sales_dashboard')) {
	header('Location: ' . $NewRootPath);
	exit;
}

include_once(dirname(__DIR__) . '/includes/header.php');
include_once(dirname(__DIR__) . '/includes/sidebar.php');

$today = date('Y-m-d');
$yearStart = date('Y-01-01');
?>

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/invoice.css">

<div class="content-wrapper invoice-bi-page" id="invoiceReportRoot" aria-busy="true">
    <section class="content-header invoice-header">
        <div class="invoice-eyebrow"><i class="fa fa-bar-chart"></i> Business Intelligence <span>/</span> Sales</div>
        <div class="invoice-header-row">
            <div>
                <h1>Invoice Value Analysis <small>Governed invoice option value</small></h1>
                <p>See invoice value, volume, run-rate, and supporting detail with the published ERP calculation and permission-aware salesperson scope.</p>
            </div>
            <div class="invoice-header-actions">
                <a href="<?php echo $NewRootPath; ?>v2/bi/reports.php" class="btn btn-default"><i class="fa fa-files-o"></i> Report library</a>
                <button type="button" id="invoiceRefresh" class="btn btn-primary"><i class="fa fa-refresh"></i> Refresh</button>
                <button type="button" id="invoiceExport" class="btn btn-default"><i class="fa fa-file-excel-o"></i> Export XLSX</button>
                <select id="invoiceSavedView" class="form-control input-sm" aria-label="Private saved invoice views"><option value="">Saved views</option></select>
                <button type="button" id="invoiceSaveView" class="btn btn-default"><i class="fa fa-bookmark-o"></i> Save view</button>
            </div>
        </div>
        <div class="invoice-header-meta"><span><i class="fa fa-database"></i> Live ERP data</span><span><i class="fa fa-lock"></i> Read-only and permission-aware</span><span>Formula status: <strong>Published</strong></span></div>
    </section>

    <section class="content invoice-content">
        <div id="invoiceAlert" class="alert invoice-alert" role="alert" style="display:none;"></div>

        <section class="invoice-panel invoice-filter-panel" aria-label="Invoice report controls">
            <div class="invoice-panel-heading"><div><span class="invoice-kicker">Explore the source metric</span><h2>Report controls</h2></div><button type="button" id="invoiceReset" class="btn btn-link btn-xs"><i class="fa fa-undo"></i> Reset</button></div>
            <div class="row invoice-filter-grid">
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="invoiceDatePreset">Date range</label><select id="invoiceDatePreset" class="form-control"><option value="ytd">Year to date</option><option value="today">Today</option><option value="yesterday">Yesterday</option><option value="week">This week</option><option value="last_week">Last week</option><option value="month">This month</option><option value="last_month">Last month</option><option value="quarter">This quarter</option><option value="last_quarter">Last quarter</option><option value="last_7">Last 7 days</option><option value="last_30">Last 30 days</option><option value="last_90">Last 90 days</option><option value="year">This year</option><option value="last_year">Last year</option><option value="all">All available dates</option><option value="custom">Custom range</option></select></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="invoiceStart">From</label><input id="invoiceStart" class="form-control" type="date" value="<?php echo htmlspecialchars($yearStart, ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="invoiceEnd">To</label><input id="invoiceEnd" class="form-control" type="date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="invoiceSalesperson">Salesperson code</label><input id="invoiceSalesperson" class="form-control" type="text" maxlength="40" placeholder="Admin filter only"><small class="invoice-field-help">Non-admin users stay in their ERP scope.</small></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="invoiceSearch">Search invoice or item</label><input id="invoiceSearch" class="form-control" type="search" maxlength="100" placeholder="Invoice no., item, narrative"></div>
                <div class="col-lg-2 col-md-3 col-sm-6 invoice-apply-wrap"><label>&nbsp;</label><button type="button" id="invoiceApply" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Apply controls</button></div>
            </div>
            <div id="invoiceFilterChips" class="invoice-filter-chips" aria-live="polite"></div>
            <div class="invoice-scope-note"><i class="fa fa-info-circle"></i> Value is calculated as <code>unitprice × (1 − discountpercent) × quantity × option quantity</code> from <code>invoice</code>, <code>invoicedetails</code>, and <code>invoiceoptions</code>. Returned and in-progress invoices are excluded.</div>
        </section>

        <nav class="invoice-tabs" aria-label="Invoice report sections"><button type="button" class="is-active" data-invoice-tab="overview"><i class="fa fa-dashboard"></i> Overview</button><button type="button" data-invoice-tab="trend"><i class="fa fa-line-chart"></i> Trend</button><button type="button" data-invoice-tab="details"><i class="fa fa-table"></i> Detail &amp; drill-through</button></nav>

        <section class="invoice-view is-active" data-invoice-view="overview">
            <div class="invoice-section-title"><div><span class="invoice-kicker">Decision view</span><h2>Invoice value overview</h2><p id="invoiceSubtitle">Loading the selected period…</p></div><div class="invoice-context" id="invoiceContext">Active company · live source</div></div>
            <div class="row invoice-kpi-row">
                <div class="col-lg-3 col-md-6 col-sm-6"><div class="invoice-kpi-card kpi-blue"><span>Invoice value</span><strong id="invoiceKpiValue">—</strong><small>Published metric total</small></div></div>
                <div class="col-lg-3 col-md-6 col-sm-6"><div class="invoice-kpi-card kpi-purple"><span>Invoices</span><strong id="invoiceKpiCount">—</strong><small>Distinct invoice numbers</small></div></div>
                <div class="col-lg-3 col-md-6 col-sm-6"><div class="invoice-kpi-card kpi-teal"><span>Average invoice value</span><strong id="invoiceKpiAverage">—</strong><small>Total divided by invoices</small></div></div>
                <div class="col-lg-3 col-md-6 col-sm-6"><div class="invoice-kpi-card kpi-amber"><span>Detail option rows</span><strong id="invoiceKpiRows">—</strong><small>Available for drill-through</small></div></div>
            </div>
            <div class="row"><div class="col-lg-8"><div class="invoice-panel invoice-chart-panel"><div class="invoice-chart-title"><div><strong>Monthly invoice value</strong><span>Value and invoice volume across the selected period</span></div><span id="invoiceTrendStatus">Loading…</span></div><div class="invoice-chart-wrap"><canvas id="invoiceTrendChart" aria-label="Monthly invoice value chart"></canvas><div id="invoiceTrendEmpty" class="invoice-chart-empty">No invoice data in the selected period.</div></div><div id="invoiceTrendFallback" class="invoice-trend-fallback"></div></div></div><div class="col-lg-4"><div class="invoice-panel invoice-definition-card"><span class="invoice-kicker">Governed lineage</span><h3>How to read this report</h3><dl><dt>Grain</dt><dd>One invoice option detail row</dd><dt>Date role</dt><dd><code>invoice.invoicesdate</code></dd><dt>Scope</dt><dd id="invoiceScopeText">Resolved from the active ERP session</dd><dt>Freshness</dt><dd id="invoiceFreshness">Live source query</dd></dl><p class="invoice-definition-note"><i class="fa fa-shield"></i> The workspace is additive. The original report remains linked from the report library and is not mutated.</p></div></div></div>
        </section>

        <section class="invoice-view" data-invoice-view="trend" hidden><div class="invoice-section-title"><div><span class="invoice-kicker">Time series</span><h2>When is invoice value moving?</h2><p>Use the monthly series to identify run-rate changes before drilling into invoice and item evidence.</p></div></div><div class="invoice-panel invoice-chart-panel invoice-trend-large"><div class="invoice-chart-title"><div><strong>Value run-rate</strong><span>Hover the chart for monthly value and invoice count.</span></div></div><div class="invoice-chart-wrap"><canvas id="invoiceTrendChartLarge" aria-label="Large monthly invoice value chart"></canvas><div class="invoice-chart-empty" id="invoiceTrendLargeEmpty">No invoice data in the selected period.</div></div></div></section>

        <section class="invoice-view" data-invoice-view="details" hidden>
            <div class="invoice-section-title"><div><span class="invoice-kicker">Drill-through</span><h2>Invoice option detail</h2><p>Server-side pagination keeps the browser responsive while preserving the published formula at row level.</p></div><div class="invoice-detail-status" id="invoiceDetailStatus">Loading…</div></div>
            <div class="invoice-panel invoice-table-panel">
                <div class="invoice-detail-toolbar"><label for="invoicePageSize">Rows</label><select id="invoicePageSize" class="form-control input-sm"><option value="25">25</option><option value="50" selected>50</option><option value="100">100</option><option value="250">250</option><option value="500">500</option></select><button type="button" id="invoicePrevPage" class="btn btn-default btn-xs" disabled><i class="fa fa-chevron-left"></i> Previous</button><button type="button" id="invoiceNextPage" class="btn btn-default btn-xs" disabled>Next <i class="fa fa-chevron-right"></i></button><span id="invoicePageStatus" class="text-muted">Loading…</span></div>
                <div class="table-responsive"><table class="table invoice-table" id="invoiceDetailTable"><thead><tr><th data-invoice-sort="date">Date <i class="fa fa-sort"></i></th><th data-invoice-sort="invoiceno">Invoice <i class="fa fa-sort"></i></th><th>Sales case</th><th>Salesperson</th><th data-invoice-sort="stkcode">Item <i class="fa fa-sort"></i></th><th>Narrative</th><th class="text-right">Unit price</th><th class="text-right">Discount</th><th class="text-right">Qty</th><th class="text-right">Option qty</th><th class="text-right" data-invoice-sort="line_value">Line value <i class="fa fa-sort"></i></th></tr></thead><tbody><tr><td colspan="11" class="invoice-empty-cell">Loading detail…</td></tr></tbody></table></div>
            </div>
        </section>

        <div class="invoice-footer"><span><i class="fa fa-clock-o"></i> Last refreshed: <strong id="invoiceLastRefreshed">—</strong></span><span>Applied period: <strong id="invoiceAppliedPeriod">—</strong></span><span>Report status: <strong id="invoiceReportStatus">Loading</strong></span></div>
    </section>
</div>

<script>
    window.SAHAMID_INVOICE_BI = {
        apiUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/invoice_report.php'); ?>,
        defaultStart: <?php echo json_encode($yearStart); ?>,
        defaultEnd: <?php echo json_encode($today); ?>
    };
</script>
<script src="<?php echo $NewRootPath; ?>v2/bi/invoice.js"></script>

<?php
include_once(dirname(__DIR__) . '/includes/footer.php');
include_once(dirname(__DIR__) . '/includes/foot.php');
