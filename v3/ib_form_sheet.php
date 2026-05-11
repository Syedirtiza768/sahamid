<?php
$active = 'ib_form_sheet';
include_once 'config.php';
require_once $PathPrefix . 'includes/IBFormSheet.inc';

$flashOk = '';
$flashErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ib_form'])) {
	$r = ib_form_sheet_upsert($db, $_POST, $_SESSION['UserID'] ?? '');
	if (!empty($r['ok'])) {
		$flashOk = 'Saved for that month (existing row updated if the month already existed).';
	} else {
		$flashErr = $r['error'] ?? 'Could not save.';
	}
}

$list = ib_form_sheet_list($db, 240);

include_once 'includes/header.php';
include_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
	<section class="content-header">
		<h2>IB form sheet</h2>
		<p class="text-muted">Fields match the header row from your Excel file. One saved row per calendar month.</p>
	</section>
	<section class="content">
		<?php if ($flashOk) { ?>
			<div class="alert alert-success"><?php echo htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php } ?>
		<?php if ($flashErr) { ?>
			<div class="alert alert-danger"><?php echo htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php } ?>

		<div class="row">
			<div class="col-md-10">
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title">Enter values</h3>
					</div>
					<form class="box-body" method="post" action="">
						<input type="hidden" name="save_ib_form" value="1" />
						<div class="form-group">
							<label>Month</label>
							<input type="month" name="period_month" class="form-control" required
								value="<?php echo htmlspecialchars(date('Y-m'), ENT_QUOTES, 'UTF-8'); ?>" />
						</div>
						<div class="form-group">
							<label>Total Payment (GST) at 1st of every month</label>
							<input type="number" step="0.01" name="total_payment_gst" class="form-control" value="0" />
						</div>
						<div class="form-group">
							<label>Total Payment at 1st NonGST / CASH of every month</label>
							<input type="number" step="0.01" name="total_payment_nongst_cash" class="form-control" value="0" />
						</div>
						<div class="form-group">
							<label>Total Payment at 1st (international) every month</label>
							<input type="number" step="0.01" name="total_payment_international" class="form-control" value="0" />
						</div>
						<div class="form-group">
							<label>Total Payment at 1st (Freightward) month</label>
							<input type="number" step="0.01" name="total_payment_freightward" class="form-control" value="0" />
						</div>
						<div class="form-group">
							<label>Total Advance Payment 1st of every month</label>
							<input type="number" step="0.01" name="total_advance_payment" class="form-control" value="0" />
						</div>
						<button type="submit" class="btn btn-primary">Save</button>
					</form>
				</div>

				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Saved entries</h3>
						<p class="text-muted" style="margin:0">
							Retrieve programmatically:
							<code><?php echo htmlspecialchars($NewRootPath, ENT_QUOTES, 'UTF-8'); ?>api/ib_form_sheet_data.php?format=json</code>
							— optional <code>&amp;period_month=YYYY-MM</code> for one month.
						</p>
					</div>
					<div class="box-body table-responsive">
						<table class="table table-striped table-bordered">
							<thead>
								<tr>
									<th>Month</th>
									<th>GST</th>
									<th>NonGST/Cash</th>
									<th>International</th>
									<th>Freightward</th>
									<th>Advance</th>
									<th>By</th>
									<th>Updated</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($list as $row) { ?>
									<tr>
										<td><?php echo htmlspecialchars($row['period_month'], ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars(number_format((float)$row['total_payment_gst'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars(number_format((float)$row['total_payment_nongst_cash'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars(number_format((float)$row['total_payment_international'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars(number_format((float)$row['total_payment_freightward'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars(number_format((float)$row['total_advance_payment'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars($row['entered_by'], ENT_QUOTES, 'UTF-8'); ?></td>
										<td><?php echo htmlspecialchars($row['updated_at'], ENT_QUOTES, 'UTF-8'); ?></td>
									</tr>
								<?php } ?>
								<?php if (!$list) { ?>
									<tr><td colspan="8">No rows yet. Run <code>Database/ib_form_sheet.sql</code> on your company database if the table is missing.</td></tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>
<?php include_once 'includes/footer.php'; ?>
