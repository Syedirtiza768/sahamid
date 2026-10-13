<?php

namespace SAHamid\BI\Commercial;

use SAHamid\BI\Exception\BIException;

class ReportingRequest
{
	private $from;
	private $through;
	private $salespeople;
	private $businessCutoff;

	private function __construct($from, $through, $salespeople, $businessCutoff)
	{
		$this->from = $from;
		$this->through = $through;
		$this->salespeople = $salespeople;
		$this->businessCutoff = $businessCutoff;
	}

	public static function fromArray(array $input, array $allowedSalespeople = null)
	{
		$from = isset($input['from']) ? $input['from'] : '';
		$through = isset($input['through']) ? $input['through'] : '';
		if (!self::isDate($from) || !self::isDate($through) || $from > $through) {
			throw new BIException('invalid_filter', 'Choose a valid inclusive reporting date range.', 400);
		}
		$days = (int) floor((strtotime($through) - strtotime($from)) / 86400) + 1;
		if ($days > 400) throw new BIException('invalid_filter', 'Choose a date range of 400 days or less.', 400);

		$cutoff = isset($input['business_cutoff']) && $input['business_cutoff'] !== '' ? $input['business_cutoff'] : $through;
		if (!self::isDate($cutoff) || $cutoff < $from || $cutoff > $through) {
			throw new BIException('invalid_filter', 'The business cutoff must fall within the selected reporting range.', 400);
		}

		$salespeople = null;
		if (array_key_exists('salespeople', $input)) {
			$requested = is_array($input['salespeople']) ? $input['salespeople'] : array($input['salespeople']);
			$salespeople = array();
			foreach ($requested as $name) {
				if (!is_string($name) || trim($name) === '') throw new BIException('invalid_filter', 'A salesperson filter is invalid.', 400);
				if ($allowedSalespeople !== null && !in_array($name, $allowedSalespeople, true)) {
					throw new BIException('forbidden', 'A requested salesperson is outside your authorized reporting scope.', 403);
				}
				$salespeople[$name] = $name;
			}
			$salespeople = array_values($salespeople);
		}
		return new self($from, $through, $salespeople, $cutoff);
	}

	public function getFrom() { return $this->from; }
	public function getThrough() { return $this->through; }
	public function getSalespeople() { return $this->salespeople; }
	public function getBusinessCutoff() { return $this->businessCutoff; }

	public function toArray()
	{
		return array('from' => $this->from, 'through' => $this->through, 'business_cutoff' => $this->businessCutoff, 'salespeople' => $this->salespeople);
	}

	private static function isDate($value)
	{
		if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return false;
		$parsed = \DateTime::createFromFormat('!Y-m-d', $value);
		return $parsed && $parsed->format('Y-m-d') === $value;
	}
}
