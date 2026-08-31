<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Metrics\MetricRegistry;
use SAHamid\BI\Query\CacheKey;
use SAHamid\BI\Query\QueryRequest;
use SAHamid\BI\Reports\ReportRegistry;
use SAHamid\BI\Security\AuthorizationContext;
use SAHamid\BI\Security\ReportAuthorization;

$passed = 0;
$failed = 0;

function checkCondition($condition, $message)
{
	global $passed, $failed;
	if ($condition) {
		$passed++;
		echo "PASS: {$message}\n";
	} else {
		$failed++;
		echo "FAIL: {$message}\n";
	}
}

function expectBIException(callable $callback, $errorCode, $message)
{
	try {
		$callback();
		checkCondition(false, $message);
	} catch (BIException $exception) {
		checkCondition($exception->getErrorCode() === $errorCode, $message);
	}
}

$request = QueryRequest::fromArray(array(
	'metricIds' => array('sales.invoice_value'),
	'dateRange' => array('start' => '2026-01-01', 'end' => '2026-06-30'),
	'filters' => array(array('dimension' => 'salesperson', 'operator' => 'eq', 'value' => 'SP01')),
	'limit' => 20,
));
checkCondition($request->getMetricIds() === array('sales.invoice_value'), 'query request accepts a registered-shaped metric ID');
checkCondition($request->getDateRange()['start'] === '2026-01-01', 'query request preserves the validated start date');
checkCondition($request->getFilter('salesperson')['value'] === 'SP01', 'query request normalizes the salesperson filter');

expectBIException(function () {
	QueryRequest::fromArray(array('metricIds' => array('sales.invoice_value'), 'dateRange' => array('start' => '2026-02-31', 'end' => '2026-03-01')));
}, 'invalid_date_range', 'invalid calendar dates are rejected');
expectBIException(function () {
	QueryRequest::fromArray(array('metricIds' => array('sales.invoice_value'), 'dateRange' => array('start' => '2026-02-01', 'end' => '2026-03-01'), 'limit' => 1001));
}, 'invalid_limit', 'unbounded result limits are rejected');

$registry = new MetricRegistry();
checkCondition(count($registry->all()) === 12, 'starter registry contains the twelve candidate sales metrics');
checkCondition($registry->get('sales.invoice_value')->getStatus() === 'trusted', 'invoice value is published after automated reconciliation');
checkCondition($registry->get('sales.invoice_value')->isExecutable(), 'validated invoice metric is executable');
$invoiceDefinition = $registry->get('sales.invoice_value')->toArray();
checkCondition(isset($invoiceDefinition['id'], $invoiceDefinition['status'], $invoiceDefinition['formula'], $invoiceDefinition['lineage']), 'catalog definitions expose governance and lineage fields');
checkCondition(count($invoiceDefinition['lineage']) > 0 && count($invoiceDefinition['caveats']) > 0, 'catalog definitions expose source lineage and caveats');

$reportRegistry = new ReportRegistry();
checkCondition(count($reportRegistry->all()) === 88, 'BI report library catalogs the 81 current hub entries, five sidebar-only reports, and two additional BI-native reports');
checkCondition($reportRegistry->get('menu.reorder_level_ps')->getTitle() === 'Reorder Level PS', 'duplicate source key is represented once using the PHP-rendered report entry');
checkCondition($reportRegistry->get('menu.expense_listing')->getStatus() === 'enhanced' && $reportRegistry->get('menu.expense_listing')->getBiRoute() === 'v2/bi/expenses.php', 'expense listing points to the enhanced expense workspace');
checkCondition($reportRegistry->get('bi.invoice_value')->getStatus() === 'enhanced' && $reportRegistry->get('bi.invoice_value')->getBiRoute() === 'v2/bi/invoice.php', 'invoice value points to the governed invoice workspace');
$reportRoot = dirname(dirname(__DIR__));
$missingReportRoutes = array();
foreach ($reportRegistry->all() as $registeredReport) {
	$registeredDefinition = $registeredReport->toArray();
	if (empty($registeredDefinition['legacy_route'])) {
		continue;
	}
	$routePath = parse_url($registeredDefinition['legacy_route'], PHP_URL_PATH);
	if (!$routePath || !is_file($reportRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $routePath))) {
		$missingReportRoutes[] = $registeredDefinition['id'];
	}
}
checkCondition($missingReportRoutes === array(), 'every catalog entry has an existing live source route');
checkCondition(is_file($reportRoot . '/v2/bi/live_report.php') && is_file($reportRoot . '/v2/bi/api/table_export.php'), 'live BI workspace and generic XLSX export endpoint are installed');
$allowedReports = new ReportAuthorization(array('PageSecurityArray' => array('StockMovements.php' => 21)));
$blockedContext = new AuthorizationContext('limited', 'sahamid', 'SA Hamid', 3, false, array('sales_dashboard'), array(1), 'SP01');
$allowedContext = new AuthorizationContext('manager', 'sahamid', 'SA Hamid', 3, false, array('sales_dashboard'), array(21), 'SP01');
checkCondition(!$allowedReports->isAllowed($reportRegistry->get('menu.inventory_item_movements'), $blockedContext), 'live report workspace preserves source page-security denial');
checkCondition($allowedReports->isAllowed($reportRegistry->get('menu.inventory_item_movements'), $allowedContext), 'live report workspace accepts the matching source page-security token');

$selfContext = new AuthorizationContext('alice', 'sahamid', 'SA Hamid', 3, false, array('sales_dashboard'), array(1), 'SP01');
$adminContext = new AuthorizationContext('admin', 'sahamid', 'SA Hamid', 8, true, array('*'), array(0, 1), null);
checkCondition($selfContext->canUseSalesAnalytics(), 'sales dashboard permission grants BI entry access');
checkCondition($selfContext->hasSalespersonScope() && $selfContext->getSalespersonCode() === 'SP01', 'non-admin context is salesperson scoped');
checkCondition($adminContext->isAdministrator() && !$adminContext->hasSalespersonScope(), 'administrator context is not salesperson scoped');

$requestB = QueryRequest::fromArray(array(
	'metricIds' => array('sales.invoice_value'),
	'dateRange' => array('start' => '2026-01-01', 'end' => '2026-06-30'),
	'filters' => array(array('dimension' => 'salesperson', 'operator' => 'eq', 'value' => 'SP01')),
));
$keyA = CacheKey::forRequest($request, $selfContext, array('sales.invoice_value' => 1));
$keyB = CacheKey::forRequest($requestB, $adminContext, array('sales.invoice_value' => 1));
checkCondition($keyA !== $keyB, 'cache keys isolate authorization scope and database context');

echo "{$passed} passed, {$failed} failed\n";
exit($failed === 0 ? 0 : 1);
