<?php
/**
 * JSON API for IB form sheet entries (session required).
 * GET  ?format=json — list all
 * GET  ?format=json&period_month=YYYY-MM — one month by period
 * GET  ?format=json&id=123 — one row by primary key
 * POST body (form or JSON) — upsert by month when `id` is absent; update by id when `id` is present
 * POST body with delete=1 (or JSON "delete": true) and id — delete row
 * DELETE ?id=123 — delete row
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

if ($method === 'DELETE') {
	$id = (int)($_GET['id'] ?? 0);
	echo json_encode(ib_form_sheet_delete_by_id($db, $id));
	exit;
}

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
	$del = !empty($input['delete']) || !empty($input['_method']) && strtoupper((string)$input['_method']) === 'DELETE';
	if ($del && !empty($input['id'])) {
		echo json_encode(ib_form_sheet_delete_by_id($db, (int)$input['id']));
		exit;
	}
	if (!empty($input['id']) && ctype_digit((string)$input['id'])) {
		echo json_encode(ib_form_sheet_update_by_id($db, (int)$input['id'], $input, $_SESSION['UserID'] ?? ''));
		exit;
	}
	echo json_encode(ib_form_sheet_upsert($db, $input, $_SESSION['UserID'] ?? ''));
	exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
	$row = ib_form_sheet_get_by_id($db, $id);
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
