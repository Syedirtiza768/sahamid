<?php

/**
 * Permission-aware invoice evidence endpoint.
 *
 * This endpoint is read-only and supports the published invoice metric. It
 * must never become a second formula path.
 */

$BiRootPath = dirname(__DIR__, 3);
$PathPrefix = $BiRootPath . DIRECTORY_SEPARATOR;
include_once($BiRootPath . '/config.php');
if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
}
session_start();
require_once($BiRootPath . '/bi/bootstrap.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function biEvidenceResponse(array $payload, $status)
{
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biEvidenceResponse(array(
		'ok' => false,
		'error' => array('code' => 'unauthorized', 'message' => 'An authenticated ERP session is required.')
	), 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
	biEvidenceResponse(array(
		'ok' => false,
		'error' => array('code' => 'method_not_allowed', 'message' => 'Use GET or POST for read-only BI evidence queries.')
	), 405);
}

$input = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$raw = file_get_contents('php://input');
	if ($raw !== false && trim($raw) !== '') {
		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			biEvidenceResponse(array(
				'ok' => false,
				'error' => array('code' => 'invalid_json', 'message' => 'The request body must be a JSON object.')
			), 400);
		}
		$input = $decoded;
	}
}

if (!$input) {
	$input = array(
		'metricIds' => array('sales.invoice_value'),
		'dateRange' => array(
			'start' => isset($_GET['startDate']) ? $_GET['startDate'] : null,
			'end' => isset($_GET['endDate']) ? $_GET['endDate'] : null,
		),
		'limit' => isset($_GET['limit']) ? $_GET['limit'] : 100,
	);
	if (isset($_GET['invoiceNo'])) {
		$input['invoiceNo'] = $_GET['invoiceNo'];
	}
	if (isset($_GET['salesperson'])) {
		$input['filters'] = array(array(
			'dimension' => 'salesperson',
			'operator' => 'eq',
			'value' => $_GET['salesperson'],
		));
	}
}

$invoiceNo = isset($input['invoiceNo']) ? $input['invoiceNo'] : null;
if ($invoiceNo !== null && (string) $invoiceNo !== '' && preg_match('/^\d{1,20}$/', (string) $invoiceNo) !== 1) {
	biEvidenceResponse(array(
		'ok' => false,
		'error' => array('code' => 'invalid_invoice', 'message' => 'The invoice number must contain only digits.')
	), 400);
}

try {
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	$request = \SAHamid\BI\Query\QueryRequest::fromArray($input);
	$result = (new \SAHamid\BI\Query\QueryService($db))->executeInvoiceDrillThrough($request, $context, $invoiceNo === '' ? null : $invoiceNo);
	biEvidenceResponse(array('ok' => true, 'data' => $result->toArray()), 200);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biEvidenceResponse(array(
		'ok' => false,
		'error' => array(
			'code' => $exception->getErrorCode(),
			'message' => $exception->getMessage(),
			'details' => $exception->getDetails(),
		)
	), $exception->getHttpStatus());
} catch (\Throwable $exception) {
	error_log('[bi] unhandled invoice evidence endpoint failure: ' . get_class($exception));
	biEvidenceResponse(array(
		'ok' => false,
		'error' => array('code' => 'bi_unavailable', 'message' => 'The invoice evidence service is temporarily unavailable.')
	), 503);
}
