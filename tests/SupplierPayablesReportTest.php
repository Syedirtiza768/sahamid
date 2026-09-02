<?php

/* Focused, database-free tests for report date/filter calculations. */
require_once __DIR__ . '/../includes/SupplierPayablesReport.inc';

function SP_ReportTestAssert($condition, $message) {
	if (!$condition) {
		fwrite(STDERR, "FAIL: " . $message . PHP_EOL);
		exit(1);
	}
}

SP_ReportTestAssert(SP_ReportAgeBucket('2026-09-02', '2026-09-02') === 'current', 'Due today is current');
SP_ReportTestAssert(SP_ReportAgeBucket('2026-09-01', '2026-09-02') === '1_30', 'One day overdue is in the 1-30 bucket');
SP_ReportTestAssert(SP_ReportAgeBucket('2026-08-02', '2026-09-02') === '31_60', 'Thirty-one days overdue is in the 31-60 bucket');
SP_ReportTestAssert(SP_ReportAgeBucket('2026-06-03', '2026-09-02') === '90_plus', 'More than ninety days overdue is in the 90+ bucket');

$filters = SP_ReportReadFilters(array(
	'as_of' => '2026-09-02',
	'invoice_from' => '2026-09-30',
	'invoice_to' => '2026-09-01',
	'payment_from' => '2026-09-28',
	'payment_to' => '2026-09-02',
	'invoice_status' => 'overdue',
	'aging_bucket' => '90_plus',
	'currency' => 'usd',
	'page' => '3',
));
SP_ReportTestAssert($filters['invoice_from'] === '2026-09-01' && $filters['invoice_to'] === '2026-09-30', 'Invoice dates are normalized into ascending order');
SP_ReportTestAssert($filters['payment_from'] === '2026-09-02' && $filters['payment_to'] === '2026-09-28', 'Payment dates are normalized into ascending order');
SP_ReportTestAssert($filters['invoice_status'] === 'overdue' && $filters['aging_bucket'] === '90_plus', 'Allowed status and aging filters are retained');
SP_ReportTestAssert($filters['currency'] === 'USD' && $filters['page'] === 3, 'Currency and page filters are normalized');

echo "SupplierPayablesReportTest: OK" . PHP_EOL;
