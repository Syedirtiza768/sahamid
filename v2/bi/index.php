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

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/bi/expense-report.css?v=20260902-1">

<div class="content-wrapper">
    <section class="content">
        <?php include(__DIR__ . '/expense-report-view.php'); ?>
    </section>
</div>

<script>
    window.SAHAMID_BI = {
        expenseReportUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/expense_report.php'); ?>,
        expenseExportUrl: <?php echo json_encode($NewRootPath . 'v2/bi/api/expense_export.php'); ?>
    };
</script>
<script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/chart.js/Chart.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/bi/expense-report.js?v=20260902-1"></script>

<?php
include_once(dirname(__DIR__) . '/includes/footer.php');
include_once(dirname(__DIR__) . '/includes/foot.php');
