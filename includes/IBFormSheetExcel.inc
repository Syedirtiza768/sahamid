<?php
/**
 * Bulk import IB form rows from Excel (.xlsx / .xls), first sheet.
 * Requires vendor/autoload.php (PhpSpreadsheet) before this file is included.
 */

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

if (!function_exists('ib_form_sheet_excel_normalize_header')) {
	function ib_form_sheet_excel_normalize_header($raw) {
		return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string)$raw)));
	}

	/**
	 * Map header labels to column indexes (0-based).
	 * 1) Exact match (template column names, any column order).
	 * 2) Fuzzy keyword match for legacy spreadsheets.
	 * 3) Positional A–F when row 1 has at least six columns but some labels did not match.
	 *
	 * @param array<int, string|float|null> $headerRow
	 * @return array{month:?int,gst:?int,non:?int,intl:?int,freight:?int,advance:?int}|null
	 */
	function ib_form_sheet_excel_map_columns(array $headerRow) {
		$map = [
			'month' => null,
			'gst' => null,
			'non' => null,
			'intl' => null,
			'freight' => null,
			'advance' => null,
		];
		$exactNorms = [];
		if (function_exists('ib_form_sheet_import_template_headers')) {
			$keys = ['month', 'gst', 'non', 'intl', 'freight', 'advance'];
			foreach (ib_form_sheet_import_template_headers() as $i => $label) {
				if (isset($keys[$i])) {
					$exactNorms[$keys[$i]] = ib_form_sheet_excel_normalize_header($label);
				}
			}
		}
		$freightAlias = ib_form_sheet_excel_normalize_header('Total Payment at 1st (Frightward) month');
		foreach ($headerRow as $colIndex => $raw) {
			$h = ib_form_sheet_excel_normalize_header($raw);
			if ($h === '') {
				continue;
			}
			foreach ($exactNorms as $field => $norm) {
				if ($map[$field] === null && $h === $norm) {
					$map[$field] = (int)$colIndex;
					break;
				}
			}
			if ($map['freight'] === null && $h === $freightAlias) {
				$map['freight'] = (int)$colIndex;
			}
		}
		$allMapped = true;
		foreach ($map as $v) {
			if ($v === null) {
				$allMapped = false;
				break;
			}
		}
		if ($allMapped) {
			return $map;
		}
		foreach ($headerRow as $colIndex => $raw) {
			$h = ib_form_sheet_excel_normalize_header($raw);
			if ($h === '') {
				continue;
			}
			if ($map['advance'] === null && strpos($h, 'advance') !== false) {
				$map['advance'] = (int)$colIndex;
				continue;
			}
			if ($map['intl'] === null && strpos($h, 'international') !== false) {
				$map['intl'] = (int)$colIndex;
				continue;
			}
			if ($map['freight'] === null && (strpos($h, 'fright') !== false || strpos($h, 'freight') !== false)) {
				$map['freight'] = (int)$colIndex;
				continue;
			}
			if ($map['gst'] === null && strpos($h, 'gst') !== false) {
				$map['gst'] = (int)$colIndex;
				continue;
			}
			if ($map['non'] === null && (strpos($h, 'nongst') !== false || (strpos($h, 'non') !== false && strpos($h, 'cash') !== false))) {
				$map['non'] = (int)$colIndex;
				continue;
			}
			if ($map['month'] === null && $h === 'month') {
				$map['month'] = (int)$colIndex;
				continue;
			}
			if ($map['month'] === null && strpos($h, 'month') !== false && strpos($h, 'payment') === false && strpos($h, 'advance') === false) {
				$map['month'] = (int)$colIndex;
			}
		}
		$missing = array_filter($map, static function ($v) {
			return $v === null;
		});
		if ($missing && count($headerRow) >= 6) {
			return [
				'month' => 0,
				'gst' => 1,
				'non' => 2,
				'intl' => 3,
				'freight' => 4,
				'advance' => 5,
			];
		}
		if ($missing) {
			return null;
		}
		return $map;
	}

	/**
	 * @param mixed $value Cell value (may be Excel serial date)
	 * @param mixed $formatted Formatted string when using rangeToArray(..., true, true)
	 */
	function ib_form_sheet_excel_month_to_db($value, $formatted = null) {
		if ($value === null && $formatted === null) {
			return null;
		}
		if ($value instanceof \DateTimeInterface) {
			return $value->format('Y-m-01');
		}
		if (is_numeric($value)) {
			$n = (float)$value;
			if ($n > 20000 && $n < 80000) {
				try {
					$dt = ExcelDate::excelToDateTimeObject($n);
					return $dt->format('Y-m-01');
				} catch (\Throwable $e) {
					// fall through
				}
			}
		}
		$s = trim((string)($formatted !== null && $formatted !== '' ? $formatted : $value));
		if ($s === '') {
			return null;
		}
		$norm = ib_form_sheet_normalize_month($s);
		if ($norm) {
			return $norm;
		}
		$ts = strtotime($s);
		if ($ts !== false) {
			return date('Y-m-01', $ts);
		}
		return null;
	}

	/**
	 * @return array{ok:bool,imported?:int,skipped?:int,errors?:list<string>,error?:string}
	 */
	function ib_form_sheet_bulk_import_from_path($db, $filepath, $userId = '') {
		if (!is_readable($filepath)) {
			return ['ok' => false, 'error' => 'Uploaded file could not be read.'];
		}
		try {
			$spreadsheet = IOFactory::load($filepath);
		} catch (\Throwable $e) {
			return ['ok' => false, 'error' => 'Could not open spreadsheet: ' . $e->getMessage()];
		}
		$sheet = $spreadsheet->getActiveSheet();
		$highestRow = (int)$sheet->getHighestDataRow();
		$highestCol = $sheet->getHighestDataColumn();
		if ($highestRow < 2) {
			return ['ok' => false, 'error' => 'No data rows found (need a header row and at least one data row).'];
		}
		$range = 'A1:' . $highestCol . $highestRow;
		$matrix = $sheet->rangeToArray($range, null, true, true, false);
		if (!$matrix || !isset($matrix[0])) {
			return ['ok' => false, 'error' => 'Could not read sheet range.'];
		}
		$headerRow = $matrix[0];
		$map = ib_form_sheet_excel_map_columns($headerRow);
		if ($map === null) {
			$hint = '';
			if (function_exists('ib_form_sheet_import_template_headers')) {
				$hint = ' Row 1 should use these labels (any order) or six columns A–F in this order: '
					. implode(' · ', ib_form_sheet_import_template_headers());
			}
			return ['ok' => false, 'error' => 'Could not detect required columns from the header row.' . $hint];
		}
		$imported = 0;
		$skipped = 0;
		$errors = [];
		$maxRows = 2000;
		$dataRows = count($matrix) - 1;
		$last = min($dataRows, $maxRows);

		try {
			for ($r = 1; $r <= $last; $r++) {
				if (!isset($matrix[$r])) {
					break;
				}
				$row = $matrix[$r];
				$empty = true;
				foreach ($row as $cell) {
					if ($cell !== null && trim((string)$cell) !== '') {
						$empty = false;
						break;
					}
				}
				if ($empty) {
					$skipped++;
					continue;
				}
				$rawMonth = $row[$map['month']] ?? null;
				$period = ib_form_sheet_excel_month_to_db($rawMonth, null);
				if (!$period) {
					$coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($map['month'] + 1) . ($r + 1);
					$cell = $sheet->getCell($coord);
					$period = ib_form_sheet_excel_month_to_db($cell->getCalculatedValue(), $cell->getFormattedValue());
				}
				if (!$period) {
					$skipped++;
					continue;
				}
				$data = [
					'period_month' => $period,
					'total_payment_gst' => $row[$map['gst']] ?? 0,
					'total_payment_nongst_cash' => $row[$map['non']] ?? 0,
					'total_payment_international' => $row[$map['intl']] ?? 0,
					'total_payment_freightward' => $row[$map['freight']] ?? 0,
					'total_advance_payment' => $row[$map['advance']] ?? 0,
				];
				$res = ib_form_sheet_upsert($db, $data, $userId);
				if (empty($res['ok'])) {
					$errors[] = 'Row ' . ($r + 1) . ': ' . ($res['error'] ?? 'Save failed');
					if (count($errors) >= 50) {
						$errors[] = 'Further errors omitted.';
						break;
					}
					continue;
				}
				$imported++;
			}
		} catch (\Throwable $e) {
			return ['ok' => false, 'error' => $e->getMessage()];
		}
		return [
			'ok' => true,
			'imported' => $imported,
			'skipped' => $skipped,
			'errors' => $errors,
		];
	}
}
