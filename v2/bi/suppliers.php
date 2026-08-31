<?php

/**
 * BI-native Supplier Relationship Intelligence workspace.
 *
 * The page is read-only. All supplier/AP calculations are served by the
 * permission-aware suppliers API so the overview, portfolio, and drill-down
 * views cannot drift apart.
 */

$active = 'bi_suppliers';
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

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/supplier.css?v=20260831-3">
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/supplier-intelligence.css?v=20260831-3">

<div class="content-wrapper supplier-bi-page" id="supplierReportRoot" aria-busy="true">
    <section class="content-header supplier-header">
        <div class="supplier-header-copy">
            <div class="supplier-eyebrow"><i class="fa fa-bar-chart"></i> Business Intelligence <span>/</span> Accounts Payable</div>
            <h1>Supplier Relationship Intelligence <small>One consolidated supplier ledger, payment, and ageing view</small></h1>
            <p>See what each supplier is owed, how long it has been outstanding, what has been allocated or paid, and the activity and controls behind the balance.</p>
        </div>
        <div class="supplier-header-actions">
            <a href="<?php echo $NewRootPath; ?>v2/bi/reports.php" class="btn btn-default"><i class="fa fa-files-o"></i> Report library</a>
            <span class="supplier-live-pill"><i class="fa fa-circle"></i> Live ERP data</span>
            <button type="button" id="supplierRefresh" class="btn btn-primary"><i class="fa fa-refresh"></i> Refresh</button>
            <button type="button" id="supplierExport" class="btn btn-default"><i class="fa fa-file-excel-o"></i> Export XLSX</button>
            <select id="supplierSavedView" class="form-control input-sm" aria-label="Private saved supplier views"><option value="">Saved views</option></select>
            <button type="button" id="supplierSaveView" class="btn btn-default"><i class="fa fa-bookmark-o"></i> Save view</button>
            <button type="button" id="supplierReset" class="btn btn-link"><i class="fa fa-undo"></i> Reset</button>
        </div>
    </section>

    <section class="content supplier-content">
        <div id="supplierAlert" class="alert supplier-alert" role="alert" style="display:none;"></div>

        <nav class="supplier-section-nav" id="supplierSectionNav" aria-label="Supplier report sections">
            <a class="is-active" href="#overview"><i class="fa fa-dashboard"></i><span>Overview</span></a>
            <a href="#portfolio"><i class="fa fa-users"></i><span>Supplier portfolio</span></a>
            <a href="#payments"><i class="fa fa-money"></i><span>Ageing &amp; payments</span></a>
            <a href="#transactions"><i class="fa fa-table"></i><span>Transactions</span></a>
            <a href="#controls"><i class="fa fa-shield"></i><span>Controls</span></a>
            <span class="supplier-nav-scope" id="supplierNavScope"><i class="fa fa-circle-o-notch fa-spin"></i> Loading portfolio</span>
        </nav>

        <section id="filters" class="supplier-panel supplier-filter-panel">
            <div class="supplier-panel-heading">
                <div><span class="supplier-kicker">Explore the supplier ledger</span><h2>Portfolio command center</h2><p class="supplier-filter-intro">Every selection updates the KPIs, charts, portfolio, transactions, controls, and export together.</p></div>
                <div class="supplier-period-buttons" role="group" aria-label="Date presets">
                    <button type="button" class="btn btn-default btn-xs" data-period="month">This month</button>
                    <button type="button" class="btn btn-default btn-xs" data-period="quarter">Last 90 days</button>
                    <button type="button" class="btn btn-default btn-xs" data-period="ytd">Year to date</button>
                    <select id="supplierDatePreset" class="form-control input-sm" aria-label="More date presets">
                        <option value="ytd">Year to date</option><option value="today">Today</option><option value="yesterday">Yesterday</option><option value="this_week">This week</option><option value="last_week">Last week</option><option value="this_month">This month</option><option value="last_month">Last month</option><option value="this_quarter">This quarter</option><option value="last_quarter">Last quarter</option><option value="this_year">This year</option><option value="last_year">Last year</option><option value="last_7">Last 7 days</option><option value="last_30">Last 30 days</option><option value="last_90">Last 90 days</option><option value="all">All available dates</option><option value="custom">Custom range</option>
                    </select>
                    <button type="button" id="supplierAdvancedToggle" class="btn btn-default btn-xs" aria-expanded="false"><i class="fa fa-sliders"></i> Advanced <span id="supplierAdvancedCount" class="supplier-filter-count">0</span></button>
                </div>
            </div>
            <div class="supplier-quick-views" aria-label="Quick portfolio views">
                <span><i class="fa fa-bolt"></i> Quick views</span>
                <button type="button" data-quick-view="all" class="is-active">All relationships</button>
                <button type="button" data-quick-view="outstanding">Open payables</button>
                <button type="button" data-quick-view="overdue">Overdue risk</button>
                <button type="button" data-quick-view="credits">Unapplied credits</button>
                <button type="button" data-quick-view="on_hold">On hold</button>
                <button type="button" data-quick-view="missing_due">Missing due dates</button>
            </div>
            <div class="row supplier-filter-grid">
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierStart">Activity from</label><input id="supplierStart" class="form-control" type="date" value="<?php echo htmlspecialchars($yearStart, ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierEnd">As-of / activity to</label><input id="supplierEnd" class="form-control" type="date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierStatus">Transaction status</label><select id="supplierStatus" class="form-control"><option value="all">All transactions</option><option value="outstanding">Outstanding only</option><option value="overdue">Overdue only</option><option value="settled">Settled / paid only</option></select></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierAging">Ageing position</label><select id="supplierAging" class="form-control"><option value="all">All ageing buckets</option><option value="current">Current / not due</option><option value="due">Due now</option><option value="overdue1">Overdue threshold 1</option><option value="overdue2">Overdue threshold 2+</option><option value="credits">Credits / overpayments</option><option value="settled">Settled</option></select></div>
                <div class="col-lg-3 col-md-4 col-sm-8"><label for="supplierSearch">Search across relationships and transactions</label><div class="supplier-search-field"><i class="fa fa-search"></i><input id="supplierSearch" class="form-control" type="search" maxlength="100" placeholder="Name, code, phone, reference, comment, terms…"></div></div>
                <div class="col-lg-1 col-md-2 col-sm-4 supplier-filter-submit"><label>&nbsp;</label><button type="button" id="supplierApply" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Apply</button></div>
            </div>
            <div class="supplier-picker-block">
                <div class="supplier-picker-label"><label id="supplierPickerLabel">Suppliers</label><span>Search and select up to 100 suppliers; leave empty for the complete portfolio.</span></div>
                <div class="supplier-multiselect" id="supplierMultiSelect">
                    <button type="button" id="supplierPickerToggle" class="supplier-picker-toggle" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="supplierPickerLabel supplierPickerSummary"><span id="supplierPickerSummary">All suppliers</span><i class="fa fa-chevron-down"></i></button>
                    <div class="supplier-picker-menu" id="supplierPickerMenu" hidden>
                        <div class="supplier-picker-search"><i class="fa fa-search"></i><input id="supplierPickerSearch" type="search" class="form-control" placeholder="Search supplier name or code" autocomplete="off"></div>
                        <div class="supplier-picker-actions"><button type="button" id="supplierSelectVisible">Select visible</button><button type="button" id="supplierClearSelection">Clear selection</button><button type="button" id="supplierApplySelection" class="supplier-picker-apply"><i class="fa fa-check"></i> Apply selection</button><span id="supplierPickerMatchCount">Loading suppliers…</span></div>
                        <div class="supplier-picker-list" id="supplierPickerList" role="listbox" aria-multiselectable="true"></div>
                    </div>
                </div>
                <div id="supplierSelectedList" class="supplier-selected-list" aria-live="polite"></div>
            </div>
            <div id="supplierSelectionSummary" class="supplier-selection-summary" hidden><div><i class="fa fa-crosshairs"></i><span><strong id="supplierSelectionHeadline">Focused supplier portfolio</strong><small id="supplierSelectionNames"></small></span></div><button type="button" id="supplierSelectionClear" class="btn btn-link btn-xs">Return to all suppliers</button></div>
            <div class="row supplier-filter-grid supplier-advanced-fields" hidden>
                <div class="col-lg-3 col-md-4 col-sm-6"><label>Supplier types</label><div id="supplierTypeOptions" class="supplier-check-filter"><span>Loading supplier types…</span></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><label>Payment terms</label><div id="supplierPaymentTermOptions" class="supplier-check-filter"><span>Loading payment terms…</span></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><label>Transaction types</label><div id="supplierTransactionTypeOptions" class="supplier-check-filter"><span>Loading transaction types…</span></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><label for="supplierAttention">Control / attention signal</label><select id="supplierAttention" class="form-control"><option value="all">All control states</option><option value="missing_due">Missing due date</option><option value="on_hold">Open and on hold</option><option value="zero_unsettled">Zero balance, not settled</option><option value="unapplied_credit">Unapplied credit / overpayment</option></select></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierDueFrom">Due date from</label><input id="supplierDueFrom" class="form-control" type="date"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierDueTo">Due date to</label><input id="supplierDueTo" class="form-control" type="date"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierMinOutstanding">Outstanding from</label><input id="supplierMinOutstanding" class="form-control" type="number" step="0.01" placeholder="No minimum"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierMaxOutstanding">Outstanding to</label><input id="supplierMaxOutstanding" class="form-control" type="number" step="0.01" placeholder="No maximum"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="supplierGroupBy">Portfolio emphasis</label><select id="supplierGroupBy" class="form-control"><option value="supplier">Supplier</option><option value="balance">Outstanding balance</option><option value="overdue">Overdue exposure</option><option value="paid">Paid / allocated</option><option value="transactions">Transaction activity</option><option value="lastpaid">Last payment</option></select></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label>Calculation basis</label><div class="supplier-readonly-field"><i class="fa fa-lock"></i> Live ledger through as-of date</div></div>
            </div>
            <div id="supplierFilterChips" class="supplier-filter-chips" aria-live="polite"></div>
            <div class="supplier-scope-note"><i class="fa fa-info-circle"></i> Portfolio balances are calculated through the selected as-of date. Activity dates control trends and transaction detail. Currency is intentionally not a filter: every currency remains visible and is reconciled separately below. Paid / allocated means the ERP allocation applied against supplier documents.</div>
        </section>

        <section id="overview" class="supplier-report-section">
            <div class="supplier-section-title"><div><span class="supplier-kicker">Decision view</span><h2>Supplier relationship overview</h2><p id="supplierSummarySubtitle">Loading the selected ledger…</p></div><div class="supplier-context" id="supplierContext">Active company · read-only</div></div>
            <div class="row supplier-kpi-row">
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="supplier-kpi-card kpi-blue supplier-kpi-action" data-kpi-view="all" role="button" tabindex="0" title="Show the complete selected portfolio"><span>Net AP balance</span><strong id="supplierKpiBalance">—</strong><small>All transactions through as-of</small></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="supplier-kpi-card kpi-purple supplier-kpi-action" data-kpi-view="outstanding" role="button" tabindex="0" title="Filter to outstanding supplier transactions"><span>Open payables</span><strong id="supplierKpiOpen">—</strong><small>Click to isolate open balances</small></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="supplier-kpi-card kpi-amber supplier-kpi-action" data-kpi-view="due" role="button" tabindex="0" title="Filter to items due within threshold 1"><span>Due now</span><strong id="supplierKpiDue">—</strong><small>Click to inspect due items</small></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="supplier-kpi-card kpi-red supplier-kpi-action" data-kpi-view="overdue" role="button" tabindex="0" title="Filter to overdue supplier transactions"><span>Overdue</span><strong id="supplierKpiOverdue">—</strong><small>Click to prioritize risk</small></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="supplier-kpi-card kpi-green supplier-kpi-action" data-kpi-view="settled" role="button" tabindex="0" title="Filter to settled and paid supplier transactions"><span>Paid / allocated</span><strong id="supplierKpiPaid">—</strong><small>Click to inspect settled activity</small></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="supplier-kpi-card kpi-teal supplier-kpi-action" data-navigate="portfolio" role="button" tabindex="0" title="Open the supplier portfolio"><span>Suppliers with balance</span><strong id="supplierKpiSuppliers">—</strong><small>Click to open portfolio</small></div></div>
            </div>
            <div class="row supplier-insight-row">
                <div class="col-md-8"><div class="supplier-insight-card"><div class="supplier-insight-icon"><i class="fa fa-lightbulb-o"></i></div><div><span class="supplier-kicker">Management signal</span><strong id="supplierInsightHeadline">Building the report narrative…</strong><p id="supplierInsightBody">The report will highlight overdue concentration, payment activity, and supplier follow-up signals after the selected data is loaded.</p></div></div></div>
                <div class="col-md-4"><div class="supplier-side-stat"><span class="supplier-kicker">Activity in selected period</span><strong id="supplierActivityPaid">—</strong><p id="supplierActivityMeta">Payments / credits recorded in the period.</p></div></div>
                <div class="col-md-12"><div class="supplier-exception-strip"><div><span class="supplier-kicker">Deterministic exceptions</span><strong id="supplierExceptionHeadline">Checking the AP control queue…</strong><p id="supplierExceptionBody">Exceptions are calculated from the same live ledger used by the report.</p></div><ul id="supplierExceptionList" aria-live="polite"><li>Loading exceptions…</li></ul></div></div>
            </div>
            <div class="row">
                <div class="col-lg-7"><div class="supplier-panel supplier-chart-panel"><div class="supplier-chart-title"><div><strong>Outstanding by ageing bucket</strong><span>Amounts are net of ERP allocations · click a segment to filter</span></div><span id="supplierAgeingStatus">Loading…</span></div><div class="supplier-chart-wrap"><canvas id="supplierAgeingChart" aria-label="Supplier outstanding ageing chart"></canvas><div id="supplierAgeingEmpty" class="supplier-chart-empty">No ageing data for the selected controls.</div></div><div id="supplierAgeingFallback" class="supplier-trend-fallback"></div></div></div>
                <div class="col-lg-5"><div class="supplier-panel supplier-definition-card"><span class="supplier-kicker">Governed lineage</span><h3>How to read this report</h3><dl><dt>Balance formula</dt><dd><code>ovamount + ovgst - alloc</code></dd><dt>Paid / allocated</dt><dd>Positive ERP allocation applied to supplier documents</dd><dt>Ageing basis</dt><dd>Supplier transaction due date as of <strong id="supplierAsOfDate">—</strong></dd><dt>Thresholds</dt><dd id="supplierThresholds">Configured in ERP</dd><dt>Freshness</dt><dd id="supplierFreshness">Live source query</dd></dl><p class="supplier-definition-note"><i class="fa fa-shield"></i> Read-only, permission-aware, and linked to the original AP reports for reconciliation.</p></div></div>
            </div>
        </section>

        <section id="portfolio" class="supplier-report-section">
            <div class="supplier-section-title"><div><span class="supplier-kicker">Relationship coverage</span><h2>Supplier portfolio</h2><p>One row per supplier with commercial terms, relationship contacts, payment history, and current exposure.</p></div><div class="supplier-detail-status" id="supplierPortfolioStatus">Loading…</div></div>
            <div class="supplier-panel supplier-table-panel">
                <div class="supplier-detail-toolbar"><button type="button" id="supplierDensityToggle" class="btn btn-default btn-xs"><i class="fa fa-compress"></i> Compact rows</button><div class="supplier-column-picker"><button type="button" class="btn btn-default btn-xs" data-column-toggle="portfolio"><i class="fa fa-columns"></i> Columns</button><div class="supplier-column-menu" data-column-menu="portfolio" hidden><strong>Portfolio columns</strong><label><input type="checkbox" data-table-column="contact" checked> Contact / terms</label><label><input type="checkbox" data-table-column="currency" checked> Currency</label><label><input type="checkbox" data-table-column="paid" checked> Paid / allocated</label><label><input type="checkbox" data-table-column="current" checked> Current</label><label><input type="checkbox" data-table-column="due" checked> Due now</label><label><input type="checkbox" data-table-column="overdue" checked> Overdue</label><label><input type="checkbox" data-table-column="bills" checked> Open bills</label><label><input type="checkbox" data-table-column="last_payment" checked> Last payment</label></div></div><label for="supplierPortfolioPageSize">Rows</label><select id="supplierPortfolioPageSize" class="form-control input-sm"><option value="25">25</option><option value="50" selected>50</option><option value="100">100</option><option value="250">250</option></select><button type="button" id="supplierPortfolioPrev" class="btn btn-default btn-xs" disabled><i class="fa fa-chevron-left"></i> Previous</button><button type="button" id="supplierPortfolioNext" class="btn btn-default btn-xs" disabled>Next <i class="fa fa-chevron-right"></i></button><span id="supplierPortfolioPageStatus" class="text-muted">Loading…</span></div>
                <div class="table-responsive"><table class="table supplier-table" id="supplierPortfolioTable"><thead><tr><th data-column="supplier" data-supplier-sort="supplier">Supplier <i class="fa fa-sort"></i></th><th data-column="contact">Contact / terms</th><th data-column="currency">Currency</th><th data-column="net" class="text-right" data-supplier-sort="balance">Net balance <i class="fa fa-sort"></i></th><th data-column="paid" class="text-right" data-supplier-sort="paid">Paid / allocated <i class="fa fa-sort"></i></th><th data-column="open" class="text-right">Open payables</th><th data-column="current" class="text-right">Current</th><th data-column="due" class="text-right">Due now</th><th data-column="overdue" class="text-right" data-supplier-sort="overdue">Overdue <i class="fa fa-sort"></i></th><th data-column="bills" class="text-right">Open bills</th><th data-column="last_payment" data-supplier-sort="lastpaid">Last payment <i class="fa fa-sort"></i></th></tr></thead><tbody><tr><td colspan="11" class="supplier-empty-cell">Loading supplier portfolio…</td></tr></tbody></table></div>
            </div>
        </section>

        <section id="payments" class="supplier-report-section">
            <div class="supplier-section-title"><div><span class="supplier-kicker">Cash movement &amp; exposure</span><h2>Ageing and payment activity</h2><p>Compare supplier purchases, payment / credit activity, and allocations across the selected activity period.</p></div></div>
            <div class="row">
                <div class="col-lg-8"><div class="supplier-panel supplier-chart-panel"><div class="supplier-chart-title"><div><strong>Monthly purchases vs. payments</strong><span>Transaction activity within the selected date range · click a month to drill in</span></div><span id="supplierTrendStatus">Loading…</span></div><div class="supplier-chart-wrap"><canvas id="supplierTrendChart" aria-label="Supplier purchases and payment trend"></canvas><div id="supplierTrendEmpty" class="supplier-chart-empty">No supplier activity for the selected period.</div></div><div id="supplierTrendFallback" class="supplier-trend-fallback"></div></div></div>
                <div class="col-lg-4"><div class="supplier-panel supplier-table-panel supplier-ageing-table-panel"><div class="supplier-chart-title"><div><strong>Ageing detail</strong><span>Supplier count and net exposure</span></div></div><div class="table-responsive"><table class="table supplier-table supplier-compact-table" id="supplierAgeingTable"><thead><tr><th>Bucket</th><th class="text-right">Suppliers</th><th class="text-right">Amount</th></tr></thead><tbody><tr><td colspan="3" class="supplier-empty-cell">Loading ageing…</td></tr></tbody></table></div></div></div>
            </div>
            <div class="supplier-panel supplier-currency-panel"><div class="supplier-chart-title"><div><strong>Currency reconciliation</strong><span>Currency is never hidden by a filter; each source currency remains separately visible here</span></div></div><div class="table-responsive"><table class="table supplier-table supplier-compact-table" id="supplierCurrencyTable"><thead><tr><th>Currency</th><th class="text-right">Net balance</th><th class="text-right">Open payables</th><th class="text-right">Overdue</th><th class="text-right">Paid / allocated</th></tr></thead><tbody><tr><td colspan="5" class="supplier-empty-cell">Loading currency reconciliation…</td></tr></tbody></table></div></div>
        </section>

        <section id="transactions" class="supplier-report-section">
            <div class="supplier-section-title"><div><span class="supplier-kicker">Drill-through evidence</span><h2>Supplier transaction detail</h2><p>Invoices, credits, payments, due dates, allocation, remaining balance, hold state, and comments. Pagination keeps the browser responsive; export includes all matching rows up to 50,000.</p></div><div class="supplier-detail-status" id="supplierDetailStatus">Loading…</div></div>
            <div class="supplier-panel supplier-table-panel">
                <div class="supplier-detail-toolbar"><div class="supplier-column-picker"><button type="button" class="btn btn-default btn-xs" data-column-toggle="details"><i class="fa fa-columns"></i> Columns</button><div class="supplier-column-menu" data-column-menu="details" hidden><strong>Transaction columns</strong><label><input type="checkbox" data-table-column="due_date" checked> Due date</label><label><input type="checkbox" data-table-column="days" checked> Days overdue</label><label><input type="checkbox" data-table-column="age" checked> Age bucket</label><label><input type="checkbox" data-table-column="total" checked> Document total</label><label><input type="checkbox" data-table-column="paid" checked> Paid / allocated</label><label><input type="checkbox" data-table-column="payment" checked> Payment / credit</label><label><input type="checkbox" data-table-column="status" checked> Status</label><label><input type="checkbox" data-table-column="comments" checked> Comments</label></div></div><label for="supplierDetailPageSize">Rows</label><select id="supplierDetailPageSize" class="form-control input-sm"><option value="25">25</option><option value="50" selected>50</option><option value="100">100</option><option value="250">250</option><option value="500">500</option></select><button type="button" id="supplierDetailPrev" class="btn btn-default btn-xs" disabled><i class="fa fa-chevron-left"></i> Previous</button><button type="button" id="supplierDetailNext" class="btn btn-default btn-xs" disabled>Next <i class="fa fa-chevron-right"></i></button><span id="supplierDetailPageStatus" class="text-muted">Loading…</span></div>
                <div class="table-responsive"><table class="table supplier-table supplier-detail-table" id="supplierDetailTable"><thead><tr><th data-column="date" data-detail-sort="date">Date <i class="fa fa-sort"></i></th><th data-column="supplier">Supplier</th><th data-column="type">Type / reference</th><th data-column="due_date" data-detail-sort="due_date">Due date <i class="fa fa-sort"></i></th><th data-column="days" data-detail-sort="days">Days overdue <i class="fa fa-sort"></i></th><th data-column="age">Age bucket</th><th data-column="total" class="text-right" data-detail-sort="total">Document total <i class="fa fa-sort"></i></th><th data-column="paid" class="text-right" data-detail-sort="paid">Paid / allocated <i class="fa fa-sort"></i></th><th data-column="payment" class="text-right">Payment / credit</th><th data-column="outstanding" class="text-right" data-detail-sort="outstanding">Outstanding <i class="fa fa-sort"></i></th><th data-column="status">Status</th><th data-column="comments">Comments</th></tr></thead><tbody><tr><td colspan="12" class="supplier-empty-cell">Loading transaction detail…</td></tr></tbody></table></div>
            </div>
        </section>

        <section id="controls" class="supplier-report-section">
            <div class="supplier-section-title"><div><span class="supplier-kicker">Trust &amp; follow-up</span><h2>Data quality and relationship controls</h2><p>Use these checks before relying on a supplier balance for payment decisions or management reporting.</p></div><div class="supplier-quality-badge" id="supplierQualityBadge">Loading controls…</div></div>
            <div class="row supplier-quality-grid">
                <div class="col-md-4"><div class="supplier-quality-card supplier-control-action" data-quick-view="missing_due" role="button" tabindex="0"><i class="fa fa-calendar-o"></i><div><strong id="supplierMissingDue">—</strong><span>Open bills without a usable due date · click to inspect</span></div></div></div>
                <div class="col-md-4"><div class="supplier-quality-card supplier-control-action" data-quick-view="on_hold" role="button" tabindex="0"><i class="fa fa-hand-paper-o"></i><div><strong id="supplierOnHold">—</strong><span>Open bills currently on hold · click to inspect</span></div></div></div>
                <div class="col-md-4"><div class="supplier-quality-card supplier-control-action" data-quick-view="zero_unsettled" role="button" tabindex="0"><i class="fa fa-check-square-o"></i><div><strong id="supplierZeroUnsettled">—</strong><span>Zero-balance transactions not marked settled · click to inspect</span></div></div></div>
            </div>
            <div class="row">
                <div class="col-md-7"><div class="supplier-panel supplier-control-panel"><div class="supplier-chart-title"><div><strong>Relationship follow-up guide</strong><span>Recommended operating path</span></div></div><ol class="supplier-reading-path"><li><span>01</span><div><strong>Prioritize exposure</strong><p>Start with overdue 2+ balances and supplier concentration in the portfolio.</p></div></li><li><span>02</span><div><strong>Validate payment history</strong><p>Compare Paid / allocated with payment activity and open the transaction evidence.</p></div></li><li><span>03</span><div><strong>Resolve controls</strong><p>Review missing due dates, holds, unallocated credits, and missing relationship contacts.</p></div></li></ol></div></div>
                <div class="col-md-5"><div class="supplier-panel supplier-control-panel"><div class="supplier-chart-title"><div><strong>Source and formula</strong><span>Reconciliation contract</span></div></div><dl class="supplier-source-list"><dt>Source tables</dt><dd><code>suppliers</code>, <code>supptrans</code>, <code>suppallocs</code>, <code>paymentterms</code>, <code>systypes</code></dd><dt>Original reports</dt><dd><a href="<?php echo $NewRootPath; ?>AgedSuppliers.php" target="_blank" rel="noopener">Aged Supplier Report</a> · <a href="<?php echo $NewRootPath; ?>SupplierTransInquiry.php" target="_blank" rel="noopener">Supplier Transaction Inquiry</a></dd><dt>Privacy</dt><dd>Read-only and scoped by the active ERP BI permission.</dd></dl></div></div>
            </div>
        </section>

        <button type="button" id="supplierBackToTop" class="supplier-back-to-top" title="Back to report navigation" aria-label="Back to report navigation"><i class="fa fa-arrow-up"></i></button>
        <div class="supplier-footer"><span><i class="fa fa-clock-o"></i> Last refreshed: <strong id="supplierLastRefreshed">—</strong></span><span>Activity period: <strong id="supplierAppliedPeriod">—</strong></span><span>As-of: <strong id="supplierFooterAsOf">—</strong></span><span>Report status: <strong id="supplierReportStatus">Loading</strong></span></div>
    </section>
</div>

<script>
    window.SAHAMID_SUPPLIER_BI = {
        apiUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/suppliers.php'); ?>,
        defaultStart: <?php echo json_encode($yearStart); ?>,
        defaultEnd: <?php echo json_encode($today); ?>
    };
</script>
<script src="<?php echo $NewRootPath; ?>v2/bi/supplier-intelligence.js?v=20260831-3"></script>

<?php
include_once(dirname(__DIR__) . '/includes/footer.php');
include_once(dirname(__DIR__) . '/includes/foot.php');
