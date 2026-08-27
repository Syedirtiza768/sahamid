<?php

namespace SAHamid\BI\Query;

use SAHamid\BI\Exception\BIException;

class QueryRequest
{
	private $metricIds;
	private $dimensions;
	private $dateRange;
	private $filters;
	private $comparison;
	private $limit;

	private function __construct(array $metricIds, array $dimensions, array $dateRange, array $filters, $comparison, $limit)
	{
		$this->metricIds = $metricIds;
		$this->dimensions = $dimensions;
		$this->dateRange = $dateRange;
		$this->filters = $filters;
		$this->comparison = $comparison;
		$this->limit = $limit;
	}

	public static function fromArray(array $input)
	{
		$metricIds = isset($input['metricIds']) && is_array($input['metricIds']) ? array_values($input['metricIds']) : array();
		foreach ($metricIds as $index => $metricId) {
			$metricId = (string) $metricId;
			if (preg_match('/^[a-z0-9_.-]+$/', $metricId) !== 1) {
				throw new BIException('invalid_request', 'Metric IDs must contain only lowercase letters, numbers, dots, dashes, or underscores.', 400, array('index' => $index));
			}
			$metricIds[$index] = $metricId;
		}
		if (!$metricIds) {
			throw new BIException('invalid_request', 'At least one valid metric ID is required.', 400);
		}

		$dimensions = isset($input['dimensions']) && is_array($input['dimensions']) ? array_values(array_map('strval', $input['dimensions'])) : array();
		$dateRange = isset($input['dateRange']) && is_array($input['dateRange']) ? $input['dateRange'] : array();
		$start = isset($dateRange['start']) ? $dateRange['start'] : (isset($dateRange['from']) ? $dateRange['from'] : null);
		$end = isset($dateRange['end']) ? $dateRange['end'] : (isset($dateRange['to']) ? $dateRange['to'] : null);
		self::validateDate($start, 'start');
		self::validateDate($end, 'end');
		if ($start > $end) {
			throw new BIException('invalid_date_range', 'The start date must not be after the end date.', 400);
		}

		$filters = isset($input['filters']) && is_array($input['filters']) ? self::normalizeFilters($input['filters']) : array();
		$comparison = isset($input['comparison']) && $input['comparison'] !== '' ? (string) $input['comparison'] : null;
		if ($comparison !== null && !in_array($comparison, array('none', 'previous_period', 'previous_year'), true)) {
			throw new BIException('invalid_comparison', 'The requested comparison is not supported.', 400);
		}

		$limit = isset($input['limit']) ? (int) $input['limit'] : 100;
		if ($limit < 1 || $limit > 1000) {
			throw new BIException('invalid_limit', 'The query limit must be between 1 and 1000.', 400);
		}

		return new self($metricIds, $dimensions, array('start' => $start, 'end' => $end), $filters, $comparison, $limit);
	}

	public function getMetricIds() { return $this->metricIds; }
	public function getDimensions() { return $this->dimensions; }
	public function getDateRange() { return $this->dateRange; }
	public function getFilters() { return $this->filters; }
	public function getComparison() { return $this->comparison; }
	public function getLimit() { return $this->limit; }

	public function getFilter($dimension)
	{
		foreach ($this->filters as $filter) {
			if ($filter['dimension'] === $dimension) {
				return $filter;
			}
		}
		return null;
	}

	public function getCanonicalPayload()
	{
		$payload = array(
			'metric_ids' => $this->metricIds,
			'dimensions' => $this->dimensions,
			'date_range' => $this->dateRange,
			'filters' => $this->filters,
			'comparison' => $this->comparison,
			'limit' => $this->limit,
		);
		return self::canonicalize($payload);
	}

	private static function normalizeFilters(array $filters)
	{
		$normalized = array();
		$seenDimensions = array();
		foreach ($filters as $filter) {
			if (!is_array($filter) || !isset($filter['dimension']) || !isset($filter['operator'])) {
				throw new BIException('invalid_filter', 'Each filter requires a dimension and operator.', 400);
			}
			$dimension = (string) $filter['dimension'];
			$operator = (string) $filter['operator'];
			if (preg_match('/^[a-z0-9_.-]+$/', $dimension) !== 1 || !in_array($operator, array('eq', 'in', 'in_scope'), true)) {
				throw new BIException('invalid_filter', 'The requested filter is not supported.', 400);
			}
			if (isset($seenDimensions[$dimension])) {
				throw new BIException('invalid_filter', 'Only one filter per dimension is supported in this query slice.', 400);
			}
			$seenDimensions[$dimension] = true;
			$value = isset($filter['value']) ? $filter['value'] : null;
			if ($operator === 'in') {
				if (!is_array($value) || count($value) > 100) {
					throw new BIException('invalid_filter', 'An in-filter must contain at most 100 values.', 400);
				}
				$value = array_values(array_map('strval', $value));
			} elseif ($operator === 'in_scope') {
				$value = null;
			} else {
				$value = is_scalar($value) ? (string) $value : null;
			}
			$normalized[] = array('dimension' => $dimension, 'operator' => $operator, 'value' => $value);
		}
		usort($normalized, function ($left, $right) {
			return strcmp(json_encode($left), json_encode($right));
		});
		return $normalized;
	}

	private static function validateDate($date, $name)
	{
		if (!is_string($date) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
			throw new BIException('invalid_date_range', 'A valid ' . $name . ' date in YYYY-MM-DD format is required.', 400);
		}
		$parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
		$errors = \DateTimeImmutable::getLastErrors();
		if (!$parsed || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $parsed->format('Y-m-d') !== $date) {
			throw new BIException('invalid_date_range', 'A valid ' . $name . ' date in YYYY-MM-DD format is required.', 400);
		}
	}

	private static function canonicalize($value)
	{
		if (!is_array($value)) {
			return $value;
		}
		if (!$value || array_keys($value) === range(0, count($value) - 1)) {
			return array_map(array(__CLASS__, 'canonicalize'), $value);
		}
		ksort($value);
		foreach ($value as $key => $item) {
			$value[$key] = self::canonicalize($item);
		}
		return $value;
	}
}
