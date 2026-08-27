<?php

/**
 * Governed BI landing page.
 *
 * This is an additive screen alongside the existing sales dashboard. It uses
 * the ERP shell and session, while loading metric definitions and values only
 * through the governed BI endpoints.
 */

$active = 'bi';
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

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/bi.css">

<div class="content-wrapper bi-page">
    <section class="content-header">
        <h1>Governed Business Intelligence <small>Definitions, trust, and live ERP data</small></h1>
    </section>

    <section class="content">
        <div id="biAlert" class="alert bi-alert" role="alert" style="display:none;"></div>

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary bi-hero">
                    <div class="box-body">
                        <div class="bi-hero-icon"><i class="fa fa-line-chart"></i></div>
                        <div>
                            <h2>Trusted metrics over your current ERP company</h2>
                            <p class="text-muted">Every number is sourced from the active company database, permission-scoped, versioned, and shown with its validation state.</p>
                            <div id="biContext" class="bi-context text-muted">Loading current ERP context…</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box bi-governance-box">
                    <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-shield"></i> Governance status</h3></div>
                    <div class="box-body">
                        <div class="bi-governance-row"><span>Published metrics</span><strong id="biPublishedCount">—</strong></div>
                        <div class="bi-governance-row"><span>Awaiting validation</span><strong id="biPendingCount">—</strong></div>
                        <div class="bi-governance-row"><span>Catalog source</span><strong>Live registry</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-filter"></i> Query controls</h3>
            </div>
            <div class="box-body">
                <form id="biQueryForm" class="form-inline bi-query-form">
                    <div class="form-group">
                        <label for="biStartDate">From</label>
                        <input id="biStartDate" class="form-control" type="date" value="<?php echo htmlspecialchars($yearStart, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="biEndDate">To</label>
                        <input id="biEndDate" class="form-control" type="date" value="<?php echo htmlspecialchars($today, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="biInvoiceNo">Invoice #</label>
                        <input id="biInvoiceNo" class="form-control" type="text" inputmode="numeric" maxlength="20" placeholder="Optional evidence filter">
                    </div>
                    <button id="biRefreshCatalog" type="button" class="btn btn-default"><i class="fa fa-refresh"></i> Refresh catalog</button>
                    <span class="help-block bi-help">Run published metrics when available. Use “Reconcile” on the invoice metric to review live evidence before Finance/Sales approval; draft or awaiting-validation metrics remain visible for transparency.</span>
                </form>
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-list-alt"></i> Metric catalog</h3>
                <span class="pull-right text-muted" id="biCatalogUpdated"></span>
            </div>
            <div class="box-body">
                <div id="biMetricGrid" class="row bi-metric-grid">
                    <div class="col-md-12 bi-loading"><i class="fa fa-spinner fa-spin"></i> Loading governed metric definitions…</div>
                </div>
            </div>
        </div>

        <div class="box" id="biResultBox" style="display:none;">
            <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-bar-chart"></i> Query result</h3></div>
            <div class="box-body" id="biResultBody"></div>
        </div>
    </section>
</div>

<script>
    window.SAHAMID_BI = {
        catalogUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/catalog.php'); ?>,
        queryUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/query.php'); ?>,
        evidenceUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/invoice_drillthrough.php'); ?>,
        reconciliationUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/invoice_reconciliation.php'); ?>
    };
</script>
<script src="<?php echo $NewRootPath; ?>v2/bi/bi.js"></script>

<?php
include_once(dirname(__DIR__) . '/includes/footer.php');
include_once(dirname(__DIR__) . '/includes/foot.php');
