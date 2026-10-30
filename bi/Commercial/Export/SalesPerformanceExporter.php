<?php

namespace SAHamid\BI\Commercial\Export;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use SAHamid\BI\Commercial\DecimalAmount;

/** Builds report exports exclusively from a saved run payload and its saved facts. */
class SalesPerformanceExporter
{
	private $navy = '173F5F';
	private $green = '1F8A70';
	private $light = 'EAF2F8';

	public function render($format, array $report, array $facts)
	{
		if ($format === 'csv') return $this->csv($report, $facts);
		if ($format === 'xlsx') return $this->xlsx($report, $facts);
		if ($format === 'pdf') return $this->pdf($report, $facts);
		throw new \InvalidArgumentException('Choose CSV, XLSX or PDF.');
	}

	private function csv(array $report, array $facts)
	{
		$stream = fopen('php://temp', 'w+b');
		fwrite($stream, "\xEF\xBB\xBF");
		$meta = isset($report['meta']) ? $report['meta'] : array();
		$this->csvRow($stream, array('Sales Performance Report'));
		$this->csvRow($stream, array('Period', isset($meta['from']) ? $meta['from'] : '', isset($meta['through']) ? $meta['through'] : ''));
		$this->csvRow($stream, array('Business cutoff', isset($meta['business_cutoff']) ? $meta['business_cutoff'] : ''));
		$this->csvRow($stream, array('Scope', $this->scopeLabel($meta)));
		$this->csvRow($stream, array('Definition version', isset($meta['definition_version']) ? $meta['definition_version'] : ''));
		$this->csvRow($stream, array('Validation status', isset($meta['validation_status']) ? $meta['validation_status'] : ''));
		$this->csvRow($stream, array('Frozen run', isset($report['frozen_run']['run_key']) ? $report['frozen_run']['run_key'] : ''));
		$this->csvRow($stream, array('Content hash', isset($report['frozen_run']['content_hash']) ? $report['frozen_run']['content_hash'] : ''));
		$this->csvRow($stream, array('Content integrity', isset($report['frozen_run']['integrity_status']) ? $report['frozen_run']['integrity_status'] : 'unverified'));
		$this->csvRow($stream, array());

		$this->csvRow($stream, array('Period comparison'));
		$this->csvRow($stream, array('Metric', 'Period', 'Count', 'Raw value PKR', 'Net value PKR', 'Normalized value PKR', 'Value status', 'Raw unknown count', 'Net unknown count', 'Normalized unknown count'));
		foreach (array('current' => 'Current', 'comparison' => 'Comparison') as $periodKey => $periodLabel) {
			$period = isset($report[$periodKey]) ? $report[$periodKey] : array();
			foreach ($this->metricKeys() as $key => $label) {
				$m = isset($period['metrics'][$key]) ? $period['metrics'][$key] : array();
				$this->csvRow($stream, array($label, $periodLabel, $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, 'net_value'), $this->value($m, 'normalized_value'), $this->value($m, 'value_status'), $this->value($m, 'raw_unknown_count'), $this->value($m, 'net_unknown_count'), $this->value($m, 'normalized_unknown_count')));
			}
		}

		$this->csvRow($stream, array());
		$this->csvRow($stream, array('Daily activity'));
		$this->csvRow($stream, array('Date', 'Week starting', 'Coverage', 'Metric', 'Count', 'Raw value PKR', 'Net value PKR', 'Normalized value PKR', 'Value status', 'Unknown normalized values'));
		foreach ($this->arrayValue($report, array('current', 'daily')) as $day) {
			foreach ($this->metricKeys() as $key => $label) {
				$m = isset($day['metrics'][$key]) ? $day['metrics'][$key] : array();
				$this->csvRow($stream, array($day['date'], $day['week_start'], $day['coverage_status'], $label, $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, 'net_value'), $this->value($m, 'normalized_value'), $this->value($m, 'value_status'), $this->value($m, 'normalized_unknown_count')));
			}
		}

		$this->csvRow($stream, array());
		$this->csvRow($stream, array('Weekly activity'));
		$this->csvRow($stream, array('Week starting', 'Reporting coverage', 'Metric', 'Count', 'Raw value PKR', 'Net value PKR', 'Normalized value PKR', 'Value status', 'Unknown normalized values'));
		$currentPeriod = $this->arrayValue($report, array('current'));
		$currentInsights = $this->periodInsights($currentPeriod);
		foreach ($this->arrayValue($currentPeriod, array('weekly')) as $week) {
			foreach ($this->metricKeys() as $key => $label) {
				$m = isset($week['metrics'][$key]) ? $week['metrics'][$key] : array();
				$this->csvRow($stream, array($week['week_start'], $this->weekCoverageText($currentInsights, $week['week_start']), $label, $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, 'net_value'), $this->value($m, 'normalized_value'), $this->value($m, 'value_status'), $this->value($m, 'normalized_unknown_count')));
			}
		}
		$cumulative = $this->cumulativeRows($report);
		$this->csvRow($stream, array());
		$this->csvRow($stream, array('Cumulative reported activity'));
		$this->csvRow($stream, $cumulative['headers']);
		foreach ($cumulative['rows'] as $row) $this->csvRow($stream, $row);
		$insights = $this->insights($report);
		$this->csvRow($stream, array());
		$this->csvRow($stream, array('Weekly statistical baseline'));
		$this->csvRow($stream, array('Baseline rule', isset($insights['baseline_rule']) ? $insights['baseline_rule'] : ''));
		$this->csvRow($stream, array('Measure', 'Weeks', 'Mean', 'Median', 'Population standard deviation', 'Latest complete week', 'Latest value', 'Z-score', 'Position'));
		foreach ($this->arrayValue($insights, array('weekly_statistics')) as $stat) {
			$this->csvRow($stream, array($this->value($stat, 'label'), $this->value($stat, 'observations'), $this->value($stat, 'mean'), $this->value($stat, 'median'), $this->value($stat, 'population_sd'), $this->value($stat, 'latest_week_start'), $this->value($stat, 'latest'), $this->value($stat, 'z_score'), $this->value($stat, 'position')));
		}
		$this->csvRow($stream, array());
		$this->csvRow($stream, array('Weekday statistical baseline'));
		$weekStarts = $this->arrayValue($insights, array('complete_week_starts'));
		$this->csvRow($stream, array_merge(array('Measure', 'Weekday'), $weekStarts, array('Weeks', 'Mean', 'Median', 'Population standard deviation', 'Latest value', 'Z-score', 'Position')));
		foreach ($this->arrayValue($insights, array('weekday_statistics')) as $metricRows) {
			foreach ($metricRows as $stat) {
				$row = array($this->value($stat, 'label'), $this->value($stat, 'weekday_label'));
				foreach ($weekStarts as $weekStart) $row[] = isset($stat['values'][$weekStart]) ? $stat['values'][$weekStart] : null;
				$this->csvRow($stream, array_merge($row, array($this->value($stat, 'observations'), $this->value($stat, 'mean'), $this->value($stat, 'median'), $this->value($stat, 'population_sd'), $this->value($stat, 'latest'), $this->value($stat, 'z_score'), $this->value($stat, 'position'))));
			}
		}

		$this->csvRow($stream, array());
		$this->csvRow($stream, array('Team contribution'));
		$this->csvRow($stream, array('Team', 'Metric', 'Count', 'Raw value PKR', 'Net value PKR', 'Normalized value PKR', 'Value status', 'Unknown normalized values'));
		foreach ($this->arrayValue($report, array('current', 'teams')) as $team) {
			foreach ($this->metricKeys() as $key => $label) {
				$m = isset($team['metrics'][$key]) ? $team['metrics'][$key] : array();
				$this->csvRow($stream, array($team['name'], $label, $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, 'net_value'), $this->value($m, 'normalized_value'), $this->value($m, 'value_status'), $this->value($m, 'normalized_unknown_count')));
			}
		}

		$this->csvRow($stream, array());
		$this->csvRow($stream, array('Frozen supporting records'));
		$this->csvRow($stream, array('Period role', 'Business at', 'Metric family', 'Source type', 'Source key', 'Case', 'Customer', 'Branch', 'Actor user', 'Entered by', 'Credited owner', 'Credited owner code', 'Credited team ID', 'Attribution basis', 'Attribution version', 'Raw source amount', 'Source currency', 'Raw PKR', 'Net amount', 'Normalized amount', 'Reporting currency', 'Value status', 'Coverage status', 'Eligibility status', 'Date basis', 'Tax basis', 'Warnings', 'Source version', 'Calculation version', 'Normalization references', 'Source evidence summary'));
		foreach ($facts as $fact) {
			$this->csvRow($stream, array($this->value($fact, 'period_role') === 'comparison' ? 'Comparison' : 'Current', $this->value($fact, 'business_at'), $this->value($fact, 'metric_family'), $this->value($fact, 'source_type'), $this->value($fact, 'source_key'),
				$this->value($fact, 'salescaseref'), $this->value($fact, 'customer'), $this->value($fact, 'branch'), $this->value($fact, 'actor_user_id'),
				$this->value($fact, 'entered_by_user_id'), $this->value($fact, 'credited_salesperson'), $this->value($fact, 'credited_salesman_code'),
				$this->value($fact, 'credited_team'), $this->value($fact, 'attribution_basis'),
				$this->value($fact, 'attribution_version'), $this->value($fact, 'raw_amount'), $this->value($fact, 'currency'),
				$this->value($fact, 'reporting_raw_amount'), $this->value($fact, 'net_amount'), $this->value($fact, 'normalized_amount'),
				$this->value($fact, 'reporting_currency'), $this->value($fact, 'value_status'), $this->value($fact, 'coverage_status'),
				$this->value($fact, 'eligibility_status'), $this->value($fact, 'date_basis'), $this->value($fact, 'tax_basis'),
				$this->value($fact, 'warnings_text'), $this->value($fact, 'source_version'), $this->value($fact, 'calculation_version'),
				$this->value($fact, 'normalization_references'), $this->value($fact, 'source_evidence')));
		}

		$this->csvRow($stream, array());
		$this->csvRow($stream, array('Limitations'));
		foreach ($this->limitations($report) as $note) $this->csvRow($stream, array($note));
		rewind($stream);
		$result = stream_get_contents($stream);
		fclose($stream);
		return $result;
	}

	private function csvRow($stream, array $values)
	{
		$safe = array();
		foreach ($values as $value) {
			$text = $value === null ? '' : (is_scalar($value) ? (string) $value : '');
			if ($text !== '' && preg_match('/^\s*[=+@]/', $text)) $text = "'" . $text;
			if ($text !== '' && preg_match('/^\s*-/', $text) && !preg_match('/^\s*-?\d+(?:\.\d+)?\s*$/', $text)) $text = "'" . $text;
			$safe[] = $text;
		}
		fputcsv($stream, $safe, ',', '"');
	}

	private function xlsx(array $report, array $facts)
	{
		$book = new Spreadsheet();
		$this->buildCoverageSheet($book->getActiveSheet(), $report);
		$this->buildDailySheet($book->createSheet(), $report);
		$this->buildWeeklySheet($book->createSheet(), $report);
		$this->buildCumulativeSheet($book->createSheet(), $report);
		$this->buildInsightsSheet($book->createSheet(), $report);
		$this->buildTeamSheet($book->createSheet(), $report);
		$this->buildMonthSheet($book->createSheet(), $report);
		$this->buildAdjustmentSheet($book->createSheet(), $report);
		$this->buildReconciliationSheet($book->createSheet());
		$this->buildFactsSheet($book->createSheet(), $facts);
		$book->setActiveSheetIndex(0);
		$writer = new Xlsx($book);
		ob_start();
		$writer->save('php://output');
		$binary = ob_get_clean();
		$book->disconnectWorksheets();
		return $binary;
	}

	private function buildCoverageSheet($sheet, array $report)
	{
		$meta = isset($report['meta']) ? $report['meta'] : array();
		$sheet->setTitle('Definitions Coverage');
		$rows = array(
			array('S.A. Hamid - Sales Performance'),
			array('Company scope', $this->scopeLabel($meta)),
			array('Current period', $this->periodLabel(isset($meta['from']) ? $meta['from'] : '', isset($meta['through']) ? $meta['through'] : '')),
			array('Business cutoff', isset($meta['business_cutoff']) ? $meta['business_cutoff'] : ''),
			array('Comparison period', $this->periodLabel(isset($meta['compare_from']) ? $meta['compare_from'] : '', isset($meta['compare_to']) ? $meta['compare_to'] : '')),
			array('Reporting timezone', isset($meta['timezone']) ? $meta['timezone'] : 'Asia/Karachi'),
			array('Currency and tax basis', 'Amounts are PKR where known; net excludes GST under the provisional source policy. Unknown classifications stay blank and flagged.'),
			array('Definition version', isset($meta['definition_version']) ? $meta['definition_version'] : ''),
			array('Validation status', isset($meta['validation_status']) ? $meta['validation_status'] : 'provisional'),
			array('Generated at', isset($meta['generated_at']) ? $meta['generated_at'] : ''),
			array('Frozen run key', isset($report['frozen_run']['run_key']) ? $report['frozen_run']['run_key'] : ''),
			array('Content hash', isset($report['frozen_run']['content_hash']) ? $report['frozen_run']['content_hash'] : ''),
			array('Content integrity', isset($report['frozen_run']['integrity_status']) ? $report['frozen_run']['integrity_status'] : 'unverified'),
			array('Current captured reporting days', isset($report['current']['reporting_days']) ? (int) $report['current']['reporting_days'] : 0),
			array('Comparison captured reporting days', isset($report['comparison']['reporting_days']) ? (int) $report['comparison']['reporting_days'] : 0)
		);
		$rowNo = 1;
		foreach ($rows as $row) {
			$this->setText($sheet, 'A' . $rowNo, $row[0]);
			if (isset($row[1])) $this->setText($sheet, 'B' . $rowNo, $row[1]);
			$rowNo++;
		}
		$sheet->mergeCells('A1:B1');
		$this->title($sheet, 'A1:B1');
		$sheet->setCellValue('A16', 'Assumptions and known limitations');
		$this->section($sheet, 'A16:B16');
		$rowNo = 17;
		foreach ($this->limitations($report) as $note) {
			$this->setText($sheet, 'A' . $rowNo, '• ' . $note);
			$sheet->mergeCells('A' . $rowNo . ':B' . $rowNo);
			$rowNo++;
		}
		$sheet->setCellValue('A' . ($rowNo + 1), 'Daily coverage');
		$this->section($sheet, 'A' . ($rowNo + 1) . ':B' . ($rowNo + 1));
		$this->table($sheet, array('Date', 'Coverage status'), $this->coverageRows($report), $rowNo + 2);
		$sheet->getColumnDimension('A')->setWidth(38);
		$sheet->getColumnDimension('B')->setWidth(88);
		$sheet->getStyle('B1:B' . max(14, $rowNo))->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
		$sheet->freezePane('A2');
	}

	private function buildDailySheet($sheet, array $report)
	{
		$sheet->setTitle('Daily');
		$headers = array('Date', 'Week starting', 'Coverage', 'Metric', 'Count', 'Raw amount PKR', 'Net amount PKR', 'Normalized amount PKR', 'Value status', 'Raw unknown count', 'Net unknown count', 'Normalized unknown count');
		$rows = array();
		foreach ($this->arrayValue($report, array('current', 'daily')) as $day) {
			foreach ($this->metricKeys() as $key => $label) {
				$m = isset($day['metrics'][$key]) ? $day['metrics'][$key] : array();
				$rows[] = array($day['date'], $day['week_start'], $day['coverage_status'], $label, $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, 'net_value'), $this->value($m, 'normalized_value'), $this->value($m, 'value_status'), $this->value($m, 'raw_unknown_count'), $this->value($m, 'net_unknown_count'), $this->value($m, 'normalized_unknown_count'));
			}
		}
		$this->table($sheet, $headers, $rows);
		$this->widths($sheet, array(13, 13, 23, 24, 12, 19, 19, 23, 16, 15, 15, 22));
	}

	private function buildWeeklySheet($sheet, array $report)
	{
		$sheet->setTitle('Weekly');
		$headers = array('Week starting', 'Period', 'Reporting coverage', 'Metric', 'Count', 'Raw amount PKR', 'Net amount PKR', 'Normalized amount PKR', 'Value status', 'Unknown normalized count');
		$rows = array();
		foreach (array('current' => 'Current', 'comparison' => 'Comparison') as $periodKey => $periodLabel) {
			$period = $this->arrayValue($report, array($periodKey));
			$periodInsights = $this->periodInsights($period);
			foreach ($this->arrayValue($period, array('weekly')) as $week) {
				foreach ($this->metricKeys() as $key => $label) {
					$m = isset($week['metrics'][$key]) ? $week['metrics'][$key] : array();
					$rows[] = array($week['week_start'], $periodLabel, $this->weekCoverageText($periodInsights, $week['week_start']), $label, $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, 'net_value'), $this->value($m, 'normalized_value'), $this->value($m, 'value_status'), $this->value($m, 'normalized_unknown_count'));
				}
			}
		}
		$this->table($sheet, $headers, $rows);
		$this->widths($sheet, array(15, 16, 34, 25, 12, 19, 19, 23, 16, 24));
	}

	private function buildInsightsSheet($sheet, array $report)
	{
		$sheet->setTitle('Benchmarks');
		$insights = $this->insights($report);
		$sheet->setCellValue('A1', 'Statistical baseline');
		$sheet->mergeCells('A1:I1');
		$this->title($sheet, 'A1:I1');
		$sheet->setCellValue('A2', isset($insights['baseline_rule']) ? $insights['baseline_rule'] : '');
		$sheet->mergeCells('A2:I2');
		$sheet->getStyle('A2:I2')->getAlignment()->setWrapText(true);
		$weeklyRows = array();
		foreach ($this->arrayValue($insights, array('weekly_statistics')) as $stat) {
			$weeklyRows[] = array($this->value($stat, 'label'), $this->value($stat, 'observations'), $this->value($stat, 'mean'), $this->value($stat, 'median'), $this->value($stat, 'population_sd'), $this->value($stat, 'latest_week_start'), $this->value($stat, 'latest'), $this->value($stat, 'z_score'), $this->value($stat, 'position'));
		}
		$this->table($sheet, array('Measure', 'Weeks', 'Mean', 'Median', 'Population SD', 'Latest complete week', 'Latest value', 'Z-score', 'Position'), $weeklyRows, 4);

		$weekStarts = $this->arrayValue($insights, array('complete_week_starts'));
		$weekdayHeaders = array_merge(array('Measure', 'Weekday'), $weekStarts, array('Weeks', 'Mean', 'Median', 'Population SD', 'Latest value', 'Z-score', 'Position'));
		$weekdayRows = array();
		foreach ($this->arrayValue($insights, array('weekday_statistics')) as $metricRows) {
			foreach ($metricRows as $stat) {
				$row = array($this->value($stat, 'label'), $this->value($stat, 'weekday_label'));
				foreach ($weekStarts as $weekStart) $row[] = isset($stat['values'][$weekStart]) ? $stat['values'][$weekStart] : null;
				$weekdayRows[] = array_merge($row, array($this->value($stat, 'observations'), $this->value($stat, 'mean'), $this->value($stat, 'median'), $this->value($stat, 'population_sd'), $this->value($stat, 'latest'), $this->value($stat, 'z_score'), $this->value($stat, 'position')));
			}
		}
		$weekdayTitleRow = max(6, count($weeklyRows) + 6);
		$lastWeekdayColumn = $this->column(count($weekdayHeaders));
		$sheet->setCellValue('A' . $weekdayTitleRow, 'Weekday comparison');
		$sheet->mergeCells('A' . $weekdayTitleRow . ':' . $lastWeekdayColumn . $weekdayTitleRow);
		$this->section($sheet, 'A' . $weekdayTitleRow . ':' . $lastWeekdayColumn . $weekdayTitleRow);
		$this->table($sheet, $weekdayHeaders, $weekdayRows, $weekdayTitleRow + 1);
		$sheet->getColumnDimension('A')->setWidth(36);
		$sheet->getColumnDimension('B')->setWidth(15);
		for ($column = 3; $column <= count($weekdayHeaders); $column++) $sheet->getColumnDimension($this->column($column))->setWidth(19);
		$sheet->freezePane('C5');
	}

	private function buildCumulativeSheet($sheet, array $report)
	{
		$sheet->setTitle('Cumulative');
		$cumulative = $this->cumulativeRows($report);
		$this->table($sheet, $cumulative['headers'], $cumulative['rows']);
		$this->widths($sheet, array(16, 28, 20, 22, 18, 24, 21, 18, 25, 21, 18, 18, 24, 18));
	}

	private function buildTeamSheet($sheet, array $report)
	{
		$sheet->setTitle('Teams');
		$headers = array('Team', 'Period', 'Metric', 'Count', 'Raw amount PKR', 'Net amount PKR', 'Normalized amount PKR', 'Value status', 'Unknown normalized count');
		$rows = array();
		foreach (array('current' => 'Current', 'comparison' => 'Comparison') as $periodKey => $periodLabel) {
			foreach ($this->arrayValue($report, array($periodKey, 'teams')) as $team) {
				foreach ($this->metricKeys() as $key => $label) {
					$m = isset($team['metrics'][$key]) ? $team['metrics'][$key] : array();
					$rows[] = array($team['name'], $periodLabel, $label, $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, 'net_value'), $this->value($m, 'normalized_value'), $this->value($m, 'value_status'), $this->value($m, 'normalized_unknown_count'));
				}
			}
		}
		$this->table($sheet, $headers, $rows);
		$this->widths($sheet, array(25, 16, 25, 12, 19, 19, 23, 16, 24));
	}

	private function buildMonthSheet($sheet, array $report)
	{
		$sheet->setTitle('Month Comparison');
		$headers = array('Calendar month', 'Period', 'Metric', 'Count', 'Raw amount PKR', 'Net amount PKR', 'Normalized amount PKR', 'Value status', 'Unknown normalized count');
		$rows = array();
		foreach (array('current' => 'Current', 'comparison' => 'Comparison') as $periodKey => $periodLabel) {
			$months = array();
			foreach ($this->arrayValue($report, array($periodKey, 'daily')) as $day) {
				$month = substr($day['date'], 0, 7);
				if (!isset($months[$month])) $months[$month] = array();
				foreach ($this->metricKeys() as $key => $label) {
					$m = isset($day['metrics'][$key]) ? $day['metrics'][$key] : array();
					if (!isset($months[$month][$key])) $months[$month][$key] = array('label' => $label, 'count' => 0, 'raw' => '0.00', 'net' => '0.00', 'normalized' => '0.00', 'status' => 'complete', 'unknown' => 0, 'net_unknown' => 0, 'raw_unknown' => 0);
					$months[$month][$key]['count'] += isset($m['count']) ? (int) $m['count'] : 0;
					$months[$month][$key]['unknown'] += isset($m['normalized_unknown_count']) ? (int) $m['normalized_unknown_count'] : 0;
					$months[$month][$key]['net_unknown'] += isset($m['net_unknown_count']) ? (int) $m['net_unknown_count'] : 0;
					$months[$month][$key]['raw_unknown'] += isset($m['raw_unknown_count']) ? (int) $m['raw_unknown_count'] : 0;
					foreach (array('raw_value' => 'raw', 'net_value' => 'net', 'normalized_value' => 'normalized') as $field => $target) {
						if (isset($m[$field]) && $m[$field] !== null) $months[$month][$key][$target] = DecimalAmount::add($months[$month][$key][$target], (string) $m[$field]);
					}
					if (isset($m['value_status']) && $m['value_status'] !== 'complete') $months[$month][$key]['status'] = $m['value_status'];
				}
			}
			ksort($months);
			foreach ($months as $month => $metrics) foreach ($metrics as $item) $rows[] = array($month, $periodLabel, $item['label'], $item['count'], $item['raw'], $item['net'], $item['normalized'], $item['status'], $item['unknown']);
		}
		$this->table($sheet, $headers, $rows);
		$this->widths($sheet, array(16, 16, 25, 12, 19, 19, 23, 16, 24));
	}

	private function buildAdjustmentSheet($sheet, array $report)
	{
		$sheet->setTitle('Adjustments');
		$headers = array('ID', 'Metric', 'Adjustment type', 'Count effect', 'Reversal of ID', 'Source key', 'Amount excluded PKR', 'Reason', 'Status', 'Policy version', 'Requested by', 'Requested at', 'Reviewed by', 'Reviewed at', 'Review note');
		$rows = array();
		foreach (array('pending_adjustments', 'adjustment_history') as $group) {
			foreach ($this->arrayValue($report, array($group)) as $item) {
				$rows[] = array(isset($item['id']) ? (int) $item['id'] : null, $this->value($item, 'metric_key'), $this->value($item, 'adjustment_type'),
					$this->value($item, 'count_effect'), $this->value($item, 'reversal_of_id'), $this->value($item, 'source_key'),
					$this->value($item, 'excluded_amount'), $this->value($item, 'reason'), $this->value($item, 'status'),
					$this->value($item, 'policy_version'), $this->value($item, 'requested_by'), $this->value($item, 'requested_at'),
					$this->value($item, 'reviewed_by'), $this->value($item, 'reviewed_at'), $this->value($item, 'review_note'));
			}
		}
		$this->table($sheet, $headers, $rows);
		if (!count($rows)) {
			$this->setText($sheet, 'A2', 'No adjustment records are included in this frozen report.');
			$sheet->mergeCells('A2:O2');
		}
		$this->widths($sheet, array(9, 22, 24, 18, 18, 30, 22, 48, 16, 26, 22, 20, 22, 20, 48));
	}

	private function buildReconciliationSheet($sheet)
	{
		$sheet->setTitle('Reconciliation');
		$sheet->setCellValue('A1', 'Reference report reconciliation');
		$this->title($sheet, 'A1:B1');
		$sheet->mergeCells('A2:B2');
		$this->setText($sheet, 'A2', 'No curated reference observations or approved source-to-reference bridges are attached to this run. Reference figures are not treated as operational transactions.');
		$sheet->getStyle('A2')->getAlignment()->setWrapText(true);
		$sheet->getColumnDimension('A')->setWidth(34);
		$sheet->getColumnDimension('B')->setWidth(90);
	}

	private function buildFactsSheet($sheet, array $facts)
	{
		$sheet->setTitle('Supporting Documents');
		$headers = array('Period role', 'Business at', 'Metric family', 'Source type', 'Source key', 'Case', 'Customer', 'Branch', 'Actor user', 'Entered by', 'Credited owner', 'Credited owner code', 'Credited team ID', 'Attribution basis', 'Attribution version', 'Raw source amount', 'Source currency', 'Raw PKR', 'Net amount', 'Normalized amount', 'Reporting currency', 'Value status', 'Coverage status', 'Eligibility status', 'Date basis', 'Tax basis', 'Warnings', 'Source version', 'Calculation version', 'Normalization references', 'Source evidence summary');
		$rows = array();
		foreach ($facts as $fact) {
			$evidence = isset($fact['source_evidence']) ? $fact['source_evidence'] : '';
			$rows[] = array($this->value($fact, 'period_role') === 'comparison' ? 'Comparison' : 'Current', $this->value($fact, 'business_at'), $this->value($fact, 'metric_family'), $this->value($fact, 'source_type'), $this->value($fact, 'source_key'),
				$this->value($fact, 'salescaseref'), $this->value($fact, 'customer'), $this->value($fact, 'branch'), $this->value($fact, 'actor_user_id'),
				$this->value($fact, 'entered_by_user_id'), $this->value($fact, 'credited_salesperson'), $this->value($fact, 'credited_salesman_code'),
				$this->value($fact, 'credited_team'), $this->value($fact, 'attribution_basis'), $this->value($fact, 'attribution_version'),
				$this->value($fact, 'raw_amount'), $this->value($fact, 'currency'),
				$this->value($fact, 'reporting_raw_amount'), $this->value($fact, 'net_amount'), $this->value($fact, 'normalized_amount'),
				$this->value($fact, 'reporting_currency'), $this->value($fact, 'value_status'), $this->value($fact, 'coverage_status'),
				$this->value($fact, 'eligibility_status'), $this->value($fact, 'date_basis'), $this->value($fact, 'tax_basis'),
				$this->value($fact, 'warnings_text'), $this->value($fact, 'source_version'), $this->value($fact, 'calculation_version'),
				$this->value($fact, 'normalization_references'), $evidence);
		}
		$this->table($sheet, $headers, $rows);
		if (!count($rows)) {
			$this->setText($sheet, 'A2', 'No frozen supporting records are available for this run.');
			$sheet->mergeCells('A2:AE2');
		}
		$this->widths($sheet, array(16, 22, 22, 34, 22, 22, 18, 14, 18, 18, 24, 20, 18, 30, 30, 20, 14, 18, 18, 20, 18, 22, 32, 24, 28, 30, 42, 34, 34, 38, 48));
	}

	private function pdf(array $report, array $facts)
	{
		require_once dirname(__FILE__) . '/../../../includes/tcpdf/tcpdf.php';
		require_once __DIR__ . '/SalesPerformanceReportPdf.php';
		set_error_handler(function($severity, $message, $file, $line) {
			if ($severity === E_WARNING && $line === 1671 && basename($file) === 'tcpdf_fonts.php' && strpos($message, 'chr() expects parameter 1 to be int, string given') === 0) return true;
			return false;
		});
		try {
		$pdf = new SalesPerformanceReportPdf();
		$pdf->SetCreator('S.A. Hamid ERP');
		$pdf->SetAuthor('S.A. Hamid ERP');
		$pdf->SetTitle('Sales Performance Report');
		$pdf->SetSubject('Frozen commercial performance report');
		$pdf->SetMargins(11, 19, 11);
		$pdf->SetHeaderMargin(7);
		$pdf->SetFooterMargin(9);
		$pdf->SetAutoPageBreak(true, 15);
		$pdf->reportRunKey = isset($report['frozen_run']['run_key']) ? $report['frozen_run']['run_key'] : '';
		$pdf->AddPage('L', 'A4');
		$pdf->SetFont('helvetica', '', 9);
		$meta = isset($report['meta']) ? $report['meta'] : array();
		$html = '<h1 style="color:#173f5f;font-size:19px">Commercial Performance Report</h1>';
		$html .= '<table cellpadding="5" cellspacing="0" border="1">';
		$html .= $this->pdfRow('Reporting period', $this->periodLabel(isset($meta['from']) ? $meta['from'] : '', isset($meta['through']) ? $meta['through'] : ''));
		$html .= $this->pdfRow('Business cutoff', isset($meta['business_cutoff']) ? $meta['business_cutoff'] : '');
		$html .= $this->pdfRow('Comparison period', $this->periodLabel(isset($meta['compare_from']) ? $meta['compare_from'] : '', isset($meta['compare_to']) ? $meta['compare_to'] : ''));
		$html .= $this->pdfRow('Reporting scope', $this->scopeLabel($meta));
		$html .= $this->pdfRow('Captured reporting days', (isset($report['current']['reporting_days']) ? $report['current']['reporting_days'] : 0) . ' current / ' . (isset($report['comparison']['reporting_days']) ? $report['comparison']['reporting_days'] : 0) . ' comparison');
		$html .= $this->pdfRow('Definition and status', (isset($meta['definition_version']) ? $meta['definition_version'] : 'unknown') . ' / ' . (isset($meta['validation_status']) ? $meta['validation_status'] : 'provisional'));
		$html .= $this->pdfRow('Frozen run / hash', (isset($report['frozen_run']['run_key']) ? $report['frozen_run']['run_key'] : 'not available') . ' / ' . (isset($report['frozen_run']['content_hash']) ? $report['frozen_run']['content_hash'] : ''));
		$html .= $this->pdfRow('Content integrity', isset($report['frozen_run']['integrity_status']) ? $report['frozen_run']['integrity_status'] : 'unverified');
		$html .= $this->pdfRow('Generated', isset($meta['generated_at']) ? $meta['generated_at'] : '');
		$html .= '</table><br><h2>Company summary and period comparison</h2>';
		$html .= $this->pdfComparisonTable($report);
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->SetFont('helvetica', '', 8);
		$html = '<h2>Activity trends and daily coverage</h2><h3>Weekly activity</h3>' . $this->pdfWeeklyTable($report);
		$html .= '<br><h3>Daily activity and coverage</h3>' . $this->pdfDailyTable($report);
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->SetFont('helvetica', '', 8);
		$html = '<h2>Customer PO analysis</h2><p>Actual captured customer PO receipts and the legacy order confirmation proxy are shown as separate measures.</p>';
		$html .= $this->pdfStageAnalysisTable($report, array('purchase_orders', 'order_confirmations'));
		$html .= '<br><h2>Delivery challan analysis</h2><p>DC document activity is reported independently from quotation and PO stages.</p>';
		$html .= $this->pdfStageAnalysisTable($report, array('delivery_challans'));
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->SetFont('helvetica', '', 8);
		$html = '<h2>Team contribution</h2>' . $this->pdfTeamTable($report);
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->SetFont('helvetica', '', 8);
		$html = '<h2>Cumulative performance</h2><p>Cumulative counts and PKR values follow the selected current period. Partial and unavailable amounts remain labelled.</p>' . $this->pdfCumulativeTable($report);
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->SetFont('helvetica', '', 8);
		$insights = $this->insights($report);
		$html = '<h2>Statistical baselines</h2><p>' . $this->html(isset($insights['baseline_rule']) ? $insights['baseline_rule'] : '') . '</p>';
		$html .= $this->pdfWeeklyStatisticsTable($insights);
		$html .= '<br><h3>Weekday baselines</h3>' . $this->pdfWeekdayStatisticsTable($insights);
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->SetFont('helvetica', '', 8);
		$html = '<h2>Frozen supporting records</h2><p>Captured events and ERP proxy records are shown at their saved source grain. Blank amounts mean a report-currency value was unavailable.</p>' . $this->pdfFactsTable($facts);
		$pdf->writeHTML($html, true, false, true, false, '');

		$pdf->AddPage('L', 'A4');
		$pdf->SetFont('helvetica', '', 9);
		$html = '<h2>Raw to normalized reconciliation and limitations</h2><p>Quotation, customer PO receipt, order confirmation and delivery challan values describe separate commercial stages. They are not added together as revenue.</p>';
		$html .= $this->pdfComparisonTable($report);
		$html .= '<br><h3>Definitions and coverage notes</h3><ul>';
		foreach ($this->limitations($report) as $note) $html .= '<li>' . $this->html($note) . '</li>';
		$html .= '</ul><p>Reference-report reconciliation has not been attached to this run. Unavailable values are omitted and identified by status in the CSV and workbook.</p>';
		$pdf->writeHTML($html, true, false, true, false, '');
		return $pdf->Output('', 'S');
		} finally {
			restore_error_handler();
		}
	}

	private function pdfComparisonTable(array $report)
	{
		$head = array('Metric', 'Current count', 'Current raw PKR', 'Current net PKR', 'Current reported PKR', 'Value basis', 'Compare count', 'Compare reported PKR', 'Current status');
		$rows = array();
		foreach ($this->metricKeys() as $key => $label) {
			$current = isset($report['current']['metrics'][$key]) ? $report['current']['metrics'][$key] : array();
			$comparison = isset($report['comparison']['metrics'][$key]) ? $report['comparison']['metrics'][$key] : array();
			$valueField = $key === 'purchase_orders' ? 'net_value' : 'normalized_value';
			$rows[] = array($label, $this->value($current, 'count'), $this->value($current, 'raw_value'), $this->value($current, 'net_value'), $this->value($current, $valueField), $key === 'purchase_orders' ? 'Net' : 'Normalized', $this->value($comparison, 'count'), $this->value($comparison, $valueField), $this->value($current, 'value_status'));
		}
		return $this->pdfTable($head, $rows);
	}

	private function pdfWeeklyTable(array $report)
	{
		$rows = array();
		$currentPeriod = $this->arrayValue($report, array('current'));
		$currentInsights = $this->periodInsights($currentPeriod);
		foreach ($this->arrayValue($report, array('current', 'weekly')) as $week) {
			foreach (array('quotations', 'revised_quotations', 'purchase_orders', 'order_confirmations', 'delivery_challans') as $key) {
				$m = isset($week['metrics'][$key]) ? $week['metrics'][$key] : array();
				$valueField = $key === 'purchase_orders' ? 'net_value' : 'normalized_value';
				$rows[] = array($week['week_start'], $this->weekCoverageText($currentInsights, $week['week_start']), $this->metricKeys()[$key], $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, $valueField), $key === 'purchase_orders' ? 'Net' : 'Normalized', $this->value($m, 'value_status'));
			}
		}
		return $this->pdfTable(array('Week', 'Coverage', 'Separate measure', 'Count', 'Raw PKR', 'Reported PKR', 'Value basis', 'Status'), $rows);
	}

	private function pdfStageAnalysisTable(array $report, array $metricKeys)
	{
		$rows = array();
		foreach (array('current' => 'Current', 'comparison' => 'Comparison') as $periodKey => $periodLabel) {
			foreach ($this->arrayValue($report, array($periodKey, 'weekly')) as $week) {
				foreach ($metricKeys as $key) {
					$m = isset($week['metrics'][$key]) ? $week['metrics'][$key] : array();
					$rows[] = array($periodLabel, $week['week_start'], $this->metricKeys()[$key], $this->value($m, 'count'), $this->value($m, 'raw_value'), $this->value($m, 'net_value'), $this->value($m, 'normalized_value'), $this->value($m, 'value_status'), $this->value($m, 'normalized_unknown_count'));
				}
			}
		}
		return $this->pdfTable(array('Period', 'Week starting', 'Measure', 'Count', 'Raw PKR', 'Net PKR', 'Normalized PKR', 'Status', 'Unknown values'), $rows);
	}

	private function pdfDailyTable(array $report)
	{
		$rows = array();
		foreach ($this->arrayValue($report, array('current', 'daily')) as $day) {
			$m = isset($day['metrics']) ? $day['metrics'] : array();
			$rows[] = array($day['date'], $day['coverage_status'],
				$this->value(isset($m['quotations']) ? $m['quotations'] : array(), 'count'), $this->value(isset($m['quotations']) ? $m['quotations'] : array(), 'normalized_value'),
				$this->value(isset($m['purchase_orders']) ? $m['purchase_orders'] : array(), 'count'), $this->value(isset($m['purchase_orders']) ? $m['purchase_orders'] : array(), 'net_value'),
				$this->value(isset($m['order_confirmations']) ? $m['order_confirmations'] : array(), 'count'), $this->value(isset($m['order_confirmations']) ? $m['order_confirmations'] : array(), 'normalized_value'),
				$this->value(isset($m['delivery_challans']) ? $m['delivery_challans'] : array(), 'count'), $this->value(isset($m['delivery_challans']) ? $m['delivery_challans'] : array(), 'normalized_value'));
		}
		return $this->pdfTable(array('Date', 'Coverage', 'Quotes', 'Quote PKR', 'Customer PO receipts', 'PO net PKR', 'OCs (proxy)', 'OC PKR', 'DCs', 'DC PKR'), $rows);
	}

	private function pdfTeamTable(array $report)
	{
		$rows = array();
		foreach ($this->arrayValue($report, array('current', 'teams')) as $team) {
			foreach (array('quotations', 'purchase_orders', 'order_confirmations', 'delivery_challans') as $key) {
				$m = isset($team['metrics'][$key]) ? $team['metrics'][$key] : array();
				$valueField = $key === 'purchase_orders' ? 'net_value' : 'normalized_value';
				$rows[] = array($team['name'], $this->metricKeys()[$key], $this->value($m, 'count'), $this->value($m, $valueField), $key === 'purchase_orders' ? 'Net' : 'Normalized', $this->value($m, 'value_status'));
			}
		}
		return $this->pdfTable(array('Team', 'Separate measure', 'Count', 'Reported PKR', 'Value basis', 'Status'), $rows);
	}

	private function pdfCumulativeTable(array $report)
	{
		$keys = array(
			'quotations' => array('value_field' => 'normalized_value', 'unknown_field' => 'normalized_unknown_count'),
			'purchase_orders' => array('value_field' => 'net_value', 'unknown_field' => 'net_unknown_count'),
			'order_confirmations' => array('value_field' => 'normalized_value', 'unknown_field' => 'normalized_unknown_count'),
			'delivery_challans' => array('value_field' => 'normalized_value', 'unknown_field' => 'normalized_unknown_count')
		);
		$state = array();
		foreach ($keys as $key => $definition) $state[$key] = array('count' => 0, 'known_count' => 0, 'unknown_count' => 0, 'amount' => '0.00');
		$rows = array();
		foreach ($this->arrayValue($report, array('current', 'daily')) as $day) {
			$row = array($day['date'], isset($day['coverage_status']) ? $day['coverage_status'] : 'unknown');
			foreach ($keys as $key => $definition) {
				$m = isset($day['metrics'][$key]) ? $day['metrics'][$key] : array();
				$count = isset($m['count']) ? (int) $m['count'] : 0;
				$unknown = isset($m[$definition['unknown_field']]) ? (int) $m[$definition['unknown_field']] : 0;
				$status = isset($m['value_status']) ? $m['value_status'] : 'complete';
				$state[$key]['count'] += $count;
				$state[$key]['unknown_count'] += $unknown;
				$valueField = $definition['value_field'];
				if ($status !== 'unavailable' && isset($m[$valueField]) && $m[$valueField] !== null) {
					$state[$key]['amount'] = DecimalAmount::add($state[$key]['amount'], (string) $m[$valueField]);
					$state[$key]['known_count'] += max(0, $count - $unknown);
				} elseif ($unknown === 0 && $count > 0) {
					$state[$key]['unknown_count'] += $count;
				}
				$cumulativeStatus = $state[$key]['unknown_count'] === 0 ? 'complete' : ($state[$key]['known_count'] > 0 ? 'partial' : 'unavailable');
				$row[] = $state[$key]['count'];
				$row[] = $cumulativeStatus === 'unavailable' ? null : $state[$key]['amount'];
				$row[] = $cumulativeStatus . ($state[$key]['unknown_count'] > 0 ? ' (' . $state[$key]['unknown_count'] . ' unknown)' : '');
			}
			$rows[] = $row;
		}
		return $this->pdfTable(array('Date', 'Coverage', 'Quotes count', 'Quotes normalized PKR', 'Quotes status', 'Customer PO receipts', 'PO net PKR', 'PO status', 'OCs (proxy)', 'OC normalized PKR', 'OC status', 'DC count', 'DC normalized PKR', 'DC status'), $rows);
	}

	private function cumulativeRows(array $report)
	{
		$definitions = array(
			'quotations' => array('value_field' => 'normalized_value', 'unknown_field' => 'normalized_unknown_count'),
			'purchase_orders' => array('value_field' => 'net_value', 'unknown_field' => 'net_unknown_count'),
			'order_confirmations' => array('value_field' => 'normalized_value', 'unknown_field' => 'normalized_unknown_count'),
			'delivery_challans' => array('value_field' => 'normalized_value', 'unknown_field' => 'normalized_unknown_count')
		);
		$state = array();
		foreach ($definitions as $key => $definition) $state[$key] = array('count' => 0, 'known_count' => 0, 'unknown_count' => 0, 'amount' => '0.00');
		$coverageByWeek = $this->arrayValue($this->insights($report), array('week_coverage'));
		$rows = array();
		foreach ($this->arrayValue($report, array('current', 'weekly')) as $week) {
			$weekStart = isset($week['week_start']) ? (string) $week['week_start'] : '';
			$coverage = isset($coverageByWeek[$weekStart]) ? $coverageByWeek[$weekStart] : array();
			$coverageText = isset($coverage['coverage_status']) ? str_replace('_', ' ', $coverage['coverage_status']) : 'unknown';
			if (isset($coverage['planned_workdays'])) $coverageText .= ' (' . (isset($coverage['complete_workdays']) ? $coverage['complete_workdays'] : 0) . '/' . $coverage['planned_workdays'] . ' workdays complete)';
			$row = array($weekStart, $coverageText);
			foreach ($definitions as $key => $definition) {
				$metric = isset($week['metrics'][$key]) ? $week['metrics'][$key] : array();
				$count = isset($metric['count']) ? (int) $metric['count'] : 0;
				$unknown = isset($metric[$definition['unknown_field']]) ? (int) $metric[$definition['unknown_field']] : 0;
				$valueField = $definition['value_field'];
				$status = isset($metric['value_status']) ? $metric['value_status'] : 'complete';
				$hasAmount = $status !== 'unavailable' && isset($metric[$valueField]) && $metric[$valueField] !== null;
				if (!$hasAmount && $count > 0 && $unknown === 0) $unknown = $count;
				$state[$key]['count'] += $count;
				$state[$key]['unknown_count'] += $unknown;
				if ($hasAmount) {
					$state[$key]['amount'] = DecimalAmount::add($state[$key]['amount'], (string) $metric[$valueField]);
					$state[$key]['known_count'] += max(0, $count - $unknown);
				}
				$cumulativeStatus = $state[$key]['unknown_count'] === 0 ? 'complete' : ($state[$key]['known_count'] > 0 ? 'partial' : 'unavailable');
				$row[] = $state[$key]['count'];
				$row[] = $cumulativeStatus === 'unavailable' ? null : $state[$key]['amount'];
				$row[] = $cumulativeStatus . ($state[$key]['unknown_count'] > 0 ? ' (' . $state[$key]['unknown_count'] . ' unknown)' : '');
			}
			$rows[] = $row;
		}
		return array(
			'headers' => array('Week starting', 'Reporting coverage', 'Quotes cumulative count', 'Quote cumulative normalized PKR', 'Quote value status', 'Customer PO cumulative count', 'PO cumulative net PKR', 'PO value status', 'OC proxy cumulative count', 'OC proxy cumulative normalized PKR', 'OC value status', 'DC cumulative count', 'DC cumulative normalized PKR', 'DC value status'),
			'rows' => $rows
		);
	}

	private function pdfWeeklyStatisticsTable(array $insights)
	{
		$rows = array();
		foreach ($this->arrayValue($insights, array('weekly_statistics')) as $stat) {
			$type = $this->value($stat, 'type');
			$rows[] = array($this->value($stat, 'label'), $this->value($stat, 'observations'),
				$this->insightValue($this->value($stat, 'mean'), $type), $this->insightValue($this->value($stat, 'median'), $type),
				$this->insightValue($this->value($stat, 'population_sd'), $type), $this->value($stat, 'latest_week_start'),
				$this->insightValue($this->value($stat, 'latest'), $type), $this->insightZScore($this->value($stat, 'z_score')), $this->value($stat, 'position'));
		}
		return $this->pdfTable(array('Measure', 'Weeks', 'Mean', 'Median', 'Population SD', 'Latest complete week', 'Latest', 'Z-score', 'Position'), $rows);
	}

	private function pdfWeekdayStatisticsTable(array $insights)
	{
		$rows = array();
		foreach ($this->arrayValue($insights, array('weekday_statistics')) as $metricRows) {
			foreach ($metricRows as $stat) {
				$type = $this->value($stat, 'type');
				$rows[] = array($this->value($stat, 'label'), $this->value($stat, 'weekday_label'), $this->value($stat, 'observations'),
					$this->insightValue($this->value($stat, 'mean'), $type), $this->insightValue($this->value($stat, 'median'), $type),
					$this->insightValue($this->value($stat, 'population_sd'), $type), $this->insightValue($this->value($stat, 'latest'), $type),
					$this->insightZScore($this->value($stat, 'z_score')), $this->value($stat, 'position'));
			}
		}
		return $this->pdfTable(array('Measure', 'Weekday', 'Weeks', 'Mean', 'Median', 'Population SD', 'Latest', 'Z-score', 'Position'), $rows);
	}

	private function pdfFactsTable(array $facts)
	{
		$rows = array();
		foreach (array_slice($facts, 0, 150) as $fact) $rows[] = array($this->value($fact, 'period_role') === 'comparison' ? 'Comparison' : 'Current', $this->value($fact, 'business_at'), $this->value($fact, 'metric_family'), $this->value($fact, 'source_type'),
			$this->value($fact, 'source_key'), $this->value($fact, 'salescaseref'), $this->value($fact, 'credited_salesperson'),
			$this->value($fact, 'raw_amount') . ' ' . $this->value($fact, 'currency'), $this->value($fact, 'normalized_amount'), $this->value($fact, 'value_status'));
		if (count($facts) > 150) $rows[] = array('', '', '', '', '', '', '', '', '', 'PDF detail limited to first 150; workbook and CSV contain all ' . count($facts) . ' saved facts.');
		return $this->pdfTable(array('Period', 'Date', 'Metric family', 'Source type', 'Record key', 'Case', 'Owner', 'Source amount', 'Normalized', 'Status'), $rows);
	}

	private function pdfTable(array $headers, array $rows)
	{
		$html = '<table border="1" cellpadding="3" cellspacing="0"><thead><tr style="background-color:#173f5f;color:#ffffff">';
		foreach ($headers as $header) $html .= '<th><b>' . $this->html($header) . '</b></th>';
		$html .= '</tr></thead><tbody>';
		foreach ($rows as $row) {
			$html .= '<tr>';
			foreach ($row as $value) $html .= '<td>' . $this->html($value === null || $value === '' ? 'Unavailable' : $value) . '</td>';
			$html .= '</tr>';
		}
		return $html . '</tbody></table>';
	}

	private function pdfRow($label, $value)
	{
		return '<tr><td width="25%"><b>' . $this->html($label) . '</b></td><td>' . $this->html($value) . '</td></tr>';
	}

	private function html($value)
	{
		return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	private function metricKeys()
	{
		return array(
			'enquiries' => 'Enquiries',
			'quotations' => 'Initial quotations',
			'revised_quotations' => 'Revised quotations',
			'purchase_orders' => 'Customer PO receipts',
			'order_confirmations' => 'Order confirmations (proxy)',
			'delivery_challans' => 'Delivery challans',
			'followups' => 'Follow-ups',
			'enquiry_responses' => 'Enquiry responses',
			'company_regrets' => 'Company regrets',
			'customer_rejections' => 'Customer rejections'
		);
	}

	private function value(array $row, $key)
	{
		return array_key_exists($key, $row) ? $row[$key] : null;
	}

	private function insights(array $report)
	{
		$current = $this->arrayValue($report, array('current'));
		return $this->periodInsights($current);
	}

	private function periodInsights(array $period)
	{
		if (isset($period['insights']) && is_array($period['insights'])) return $period['insights'];
		return \SAHamid\BI\Commercial\SalesPerformanceInsights::build($period);
	}

	private function weekCoverageText(array $insights, $weekStart)
	{
		$coverageByWeek = $this->arrayValue($insights, array('week_coverage'));
		if (!isset($coverageByWeek[$weekStart])) return 'Coverage unknown';
		$coverage = $coverageByWeek[$weekStart];
		$status = isset($coverage['coverage_status']) ? str_replace('_', ' ', $coverage['coverage_status']) : 'unknown';
		if ($status === 'after cutoff' || $status === 'no planned workdays') return $status;
		if (isset($coverage['planned_workdays'])) {
			$status .= ' (' . (isset($coverage['complete_workdays']) ? $coverage['complete_workdays'] : 0) . '/' . $coverage['planned_workdays'] . ' workdays complete)';
		}
		return $status;
	}

	private function insightValue($value, $type)
	{
		if ($value === null || $value === '') return 'N/A';
		$formatted = number_format((float) $value, 2, '.', ',');
		return $type === 'money' ? 'PKR ' . $formatted : $formatted;
	}

	private function insightZScore($value)
	{
		if ($value === null || $value === '') return 'N/A';
		$score = (float) $value;
		return ($score > 0 ? '+' : '') . number_format($score, 2, '.', ',');
	}

	private function arrayValue(array $row, array $path)
	{
		foreach ($path as $key) {
			if (!is_array($row) || !array_key_exists($key, $row)) return array();
			$row = $row[$key];
		}
		return is_array($row) ? $row : array();
	}

	private function scopeLabel(array $meta)
	{
		$scope = isset($meta['effective_scope']) && is_array($meta['effective_scope']) ? $meta['effective_scope'] : array();
		$names = isset($scope['salespeople']) ? $scope['salespeople'] : 'not specified';
		if (is_array($names)) $names = implode(', ', $names);
		$teams = isset($scope['team_ids']) && is_array($scope['team_ids']) ? implode(', ', $scope['team_ids']) : '';
		$label = (isset($scope['kind']) ? $scope['kind'] : 'unknown') . '; teams: ' . ($teams === '' ? 'all within scope' : $teams) . '; people: ' . $names;
		if (isset($scope['named_population']) && is_array($scope['named_population'])) {
			$population = $scope['named_population'];
			$label .= '; population: ' . (isset($population['label']) ? $population['label'] : 'approved named population');
			if (isset($population['version'])) $label .= ' v' . (int) $population['version'];
			if (array_key_exists('include_unassigned', $population)) $label .= '; unassigned ' . ($population['include_unassigned'] ? 'included' : 'excluded');
		}
		return $label;
	}

	private function periodLabel($from, $through)
	{
		return trim((string) $from . ' to ' . (string) $through, ' to');
	}

	private function limitations(array $report)
	{
		$assumptions = isset($report['assumptions']) && is_array($report['assumptions']) ? $report['assumptions'] : array();
		$notes = array();
		foreach ($assumptions as $note) if (is_scalar($note) && trim((string) $note) !== '') $notes[] = (string) $note;
		$notes[] = 'All values are PKR only when the source is PKR and valuation is known; foreign currency and unknown tax classification are not guessed.';
		$notes[] = 'Quotation, customer PO receipt, order confirmation proxy, and delivery challan figures are separate commercial stages and must not be added as revenue.';
		$notes[] = 'Historical team attribution falls back to current membership where no effective-dated history is available; unresolved owners remain unassigned.';
		$notes[] = 'Reference-report reconciliation and an approved adjustment bridge are not attached to this frozen run.';
		return array_values(array_unique($notes));
	}

	private function coverageRows(array $report)
	{
		$rows = array();
		foreach (array('current' => 'Current', 'comparison' => 'Comparison') as $period => $label) {
			foreach ($this->arrayValue($report, array($period, 'daily')) as $day) $rows[] = array($label . ' ' . $day['date'], $day['coverage_status']);
		}
		return $rows;
	}

	private function table($sheet, array $headers, array $rows, $startRow = 1)
	{
		foreach ($headers as $index => $value) $this->setText($sheet, $this->column($index + 1) . $startRow, $value);
		$sheet->getStyle('A' . $startRow . ':' . $this->column(count($headers)) . $startRow)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
		$sheet->getStyle('A' . $startRow . ':' . $this->column(count($headers)) . $startRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->green);
		$rowNo = $startRow + 1;
		foreach ($rows as $row) {
			foreach ($headers as $index => $header) {
				$value = array_key_exists($index, $row) ? $row[$index] : null;
				$cell = $this->column($index + 1) . $rowNo;
				if ($value === null || $value === '') continue;
				if ($this->isNumericColumn($header) && is_numeric($value)) {
					$sheet->setCellValue($cell, 0 + $value);
					if (strpos(strtolower($header), 'amount') !== false || strpos(strtolower($header), 'value') !== false) $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00;[Red]-#,##0.00');
				} else {
					$this->setText($sheet, $cell, $value);
				}
			}
			$rowNo++;
		}
		$last = max($startRow + 1, $rowNo - 1);
		$range = 'A' . $startRow . ':' . $this->column(count($headers)) . $last;
		$sheet->setAutoFilter('A' . $startRow . ':' . $this->column(count($headers)) . max($startRow, $last));
		$sheet->freezePane('A' . ($startRow + 1));
		$sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
	}

	private function isNumericColumn($header)
	{
		$header = strtolower((string) $header);
		return strpos($header, 'count') !== false || strpos($header, 'amount') !== false || strpos($header, 'value') !== false || $header === 'id'
			|| in_array($header, array('weeks', 'mean', 'median', 'population sd', 'z-score', 'latest'), true);
	}

	private function setText($sheet, $cell, $value)
	{
		$text = $value === null ? '' : (string) $value;
		$sheet->setCellValueExplicit($cell, $text, DataType::TYPE_STRING);
	}

	private function title($sheet, $range)
	{
		$sheet->getStyle($range)->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('FFFFFF');
		$sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->navy);
	}

	private function section($sheet, $range)
	{
		$sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setRGB($this->navy);
		$sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($this->light);
	}

	private function widths($sheet, array $widths)
	{
		foreach ($widths as $index => $width) $sheet->getColumnDimension($this->column((int) $index + 1))->setWidth($width);
	}

	private function column($number)
	{
		$result = '';
		while ($number > 0) {
			$number--;
			$result = chr(65 + ($number % 26)) . $result;
			$number = (int) floor($number / 26);
		}
		return $result;
	}
}
