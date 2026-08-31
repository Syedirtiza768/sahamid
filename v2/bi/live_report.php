<?php

/**
 * Live BI workspace for reports whose existing calculation and filters remain
 * authoritative. The source report runs in the active ERP session while the
 * BI shell adds consistent navigation and result-table tools.
 */

$active = 'bi_reports';
$AllowAnyone = true;
include_once(dirname(__DIR__) . '/config.php');

if (!userHasPermission($db, 'sales_dashboard')) {
	header('Location: ' . $NewRootPath);
	exit;
}

require_once(dirname(dirname(__DIR__)) . '/bi/bootstrap.php');
$reportId = isset($_GET['report']) ? trim((string) $_GET['report']) : '';
$registry = new \SAHamid\BI\Reports\ReportRegistry();
try {
	$report = $registry->get($reportId);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	http_response_code(404);
	echo 'Report not found.';
	exit;
}

$definition = $report->toArray();
$authorizationContext = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
$reportAuthorization = new \SAHamid\BI\Security\ReportAuthorization($_SESSION);
if (!$reportAuthorization->isAllowed($report, $authorizationContext)) {
	http_response_code(403);
	echo 'You are not authorized to use this report.';
	exit;
}
if (!empty($definition['bi_route'])) {
	header('Location: ' . $NewRootPath . $definition['bi_route']);
	exit;
}

$legacyRoute = isset($definition['legacy_route']) ? (string) $definition['legacy_route'] : '';
$sourcePath = preg_replace('/[?#].*$/', '', $legacyRoute);
$absoluteSourcePath = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sourcePath);
if ($legacyRoute === '' || !is_file($absoluteSourcePath)) {
	http_response_code(404);
	echo 'The source report route is unavailable.';
	exit;
}

$sourceUrl = $NewRootPath . $legacyRoute;
$title = htmlspecialchars($definition['title'], ENT_QUOTES, 'UTF-8');
$category = htmlspecialchars($definition['category'], ENT_QUOTES, 'UTF-8');
$description = htmlspecialchars($definition['description'], ENT_QUOTES, 'UTF-8');

include_once(dirname(__DIR__) . '/includes/header.php');
include_once(dirname(__DIR__) . '/includes/sidebar.php');
?>

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/live_report.css">

<div class="content-wrapper bi-live-page" id="biLiveRoot" data-report-id="<?php echo htmlspecialchars($definition['id'], ENT_QUOTES, 'UTF-8'); ?>" aria-busy="true">
    <section class="content-header bi-live-header">
        <div class="bi-live-breadcrumb"><a href="<?php echo $NewRootPath; ?>v2/bi/reports.php"><i class="fa fa-chevron-left"></i> Enhanced Report Library</a><span>/</span><?php echo $category; ?></div>
        <div class="bi-live-title-row">
            <div>
                <h1><?php echo $title; ?> <span class="bi-live-badge"><i class="fa fa-circle"></i> Live report</span></h1>
                <p><?php echo $description; ?></p>
            </div>
            <div class="bi-live-header-actions">
                <select id="biLiveSavedViews" class="form-control input-sm" aria-label="Saved private report views"><option value="">Saved views</option></select>
                <button type="button" class="btn btn-default" id="biLiveSaveView"><i class="fa fa-bookmark-o"></i> Save view</button>
                <a class="btn btn-default" id="biLiveOpenSource" href="<?php echo htmlspecialchars($sourceUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener"><i class="fa fa-external-link"></i> Open full page</a>
            </div>
        </div>
        <div class="bi-live-meta"><span><i class="fa fa-lock"></i> Active ERP permissions</span><span><i class="fa fa-database"></i> Existing calculation and filters</span><span><i class="fa fa-clock-o"></i> Loaded: <strong id="biLiveRefreshed">—</strong></span></div>
    </section>

    <section class="content bi-live-content">
        <div id="biLiveAlert" class="alert bi-live-alert" role="alert" style="display:none;"></div>

        <section class="bi-live-toolbar" aria-label="Live report tools">
            <div class="bi-live-history-tools">
                <button type="button" class="btn btn-default btn-sm" id="biLiveBack" title="Back inside report"><i class="fa fa-chevron-left"></i></button>
                <button type="button" class="btn btn-default btn-sm" id="biLiveForward" title="Forward inside report"><i class="fa fa-chevron-right"></i></button>
                <button type="button" class="btn btn-primary btn-sm" id="biLiveReload"><i class="fa fa-refresh"></i> Reload data</button>
            </div>
            <div class="bi-live-search"><i class="fa fa-search"></i><input id="biLiveSearch" type="search" class="form-control input-sm" placeholder="Search loaded table rows"></div>
            <select id="biLiveDensity" class="form-control input-sm" aria-label="Table density"><option value="comfortable">Comfortable rows</option><option value="compact">Compact rows</option></select>
            <button type="button" class="btn btn-default btn-sm" id="biLiveColumns"><i class="fa fa-columns"></i> Columns</button>
            <button type="button" class="btn btn-default btn-sm" id="biLiveExport"><i class="fa fa-file-excel-o"></i> Export visible XLSX</button>
            <button type="button" class="btn btn-default btn-sm" id="biLivePrint"><i class="fa fa-print"></i> Print</button>
            <button type="button" class="btn btn-default btn-sm" id="biLiveFullscreen"><i class="fa fa-expand"></i> Fullscreen</button>
        </section>

        <div id="biLiveColumnPanel" class="bi-live-column-panel" hidden>
            <div><strong>Visible columns</strong><span>Column controls apply to the largest loaded result table.</span></div>
            <div id="biLiveColumnOptions" class="bi-live-column-options"><span class="text-muted">Generate or load a table to configure columns.</span></div>
        </div>

        <div class="bi-live-guidance"><i class="fa fa-info-circle"></i> Use the report’s own filters inside the workspace. After results load, the BI toolbar can search rows, hide columns, change density, print, or export the visible table to XLSX.</div>

        <section class="bi-live-frame-panel" id="biLiveFramePanel">
            <div class="bi-live-frame-status"><span id="biLiveFrameStatus"><i class="fa fa-spinner fa-spin"></i> Loading live report…</span><span id="biLiveResultStatus">Waiting for report content</span></div>
            <div class="bi-live-frame-wrap">
                <div class="bi-live-loading" id="biLiveLoading"><i class="fa fa-spinner fa-spin"></i><strong>Opening the live report</strong><span>The report is using your current ERP session and permissions.</span></div>
                <iframe id="biLiveFrame" title="<?php echo $title; ?> live report" src="<?php echo htmlspecialchars($sourceUrl, ENT_QUOTES, 'UTF-8'); ?>"></iframe>
            </div>
        </section>

        <section class="bi-live-source-details">
            <div><span>Source route</span><strong><?php echo htmlspecialchars($legacyRoute, ENT_QUOTES, 'UTF-8'); ?></strong></div>
            <div><span>Report source</span><strong><?php echo htmlspecialchars($definition['source'], ENT_QUOTES, 'UTF-8'); ?></strong></div>
            <div><span>Calculation status</span><strong>Existing production logic</strong></div>
            <div><span>Access model</span><strong>Current ERP session</strong></div>
        </section>
    </section>
</div>

<script>
window.SAHAMID_BI_LIVE_REPORT = {
    reportId: <?php echo json_encode($definition['id']); ?>,
    title: <?php echo json_encode($definition['title']); ?>,
    sourceUrl: <?php echo json_encode($sourceUrl); ?>,
    exportUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/table_export.php'); ?>
};
</script>
<script src="<?php echo $NewRootPath; ?>v2/bi/live_report.js"></script>

<?php
include_once(dirname(__DIR__) . '/includes/footer.php');
include_once(dirname(__DIR__) . '/includes/foot.php');
