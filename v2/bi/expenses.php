<?php

/**
 * Expense Intelligence workspace.
 *
 * This is an additive, read-only BI view over the existing petty-cash
 * workflow. The API is responsible for all data retrieval and filtering.
 */

$active = 'bi_expenses';
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

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/expense.css?v=20260831-3">

<div class="content-wrapper expense-bi-page" id="expenseReportRoot" aria-busy="true">
    <section class="content-header expense-header">
        <div class="expense-header-copy">
            <div class="expense-eyebrow"><i class="fa fa-bar-chart"></i> Business Intelligence <span> / </span> Finance &amp; Controls</div>
            <h1>Expense Intelligence <small>Granular petty-cash spend analysis</small></h1>
            <p>Understand what was spent, by whom, where it was coded, how it moved over time, and which records still need attention.</p>
        </div>
        <div class="expense-header-actions">
            <span class="expense-live-pill"><i class="fa fa-circle"></i> Live ERP data</span>
            <button type="button" id="expenseRefresh" class="btn btn-primary"><i class="fa fa-refresh"></i> Refresh report</button>
            <button type="button" id="expenseExport" class="btn btn-default"><i class="fa fa-file-excel-o"></i> Export XLSX</button>
            <select id="expenseSavedView" class="form-control input-sm" aria-label="Saved private report views"><option value="">Saved views</option></select>
            <button type="button" id="expenseSaveView" class="btn btn-default"><i class="fa fa-bookmark-o"></i> Save view</button>
            <button type="button" id="expenseReset" class="btn btn-link"><i class="fa fa-undo"></i> Reset</button>
        </div>
    </section>

    <section class="content expense-content">
        <div id="expenseAlert" class="alert expense-alert" role="alert" style="display:none;"></div>

        <nav class="expense-section-nav" aria-label="Expense report sections">
            <a class="is-active" href="#overview"><i class="fa fa-dashboard"></i><span>Overview</span></a>
            <a href="#prompt"><i class="fa fa-terminal"></i><span>Prompt builder</span></a>
            <a href="#trend"><i class="fa fa-line-chart"></i><span>Trend</span></a>
            <a href="#breakdown"><i class="fa fa-pie-chart"></i><span>Categories &amp; GL</span></a>
            <a href="#owners"><i class="fa fa-users"></i><span>Owners &amp; tabs</span></a>
            <a href="#detail"><i class="fa fa-table"></i><span>Transaction detail</span></a>
            <a href="#quality"><i class="fa fa-shield"></i><span>Data quality</span></a>
        </nav>

        <section id="filters" class="expense-panel expense-filter-panel">
            <div class="expense-panel-heading">
                <div>
                    <span class="expense-kicker">Explore the ledger</span>
                    <h2>Report controls</h2>
                </div>
                <div class="expense-period-buttons" role="group" aria-label="Date presets">
                    <button type="button" class="btn btn-default btn-xs" data-period="month">This month</button>
                    <button type="button" class="btn btn-default btn-xs" data-period="quarter">Last 90 days</button>
                    <button type="button" class="btn btn-default btn-xs" data-period="ytd">Year to date</button>
                    <select id="expenseDatePreset" class="form-control input-sm" aria-label="More date presets">
                        <option value="">More date presets…</option>
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This week</option>
                        <option value="last_week">Last week</option>
                        <option value="this_month">This month</option>
                        <option value="last_month">Last month</option>
                        <option value="this_quarter">This quarter</option>
                        <option value="last_quarter">Last quarter</option>
                        <option value="this_year">This year</option>
                        <option value="last_year">Last year</option>
                        <option value="last_7">Last 7 days</option>
                        <option value="last_30">Last 30 days</option>
                        <option value="last_90">Last 90 days</option>
                        <option value="ytd">Year to date</option>
                        <option value="all">All available dates</option>
                    </select>
                    <button type="button" id="expenseAdvancedToggle" class="btn btn-default btn-xs" aria-expanded="false">More filters <span id="expenseAdvancedCount" class="expense-filter-count">0</span></button>
                </div>
            </div>
            <div class="row expense-filter-grid">
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label for="expenseStart">From</label>
                    <input id="expenseStart" class="form-control" type="date" value="<?php echo htmlspecialchars($yearStart, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label for="expenseEnd">To</label>
                    <input id="expenseEnd" class="form-control" type="date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 expense-advanced-field">
                    <label for="expenseCategory">Category</label>
                    <select id="expenseCategory" class="form-control"><option value="">All categories</option></select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 expense-advanced-field">
                    <label for="expenseTab">Petty-cash tab</label>
                    <select id="expenseTab" class="form-control"><option value="">All tabs</option></select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 expense-advanced-field">
                    <label for="expenseOwner">Owner</label>
                    <select id="expenseOwner" class="form-control"><option value="">All owners</option></select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label for="expenseGroupBy">Group breakdown by</label>
                    <select id="expenseGroupBy" class="form-control">
                        <option value="category">Category</option>
                        <option value="enhanced_tag">Enhanced classification</option>
                        <option value="gl">GL account</option>
                        <option value="owner">Owner</option>
                        <option value="tab">Petty-cash tab</option>
                        <option value="tab_type">Tab type</option>
                        <option value="tag">Tag</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 expense-advanced-field">
                    <label for="expenseAuth">Authorization</label>
                    <select id="expenseAuth" class="form-control">
                        <option value="all">All states</option>
                        <option value="authorized">Authorized only</option>
                        <option value="pending">Pending only</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 expense-advanced-field">
                    <label for="expensePosting">GL posting</label>
                    <select id="expensePosting" class="form-control">
                        <option value="all">All states</option>
                        <option value="posted">Posted only</option>
                        <option value="unposted">Unposted only</option>
                    </select>
                </div>
                <div class="col-lg-5 col-md-6 col-sm-12">
                    <label for="expenseSearch">Search notes, receipts, categories or tabs</label>
                    <input id="expenseSearch" class="form-control" type="search" placeholder="e.g. fuel, travel, supplier, receipt reference">
                </div>
                <div class="col-lg-3 col-md-3 col-sm-12 expense-filter-submit">
                    <button type="button" id="expenseApply" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Apply controls</button>
                </div>
            </div>
            <div id="expenseActiveFilterChips" class="expense-filter-chips" aria-live="polite"></div>
            <div class="expense-scope-note"><i class="fa fa-info-circle"></i> Spend is based on petty-cash claims in <code>pcashdetails</code>. Claims are stored as negative source amounts and displayed as positive spend magnitude. Cash assignments into tabs are excluded from spend and shown separately.</div>
        </section>

        <section id="prompt" class="expense-panel expense-prompt-panel">
            <div class="expense-prompt-intro">
                <div>
                    <span class="expense-kicker">Deterministic query builder</span>
                    <h2>Describe the view you need</h2>
                    <p>Write a plain-language request and the report will tokenize it, map it to governed filters, and run a safe parameterized query. No AI and no free-form SQL are used.</p>
                </div>
                <div class="expense-prompt-badge"><i class="fa fa-lock"></i> Allowlisted dimensions only</div>
            </div>
            <form id="expensePromptForm" class="expense-prompt-form">
                <div class="expense-prompt-input-wrap">
                    <label for="expensePromptInput">What do you want to analyze?</label>
                    <textarea id="expensePromptInput" class="form-control" rows="3" maxlength="500" placeholder="e.g. Show pending claims by owner for last month"></textarea>
                    <div class="expense-prompt-help" id="expensePromptHelp">Try a measure, grouping, date range, status, receipt rule, amount threshold, or top/bottom limit.</div>
                </div>
                <div class="expense-prompt-actions">
                    <button type="submit" id="expensePromptRun" class="btn btn-primary"><i class="fa fa-play"></i> Run request</button>
                    <button type="button" id="expensePromptClear" class="btn btn-link">Clear</button>
                </div>
            </form>
            <div class="expense-prompt-examples" aria-label="Prompt examples">
                <span>Examples:</span>
                <button type="button" class="expense-prompt-example" data-prompt-example="Show total spend by category for the last 90 days">Spend by category</button>
                <button type="button" class="expense-prompt-example" data-prompt-example="Show pending claims by owner for last month">Pending by owner</button>
                <button type="button" class="expense-prompt-example" data-prompt-example="List top 5 transactions with missing receipts over 100k">Top missing receipts</button>
            </div>
            <div id="expensePromptResult" class="expense-prompt-result" aria-live="polite">
                <div class="expense-prompt-empty"><i class="fa fa-terminal"></i><span>Enter a request to create an interactive analysis.</span></div>
            </div>
        </section>

        <section id="overview" class="expense-report-section">
            <div class="expense-section-title">
                <div>
                    <span class="expense-kicker">Decision view</span>
                    <h2>Spend overview</h2>
                    <p id="expenseSummarySubtitle">Loading the selected period…</p>
                </div>
                <div class="expense-context" id="expenseContext">Active company · read-only</div>
            </div>

            <div class="row expense-kpi-row">
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="expense-kpi-card kpi-blue"><span class="expense-kpi-label">Total spend</span><strong id="kpiTotal">—</strong><small id="kpiTotalMeta">Claims only</small><span id="kpiTotalCompare" class="expense-kpi-comparison">—</span></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="expense-kpi-card kpi-purple"><span class="expense-kpi-label">Expense claims</span><strong id="kpiClaims">—</strong><small id="kpiClaimsMeta">Transactions</small><span id="kpiClaimsCompare" class="expense-kpi-comparison">—</span></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="expense-kpi-card kpi-teal"><span class="expense-kpi-label">Average claim</span><strong id="kpiAverage">—</strong><small id="kpiAverageMeta">Per transaction</small><span id="kpiAverageCompare" class="expense-kpi-comparison">—</span></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="expense-kpi-card kpi-amber"><span class="expense-kpi-label">Pending approval</span><strong id="kpiPending">—</strong><small id="kpiPendingMeta">Needs review</small><span id="kpiPendingCompare" class="expense-kpi-comparison">—</span></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="expense-kpi-card kpi-red"><span class="expense-kpi-label">Unposted to GL</span><strong id="kpiUnposted">—</strong><small id="kpiUnpostedMeta">Control queue</small><span id="kpiUnpostedCompare" class="expense-kpi-comparison">—</span></div></div>
                <div class="col-lg-2 col-md-4 col-sm-6"><div class="expense-kpi-card kpi-green"><span class="expense-kpi-label">Receipt coverage</span><strong id="kpiReceipts">—</strong><small id="kpiReceiptsMeta">Evidence attached</small><span id="kpiReceiptsCompare" class="expense-kpi-comparison">—</span></div></div>
            </div>

            <div class="row expense-insight-row">
                <div class="col-md-8"><div class="expense-insight-card"><div class="expense-insight-icon"><i class="fa fa-lightbulb-o"></i></div><div><span class="expense-kicker">Management signal</span><strong id="expenseInsightHeadline">Building the report narrative…</strong><p id="expenseInsightBody">The report will highlight concentration, timing changes, and control gaps after the selected data is loaded.</p></div></div></div>
                <div class="col-md-4"><div class="expense-side-stat"><span class="expense-kicker">Cash assigned in period</span><strong id="expenseAssigned">—</strong><p id="expenseAssignedMeta">Transfers into petty-cash tabs are tracked separately.</p></div></div>
                <div class="col-md-12"><div class="expense-exception-strip"><div><span class="expense-kicker">Deterministic exceptions</span><strong id="expenseExceptionHeadline">Checking for unusual claims…</strong><p id="expenseExceptionBody">Exceptions are calculated from the selected filtered data.</p></div><ul id="expenseExceptionList" aria-live="polite"><li>Loading exceptions…</li></ul></div></div>
            </div>
        </section>

        <section id="trend" class="expense-report-section">
            <div class="expense-section-title"><div><span class="expense-kicker">Time series</span><h2>When is spend happening?</h2><p>Monthly run-rate makes spikes, seasonality, and control queues visible.</p></div></div>
            <div class="row">
                <div class="col-lg-8"><div class="expense-panel chart-panel"><div class="expense-chart-title"><strong>Monthly spend run-rate</strong><span>Spend magnitude with pending and unposted overlays</span></div><div class="expense-chart-wrap"><canvas id="expenseTrendChart"></canvas><div class="chart-empty" id="expenseTrendEmpty">No transactions in the selected period.</div></div></div></div>
                <div class="col-lg-4"><div class="expense-panel chart-panel"><div class="expense-chart-title"><strong>Claims vs. control queue</strong><span>How many records need follow-up each month?</span></div><div class="expense-chart-wrap compact"><canvas id="expenseStatusChart"></canvas><div class="chart-empty" id="expenseStatusEmpty">No transactions in the selected period.</div></div></div></div>
            </div>
        </section>

        <section id="breakdown" class="expense-report-section">
            <div class="expense-section-title"><div><span class="expense-kicker">Mix &amp; coding</span><h2>What is driving the spend?</h2><p>Use the grouping control above to move from category to GL, tag, tab type, or any other governed dimension.</p></div><div class="expense-selection-label">Breakdown: <strong id="expenseBreakdownLabel">Expense category</strong></div></div>
            <div class="row">
                <div class="col-lg-5"><div class="expense-panel chart-panel"><div class="expense-chart-title"><div><strong>Spend mix</strong><span>Click a category slice to filter the report</span></div><div class="expense-inline-control-group"><label class="expense-inline-control" for="expenseVisualMetric">Visualize <select id="expenseVisualMetric" class="form-control input-sm"><option value="spend">Spend</option><option value="transaction_count">Claims</option><option value="average_spend">Average claim</option><option value="pending_spend">Pending spend</option><option value="unposted_spend">Unposted spend</option><option value="receipt_coverage">Receipt coverage</option></select></label><label class="expense-inline-control" for="expenseTopN">Top <select id="expenseTopN" class="form-control input-sm"><option value="5">5</option><option value="10">10</option><option value="20" selected>20</option><option value="50">50</option></select></label></div></div><div class="expense-chart-wrap compact"><canvas id="expenseMixChart" aria-label="Expense mix chart"></canvas><div class="chart-empty" id="expenseMixEmpty">No grouped spend available.</div></div></div></div>
                <div class="col-lg-7"><div class="expense-panel table-panel"><div class="expense-chart-title"><strong id="expenseBreakdownTableTitle">Category detail</strong><span>Spend concentration, control state, evidence coverage, and distribution</span></div><div class="table-responsive"><table class="table expense-table" id="expenseBreakdownTable"><thead><tr><th>#</th><th>Group</th><th class="text-right">Spend</th><th class="text-right">Share</th><th class="text-right">Claims</th><th class="text-right">Avg claim</th><th class="text-right">Min</th><th class="text-right">Max</th><th class="text-right">Pending</th><th class="text-right">Pending spend</th><th class="text-right">Unposted spend</th><th class="text-right">Receipts</th></tr></thead><tbody><tr><td colspan="12" class="expense-empty-cell">Loading grouped analysis…</td></tr></tbody></table></div></div></div>
            </div>
            <div class="row expense-mini-grid">
                <div class="col-md-6"><div class="expense-panel mini-table-panel"><div class="expense-chart-title"><strong>Category concentration</strong><span>Largest categories by spend</span></div><div class="table-responsive"><table class="table expense-table" id="expenseCategoryTable"><thead><tr><th>Category / enhanced tag</th><th class="text-right">Spend</th><th class="text-right">Claims</th><th class="text-right">Receipt %</th></tr></thead><tbody><tr><td colspan="4" class="expense-empty-cell">Loading…</td></tr></tbody></table></div></div></div>
                <div class="col-md-6"><div class="expense-panel mini-table-panel"><div class="expense-chart-title"><strong>GL coding footprint</strong><span>Expense accounts receiving the claims</span></div><div class="table-responsive"><table class="table expense-table" id="expenseOwnerTable"><thead><tr><th>Owner</th><th class="text-right">Spend</th><th class="text-right">Claims</th><th class="text-right">Pending</th></tr></thead><tbody><tr><td colspan="4" class="expense-empty-cell">Loading…</td></tr></tbody></table></div></div></div>
            </div>
        </section>

        <section id="owners" class="expense-report-section">
            <div class="expense-section-title"><div><span class="expense-kicker">Accountability</span><h2>Who is spending, and through which tabs?</h2><p>Owner and tab views help managers connect spend to responsibility, tab limits, and operating patterns.</p></div></div>
            <div class="row">
                <div class="col-lg-7"><div class="expense-panel chart-panel"><div class="expense-chart-title"><strong>Owner spend ranking</strong><span>Click a bar to filter by owner</span></div><div class="expense-chart-wrap owner-chart"><canvas id="expenseOwnerChart" aria-label="Owner spend ranking chart"></canvas><div class="chart-empty" id="expenseOwnerEmpty">No owner data available.</div></div></div></div>
                <div class="col-lg-5"><div class="expense-panel"><div class="expense-chart-title"><strong>How to read this report</strong><span>Recommended analysis path</span></div><ol class="expense-reading-path"><li><span>01</span><div><strong>Start with concentration</strong><p>Find categories, GL accounts, owners, or tabs that explain most of the spend.</p></div></li><li><span>02</span><div><strong>Check the timing</strong><p>Compare monthly run-rate with pending and unposted queues before drawing conclusions.</p></div></li><li><span>03</span><div><strong>Drill to evidence</strong><p>Open transaction detail, review receipts, and resolve unmapped master data.</p></div></li></ol></div></div>
            </div>
        </section>

        <section id="detail" class="expense-report-section">
            <div class="expense-section-title"><div><span class="expense-kicker">Drill-through</span><h2>Transaction detail</h2><p>One row per petty-cash claim. Detail is paginated for browser performance; XLSX export includes all matching server-side rows up to 25,000.</p></div><div class="expense-detail-status" id="expenseDetailStatus">Loading…</div></div>
            <div class="expense-panel table-panel">
                <div class="expense-detail-toolbar"><label for="expenseDetailSearch"><i class="fa fa-search"></i> Find within loaded detail</label><input id="expenseDetailSearch" class="form-control input-sm" type="search" placeholder="Refine the loaded rows"><label for="expensePageSize" class="expense-page-size-label">Rows</label><select id="expensePageSize" class="form-control input-sm"><option value="25">25</option><option value="50" selected>50</option><option value="100">100</option><option value="250">250</option></select><button type="button" id="expensePrevPage" class="btn btn-default btn-xs" disabled><i class="fa fa-chevron-left"></i> Previous</button><button type="button" id="expenseNextPage" class="btn btn-default btn-xs" disabled>Next <i class="fa fa-chevron-right"></i></button><span class="text-muted" id="expensePageStatus">Loading…</span></div>
                <div class="table-responsive"><table class="table expense-table expense-detail-table" id="expenseDetailTable"><thead><tr><th class="expense-sortable" data-sort="date">Date</th><th class="expense-sortable" data-sort="category">Category</th><th>Enhanced tag</th><th class="expense-sortable" data-sort="tab">Tab</th><th class="expense-sortable" data-sort="owner">Owner</th><th class="expense-sortable" data-sort="gl">GL account</th><th>Tab type</th><th>Status</th><th class="expense-sortable text-right" data-sort="spend">Spend</th><th>Receipt</th><th>Notes</th></tr></thead><tbody><tr><td colspan="11" class="expense-empty-cell">Loading detail…</td></tr></tbody></table></div>
            </div>
        </section>

        <section id="quality" class="expense-report-section">
            <div class="expense-section-title"><div><span class="expense-kicker">Trust &amp; controls</span><h2>Data quality and follow-up queue</h2><p>These signals do not change the spend total; they show where the source records need review before deeper interpretation.</p></div><div class="expense-quality-badge" id="expenseQualityBadge">Loading controls…</div></div>
            <div class="row expense-quality-grid">
                <div class="col-lg-3 col-md-4 col-sm-6"><div class="expense-quality-card"><i class="fa fa-file-o"></i><div><strong id="qualityMissingReceipt">—</strong><span>Claims without receipt evidence</span></div></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><div class="expense-quality-card"><i class="fa fa-tags"></i><div><strong id="qualityMissingCategory">—</strong><span>Claims without category mapping</span></div></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><div class="expense-quality-card"><i class="fa fa-book"></i><div><strong id="qualityMissingGl">—</strong><span>Claims without GL mapping</span></div></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><div class="expense-quality-card"><i class="fa fa-user"></i><div><strong id="qualityMissingTab">—</strong><span>Claims without tab master</span></div></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><div class="expense-quality-card warning"><i class="fa fa-clock-o"></i><div><strong id="qualityPending">—</strong><span>Pending authorization</span></div></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><div class="expense-quality-card warning"><i class="fa fa-upload"></i><div><strong id="qualityUnposted">—</strong><span>Unposted to GL</span></div></div></div>
                <div class="col-lg-3 col-md-4 col-sm-6"><div class="expense-quality-card warning"><i class="fa fa-exclamation-triangle"></i><div><strong id="qualitySign">—</strong><span>Non-negative source amounts</span></div></div></div>
            </div>
            <div class="expense-panel table-panel expense-classification-panel"><div class="expense-chart-title"><div><strong>Enhanced classification dictionary</strong><span>Every configured expense description is read and classified for management analysis</span></div><span id="expenseClassificationStatus">Loading descriptions…</span></div><div class="table-responsive"><table class="table expense-table" id="expenseClassificationTable"><thead><tr><th>Code</th><th>Configured description</th><th>Enhanced tag</th><th>Keyword signal</th></tr></thead><tbody><tr><td colspan="4" class="expense-empty-cell">Loading descriptions…</td></tr></tbody></table></div></div>
            <div class="expense-panel expense-definition-panel"><div class="row"><div class="col-md-7"><h3><i class="fa fa-shield"></i> Metric definition and lineage</h3><p><strong>Total spend</strong> = the positive magnitude of non-<code>ASSIGNCASH</code> petty-cash claim amounts from <code>pcashdetails</code>, grouped using the selected master-data dimensions. Authorization comes from <code>pcashdetails.authorized</code>; GL posting comes from <code>pcashdetails.posted</code>; receipt coverage checks <code>receipt</code> and <code>receiptimage</code>.</p></div><div class="col-md-5"><h3><i class="fa fa-database"></i> Source context</h3><dl class="expense-source-list"><dt>Grain</dt><dd>One row per petty-cash claim</dd><dt>Date role</dt><dd><code>pcashdetails.date</code></dd><dt>Read mode</dt><dd>Read-only; no ERP records are mutated</dd><dt>Transfers</dt><dd>Cash assignments reported separately</dd></dl></div></div></div>
        </section>

        <div class="expense-report-footer"><span><i class="fa fa-clock-o"></i> Last refreshed: <strong id="expenseLastRefreshed">—</strong></span><span>Applied period: <strong id="expenseAppliedPeriod">—</strong></span><span>Matching claims: <strong id="expenseRecordCount">—</strong></span><span>Server: <strong id="expenseServerDuration">—</strong></span><span>Report status: <strong id="expenseReportStatus" aria-live="polite">Loading</strong></span></div>
    </section>
</div>

<script>
    window.SAHAMID_EXPENSE_BI = {
        summaryUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/expenses.php'); ?>,
        detailUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/expenses.php'); ?>,
        exportUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/expenses.php'); ?>,
        defaultStart: <?php echo json_encode($yearStart); ?>,
        defaultEnd: <?php echo json_encode($today); ?>
    };
</script>
<script src="<?php echo $NewRootPath; ?>v2/bi/expense.js?v=20260831-3"></script>

<?php
include_once(dirname(__DIR__) . '/includes/footer.php');
include_once(dirname(__DIR__) . '/includes/foot.php');
