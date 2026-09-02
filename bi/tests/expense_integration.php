<?php

if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

$root = dirname(__DIR__, 2);
require_once $root . '/config.php';
require_once $root . '/bi/bootstrap.php';

use SAHamid\BI\Expense\ExpenseReportRequest;
use SAHamid\BI\Expense\ExpenseReportService;
use SAHamid\BI\Expense\ExpenseWorkbookExporter;
use SAHamid\BI\Security\AuthorizationContext;

$databaseName = getenv('BI_TEST_DB_NAME') ?: 'sahamid';
$db = mysqli_connect($host, $DBUser, $DBPassword, $databaseName);
if (!$db) {
	fwrite(STDERR, "FAIL: could not connect to the BI integration database\n");
	exit(1);
}
mysqli_set_charset($db, 'utf8');

$context = new AuthorizationContext('integration-test', $databaseName, 'SA Hamid', 8, true, array('*'), array(0), null);
$request = ExpenseReportRequest::fromArray(array(
	'startDate' => getenv('BI_TEST_START') ?: '2026-01-01',
	'endDate' => getenv('BI_TEST_END') ?: date('Y-m-d'),
	'pageSize' => 10,
));

try {
	$report = (new ExpenseReportService($db, $context))->getReport($request);
	if (!isset($report['summary']['net_total'], $report['breakdowns']['categories'], $report['breakdowns']['users'], $report['breakdowns']['user_expenses'], $report['transactions']['rows'])) {
		throw new RuntimeException('report contract is incomplete');
	}
	if (!isset($report['validation']['status']) || $report['validation']['status'] !== 'passed') {
		throw new RuntimeException('report consistency validation failed');
	}
	if ($report['transactions']['total_rows'] > 0 && !$report['breakdowns']['categories']) {
		throw new RuntimeException('expense rows were not categorized');
	}
	$withoutLocalPurchases = ExpenseReportRequest::fromArray(array(
		'startDate' => getenv('BI_TEST_START') ?: '2026-01-01',
		'endDate' => getenv('BI_TEST_END') ?: date('Y-m-d'),
		'includeLocalPurchases' => false,
		'pageSize' => 10,
	));
	$filteredReport = (new ExpenseReportService($db, $context))->getReport($withoutLocalPurchases);
	if ($filteredReport['transactions']['total_rows'] > $report['transactions']['total_rows'] || $filteredReport['metadata']['include_local_purchases'] !== false || $filteredReport['validation']['status'] !== 'passed') {
		throw new RuntimeException('local-purchase exclusion did not apply');
	}
	echo 'PASS: live report returned ' . $report['transactions']['total_rows'] . " scoped transactions\n";

	$validatedFilterRequest = ExpenseReportRequest::fromArray(array(
		'startDate' => getenv('BI_TEST_START') ?: '2026-01-01',
		'endDate' => getenv('BI_TEST_END') ?: date('Y-m-d'),
		'entryKind' => 'expense',
		'receipt' => 'with_receipt',
		'minAmount' => 1,
		'maxAmount' => 1000000000,
		'pageSize' => 10,
	));
	$validatedFilterReport = (new ExpenseReportService($db, $context))->getReport($validatedFilterRequest);
	if ($validatedFilterReport['validation']['status'] !== 'passed') {
		throw new RuntimeException('extended filter consistency validation failed');
	}
	foreach ($validatedFilterReport['transactions']['rows'] as $row) {
		if ($row['entry_kind'] !== 'expense' || !$row['has_receipt'] || abs((float) $row['functional_amount']) < 1 || abs((float) $row['functional_amount']) > 1000000000) {
			throw new RuntimeException('extended filters returned an invalid transaction row');
		}
	}
	echo 'PASS: extended entry, receipt, and amount filters returned validated rows' . "\n";

	$exportReport = (new ExpenseReportService($db, $context))->getReport($request, true);
	$workbook = (new ExpenseWorkbookExporter())->build($exportReport);
	$temporaryFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bi-expense-integration.xlsx';
	$writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($workbook);
	$writer->save($temporaryFile);
	$size = filesize($temporaryFile);
	$workbook->disconnectWorksheets();
	@unlink($temporaryFile);
	if ($size === false || $size < 1000) {
		throw new RuntimeException('Excel workbook was not generated');
	}
	echo 'PASS: Excel workbook generated (' . $size . " bytes)\n";
	exit(0);
} catch (Throwable $exception) {
	fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . "\n");
	exit(1);
} finally {
	mysqli_close($db);
}
