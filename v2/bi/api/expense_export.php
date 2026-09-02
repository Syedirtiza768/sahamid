<?php

$BiRootPath = dirname(__DIR__, 3);
$PathPrefix = $BiRootPath . DIRECTORY_SEPARATOR;
include_once($BiRootPath . '/config.php');
if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
}
session_start();
require_once($BiRootPath . '/bi/bootstrap.php');

function biExpenseExportError($message, $status)
{
	http_response_code((int) $status);
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	echo json_encode(array('ok' => false, 'error' => array('message' => $message)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biExpenseExportError('An authenticated ERP session is required.', 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
	biExpenseExportError('Use GET to export this read-only report.', 405);
}

try {
	set_time_limit(180);
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	$request = \SAHamid\BI\Expense\ExpenseReportRequest::fromArray($_GET);
	$report = (new \SAHamid\BI\Expense\ExpenseReportService($db, $context))->getReport($request, true);
	$workbook = (new \SAHamid\BI\Expense\ExpenseWorkbookExporter())->build($report);
	$range = $request->getDateRange();
	$fileName = 'expense-report-' . $range['start'] . '-to-' . $range['end'] . '.xlsx';
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="' . $fileName . '"');
	header('Cache-Control: max-age=0, no-store');
	header('X-Content-Type-Options: nosniff');
	$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook);
	$writer->save('php://output');
	$workbook->disconnectWorksheets();
	exit;
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biExpenseExportError($exception->getMessage(), $exception->getHttpStatus());
} catch (\Throwable $exception) {
	error_log('[bi-expense] export failure: ' . get_class($exception) . ': ' . $exception->getMessage());
	biExpenseExportError('The Excel expense export could not be generated.', 503);
}
