<?php

/**
 * Export a table already rendered inside a permission-checked BI workspace.
 *
 * This endpoint never queries operational tables. The browser sends only the
 * currently visible table headers and rows from a same-origin report frame.
 */

$BiRootPath = dirname(__DIR__, 3);
$PathPrefix = $BiRootPath . DIRECTORY_SEPARATOR;
include_once($BiRootPath . '/config.php');
if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
}
session_start();
require_once($BiRootPath . '/bi/bootstrap.php');

function biTableExportFail($code, $message, $status)
{
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');
	http_response_code((int) $status);
	echo json_encode(array('ok' => false, 'error' => array('code' => $code, 'message' => $message)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	biTableExportFail('method_not_allowed', 'Use POST to export report data.', 405);
}
if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biTableExportFail('unauthorized', 'An authenticated ERP session is required.', 401);
}
if (!isset($_SERVER['HTTP_X_BI_REQUEST']) || $_SERVER['HTTP_X_BI_REQUEST'] !== '1') {
	biTableExportFail('invalid_request', 'The export request is missing its BI request marker.', 400);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

try {
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	if (!$context->canUseSalesAnalytics()) {
		biTableExportFail('forbidden', 'You are not authorized to export BI report data.', 403);
	}

	$raw = file_get_contents('php://input');
	$input = $raw === false ? null : json_decode($raw, true);
	if (!is_array($input)) {
		biTableExportFail('invalid_json', 'The export body must be a JSON object.', 400);
	}

	$title = isset($input['title']) ? trim((string) $input['title']) : 'BI report';
	if ($title === '' || strlen($title) > 200) {
		biTableExportFail('invalid_title', 'The report title is required and limited to 200 characters.', 400);
	}
	$columns = isset($input['columns']) && is_array($input['columns']) ? array_values($input['columns']) : array();
	$rows = isset($input['rows']) && is_array($input['rows']) ? array_values($input['rows']) : array();
	if (!$columns || count($columns) > 100) {
		biTableExportFail('invalid_columns', 'Exports require between 1 and 100 visible columns.', 400);
	}
	if (count($rows) > 5000 || count($rows) * count($columns) > 100000) {
		biTableExportFail('export_too_large', 'The visible export is too large. Filter the report to 5,000 rows or fewer.', 413);
	}

	$columnMap = array();
	foreach ($columns as $index => $label) {
		$label = trim(strip_tags((string) $label));
		$columnMap['column_' . $index] = $label === '' ? 'Column ' . ($index + 1) : mb_substr($label, 0, 200);
	}
	$normalizedRows = array();
	foreach ($rows as $row) {
		if (!is_array($row)) {
			continue;
		}
		$normalized = array();
		foreach ($columnMap as $key => $label) {
			$index = (int) substr($key, 7);
			$value = isset($row[$index]) ? trim(strip_tags((string) $row[$index])) : '';
			$normalized[$key] = mb_substr($value, 0, 5000);
		}
		$normalizedRows[] = $normalized;
	}

	$source = isset($input['source']) ? trim((string) $input['source']) : '';
	$parsedSource = parse_url($source);
	$source = is_array($parsedSource) && isset($parsedSource['path']) ? $parsedSource['path'] : '';
	if (strlen($source) > 500) {
		$source = substr($source, 0, 500);
	}
	$filename = preg_replace('/[^A-Za-z0-9._-]/', '-', strtolower($title)) . '-' . date('Ymd-His') . '.xlsx';
	$exporter = new \SAHamid\BI\Export\XlsxExporter();
	$exporter->download($filename, $title, array(
		'Company / database' => $context->getCompanyName() . ' / ' . $context->getDatabaseName(),
		'Exported by' => $context->getUserId(),
		'Exported at' => date('Y-m-d H:i:s T'),
		'Visible rows' => (string) count($normalizedRows),
		'Source report' => $source,
	), $columnMap, $normalizedRows);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biTableExportFail($exception->getErrorCode(), $exception->getMessage(), $exception->getHttpStatus());
} catch (\Throwable $exception) {
	error_log('[bi] table export failure: ' . get_class($exception));
	biTableExportFail('export_unavailable', 'The report export could not be generated.', 503);
}
