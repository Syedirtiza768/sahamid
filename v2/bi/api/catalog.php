<?php

/**
 * Read-only catalog endpoint for the embedded BI screen.
 *
 * The catalog is deliberately served from the PHP registry rather than from
 * client-side configuration. This keeps the page aligned with the same metric
 * versions and authorization boundary used by the query endpoint.
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

function biCatalogResponse(array $payload, $status)
{
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biCatalogResponse(array(
		'ok' => false,
		'error' => array('code' => 'unauthorized', 'message' => 'An authenticated ERP session is required.')
	), 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

try {
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	if (!$context->canUseSalesAnalytics()) {
		biCatalogResponse(array(
			'ok' => false,
			'error' => array('code' => 'forbidden', 'message' => 'You are not authorized to use sales analytics.')
		), 403);
	}

	$registry = new \SAHamid\BI\Metrics\MetricRegistry();
	$metrics = array();
	foreach ($registry->all() as $metric) {
		$definition = $metric->toArray();
		$definition['executable'] = $metric->isExecutable();
		$metrics[] = $definition;
	}

	biCatalogResponse(array(
		'ok' => true,
		'data' => array(
			'metrics' => $metrics,
			'context' => array(
				'company_name' => $context->getCompanyName(),
				'database_name' => $context->getDatabaseName(),
				'salesperson_scope' => $context->getSalespersonCode(),
				'administrator' => $context->isAdministrator(),
			),
			'generated_at_utc' => gmdate('Y-m-d H:i:s'),
		)
	), 200);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biCatalogResponse(array(
		'ok' => false,
		'error' => array(
			'code' => $exception->getErrorCode(),
			'message' => $exception->getMessage(),
			'details' => $exception->getDetails(),
		)
	), $exception->getHttpStatus());
} catch (\Throwable $exception) {
	error_log('[bi] unhandled catalog endpoint failure: ' . get_class($exception));
	biCatalogResponse(array(
		'ok' => false,
		'error' => array('code' => 'bi_unavailable', 'message' => 'The BI catalog is temporarily unavailable.')
	), 503);
}
