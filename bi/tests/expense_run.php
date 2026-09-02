<?php

require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Expense\ExpenseCategoryClassifier;
use SAHamid\BI\Expense\ExpenseReportRequest;

$passed = 0;
$failed = 0;

function expenseCheck($condition, $message)
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

function expectExpenseException(callable $callback, $errorCode, $message)
{
	try {
		$callback();
		expenseCheck(false, $message);
	} catch (BIException $exception) {
		expenseCheck($exception->getErrorCode() === $errorCode, $message);
	}
}

$classifier = new ExpenseCategoryClassifier();
expenseCheck($classifier->classify('Local Purchase', 'Cost of Goods Sold', 'Cost of Goods Sold') === 'Direct Costs & Materials', 'GL direct-cost group has authoritative category precedence');
expenseCheck($classifier->classify('IREM 2024 Food', 'Promotion', 'Promotions') === 'Marketing & Events', 'event food remains marketing when posted to promotions');
expenseCheck($classifier->classify('Fuel LEC-18-9180', 'Fuel', 'Operating Expenses') === 'Travel & Fleet', 'vehicle fuel is classified as travel and fleet');
expenseCheck($classifier->classify('Loan given to Employees', 'Loan to Employees', 'Current Assets') === 'Capital & Advances', 'balance-sheet advances stay separate from P&L spend');
expenseCheck($classifier->classify('', '', '') === ExpenseCategoryClassifier::UNCLASSIFIED, 'missing master data is explicitly unclassified');
expenseCheck($classifier->spendClass('Current Assets', 0) === 'Balance sheet / non-P&L', 'balance-sheet rows receive the correct spend class');

$request = ExpenseReportRequest::fromArray(array(
	'dateRange' => array('start' => '2026-08-01', 'end' => '2026-08-31'),
	'category' => 'Travel & Fleet',
	'status' => 'posted',
	'currency' => 'pkr',
	'page' => 2,
	'pageSize' => 25,
));
expenseCheck($request->getCurrency() === 'PKR', 'currency filters are normalized');
expenseCheck($request->getComparisonRange() === array('start' => '2026-07-01', 'end' => '2026-07-31'), 'comparison uses the immediately preceding equal-length period');
expenseCheck($request->getPage() === 2 && $request->getPageSize() === 25, 'transaction pagination is bounded and preserved');

$completeFilterRequest = ExpenseReportRequest::fromArray(array(
	'startDate' => '2026-08-01', 'endDate' => '2026-08-31',
	'userCode' => 'user-1', 'tabCode' => 'TAB-1', 'expenseCode' => 'FUEL',
	'glAccount' => '6100', 'accountGroup' => 'Operating Expenses', 'section' => 'Expenses',
	'spendClass' => 'P&L spend', 'receipt' => 'with_receipt', 'entryKind' => 'expense',
	'minAmount' => '100.50', 'maxAmount' => '9000', 'pageSize' => 25,
));
expenseCheck(
	$completeFilterRequest->getUserCode() === 'user-1'
		&& $completeFilterRequest->getExpenseCode() === 'FUEL'
		&& $completeFilterRequest->getSpendClass() === 'P&L spend'
		&& $completeFilterRequest->getReceipt() === 'with_receipt'
		&& $completeFilterRequest->getEntryKind() === 'expense'
		&& $completeFilterRequest->getMinAmount() === 100.5
		&& $completeFilterRequest->getMaxAmount() === 9000.0,
	'complete expense filter set is parsed and preserved'
);

$withoutLocalPurchases = ExpenseReportRequest::fromArray(array(
	'startDate' => '2026-01-01', 'endDate' => '2026-01-31', 'includeLocalPurchases' => false,
));
expenseCheck($withoutLocalPurchases->getIncludeLocalPurchases() === false, 'local-purchase exclusion toggle is preserved');
expenseCheck(ExpenseReportRequest::fromArray(array('startDate' => '2026-01-01', 'endDate' => '2026-01-31'))->getIncludeLocalPurchases() === true, 'local purchases remain included by default');

expectExpenseException(function () {
	ExpenseReportRequest::fromArray(array('startDate' => '2026-01-01', 'endDate' => '2026-01-31', 'category' => 'Made up category'));
}, 'invalid_filter', 'unknown executive categories are rejected');
expectExpenseException(function () {
	ExpenseReportRequest::fromArray(array('startDate' => '2026-01-31', 'endDate' => '2026-01-01'));
}, 'invalid_date_range', 'reversed expense date ranges are rejected');
expectExpenseException(function () {
	ExpenseReportRequest::fromArray(array('startDate' => '2026-01-01', 'endDate' => '2026-01-31', 'pageSize' => 5000));
}, 'invalid_request', 'unbounded transaction pages are rejected');
expectExpenseException(function () {
	ExpenseReportRequest::fromArray(array('startDate' => '2026-01-01', 'endDate' => '2026-01-31', 'receipt' => 'unknown'));
}, 'invalid_filter', 'unsupported evidence filters are rejected');
expectExpenseException(function () {
	ExpenseReportRequest::fromArray(array('startDate' => '2026-01-01', 'endDate' => '2026-01-31', 'minAmount' => 900, 'maxAmount' => 100));
}, 'invalid_filter', 'reversed amount filters are rejected');

echo "{$passed} passed, {$failed} failed\n";
exit($failed === 0 ? 0 : 1);
