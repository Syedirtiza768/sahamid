<?php

namespace SAHamid\BI\Expense;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExpenseWorkbookExporter
{
	private $currency;
	private $headerFill = '173F5F';
	private $accentFill = '19A974';
	private $lightFill = 'EAF2F8';

	public function build(array $report)
	{
		$this->currency = $report['metadata']['default_currency'];
		$workbook = new Spreadsheet();
		$this->buildSummary($workbook->getActiveSheet(), $report);
		$this->buildCategories($workbook->createSheet(), $report);
		$this->buildMonthly($workbook->createSheet(), $report);
		$this->buildExpenseCodes($workbook->createSheet(), $report);
		$this->buildOwners($workbook->createSheet(), $report);
		$this->buildTransactions($workbook->createSheet(), $report);
		$workbook->setActiveSheetIndex(0);
		return $workbook;
	}

	private function buildSummary($sheet, array $report)
	{
		$summary = $report['summary'];
		$metadata = $report['metadata'];
		$filters = $metadata['filters'];
		$sheet->setTitle('Executive Summary');
		$sheet->mergeCells('A1:F1');
		$sheet->setCellValue('A1', 'Comprehensive Expense Report');
		$sheet->setCellValue('A3', 'Company');
		$this->setSafeValue($sheet, 'B3', $metadata['company_name']);
		$sheet->setCellValue('A4', 'Reporting period');
		$sheet->setCellValue('B4', $filters['date_range']['start'] . ' to ' . $filters['date_range']['end']);
		$sheet->setCellValue('D3', 'Access scope');
		$this->setSafeValue($sheet, 'E3', $metadata['access_scope']);
		$sheet->setCellValue('D4', 'Generated (UTC)');
		$this->setSafeValue($sheet, 'E4', $metadata['generated_at_utc']);

		$headers = array('Net spend', 'P&L spend', 'Capital / advances', 'Action required', 'Transactions', 'Receipt coverage');
		$values = array($summary['net_total'], $summary['pnl_total'], $summary['balance_sheet_total'], $summary['action_required_total'], $summary['transaction_count'], $summary['receipt_coverage_percent'] / 100);
		foreach ($headers as $index => $header) {
			$column = chr(65 + $index);
			$sheet->setCellValue($column . '6', $header);
			$sheet->setCellValue($column . '7', $values[$index]);
		}
		$sheet->getStyle('A7:D7')->getNumberFormat()->setFormatCode($this->amountFormat());
		$sheet->getStyle('F7')->getNumberFormat()->setFormatCode('0.0%');

		$sheet->setCellValue('A9', 'Decision-ready insights');
		$row = 10;
		foreach ($report['insights'] as $insight) {
			$this->setSafeValue($sheet, 'A' . $row, $insight['title']);
			$sheet->mergeCells('B' . $row . ':F' . $row);
			$this->setSafeValue($sheet, 'B' . $row, $insight['detail']);
			$row++;
		}

		$row += 1;
		$sheet->setCellValue('A' . $row, 'Data quality & accounting treatment');
		$qualityRow = $row + 1;
		$quality = array(
			array('Missing receipt', $summary['missing_receipt_count']),
			array('Unclassified / unmapped', $summary['unclassified_count']),
			array('Foreign-currency rows', $summary['foreign_currency_count']),
			array('Missing exchange rate', $summary['missing_rate_count']),
			array('Credits / corrections', $summary['credit_count']),
		);
		foreach ($quality as $item) {
			$sheet->setCellValue('A' . $qualityRow, $item[0]);
			$sheet->setCellValue('B' . $qualityRow, $item[1]);
			$qualityRow++;
		}
		$sheet->mergeCells('D' . ($row + 1) . ':F' . ($row + 3));
		$this->setSafeValue($sheet, 'D' . ($row + 1), $metadata['amount_definition'] . "\n" . $metadata['currency_method']);
		$sheet->getStyle('D' . ($row + 1))->getAlignment()->setWrapText(true)->setVertical('top');

		$this->styleTitle($sheet, 'A1:F1');
		$this->styleHeader($sheet, 'A6:F6');
		$this->styleSection($sheet, 'A9:F9');
		$this->styleSection($sheet, 'A' . $row . ':F' . $row);
		$sheet->getColumnDimension('A')->setWidth(26);
		$sheet->getColumnDimension('B')->setWidth(28);
		$sheet->getColumnDimension('C')->setWidth(22);
		$sheet->getColumnDimension('D')->setWidth(28);
		$sheet->getColumnDimension('E')->setWidth(28);
		$sheet->getColumnDimension('F')->setWidth(22);
		$sheet->freezePane('A6');
	}

	private function buildCategories($sheet, array $report)
	{
		$sheet->setTitle('Category Analysis');
		$headers = array('Executive category', 'Net spend', 'Share', 'Transactions', 'Gross outflow', 'Credits', 'Previous period', 'Change', 'Change %', 'Posted', 'Pending authorization', 'Authorized not posted', 'Expense codes');
		$this->writeTable($sheet, $headers, $report['breakdowns']['categories'], function ($row) {
			return array($row['category'], $row['total'], $row['share_percent'] / 100, $row['transaction_count'], $row['gross_outflow'], $row['credits'], $row['previous_total'], $row['change_amount'], $row['change_percent'] === null ? null : $row['change_percent'] / 100, $row['posted_total'], $row['pending_total'], $row['authorized_unposted_total'], $row['expense_code_count']);
		});
		$last = count($report['breakdowns']['categories']) + 1;
		$sheet->getStyle('B2:B' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$sheet->getStyle('C2:C' . $last)->getNumberFormat()->setFormatCode('0.0%');
		$sheet->getStyle('E2:H' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$sheet->getStyle('I2:I' . $last)->getNumberFormat()->setFormatCode('0.0%');
		$sheet->getStyle('J2:L' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$this->setWidths($sheet, array('A' => 32, 'B' => 18, 'C' => 12, 'D' => 14, 'E' => 18, 'F' => 16, 'G' => 18, 'H' => 18, 'I' => 12, 'J' => 18, 'K' => 20, 'L' => 22, 'M' => 14));
	}

	private function buildMonthly($sheet, array $report)
	{
		$sheet->setTitle('Monthly Trend');
		$headers = array('Month', 'Net spend', 'Gross outflow', 'Credits', 'Transactions');
		$this->writeTable($sheet, $headers, $report['breakdowns']['monthly'], function ($row) {
			return array($row['period'], $row['total'], $row['gross_outflow'], $row['credits'], $row['transaction_count']);
		});
		$last = count($report['breakdowns']['monthly']) + 1;
		$sheet->getStyle('B2:D' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$this->setWidths($sheet, array('A' => 14, 'B' => 20, 'C' => 20, 'D' => 18, 'E' => 16));
	}

	private function buildExpenseCodes($sheet, array $report)
	{
		$sheet->setTitle('Expense Code Detail');
		$headers = array('Executive category', 'Spend class', 'Expense code', 'Expense description', 'GL account', 'GL account name', 'GL group', 'Tag', 'Net spend', 'Share', 'Transactions', 'Previous period', 'Change', 'Change %', 'Posted', 'Pending authorization', 'Authorized not posted');
		$this->writeTable($sheet, $headers, $report['breakdowns']['expense_codes'], function ($row) {
			return array($row['category'], $row['spend_class'], $row['codeexpense'], $row['description'], $row['glaccount'], $row['accountname'], $row['account_group'], $row['tagdescription'], $row['total'], $row['share_percent'] / 100, $row['transaction_count'], $row['previous_total'], $row['change_amount'], $row['change_percent'] === null ? null : $row['change_percent'] / 100, $row['posted_total'], $row['pending_total'], $row['authorized_unposted_total']);
		});
		$last = count($report['breakdowns']['expense_codes']) + 1;
		$sheet->getStyle('I2:I' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$sheet->getStyle('J2:J' . $last)->getNumberFormat()->setFormatCode('0.0%');
		$sheet->getStyle('L2:M' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$sheet->getStyle('N2:N' . $last)->getNumberFormat()->setFormatCode('0.0%');
		$sheet->getStyle('O2:Q' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$this->setWidths($sheet, array('A' => 30, 'B' => 23, 'C' => 14, 'D' => 34, 'E' => 14, 'F' => 30, 'G' => 24, 'H' => 28, 'I' => 18, 'J' => 11, 'K' => 14, 'L' => 18, 'M' => 18, 'N' => 11, 'O' => 18, 'P' => 20, 'Q' => 22));
	}

	private function buildOwners($sheet, array $report)
	{
		$sheet->setTitle('Cost Owners');
		$headers = array('Cost owner', 'User', 'Expense tab', 'Cost centre', 'Net spend', 'Share', 'Transactions');
		$this->writeTable($sheet, $headers, $report['breakdowns']['owners'], function ($row) {
			return array($row['owner'], $row['usercode'], $row['tabcode'], $row['cost_center'], $row['total'], $row['share_percent'] / 100, $row['transaction_count']);
		});
		$last = count($report['breakdowns']['owners']) + 1;
		$sheet->getStyle('E2:E' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$sheet->getStyle('F2:F' . $last)->getNumberFormat()->setFormatCode('0.0%');
		$this->setWidths($sheet, array('A' => 26, 'B' => 20, 'C' => 28, 'D' => 30, 'E' => 18, 'F' => 11, 'G' => 14));
	}

	private function buildTransactions($sheet, array $report)
	{
		$sheet->setTitle('Transactions');
		$headers = array('ID', 'Date', 'Executive category', 'Spend class', 'Expense code', 'Description', 'GL account', 'GL account name', 'GL group', 'Cost centre', 'Expense tab', 'Cost owner', 'Workflow status', 'Entry kind', 'Original currency', 'Original amount', 'Current rate', 'Functional amount', 'Notes', 'Receipt available', 'Receipt reference', 'Receipt image');
		$this->writeTable($sheet, $headers, $report['transactions']['rows'], function ($row) {
			return array($row['counterindex'], $row['date'], $row['category'], $row['spend_class'], $row['codeexpense'], $row['description'], $row['glaccount'], $row['accountname'], $row['account_group'], $row['cost_center'], $row['tabcode'], $row['owner'], str_replace('_', ' ', $row['workflow_status']), $row['entry_kind'], $row['currency'], $row['original_amount'], $row['current_rate'], $row['functional_amount'], $row['notes'], $row['has_receipt'] ? 'Yes' : 'No', $row['receipt_reference'], $row['receipt_image']);
		});
		$last = count($report['transactions']['rows']) + 1;
		$sheet->getStyle('P2:P' . $last)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
		$sheet->getStyle('Q2:Q' . $last)->getNumberFormat()->setFormatCode('0.000000');
		$sheet->getStyle('R2:R' . $last)->getNumberFormat()->setFormatCode($this->amountFormat());
		$this->setWidths($sheet, array('A' => 11, 'B' => 13, 'C' => 29, 'D' => 23, 'E' => 14, 'F' => 34, 'G' => 14, 'H' => 30, 'I' => 24, 'J' => 30, 'K' => 28, 'L' => 24, 'M' => 23, 'N' => 13, 'O' => 13, 'P' => 17, 'Q' => 14, 'R' => 19, 'S' => 45, 'T' => 16, 'U' => 24, 'V' => 24));
		$sheet->getStyle('S2:S' . $last)->getAlignment()->setWrapText(true)->setVertical('top');
	}

	private function writeTable($sheet, array $headers, array $rows, callable $transform)
	{
		$this->writeSafeRow($sheet, 1, $headers);
		$rowNumber = 2;
		foreach ($rows as $row) {
			$this->writeSafeRow($sheet, $rowNumber, $transform($row));
			$rowNumber++;
		}
		$lastColumn = $sheet->getHighestColumn();
		$lastRow = max(1, $rowNumber - 1);
		$this->styleHeader($sheet, 'A1:' . $lastColumn . '1');
		$sheet->setAutoFilter('A1:' . $lastColumn . $lastRow);
		$sheet->freezePane('A2');
		$sheet->getStyle('A1:' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_HAIR)->getColor()->setARGB('D8E2EA');
	}

	private function writeSafeRow($sheet, $rowNumber, array $values)
	{
		foreach (array_values($values) as $index => $value) {
			$coordinate = Coordinate::stringFromColumnIndex($index + 1) . $rowNumber;
			if (is_string($value)) {
				$this->setSafeValue($sheet, $coordinate, $value);
			} else {
				$sheet->setCellValue($coordinate, $value);
			}
		}
	}

	private function setSafeValue($sheet, $coordinate, $value)
	{
		// Expense descriptions, notes, owners, and receipt references are user
		// controlled. Explicit string cells prevent spreadsheet-formula injection.
		$sheet->setCellValueExplicit($coordinate, (string) $value, DataType::TYPE_STRING);
	}

	private function styleTitle($sheet, $range)
	{
		$sheet->getStyle($range)->getFont()->setBold(true)->setSize(18)->getColor()->setARGB('FFFFFF');
		$sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->headerFill);
		$sheet->getStyle($range)->getAlignment()->setVertical('center');
		$sheet->getRowDimension(1)->setRowHeight(30);
	}

	private function styleHeader($sheet, $range)
	{
		$sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
		$sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->headerFill);
		$sheet->getStyle($range)->getAlignment()->setWrapText(true)->setVertical('center');
	}

	private function styleSection($sheet, $range)
	{
		$sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
		$sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->accentFill);
	}

	private function setWidths($sheet, array $widths)
	{
		foreach ($widths as $column => $width) {
			$sheet->getColumnDimension($column)->setWidth($width);
		}
	}

	private function amountFormat()
	{
		return '"' . $this->currency . '" #,##0.00;[Red]-"' . $this->currency . '" #,##0.00';
	}
}
