<?php

/**
 * Read-only petty-cash expense analytics endpoint.
 *
 * The endpoint deliberately keeps transfers into a petty-cash tab
 * (ASSIGNCASH) separate from expense claims. Expense claims are stored as
 * negative amounts in pcashdetails, so the report presents their positive
 * spend magnitude and keeps the source sign visible in the data-quality view.
 */

$BiRootPath = dirname(__DIR__, 3);
$PathPrefix = $BiRootPath . DIRECTORY_SEPARATOR;
include_once($BiRootPath . '/config.php');
if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
}
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$expenseReportStartedAt = microtime(true);

function expenseReportResponse(array $payload, $status)
{
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function expenseReportFail($code, $message, $status = 400, array $details = array())
{
	expenseReportResponse(array(
		'ok' => false,
		'error' => array(
			'code' => $code,
			'message' => $message,
			'details' => $details,
		),
	), $status);
}

function expenseReportRows($db, $sql, array $params = array())
{
	$stmt = mysqli_prepare($db, $sql);
	if (!$stmt) {
		throw new RuntimeException('The expense analytics query could not be prepared.');
	}

	if (count($params) > 0) {
		$types = str_repeat('s', count($params));
		$bind = array($stmt, $types);
		foreach ($params as $index => $value) {
			$params[$index] = (string) $value;
			$bind[] = &$params[$index];
		}
		if (!call_user_func_array('mysqli_stmt_bind_param', $bind)) {
			mysqli_stmt_close($stmt);
			throw new RuntimeException('The expense analytics filters could not be bound.');
		}
	}

	if (!mysqli_stmt_execute($stmt)) {
		mysqli_stmt_close($stmt);
		throw new RuntimeException('The expense analytics query could not be executed.');
	}

	$result = mysqli_stmt_get_result($stmt);
	if (!$result) {
		mysqli_stmt_close($stmt);
		throw new RuntimeException('The expense analytics result could not be read.');
	}

	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	mysqli_free_result($result);
	mysqli_stmt_close($stmt);
	return $rows;
}

function expenseReportValue(array $source, $key, $default = '')
{
	return isset($source[$key]) && !is_array($source[$key]) ? trim((string) $source[$key]) : $default;
}

function expenseReportKeywordMatch($text, $keyword)
{
	$keyword = trim(strtolower((string) $keyword));
	if ($keyword === '') {
		return false;
	}
	$pattern = '/(?<![a-z0-9])' . preg_quote($keyword, '/') . '(?![a-z0-9])/i';
	return preg_match($pattern, $text) === 1;
}

function expenseReportClassify($description, $code = '')
{
	$text = strtolower(trim((string) $description . ' ' . (string) $code));
	$rules = array(
		'Events & Exhibitions' => array('expo', 'exhibition', 'stall', 'hall payment', 'hall charges', 'shel space', 'seminar', 'trade show', 'display renovation', 'photo shoot', 'metal structure', 'led charges', 'carpet', 'event logistics', 'logistics', 'hvacr', 'pogee', 'iapax', 'ideas', 'ieeem', 'ieeep', 'irem'),
		'Charity & Community' => array('zakat', 'sadaqah', 'sadqa', 'masjid', 'chanda', 'faqeer', 'patri'),
		'Personal / Director' => array('director', 'personal'),
		'IT & Communications' => array('computer', 'accessories', 'erp', 'network', 'networking', 'website', 'web site', 'ptcl', 'wateen', 'internet', 'telephone', 'mobile'),
		'Staff & Welfare' => array('loan given to employees', 'overtime', 'over time', 'labour', 'labor', 'salary', 'salaries', 'employee', 'staff', 'welfare', 'medical', 'uniform', 'safety gloves', 'safety jackets', 'safety shoes', 'soap', 'tissue', 'training', 'eid', 'advance', 'allowance'),
		'Insurance & Registration' => array('insurance', 'registration'),
		'Customs & Compliance' => array('custom duty', 'customs', 'gst', 'withholding', 'tax'),
		'Purchases & Materials' => array('local purchase', 'bazar purchase', 'purchase', 'cost of goods sold', 'cogs', 'project accessories', 'engineeringequipment', 'engineering equipment', 'instrument', 'equipment', 'bending cost', 'hot dip galvanizing', 'paint', 'panel', 'sanitary', 'wood work'),
		'Fuel & Transport' => array('fuel', 'petrol', 'diesel', 'cng', 'oil', 'motor', 'vehicle', 'car', 'transport', 'travel fare', 'rickshaw', 'taxi', 'uber', 'parking', 'toll', 'courier', 'freight'),
		'Travel & Accommodation' => array('travel', 'travell', 'travelling', 'traveling', 'tour', 'visit', 'hotel', 'lodging', 'accommodation', 'ticket', 'airfare', 'flight', 'train', 'bus', 'visa', 'uae'),
		'Meals & Hospitality' => array('meal', 'food', 'lunch', 'dinner', 'breakfast', 'tea', 'refreshment', 'restaurant', 'hospitality', 'catering', 'channay'),
		'Office & Administration' => array('office', 'stationery', 'stationary', 'calendar', 'diary', 'diaries', 'paper', 'print', 'printing', 'photocopy', 'postage', 'software', 'subscription'),
		'Utilities & Facilities' => array('electric', 'electricity', 'utility', 'utilities', 'water', 'gas', 'rent', 'cleaning', 'security', 'facility', 'generator', 'bill', 'bills'),
		'Maintenance & Repairs' => array('repair', 'repairing', 'spare', 'workshop', 'service', 'maintenance', 'maintainance', 'maintance', 'parts', 'tools'),
		'Sales & Marketing' => array('marketing', 'advertising', 'designs', 'innovation', 'promotion', 'customer', 'sales', 'sample'),
		'Banking & Finance' => array('loan to non employees', 'bank', 'finance', 'account', 'credit card', 'fee', 'commission', 'stamp'),
		'Production & Operations' => array('production', 'factory', 'engineering', 'coil winding', 'winding', 'hattar', 'pepsi', 'heatec', 'material', 'packing', 'packaging', 'warehouse', 'inventory', 'quality', 'operations'),
	);
	foreach ($rules as $label => $keywords) {
		foreach ($keywords as $keyword) {
			if (expenseReportKeywordMatch($text, $keyword)) {
				return $label;
			}
		}
	}
	return 'Other / Review';
}

function expenseReportClassificationSignal($description, $code = '')
{
	$text = strtolower(trim((string) $description . ' ' . (string) $code));
	$signals = array('expo', 'exhibition', 'stall', 'logistics', 'hvacr', 'seminar', 'zakat', 'masjid', 'director', 'computer', 'network', 'website', 'loan given to employees', 'overtime', 'salary', 'safety', 'soap', 'tissue', 'insurance', 'registration', 'custom duty', 'purchase', 'equipment', 'instrument', 'paint', 'fuel', 'transport', 'car', 'travel', 'travelling', 'traveling', 'tour', 'visit', 'hotel', 'meal', 'food', 'channay', 'office', 'calendar', 'diary', 'diaries', 'electricity', 'utility', 'generator', 'bill', 'maintenance', 'maintainance', 'maintance', 'repair', 'repairing', 'staff', 'marketing', 'promotion', 'sales', 'loan to non employees', 'bank', 'account', 'production', 'engineering', 'coil winding', 'factory');
	foreach ($signals as $signal) {
		if (expenseReportKeywordMatch($text, $signal)) {
			return $signal;
		}
	}
	return 'no keyword match';
}

function expenseReportDate($value, $fallback)
{
	$value = trim((string) $value);
	if ($value === '') {
		return $fallback;
	}
	$date = DateTime::createFromFormat('!Y-m-d', $value);
	$errors = DateTime::getLastErrors();
	if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) {
		expenseReportFail('invalid_date', 'Dates must use the YYYY-MM-DD format.', 400, array('value' => $value));
	}
	return $value;
}

function expenseReportEnsureTables($db)
{
	$required = array('pcashdetails', 'pcexpenses', 'pctabs', 'pctypetabs', 'www_users', 'chartmaster', 'tags');
	$missing = array();
	foreach ($required as $table) {
		$escaped = mysqli_real_escape_string($db, $table);
		$result = mysqli_query($db, "SHOW TABLES LIKE '" . $escaped . "'");
		if (!$result) {
			throw new RuntimeException('The expense analytics schema check failed.');
		}
		$exists = mysqli_num_rows($result) > 0;
		mysqli_free_result($result);
		if (!$exists) {
			$missing[] = $table;
		}
	}
	if (count($missing) > 0) {
		expenseReportFail('schema_incompatible', 'Expense analytics is unavailable because the active ERP database is missing required tables.', 503, array('missing_tables' => $missing));
	}
}

function expenseReportWhere(array $input, array &$params)
{
	$filters = isset($input['filters']) && is_array($input['filters']) ? $input['filters'] : $input;
	$today = date('Y-m-d');
	$yearStart = date('Y-01-01');
	$start = expenseReportDate(expenseReportValue($input, 'startDate', expenseReportValue($input, 'start', $yearStart)), $yearStart);
	$end = expenseReportDate(expenseReportValue($input, 'endDate', expenseReportValue($input, 'end', $today)), $today);
	if ($start > $end) {
		expenseReportFail('invalid_date_range', 'The start date must be on or before the end date.');
	}

	$params = array($start, $end);
	$where = array(
		'pd.date >= ?',
		'pd.date <= ?',
		"UPPER(pd.codeexpense) <> 'ASSIGNCASH'",
	);

	$category = expenseReportValue($filters, 'category', expenseReportValue($filters, 'categoryCode'));
	$tab = expenseReportValue($filters, 'tab', expenseReportValue($filters, 'tabCode'));
	$owner = expenseReportValue($filters, 'owner', expenseReportValue($filters, 'ownerCode'));
	$auth = strtolower(expenseReportValue($filters, 'auth', expenseReportValue($filters, 'authStatus')));
	$posting = strtolower(expenseReportValue($filters, 'posting', expenseReportValue($filters, 'postingStatus')));
	$receiptStatus = strtolower(expenseReportValue($filters, 'receiptStatus', expenseReportValue($filters, 'receipt')));
	$gl = expenseReportValue($filters, 'gl', expenseReportValue($filters, 'glAccount'));
	$minSpend = expenseReportValue($filters, 'minSpend', expenseReportValue($filters, 'minimumSpend'));
	$maxSpend = expenseReportValue($filters, 'maxSpend', expenseReportValue($filters, 'maximumSpend'));
	$search = expenseReportValue($filters, 'search');

	if ($category !== '') {
		$where[] = 'pd.codeexpense = ?';
		$params[] = $category;
	}
	if ($tab !== '') {
		$where[] = 'pd.tabcode = ?';
		$params[] = $tab;
	}
	if ($owner !== '') {
		$where[] = 'pt.usercode = ?';
		$params[] = $owner;
	}
	if ($auth === 'authorized') {
		$where[] = "pd.authorized <> '0000-00-00'";
	} elseif ($auth === 'pending') {
		$where[] = "(pd.authorized = '0000-00-00' OR pd.authorized IS NULL)";
	} elseif ($auth !== '' && $auth !== 'all') {
		expenseReportFail('invalid_filter', 'Authorization status must be all, authorized, or pending.');
	}
	if ($posting === 'posted') {
		$where[] = 'pd.posted = 1';
	} elseif ($posting === 'unposted') {
		$where[] = '(pd.posted = 0 OR pd.posted IS NULL)';
	} elseif ($posting !== '' && $posting !== 'all') {
		expenseReportFail('invalid_filter', 'Posting status must be all, posted, or unposted.');
	}
	if ($receiptStatus === 'attached' || $receiptStatus === 'has_receipt') {
		$where[] = "(COALESCE(pd.receiptimage, '') <> '' OR COALESCE(pd.receipt, '') <> '')";
	} elseif ($receiptStatus === 'missing' || $receiptStatus === 'missing_receipt') {
		$where[] = "(COALESCE(pd.receiptimage, '') = '' AND COALESCE(pd.receipt, '') = '')";
	} elseif ($receiptStatus !== '' && $receiptStatus !== 'all') {
		expenseReportFail('invalid_filter', 'Receipt status must be all, attached, or missing.');
	}
	if ($gl !== '') {
		$where[] = 'pce.glaccount = ?';
		$params[] = $gl;
	}
	if ($minSpend !== '') {
		if (!is_numeric(str_replace(',', '', $minSpend))) {
			expenseReportFail('invalid_filter', 'Minimum spend must be numeric.');
		}
		$where[] = 'ABS(pd.amount) >= ?';
		$params[] = (float) str_replace(',', '', $minSpend);
	}
	if ($maxSpend !== '') {
		if (!is_numeric(str_replace(',', '', $maxSpend))) {
			expenseReportFail('invalid_filter', 'Maximum spend must be numeric.');
		}
		$where[] = 'ABS(pd.amount) <= ?';
		$params[] = (float) str_replace(',', '', $maxSpend);
	}
	if ($search !== '') {
		$like = '%' . $search . '%';
		$where[] = '(pd.notes LIKE ? OR pd.receipt LIKE ? OR pd.codeexpense LIKE ? OR pce.description LIKE ? OR pd.tabcode LIKE ?)';
		$params[] = $like;
		$params[] = $like;
		$params[] = $like;
		$params[] = $like;
		$params[] = $like;
	}

	return array('sql' => implode(' AND ', $where), 'params' => $params, 'start' => $start, 'end' => $end);
}

function expenseReportGroupDefinition($groupBy)
{
	$definitions = array(
		'category' => array(
			'key' => "COALESCE(NULLIF(pce.codeexpense, ''), 'UNMAPPED')",
			'label' => "COALESCE(NULLIF(pce.description, ''), 'Unmapped category')",
			'caption' => 'Expense category',
		),
		'gl' => array(
			'key' => "COALESCE(NULLIF(pce.glaccount, ''), 'UNMAPPED')",
			'label' => "COALESCE(NULLIF(cm.accountname, ''), NULLIF(pce.glaccount, ''), 'Unmapped GL account')",
			'caption' => 'GL account',
		),
		'owner' => array(
			'key' => "COALESCE(NULLIF(pt.usercode, ''), 'UNMAPPED')",
			'label' => "COALESCE(NULLIF(wu.realname, ''), NULLIF(pt.usercode, ''), 'Unmapped owner')",
			'caption' => 'Owner',
		),
		'tab' => array(
			'key' => "COALESCE(NULLIF(pd.tabcode, ''), 'UNMAPPED')",
			'label' => "COALESCE(NULLIF(pd.tabcode, ''), 'Unmapped tab')",
			'caption' => 'Petty-cash tab',
		),
		'tab_type' => array(
			'key' => "COALESCE(NULLIF(pt.typetabcode, ''), 'UNMAPPED')",
			'label' => "COALESCE(NULLIF(ptt.typetabdescription, ''), NULLIF(pt.typetabcode, ''), 'Unmapped tab type')",
			'caption' => 'Tab type',
		),
		'tag' => array(
			'key' => "COALESCE(CAST(pce.tag AS CHAR), '0')",
			'label' => "COALESCE(NULLIF(tg.tagdescription, ''), 'Untagged')",
			'caption' => 'Tag',
		),
		'enhanced_tag' => array(
			'key' => "COALESCE(NULLIF(pce.codeexpense, ''), 'UNMAPPED')",
			'label' => "COALESCE(NULLIF(pce.description, ''), 'Unmapped category')",
			'caption' => 'Enhanced classification',
		),
	);
	return isset($definitions[$groupBy]) ? $definitions[$groupBy] : $definitions['category'];
}

function expenseReportGroupedRows($db, $where, array $params, $groupBy, $limit)
{
	if ($groupBy === 'enhanced_tag') {
		$categoryRows = expenseReportGroupedRows($db, $where, $params, 'category', 500);
		$groups = array();
		foreach ($categoryRows as $row) {
			$tag = expenseReportClassify($row['group_label'], $row['group_key']);
			if (!isset($groups[$tag])) {
				$groups[$tag] = array('group_key' => $tag, 'group_label' => $tag, 'spend' => 0, 'transaction_count' => 0, 'pending_count' => 0, 'pending_spend' => 0, 'unposted_count' => 0, 'unposted_spend' => 0, 'receipt_count' => 0, 'average_spend' => 0, 'min_spend' => null, 'max_spend' => null);
			}
			$groups[$tag]['spend'] += (float) $row['spend'];
			$groups[$tag]['transaction_count'] += (int) $row['transaction_count'];
			$groups[$tag]['pending_count'] += (int) $row['pending_count'];
			$groups[$tag]['pending_spend'] += (float) $row['pending_spend'];
			$groups[$tag]['unposted_count'] += (int) $row['unposted_count'];
			$groups[$tag]['unposted_spend'] += (float) $row['unposted_spend'];
			$groups[$tag]['receipt_count'] += (int) $row['receipt_count'];
			$groups[$tag]['min_spend'] = $groups[$tag]['min_spend'] === null ? $row['min_spend'] : min($groups[$tag]['min_spend'], $row['min_spend']);
			$groups[$tag]['max_spend'] = $groups[$tag]['max_spend'] === null ? $row['max_spend'] : max($groups[$tag]['max_spend'], $row['max_spend']);
		}
		$rows = array_values($groups);
		usort($rows, function ($left, $right) {
			return $left['spend'] == $right['spend'] ? strcmp($left['group_label'], $right['group_label']) : ($left['spend'] < $right['spend'] ? 1 : -1);
		});
		$rows = array_slice($rows, 0, max(1, min(500, (int) $limit)));
		foreach ($rows as &$row) {
			$row['average_spend'] = $row['transaction_count'] > 0 ? $row['spend'] / $row['transaction_count'] : null;
			$row['receipt_coverage'] = $row['transaction_count'] > 0 ? round(($row['receipt_count'] / $row['transaction_count']) * 100, 1) : null;
		}
		unset($row);
		return $rows;
	}
	$definition = expenseReportGroupDefinition($groupBy);
	$limit = max(1, min(500, (int) $limit));
	$sql = "SELECT " . $definition['key'] . " AS group_key,
				" . $definition['label'] . " AS group_label,
				SUM(ABS(pd.amount)) AS spend,
				COUNT(*) AS transaction_count,
				SUM(CASE WHEN pd.authorized = '0000-00-00' OR pd.authorized IS NULL THEN 1 ELSE 0 END) AS pending_count,
				SUM(CASE WHEN pd.authorized = '0000-00-00' OR pd.authorized IS NULL THEN ABS(pd.amount) ELSE 0 END) AS pending_spend,
				SUM(CASE WHEN pd.posted = 0 OR pd.posted IS NULL THEN 1 ELSE 0 END) AS unposted_count,
				SUM(CASE WHEN pd.posted = 0 OR pd.posted IS NULL THEN ABS(pd.amount) ELSE 0 END) AS unposted_spend,
				SUM(CASE WHEN COALESCE(pd.receiptimage, '') <> '' OR COALESCE(pd.receipt, '') <> '' THEN 1 ELSE 0 END) AS receipt_count,
				AVG(ABS(pd.amount)) AS average_spend,
				MIN(ABS(pd.amount)) AS min_spend,
				MAX(ABS(pd.amount)) AS max_spend
			FROM pcashdetails pd
			LEFT JOIN pcexpenses pce ON pce.codeexpense = pd.codeexpense
			LEFT JOIN pctabs pt ON pt.tabcode = pd.tabcode
			LEFT JOIN pctypetabs ptt ON ptt.typetabcode = pt.typetabcode
			LEFT JOIN www_users wu ON wu.userid = pt.usercode
			LEFT JOIN chartmaster cm ON cm.accountcode = pce.glaccount
			LEFT JOIN tags tg ON tg.tagref = pce.tag
			WHERE " . $where . "
			GROUP BY " . $definition['key'] . ", " . $definition['label'] . "
			ORDER BY spend DESC, group_label ASC
			LIMIT " . $limit;
	$rows = expenseReportRows($db, $sql, $params);
	foreach ($rows as &$row) {
		$row['spend'] = (float) $row['spend'];
		$row['transaction_count'] = (int) $row['transaction_count'];
		$row['pending_count'] = (int) $row['pending_count'];
		$row['pending_spend'] = (float) $row['pending_spend'];
		$row['unposted_count'] = (int) $row['unposted_count'];
		$row['unposted_spend'] = (float) $row['unposted_spend'];
		$row['receipt_count'] = (int) $row['receipt_count'];
		$row['average_spend'] = (float) $row['average_spend'];
		$row['min_spend'] = (float) $row['min_spend'];
		$row['max_spend'] = (float) $row['max_spend'];
		$row['receipt_coverage'] = $row['transaction_count'] > 0 ? round(($row['receipt_count'] / $row['transaction_count']) * 100, 1) : null;
		$row['enhanced_tag'] = expenseReportClassify($row['group_label'], $row['group_key']);
		$row['classification_signal'] = expenseReportClassificationSignal($row['group_label'], $row['group_key']);
	}
	unset($row);
	return $rows;
}

function expenseReportLookupRows($db)
{
	$categories = expenseReportRows($db, "SELECT codeexpense AS value, CONCAT(codeexpense, ' — ', description) AS label, description AS raw_description FROM pcexpenses ORDER BY description, codeexpense");
	foreach ($categories as &$category) {
		$category['enhanced_tag'] = expenseReportClassify($category['raw_description'], $category['value']);
		$category['classification_signal'] = expenseReportClassificationSignal($category['raw_description'], $category['value']);
	}
	unset($category);
	return array(
		'categories' => $categories,
		'tabs' => expenseReportRows($db, "SELECT DISTINCT pt.tabcode AS value, CONCAT(pt.tabcode, ' — ', COALESCE(ptt.typetabdescription, '')) AS label
			FROM pctabs pt LEFT JOIN pctypetabs ptt ON ptt.typetabcode = pt.typetabcode ORDER BY pt.tabcode"),
		'owners' => expenseReportRows($db, "SELECT DISTINCT pt.usercode AS value, COALESCE(NULLIF(wu.realname, ''), pt.usercode) AS label
			FROM pctabs pt LEFT JOIN www_users wu ON wu.userid = pt.usercode WHERE pt.usercode <> '' ORDER BY label, value"),
		'gl_accounts' => expenseReportRows($db, "SELECT accountcode AS value, CONCAT(accountcode, ' — ', accountname) AS label FROM chartmaster ORDER BY accountcode"),
	);
}

function expenseReportSummaryRow($db, $fromClause, $where, array $params)
{
	$rows = expenseReportRows($db, "SELECT COUNT(*) AS transaction_count,
			COUNT(DISTINCT pd.date) AS active_days,
			COUNT(DISTINCT pd.tabcode) AS tab_count,
			COUNT(DISTINCT pd.codeexpense) AS category_count,
			SUM(ABS(pd.amount)) AS total_spend,
			AVG(ABS(pd.amount)) AS average_spend,
			MIN(ABS(pd.amount)) AS min_spend,
			MAX(ABS(pd.amount)) AS max_spend,
			SUM(CASE WHEN pd.authorized = '0000-00-00' OR pd.authorized IS NULL THEN ABS(pd.amount) ELSE 0 END) AS pending_spend,
			SUM(CASE WHEN pd.posted = 0 OR pd.posted IS NULL THEN ABS(pd.amount) ELSE 0 END) AS unposted_spend,
			SUM(CASE WHEN COALESCE(pd.receiptimage, '') <> '' OR COALESCE(pd.receipt, '') <> '' THEN 1 ELSE 0 END) AS receipt_count,
			SUM(CASE WHEN pd.amount >= 0 THEN 1 ELSE 0 END) AS non_negative_source_amounts
			" . $fromClause . " WHERE " . $where, $params);
	$summary = $rows[0];
	$summary['transaction_count'] = (int) $summary['transaction_count'];
	$summary['active_days'] = (int) $summary['active_days'];
	$summary['tab_count'] = (int) $summary['tab_count'];
	$summary['category_count'] = (int) $summary['category_count'];
	$summary['total_spend'] = $summary['total_spend'] === null ? null : (float) $summary['total_spend'];
	$summary['average_spend'] = $summary['average_spend'] === null ? null : (float) $summary['average_spend'];
	$summary['min_spend'] = $summary['min_spend'] === null ? null : (float) $summary['min_spend'];
	$summary['max_spend'] = $summary['max_spend'] === null ? null : (float) $summary['max_spend'];
	$summary['pending_spend'] = $summary['pending_spend'] === null ? null : (float) $summary['pending_spend'];
	$summary['unposted_spend'] = $summary['unposted_spend'] === null ? null : (float) $summary['unposted_spend'];
	$summary['receipt_count'] = (int) $summary['receipt_count'];
	$summary['non_negative_source_amounts'] = (int) $summary['non_negative_source_amounts'];
	$summary['receipt_coverage'] = $summary['transaction_count'] > 0 ? round(($summary['receipt_count'] / $summary['transaction_count']) * 100, 1) : null;
	return $summary;
}

function expenseReportComparisonPeriod($start, $end)
{
	$startDate = new DateTime($start);
	$endDate = new DateTime($end);
	$days = ((int) $startDate->diff($endDate)->format('%a')) + 1;
	$previousEnd = clone $startDate;
	$previousEnd->modify('-1 day');
	$previousStart = clone $previousEnd;
	$previousStart->modify('-' . ($days - 1) . ' days');
	return array(
		'start' => $previousStart->format('Y-m-d'),
		'end' => $previousEnd->format('Y-m-d'),
	);
}

function expenseReportComparisonValue($current, $previous)
{
	if ($current === null || $previous === null) {
		return array('value' => $current === null ? null : (float) $current, 'change' => null, 'change_percent' => null);
	}
	$current = (float) $current;
	$previous = (float) $previous;
	return array(
		'value' => $current,
		'change' => $current - $previous,
		'change_percent' => $previous == 0 ? null : round((($current - $previous) / abs($previous)) * 100, 1),
	);
}

function expenseReportPromptTokens($prompt)
{
	$normalized = strtolower(trim((string) $prompt));
	$normalized = preg_replace('/[^\p{L}\p{N}_-]+/u', ' ', $normalized);
	$tokens = preg_split('/\s+/', trim($normalized), -1, PREG_SPLIT_NO_EMPTY);
	return array_values(array_slice($tokens ?: array(), 0, 80));
}

function expenseReportPromptHasAny($text, array $phrases)
{
	foreach ($phrases as $phrase) {
		if (expenseReportKeywordMatch($text, $phrase)) {
			return true;
		}
	}
	return false;
}

function expenseReportPromptNumber($value, $suffix = '')
{
	$value = str_replace(',', '', trim((string) $value));
	if (!is_numeric($value)) {
		return null;
	}
	$number = (float) $value;
	$suffix = strtolower(trim((string) $suffix));
	if ($suffix === 'k' || $suffix === 'thousand') {
		$number *= 1000;
	} elseif ($suffix === 'm' || $suffix === 'million') {
		$number *= 1000000;
	}
	return $number;
}

function expenseReportPromptDateRange($normalized, $baseStart, $baseEnd, array &$recognized)
{
	$today = new DateTime(date('Y-m-d'));
	$start = $baseStart ?: date('Y-01-01');
	$end = $baseEnd ?: date('Y-m-d');
	$label = 'current report dates';
	$matched = false;
	$datePattern = '/\b(20\d{2}-\d{2}-\d{2})\b\s*(?:to|through|until|-)\s*\b(20\d{2}-\d{2}-\d{2})\b/';
	if (preg_match($datePattern, $normalized, $matches)) {
		$start = $matches[1];
		$end = $matches[2];
		$label = $start . ' to ' . $end;
		$matched = true;
	} elseif (preg_match('/\b(?:on|for)\s+(20\d{2}-\d{2}-\d{2})\b/', $normalized, $matches)) {
		$start = $matches[1];
		$end = $matches[1];
		$label = $start;
		$matched = true;
	} elseif (preg_match('/\blast\s+(\d{1,4})\s+days?\b/', $normalized, $matches)) {
		$days = max(1, min(3650, (int) $matches[1]));
		$rangeStart = clone $today;
		$rangeStart->modify('-' . ($days - 1) . ' days');
		$start = $rangeStart->format('Y-m-d');
		$end = $today->format('Y-m-d');
		$label = 'last ' . $days . ' days';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('today'))) {
		$start = $today->format('Y-m-d');
		$end = $start;
		$label = 'today';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('yesterday'))) {
		$yesterday = clone $today;
		$yesterday->modify('-1 day');
		$start = $yesterday->format('Y-m-d');
		$end = $start;
		$label = 'yesterday';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('last week'))) {
		$lastWeekStart = new DateTime('monday last week');
		$lastWeekEnd = clone $lastWeekStart;
		$lastWeekEnd->modify('+6 days');
		$start = $lastWeekStart->format('Y-m-d');
		$end = $lastWeekEnd->format('Y-m-d');
		$label = 'last week';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('this week'))) {
		$thisWeekStart = new DateTime('monday this week');
		$start = $thisWeekStart->format('Y-m-d');
		$end = $today->format('Y-m-d');
		$label = 'this week';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('last month'))) {
		$lastMonthStart = new DateTime('first day of last month');
		$lastMonthEnd = new DateTime('last day of last month');
		$start = $lastMonthStart->format('Y-m-d');
		$end = $lastMonthEnd->format('Y-m-d');
		$label = 'last month';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('this month'))) {
		$thisMonthStart = new DateTime('first day of this month');
		$start = $thisMonthStart->format('Y-m-d');
		$end = $today->format('Y-m-d');
		$label = 'this month';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('last quarter'))) {
		$month = (int) $today->format('n');
		$quarterStartMonth = (int) (floor(($month - 1) / 3) * 3) - 2;
		if ($quarterStartMonth <= 0) {
			$quarterStartMonth += 12;
			$year = (int) $today->format('Y') - 1;
		} else {
			$year = (int) $today->format('Y');
		}
		$lastQuarterStart = new DateTime($year . '-' . str_pad($quarterStartMonth, 2, '0', STR_PAD_LEFT) . '-01');
		$lastQuarterEnd = clone $lastQuarterStart;
		$lastQuarterEnd->modify('+2 months')->modify('last day of this month');
		$start = $lastQuarterStart->format('Y-m-d');
		$end = $lastQuarterEnd->format('Y-m-d');
		$label = 'last quarter';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('this quarter'))) {
		$month = (int) $today->format('n');
		$quarterStartMonth = (int) (floor(($month - 1) / 3) * 3) + 1;
		$thisQuarterStart = new DateTime($today->format('Y') . '-' . str_pad($quarterStartMonth, 2, '0', STR_PAD_LEFT) . '-01');
		$start = $thisQuarterStart->format('Y-m-d');
		$end = $today->format('Y-m-d');
		$label = 'this quarter';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('year to date', 'ytd'))) {
		$start = $today->format('Y-01-01');
		$end = $today->format('Y-m-d');
		$label = 'year to date';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('last year'))) {
		$lastYear = (int) $today->format('Y') - 1;
		$start = $lastYear . '-01-01';
		$end = $lastYear . '-12-31';
		$label = 'last year';
		$matched = true;
	} elseif (expenseReportPromptHasAny($normalized, array('this year'))) {
		$start = $today->format('Y-01-01');
		$end = $today->format('Y-m-d');
		$label = 'this year';
		$matched = true;
	}
	if ($matched) {
		$recognized[] = 'date: ' . $label;
	}
	return array('start' => $start, 'end' => $end, 'label' => $label);
}

function expenseReportPromptExtractValue($normalized, array $labels)
{
	$labelPattern = implode('|', array_map(function ($label) {
		return preg_quote($label, '/');
	}, $labels));
	$pattern = '/\b(?:' . $labelPattern . ')\s*(?:code\s*)?(?:is\s*)?(?:"([^"]+)"|\'([^\']+)\'|([a-z0-9_-]+))/i';
	if (preg_match($pattern, $normalized, $matches)) {
		return trim($matches[1] !== '' ? $matches[1] : ($matches[2] !== '' ? $matches[2] : $matches[3]));
	}
	return '';
}

function expenseReportPromptResolveLookup(array $rows, $value)
{
	$value = strtolower(trim((string) $value));
	if ($value === '') {
		return null;
	}
	foreach ($rows as $row) {
		if (strtolower((string) $row['value']) === $value) {
			return $row;
		}
	}
	foreach ($rows as $row) {
		$raw = isset($row['raw_description']) ? $row['raw_description'] : $row['label'];
		if (strtolower((string) $raw) === $value || strpos(strtolower((string) $raw), $value) !== false) {
			return $row;
		}
	}
	return null;
}

function expenseReportPromptMeasureDefinition($measure)
{
	$definitions = array(
		'total_spend' => array('label' => 'Total spend', 'row_field' => 'spend', 'summary_field' => 'total_spend', 'format' => 'money'),
		'transaction_count' => array('label' => 'Claim count', 'row_field' => 'transaction_count', 'summary_field' => 'transaction_count', 'format' => 'count'),
		'average_spend' => array('label' => 'Average claim', 'row_field' => 'average_spend', 'summary_field' => 'average_spend', 'format' => 'money'),
		'pending_spend' => array('label' => 'Pending spend', 'row_field' => 'pending_spend', 'summary_field' => 'pending_spend', 'format' => 'money'),
		'unposted_spend' => array('label' => 'Unposted spend', 'row_field' => 'unposted_spend', 'summary_field' => 'unposted_spend', 'format' => 'money'),
		'receipt_coverage' => array('label' => 'Receipt coverage', 'row_field' => 'receipt_coverage', 'summary_field' => 'receipt_coverage', 'format' => 'percent'),
		'min_spend' => array('label' => 'Minimum claim', 'row_field' => 'min_spend', 'summary_field' => 'min_spend', 'format' => 'money'),
		'max_spend' => array('label' => 'Maximum claim', 'row_field' => 'max_spend', 'summary_field' => 'max_spend', 'format' => 'money'),
	);
	return isset($definitions[$measure]) ? $definitions[$measure] : $definitions['total_spend'];
}

function expenseReportPromptParse($db, $prompt, array $base)
{
	$prompt = trim((string) $prompt);
	if ($prompt === '') {
		expenseReportFail('prompt_empty', 'Write what you want to see, for example: “show spend by category for the last 90 days”.');
	}
	if (strlen($prompt) > 500) {
		expenseReportFail('prompt_too_long', 'Keep the request to 500 characters or fewer so it can be interpreted deterministically.');
	}
	$normalized = strtolower(preg_replace('/\s+/u', ' ', $prompt));
	$tokens = expenseReportPromptTokens($prompt);
	$recognized = array();
	$warnings = array();
	$baseStart = expenseReportValue($base, 'startDate', expenseReportValue($base, 'start', date('Y-01-01')));
	$baseEnd = expenseReportValue($base, 'endDate', expenseReportValue($base, 'end', date('Y-m-d')));
	$input = array(
		'action' => 'summary',
		'startDate' => $baseStart,
		'endDate' => $baseEnd,
		'category' => expenseReportValue($base, 'category'),
		'tab' => expenseReportValue($base, 'tab'),
		'owner' => expenseReportValue($base, 'owner'),
		'groupBy' => expenseReportValue($base, 'groupBy', 'category'),
		'auth' => expenseReportValue($base, 'auth', 'all'),
		'posting' => expenseReportValue($base, 'posting', 'all'),
		'search' => expenseReportValue($base, 'search'),
		'includeLookups' => '0',
	);
	$dateRange = expenseReportPromptDateRange($normalized, $baseStart, $baseEnd, $recognized);
	$input['startDate'] = $dateRange['start'];
	$input['endDate'] = $dateRange['end'];

	$groupPatterns = array(
		'enhanced_tag' => '/\b(?:by|group\s+by|grouped\s+by|per)\s+(?:the\s+)?(?:enhanced\s+(?:tag|classification)|classification)\b/',
		'tab_type' => '/\b(?:by|group\s+by|grouped\s+by|per)\s+(?:the\s+)?tab\s+type\b/',
		'gl' => '/\b(?:by|group\s+by|grouped\s+by|per)\s+(?:the\s+)?gl(?:\s+account)?\b/',
		'owner' => '/\b(?:by|group\s+by|grouped\s+by|per)\s+(?:the\s+)?(?:owner|owners|user|users|employee|employees)\b/',
		'tab' => '/\b(?:by|group\s+by|grouped\s+by|per)\s+(?:the\s+)?(?:petty[- ]cash\s+)?tabs?\b/',
		'tag' => '/\b(?:by|group\s+by|grouped\s+by|per)\s+(?:the\s+)?legacy\s+tag\b/',
		'category' => '/\b(?:by|group\s+by|grouped\s+by|per)\s+(?:the\s+)?(?:expense\s+)?categor(?:y|ies)\b/',
	);
	$groupBy = '';
	foreach ($groupPatterns as $candidate => $pattern) {
		if (preg_match($pattern, $normalized)) {
			$groupBy = $candidate;
			$recognized[] = 'group: ' . expenseReportGroupDefinition($candidate)['caption'];
			break;
		}
	}
	$explicitGroup = $groupBy !== '';
	if ($groupBy !== '') {
		$input['groupBy'] = $groupBy;
	}

	$measure = 'total_spend';
	if (preg_match('/\b(?:receipt\s+(?:coverage|rate|percentage)|(?:percentage|percent|rate)\s+of\s+(?:claims?\s+)?with\s+receipts?)\b/', $normalized)) {
		$measure = 'receipt_coverage';
	} elseif (preg_match('/\bpending\s+spend\b/', $normalized)) {
		$measure = 'pending_spend';
	} elseif (preg_match('/\bunposted\s+spend\b/', $normalized)) {
		$measure = 'unposted_spend';
	} elseif (preg_match('/\b(?:average|avg|mean)\s+(?:claim|spend|expense|amount)?\b/', $normalized)) {
		$measure = 'average_spend';
	} elseif (preg_match('/\b(?:number\s+of\s+|count\s+of\s+|count\s+|how\s+many\s+)?(?:claims?|transactions?)\b/', $normalized)) {
		$measure = 'transaction_count';
	} elseif (preg_match('/\b(?:minimum|min|smallest|lowest)\s+(?:claim|spend|expense|amount)?\b/', $normalized)) {
		$measure = 'min_spend';
	} elseif (preg_match('/\b(?:maximum|max|largest|highest)\s+(?:claim|spend|expense|amount)?\b/', $normalized)) {
		$measure = 'max_spend';
	}
	$measureDefinition = expenseReportPromptMeasureDefinition($measure);
	$recognized[] = 'measure: ' . $measureDefinition['label'];

	if (expenseReportPromptHasAny($normalized, array('pending', 'awaiting approval', 'awaiting authorization'))) {
		$input['auth'] = 'pending';
		$recognized[] = 'authorization: Pending';
	} elseif (expenseReportPromptHasAny($normalized, array('authorized', 'approved'))) {
		$input['auth'] = 'authorized';
		$recognized[] = 'authorization: Authorized';
	}
	if (expenseReportPromptHasAny($normalized, array('unposted', 'not posted'))) {
		$input['posting'] = 'unposted';
		$recognized[] = 'posting: Unposted';
	} elseif (preg_match('/\bposted\b/', $normalized) && !preg_match('/\bunposted\b/', $normalized)) {
		$input['posting'] = 'posted';
		$recognized[] = 'posting: Posted';
	}
	if (preg_match('/\b(?:missing|without|no)\s+(?:a\s+)?receipts?\b/', $normalized)) {
		$input['receiptStatus'] = 'missing';
		$recognized[] = 'receipt: Missing';
	} elseif (preg_match('/\b(?:with|attached)\s+receipts?\b/', $normalized)) {
		$input['receiptStatus'] = 'attached';
		$recognized[] = 'receipt: Attached';
	}

	$numberPattern = '/\b(?:over|above|greater\s+than|more\s+than|at\s+least)\s+(?:pkr\s*)?([0-9][0-9,]*(?:\.[0-9]+)?)\s*(k|m|thousand|million)?\b/i';
	if (preg_match($numberPattern, $normalized, $matches)) {
		$input['minSpend'] = expenseReportPromptNumber($matches[1], isset($matches[2]) ? $matches[2] : '');
		$recognized[] = 'minimum spend: ' . $input['minSpend'];
	}
	$maximumPattern = '/\b(?:under|below|less\s+than|at\s+most)\s+(?:pkr\s*)?([0-9][0-9,]*(?:\.[0-9]+)?)\s*(k|m|thousand|million)?\b/i';
	if (preg_match($maximumPattern, $normalized, $matches)) {
		$input['maxSpend'] = expenseReportPromptNumber($matches[1], isset($matches[2]) ? $matches[2] : '');
		$recognized[] = 'maximum spend: ' . $input['maxSpend'];
	}

	$topN = 20;
	$sortDirection = 'DESC';
	$limitExplicit = false;
	if (preg_match('/\b(?:top|first)\s+([0-9]{1,3})\b/', $normalized, $matches)) {
		$topN = max(5, min(50, (int) $matches[1]));
		$limitExplicit = true;
		$recognized[] = 'limit: top ' . $topN;
	} elseif (preg_match('/\b(?:bottom|last)\s+([0-9]{1,3})\b/', $normalized, $matches)) {
		$topN = max(5, min(50, (int) $matches[1]));
		$sortDirection = 'ASC';
		$limitExplicit = true;
		$recognized[] = 'limit: bottom ' . $topN;
	} elseif (expenseReportPromptHasAny($normalized, array('lowest', 'smallest'))) {
		$sortDirection = 'ASC';
	}
	$input['topN'] = $topN;

	$searchPattern = '/\b(?:search|contains?|containing|description)\s+(?:"([^"]+)"|\'([^\']+)\')/i';
	if (preg_match($searchPattern, $prompt, $matches)) {
		$input['search'] = trim($matches[1] !== '' ? $matches[1] : $matches[2]);
		$recognized[] = 'text search: ' . $input['search'];
	}

	$lookupRows = expenseReportLookupRows($db);
	$lookupSpecs = array(
		array('field' => 'category', 'labels' => array('category', 'codeexpense'), 'rows' => $lookupRows['categories'], 'caption' => 'category'),
		array('field' => 'owner', 'labels' => array('owner', 'user', 'employee'), 'rows' => $lookupRows['owners'], 'caption' => 'owner'),
		array('field' => 'tab', 'labels' => array('tab', 'petty cash tab'), 'rows' => $lookupRows['tabs'], 'caption' => 'tab'),
		array('field' => 'gl', 'labels' => array('gl account', 'gl'), 'rows' => $lookupRows['gl_accounts'], 'caption' => 'GL account'),
	);
	$resolvedWords = array();
	foreach ($lookupSpecs as $spec) {
		$value = expenseReportPromptExtractValue($normalized, $spec['labels']);
		if ($value === '' || in_array(strtolower($value), array('for', 'from', 'to', 'by', 'and', 'or', 'with', 'over', 'under', 'last', 'this'), true)) {
			continue;
		}
		$resolved = expenseReportPromptResolveLookup($spec['rows'], $value);
		if ($resolved) {
			$input[$spec['field']] = $resolved['value'];
			$recognized[] = $spec['caption'] . ': ' . $resolved['label'];
			$resolvedWords = array_merge($resolvedWords, expenseReportPromptTokens(isset($resolved['raw_description']) ? $resolved['raw_description'] : $resolved['label']));
		} else {
			$warnings[] = 'No ' . $spec['caption'] . ' matched “' . $value . '”; that phrase was not applied as a filter.';
		}
	}

	$isDetail = expenseReportPromptHasAny($normalized, array('detail', 'details', 'transaction', 'transactions', 'rows', 'list'));
	$hasTopOrBottom = preg_match('/\b(?:top|bottom|first|last)\s+[0-9]{1,3}\b/', $normalized) === 1;
	$resultType = $isDetail ? 'detail' : (($explicitGroup || $hasTopOrBottom) ? 'grouped' : 'summary');
	if ($resultType === 'detail') {
		$recognized[] = 'result: transaction detail';
	} elseif ($resultType === 'grouped') {
		$recognized[] = 'result: grouped analysis';
	} else {
		$recognized[] = 'result: summary';
	}

	$ignoredTokens = array('show', 'me', 'the', 'what', 'is', 'are', 'of', 'for', 'in', 'on', 'from', 'to', 'by', 'group', 'grouped', 'per', 'with', 'and', 'or', 'as', 'please', 'give', 'get', 'find', 'see', 'view', 'all', 'available', 'last', 'this', 'month', 'months', 'year', 'years', 'week', 'weeks', 'quarter', 'quarters', 'day', 'days', 'today', 'yesterday', 'date', 'dates', 'spend', 'expense', 'expenses', 'amount', 'total', 'claims', 'claim', 'transactions', 'transaction', 'count', 'number', 'average', 'avg', 'mean', 'pending', 'awaiting', 'approval', 'authorization', 'authorized', 'approved', 'posted', 'unposted', 'missing', 'without', 'receipt', 'receipts', 'attached', 'over', 'above', 'greater', 'more', 'than', 'least', 'under', 'below', 'less', 'most', 'top', 'bottom', 'first', 'last', 'detail', 'details', 'rows', 'list', 'category', 'categories', 'owner', 'owners', 'user', 'users', 'employee', 'employees', 'tab', 'tabs', 'petty', 'cash', 'gl', 'account', 'accounts', 'tag', 'tags', 'legacy', 'enhanced', 'classification', 'search', 'contains', 'containing', 'description');
	$unrecognized = array();
	foreach ($tokens as $token) {
		if (in_array($token, $ignoredTokens, true) || in_array($token, $resolvedWords, true) || preg_match('/^20\d{2}$/', $token)) {
			continue;
		}
		if (is_numeric($token) || strlen($token) <= 1) {
			continue;
		}
		if (!in_array($token, $unrecognized, true)) {
			$unrecognized[] = $token;
		}
	}
	if (count($unrecognized) > 0) {
		$warnings[] = 'Unrecognized words are shown in the token list and ignored by the SQL compiler.';
	}
	$input['measure'] = $measure;
	$input['resultType'] = $resultType;
	$input['promptSortDirection'] = $sortDirection;
	return array(
		'input' => $input,
		'prompt' => $prompt,
		'normalized' => $normalized,
		'tokens' => $tokens,
		'recognized' => $recognized,
		'unrecognized' => $unrecognized,
		'warnings' => $warnings,
		'date_range' => $dateRange,
		'group_by' => $input['groupBy'],
		'group_caption' => expenseReportGroupDefinition($input['groupBy'])['caption'],
		'measure' => array_merge(array('key' => $measure), $measureDefinition),
		'result_type' => $resultType,
		'limit' => $topN,
		'limit_explicit' => $limitExplicit,
		'sort_direction' => $sortDirection,
	);
}

function expenseReportDetailResult($db, $fromClause, $where, array $params, array $input)
{
	$sortDefinitions = array(
		'date' => 'pd.date',
		'category' => 'pce.description',
		'tab' => 'pd.tabcode',
		'owner' => 'wu.realname',
		'gl' => 'pce.glaccount',
		'spend' => 'ABS(pd.amount)',
		'authorization' => 'pd.authorized',
		'posting' => 'pd.posted',
	);
	$sort = strtolower(expenseReportValue($input, 'sort', 'date'));
	$sortColumn = isset($sortDefinitions[$sort]) ? $sortDefinitions[$sort] : $sortDefinitions['date'];
	$direction = strtoupper(expenseReportValue($input, 'direction', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
	$isExport = expenseReportValue($input, 'action', '') === 'export';
	$limit = $isExport
		? max(1, min(25000, (int) expenseReportValue($input, 'exportLimit', '25000')))
		: max(1, min(500, (int) expenseReportValue($input, 'pageSize', expenseReportValue($input, 'limit', '50'))));
	$offset = $isExport ? 0 : max(0, min(100000, (int) expenseReportValue($input, 'offset', '0')));
	$countRows = expenseReportRows($db, 'SELECT COUNT(*) AS total_count ' . $fromClause . ' WHERE ' . $where, $params);
	$totalCount = (int) $countRows[0]['total_count'];
	$sql = "SELECT pd.counterindex, pd.date, pd.codeexpense,
			COALESCE(NULLIF(pce.description, ''), 'Unmapped category') AS category,
			pd.tabcode,
			COALESCE(NULLIF(ptt.typetabdescription, ''), NULLIF(ptt.typetabcode, ''), 'Unmapped tab type') AS tab_type,
			COALESCE(NULLIF(wu.realname, ''), NULLIF(pt.usercode, ''), 'Unmapped owner') AS owner,
			COALESCE(NULLIF(pce.glaccount, ''), 'Unmapped') AS glaccount,
			COALESCE(NULLIF(cm.accountname, ''), 'Unmapped GL account') AS glaccount_name,
			ABS(pd.amount) AS spend,
			CASE WHEN pd.authorized = '0000-00-00' OR pd.authorized IS NULL THEN 'Pending approval' ELSE 'Authorized' END AS authorization_status,
			CASE WHEN pd.posted = 1 THEN 'Posted' ELSE 'Unposted' END AS posting_status,
			CASE WHEN COALESCE(pd.receiptimage, '') <> '' OR COALESCE(pd.receipt, '') <> '' THEN 1 ELSE 0 END AS has_receipt,
			pd.lastreading, pd.meterreading, pd.notes, pd.receipt, pd.receiptimage, pd.amount AS source_amount
			" . $fromClause . " WHERE " . $where . " ORDER BY " . $sortColumn . " " . $direction . ", pd.counterindex DESC LIMIT " . $offset . ", " . $limit;
	$rows = expenseReportRows($db, $sql, $params);
	foreach ($rows as &$row) {
		$row['counterindex'] = (int) $row['counterindex'];
		$row['spend'] = (float) $row['spend'];
		$row['has_receipt'] = (int) $row['has_receipt'];
		$row['lastreading'] = (int) $row['lastreading'];
		$row['meterreading'] = (int) $row['meterreading'];
		$row['source_amount'] = (float) $row['source_amount'];
		$row['enhanced_tag'] = expenseReportClassify($row['category'], $row['codeexpense']);
		$row['classification_signal'] = expenseReportClassificationSignal($row['category'], $row['codeexpense']);
	}
	unset($row);
	return array(
		'rows' => $rows,
		'total_count' => $totalCount,
		'has_more' => $offset + count($rows) < $totalCount,
		'limit' => $limit,
		'offset' => $offset,
		'sort' => $sort,
		'direction' => $direction,
	);
}

function expenseReportPromptBuildResult($db, $fromClause, $where, array $params, array $plan, array $summary)
{
	$measure = $plan['measure'];
	$result = array(
		'type' => $plan['result_type'],
		'measure' => $measure,
		'group_by' => $plan['group_by'],
		'group_caption' => $plan['group_caption'],
		'rows' => array(),
	);
	if ($plan['result_type'] === 'summary') {
		$value = $measure['summary_field'] !== null && isset($summary[$measure['summary_field']]) ? $summary[$measure['summary_field']] : null;
		$result['value'] = $value;
		$result['supporting'] = array(
			'total_spend' => $summary['total_spend'],
			'transaction_count' => $summary['transaction_count'],
			'receipt_coverage' => $summary['receipt_coverage'],
		);
	} elseif ($plan['result_type'] === 'grouped') {
		$rows = expenseReportGroupedRows($db, $where, $params, $plan['group_by'], 500);
		$rowField = $measure['row_field'];
		usort($rows, function ($left, $right) use ($rowField, $plan) {
			$leftValue = isset($left[$rowField]) && $left[$rowField] !== null ? (float) $left[$rowField] : 0;
			$rightValue = isset($right[$rowField]) && $right[$rowField] !== null ? (float) $right[$rowField] : 0;
			if ($leftValue == $rightValue) {
				return strcmp($left['group_label'], $right['group_label']);
			}
			$isAscending = $plan['sort_direction'] === 'ASC';
			return $isAscending ? ($leftValue < $rightValue ? -1 : 1) : ($leftValue > $rightValue ? -1 : 1);
		});
		foreach (array_slice($rows, 0, $plan['limit']) as $row) {
			$result['rows'][] = array(
				'group_key' => $row['group_key'],
				'group_label' => $row['group_label'],
				'value' => $rowField === 'receipt_coverage' ? $row['receipt_coverage'] : $row[$rowField],
				'spend' => $row['spend'],
				'transaction_count' => $row['transaction_count'],
				'average_spend' => $row['average_spend'],
				'pending_spend' => $row['pending_spend'],
				'unposted_spend' => $row['unposted_spend'],
				'receipt_coverage' => $row['receipt_coverage'],
			);
		}
	} else {
		$detailInput = $plan['input'];
		$detailInput['action'] = 'details';
		$detailInput['pageSize'] = $plan['limit'];
		$detailInput['offset'] = 0;
		$detailInput['sort'] = 'spend';
		$detailInput['direction'] = $plan['sort_direction'];
		$detailResult = expenseReportDetailResult($db, $fromClause, $where, $params, $detailInput);
		$result['rows'] = $detailResult['rows'];
		$result['total_count'] = $detailResult['total_count'];
		$result['has_more'] = $detailResult['has_more'];
	}
	$definition = expenseReportGroupDefinition($plan['group_by']);
	if ($plan['result_type'] === 'summary') {
		$sqlTemplate = 'SELECT COUNT(*) AS transaction_count, SUM(ABS(pd.amount)) AS total_spend, AVG(ABS(pd.amount)) AS average_spend, MIN(ABS(pd.amount)) AS min_spend, MAX(ABS(pd.amount)) AS max_spend ' . $fromClause . ' WHERE ' . $where;
	} elseif ($plan['result_type'] === 'grouped') {
		$sqlTemplate = 'SELECT ' . $definition['key'] . ' AS group_key, ' . $definition['label'] . ' AS group_label, SUM(ABS(pd.amount)) AS spend, COUNT(*) AS transaction_count ' . $fromClause . ' WHERE ' . $where . ' GROUP BY ' . $definition['key'] . ', ' . $definition['label'] . ' ORDER BY spend ' . $plan['sort_direction'] . ' LIMIT 500';
	} else {
		$sqlTemplate = 'SELECT pd.date, pce.description AS category, pd.tabcode, ABS(pd.amount) AS spend ' . $fromClause . ' WHERE ' . $where . ' ORDER BY ABS(pd.amount) ' . $plan['sort_direction'] . ' LIMIT ' . $plan['limit'];
	}
	$result['sql'] = array(
		'parameterized' => true,
		'sql_template' => $sqlTemplate,
		'parameters' => $params,
		'guardrail' => 'Only allowlisted dimensions, measures, filters, and bound parameters are accepted; free-form SQL is disabled.',
	);
	return $result;
}

function expenseReportExportXlsx(array $rows, array $range, array $input, $context, $totalCount)
{
	if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
		expenseReportFail('export_unavailable', 'Excel export is unavailable because the spreadsheet runtime is not installed.', 503);
	}

	$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	$sheet->setTitle('Expense Detail');
	$sheet->setCellValue('A1', 'Expense Intelligence — Transaction detail');
	$sheet->setCellValue('A2', 'Date range');
	$sheet->setCellValue('B2', $range['start'] . ' to ' . $range['end']);
	$sheet->setCellValue('A3', 'Applied controls');
	$sheet->setCellValue('B3', 'Group by: ' . expenseReportValue($input, 'groupBy', 'category') . '; search: ' . expenseReportValue($input, 'search', 'none'));
	$sheet->setCellValue('A4', 'Exported at UTC');
	$sheet->setCellValue('B4', gmdate('Y-m-d H:i:s'));
	$sheet->setCellValue('D4', 'Rows included');
	$sheet->setCellValue('E4', count($rows) . ' of ' . $totalCount . ' matching rows');
	$headers = array('Date', 'Category code', 'Category', 'Enhanced tag', 'Petty-cash tab', 'Tab type', 'Owner', 'GL account', 'GL account name', 'Authorization', 'Posting', 'Spend', 'Receipt', 'Notes', 'Source amount');
	foreach ($headers as $index => $header) {
		$sheet->setCellValueByColumnAndRow($index + 1, 6, $header);
	}
	$totalSpend = 0;
	foreach ($rows as $rowIndex => $row) {
		$excelRow = $rowIndex + 7;
		$values = array(
			$row['date'],
			$row['codeexpense'],
			$row['category'],
			$row['enhanced_tag'],
			$row['tabcode'],
			$row['tab_type'],
			$row['owner'],
			$row['glaccount'],
			$row['glaccount_name'],
			$row['authorization_status'],
			$row['posting_status'],
			(float) $row['spend'],
			(int) $row['has_receipt'] === 1 ? 'Attached' : 'Missing',
			$row['notes'],
			(float) $row['source_amount'],
		);
		foreach ($values as $index => $value) {
			$sheet->setCellValueByColumnAndRow($index + 1, $excelRow, $value);
		}
		$totalSpend += (float) $row['spend'];
	}
	$totalRow = count($rows) + 7;
	$sheet->setCellValue('A' . $totalRow, 'Visible export total');
	$sheet->setCellValue('L' . $totalRow, $totalSpend);
	$sheet->getStyle('A6:O6')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
	$sheet->getStyle('A6:O6')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF1D79C8');
	$sheet->getStyle('A' . $totalRow . ':O' . $totalRow)->getFont()->setBold(true);
	$sheet->getStyle('L7:L' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
	$sheet->getStyle('O7:O' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
	$sheet->freezePane('A7');
	$sheet->setAutoFilter('A6:O6');
	foreach (range(1, 15) as $column) {
		$sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
	}
	$sheet->getColumnDimension('C')->setWidth(28);
	$sheet->getColumnDimension('D')->setWidth(24);
	$sheet->getColumnDimension('I')->setWidth(28);
	$sheet->getColumnDimension('N')->setWidth(38);
	$filename = 'expense-intelligence-' . $range['start'] . '-to-' . $range['end'] . '.xlsx';
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment; filename="' . $filename . '"');
	header('Cache-Control: max-age=0');
	$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
	$writer->save('php://output');
	exit;
}

if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	expenseReportFail('unauthorized', 'An authenticated ERP session is required.', 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
	expenseReportFail('method_not_allowed', 'Use GET or POST for read-only expense analytics queries.', 405);
}

$input = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$raw = file_get_contents('php://input');
	if ($raw !== false && trim($raw) !== '') {
		$decoded = json_decode($raw, true);
		if (!is_array($decoded)) {
			expenseReportFail('invalid_json', 'The request body must be a JSON object.');
		}
		$input = $decoded;
	}
}
if (!$input) {
	$input = $_GET;
}

try {
	require_once($BiRootPath . '/bi/bootstrap.php');
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	if (!$context->canUseSalesAnalytics()) {
		expenseReportFail('forbidden', 'You are not authorized to use business intelligence.', 403);
	}
	expenseReportEnsureTables($db);

	$action = strtolower(expenseReportValue($input, 'action', 'summary'));
	$includeLookups = expenseReportValue($input, 'includeLookups', '1') !== '0';
	$isPrompt = $action === 'prompt';
	$promptPlan = null;
	if ($isPrompt) {
		$baseFilters = isset($input['baseFilters']) && is_array($input['baseFilters']) ? $input['baseFilters'] : array();
		$promptPlan = expenseReportPromptParse($db, expenseReportValue($input, 'prompt'), $baseFilters);
		$input = $promptPlan['input'];
		$action = 'summary';
		$includeLookups = false;
	}
	$whereParams = array();
	$range = expenseReportWhere($input, $whereParams);
	$where = $range['sql'];
	$baseParams = $range['params'];
	$groupBy = strtolower(expenseReportValue($input, 'groupBy', 'category'));
	$groupDefinition = expenseReportGroupDefinition($groupBy);
	$topN = max(5, min(50, (int) expenseReportValue($input, 'topN', '20')));

	$fromClause = "FROM pcashdetails pd
		LEFT JOIN pcexpenses pce ON pce.codeexpense = pd.codeexpense
		LEFT JOIN pctabs pt ON pt.tabcode = pd.tabcode
		LEFT JOIN pctypetabs ptt ON ptt.typetabcode = pt.typetabcode
		LEFT JOIN www_users wu ON wu.userid = pt.usercode
		LEFT JOIN chartmaster cm ON cm.accountcode = pce.glaccount
		LEFT JOIN tags tg ON tg.tagref = pce.tag";

	if ($action === 'details' || $action === 'export') {
		$sortDefinitions = array(
			'date' => 'pd.date',
			'category' => 'pce.description',
			'tab' => 'pd.tabcode',
			'owner' => 'wu.realname',
			'gl' => 'pce.glaccount',
			'spend' => 'ABS(pd.amount)',
			'authorization' => 'pd.authorized',
			'posting' => 'pd.posted',
		);
		$sort = strtolower(expenseReportValue($input, 'sort', 'date'));
		$sortColumn = isset($sortDefinitions[$sort]) ? $sortDefinitions[$sort] : $sortDefinitions['date'];
		$direction = strtoupper(expenseReportValue($input, 'direction', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
		$isExport = $action === 'export';
		$limit = $isExport ? max(1, min(25000, (int) expenseReportValue($input, 'exportLimit', '25000'))) : max(1, min(500, (int) expenseReportValue($input, 'pageSize', expenseReportValue($input, 'limit', '50'))));
		$offset = $isExport ? 0 : max(0, min(100000, (int) expenseReportValue($input, 'offset', '0')));
		$countRows = expenseReportRows($db, 'SELECT COUNT(*) AS total_count ' . $fromClause . ' WHERE ' . $where, $baseParams);
		$totalCount = (int) $countRows[0]['total_count'];
		$sql = "SELECT pd.counterindex, pd.date, pd.codeexpense,
				COALESCE(NULLIF(pce.description, ''), 'Unmapped category') AS category,
				pd.tabcode,
				COALESCE(NULLIF(ptt.typetabdescription, ''), NULLIF(pt.typetabcode, ''), 'Unmapped tab type') AS tab_type,
				COALESCE(NULLIF(wu.realname, ''), NULLIF(pt.usercode, ''), 'Unmapped owner') AS owner,
				COALESCE(NULLIF(pce.glaccount, ''), 'Unmapped') AS glaccount,
				COALESCE(NULLIF(cm.accountname, ''), 'Unmapped GL account') AS glaccount_name,
				ABS(pd.amount) AS spend,
				CASE WHEN pd.authorized = '0000-00-00' OR pd.authorized IS NULL THEN 'Pending approval' ELSE 'Authorized' END AS authorization_status,
				CASE WHEN pd.posted = 1 THEN 'Posted' ELSE 'Unposted' END AS posting_status,
				CASE WHEN COALESCE(pd.receiptimage, '') <> '' OR COALESCE(pd.receipt, '') <> '' THEN 1 ELSE 0 END AS has_receipt,
				pd.lastreading, pd.meterreading, pd.notes, pd.receipt, pd.receiptimage, pd.amount AS source_amount
				" . $fromClause . " WHERE " . $where . " ORDER BY " . $sortColumn . " " . $direction . ", pd.counterindex DESC LIMIT " . $offset . ", " . $limit;
		$rows = expenseReportRows($db, $sql, $baseParams);
		foreach ($rows as &$row) {
			$row['counterindex'] = (int) $row['counterindex'];
			$row['spend'] = (float) $row['spend'];
			$row['has_receipt'] = (int) $row['has_receipt'];
			$row['lastreading'] = (int) $row['lastreading'];
			$row['meterreading'] = (int) $row['meterreading'];
			$row['source_amount'] = (float) $row['source_amount'];
			$row['enhanced_tag'] = expenseReportClassify($row['category'], $row['codeexpense']);
			$row['classification_signal'] = expenseReportClassificationSignal($row['category'], $row['codeexpense']);
		}
		unset($row);
		if ($isExport) {
			expenseReportExportXlsx($rows, $range, $input, $context, $totalCount);
		}
		expenseReportResponse(array(
			'ok' => true,
			'data' => array(
				'rows' => $rows,
				'total_count' => $totalCount,
				'has_more' => $offset + count($rows) < $totalCount,
				'filters' => array('start' => $range['start'], 'end' => $range['end']),
				'meta' => array('limit' => $limit, 'offset' => $offset, 'page_size' => $limit, 'sort' => $sort, 'direction' => $direction, 'duration_ms' => round((microtime(true) - $expenseReportStartedAt) * 1000, 1), 'generated_at_utc' => gmdate('Y-m-d H:i:s')),
			),
		), 200);
	}

	if ($action !== 'summary') {
		expenseReportFail('unsupported_action', 'The expense analytics action is not supported.');
	}

	$summary = expenseReportSummaryRow($db, $fromClause, $where, $baseParams);

	$assignmentParams = array($range['start'], $range['end']);
	$assignmentRows = expenseReportRows($db, "SELECT COUNT(*) AS assignment_count, SUM(ABS(pd.amount)) AS assigned_cash
		FROM pcashdetails pd WHERE pd.date >= ? AND pd.date <= ? AND UPPER(pd.codeexpense) = 'ASSIGNCASH'", $assignmentParams);
	$assignments = $assignmentRows[0];
	$assignments['assignment_count'] = (int) $assignments['assignment_count'];
	$assignments['assigned_cash'] = $assignments['assigned_cash'] === null ? null : (float) $assignments['assigned_cash'];

	$previousPeriod = expenseReportComparisonPeriod($range['start'], $range['end']);
	$comparisonInput = $input;
	$comparisonInput['startDate'] = $previousPeriod['start'];
	$comparisonInput['endDate'] = $previousPeriod['end'];
	$comparisonParams = array();
	$comparisonRange = expenseReportWhere($comparisonInput, $comparisonParams);
	$previousSummary = expenseReportSummaryRow($db, $fromClause, $comparisonRange['sql'], $comparisonRange['params']);
	$comparison = array(
		'period' => $previousPeriod,
		'previous_summary' => $previousSummary,
		'metrics' => array(
			'total_spend' => expenseReportComparisonValue($summary['total_spend'], $previousSummary['total_spend']),
			'transaction_count' => expenseReportComparisonValue($summary['transaction_count'], $previousSummary['transaction_count']),
			'average_spend' => expenseReportComparisonValue($summary['average_spend'], $previousSummary['average_spend']),
			'pending_spend' => expenseReportComparisonValue($summary['pending_spend'], $previousSummary['pending_spend']),
			'unposted_spend' => expenseReportComparisonValue($summary['unposted_spend'], $previousSummary['unposted_spend']),
			'receipt_coverage' => expenseReportComparisonValue($summary['receipt_coverage'], $previousSummary['receipt_coverage']),
		),
	);

	$trendRows = expenseReportRows($db, "SELECT DATE_FORMAT(pd.date, '%Y-%m-01') AS period,
			SUM(ABS(pd.amount)) AS spend,
			COUNT(*) AS transaction_count,
			SUM(CASE WHEN pd.authorized = '0000-00-00' OR pd.authorized IS NULL THEN ABS(pd.amount) ELSE 0 END) AS pending_spend,
			SUM(CASE WHEN pd.posted = 0 OR pd.posted IS NULL THEN ABS(pd.amount) ELSE 0 END) AS unposted_spend
			" . $fromClause . " WHERE " . $where . " GROUP BY DATE_FORMAT(pd.date, '%Y-%m-01') ORDER BY period ASC", $baseParams);
	foreach ($trendRows as &$row) {
		$row['spend'] = (float) $row['spend'];
		$row['transaction_count'] = (int) $row['transaction_count'];
		$row['pending_spend'] = (float) $row['pending_spend'];
		$row['unposted_spend'] = (float) $row['unposted_spend'];
	}
	unset($row);

	$breakdown = expenseReportGroupedRows($db, $where, $baseParams, $groupBy, $topN);
	$categoryBreakdown = expenseReportGroupedRows($db, $where, $baseParams, 'category', 12);
	$glBreakdown = expenseReportGroupedRows($db, $where, $baseParams, 'gl', 12);
	$ownerBreakdown = expenseReportGroupedRows($db, $where, $baseParams, 'owner', 12);

	$qualityRows = expenseReportRows($db, "SELECT
			SUM(CASE WHEN pce.codeexpense IS NULL THEN 1 ELSE 0 END) AS missing_category,
			SUM(CASE WHEN pce.glaccount IS NULL OR pce.glaccount = '' THEN 1 ELSE 0 END) AS missing_gl,
			SUM(CASE WHEN pt.tabcode IS NULL THEN 1 ELSE 0 END) AS missing_tab_master,
			SUM(CASE WHEN pd.authorized = '0000-00-00' OR pd.authorized IS NULL THEN 1 ELSE 0 END) AS pending_authorization,
			SUM(CASE WHEN pd.posted = 0 OR pd.posted IS NULL THEN 1 ELSE 0 END) AS unposted,
			SUM(CASE WHEN COALESCE(pd.receiptimage, '') = '' AND COALESCE(pd.receipt, '') = '' THEN 1 ELSE 0 END) AS missing_receipt,
			SUM(CASE WHEN pd.amount >= 0 THEN 1 ELSE 0 END) AS non_negative_source_amounts
			" . $fromClause . " WHERE " . $where, $baseParams);
	$quality = $qualityRows[0];
	foreach ($quality as $key => $value) {
		$quality[$key] = (int) $value;
	}
	$exceptions = array(
		'high_value_threshold' => null,
		'high_value_count' => 0,
		'top_claims' => array(),
		'pending_spend_share' => $summary['total_spend'] > 0 ? round(($summary['pending_spend'] / $summary['total_spend']) * 100, 1) : null,
		'unposted_spend_share' => $summary['total_spend'] > 0 ? round(($summary['unposted_spend'] / $summary['total_spend']) * 100, 1) : null,
	);
	if ($summary['average_spend'] !== null && $summary['average_spend'] > 0) {
		$exceptions['high_value_threshold'] = round($summary['average_spend'] * 3, 2);
		$exceptionParams = $baseParams;
		$exceptionParams[] = $exceptions['high_value_threshold'];
		$exceptionCountRows = expenseReportRows($db, 'SELECT COUNT(*) AS high_value_count ' . $fromClause . ' WHERE ' . $where . ' AND ABS(pd.amount) >= ?', $exceptionParams);
		$exceptions['high_value_count'] = (int) $exceptionCountRows[0]['high_value_count'];
		$exceptionRows = expenseReportRows($db, "SELECT pd.date, pd.codeexpense,
				COALESCE(NULLIF(pce.description, ''), 'Unmapped category') AS category,
				COALESCE(NULLIF(wu.realname, ''), NULLIF(pt.usercode, ''), 'Unmapped owner') AS owner,
				ABS(pd.amount) AS spend
				" . $fromClause . " WHERE " . $where . " AND ABS(pd.amount) >= ? ORDER BY ABS(pd.amount) DESC, pd.date DESC LIMIT 5", $exceptionParams);
		foreach ($exceptionRows as &$exceptionRow) {
			$exceptionRow['spend'] = (float) $exceptionRow['spend'];
			$exceptionRow['enhanced_tag'] = expenseReportClassify($exceptionRow['category'], $exceptionRow['codeexpense']);
		}
		unset($exceptionRow);
		$exceptions['top_claims'] = $exceptionRows;
	}
	$lookupRows = $includeLookups ? expenseReportLookupRows($db) : null;
	$classificationReviewCount = 0;
	if ($includeLookups) {
		foreach ($lookupRows['categories'] as $lookupCategory) {
			if ($lookupCategory['enhanced_tag'] === 'Other / Review') {
				$classificationReviewCount++;
			}
		}
	} else {
		$classificationRows = expenseReportRows($db, 'SELECT codeexpense, description FROM pcexpenses');
		foreach ($classificationRows as $classificationRow) {
			if (expenseReportClassify($classificationRow['description'], $classificationRow['codeexpense']) === 'Other / Review') {
				$classificationReviewCount++;
			}
		}
	}

	$responseData = array(
			'summary' => $summary,
			'assignments' => $assignments,
			'comparison' => $comparison,
			'trend' => $trendRows,
			'breakdown' => $breakdown,
			'category_breakdown' => $categoryBreakdown,
			'gl_breakdown' => $glBreakdown,
			'owner_breakdown' => $ownerBreakdown,
			'quality' => $quality,
			'exceptions' => $exceptions,
	'lookups' => $includeLookups ? $lookupRows : null,
			'filters' => array(
				'start' => $range['start'],
				'end' => $range['end'],
				'group_by' => $groupBy,
			),
			'meta' => array(
				'company_name' => $context->getCompanyName(),
				'database_name' => $context->getDatabaseName(),
				'source' => 'pcashdetails + pcexpenses + pctabs + pctypetabs + chartmaster + tags',
				'grain' => 'one row per petty-cash claim in pcashdetails; ASSIGNCASH transfers excluded from spend',
				'amount_basis' => 'positive magnitude of source expense claim; source ledger stores claims as negative amounts',
				'date_role' => 'pcashdetails.date',
				'classification_basis' => 'normalized pcexpenses.description and codeexpense matched against whole-word and phrase rules',
				'classification_version' => 'expense-description-v2',
				'classification_review_count' => $classificationReviewCount,
				'duration_ms' => round((microtime(true) - $expenseReportStartedAt) * 1000, 1),
				'generated_at_utc' => gmdate('Y-m-d H:i:s'),
				'group_caption' => $groupDefinition['caption'],
			),
	);
	if ($isPrompt && $promptPlan) {
		$responseData['prompt'] = array(
			'request' => $promptPlan['prompt'],
			'normalized' => $promptPlan['normalized'],
			'tokens' => $promptPlan['tokens'],
			'recognized' => $promptPlan['recognized'],
			'unrecognized' => $promptPlan['unrecognized'],
			'warnings' => $promptPlan['warnings'],
			'interpretation' => array(
				'date_range' => $promptPlan['date_range'],
				'group_by' => $promptPlan['group_caption'],
				'measure' => $promptPlan['measure']['label'],
				'result_type' => $promptPlan['result_type'],
				'limit' => $promptPlan['limit'],
				'limit_explicit' => $promptPlan['limit_explicit'],
				'limit_direction' => $promptPlan['sort_direction'],
			),
			'result' => expenseReportPromptBuildResult($db, $fromClause, $where, $baseParams, $promptPlan, $summary),
		);
	}
	expenseReportResponse(array(
		'ok' => true,
		'data' => $responseData,
	), 200);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	expenseReportFail($exception->getErrorCode(), $exception->getMessage(), $exception->getHttpStatus(), $exception->getDetails());
} catch (Throwable $exception) {
	error_log('[bi] unhandled expense analytics endpoint failure: ' . get_class($exception) . ': ' . $exception->getMessage());
	expenseReportFail('bi_unavailable', 'Expense analytics is temporarily unavailable. The source query did not return a result.', 503);
}
