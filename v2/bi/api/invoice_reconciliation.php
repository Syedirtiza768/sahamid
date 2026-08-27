<?php

/**
 * Permission-aware, read-only invoice reconciliation endpoint.
 *
 * This is certification evidence for sales.invoice_value. It deliberately
 * remains available while the metric is awaiting validation and never writes
 * source data or changes the metric registry.
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

function biReconciliationResponse(array $payload, $status)
{
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biReconciliationResponse(array(
		'ok' => false,
		'error' => array('code' => 'unauthorized', 'message' => 'An authenticated ERP session is required.')
	), 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
	biReconciliationResponse(array(
		'ok' => false,
		'error' => array('code' => 'method_not_allowed', 'message' => 'Use GET or POST for read-only invoice reconciliation.')
	), 405);
}

$input = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$raw = file_get_contents('php://input');
	if ($raw !== false && trim($raw) !== '') {
		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			biReconciliationResponse(array(
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
		'limit' => 100,
	);
	if (isset($_GET['salesperson'])) {
		$input['filters'] = array(array(
			'dimension' => 'salesperson',
			'operator' => 'eq',
			'value' => $_GET['salesperson'],
		));
	}
}

try {
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	$request = \SAHamid\BI\Query\QueryRequest::fromArray($input);
	$result = (new \SAHamid\BI\Reconciliation\InvoiceReconciliationService($db))->reconcile($request, $context);
	biReconciliationResponse(array('ok' => true, 'data' => $result->toArray()), 200);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biReconciliationResponse(array(
		'ok' => false,
		'error' => array(
			'code' => $exception->getErrorCode(),
			'message' => $exception->getMessage(),
			'details' => $exception->getDetails(),
		)
	), $exception->getHttpStatus());
} catch (\Throwable $exception) {
	error_log('[bi] unhandled invoice reconciliation endpoint failure: ' . get_class($exception));
	biReconciliationResponse(array(
		'ok' => false,
		'error' => array('code' => 'bi_unavailable', 'message' => 'The invoice reconciliation service is temporarily unavailable.')
	), 503);
}
