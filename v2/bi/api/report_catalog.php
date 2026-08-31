<?php

/**
 * Permission-aware, read-only catalog for the enhanced BI report library.
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

function biReportCatalogResponse(array $payload, $status)
{
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biReportCatalogResponse(array(
		'ok' => false,
		'error' => array('code' => 'unauthorized', 'message' => 'An authenticated ERP session is required.'),
	), 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

try {
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	if (!$context->canUseSalesAnalytics()) {
		biReportCatalogResponse(array(
			'ok' => false,
			'error' => array('code' => 'forbidden', 'message' => 'You are not authorized to use business intelligence.'),
		), 403);
	}

	$registry = new \SAHamid\BI\Reports\ReportRegistry();
	$reportAuthorization = new \SAHamid\BI\Security\ReportAuthorization($_SESSION);
	$reports = array();
	foreach ($registry->all() as $report) {
		if (!$reportAuthorization->isAllowed($report, $context)) {
			continue;
		}
		$reports[] = $report->toArray();
	}

	biReportCatalogResponse(array(
		'ok' => true,
		'data' => array(
			'reports' => $reports,
			'counts' => array(
				'total' => count($reports),
				'enhanced' => count(array_filter($reports, function ($report) { return $report['status'] === 'enhanced'; })),
				'compatibility' => count(array_filter($reports, function ($report) { return $report['status'] === 'compatibility'; })),
			),
			'context' => array(
				'company_name' => $context->getCompanyName(),
				'database_name' => $context->getDatabaseName(),
				'salesperson_scope' => $context->getSalespersonCode(),
				'administrator' => $context->isAdministrator(),
			),
			'generated_at_utc' => gmdate('Y-m-d H:i:s'),
		),
	), 200);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biReportCatalogResponse(array(
		'ok' => false,
		'error' => array(
			'code' => $exception->getErrorCode(),
			'message' => $exception->getMessage(),
			'details' => $exception->getDetails(),
		),
	), $exception->getHttpStatus());
} catch (Throwable $exception) {
	error_log('[bi] unhandled report catalog failure: ' . get_class($exception));
	biReportCatalogResponse(array(
		'ok' => false,
		'error' => array('code' => 'bi_unavailable', 'message' => 'The BI report catalog is temporarily unavailable.'),
	), 503);
}
