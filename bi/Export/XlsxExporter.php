<?php

namespace SAHamid\BI\Export;

use SAHamid\BI\Exception\BIException;

/**
 * Small, reusable XLSX download writer for governed BI result sets.
 *
 * The exporter deliberately accepts already-authorized rows. It has no
 * database access and therefore cannot bypass a report's permission boundary.
 */
class XlsxExporter
{
	public function download($filename, $title, array $metadata, array $columns, array $rows)
	{
		if (!class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
			throw new BIException('export_unavailable', 'XLSX export is not available on this server.', 503);
		}

		$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->setTitle('Report');
		$columnCount = max(1, count($columns));
		$lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);

		$sheet->mergeCells('A1:' . $lastColumn . '1');
		$sheet->setCellValue('A1', $title);
		$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('FFFFFF');
		$sheet->getStyle('A1:' . $lastColumn . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('173B63');

		$rowNumber = 3;
		foreach ($metadata as $label => $value) {
			$sheet->setCellValue('A' . $rowNumber, (string) $label);
			$sheet->setCellValue('B' . $rowNumber, (string) $value);
			$sheet->getStyle('A' . $rowNumber)->getFont()->setBold(true);
			$rowNumber++;
		}

		$headerRow = $rowNumber + 1;
		$columnIndex = 1;
		foreach ($columns as $key => $label) {
			$cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex) . $headerRow;
			$sheet->setCellValue($cell, $label);
			$columnIndex++;
		}
		$sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
		$sheet->getStyle('A' . $headerRow . ':' . $lastColumn . $headerRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('2F75B5');
		$sheet->freezePane('A' . ($headerRow + 1));

		$dataRow = $headerRow + 1;
		foreach ($rows as $row) {
			$columnIndex = 1;
			foreach ($columns as $key => $label) {
				$value = isset($row[$key]) ? $row[$key] : '';
				$cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex) . $dataRow;
				if (is_int($value) || is_float($value)) {
					$sheet->setCellValue($cell, $value);
				} else {
					$sheet->setCellValueExplicit($cell, (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
				}
				$columnIndex++;
			}
			$dataRow++;
		}

		for ($i = 1; $i <= $columnCount; $i++) {
			$sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
		}
		$sheet->getStyle('A' . $headerRow . ':' . $lastColumn . max($headerRow, $dataRow - 1))->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

		$cleanFilename = preg_replace('/[^A-Za-z0-9._-]/', '-', (string) $filename);
		$cleanFilename = trim($cleanFilename, '-');
		if ($cleanFilename === '') {
			$cleanFilename = 'bi-report';
		}
		if (substr($cleanFilename, -5) !== '.xlsx') {
			$cleanFilename .= '.xlsx';
		}

		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . $cleanFilename . '"');
		header('Cache-Control: no-store');
		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
		$writer->save('php://output');
		exit;
	}
}
