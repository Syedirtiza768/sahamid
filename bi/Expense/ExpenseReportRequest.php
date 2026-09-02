<?php

namespace SAHamid\BI\Expense;

use SAHamid\BI\Exception\BIException;

class ExpenseReportRequest
{
	private $dateRange;
	private $category;
	private $costCenter;
	private $tabCode;
	private $userCode;
	private $expenseCode;
	private $glAccount;
	private $accountGroup;
	private $section;
	private $spendClass;
	private $status;
	private $receipt;
	private $entryKind;
	private $currency;
	private $search;
	private $includeLocalPurchases;
	private $minAmount;
	private $maxAmount;
	private $page;
	private $pageSize;
	private $sort;
	private $direction;

	private function __construct(array $values)
	{
		$this->dateRange = $values['date_range'];
		$this->category = $values['category'];
		$this->costCenter = $values['cost_center'];
		$this->tabCode = $values['tab_code'];
		$this->userCode = $values['user_code'];
		$this->expenseCode = $values['expense_code'];
		$this->glAccount = $values['gl_account'];
		$this->accountGroup = $values['account_group'];
		$this->section = $values['section'];
		$this->spendClass = $values['spend_class'];
		$this->status = $values['status'];
		$this->receipt = $values['receipt'];
		$this->entryKind = $values['entry_kind'];
		$this->currency = $values['currency'];
		$this->search = $values['search'];
		$this->includeLocalPurchases = $values['include_local_purchases'];
		$this->minAmount = $values['min_amount'];
		$this->maxAmount = $values['max_amount'];
		$this->page = $values['page'];
		$this->pageSize = $values['page_size'];
		$this->sort = $values['sort'];
		$this->direction = $values['direction'];
	}

	public static function fromArray(array $input, ExpenseCategoryClassifier $classifier = null)
	{
		$classifier = $classifier === null ? new ExpenseCategoryClassifier() : $classifier;
		$range = isset($input['dateRange']) && is_array($input['dateRange']) ? $input['dateRange'] : array();
		$start = isset($range['start']) ? $range['start'] : (isset($input['startDate']) ? $input['startDate'] : null);
		$end = isset($range['end']) ? $range['end'] : (isset($input['endDate']) ? $input['endDate'] : null);
		self::validateDate($start, 'start');
		self::validateDate($end, 'end');
		if ($start > $end) {
			throw new BIException('invalid_date_range', 'The start date must not be after the end date.', 400);
		}
		$days = (int) ((strtotime($end) - strtotime($start)) / 86400) + 1;
		if ($days > 7305) {
			throw new BIException('invalid_date_range', 'Expense reports are limited to a 20-year date range.', 400);
		}

		$category = self::optionalString($input, 'category', 80);
		if ($category !== null && !in_array($category, $classifier->categories(), true)) {
			throw new BIException('invalid_filter', 'The requested executive expense category is not supported.', 400);
		}
		$status = self::optionalString($input, 'status', 40);
		if ($status !== null && !in_array($status, array('pending_authorization', 'authorized_unposted', 'posted'), true)) {
			throw new BIException('invalid_filter', 'The requested expense workflow status is not supported.', 400);
		}
		$receipt = self::optionalString($input, 'receipt', 20);
		if ($receipt !== null && !in_array($receipt, array('with_receipt', 'without_receipt'), true)) {
			throw new BIException('invalid_filter', 'The requested receipt filter is not supported.', 400);
		}
		$entryKind = self::optionalString($input, 'entryKind', 20);
		if ($entryKind !== null && !in_array($entryKind, array('expense', 'credit'), true)) {
			throw new BIException('invalid_filter', 'The requested entry type is not supported.', 400);
		}
		$spendClass = self::optionalString($input, 'spendClass', 40);
		if ($spendClass !== null && !in_array($spendClass, array('P&L spend', 'Balance sheet / non-P&L', 'Unclassified'), true)) {
			throw new BIException('invalid_filter', 'The requested spend class is not supported.', 400);
		}
		$currency = self::optionalString($input, 'currency', 3);
		if ($currency !== null && preg_match('/^[A-Za-z]{3}$/', $currency) !== 1) {
			throw new BIException('invalid_filter', 'Currency must be a three-letter code.', 400);
		}
		$currency = $currency === null ? null : strtoupper($currency);
		$includeLocalPurchases = self::optionalBoolean($input, 'includeLocalPurchases', true);
		$minAmount = self::optionalAmount($input, 'minAmount');
		$maxAmount = self::optionalAmount($input, 'maxAmount');
		if ($minAmount !== null && $maxAmount !== null && $minAmount > $maxAmount) {
			throw new BIException('invalid_filter', 'The minimum amount must not be greater than the maximum amount.', 400);
		}

		$page = isset($input['page']) ? (int) $input['page'] : 1;
		$pageSize = isset($input['pageSize']) ? (int) $input['pageSize'] : 50;
		if ($page < 1 || $page > 100000) {
			throw new BIException('invalid_request', 'The requested transaction page is not valid.', 400);
		}
		if ($pageSize < 10 || $pageSize > 200) {
			throw new BIException('invalid_request', 'Transaction page size must be between 10 and 200.', 400);
		}
		$sort = isset($input['sort']) ? (string) $input['sort'] : 'date';
		$allowedSorts = array('date', 'amount', 'category', 'description', 'owner', 'status');
		if (!in_array($sort, $allowedSorts, true)) {
			throw new BIException('invalid_request', 'The requested transaction sort is not supported.', 400);
		}
		$direction = isset($input['direction']) ? strtolower((string) $input['direction']) : 'desc';
		if (!in_array($direction, array('asc', 'desc'), true)) {
			throw new BIException('invalid_request', 'Sort direction must be asc or desc.', 400);
		}

		return new self(array(
			'date_range' => array('start' => $start, 'end' => $end),
			'category' => $category,
			'cost_center' => self::optionalString($input, 'costCenter', 20),
			'tab_code' => self::optionalString($input, 'tabCode', 20),
			'user_code' => self::optionalString($input, 'userCode', 50),
			'expense_code' => self::optionalString($input, 'expenseCode', 80),
			'gl_account' => self::optionalString($input, 'glAccount', 80),
			'account_group' => self::optionalString($input, 'accountGroup', 120),
			'section' => self::optionalString($input, 'section', 120),
			'spend_class' => $spendClass,
			'status' => $status,
			'receipt' => $receipt,
			'entry_kind' => $entryKind,
			'currency' => $currency,
			'search' => self::optionalString($input, 'search', 100),
			'include_local_purchases' => $includeLocalPurchases,
			'min_amount' => $minAmount,
			'max_amount' => $maxAmount,
			'page' => $page,
			'page_size' => $pageSize,
			'sort' => $sort,
			'direction' => $direction,
		));
	}

	public function getDateRange() { return $this->dateRange; }
	public function getCategory() { return $this->category; }
	public function getCostCenter() { return $this->costCenter; }
	public function getTabCode() { return $this->tabCode; }
	public function getUserCode() { return $this->userCode; }
	public function getExpenseCode() { return $this->expenseCode; }
	public function getGlAccount() { return $this->glAccount; }
	public function getAccountGroup() { return $this->accountGroup; }
	public function getSection() { return $this->section; }
	public function getSpendClass() { return $this->spendClass; }
	public function getStatus() { return $this->status; }
	public function getReceipt() { return $this->receipt; }
	public function getEntryKind() { return $this->entryKind; }
	public function getCurrency() { return $this->currency; }
	public function getSearch() { return $this->search; }
	public function getIncludeLocalPurchases() { return $this->includeLocalPurchases; }
	public function getMinAmount() { return $this->minAmount; }
	public function getMaxAmount() { return $this->maxAmount; }
	public function getPage() { return $this->page; }
	public function getPageSize() { return $this->pageSize; }
	public function getSort() { return $this->sort; }
	public function getDirection() { return $this->direction; }

	public function getComparisonRange()
	{
		$start = new \DateTimeImmutable($this->dateRange['start']);
		$end = new \DateTimeImmutable($this->dateRange['end']);
		$days = (int) $start->diff($end)->format('%a') + 1;
		$comparisonEnd = $start->modify('-1 day');
		$comparisonStart = $comparisonEnd->modify('-' . ($days - 1) . ' days');
		return array('start' => $comparisonStart->format('Y-m-d'), 'end' => $comparisonEnd->format('Y-m-d'));
	}

	public function toArray()
	{
		return array(
			'date_range' => $this->dateRange,
			'category' => $this->category,
			'cost_center' => $this->costCenter,
			'tab_code' => $this->tabCode,
			'user_code' => $this->userCode,
			'expense_code' => $this->expenseCode,
			'gl_account' => $this->glAccount,
			'account_group' => $this->accountGroup,
			'section' => $this->section,
			'spend_class' => $this->spendClass,
			'status' => $this->status,
			'receipt' => $this->receipt,
			'entry_kind' => $this->entryKind,
			'currency' => $this->currency,
			'search' => $this->search,
			'include_local_purchases' => $this->includeLocalPurchases,
			'min_amount' => $this->minAmount,
			'max_amount' => $this->maxAmount,
			'page' => $this->page,
			'page_size' => $this->pageSize,
			'sort' => $this->sort,
			'direction' => $this->direction,
		);
	}

	private static function optionalString(array $input, $key, $maximumLength)
	{
		if (!isset($input[$key]) || trim((string) $input[$key]) === '') {
			return null;
		}
		$value = trim((string) $input[$key]);
		if (strlen($value) > $maximumLength) {
			throw new BIException('invalid_filter', 'A report filter is longer than allowed.', 400, array('filter' => $key));
		}
		return $value;
	}

	private static function optionalAmount(array $input, $key)
	{
		if (!isset($input[$key]) || trim((string) $input[$key]) === '') {
			return null;
		}
		$value = trim((string) $input[$key]);
		if (!is_numeric($value) || !is_finite((float) $value) || (float) $value < 0 || (float) $value > 1000000000000000) {
			throw new BIException('invalid_filter', 'An amount filter must be a non-negative number within the supported range.', 400, array('filter' => $key));
		}
		return (float) $value;
	}

	private static function optionalBoolean(array $input, $key, $default)
	{
		if (!array_key_exists($key, $input) || $input[$key] === null || $input[$key] === '') {
			return (bool) $default;
		}
		$value = $input[$key];
		if (is_bool($value)) {
			return $value;
		}
		if (is_int($value) || is_float($value)) {
			if ((int) $value === 1) { return true; }
			if ((int) $value === 0) { return false; }
		}
		$normalized = strtolower(trim((string) $value));
		if (in_array($normalized, array('1', 'true', 'yes', 'on'), true)) { return true; }
		if (in_array($normalized, array('0', 'false', 'no', 'off'), true)) { return false; }
		throw new BIException('invalid_filter', 'The local-purchase toggle must be true or false.', 400);
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
}
