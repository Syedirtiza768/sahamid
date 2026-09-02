<?php

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

function biExpenseJsonResponse(array $payload, $status)
{
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biExpenseJsonResponse(array('ok' => false, 'error' => array('code' => 'unauthorized', 'message' => 'An authenticated ERP session is required.')), 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
	biExpenseJsonResponse(array('ok' => false, 'error' => array('code' => 'method_not_allowed', 'message' => 'Use GET or POST for read-only expense reports.')), 405);
}

$input = $_GET;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$raw = file_get_contents('php://input');
	$input = $raw === false || trim($raw) === '' ? array() : json_decode($raw, true);
	if (!is_array($input)) {
		biExpenseJsonResponse(array('ok' => false, 'error' => array('code' => 'invalid_json', 'message' => 'The request body must be a JSON object.')), 400);
	}
}

try {
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	$request = \SAHamid\BI\Expense\ExpenseReportRequest::fromArray($input);
	$report = (new \SAHamid\BI\Expense\ExpenseReportService($db, $context))->getReport($request);
	biExpenseJsonResponse(array('ok' => true, 'data' => $report), 200);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biExpenseJsonResponse(array('ok' => false, 'error' => array('code' => $exception->getErrorCode(), 'message' => $exception->getMessage(), 'details' => $exception->getDetails())), $exception->getHttpStatus());
} catch (\Throwable $exception) {
	error_log('[bi-expense] unhandled report failure: ' . get_class($exception) . ': ' . $exception->getMessage());
	biExpenseJsonResponse(array('ok' => false, 'error' => array('code' => 'expense_report_unavailable', 'message' => 'The expense report is temporarily unavailable.')), 503);
}
