<?php
/**
 * JSON read/write for IB form sheet entries (same fields as Excel header row).
 * GET  ?format=json — list entries (requires logged-in session)
 * GET  ?format=json&period_month=YYYY-MM — single month
 * POST application/x-www-form-urlencoded or JSON body — upsert (requires session)
 */
header('Content-Type: application/json; charset=utf-8');

$PathPrefix = '../';
include_once $PathPrefix . 'includes/session.inc';
require_once $PathPrefix . 'includes/IBFormSheet.inc';

if (!isset($_SESSION['UserID'])) {
	http_response_code(401);
	echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
	exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST') {
	$input = $_POST;
	$ct = $_SERVER['CONTENT_TYPE'] ?? '';
	if (stripos($ct, 'application/json') !== false) {
		$raw = file_get_contents('php://input');
		$decoded = json_decode($raw, true);
		if (is_array($decoded)) {
			$input = $decoded;
		}
	}
	$r = ib_form_sheet_upsert($db, $input, $_SESSION['UserID'] ?? '');
	echo json_encode($r);
	exit;
}

$period = isset($_GET['period_month']) ? $_GET['period_month'] : null;
if ($period) {
	$row = ib_form_sheet_get_by_month($db, $period);
	if (!$row) {
		echo json_encode(['ok' => true, 'entry' => null]);
		exit;
	}
	$row['labels'] = [
		'Month' => $row['period_month'],
		'Total Payment (GST)at 1st of everymonth' => (float)$row['total_payment_gst'],
		'Total Payment at 1st NonGST/ CASH) of everymonth' => (float)$row['total_payment_nongst_cash'],
		'Total Payment at 1st (international)  everymonth' => (float)$row['total_payment_international'],
		'Total Payment at 1st (Frightward ) month' => (float)$row['total_payment_freightward'],
		'Total Advance Payment  1st o f every month' => (float)$row['total_advance_payment'],
	];
	echo json_encode(['ok' => true, 'entry' => $row]);
	exit;
}

echo json_encode([
	'ok' => true,
	'entries' => ib_form_sheet_list_json_ready($db, 500),
]);
