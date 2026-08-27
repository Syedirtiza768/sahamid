<?php

/**
 * Governed BI query endpoint.
 *
 * This endpoint is intentionally read-only and does not use v2/config.php:
 * that legacy wrapper sets AllowAnyone for its dashboard pages. The session
 * bootstrap is still reused, but authentication and BI permission are checked
 * explicitly below before any metric query is attempted.
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

function biJsonResponse(array $payload, $status)
{
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biJsonResponse(array(
		'ok' => false,
		'error' => array('code' => 'unauthorized', 'message' => 'An authenticated ERP session is required.')
	), 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
	biJsonResponse(array(
		'ok' => false,
		'error' => array('code' => 'method_not_allowed', 'message' => 'Use GET or POST for read-only BI queries.')
	), 405);
}

$input = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$raw = file_get_contents('php://input');
	if ($raw !== false && trim($raw) !== '') {
		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			biJsonResponse(array(
				'ok' => false,
				'error' => array('code' => 'invalid_json', 'message' => 'The request body must be a JSON object.')
			), 400);
		}
		$input = $decoded;
	}
}

if (!$input) {
	$input = array(
		'metricIds' => isset($_GET['metricId']) ? array($_GET['metricId']) : array(),
		'dateRange' => array(
			'start' => isset($_GET['startDate']) ? $_GET['startDate'] : null,
			'end' => isset($_GET['endDate']) ? $_GET['endDate'] : null,
		),
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
	$result = (new \SAHamid\BI\Query\QueryService($db))->execute($request, $context);
	biJsonResponse(array('ok' => true, 'data' => $result->toArray()), 200);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biJsonResponse(array(
		'ok' => false,
		'error' => array(
			'code' => $exception->getErrorCode(),
			'message' => $exception->getMessage(),
			'details' => $exception->getDetails(),
		)
	), $exception->getHttpStatus());
} catch (\Throwable $exception) {
	error_log('[bi] unhandled query endpoint failure: ' . get_class($exception));
	biJsonResponse(array(
		'ok' => false,
		'error' => array('code' => 'bi_unavailable', 'message' => 'The BI query service is temporarily unavailable.')
	), 503);
}
