<?php
/**
 * Download sample .xlsx for IB form sheet bulk import (row 1 = canonical headers).
 */
$PathPrefix = '../';
include_once __DIR__ . '/config.php';
require_once $PathPrefix . 'includes/IBFormSheet.inc';

$autoload = $PathPrefix . 'vendor/autoload.php';
if (!is_readable($autoload)) {
	header('HTTP/1.1 503 Service Unavailable');
	header('Content-Type: text/plain; charset=UTF-8');
	echo 'Excel template requires Composer dependencies (vendor/). Run composer install on the server.';
	exit;
}
require_once $autoload;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$headers = ib_form_sheet_import_template_headers();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('SCM data');

foreach ($headers as $i => $label) {
	$coord = Coordinate::stringFromColumnIndex($i + 1) . '1';
	$sheet->setCellValue($coord, $label);
}

// Example row (edit before upload): month as YYYY-MM; amounts are numeric.
$sheet->setCellValue('A2', '2026-01');
$sheet->setCellValue('B2', 0);
$sheet->setCellValue('C2', 0);
$sheet->setCellValue('D2', 0);
$sheet->setCellValue('E2', 0);
$sheet->setCellValue('F2', 0);

foreach (range(1, 6) as $col) {
	$sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
}

$filename = 'ib_form_sheet_import_template.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
