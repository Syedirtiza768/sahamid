<?php
$active = 'ib_form_sheet';
include_once 'config.php';
require_once $PathPrefix . 'includes/IBFormSheet.inc';

$flashOk = '';
$flashErr = '';
$flashWarn = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_excel_upload'])) {
	$autoload = $PathPrefix . 'vendor/autoload.php';
	if (!is_readable($autoload)) {
		$flashErr = 'Excel import requires Composer dependencies (vendor/). Run composer install on the server.';
	} else {
		require_once $autoload;
		require_once $PathPrefix . 'includes/IBFormSheetExcel.inc';
		if (empty($_FILES['excel_file']['tmp_name']) || (int)$_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
			$flashErr = 'Choose a valid Excel file to upload.';
		} else {
			$ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));
			if (!in_array($ext, ['xlsx', 'xls'], true)) {
				$flashErr = 'Allowed file types: .xlsx and .xls only.';
			} else {
				$r = ib_form_sheet_bulk_import_from_path($db, $_FILES['excel_file']['tmp_name'], $_SESSION['UserID'] ?? '');
				if (empty($r['ok'])) {
					$flashErr = $r['error'] ?? 'Import failed.';
					if (!empty($r['errors'])) {
						$flashErr .= ' ' . implode(' ', $r['errors']);
					}
				} else {
					if (!empty($r['errors'])) {
						$_SESSION['ib_form_bulk_warn'] = $r['errors'];
					}
					$n = (int)($r['imported'] ?? 0);
					$s = (int)($r['skipped'] ?? 0);
					header('Location: ib_form_sheet.php?msg=bulk&n=' . $n . '&s=' . $s . '&w=' . (!empty($r['errors']) ? '1' : '0'), true, 303);
					exit;
				}
			}
		}
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ib_form'])) {
	$delId = isset($_POST['delete_id']) ? (int)$_POST['delete_id'] : 0;
	$r = ib_form_sheet_delete_by_id($db, $delId);
	if (!empty($r['ok'])) {
		header('Location: ib_form_sheet.php?msg=deleted', true, 303);
		exit;
	}
	$flashErr = $r['error'] ?? 'Could not delete.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_ib_form'])) {
	$editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
	if ($editId > 0) {
		$r = ib_form_sheet_update_by_id($db, $editId, $_POST, $_SESSION['UserID'] ?? '');
		if (!empty($r['ok'])) {
			header('Location: ib_form_sheet.php?msg=updated', true, 303);
			exit;
		}
		$flashErr = $r['error'] ?? 'Could not update.';
	} else {
		$r = ib_form_sheet_upsert($db, $_POST, $_SESSION['UserID'] ?? '');
		if (!empty($r['ok'])) {
			header('Location: ib_form_sheet.php?msg=saved', true, 303);
			exit;
		}
		$flashErr = $r['error'] ?? 'Could not save.';
	}
}

if (isset($_GET['msg'])) {
	if ($_GET['msg'] === 'saved') {
		$flashOk = 'Entry saved.';
	} elseif ($_GET['msg'] === 'updated') {
		$flashOk = 'Entry updated.';
	} elseif ($_GET['msg'] === 'deleted') {
		$flashOk = 'Entry deleted.';
	} elseif ($_GET['msg'] === 'bulk') {
		$n = (int)($_GET['n'] ?? 0);
		$s = (int)($_GET['s'] ?? 0);
		$flashOk = 'Imported ' . $n . ' row(s). Skipped ' . $s . ' empty or unparseable row(s).';
		if (!empty($_GET['w']) && !empty($_SESSION['ib_form_bulk_warn'])) {
			$flashWarn = implode("\n", $_SESSION['ib_form_bulk_warn']);
			unset($_SESSION['ib_form_bulk_warn']);
		}
	}
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$prefill = null;
if ($editId > 0) {
	$prefill = ib_form_sheet_get_by_id($db, $editId);
	if (!$prefill) {
		$editId = 0;
	}
}

if (!empty($flashErr) && isset($_POST['save_ib_form'])) {
	$editId = isset($_POST['edit_id']) ? (int)$_POST['edit_id'] : 0;
	$pn = ib_form_sheet_normalize_month($_POST['period_month'] ?? '');
	$monthVal = $pn ? substr($pn, 0, 7) : date('Y-m');
	$vGst = ib_form_sheet_parse_amount($_POST['total_payment_gst'] ?? 0);
	$vNon = ib_form_sheet_parse_amount($_POST['total_payment_nongst_cash'] ?? 0);
	$vIntl = ib_form_sheet_parse_amount($_POST['total_payment_international'] ?? 0);
	$vFr = ib_form_sheet_parse_amount($_POST['total_payment_freightward'] ?? 0);
	$vAdv = ib_form_sheet_parse_amount($_POST['total_advance_payment'] ?? 0);
} else {
	$monthVal = $prefill ? substr($prefill['period_month'], 0, 7) : date('Y-m');
	$vGst = $prefill ? (float)$prefill['total_payment_gst'] : 0;
	$vNon = $prefill ? (float)$prefill['total_payment_nongst_cash'] : 0;
	$vIntl = $prefill ? (float)$prefill['total_payment_international'] : 0;
	$vFr = $prefill ? (float)$prefill['total_payment_freightward'] : 0;
	$vAdv = $prefill ? (float)$prefill['total_advance_payment'] : 0;
}

$list = ib_form_sheet_list($db, 240);

include_once 'includes/header.php';
include_once 'includes/sidebar.php';
?>
<div class="content-wrapper">
	<section class="content-header">
		<h2>SCM data</h2>
		<p class="text-muted">Fields match the header row from your Excel file. One row per calendar month; create new or edit an existing row.</p>
	</section>
	<section class="content">
		<?php if ($flashOk) { ?>
			<div class="alert alert-success"><?php echo htmlspecialchars($flashOk, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php } ?>
		<?php if ($flashErr) { ?>
			<div class="alert alert-danger"><?php echo htmlspecialchars($flashErr, ENT_QUOTES, 'UTF-8'); ?></div>
		<?php } ?>
		<?php if ($flashWarn) { ?>
			<div class="alert alert-warning"><pre style="white-space:pre-wrap;margin:0;background:transparent;border:0;padding:0;font:inherit"><?php echo htmlspecialchars($flashWarn, ENT_QUOTES, 'UTF-8'); ?></pre></div>
		<?php } ?>

		<div class="row">
			<div class="col-md-10">
				<div class="box box-primary">
					<div class="box-header with-border">
						<h3 class="box-title"><?php echo $editId ? 'Edit entry' : 'New entry'; ?></h3>
						<?php if ($editId) { ?>
							<div class="box-tools">
								<a href="<?php echo htmlspecialchars($NewRootPath . 'v3/ib_form_sheet.php', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default btn-sm">Cancel edit</a>
							</div>
						<?php } ?>
					</div>
					<form class="box-body" method="post" action="">
						<input type="hidden" name="save_ib_form" value="1" />
						<?php if ($editId) { ?>
							<input type="hidden" name="edit_id" value="<?php echo (int)$editId; ?>" />
						<?php } ?>
						<div class="form-group">
							<label>Month</label>
							<input type="month" name="period_month" class="form-control" required
								value="<?php echo htmlspecialchars($monthVal, ENT_QUOTES, 'UTF-8'); ?>" />
						</div>
						<div class="form-group">
							<label>Total Payment (GST) at 1st of every month</label>
							<input type="number" step="0.01" name="total_payment_gst" class="form-control"
								value="<?php echo htmlspecialchars((string)$vGst, ENT_QUOTES, 'UTF-8'); ?>" />
						</div>
						<div class="form-group">
							<label>Total Payment at 1st NonGST / CASH of every month</label>
							<input type="number" step="0.01" name="total_payment_nongst_cash" class="form-control"
								value="<?php echo htmlspecialchars((string)$vNon, ENT_QUOTES, 'UTF-8'); ?>" />
						</div>
						<div class="form-group">
							<label>Total Payment at 1st (international) every month</label>
							<input type="number" step="0.01" name="total_payment_international" class="form-control"
								value="<?php echo htmlspecialchars((string)$vIntl, ENT_QUOTES, 'UTF-8'); ?>" />
						</div>
						<div class="form-group">
							<label>Total Payment at 1st (Freightward) month</label>
							<input type="number" step="0.01" name="total_payment_freightward" class="form-control"
								value="<?php echo htmlspecialchars((string)$vFr, ENT_QUOTES, 'UTF-8'); ?>" />
						</div>
						<div class="form-group">
							<label>Total Advance Payment 1st of every month</label>
							<input type="number" step="0.01" name="total_advance_payment" class="form-control"
								value="<?php echo htmlspecialchars((string)$vAdv, ENT_QUOTES, 'UTF-8'); ?>" />
						</div>
						<button type="submit" class="btn btn-primary"><?php echo $editId ? 'Update' : 'Create'; ?></button>
					</form>
				</div>

				<div class="box box-default">
					<div class="box-header with-border">
						<h3 class="box-title">Bulk import from Excel</h3>
						<div class="box-tools">
							<a href="<?php echo htmlspecialchars($NewRootPath . 'v3/ib_form_sheet_template.php', ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-default btn-sm">
								Download sample template (.xlsx)
							</a>
						</div>
					</div>
					<form class="box-body" method="post" action="" enctype="multipart/form-data">
						<input type="hidden" name="bulk_excel_upload" value="1" />
						<p class="text-muted">First sheet only. Row 1: use the <strong>exact</strong> template column names (any order) or six columns <strong>A–F</strong> in template order. Row 2 onward: data; up to 2000 rows. Existing months are updated. Legacy files with keyword-style headers still work when columns are detected.</p>
						<?php if (function_exists('ib_form_sheet_import_template_headers')) { ?>
							<p class="text-muted small" style="margin-top:-6px">Template headers: <?php echo htmlspecialchars(implode(' · ', ib_form_sheet_import_template_headers()), ENT_QUOTES, 'UTF-8'); ?></p>
						<?php } ?>
						<div class="form-group">
							<label>Excel file (.xlsx or .xls)</label>
							<input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required />
						</div>
						<button type="submit" class="btn btn-default">Upload and import</button>
					</form>
				</div>

				<div class="box">
					<div class="box-header with-border">
						<h3 class="box-title">Saved entries</h3>
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
									<th class="text-right">Actions</th>
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
										<td class="text-right text-nowrap">
											<a class="btn btn-xs btn-default" href="<?php echo htmlspecialchars($NewRootPath . 'v3/ib_form_sheet.php?edit=' . (int)$row['id'], ENT_QUOTES, 'UTF-8'); ?>">Edit</a>
											<form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this entry?');">
												<input type="hidden" name="delete_ib_form" value="1" />
												<input type="hidden" name="delete_id" value="<?php echo (int)$row['id']; ?>" />
												<button type="submit" class="btn btn-xs btn-danger">Delete</button>
											</form>
										</td>
									</tr>
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
