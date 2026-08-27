<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Metrics\MetricRegistry;
use SAHamid\BI\Query\CacheKey;
use SAHamid\BI\Query\QueryRequest;
use SAHamid\BI\Security\AuthorizationContext;

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
checkCondition($registry->get('sales.invoice_value')->getStatus() === 'awaiting_validation', 'invoice value remains unavailable until reconciliation');
checkCondition(!$registry->get('sales.invoice_value')->isExecutable(), 'unvalidated metrics cannot execute');
$invoiceDefinition = $registry->get('sales.invoice_value')->toArray();
checkCondition(isset($invoiceDefinition['id'], $invoiceDefinition['status'], $invoiceDefinition['formula'], $invoiceDefinition['lineage']), 'catalog definitions expose governance and lineage fields');
checkCondition(count($invoiceDefinition['lineage']) > 0 && count($invoiceDefinition['caveats']) > 0, 'catalog definitions expose source lineage and caveats');

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
