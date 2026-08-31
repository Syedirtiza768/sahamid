<?php

if (isset($_GET['report'])) {
	header('Location: live_report.php?' . http_build_query($_GET));
	exit;
}

/**
 * Generic BI report workspace shell.
 *
 * Compatibility reports use this page for a consistent, bookmarkable BI
 * starting point while their proven legacy calculation remains available.
 */

$active = 'bi_reports';
$AllowAnyone = true;
include_once(dirname(__DIR__) . '/config.php');

if (!userHasPermission($db, 'sales_dashboard')) {
	header('Location: ' . $NewRootPath);
	exit;
}

require_once(dirname(dirname(__DIR__)) . '/bi/bootstrap.php');
$reportId = isset($_GET['report']) ? (string) $_GET['report'] : '';
$registry = new \SAHamid\BI\Reports\ReportRegistry();
try {
	$report = $registry->get($reportId);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	http_response_code(404);
	echo 'Report not found.';
	exit;
}

$definition = $report->toArray();
$title = htmlspecialchars($definition['title'], ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($definition['description'], ENT_QUOTES, 'UTF-8');
$legacyRoute = isset($definition['legacy_route']) ? $definition['legacy_route'] : '';
$legacyUrl = $legacyRoute ? $NewRootPath . $legacyRoute : '';
$dateToday = date('Y-m-d');
$dateYearStart = date('Y-01-01');
?>

<?php include_once(dirname(__DIR__) . '/includes/header.php'); ?>
<?php include_once(dirname(__DIR__) . '/includes/sidebar.php'); ?>

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/report.css">

<div class="content-wrapper bi-workspace" data-report-id="<?php echo htmlspecialchars($definition['id'], ENT_QUOTES, 'UTF-8'); ?>">
    <section class="content-header bi-workspace-header">
        <div class="bi-workspace-breadcrumb"><a href="<?php echo $NewRootPath; ?>v2/bi/reports.php"><i class="fa fa-chevron-left"></i> Enhanced Report Library</a><span>/</span><?php echo htmlspecialchars($definition['category'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div class="bi-workspace-title-row">
            <div>
                <h1><?php echo $title; ?> <span class="bi-workspace-status bi-workspace-status-<?php echo htmlspecialchars($definition['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $definition['status'] === 'enhanced' ? 'BI-native' : 'Compatibility workspace'; ?></span></h1>
                <p><?php echo $description; ?></p>
            </div>
            <div class="bi-workspace-actions">
                <?php if ($legacyUrl) { ?><a class="btn btn-default" href="<?php echo htmlspecialchars($legacyUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> Open original</a><?php } ?>
                <button type="button" class="btn btn-primary" id="biWorkspaceRefresh"><i class="fa fa-refresh"></i> Refresh</button>
                <button type="button" class="btn btn-default" id="biWorkspaceSave"><i class="fa fa-bookmark-o"></i> Save view</button>
            </div>
        </div>
        <div class="bi-workspace-meta"><span><i class="fa fa-clock-o"></i> Last refreshed: <strong id="biWorkspaceRefreshed">—</strong></span><span><i class="fa fa-database"></i> Source: <strong><?php echo htmlspecialchars($definition['source'], ENT_QUOTES, 'UTF-8'); ?></strong></span></div>
    </section>

    <section class="content bi-workspace-content">
        <div id="biWorkspaceAlert" class="alert bi-workspace-alert" role="alert" style="display:none;"></div>

        <section class="bi-workspace-panel bi-workspace-filter-panel" aria-label="Report controls">
            <div class="bi-workspace-panel-heading"><div><span class="bi-workspace-kicker">Explore the report</span><h2>Report controls</h2></div><button type="button" class="btn btn-link btn-xs" id="biWorkspaceClear"><i class="fa fa-undo"></i> Reset filters</button></div>
            <div class="row bi-workspace-filter-grid">
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="biWorkspacePreset">Date range</label><select id="biWorkspacePreset" class="form-control"><option value="ytd">Year to date</option><option value="today">Today</option><option value="yesterday">Yesterday</option><option value="week">This week</option><option value="last_week">Last week</option><option value="month">This month</option><option value="last_month">Last month</option><option value="quarter">This quarter</option><option value="last_quarter">Last quarter</option><option value="last_7">Last 7 days</option><option value="last_30">Last 30 days</option><option value="last_90">Last 90 days</option><option value="year">This year</option><option value="last_year">Last year</option><option value="all">All time</option><option value="custom">Custom range</option></select></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="biWorkspaceStart">From</label><input id="biWorkspaceStart" class="form-control" type="date" value="<?php echo $dateYearStart; ?>"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="biWorkspaceEnd">To</label><input id="biWorkspaceEnd" class="form-control" type="date" value="<?php echo $dateToday; ?>"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="biWorkspaceSearch">Search</label><input id="biWorkspaceSearch" class="form-control" type="search" placeholder="Report-specific search"></div>
                <div class="col-lg-2 col-md-3 col-sm-6"><label for="biWorkspaceGroup">Group by</label><select id="biWorkspaceGroup" class="form-control"><option value="">Choose after migration</option><?php foreach ($definition['group_by'] as $group) { ?><option value="<?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?></option><?php } ?></select></div>
                <div class="col-lg-2 col-md-3 col-sm-6 bi-workspace-advanced-button"><label>&nbsp;</label><button type="button" class="btn btn-default btn-block" id="biWorkspaceAdvanced"><i class="fa fa-sliders"></i> More filters <span id="biWorkspaceAdvancedCount" class="bi-filter-count">0</span></button></div>
            </div>
            <div id="biWorkspaceAdvancedPanel" class="bi-workspace-advanced-panel" hidden><div class="bi-advanced-copy"><strong>Advanced filters</strong><span>This shell preserves report state. Report-specific filters will be enabled with the validated BI handler for this report.</span></div><div class="bi-advanced-placeholder"><i class="fa fa-filter"></i> No additional BI filters are published for this compatibility workspace yet.</div></div>
            <div id="biWorkspaceChips" class="bi-workspace-chips" aria-live="polite"></div>
        </section>

        <nav class="bi-workspace-tabs" role="tablist" aria-label="Report views"><button type="button" class="is-active" data-tab="overview" role="tab" aria-selected="true"><i class="fa fa-dashboard"></i> Overview</button><button type="button" data-tab="data" role="tab" aria-selected="false"><i class="fa fa-table"></i> Detailed data</button><button type="button" data-tab="visualization" role="tab" aria-selected="false"><i class="fa fa-bar-chart"></i> Visualization</button></nav>

        <section class="bi-workspace-view is-active" data-view="overview">
            <div class="bi-workspace-panel bi-workspace-status-panel"><div class="bi-status-icon"><i class="fa fa-road"></i></div><div><span class="bi-workspace-kicker">Migration status</span><h2>This report is staged for BI modernization</h2><p>The original report remains the calculation authority. This workspace establishes the common filters, URL state, saved-view behavior, and executive/detail layout before report-specific queries are published.</p><div class="bi-workspace-status-list"><span><i class="fa fa-check"></i> Existing source preserved</span><span><i class="fa fa-check"></i> Permission boundary preserved</span><span><i class="fa fa-clock-o"></i> Validated BI handler pending</span></div></div></div>
            <div class="row"><div class="col-md-4"><div class="bi-workspace-info-card"><span>Available dimensions</span><strong><?php echo count($definition['group_by']); ?></strong><small><?php echo htmlspecialchars(implode(' · ', $definition['group_by']), ENT_QUOTES, 'UTF-8'); ?></small></div></div><div class="col-md-4"><div class="bi-workspace-info-card"><span>Source date role</span><strong><?php echo htmlspecialchars($definition['date_role'] ? $definition['date_role'] : 'Report-specific', ENT_QUOTES, 'UTF-8'); ?></strong><small>Use the original report for its complete date semantics until migration.</small></div></div><div class="col-md-4"><div class="bi-workspace-info-card"><span>Available visualizations</span><strong><?php echo count($definition['visualizations']); ?></strong><small><?php echo htmlspecialchars(implode(' · ', $definition['visualizations']), ENT_QUOTES, 'UTF-8'); ?></small></div></div></div>
        </section>

        <section class="bi-workspace-view" data-view="data" hidden><div class="bi-workspace-panel bi-workspace-empty-view"><i class="fa fa-table"></i><h2>Detailed data will appear here after validation</h2><p>Use <strong>Open original</strong> to access the existing report today. Once this report receives a governed handler, this view will provide server-side pagination, sorting, column selection, grouped subtotals, and permission-aware drill-through.</p><?php if ($legacyUrl) { ?><a class="btn btn-primary" href="<?php echo htmlspecialchars($legacyUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> Open original report</a><?php } ?></div></section>
        <section class="bi-workspace-view" data-view="visualization" hidden><div class="bi-workspace-panel bi-workspace-empty-view"><i class="fa fa-bar-chart"></i><h2>Visualization is intentionally waiting for a governed dataset</h2><p>Charts will be enabled only where the report’s dimensions and measures are mathematically meaningful. This avoids decorative or misleading KPIs while the existing calculation is being validated.</p></div></section>

        <section class="bi-workspace-panel bi-workspace-definition"><div><span class="bi-workspace-kicker">Governance details</span><h2>Report contract</h2></div><dl><div><dt>Grain</dt><dd><?php echo htmlspecialchars($definition['grain'], ENT_QUOTES, 'UTF-8'); ?></dd></div><div><dt>Filters</dt><dd><?php echo htmlspecialchars(implode(' · ', $definition['filters']), ENT_QUOTES, 'UTF-8'); ?></dd></div><div><dt>Aggregations</dt><dd><?php echo htmlspecialchars(implode(' · ', $definition['aggregations']), ENT_QUOTES, 'UTF-8'); ?></dd></div></dl></section>
    </section>
</div>

<script>
    window.SAHAMID_BI_REPORT = { reportId: <?php echo json_encode($definition['id']); ?>, title: <?php echo json_encode($definition['title']); ?>, today: <?php echo json_encode($dateToday); ?>, yearStart: <?php echo json_encode($dateYearStart); ?> };
</script>
<script src="<?php echo $NewRootPath; ?>v2/bi/report.js"></script>

<?php include_once(dirname(__DIR__) . '/includes/footer.php'); ?>
<?php include_once(dirname(__DIR__) . '/includes/foot.php'); ?>
