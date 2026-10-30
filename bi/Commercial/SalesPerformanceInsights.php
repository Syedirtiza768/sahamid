<?php

namespace SAHamid\BI\Commercial;

/** Builds transparent baselines from fully covered, closed Monday-Saturday weeks. */
class SalesPerformanceInsights
{
	public static function build(array $period)
	{
		$from = isset($period['from']) ? (string) $period['from'] : '';
		$to = isset($period['to']) ? (string) $period['to'] : '';
		$asOf = isset($period['as_of']) ? (string) $period['as_of'] : $to;
		$through = $to !== '' && $asOf !== '' ? min($to, $asOf) : ($asOf !== '' ? $asOf : $to);
		$dailyByDate = array();
		$dailyRows = isset($period['daily']) && is_array($period['daily']) ? $period['daily'] : array();
		foreach ($dailyRows as $day) {
			if (isset($day['date'])) $dailyByDate[(string) $day['date']] = $day;
		}

		$weeks = isset($period['weekly']) && is_array($period['weekly']) ? $period['weekly'] : array();
		usort($weeks, function($left, $right) {
			return strcmp(isset($left['week_start']) ? $left['week_start'] : '', isset($right['week_start']) ? $right['week_start'] : '');
		});
		$weekCoverage = array();
		$completeWeekStarts = array();
		foreach ($weeks as $week) {
			$weekStart = isset($week['week_start']) ? (string) $week['week_start'] : '';
			if ($weekStart === '') continue;
			$afterCutoff = $through !== '' && $weekStart > $through;
			$completeWindow = true;
			$partialDateRange = false;
			$missingSelectedDate = false;
			$plannedWorkdays = 0;
			$completeWorkdays = 0;
			$seenStatuses = array();
			for ($offset = 0; $offset < 6; $offset++) {
				$date = date('Y-m-d', strtotime($weekStart . ' +' . $offset . ' days'));
				if ($from === '' || $through === '' || $date < $from || $date > $through) {
					$completeWindow = false;
					$partialDateRange = true;
					continue;
				}
				if (!isset($dailyByDate[$date])) {
					$completeWindow = false;
					$missingSelectedDate = true;
					continue;
				}
				$day = $dailyByDate[$date];
				if (empty($day['planned_workday'])) continue;
				$plannedWorkdays++;
				$status = isset($day['coverage_status']) ? (string) $day['coverage_status'] : 'unknown';
				$seenStatuses[$status] = true;
				if ($status === 'complete') $completeWorkdays++;
			}
			$fullyCovered = $completeWindow && !$missingSelectedDate && $plannedWorkdays > 0 && $completeWorkdays === $plannedWorkdays;
			if ($afterCutoff) $coverageStatus = 'after_cutoff';
			elseif ($missingSelectedDate) $coverageStatus = 'coverage_unknown';
			elseif ($plannedWorkdays === 0) $coverageStatus = 'no_planned_workdays';
			elseif ($fullyCovered) $coverageStatus = 'complete';
			elseif ($completeWorkdays !== $plannedWorkdays && isset($seenStatuses['partial'])) $coverageStatus = 'partial';
			elseif ($completeWorkdays !== $plannedWorkdays && isset($seenStatuses['unresolved'])) $coverageStatus = 'unresolved';
			elseif ($completeWorkdays !== $plannedWorkdays && isset($seenStatuses['not_submitted'])) $coverageStatus = 'not_submitted';
			elseif ($completeWorkdays !== $plannedWorkdays) $coverageStatus = 'coverage_unknown';
			elseif ($partialDateRange) $coverageStatus = 'partial_period';
			else $coverageStatus = 'coverage_unknown';

			$weekCoverage[$weekStart] = array(
				'week_start' => $weekStart,
				'week_end' => date('Y-m-d', strtotime($weekStart . ' +5 days')),
				'coverage_status' => $coverageStatus,
				'partial_date_range' => $partialDateRange,
				'after_cutoff' => $afterCutoff,
				'planned_workdays' => $plannedWorkdays,
				'complete_workdays' => $completeWorkdays,
				'complete_for_baseline' => $fullyCovered
			);
			if ($fullyCovered) $completeWeekStarts[] = $weekStart;
		}

		$latestWeekStart = count($completeWeekStarts) ? $completeWeekStarts[count($completeWeekStarts) - 1] : null;
		$definitions = self::metricDefinitions();
		$weeklyStatistics = array();
		$weekdayStatistics = array();
		foreach ($definitions as $definition) {
			$observations = array();
			foreach ($completeWeekStarts as $weekStart) {
				$week = self::findWeek($weeks, $weekStart);
				$value = $week === null ? null : self::measureValue(isset($week['metrics']) ? $week['metrics'] : array(), $definition);
				if ($value !== null) $observations[] = array('week_start' => $weekStart, 'value' => $value);
			}
			$weeklyStatistics[] = self::summarize($definition, $observations, $latestWeekStart);

			$weekdayRows = array();
			for ($weekday = 1; $weekday <= 6; $weekday++) {
				$weekdayObservations = array();
				$weekdayValues = array();
				foreach ($completeWeekStarts as $weekStart) {
					$date = date('Y-m-d', strtotime($weekStart . ' +' . ($weekday - 1) . ' days'));
					if (!isset($dailyByDate[$date]) || empty($dailyByDate[$date]['planned_workday'])) continue;
					$value = self::measureValue(isset($dailyByDate[$date]['metrics']) ? $dailyByDate[$date]['metrics'] : array(), $definition);
					$weekdayValues[$weekStart] = $value;
					if ($value !== null) $weekdayObservations[] = array('week_start' => $weekStart, 'value' => $value);
				}
				$summary = self::summarize($definition, $weekdayObservations, $latestWeekStart);
				$summary['weekday'] = $weekday;
				$summary['weekday_label'] = self::weekdayLabel($weekday);
				$summary['values'] = $weekdayValues;
				$weekdayRows[] = $summary;
			}
			$weekdayStatistics[$definition['id']] = $weekdayRows;
		}

		return array(
			'baseline_rule' => 'Population mean and standard deviation use only full Monday-Saturday weeks with complete submitted coverage. Missing or partial days are excluded, not treated as zero. Z-score positions are marked extraordinary above +2 or below -2 standard deviations.',
			'week_coverage' => $weekCoverage,
			'complete_week_starts' => $completeWeekStarts,
			'latest_complete_week' => $latestWeekStart,
			'weekly_statistics' => $weeklyStatistics,
			'weekday_statistics' => $weekdayStatistics
		);
	}

	public static function metricDefinitions()
	{
		return array(
			array('id' => 'enquiries.count', 'label' => 'Enquiries', 'metric' => 'enquiries', 'field' => 'count', 'type' => 'count'),
			array('id' => 'quotations.count', 'label' => 'Initial quotations', 'metric' => 'quotations', 'field' => 'count', 'type' => 'count'),
			array('id' => 'quotations.normalized_value', 'label' => 'Quotation value (normalized)', 'metric' => 'quotations', 'field' => 'normalized_value', 'type' => 'money'),
			array('id' => 'revised_quotations.count', 'label' => 'Revised quotations', 'metric' => 'revised_quotations', 'field' => 'count', 'type' => 'count'),
			array('id' => 'purchase_orders.count', 'label' => 'Customer PO receipts', 'metric' => 'purchase_orders', 'field' => 'count', 'type' => 'count'),
			array('id' => 'purchase_orders.net_value', 'label' => 'Customer PO net value', 'metric' => 'purchase_orders', 'field' => 'net_value', 'type' => 'money'),
			array('id' => 'order_confirmations.count', 'label' => 'Order confirmations (legacy proxy)', 'metric' => 'order_confirmations', 'field' => 'count', 'type' => 'count'),
			array('id' => 'order_confirmations.normalized_value', 'label' => 'Order confirmation value (legacy proxy)', 'metric' => 'order_confirmations', 'field' => 'normalized_value', 'type' => 'money'),
			array('id' => 'delivery_challans.count', 'label' => 'Delivery challans', 'metric' => 'delivery_challans', 'field' => 'count', 'type' => 'count'),
			array('id' => 'delivery_challans.normalized_value', 'label' => 'Delivery challan value (normalized)', 'metric' => 'delivery_challans', 'field' => 'normalized_value', 'type' => 'money'),
			array('id' => 'followups.count', 'label' => 'Follow-ups', 'metric' => 'followups', 'field' => 'count', 'type' => 'count')
		);
	}

	private static function findWeek(array $weeks, $weekStart)
	{
		foreach ($weeks as $week) if (isset($week['week_start']) && (string) $week['week_start'] === (string) $weekStart) return $week;
		return null;
	}

	private static function measureValue(array $metrics, array $definition)
	{
		$key = $definition['metric'];
		if (!isset($metrics[$key]) || !is_array($metrics[$key])) return null;
		$metric = $metrics[$key];
		$field = $definition['field'];
		if ($field === 'count') return isset($metric['count']) && is_numeric($metric['count']) ? (float) $metric['count'] : null;
		if (!isset($metric[$field]) || !is_numeric($metric[$field]) || !isset($metric['value_status']) || $metric['value_status'] !== 'complete') return null;
		$unknownField = $field === 'net_value' ? 'net_unknown_count' : ($field === 'raw_value' ? 'raw_unknown_count' : 'normalized_unknown_count');
		if (isset($metric[$unknownField]) && (int) $metric[$unknownField] > 0) return null;
		return (float) $metric[$field];
	}

	private static function summarize(array $definition, array $observations, $latestWeekStart)
	{
		$values = array();
		$latest = null;
		foreach ($observations as $observation) {
			$value = (float) $observation['value'];
			$values[] = $value;
			if ($latestWeekStart !== null && $observation['week_start'] === $latestWeekStart) $latest = $value;
		}
		$count = count($values);
		$mean = null;
		$median = null;
		$populationSd = null;
		$zScore = null;
		$position = 'Unavailable';
		if ($count > 0) {
			$mean = array_sum($values) / $count;
			sort($values, SORT_NUMERIC);
			$middle = (int) floor($count / 2);
			$median = ($count % 2) ? $values[$middle] : ($values[$middle - 1] + $values[$middle]) / 2;
			$squared = 0.0;
			foreach ($values as $value) $squared += ($value - $mean) * ($value - $mean);
			$populationSd = sqrt($squared / $count);
			if ($latest !== null && $populationSd > 0) {
				$zScore = ($latest - $mean) / $populationSd;
				if ($zScore > 2) $position = 'Extraordinary high';
				elseif ($zScore < -2) $position = 'Extraordinary low';
				elseif ($zScore > 0) $position = 'Above mean';
				elseif ($zScore < 0) $position = 'Below mean';
				else $position = 'At mean';
			}
		}
		return array(
			'id' => $definition['id'],
			'label' => $definition['label'],
			'type' => $definition['type'],
			'observations' => $count,
			'mean' => $mean,
			'median' => $median,
			'population_sd' => $populationSd,
			'latest_week_start' => $latestWeekStart,
			'latest' => $latest,
			'z_score' => $zScore,
			'position' => $position
		);
	}

	private static function weekdayLabel($weekday)
	{
		$labels = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday');
		return isset($labels[$weekday]) ? $labels[$weekday] : '';
	}
}
