<?php

$PageSecurity = 2;
include('includes/session.inc');
include('includes/SupplierPayablesReport.inc');

require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$filters = SP_ReportReadFilters($_GET);
$scope = isset($_GET['scope']) && $_GET['scope'] === 'complete' ? 'complete' : 'current';
if ($scope === 'complete') {
	$filters = SP_ReportCompleteFilters($filters);
}

$exportLimit = 1000000;
$summary = SP_ReportGetSummary($db, $filters);
$supplierRows = SP_ReportGetSupplierSummary($db, $filters, $exportLimit, 0);
$payableRows = SP_ReportRunRows($db, SP_ReportGetPayables($db, $filters, $exportLimit, 0), _('Could not retrieve payable details for export'));
$paymentRows = SP_ReportRunRows($db, SP_ReportGetPayments($db, $filters, $exportLimit, 0), _('Could not retrieve payment details for export'));
$agingRows = SP_ReportGetAging($db, $filters);

function SP_ExcelDateValue($value) {
	$value = substr((string)$value, 0, 10);
	if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) || strpos($value, '0000-') === 0) return '';
	try {
		return ExcelDate::PHPToExcel(new DateTime($value));
	} catch (Exception $e) {
		return '';
	}
}

function SP_ExcelWriteSheet($sheet, $title, $headers, $rows, $dateColumns = array(), $amountColumns = array(), $integerColumns = array()) {
	$sheet->setTitle(substr($title, 0, 31));
	$sheet->fromArray(array($headers), null, 'A1');
	$rowNumber = 2;
	foreach ($rows as $row) {
		$output = array();
		foreach ($headers as $index => $header) {
			$value = isset($row[$index]) ? $row[$index] : '';
			if (in_array($index, $dateColumns, true)) $value = SP_ExcelDateValue($value);
			$output[] = $value;
		}
		$sheet->fromArray(array($output), null, 'A' . $rowNumber);
		$rowNumber++;
	}
	$lastRow = max(1, $rowNumber - 1);
	$lastColumn = Coordinate::stringFromColumnIndex(count($headers));
	$headerStyle = $sheet->getStyle('A1:' . $lastColumn . '1');
	$headerStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
	$headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('173E5B');
	$headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
	$sheet->freezePane('A2');
	$sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
	$sheet->getStyle('A1:' . $lastColumn . $lastRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('D9E3E9');
	foreach ($amountColumns as $column) $sheet->getStyleByColumnAndRow($column + 1, 2, $column + 1, $lastRow)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
	foreach ($integerColumns as $column) $sheet->getStyleByColumnAndRow($column + 1, 2, $column + 1, $lastRow)->getNumberFormat()->setFormatCode('0');
	foreach ($dateColumns as $column) $sheet->getStyleByColumnAndRow($column + 1, 2, $column + 1, $lastRow)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
	for ($column = 1; $column <= count($headers); $column++) {
		$width = min(34, max(12, strlen((string)$headers[$column - 1]) + 2));
		$sheet->getColumnDimensionByColumn($column)->setWidth($width);
	}
}

function SP_ExcelMetricRows($summary, $currency) {
	return array(
		array('Total outstanding payables', $summary['total_outstanding'], $currency, 'Net open supplier ledger balance in reporting currency'),
		array('Total overdue payables', $summary['total_overdue'], $currency, 'Open balances with due date before the selected as-of date'),
		array('Due in next 7 days', $summary['due_7'], $currency, 'Open balances due from as-of date through 7 days after'),
		array('Due in next 30 days', $summary['due_30'], $currency, 'Open balances due from as-of date through 30 days after'),
		array('Due in next 60 days', $summary['due_60'], $currency, 'Open balances due from as-of date through 60 days after'),
		array('Due in next 90 days', $summary['due_90'], $currency, 'Open balances due from as-of date through 90 days after'),
		array('Paid during selected period', $summary['paid_period'], $currency, 'Recorded supplier payments in the payment date range'),
		array('Active suppliers', $summary['active_suppliers'], '', 'Supplier master records with suppliersince on or before as-of date'),
		array('Unpaid invoices', $summary['unpaid_invoices'], '', 'Open purchase invoice or debit note records'),
		array('On-hold invoices', $summary['on_hold_invoices'], '', 'Open supplier ledger records with hold flag set'),
		array('Average payment time (days)', $summary['average_payment_days'] === null ? '' : $summary['average_payment_days'], '', 'Weighted days from linked invoice date to payment date'),
		array('On-time payment rate (%)', $summary['on_time_rate'] === null ? '' : $summary['on_time_rate'], '%', 'Allocated invoice payments made on or before due date'),
		array('Upcoming cash requirement', $summary['upcoming_cash'], $currency, 'Open balances due within the next 30 days, excluding overdue'),
	);
}

$companyCurrency = isset($_SESSION['CompanyRecord']['currencydefault']) ? $_SESSION['CompanyRecord']['currencydefault'] : '';
$spreadsheet = new Spreadsheet();
$summarySheet = $spreadsheet->getActiveSheet();
SP_ExcelWriteSheet($summarySheet, 'Executive Summary', array('Metric', 'Value', 'Currency', 'Definition'), SP_ExcelMetricRows($summary, $companyCurrency), array(), array(1), array(7, 8, 9));

$supplierExport = array();
foreach ($supplierRows as $row) {
	$supplierExport[] = array(
		$row['supplierid'], $row['suppname'], $row['status'], $row['supplier_category'], $row['contact_email'], $row['contact_phone'], $row['payment_terms'],
		$row['currency'], (float)$row['total_invoiced'], (float)$row['total_paid'], (float)$row['outstanding'], (float)$row['overdue'], $row['next_due'], $row['last_payment'],
		(int)$row['open_invoices'], (int)$row['overdue_invoices'], (float)$row['average_days'], (float)$row['on_time_rate'], $row['project_refs'], $row['cost_centers'], (int)$row['purchase_order_count'],
	);
}
$supplierSheet = $spreadsheet->createSheet();
SP_ExcelWriteSheet($supplierSheet, 'Supplier Summary', array('Supplier ID', 'Supplier Name', 'Status', 'Category', 'Contact Email', 'Contact Phone', 'Payment Terms', 'Currency', 'Total Invoiced (Base)', 'Paid (Base)', 'Outstanding (Base)', 'Overdue (Base)', 'Next Payment Due', 'Last Payment Date', 'Open Invoices', 'Overdue Invoices', 'Average Days to Pay', 'On-time %', 'Project / Job References', 'Cost Centers / GL Tags', 'Purchase Orders'), $supplierExport, array(12, 13), array(8, 9, 10, 11, 16), array(14, 15, 20));

$payableExport = array();
foreach ($payableRows as $row) {
	$payableExport[] = array(
		$row['transno'], $row['type_name'], $row['suppreference'], $row['supplierno'], $row['suppname'], '', $row['trandate'], $row['effective_duedate'],
		(float)$row['original_amount'], (float)$row['original_amount'] - (float)$row['outstanding_amount'], (float)$row['outstanding_amount'], $row['currency'], $row['payment_status'],
		SP_ReportAgeBucket($row['effective_duedate'], $filters['as_of']), max(0, (int)(new DateTime($row['effective_duedate']))->diff(new DateTime($filters['as_of']))->format('%r%a')), $row['payment_terms'], $row['hold'] ? 'On hold' : '', $row['project_refs'], $row['cost_centers'],
	);
}
$payableSheet = $spreadsheet->createSheet();
SP_ExcelWriteSheet($payableSheet, 'Payables Detail', array('Transaction No', 'Type', 'Invoice Reference', 'Supplier ID', 'Supplier Name', 'Purchase Order', 'Invoice Date', 'Due Date', 'Original Amount', 'Paid / Allocated', 'Outstanding Amount', 'Currency', 'Payment Status', 'Aging Bucket', 'Days Overdue', 'Payment Terms', 'Hold Status', 'Project / Job References', 'Cost Centers / GL Tags'), $payableExport, array(6, 7), array(8, 9, 10), array(0, 14));

$paymentExport = array();
foreach ($paymentRows as $row) {
	$paymentExport[] = array($row['transno'], $row['supplierno'], $row['suppname'], $row['payment_date'], (float)$row['payment_amount'], (float)$row['payment_amount_base'], $row['currency'], $row['payment_method'], $row['payment_reference'], $row['bank_account'], $row['payment_status'], $row['project_refs'], $row['cost_centers']);
}
$paymentSheet = $spreadsheet->createSheet();
SP_ExcelWriteSheet($paymentSheet, 'Payment Detail', array('Payment No', 'Supplier ID', 'Supplier Name', 'Payment Date', 'Amount (Supplier Currency)', 'Amount (Base)', 'Currency', 'Payment Method', 'Payment Reference', 'Bank Account', 'Payment Status', 'Project / Job References', 'Cost Centers / GL Tags'), $paymentExport, array(3), array(4, 5), array(0));

$agingExport = array();
foreach ($agingRows as $row) $agingExport[] = array(SP_ReportAgeBucketLabel($row['aging_bucket']), $row['aging_bucket'], (float)$row['amount'], (int)$row['invoice_count'], $filters['as_of']);
$agingSheet = $spreadsheet->createSheet();
SP_ExcelWriteSheet($agingSheet, 'Aging Analysis', array('Aging Bucket', 'Code', 'Amount (Base)', 'Invoice Count', 'As-of Date'), $agingExport, array(4), array(2), array(3));

$metadataRows = array(
	array('Export scope', $scope === 'complete' ? 'Complete report' : 'Current filtered view'),
	array('Reporting / as-of date', $filters['as_of']),
	array('Invoice posted range', ($filters['invoice_from'] !== '' ? $filters['invoice_from'] : 'Any') . ' to ' . ($filters['invoice_to'] !== '' ? $filters['invoice_to'] : 'Any')),
	array('Payment date range', $filters['payment_from'] . ' to ' . $filters['payment_to']),
	array('Supplier search', $filters['supplier'] !== '' ? $filters['supplier'] : 'All'),
	array('Supplier category', $filters['supplier_type']),
	array('Invoice status', $filters['invoice_status']),
	array('Payment status', $filters['payment_status']),
	array('Due date range', ($filters['due_from'] !== '' ? $filters['due_from'] : 'Any') . ' to ' . ($filters['due_to'] !== '' ? $filters['due_to'] : 'Any')),
	array('Aging bucket', $filters['aging_bucket']),
	array('Currency', $filters['currency']),
	array('Payment method', $filters['payment_method']),
	array('Project / job reference', $filters['project'] !== '' ? $filters['project'] : 'All'),
	array('Cost center / GL tag', $filters['cost_center']),
	array('Export timestamp', date('Y-m-d H:i:s')),
	array('Source', 'Existing supplier ledger (supptrans), allocations (suppallocs), bank transactions (banktrans), supplier master, payment methods, and GL tags'),
	array('Reconciliation rule', 'Outstanding = stored supplier transaction amount including tax less recorded allocations; current balances use supptrans.alloc and historical balances use allocations dated on or before as-of date.'),
	array('Data limitations', 'The current application does not persist supplier active flags, dispute reasons, legal entities, departments, approvers, or direct invoice-to-PO references.'),
);
$metadataSheet = $spreadsheet->createSheet();
SP_ExcelWriteSheet($metadataSheet, 'Applied Filters and Metadata', array('Field', 'Value'), $metadataRows);

$filename = 'Supplier_Payables_' . date('Y-m-d_His') . '.xlsx';
while (ob_get_level() > 0) ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
