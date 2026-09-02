<?php

namespace SAHamid\BI\Expense;

/**
 * Maps the ERP's very granular petty-cash codes into stable executive buckets.
 *
 * The GL group wins where it is specific (for example direct costs, marketing,
 * or a balance-sheet posting). Operating Expense is intentionally refined by
 * the account/code description so executives do not get one unhelpful bucket.
 */
class ExpenseCategoryClassifier
{
	const UNCLASSIFIED = 'Unclassified';

	private $categories = array(
		'Direct Costs & Materials',
		'Marketing & Events',
		'Travel & Fleet',
		'People & Welfare',
		'Facilities & Utilities',
		'Technology & Equipment',
		'Logistics & Office Supplies',
		'Taxes, Insurance & Compliance',
		'Community & Personal',
		'Capital & Advances',
		'Other & Miscellaneous',
		self::UNCLASSIFIED,
	);

	public function categories()
	{
		return $this->categories;
	}

	public function classify($expenseDescription, $accountName, $accountGroup)
	{
		$description = $this->normalize($expenseDescription);
		$account = $this->normalize($accountName);
		$group = $this->normalize($accountGroup);
		$text = trim($description . ' ' . $account);

		if ($description === '' && $account === '' && $group === '') {
			return self::UNCLASSIFIED;
		}

		if ($this->containsAny($group, array('current assets', 'fixed assets'))) {
			return 'Capital & Advances';
		}
		if ($this->containsAny($group, array('cost of goods sold', 'outward freight'))) {
			return 'Direct Costs & Materials';
		}
		if ($this->containsAny($group, array('marketing expenses', 'promotions', 'giveaways'))) {
			return 'Marketing & Events';
		}

		if ($this->containsAny($text, array('zakat', 'charity', 'charitable', 'sadaqah', 'sadqah', 'masjid', 'chanda', 'personal'))) {
			return 'Community & Personal';
		}
		if ($this->containsAny($text, array('custom duty', 'tax', 'insurance', 'registration', 'regration', 'token', 'permit', 'license'))) {
			return 'Taxes, Insurance & Compliance';
		}
		if ($this->containsAny($text, array('expo', 'exhibition', 'stall', 'seminar', 'marketing', 'promotion', 'calendar', 'diaries', 'sales team activ', 'customer lunch'))) {
			return 'Marketing & Events';
		}
		if ($this->containsAny($text, array('fuel', 'oil change', 'vehicle', 'vehical', 'car ', ' car', 'travel', 'travell', 'tour ', 'toll', 'hotel', 'air fare', 'air travelling', 'automotive'))) {
			return 'Travel & Fleet';
		}
		if ($this->containsAny($text, array('salary', 'salaries', 'wage', 'overtime', 'over time', 'staff welfare', 'safety ', 'benefit', 'daily food', 'monthly lunch', 'refreshment', 'food expense', 'medicine'))) {
			return 'People & Welfare';
		}
		if ($this->containsAny($text, array('rent', 'electric', 'utilities', 'utility', 'gas bill', 'water bill', 'wateen', 'ptcl', 'telephone', 'internet', 'generator', 'building', 'office repair', 'office maintenance', 'sanitary', 'gardening', 'air conditioner', 'ac charges', 'paint work'))) {
			return 'Facilities & Utilities';
		}
		if ($this->containsAny($text, array('computer', 'software', 'erp', 'network', 'hardware', 'engineering equipment', 'accessor', 'equipment'))) {
			return 'Technology & Equipment';
		}
		if ($this->containsAny($text, array('courier', 'postage', 'freight', 'bilty', 'packing', 'stationery', 'stationary', 'printing', 'material shifting', 'unloading', 'office supplies'))) {
			return 'Logistics & Office Supplies';
		}
		if ($this->containsAny($text, array('cost of sales', 'cost of goods', 'purchase', 'material', 'labour', 'labor', 'production'))) {
			return 'Direct Costs & Materials';
		}

		return 'Other & Miscellaneous';
	}

	public function spendClass($accountGroup, $pandl)
	{
		if ($pandl !== null && (int) $pandl === 0) {
			return 'Balance sheet / non-P&L';
		}
		$group = $this->normalize($accountGroup);
		if ($this->containsAny($group, array('current assets', 'fixed assets', 'liabilities', 'financed'))) {
			return 'Balance sheet / non-P&L';
		}
		if ($group === '') {
			return 'Unclassified';
		}
		return 'P&L spend';
	}

	private function normalize($value)
	{
		$value = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
		$value = strtolower(trim(preg_replace('/\s+/', ' ', $value)));
		return $value;
	}

	private function containsAny($haystack, array $needles)
	{
		foreach ($needles as $needle) {
			if ($needle !== '' && strpos($haystack, $needle) !== false) {
				return true;
			}
		}
		return false;
	}
}
