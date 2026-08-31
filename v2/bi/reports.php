<?php

/**
 * Enhanced BI report library.
 *
 * This is an additive navigation layer over the existing report routes. It
 * gives users one searchable catalog and sends migrated reports to a BI-native
 * workspace while keeping the original report available for comparison.
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
?>

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/reports.css">

<div class="content-wrapper bi-report-library">
    <section class="content-header bi-library-header">
        <div>
            <div class="bi-library-eyebrow"><i class="fa fa-bar-chart"></i> Business Intelligence <span>/</span> Reports</div>
            <h1>Enhanced Report Library <small>One consistent starting point for every business report</small></h1>
            <p>Open every report with its working ERP filters and live results inside the BI section, with consistent search, export, print, column, and saved-view tools.</p>
        </div>
        <div class="bi-library-header-actions">
            <span class="bi-live-pill"><i class="fa fa-circle"></i> Permission-aware</span>
            <button type="button" class="btn btn-primary" id="biLibraryRefresh"><i class="fa fa-refresh"></i> Refresh catalog</button>
        </div>
    </section>

    <section class="content bi-library-content">
        <div id="biLibraryAlert" class="alert bi-library-alert" role="alert" style="display:none;"></div>

        <div class="bi-library-summary" aria-live="polite">
            <div class="bi-library-summary-card"><span>Total reports</span><strong id="biLibraryTotal">—</strong><small>Current BI catalog</small></div>
            <div class="bi-library-summary-card summary-green"><span>BI-native</span><strong id="biLibraryEnhanced">—</strong><small>Enhanced workspaces</small></div>
            <div class="bi-library-summary-card summary-amber"><span>Live source reports</span><strong id="biLibraryCompatibility">—</strong><small>Working filters and results</small></div>
            <div class="bi-library-summary-card summary-blue"><span>Company</span><strong id="biLibraryCompany">—</strong><small id="biLibraryGenerated">Loading context…</small></div>
        </div>

        <section class="bi-library-panel bi-library-controls" aria-label="Report filters">
            <div class="bi-library-control-heading">
                <div><span class="bi-library-kicker">Find a report</span><h2>Report catalog</h2></div>
                <span class="bi-library-result-count" id="biLibraryResultCount">Loading…</span>
            </div>
            <div class="row">
                <div class="col-md-6 col-sm-12">
                    <label for="biLibrarySearch">Search report names, descriptions, or source routes</label>
                    <div class="bi-library-search"><i class="fa fa-search"></i><input id="biLibrarySearch" class="form-control" type="search" placeholder="e.g. outstanding, inventory, recovery"></div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label for="biLibraryCategory">Module</label>
                    <select id="biLibraryCategory" class="form-control"><option value="">All modules</option></select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label for="biLibraryStatus">Availability</label>
                    <select id="biLibraryStatus" class="form-control">
                        <option value="">All statuses</option>
                        <option value="enhanced">BI-native</option>
                        <option value="compatibility">Live source report</option>
                    </select>
                </div>
            </div>
            <div class="bi-library-note"><i class="fa fa-info-circle"></i> Every entry is data-backed. BI-native reports use dedicated APIs; live source reports run their existing production calculation and report-specific filters inside a common BI workspace.</div>
        </section>

        <section id="biLibraryGrid" class="row bi-library-grid" aria-live="polite">
            <div class="col-md-12 bi-library-loading"><i class="fa fa-spinner fa-spin"></i> Loading the report catalog…</div>
        </section>

        <footer class="bi-library-footer"><i class="fa fa-shield"></i> The catalog uses the active ERP session and existing BI permission boundary. It does not grant access to a report or export data outside the user’s existing scope.</footer>
    </section>
</div>

<script>
    window.SAHAMID_BI_REPORT_LIBRARY = {
        catalogUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/report_catalog.php'); ?>,
        workspaceUrl: <?php echo json_encode($NewRootPath . 'v2/bi/live_report.php'); ?>,
        rootUrl: <?php echo json_encode($NewRootPath); ?>
    };
</script>
<script src="<?php echo $NewRootPath; ?>v2/bi/reports.js"></script>

<?php
include_once(dirname(__DIR__) . '/includes/footer.php');
include_once(dirname(__DIR__) . '/includes/foot.php');
