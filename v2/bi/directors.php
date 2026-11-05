<?php
/** Consolidated, permission-aware directors' reporting workspace. */
$active = 'bi_directors';
$AllowAnyone = true;
include_once(dirname(__DIR__) . '/config.php');
require_once(dirname(__DIR__, 2) . '/bi/bootstrap.php');

try {
    $directorsContext = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
    (new \SAHamid\BI\Directors\DirectorsAuthorization($directorsContext, $_SESSION))->assertAccess();
} catch (\SAHamid\BI\Exception\BIException $exception) {
    http_response_code($exception->getHttpStatus());
    echo '<p>' . htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') . '</p>';
    exit;
}

include_once(dirname(__DIR__) . '/includes/header.php');
include_once(dirname(__DIR__) . '/includes/sidebar.php');
?>
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/directors.css">
<div class="content-wrapper directors-page" id="directorsRoot" aria-busy="true">
    <section class="content-header dir-hero">
        <div class="dir-eyebrow">BUSINESS INTELLIGENCE <span>/</span> DIRECTORS</div>
        <div class="dir-hero-row">
            <div><h1>Directors’ Reporting Dashboard</h1><p>Performance, exposure and the records behind every number.</p></div>
            <div class="dir-actions"><a class="dir-button" href="<?php echo $NewRootPath; ?>v2/bi/reports.php"><i class="fa fa-files-o" aria-hidden="true"></i> Report library</a><button class="dir-button" id="dirRefresh" type="button"><i class="fa fa-refresh" aria-hidden="true"></i> Refresh</button><button class="dir-button dir-primary" id="dirExportSummary" type="button" title="Export the authorized company overview for the applied reporting period"><i class="fa fa-download" aria-hidden="true"></i> Export overview</button></div>
        </div>
        <div class="dir-context"><strong id="dirCompany">Resolving company…</strong><span id="dirScope">Authorized company data</span><span id="dirFreshness">Live source · awaiting refresh</span></div>
    </section>
    <section class="content dir-content">
        <div id="dirAlert" class="dir-alert" role="alert" hidden></div>
        <form id="dirFilters" class="dir-panel dir-filter-panel">
            <div class="dir-filter-grid">
                <label>Reporting period<select id="dirPreset"><option value="ytd">Fiscal year to date</option><option value="month">Month to date</option><option value="quarter">Quarter to date</option><option value="last_month">Last month</option><option value="last_quarter">Last quarter</option><option value="last_year">Last fiscal year</option><option value="custom">Custom dates</option></select></label>
                <label>From<input id="dirStart" type="date" required></label>
                <label>To<input id="dirEnd" type="date" required></label>
                <label>Compare with<select id="dirComparison"><option value="previous">Previous equal period</option><option value="mom">Previous month</option><option value="qoq">Previous quarter</option><option value="yoy">Previous year</option><option value="custom">Custom comparison</option><option value="none">No comparison</option></select></label>
                <label id="dirCustomStartWrap" hidden>Comparison from<input id="dirComparisonStart" type="date"></label>
                <label id="dirCustomEndWrap" hidden>Comparison to<input id="dirComparisonEnd" type="date"></label>
                <button class="dir-button dir-primary dir-apply" type="submit"><i class="fa fa-filter" aria-hidden="true"></i> Apply</button>
            </div>
            <div id="dirPeriodContext" class="dir-period-context" aria-live="polite">Loading reporting periods…</div>
            <div id="dirChips" class="dir-chips" aria-label="Active investigation filters"></div>
        </form>
        <nav class="dir-tabs" aria-label="Directors dashboard views"><button type="button" data-view="overview" class="is-active">Executive overview</button><button type="button" data-view="explore">Investigate</button><button type="button" data-view="coverage">Definitions &amp; coverage</button></nav>

        <section id="dirOverview" aria-label="Executive overview">
            <div class="dir-section-heading"><div><div class="dir-eyebrow">THE BUSINESS AT A GLANCE</div><h2>Results &amp; exposure</h2><p>Period flows and current balances are labeled separately. Select a measure to investigate.</p></div><span id="dirCoverageStatus" class="dir-tag">Loading coverage…</span></div>
            <div id="dirMetricGroups" class="dir-metric-groups"><div class="dir-placeholder">Loading authorized business measures…</div></div>
            <section id="dirBudgetComparison" class="dir-panel" aria-label="Recorded budget versus actual" hidden></section>
            <div class="dir-section-heading"><div><div class="dir-eyebrow">FOCUS YOUR ATTENTION</div><h2>Risks &amp; opportunities</h2><p>Deterministic exceptions from the selected records, with a path to the evidence.</p></div></div>
            <div id="dirRisks" class="dir-risk-grid"><div class="dir-placeholder">Checking changes and exceptions…</div></div>
        </section>

        <section id="dirExplore" aria-label="Investigate a business measure" hidden>
            <div class="dir-section-heading"><div><div class="dir-eyebrow">FROM THE NUMBER TO THE RECORD</div><h2 id="dirExploreTitle">Investigate performance</h2><p id="dirExploreSubtitle">Choose a measure to explore its movements, concentrations and source records.</p></div><button type="button" id="dirBack" class="dir-button">← Executive overview</button></div>
            <div class="dir-panel dir-explore-controls">
                <label>Business measure<select id="dirDataset"><option value="">Choose a measure</option></select></label>
                <label>Break down by<select id="dirGroup"><option value="">Choose a dimension</option></select></label>
                <label>Find records<input id="dirSearch" type="search" maxlength="100" placeholder="Reference or description"></label>
                <button type="button" class="dir-button" id="dirExploreApply">Apply investigation</button>
            </div>
            <div id="dirInvestigationEmpty" class="dir-placeholder">Select any available measure from the overview to begin.</div>
            <div id="dirInvestigation" hidden>
                <div class="dir-investigation-kpis" id="dirInvestigationKpis"></div>
                <div class="dir-definition" id="dirDefinition"></div>
                <div class="dir-analysis-grid">
                    <section class="dir-panel"><div class="dir-panel-heading"><div><h3 id="dirTrendTitle">Trend &amp; comparison</h3><p>Choose a month to investigate that interval.</p></div><span class="dir-chart-legend"><i></i> Current <i class="dir-legend-comparison"></i> Comparison</span></div><div id="dirTrend" class="dir-chart"></div><details class="dir-chart-data"><summary>View trend values</summary><div id="dirTrendTable" class="dir-table-wrap"></div></details></section>
                    <section class="dir-panel"><div class="dir-panel-heading"><div><h3 id="dirBreakdownTitle">Contributors to movement</h3><p>Choose a group to filter the underlying records.</p></div></div><div id="dirBreakdown" class="dir-breakdown"></div></section>
                </div>
                <section class="dir-panel dir-record-panel"><div class="dir-panel-heading"><div><h3>Underlying records</h3><p id="dirRecordContext">Source rows using the same reporting definition and filters.</p></div><button type="button" id="dirExport" class="dir-button"><i class="fa fa-file-excel-o" aria-hidden="true"></i> Export filtered records</button></div><div class="dir-table-toolbar"><label>Rows<select id="dirPageSize"><option value="25">25</option><option value="50" selected>50</option><option value="100">100</option></select></label><span id="dirPageStatus" role="status"></span><div class="dir-pagination"><button type="button" class="dir-button" id="dirPrevious">← Previous</button><button type="button" class="dir-button" id="dirNext">Next →</button></div></div><div class="dir-table-wrap"><table class="dir-table" id="dirRecords"><thead></thead><tbody></tbody></table></div></section>
            </div>
        </section>

        <section id="dirCoverage" aria-label="Reporting definitions and limitations" hidden>
            <div class="dir-section-heading"><div><div class="dir-eyebrow">KNOW WHAT THE NUMBERS MEAN</div><h2>Definitions &amp; reporting coverage</h2><p>Actual source activity, estimates and unavailable measures remain explicit.</p></div></div>
            <section class="dir-panel"><div class="dir-panel-heading"><div><h3>Known limitations &amp; missing evidence</h3><p>These limitations apply to interpretation and comparison.</p></div></div><ul id="dirLimitations" class="dir-limitations"><li>Loading coverage…</li></ul></section>
            <section class="dir-panel"><div class="dir-panel-heading"><div><h3>Business questions &amp; metric definitions</h3><p>Each measure can be investigated using its supported dimensions. Measures from different grains are never added into a single business-volume total.</p></div></div><div class="dir-table-wrap"><table class="dir-table dir-coverage-table"><thead><tr><th>Measure / business question</th><th>Basis &amp; coverage</th><th>Definition &amp; limitations</th><th>Available dimensions</th></tr></thead><tbody id="dirDefinitions"></tbody></table></div></section>
        </section>
        <footer class="dir-footer"><span id="dirStatus" role="status" aria-live="polite">Loading live data…</span><span>Source records open in a separate tab so this investigation stays in place.</span></footer>
    </section>
</div>
<script>
window.SAHAMID_DIRECTORS = {
    apiUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/directors.php'); ?>,
    rootUrl: <?php echo json_encode($NewRootPath); ?>,
    today: <?php echo json_encode(date('Y-m-d')); ?>
};
</script>
<script src="<?php echo $NewRootPath; ?>v2/bi/directors.js"></script>
<?php
include_once(dirname(__DIR__) . '/includes/footer.php');
include_once(dirname(__DIR__) . '/includes/foot.php');
