<?php

namespace SAHamid\BI\Expense;

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Security\AuthorizationContext;

/**
 * Read-only expense analytics over the petty-cash claim ledger.
 *
 * pcashdetails is the reporting grain. ASSIGNCASH rows are funding transfers,
 * not expenses, and are excluded. Expense rows are normally negative, so net
 * spend is -amount; positive non-assignment rows remain visible as credits.
 */
class ExpenseReportService
{
	private $db;
	private $context;
	private $classifier;
	private $defaultCurrency;

	public function __construct($db, AuthorizationContext $context, ExpenseCategoryClassifier $classifier = null)
	{
		$this->db = $db;
		$this->context = $context;
		$this->classifier = $classifier === null ? new ExpenseCategoryClassifier() : $classifier;
		$this->defaultCurrency = $this->loadDefaultCurrency();
	}

	public function getReport(ExpenseReportRequest $request, $includeAllTransactions = false)
	{
		if (!$this->context->canUseSalesAnalytics()) {
			throw new BIException('forbidden', 'You are not authorized to use Business Intelligence.', 403);
		}

		$startedAt = microtime(true);
		$definitions = $this->loadExpenseDefinitions();
		$categoryFilter = $this->resolveCategoryFilter($request, $definitions);
		$currentWhere = $this->buildWhere($request, $request->getDateRange(), $categoryFilter, $definitions);
		$comparisonRange = $request->getComparisonRange();
		$comparisonWhere = $this->buildWhere($request, $comparisonRange, $categoryFilter, $definitions);

		$summary = $this->fetchSummary($currentWhere);
		$previousSummary = $this->fetchSummary($comparisonWhere);
		$summary['previous_period_total'] = $previousSummary['net_total'];
		$summary['change_amount'] = $summary['net_total'] - $previousSummary['net_total'];
		$summary['change_percent'] = $this->percentChange($summary['net_total'], $previousSummary['net_total']);

		$currentCodes = $this->fetchExpenseCodes($currentWhere);
		$previousCodes = $this->fetchExpenseCodes($comparisonWhere);
		$codes = $this->decorateExpenseCodes($currentCodes, $previousCodes, $definitions, $summary['net_total']);
		$categories = $this->rollUpCategories($codes, $summary['net_total']);
		$monthly = $this->fetchMonthly($currentWhere, $request->getDateRange());
		$statuses = $this->fetchStatuses($currentWhere, $summary['net_total']);
		$costCenters = $this->fetchCostCenters($currentWhere, $summary['net_total']);
		$owners = $this->fetchOwners($currentWhere, $summary['net_total']);
		$users = $this->fetchUsers($currentWhere, $comparisonWhere, $summary['net_total']);
		$userExpenses = $this->fetchUserExpenses($currentWhere, $comparisonWhere, $summary['net_total'], $definitions);
		$currencies = $this->fetchCurrencies($currentWhere, $summary['net_total']);
		$transactions = $this->fetchTransactions($currentWhere, $request, $includeAllTransactions);
		$options = $this->fetchFilterOptions($definitions);
		$insights = $this->buildInsights($summary, $categories, $owners);

		return array(
			'summary' => $summary,
			'insights' => $insights,
			'breakdowns' => array(
				'categories' => $categories,
				'monthly' => $monthly,
				'statuses' => $statuses,
				'cost_centers' => $costCenters,
				'owners' => $owners,
				'users' => $users,
				'user_expenses' => $userExpenses,
				'currencies' => $currencies,
				'expense_codes' => $codes,
			),
			'transactions' => array(
				'rows' => $transactions,
				'page' => $includeAllTransactions ? 1 : $request->getPage(),
				'page_size' => $includeAllTransactions ? count($transactions) : $request->getPageSize(),
				'total_rows' => $summary['transaction_count'],
				'total_pages' => $includeAllTransactions ? 1 : max(1, (int) ceil($summary['transaction_count'] / $request->getPageSize())),
			),
			'options' => $options,
			'metadata' => array(
				'generated_at_utc' => gmdate('Y-m-d H:i:s'),
				'elapsed_ms' => (int) round((microtime(true) - $startedAt) * 1000),
				'database_name' => $this->context->getDatabaseName(),
				'company_name' => $this->context->getCompanyName(),
				'default_currency' => $this->defaultCurrency,
				'access_scope' => $this->context->isAdministrator() ? 'All expense tabs' : 'Expense tabs assigned to ' . $this->context->getUserId(),
				'date_role' => 'pcashdetails.date (claim/expense date)',
				'amount_definition' => 'Net spend = negative of non-ASSIGNCASH claim amounts; positive non-assignment rows are credits.',
				'local_purchase_definition' => 'Local purchases are rows whose expense code or expense description contains “Local Purchase”.',
				'include_local_purchases' => $request->getIncludeLocalPurchases(),
				'currency_method' => 'Tab-currency amounts are divided by the current currencies.rate, matching petty-cash GL posting logic. Historical rates are not stored on claims.',
				'source_lineage' => array('pcashdetails', 'pcexpenses', 'pctabs', 'pctypetabs', 'currencies', 'chartmaster', 'accountgroups', 'accountsection', 'tags', 'www_users', 'expense_listing_access'),
				'comparison_range' => $comparisonRange,
				'filters' => $request->toArray(),
			),
		);
	}

	private function loadDefaultCurrency()
	{
		$rows = $this->queryRows('SELECT currencydefault FROM companies ORDER BY coycode LIMIT 1', '', array());
		return $rows && trim((string) $rows[0]['currencydefault']) !== '' ? trim($rows[0]['currencydefault']) : 'PKR';
	}

	private function loadExpenseDefinitions()
	{
		$sql = 'SELECT e.codeexpense, e.description, e.glaccount, e.tag,
			cm.accountname, cm.group_ AS account_group,
			ag.pandl, ag.parentgroupname, s.sectionname, t.tagdescription
			FROM pcexpenses e
			LEFT JOIN chartmaster cm ON cm.accountcode = e.glaccount
			LEFT JOIN accountgroups ag ON ag.groupname = cm.group_
			LEFT JOIN accountsection s ON s.sectionid = ag.sectioninaccounts
			LEFT JOIN tags t ON t.tagref = e.tag
			ORDER BY e.codeexpense';
		$definitions = array();
		foreach ($this->queryRows($sql, '', array()) as $row) {
			$row['category'] = $this->classifier->classify($row['description'], $row['accountname'], $row['account_group']);
			$row['spend_class'] = $this->classifier->spendClass($row['account_group'], $row['pandl']);
			$definitions[(string) $row['codeexpense']] = $row;
		}
		return $definitions;
	}

	private function resolveCategoryFilter(ExpenseReportRequest $request, array $definitions)
	{
		if ($request->getCategory() === null) {
			return null;
		}
		$codes = array();
		foreach ($definitions as $code => $definition) {
			if ($definition['category'] === $request->getCategory()) {
				$codes[] = $code;
			}
		}
		return array(
			'category' => $request->getCategory(),
			'codes' => $codes,
			'include_orphans' => $request->getCategory() === ExpenseCategoryClassifier::UNCLASSIFIED,
		);
	}

	private function buildWhere(ExpenseReportRequest $request, array $range, $categoryFilter, array $definitions)
	{
		$conditions = array("UPPER(TRIM(d.codeexpense)) <> 'ASSIGNCASH'", 'd.date BETWEEN ? AND ?');
		$types = 'ss';
		$params = array($range['start'], $range['end']);
		if (!$request->getIncludeLocalPurchases()) {
			$conditions[] = 'NOT ' . $this->localPurchaseSql();
		}

		if (!$this->context->isAdministrator()) {
			$conditions[] = 'EXISTS (SELECT 1 FROM expense_listing_access ela WHERE ela.user = ? AND ela.can_access = d.tabcode)';
			$types .= 's';
			$params[] = $this->context->getUserId();
		}
		if ($request->getCostCenter() !== null) {
			$conditions[] = 'pt.typetabcode = ?';
			$types .= 's';
			$params[] = $request->getCostCenter();
		}
		if ($request->getTabCode() !== null) {
			$conditions[] = 'd.tabcode = ?';
			$types .= 's';
			$params[] = $request->getTabCode();
		}
		if ($request->getUserCode() !== null) {
			if ($request->getUserCode() === '__unassigned__') {
				$conditions[] = "COALESCE(TRIM(pt.usercode), '') = ''";
			} else {
				$conditions[] = 'pt.usercode = ?';
				$types .= 's';
				$params[] = $request->getUserCode();
			}
		}
		if ($request->getExpenseCode() !== null) {
			$conditions[] = 'd.codeexpense = ?';
			$types .= 's';
			$params[] = $request->getExpenseCode();
		}
		if ($request->getGlAccount() !== null) {
			if ($request->getGlAccount() === '__unmapped__') {
				$conditions[] = "COALESCE(TRIM(e.glaccount), '') = ''";
			} else {
				$conditions[] = 'e.glaccount = ?';
				$types .= 's';
				$params[] = $request->getGlAccount();
			}
		}
		if ($request->getAccountGroup() !== null) {
			if ($request->getAccountGroup() === '__unmapped__') {
				$conditions[] = "COALESCE(TRIM(cm.group_), '') = ''";
			} else {
				$conditions[] = 'cm.group_ = ?';
				$types .= 's';
				$params[] = $request->getAccountGroup();
			}
		}
		if ($request->getSection() !== null) {
			if ($request->getSection() === '__unmapped__') {
				$conditions[] = "COALESCE(TRIM(sec.sectionname), '') = ''";
			} else {
				$conditions[] = 'sec.sectionname = ?';
				$types .= 's';
				$params[] = $request->getSection();
			}
		}
		if ($request->getSpendClass() !== null) {
			$spendCodes = array();
			foreach ($definitions as $code => $definition) {
				if ($definition['spend_class'] === $request->getSpendClass()) {
					$spendCodes[] = $code;
				}
			}
			$spendParts = array();
			if ($spendCodes) {
				$spendParts[] = 'd.codeexpense IN (' . implode(',', array_fill(0, count($spendCodes), '?')) . ')';
				$types .= str_repeat('s', count($spendCodes));
				foreach ($spendCodes as $code) {
					$params[] = $code;
				}
			}
			if ($request->getSpendClass() === 'Unclassified') {
				$spendParts[] = 'e.codeexpense IS NULL';
			}
			$conditions[] = $spendParts ? '(' . implode(' OR ', $spendParts) . ')' : '1 = 0';
		}
		if ($request->getStatus() === 'posted') {
			$conditions[] = 'd.posted = 1';
		} elseif ($request->getStatus() === 'pending_authorization') {
			$conditions[] = "d.posted <> 1 AND (d.authorized IS NULL OR d.authorized = '0000-00-00')";
		} elseif ($request->getStatus() === 'authorized_unposted') {
			$conditions[] = "d.posted <> 1 AND d.authorized IS NOT NULL AND d.authorized <> '0000-00-00'";
		}
		if ($request->getCurrency() !== null) {
			$conditions[] = 'pt.currency = ?';
			$types .= 's';
			$params[] = $request->getCurrency();
		}
		if ($request->getReceipt() === 'with_receipt') {
			$conditions[] = $this->receiptSql();
		} elseif ($request->getReceipt() === 'without_receipt') {
			$conditions[] = 'NOT ' . $this->receiptSql();
		}
		if ($request->getEntryKind() === 'credit') {
			$conditions[] = 'd.amount > 0';
		} elseif ($request->getEntryKind() === 'expense') {
			$conditions[] = 'd.amount <= 0';
		}
		$netAmount = $this->netAmountSql();
		if ($request->getMinAmount() !== null) {
			$conditions[] = 'ABS(' . $netAmount . ') >= ?';
			$types .= 'd';
			$params[] = $request->getMinAmount();
		}
		if ($request->getMaxAmount() !== null) {
			$conditions[] = 'ABS(' . $netAmount . ') <= ?';
			$types .= 'd';
			$params[] = $request->getMaxAmount();
		}
		if ($request->getSearch() !== null) {
			$search = '%' . $request->getSearch() . '%';
			$conditions[] = '(d.notes LIKE ? OR d.tabcode LIKE ? OR d.codeexpense LIKE ? OR e.description LIKE ? OR e.glaccount LIKE ? OR cm.accountname LIKE ? OR cm.group_ LIKE ? OR sec.sectionname LIKE ? OR pt.usercode LIKE ? OR wu.realname LIKE ?)';
			$types .= 'ssssssssss';
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
		}
		if ($categoryFilter !== null) {
			$parts = array();
			if ($categoryFilter['codes']) {
				$parts[] = 'd.codeexpense IN (' . implode(',', array_fill(0, count($categoryFilter['codes']), '?')) . ')';
				$types .= str_repeat('s', count($categoryFilter['codes']));
				foreach ($categoryFilter['codes'] as $code) {
					$params[] = $code;
				}
			}
			if ($categoryFilter['include_orphans']) {
				$parts[] = 'e.codeexpense IS NULL';
			}
			$conditions[] = $parts ? '(' . implode(' OR ', $parts) . ')' : '1 = 0';
		}

		return array('sql' => ' WHERE ' . implode(' AND ', $conditions), 'types' => $types, 'params' => $params);
	}

	private function fromSql()
	{
		return ' FROM pcashdetails d
			LEFT JOIN pcexpenses e ON e.codeexpense = d.codeexpense
			LEFT JOIN chartmaster cm ON cm.accountcode = e.glaccount
			LEFT JOIN accountgroups ag ON ag.groupname = cm.group_
			LEFT JOIN accountsection sec ON sec.sectionid = ag.sectioninaccounts
			LEFT JOIN tags tg ON tg.tagref = e.tag
			LEFT JOIN pctabs pt ON pt.tabcode = d.tabcode
			LEFT JOIN pctypetabs ptt ON ptt.typetabcode = pt.typetabcode
			LEFT JOIN currencies cur ON cur.currabrev = pt.currency
			LEFT JOIN www_users wu ON wu.userid = pt.usercode';
	}

	private function localPurchaseSql()
	{
		return "(UPPER(TRIM(COALESCE(d.codeexpense, ''))) LIKE '%LOCAL PURCHASE%' OR UPPER(TRIM(COALESCE(e.description, ''))) LIKE '%LOCAL PURCHASE%')";
	}

	private function ownerSql()
	{
		return "COALESCE(NULLIF(TRIM(wu.realname), ''), NULLIF(TRIM(pt.usercode), ''), 'Unassigned')";
	}

	private function netAmountSql()
	{
		return '(-1 * d.amount) / COALESCE(NULLIF(cur.rate, 0), 1)';
	}

	private function grossAmountSql()
	{
		return 'CASE WHEN d.amount < 0 THEN (-1 * d.amount) / COALESCE(NULLIF(cur.rate, 0), 1) ELSE 0 END';
	}

	private function creditAmountSql()
	{
		return 'CASE WHEN d.amount > 0 THEN d.amount / COALESCE(NULLIF(cur.rate, 0), 1) ELSE 0 END';
	}

	private function statusSql()
	{
		return "CASE WHEN d.posted = 1 THEN 'posted' WHEN d.authorized IS NULL OR d.authorized = '0000-00-00' THEN 'pending_authorization' ELSE 'authorized_unposted' END";
	}

	private function receiptSql()
	{
		return "(TRIM(COALESCE(d.receipt, '')) <> '' OR TRIM(COALESCE(d.receiptimage, '')) <> '')";
	}

	private function fetchSummary(array $where)
	{
		$net = $this->netAmountSql();
		$gross = $this->grossAmountSql();
		$credits = $this->creditAmountSql();
		$receipt = $this->receiptSql();
		$sql = 'SELECT
			COALESCE(SUM(' . $net . '), 0) AS net_total,
			COALESCE(SUM(' . $gross . '), 0) AS gross_outflow,
			COALESCE(SUM(' . $credits . '), 0) AS credits,
			COUNT(*) AS transaction_count,
			COUNT(DISTINCT d.codeexpense) AS expense_code_count,
			COUNT(DISTINCT d.tabcode) AS active_tab_count,
			COALESCE(SUM(CASE WHEN d.posted = 1 THEN ' . $net . ' ELSE 0 END), 0) AS posted_total,
			COALESCE(SUM(CASE WHEN d.posted <> 1 AND (d.authorized IS NULL OR d.authorized = \'0000-00-00\') THEN ' . $net . ' ELSE 0 END), 0) AS pending_authorization_total,
			COALESCE(SUM(CASE WHEN d.posted <> 1 AND d.authorized IS NOT NULL AND d.authorized <> \'0000-00-00\' THEN ' . $net . ' ELSE 0 END), 0) AS authorized_unposted_total,
			COALESCE(SUM(CASE WHEN ag.pandl = 1 THEN ' . $net . ' ELSE 0 END), 0) AS pnl_total,
			COALESCE(SUM(CASE WHEN ag.pandl = 0 THEN ' . $net . ' ELSE 0 END), 0) AS balance_sheet_total,
			SUM(CASE WHEN NOT ' . $receipt . ' THEN 1 ELSE 0 END) AS missing_receipt_count,
			SUM(CASE WHEN e.codeexpense IS NULL OR cm.accountcode IS NULL THEN 1 ELSE 0 END) AS unclassified_count,
			SUM(CASE WHEN pt.currency <> ? THEN 1 ELSE 0 END) AS foreign_currency_count,
			SUM(CASE WHEN cur.rate IS NULL OR cur.rate = 0 THEN 1 ELSE 0 END) AS missing_rate_count,
			SUM(CASE WHEN d.amount > 0 THEN 1 ELSE 0 END) AS credit_count'
			. $this->fromSql() . $where['sql'];
		$types = 's' . $where['types'];
		$params = array_merge(array($this->defaultCurrency), $where['params']);
		$rows = $this->queryRows($sql, $types, $params);
		$row = $rows ? $rows[0] : array();
		$numeric = array('net_total', 'gross_outflow', 'credits', 'posted_total', 'pending_authorization_total', 'authorized_unposted_total', 'pnl_total', 'balance_sheet_total');
		foreach ($numeric as $field) {
			$row[$field] = isset($row[$field]) ? (float) $row[$field] : 0.0;
		}
		$integers = array('transaction_count', 'expense_code_count', 'active_tab_count', 'missing_receipt_count', 'unclassified_count', 'foreign_currency_count', 'missing_rate_count', 'credit_count');
		foreach ($integers as $field) {
			$row[$field] = isset($row[$field]) ? (int) $row[$field] : 0;
		}
		$row['average_transaction'] = $row['transaction_count'] > 0 ? $row['net_total'] / $row['transaction_count'] : 0.0;
		$row['receipt_coverage_percent'] = $row['transaction_count'] > 0 ? (($row['transaction_count'] - $row['missing_receipt_count']) / $row['transaction_count']) * 100 : 100.0;
		$row['action_required_total'] = $row['pending_authorization_total'] + $row['authorized_unposted_total'];
		return $row;
	}

	private function fetchExpenseCodes(array $where)
	{
		$net = $this->netAmountSql();
		$sql = 'SELECT d.codeexpense, COALESCE(e.description, \'Unmapped expense code\') AS description,
			e.glaccount, cm.accountname, cm.group_ AS account_group, ag.pandl,
			ag.parentgroupname, sec.sectionname, e.tag, tg.tagdescription,
			COALESCE(SUM(' . $net . '), 0) AS total,
			COALESCE(SUM(' . $this->grossAmountSql() . '), 0) AS gross_outflow,
			COALESCE(SUM(' . $this->creditAmountSql() . '), 0) AS credits,
			COUNT(*) AS transaction_count,
			COALESCE(SUM(CASE WHEN d.posted = 1 THEN ' . $net . ' ELSE 0 END), 0) AS posted_total,
			COALESCE(SUM(CASE WHEN d.posted <> 1 AND (d.authorized IS NULL OR d.authorized = \'0000-00-00\') THEN ' . $net . ' ELSE 0 END), 0) AS pending_total,
			COALESCE(SUM(CASE WHEN d.posted <> 1 AND d.authorized IS NOT NULL AND d.authorized <> \'0000-00-00\' THEN ' . $net . ' ELSE 0 END), 0) AS authorized_unposted_total'
			. $this->fromSql() . $where['sql']
			. ' GROUP BY d.codeexpense, e.description, e.glaccount, cm.accountname, cm.group_, ag.pandl, ag.parentgroupname, sec.sectionname, e.tag, tg.tagdescription';
		return $this->queryRows($sql, $where['types'], $where['params']);
	}

	private function decorateExpenseCodes(array $current, array $previous, array $definitions, $grandTotal)
	{
		$previousByCode = array();
		foreach ($previous as $row) {
			$previousByCode[(string) $row['codeexpense']] = (float) $row['total'];
		}
		$rows = array();
		foreach ($current as $row) {
			$code = (string) $row['codeexpense'];
			$definition = isset($definitions[$code]) ? $definitions[$code] : null;
			$row['category'] = $definition ? $definition['category'] : ExpenseCategoryClassifier::UNCLASSIFIED;
			$row['spend_class'] = $definition ? $definition['spend_class'] : 'Unclassified';
			foreach (array('total', 'gross_outflow', 'credits', 'posted_total', 'pending_total', 'authorized_unposted_total') as $field) {
				$row[$field] = (float) $row[$field];
			}
			$row['transaction_count'] = (int) $row['transaction_count'];
			$row['previous_total'] = isset($previousByCode[$code]) ? $previousByCode[$code] : 0.0;
			$row['change_amount'] = $row['total'] - $row['previous_total'];
			$row['change_percent'] = $this->percentChange($row['total'], $row['previous_total']);
			$row['share_percent'] = $grandTotal != 0 ? ($row['total'] / $grandTotal) * 100 : 0.0;
			$rows[] = $row;
		}
		usort($rows, function ($left, $right) {
			return $left['total'] == $right['total'] ? strcmp($left['description'], $right['description']) : ($left['total'] < $right['total'] ? 1 : -1);
		});
		return $rows;
	}

	private function rollUpCategories(array $codes, $grandTotal)
	{
		$categories = array();
		foreach ($codes as $code) {
			$name = $code['category'];
			if (!isset($categories[$name])) {
				$categories[$name] = array(
					'category' => $name, 'total' => 0.0, 'gross_outflow' => 0.0, 'credits' => 0.0,
					'previous_total' => 0.0, 'transaction_count' => 0, 'posted_total' => 0.0,
					'pending_total' => 0.0, 'authorized_unposted_total' => 0.0, 'expense_code_count' => 0,
				);
			}
			foreach (array('total', 'gross_outflow', 'credits', 'previous_total', 'posted_total', 'pending_total', 'authorized_unposted_total') as $field) {
				$categories[$name][$field] += $code[$field];
			}
			$categories[$name]['transaction_count'] += $code['transaction_count'];
			$categories[$name]['expense_code_count']++;
		}
		$rows = array_values($categories);
		foreach ($rows as &$row) {
			$row['change_amount'] = $row['total'] - $row['previous_total'];
			$row['change_percent'] = $this->percentChange($row['total'], $row['previous_total']);
			$row['share_percent'] = $grandTotal != 0 ? ($row['total'] / $grandTotal) * 100 : 0.0;
		}
		unset($row);
		usort($rows, function ($left, $right) { return $left['total'] < $right['total'] ? 1 : ($left['total'] > $right['total'] ? -1 : 0); });
		return $rows;
	}

	private function fetchMonthly(array $where, array $range)
	{
		$sql = "SELECT DATE_FORMAT(d.date, '%Y-%m') AS period, COALESCE(SUM(" . $this->netAmountSql() . '), 0) AS total,
			COALESCE(SUM(' . $this->grossAmountSql() . '), 0) AS gross_outflow,
			COALESCE(SUM(' . $this->creditAmountSql() . '), 0) AS credits, COUNT(*) AS transaction_count'
			. $this->fromSql() . $where['sql'] . ' GROUP BY DATE_FORMAT(d.date, \'%Y-%m\') ORDER BY period';
		$byPeriod = array();
		foreach ($this->queryRows($sql, $where['types'], $where['params']) as $row) {
			$row['total'] = (float) $row['total'];
			$row['gross_outflow'] = (float) $row['gross_outflow'];
			$row['credits'] = (float) $row['credits'];
			$row['transaction_count'] = (int) $row['transaction_count'];
			$byPeriod[$row['period']] = $row;
		}
		$rows = array();
		$cursor = new \DateTimeImmutable(substr($range['start'], 0, 7) . '-01');
		$last = new \DateTimeImmutable(substr($range['end'], 0, 7) . '-01');
		while ($cursor <= $last) {
			$key = $cursor->format('Y-m');
			$rows[] = isset($byPeriod[$key]) ? $byPeriod[$key] : array('period' => $key, 'total' => 0.0, 'gross_outflow' => 0.0, 'credits' => 0.0, 'transaction_count' => 0);
			$cursor = $cursor->modify('+1 month');
		}
		return $rows;
	}

	private function fetchStatuses(array $where, $grandTotal)
	{
		$status = $this->statusSql();
		$sql = 'SELECT ' . $status . ' AS workflow_status, COALESCE(SUM(' . $this->netAmountSql() . '), 0) AS total, COUNT(*) AS transaction_count'
			. $this->fromSql() . $where['sql'] . ' GROUP BY ' . $status;
		return $this->normalizeBreakdown($this->queryRows($sql, $where['types'], $where['params']), $grandTotal);
	}

	private function fetchCostCenters(array $where, $grandTotal)
	{
		$sql = "SELECT COALESCE(pt.typetabcode, '') AS cost_center_code, COALESCE(ptt.typetabdescription, 'Unassigned') AS cost_center,
			COALESCE(SUM(" . $this->netAmountSql() . '), 0) AS total, COUNT(*) AS transaction_count'
			. $this->fromSql() . $where['sql'] . ' GROUP BY pt.typetabcode, ptt.typetabdescription ORDER BY total DESC';
		return $this->normalizeBreakdown($this->queryRows($sql, $where['types'], $where['params']), $grandTotal);
	}

	private function fetchOwners(array $where, $grandTotal)
	{
		$sql = "SELECT d.tabcode, COALESCE(pt.usercode, '') AS usercode,
			COALESCE(NULLIF(TRIM(wu.realname), ''), NULLIF(TRIM(pt.usercode), ''), 'Unassigned') AS owner,
			COALESCE(ptt.typetabdescription, 'Unassigned') AS cost_center,
			COALESCE(SUM(" . $this->netAmountSql() . '), 0) AS total, COUNT(*) AS transaction_count'
			. $this->fromSql() . $where['sql'] . ' GROUP BY d.tabcode, pt.usercode, wu.realname, ptt.typetabdescription ORDER BY total DESC';
		return $this->normalizeBreakdown($this->queryRows($sql, $where['types'], $where['params']), $grandTotal);
	}

	private function fetchUsers(array $where, array $previousWhere, $grandTotal)
	{
		$current = $this->queryUserTotals($where);
		$previous = $this->queryUserTotals($previousWhere);
		$previousByUser = array();
		foreach ($previous as $row) {
			$previousByUser[$this->userKey($row)] = (float) $row['total'];
		}
		$rows = array();
		$numeric = array('total', 'gross_outflow', 'credits', 'posted_total', 'pending_total', 'authorized_unposted_total', 'pnl_total', 'balance_sheet_total');
		$integers = array('transaction_count', 'tab_count', 'expense_code_count', 'missing_receipt_count');
		foreach ($current as $row) {
			foreach ($numeric as $field) { $row[$field] = (float) $row[$field]; }
			foreach ($integers as $field) { $row[$field] = (int) $row[$field]; }
			$row['user_key'] = $this->userKey($row);
			$row['previous_total'] = isset($previousByUser[$row['user_key']]) ? $previousByUser[$row['user_key']] : 0.0;
			$row['change_amount'] = $row['total'] - $row['previous_total'];
			$row['change_percent'] = $this->percentChange($row['total'], $row['previous_total']);
			$row['share_percent'] = $grandTotal != 0 ? ($row['total'] / $grandTotal) * 100 : 0.0;
			$row['receipt_coverage_percent'] = $row['transaction_count'] > 0 ? (($row['transaction_count'] - $row['missing_receipt_count']) / $row['transaction_count']) * 100 : 100.0;
			$rows[] = $row;
		}
		usort($rows, function ($left, $right) {
			return $left['total'] == $right['total'] ? strcmp($left['owner'], $right['owner']) : ($left['total'] < $right['total'] ? 1 : -1);
		});
		return $rows;
	}

	private function queryUserTotals(array $where)
	{
		$owner = $this->ownerSql();
		$net = $this->netAmountSql();
		$sql = 'SELECT ' . $owner . ' AS owner, COALESCE(pt.usercode, \'\') AS usercode,
			COALESCE(SUM(' . $net . '), 0) AS total,
			COALESCE(SUM(' . $this->grossAmountSql() . '), 0) AS gross_outflow,
			COALESCE(SUM(' . $this->creditAmountSql() . '), 0) AS credits,
			COUNT(*) AS transaction_count, COUNT(DISTINCT d.tabcode) AS tab_count,
			COUNT(DISTINCT d.codeexpense) AS expense_code_count,
			COALESCE(SUM(CASE WHEN d.posted = 1 THEN ' . $net . ' ELSE 0 END), 0) AS posted_total,
			COALESCE(SUM(CASE WHEN d.posted <> 1 AND (d.authorized IS NULL OR d.authorized = \'0000-00-00\') THEN ' . $net . ' ELSE 0 END), 0) AS pending_total,
			COALESCE(SUM(CASE WHEN d.posted <> 1 AND d.authorized IS NOT NULL AND d.authorized <> \'0000-00-00\' THEN ' . $net . ' ELSE 0 END), 0) AS authorized_unposted_total,
			COALESCE(SUM(CASE WHEN ag.pandl = 1 THEN ' . $net . ' ELSE 0 END), 0) AS pnl_total,
			COALESCE(SUM(CASE WHEN ag.pandl = 0 THEN ' . $net . ' ELSE 0 END), 0) AS balance_sheet_total,
			SUM(CASE WHEN NOT ' . $this->receiptSql() . ' THEN 1 ELSE 0 END) AS missing_receipt_count'
			. $this->fromSql() . $where['sql'] . ' GROUP BY pt.usercode, wu.realname ORDER BY total DESC';
		return $this->queryRows($sql, $where['types'], $where['params']);
	}

	private function fetchUserExpenses(array $where, array $previousWhere, $grandTotal, array $definitions)
	{
		$current = $this->queryUserExpenseDetails($where);
		$previous = $this->queryUserExpenseDetails($previousWhere);
		$previousByKey = array();
		foreach ($previous as $row) {
			$previousByKey[$this->userExpenseKey($row)] = (float) $row['total'];
		}
		$rows = array();
		foreach ($current as $row) {
			$code = (string) $row['codeexpense'];
			$definition = isset($definitions[$code]) ? $definitions[$code] : null;
			$row['category'] = $definition ? $definition['category'] : ExpenseCategoryClassifier::UNCLASSIFIED;
			$row['spend_class'] = $definition ? $definition['spend_class'] : 'Unclassified';
			foreach (array('total', 'gross_outflow', 'credits', 'posted_total', 'pending_total', 'authorized_unposted_total') as $field) { $row[$field] = (float) $row[$field]; }
			foreach (array('transaction_count', 'tab_count') as $field) { $row[$field] = (int) $row[$field]; }
			$row['user_key'] = $this->userKey($row);
			$row['previous_total'] = isset($previousByKey[$this->userExpenseKey($row)]) ? $previousByKey[$this->userExpenseKey($row)] : 0.0;
			$row['change_amount'] = $row['total'] - $row['previous_total'];
			$row['change_percent'] = $this->percentChange($row['total'], $row['previous_total']);
			$row['share_percent'] = $grandTotal != 0 ? ($row['total'] / $grandTotal) * 100 : 0.0;
			$rows[] = $row;
		}
		usort($rows, function ($left, $right) {
			if ($left['total'] == $right['total']) {
				return strcmp($left['owner'] . '|' . $left['description'], $right['owner'] . '|' . $right['description']);
			}
			return $left['total'] < $right['total'] ? 1 : -1;
		});
		return $rows;
	}

	private function queryUserExpenseDetails(array $where)
	{
		$owner = $this->ownerSql();
		$net = $this->netAmountSql();
		$sql = 'SELECT ' . $owner . ' AS owner, COALESCE(pt.usercode, \'\') AS usercode,
			d.codeexpense, COALESCE(e.description, \'Unmapped expense code\') AS description,
			e.glaccount, cm.accountname, cm.group_ AS account_group, ag.pandl,
			ag.parentgroupname, sec.sectionname, e.tag, tg.tagdescription,
			COALESCE(SUM(' . $net . '), 0) AS total,
			COALESCE(SUM(' . $this->grossAmountSql() . '), 0) AS gross_outflow,
			COALESCE(SUM(' . $this->creditAmountSql() . '), 0) AS credits,
			COUNT(*) AS transaction_count, COUNT(DISTINCT d.tabcode) AS tab_count,
			COALESCE(SUM(CASE WHEN d.posted = 1 THEN ' . $net . ' ELSE 0 END), 0) AS posted_total,
			COALESCE(SUM(CASE WHEN d.posted <> 1 AND (d.authorized IS NULL OR d.authorized = \'0000-00-00\') THEN ' . $net . ' ELSE 0 END), 0) AS pending_total,
			COALESCE(SUM(CASE WHEN d.posted <> 1 AND d.authorized IS NOT NULL AND d.authorized <> \'0000-00-00\' THEN ' . $net . ' ELSE 0 END), 0) AS authorized_unposted_total'
			. $this->fromSql() . $where['sql'] . ' GROUP BY pt.usercode, wu.realname, d.codeexpense, e.description, e.glaccount, cm.accountname, cm.group_, ag.pandl, ag.parentgroupname, sec.sectionname, e.tag, tg.tagdescription ORDER BY total DESC';
		return $this->queryRows($sql, $where['types'], $where['params']);
	}

	private function userKey(array $row)
	{
		return trim((string) $row['usercode']) !== '' ? 'user:' . trim((string) $row['usercode']) : 'owner:' . strtolower(trim((string) $row['owner']));
	}

	private function userExpenseKey(array $row)
	{
		return $this->userKey($row) . '|expense:' . (string) $row['codeexpense'];
	}

	private function fetchCurrencies(array $where, $grandTotal)
	{
		$sql = "SELECT COALESCE(pt.currency, '" . $this->escapeSqlLiteral($this->defaultCurrency) . "') AS currency,
			COALESCE(NULLIF(cur.rate, 0), 1) AS current_rate,
			COALESCE(SUM(-1 * d.amount), 0) AS original_total,
			COALESCE(SUM(" . $this->netAmountSql() . '), 0) AS total, COUNT(*) AS transaction_count'
			. $this->fromSql() . $where['sql'] . ' GROUP BY pt.currency, cur.rate ORDER BY total DESC';
		$rows = $this->normalizeBreakdown($this->queryRows($sql, $where['types'], $where['params']), $grandTotal);
		foreach ($rows as &$row) {
			$row['current_rate'] = (float) $row['current_rate'];
			$row['original_total'] = (float) $row['original_total'];
		}
		unset($row);
		return $rows;
	}

	private function normalizeBreakdown(array $rows, $grandTotal)
	{
		foreach ($rows as &$row) {
			$row['total'] = (float) $row['total'];
			$row['transaction_count'] = (int) $row['transaction_count'];
			$row['share_percent'] = $grandTotal != 0 ? ($row['total'] / $grandTotal) * 100 : 0.0;
		}
		unset($row);
		return $rows;
	}

	private function fetchTransactions(array $where, ExpenseReportRequest $request, $includeAll)
	{
		$sorts = array(
			'date' => 'd.date',
			'amount' => 'functional_amount',
			'category' => 'cm.group_',
			'description' => 'e.description',
			'owner' => 'owner',
			'status' => 'workflow_status',
		);
		$order = $sorts[$request->getSort()] . ' ' . strtoupper($request->getDirection()) . ', d.counterindex DESC';
		$sql = 'SELECT d.counterindex, d.date, d.codeexpense,
			COALESCE(e.description, \'Unmapped expense code\') AS description,
			e.glaccount, cm.accountname, cm.group_ AS account_group, ag.pandl,
			ag.parentgroupname, sec.sectionname, e.tag, tg.tagdescription,
			d.tabcode, COALESCE(pt.typetabcode, \'\') AS cost_center_code,
			COALESCE(ptt.typetabdescription, \'Unassigned\') AS cost_center,
			COALESCE(pt.usercode, \'\') AS usercode,
			COALESCE(NULLIF(TRIM(wu.realname), \'\'), NULLIF(TRIM(pt.usercode), \'\'), \'Unassigned\') AS owner,
			COALESCE(pt.currency, ?) AS currency, COALESCE(NULLIF(cur.rate, 0), 1) AS current_rate,
			(-1 * d.amount) AS original_amount, ' . $this->netAmountSql() . ' AS functional_amount,
			CASE WHEN d.amount > 0 THEN \'credit\' ELSE \'expense\' END AS entry_kind,
			' . $this->statusSql() . ' AS workflow_status,
			NULLIF(d.authorized, \'0000-00-00\') AS authorized_date, d.posted,
			d.notes, d.receipt AS receipt_reference, d.receiptimage AS receipt_image,
			CASE WHEN ' . $this->receiptSql() . ' THEN 1 ELSE 0 END AS has_receipt'
			. $this->fromSql() . $where['sql'] . ' ORDER BY ' . $order;
		$params = array_merge(array($this->defaultCurrency), $where['params']);
		$types = 's' . $where['types'];
		if (!$includeAll) {
			$offset = ($request->getPage() - 1) * $request->getPageSize();
			$sql .= ' LIMIT ' . (int) $request->getPageSize() . ' OFFSET ' . (int) $offset;
		}
		$rows = $this->queryRows($sql, $types, $params);
		foreach ($rows as &$row) {
			$row['category'] = $this->classifier->classify($row['description'], $row['accountname'], $row['account_group']);
			$row['spend_class'] = $this->classifier->spendClass($row['account_group'], $row['pandl']);
			$row['counterindex'] = (int) $row['counterindex'];
			$row['original_amount'] = (float) $row['original_amount'];
			$row['functional_amount'] = (float) $row['functional_amount'];
			$row['current_rate'] = (float) $row['current_rate'];
			$row['posted'] = (int) $row['posted'];
			$row['has_receipt'] = (bool) $row['has_receipt'];
		}
		unset($row);
		return $rows;
	}

	private function fetchFilterOptions(array $definitions)
	{
		$where = array("UPPER(TRIM(d.codeexpense)) <> 'ASSIGNCASH'");
		$types = '';
		$params = array();
		if (!$this->context->isAdministrator()) {
			$where[] = 'EXISTS (SELECT 1 FROM expense_listing_access ela WHERE ela.user = ? AND ela.can_access = d.tabcode)';
			$types = 's';
			$params[] = $this->context->getUserId();
		}
		$sql = "SELECT DISTINCT d.tabcode, d.codeexpense,
			COALESCE(e.description, 'Unmapped expense code') AS expense_description,
			COALESCE(e.glaccount, '') AS gl_account,
			COALESCE(cm.group_, '') AS account_group,
			COALESCE(sec.sectionname, '') AS section,
			COALESCE(pt.usercode, '') AS usercode,
			COALESCE(NULLIF(TRIM(wu.realname), ''), NULLIF(TRIM(pt.usercode), ''), 'Unassigned') AS owner,
			COALESCE(pt.typetabcode, '') AS cost_center_code,
			COALESCE(ptt.typetabdescription, 'Unassigned') AS cost_center,
			COALESCE(pt.currency, '" . $this->escapeSqlLiteral($this->defaultCurrency) . "') AS currency"
			. $this->fromSql() . ' WHERE ' . implode(' AND ', $where)
			. ' ORDER BY cost_center, d.tabcode';
		$rows = $this->queryRows($sql, $types, $params);
		$centers = array();
		$tabs = array();
		$currencies = array();
		$users = array();
		$expenseCodes = array();
		$glAccounts = array();
		$accountGroups = array();
		$sections = array();
		foreach ($rows as $row) {
			$centerKey = (string) $row['cost_center_code'];
			$centers[$centerKey] = array('value' => $centerKey, 'label' => trim($row['cost_center']));
			$tabs[(string) $row['tabcode']] = array('value' => (string) $row['tabcode'], 'label' => trim($row['tabcode']));
			$currencies[(string) $row['currency']] = array('value' => (string) $row['currency'], 'label' => (string) $row['currency']);
			$userCode = trim((string) $row['usercode']);
			$userValue = $userCode !== '' ? $userCode : '__unassigned__';
			$users[$userValue] = array('value' => $userValue, 'label' => trim((string) $row['owner']) . ($userCode !== '' ? ' (' . $userCode . ')' : ''));
			$expenseCode = trim((string) $row['codeexpense']);
			if ($expenseCode !== '') {
				$expenseCodes[$expenseCode] = array('value' => $expenseCode, 'label' => $expenseCode . ' · ' . trim((string) $row['expense_description']));
			}
			$glAccount = trim((string) $row['gl_account']);
			$glAccounts[$glAccount !== '' ? $glAccount : '__unmapped__'] = array('value' => $glAccount !== '' ? $glAccount : '__unmapped__', 'label' => $glAccount !== '' ? $glAccount : 'Unmapped GL account');
			$accountGroup = trim((string) $row['account_group']);
			$accountGroups[$accountGroup !== '' ? $accountGroup : '__unmapped__'] = array('value' => $accountGroup !== '' ? $accountGroup : '__unmapped__', 'label' => $accountGroup !== '' ? $accountGroup : 'Unmapped account group');
			$section = trim((string) $row['section']);
			$sections[$section !== '' ? $section : '__unmapped__'] = array('value' => $section !== '' ? $section : '__unmapped__', 'label' => $section !== '' ? $section : 'Unmapped section');
		}
		$presentCategories = array();
		foreach ($definitions as $definition) {
			$presentCategories[$definition['category']] = true;
		}
		$categoryOptions = array();
		foreach ($this->classifier->categories() as $category) {
			if (isset($presentCategories[$category]) || $category === ExpenseCategoryClassifier::UNCLASSIFIED) {
				$categoryOptions[] = array('value' => $category, 'label' => $category);
			}
		}
		return array(
			'categories' => $categoryOptions,
			'cost_centers' => array_values($centers),
			'tabs' => array_values($tabs),
			'users' => array_values($users),
			'expense_codes' => array_values($expenseCodes),
			'gl_accounts' => array_values($glAccounts),
			'account_groups' => array_values($accountGroups),
			'sections' => array_values($sections),
			'spend_classes' => array(
				array('value' => 'P&L spend', 'label' => 'P&L spend'),
				array('value' => 'Balance sheet / non-P&L', 'label' => 'Balance sheet / non-P&L'),
				array('value' => 'Unclassified', 'label' => 'Unclassified'),
			),
			'currencies' => array_values($currencies),
			'statuses' => array(
				array('value' => 'pending_authorization', 'label' => 'Pending authorization'),
				array('value' => 'authorized_unposted', 'label' => 'Authorized, not posted'),
				array('value' => 'posted', 'label' => 'Posted to GL'),
			),
			'receipts' => array(
				array('value' => 'with_receipt', 'label' => 'With receipt'),
				array('value' => 'without_receipt', 'label' => 'Missing receipt'),
			),
			'entry_kinds' => array(
				array('value' => 'expense', 'label' => 'Expenses only'),
				array('value' => 'credit', 'label' => 'Credits only'),
			),
		);
	}

	private function buildInsights(array $summary, array $categories, array $owners)
	{
		$insights = array();
		if ($summary['transaction_count'] === 0) {
			return array(array('tone' => 'neutral', 'title' => 'No spend in this view', 'detail' => 'No expense claims match the selected dates and filters.'));
		}
		if ($summary['change_percent'] !== null) {
			$direction = $summary['change_amount'] >= 0 ? 'increased' : 'decreased';
			$insights[] = array(
				'tone' => $summary['change_amount'] > 0 ? 'warning' : 'positive',
				'title' => 'Spend ' . $direction . ' ' . number_format(abs($summary['change_percent']), 1) . '%',
				'detail' => 'Compared with the immediately preceding period of equal length.',
			);
		}
		if ($categories) {
			$top = $categories[0];
			$insights[] = array(
				'tone' => 'neutral',
				'title' => $top['category'] . ' is the largest category',
				'detail' => number_format($top['share_percent'], 1) . '% of net spend across ' . number_format($top['transaction_count']) . ' transactions.',
			);
			$topThree = array_slice($categories, 0, 3);
			$concentration = 0.0;
			foreach ($topThree as $category) { $concentration += $category['share_percent']; }
			if ($concentration >= 75) {
				$insights[] = array('tone' => 'warning', 'title' => 'Spend is concentrated', 'detail' => 'The top three categories account for ' . number_format($concentration, 1) . '% of net spend.');
			}
		}
		if ($summary['action_required_total'] != 0) {
			$insights[] = array(
				'tone' => 'warning',
				'title' => 'Workflow action is required',
				'detail' => $this->defaultCurrency . ' ' . number_format($summary['action_required_total'], 0) . ' is pending authorization or GL posting.',
			);
		}
		if ($summary['receipt_coverage_percent'] < 90) {
			$insights[] = array(
				'tone' => 'warning',
				'title' => 'Receipt coverage is ' . number_format($summary['receipt_coverage_percent'], 1) . '%',
				'detail' => number_format($summary['missing_receipt_count']) . ' matching transactions do not have a receipt reference or image.',
			);
		}
		if ($owners) {
			$insights[] = array(
				'tone' => 'neutral',
				'title' => $owners[0]['owner'] . ' is the largest cost owner',
				'detail' => number_format($owners[0]['share_percent'], 1) . '% of net spend through ' . trim($owners[0]['tabcode']) . '.',
			);
		}
		return array_slice($insights, 0, 5);
	}

	private function percentChange($current, $previous)
	{
		if (abs((float) $previous) < 0.000001) {
			return null;
		}
		return (($current - $previous) / abs($previous)) * 100;
	}

	private function queryRows($sql, $types, array $params)
	{
		$stmt = @mysqli_prepare($this->db, $sql);
		if (!$stmt) {
			error_log('[bi-expense] prepare failed: ' . mysqli_error($this->db));
			throw new BIException('source_query_failed', 'The expense source query could not be prepared.', 503);
		}
		if ($types !== '') {
			$bind = array($types);
			foreach ($params as $index => $value) {
				$bind[] = &$params[$index];
			}
			if (!call_user_func_array(array($stmt, 'bind_param'), $bind)) {
				mysqli_stmt_close($stmt);
				throw new BIException('source_query_failed', 'The expense source filters could not be applied.', 503);
			}
		}
		if (!mysqli_stmt_execute($stmt)) {
			error_log('[bi-expense] execute failed: ' . mysqli_stmt_error($stmt));
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'The expense report query failed.', 503);
		}
		$result = mysqli_stmt_get_result($stmt);
		if (!$result) {
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'The expense report result could not be read.', 503);
		}
		$rows = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$rows[] = $row;
		}
		mysqli_free_result($result);
		mysqli_stmt_close($stmt);
		return $rows;
	}

	private function escapeSqlLiteral($value)
	{
		return mysqli_real_escape_string($this->db, (string) $value);
	}
}
