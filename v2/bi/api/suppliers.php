<?php

/**
 * Supplier Relationship Intelligence endpoint.
 *
 * This is a read-only BI surface over the existing supplier ledger. It keeps
 * the ERP's net transaction formula (amount + GST - allocation), while also
 * exposing the supporting payment, ageing, supplier-master, and transaction
 * detail needed for one consolidated AP view.
 */

$BiRootPath = dirname(__DIR__, 3);
include_once($BiRootPath . '/config.php');
if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
}
session_start();
require_once($BiRootPath . '/bi/bootstrap.php');

header('Cache-Control: no-store');

function biSupplierResponse(array $payload, $status)
{
	header('Content-Type: application/json; charset=utf-8');
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function biSupplierError($code, $message, $status, array $details = array())
{
	biSupplierResponse(array(
		'ok' => false,
		'error' => array('code' => $code, 'message' => $message, 'details' => $details),
	), $status);
}

function biSupplierInput()
{
	$input = array();
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$raw = file_get_contents('php://input');
		if ($raw !== false && trim($raw) !== '') {
			$decoded = json_decode($raw, true);
			if (!is_array($decoded)) {
				biSupplierError('invalid_json', 'The request body must be a JSON object.', 400);
			}
			$input = $decoded;
		}
	}
	return $input ? $input : $_GET;
}

function biSupplierDate($value, $fallback)
{
	$value = trim((string) $value);
	if ($value === '') {
		return $fallback;
	}
	$date = \DateTime::createFromFormat('!Y-m-d', $value);
	$errors = \DateTime::getLastErrors();
	if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) {
		biSupplierError('invalid_date', 'Dates must use the YYYY-MM-DD format.', 400);
	}
	return $value;
}

function biSupplierListInput($value, $pattern, $maximum, $label)
{
	$items = is_array($value) ? $value : explode(',', (string) $value);
	$clean = array();
	foreach ($items as $item) {
		$item = trim((string) $item);
		if ($item === '') {
			continue;
		}
		if (preg_match($pattern, $item) !== 1) {
			biSupplierError('invalid_filter', $label . ' contains an invalid value.', 400);
		}
		$clean[$item] = $item;
	}
	$clean = array_values($clean);
	if (count($clean) > $maximum) {
		biSupplierError('filter_too_large', $label . ' accepts at most ' . $maximum . ' selections.', 400);
	}
	return $clean;
}

function biSupplierOptionalNumber($value, $label)
{
	$value = trim((string) $value);
	if ($value === '') {
		return null;
	}
	if (!is_numeric($value)) {
		biSupplierError('invalid_filter', $label . ' must be a number.', 400);
	}
	return (float) $value;
}

function biSupplierAppendIn(&$where, $column, array $values, &$params, &$types, $type)
{
	if (!$values) {
		return;
	}
	$where .= ' AND ' . $column . ' IN (' . implode(',', array_fill(0, count($values), '?')) . ')';
	foreach ($values as $value) {
		$types .= $type;
		$params[] = $type === 'i' ? (int) $value : (string) $value;
	}
}

function biSupplierBindAndFetch($db, $sql, $types, array $params)
{
	$stmt = @mysqli_prepare($db, $sql);
	if (!$stmt) {
		throw new \SAHamid\BI\Exception\BIException('source_query_failed', 'The supplier report query could not be prepared.', 503);
	}
	if ($types !== '') {
		$bindings = array($stmt, $types);
		foreach ($params as $index => $value) {
			$bindings[] = &$params[$index];
		}
		if (!call_user_func_array('mysqli_stmt_bind_param', $bindings)) {
			mysqli_stmt_close($stmt);
			throw new \SAHamid\BI\Exception\BIException('source_query_failed', 'The supplier report query could not bind its filters.', 503);
		}
	}
	if (!mysqli_stmt_execute($stmt)) {
		mysqli_stmt_close($stmt);
		throw new \SAHamid\BI\Exception\BIException('source_query_failed', 'The supplier report query failed; no rows were returned.', 503);
	}
	$result = mysqli_stmt_get_result($stmt);
	if (!$result) {
		mysqli_stmt_close($stmt);
		throw new \SAHamid\BI\Exception\BIException('source_query_failed', 'The supplier report result could not be read.', 503);
	}
	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	mysqli_free_result($result);
	mysqli_stmt_close($stmt);
	return $rows;
}

function biSupplierNumber($value)
{
	return (float) $value;
}

function biSupplierCastRows(array $rows)
{
	$numeric = array(
		'net_balance', 'total_bills', 'paid_allocated', 'open_payables', 'current_amount',
		'due_now', 'overdue_1', 'overdue_2', 'payment_activity', 'purchase_activity',
		'allocated_activity', 'payment_credit_activity', 'unapplied_credits', 'open_bill_count',
		'transaction_count', 'supplier_count', 'open_supplier_count', 'overdue_supplier_count',
		'currency_count', 'amount', 'purchases', 'payments', 'allocated', 'transaction_count',
		'supplier_count', 'max_days_overdue', 'activity_bills', 'activity_paid', 'activity_balance',
		'paid_amount', 'payment_amount', 'outstanding', 'total_amount', 'allocated_amount',
		'missing_due_date', 'on_hold_open_bill', 'zero_balance_unsettled',
	);
	foreach ($rows as &$row) {
		foreach ($numeric as $field) {
			if (array_key_exists($field, $row)) {
				$row[$field] = biSupplierNumber($row[$field]);
			}
		}
		foreach (array('open_bill_count', 'transaction_count', 'supplier_count', 'open_supplier_count', 'overdue_supplier_count', 'currency_count') as $field) {
			if (array_key_exists($field, $row)) {
				$row[$field] = (int) $row[$field];
			}
		}
	}
	unset($row);
	return $rows;
}

function biSupplierClause($search, array $supplierIds, array $supplierTypes, array $paymentTerms, array $transactionTypes, &$params, &$types, $dateClause)
{
	$params = array();
	$types = '';
	$where = ' WHERE ' . $dateClause;
	biSupplierAppendIn($where, 's.supplierid', $supplierIds, $params, $types, 's');
	biSupplierAppendIn($where, 's.supptype', $supplierTypes, $params, $types, 'i');
	biSupplierAppendIn($where, 's.paymentterms', $paymentTerms, $params, $types, 's');
	biSupplierAppendIn($where, 'st.type', $transactionTypes, $params, $types, 'i');
	if ($search !== '') {
		$where .= ' AND (s.supplierid LIKE ? OR s.suppname LIKE ? OR s.email LIKE ? OR s.telephone LIKE ? OR st.suppreference LIKE ? OR st.transtext LIKE ? OR ty.typename LIKE ? OR stp.typename LIKE ? OR pt.terms LIKE ?)';
		$types .= 'sssssssss';
		$needle = '%' . $search . '%';
		for ($i = 0; $i < 9; $i++) {
			$params[] = $needle;
		}
	}
	return $where;
}

function biSupplierFilterClause($status, $aging, $attention, $dueFrom, $dueTo, $minimumOutstanding, $maximumOutstanding, $outstanding, $daysOverdue, $dueDate, $asOf, $threshold1, $threshold2)
{
	$clause = '';
	if ($status === 'outstanding') {
		$clause .= ' AND ABS(' . $outstanding . ') > 0.009';
	} elseif ($status === 'settled') {
		$clause .= ' AND ABS(' . $outstanding . ') <= 0.009';
	} elseif ($status === 'overdue') {
		$clause .= ' AND ' . $outstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . (int) $threshold1;
	}
	if ($aging === 'current') {
		$clause .= ' AND ' . $outstanding . ' > 0 AND ' . $dueDate . ' > \'' . $asOf . '\'';
	} elseif ($aging === 'due') {
		$clause .= ' AND ' . $outstanding . ' > 0 AND ' . $dueDate . ' <= \'' . $asOf . '\' AND ' . $daysOverdue . ' >= 0 AND ' . $daysOverdue . ' < ' . (int) $threshold1;
	} elseif ($aging === 'overdue1') {
		$clause .= ' AND ' . $outstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . (int) $threshold1 . ' AND ' . $daysOverdue . ' < ' . (int) $threshold2;
	} elseif ($aging === 'overdue2') {
		$clause .= ' AND ' . $outstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . (int) $threshold2;
	} elseif ($aging === 'credits') {
		$clause .= ' AND ' . $outstanding . ' < -0.009';
	} elseif ($aging === 'settled') {
		$clause .= ' AND ABS(' . $outstanding . ') <= 0.009';
	}
	if ($attention === 'missing_due') {
		$clause .= ' AND ' . $outstanding . ' > 0.009 AND ' . $dueDate . ' IS NULL';
	} elseif ($attention === 'on_hold') {
		$clause .= ' AND ' . $outstanding . ' > 0.009 AND st.hold = 1';
	} elseif ($attention === 'zero_unsettled') {
		$clause .= ' AND ABS(' . $outstanding . ') <= 0.009 AND st.settled = 0';
	} elseif ($attention === 'unapplied_credit') {
		$clause .= ' AND ' . $outstanding . ' < -0.009';
	}
	if ($dueFrom !== '') {
		$clause .= ' AND ' . $dueDate . " >= '" . $dueFrom . "'";
	}
	if ($dueTo !== '') {
		$clause .= ' AND ' . $dueDate . " < DATE_ADD('" . $dueTo . "', INTERVAL 1 DAY)";
	}
	if ($minimumOutstanding !== null) {
		$clause .= ' AND ' . $outstanding . ' >= ' . (float) $minimumOutstanding;
	}
	if ($maximumOutstanding !== null) {
		$clause .= ' AND ' . $outstanding . ' <= ' . (float) $maximumOutstanding;
	}
	return $clause;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
	biSupplierError('method_not_allowed', 'Use GET or POST for read-only supplier report queries.', 405);
}
if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biSupplierError('unauthorized', 'An authenticated ERP session is required.', 401);
}

session_write_close();
$AllowAnyone = true;
$PathPrefix = $BiRootPath . DIRECTORY_SEPARATOR;
include_once($BiRootPath . '/includes/session.inc');

$input = biSupplierInput();
$action = isset($input['action']) ? strtolower(trim((string) $input['action'])) : 'summary';
if (!in_array($action, array('summary', 'ageing', 'trend', 'suppliers', 'details', 'lookups', 'export'), true)) {
	biSupplierError('invalid_action', 'The requested supplier report action is not supported.', 400);
}

$today = date('Y-m-d');
$preset = isset($input['preset']) ? strtolower(trim((string) $input['preset'])) : '';
$defaultStart = $preset === 'all' ? '1000-01-01' : date('Y-01-01');
$start = biSupplierDate(isset($input['start']) ? $input['start'] : '', $defaultStart);
$end = biSupplierDate(isset($input['end']) ? $input['end'] : '', $today);
if ($start > $end) {
	biSupplierError('invalid_date_range', 'The start date cannot be after the end date.', 400);
}

$search = isset($input['search']) ? trim((string) $input['search']) : '';
if (strlen($search) > 100) {
	biSupplierError('invalid_search', 'Search text is limited to 100 characters.', 400);
}
$supplierSelection = isset($input['supplierIds']) ? $input['supplierIds'] : (isset($input['supplierId']) ? $input['supplierId'] : '');
$supplierIds = biSupplierListInput($supplierSelection, '/^[A-Za-z0-9._-]{1,20}$/', 100, 'Supplier selection');
$supplierTypes = array_map('intval', biSupplierListInput(isset($input['supplierTypes']) ? $input['supplierTypes'] : '', '/^[0-9]{1,4}$/', 25, 'Supplier type selection'));
$paymentTerms = biSupplierListInput(isset($input['paymentTerms']) ? $input['paymentTerms'] : '', '/^[A-Za-z0-9_-]{1,10}$/', 25, 'Payment terms selection');
$transactionTypes = array_map('intval', biSupplierListInput(isset($input['transactionTypes']) ? $input['transactionTypes'] : '', '/^[0-9]{1,4}$/', 50, 'Transaction type selection'));
$status = isset($input['status']) ? strtolower(trim((string) $input['status'])) : 'all';
if (!in_array($status, array('all', 'outstanding', 'settled', 'overdue'), true)) {
	$status = 'all';
}
$aging = isset($input['aging']) ? strtolower(trim((string) $input['aging'])) : 'all';
if (!in_array($aging, array('all', 'current', 'due', 'overdue1', 'overdue2', 'credits', 'settled'), true)) {
	$aging = 'all';
}
$attention = isset($input['attention']) ? strtolower(trim((string) $input['attention'])) : 'all';
if (!in_array($attention, array('all', 'missing_due', 'on_hold', 'zero_unsettled', 'unapplied_credit'), true)) {
	$attention = 'all';
}
$dueFrom = biSupplierDate(isset($input['dueFrom']) ? $input['dueFrom'] : '', '');
$dueTo = biSupplierDate(isset($input['dueTo']) ? $input['dueTo'] : '', '');
if ($dueFrom !== '' && $dueTo !== '' && $dueFrom > $dueTo) {
	biSupplierError('invalid_due_date_range', 'The due-date start cannot be after the due-date end.', 400);
}
$minimumOutstanding = biSupplierOptionalNumber(isset($input['minOutstanding']) ? $input['minOutstanding'] : '', 'Minimum outstanding');
$maximumOutstanding = biSupplierOptionalNumber(isset($input['maxOutstanding']) ? $input['maxOutstanding'] : '', 'Maximum outstanding');
if ($minimumOutstanding !== null && $maximumOutstanding !== null && $minimumOutstanding > $maximumOutstanding) {
	biSupplierError('invalid_amount_range', 'Minimum outstanding cannot exceed maximum outstanding.', 400);
}

$pageSize = isset($input['pageSize']) ? (int) $input['pageSize'] : 50;
if (!in_array($pageSize, array(25, 50, 100, 250, 500), true)) {
	$pageSize = 50;
}
$page = isset($input['page']) ? max(1, min(2000, (int) $input['page'])) : 1;
$offset = ($page - 1) * $pageSize;
$sort = isset($input['sort']) ? strtolower(trim((string) $input['sort'])) : 'supplier';
$direction = isset($input['direction']) && strtolower($input['direction']) === 'desc' ? 'DESC' : 'ASC';

$threshold1 = isset($_SESSION['PastDueDays1']) ? max(1, (int) $_SESSION['PastDueDays1']) : 30;
$threshold2 = isset($_SESSION['PastDueDays2']) ? max($threshold1 + 1, (int) $_SESSION['PastDueDays2']) : 60;
$asOf = mysqli_real_escape_string($db, $end);
$startSql = mysqli_real_escape_string($db, $start);
$endSql = mysqli_real_escape_string($db, $end);

$amount = '(st.ovamount + st.ovgst)';
$outstanding = '(st.ovamount + st.ovgst - st.alloc)';
$billOutstanding = '(CASE WHEN ' . $outstanding . ' > 0 THEN ' . $outstanding . ' ELSE 0 END)';
$paidOnBill = '(CASE WHEN ' . $amount . ' > 0 THEN LEAST(GREATEST(st.alloc, 0), ' . $amount . ') ELSE 0 END)';
$paymentAmount = '(CASE WHEN ' . $amount . ' < 0 THEN -' . $amount . ' ELSE 0 END)';
$effectiveDueDate = "(CASE WHEN st.duedate IS NOT NULL AND st.duedate <> '0000-00-00' THEN st.duedate WHEN pt.termsindicator IS NULL OR pt.termsindicator = '' THEN NULL WHEN pt.daysbeforedue > 0 THEN DATE_ADD(st.trandate, INTERVAL pt.daysbeforedue DAY) ELSE DATE_ADD(DATE_ADD(st.trandate, INTERVAL 1 MONTH), INTERVAL (pt.dayinfollowingmonth - DAYOFMONTH(st.trandate)) DAY) END)";
$dueDateSource = "(CASE WHEN st.duedate IS NOT NULL AND st.duedate <> '0000-00-00' THEN 'Recorded due date' WHEN pt.termsindicator IS NULL OR pt.termsindicator = '' THEN 'Missing due date and payment terms' ELSE 'Calculated from payment terms' END)";
$daysOverdue = "(CASE WHEN " . $effectiveDueDate . " IS NULL THEN NULL ELSE DATEDIFF('" . $asOf . "', " . $effectiveDueDate . ") END)";
$ageBucket = "(CASE WHEN ABS(" . $outstanding . ") <= 0.009 THEN 'settled' WHEN " . $outstanding . " < -0.009 THEN 'credits' WHEN " . $effectiveDueDate . " IS NULL THEN 'undated' WHEN " . $effectiveDueDate . " > '" . $asOf . "' THEN 'current' WHEN " . $daysOverdue . " < " . $threshold1 . " THEN 'due' WHEN " . $daysOverdue . " < " . $threshold2 . " THEN 'overdue1' ELSE 'overdue2' END)";
$rowFilter = biSupplierFilterClause($status, $aging, $attention, $dueFrom, $dueTo, $minimumOutstanding, $maximumOutstanding, $outstanding, $daysOverdue, $effectiveDueDate, $asOf, $threshold1, $threshold2);
$baseFrom = ' FROM supptrans st
	INNER JOIN suppliers s ON st.supplierno = s.supplierid
	LEFT JOIN paymentterms pt ON s.paymentterms = pt.termsindicator
	LEFT JOIN systypes ty ON st.type = ty.typeid
	LEFT JOIN currencies c ON s.currcode = c.currabrev
	LEFT JOIN suppliertype stp ON s.supptype = stp.typeid';
$metadata = array(
	'date_range' => array('start' => $start, 'end' => $end),
	'as_of_date' => $end,
	'aging_thresholds' => array('overdue_1_days' => $threshold1, 'overdue_2_days' => $threshold2),
	'authorization' => array('database_name' => isset($_SESSION['DatabaseName']) ? $_SESSION['DatabaseName'] : '', 'mode' => 'active ERP session'),
	'freshness' => array('mode' => 'live_source', 'as_of_utc' => gmdate('Y-m-d H:i:s')),
	'lineage' => 'suppliers + supptrans + suppallocs-backed allocations + paymentterms + systypes',
	'formula' => 'net balance = ovamount + ovgst - alloc; paid on bills = positive allocation; payments/credits = negative supplier transactions',
);

try {
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	if (!$context->canUseSalesAnalytics()) {
		biSupplierError('forbidden', 'You are not authorized to use business intelligence.', 403);
	}

	if ($action === 'lookups') {
		$supplierRows = biSupplierBindAndFetch($db, 'SELECT s.supplierid, s.suppname, s.currcode, s.supptype, s.paymentterms, COALESCE(stp.typename, \'\') AS supplier_type, COALESCE(pt.terms, \'\') AS payment_terms FROM suppliers s LEFT JOIN suppliertype stp ON s.supptype = stp.typeid LEFT JOIN paymentterms pt ON s.paymentterms = pt.termsindicator ORDER BY s.suppname, s.supplierid', '', array());
		$supplierTypeRows = biSupplierBindAndFetch($db, 'SELECT typeid, typename FROM suppliertype ORDER BY typename, typeid', '', array());
		$paymentTermRows = biSupplierBindAndFetch($db, 'SELECT termsindicator, terms FROM paymentterms ORDER BY terms, termsindicator', '', array());
		$typeRows = biSupplierBindAndFetch($db, 'SELECT DISTINCT ty.typeid, ty.typename FROM systypes ty INNER JOIN supptrans st ON st.type = ty.typeid ORDER BY ty.typename, ty.typeid', '', array());
		biSupplierResponse(array('ok' => true, 'data' => array('suppliers' => $supplierRows, 'supplier_types' => $supplierTypeRows, 'payment_terms' => $paymentTermRows, 'transaction_types' => $typeRows, 'metadata' => $metadata)), 200);
	}

	$params = array();
	$types = '';
	$asOfWhere = biSupplierClause($search, $supplierIds, $supplierTypes, $paymentTerms, $transactionTypes, $params, $types, "st.trandate <= '" . $endSql . "'");
	$activityParams = array();
	$activityTypes = '';
	$activityWhere = biSupplierClause($search, $supplierIds, $supplierTypes, $paymentTerms, $transactionTypes, $activityParams, $activityTypes, "st.trandate BETWEEN '" . $startSql . "' AND '" . $endSql . "'");

	if ($action === 'summary') {
		$summarySql = 'SELECT
			COALESCE(SUM(' . $outstanding . '), 0) AS net_balance,
			COALESCE(SUM(CASE WHEN ' . $amount . ' > 0 THEN ' . $amount . ' ELSE 0 END), 0) AS total_bills,
			COALESCE(SUM(' . $paidOnBill . '), 0) AS paid_allocated,
			COALESCE(SUM(' . $billOutstanding . '), 0) AS open_payables,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $effectiveDueDate . ' > \'' . $asOf . '\' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS current_amount,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $effectiveDueDate . ' <= \'' . $asOf . '\' AND ' . $daysOverdue . ' >= 0 AND ' . $daysOverdue . ' < ' . $threshold1 . ' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS due_now,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . $threshold1 . ' AND ' . $daysOverdue . ' < ' . $threshold2 . ' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS overdue_1,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . $threshold2 . ' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS overdue_2,
			COALESCE(SUM(CASE WHEN ' . $outstanding . ' < -0.009 THEN -' . $outstanding . ' ELSE 0 END), 0) AS unapplied_credits,
			COUNT(DISTINCT s.supplierid) AS supplier_count,
			COUNT(DISTINCT CASE WHEN ' . $billOutstanding . ' > 0 THEN s.supplierid ELSE NULL END) AS open_supplier_count,
			COUNT(CASE WHEN ' . $billOutstanding . ' > 0 THEN 1 ELSE NULL END) AS open_bill_count,
			COUNT(DISTINCT CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . $threshold1 . ' THEN s.supplierid ELSE NULL END) AS overdue_supplier_count,
			COUNT(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $effectiveDueDate . ' IS NULL THEN 1 ELSE NULL END) AS missing_due_date,
			COUNT(CASE WHEN ' . $billOutstanding . ' > 0 AND st.hold = 1 THEN 1 ELSE NULL END) AS on_hold_open_bill,
			COUNT(CASE WHEN ABS(' . $outstanding . ') <= 0.009 AND st.settled = 0 THEN 1 ELSE NULL END) AS zero_balance_unsettled,
			COALESCE(SUM(CASE WHEN ' . $amount . ' < 0 AND st.trandate BETWEEN \'' . $startSql . '\' AND \'' . $endSql . '\' THEN -' . $amount . ' ELSE 0 END), 0) AS payment_activity,
			COALESCE(SUM(CASE WHEN ' . $amount . ' > 0 AND st.trandate BETWEEN \'' . $startSql . '\' AND \'' . $endSql . '\' THEN ' . $amount . ' ELSE 0 END), 0) AS purchase_activity,
			COALESCE(SUM(CASE WHEN ' . $amount . ' > 0 AND st.trandate BETWEEN \'' . $startSql . '\' AND \'' . $endSql . '\' THEN ' . $paidOnBill . ' ELSE 0 END), 0) AS allocated_activity,
			COUNT(DISTINCT s.currcode) AS currency_count,
			MAX(st.trandate) AS last_transaction_date,
			MAX(CASE WHEN ' . $amount . ' < 0 THEN st.trandate ELSE NULL END) AS last_payment_activity_date
		' . $baseFrom . $asOfWhere . $rowFilter;
		$rows = biSupplierBindAndFetch($db, $summarySql, $types, $params);
		$summary = isset($rows[0]) ? $rows[0] : array();
		$currencyParams = array();
		$currencyTypes = '';
		$currencyWhere = biSupplierClause($search, $supplierIds, $supplierTypes, $paymentTerms, $transactionTypes, $currencyParams, $currencyTypes, "st.trandate <= '" . $endSql . "'");
		$currencyWhere .= $rowFilter;
		$currencyRows = biSupplierBindAndFetch($db, 'SELECT s.currcode AS currency,
			COALESCE(SUM(' . $outstanding . '), 0) AS net_balance,
			COALESCE(SUM(' . $billOutstanding . '), 0) AS open_payables,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . $threshold1 . ' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS overdue,
			COALESCE(SUM(' . $paidOnBill . '), 0) AS paid_allocated
			' . $baseFrom . $currencyWhere . ' GROUP BY s.currcode ORDER BY s.currcode', $currencyTypes, $currencyParams);
		$summary = biSupplierCastRows(array($summary));
		$summary = isset($summary[0]) ? $summary[0] : array();
		biSupplierResponse(array('ok' => true, 'data' => array('summary' => $summary, 'currency_breakdown' => biSupplierCastRows($currencyRows), 'metadata' => $metadata)), 200);
	}

	if ($action === 'ageing') {
		$ageRows = biSupplierBindAndFetch($db, 'SELECT ' . $ageBucket . ' AS bucket,
			COUNT(DISTINCT s.supplierid) AS supplier_count,
			COUNT(*) AS transaction_count,
			COALESCE(SUM(CASE WHEN ' . $outstanding . ' > 0 THEN ' . $billOutstanding . ' ELSE ' . $outstanding . ' END), 0) AS amount
			' . $baseFrom . $asOfWhere . $rowFilter . ' GROUP BY ' . $ageBucket . ' ORDER BY FIELD(bucket, \'current\', \'due\', \'overdue1\', \'overdue2\', \'credits\', \'undated\', \'settled\')', $types, $params);
		biSupplierResponse(array('ok' => true, 'data' => array('ageing' => biSupplierCastRows($ageRows), 'metadata' => $metadata)), 200);
	}

	if ($action === 'trend') {
		$trendRows = biSupplierBindAndFetch($db, 'SELECT DATE_FORMAT(st.trandate, \'%Y-%m-01\') AS period_start,
			COALESCE(SUM(CASE WHEN ' . $amount . ' > 0 THEN ' . $amount . ' ELSE 0 END), 0) AS purchases,
			COALESCE(SUM(CASE WHEN ' . $amount . ' < 0 THEN -' . $amount . ' ELSE 0 END), 0) AS payments,
			COALESCE(SUM(' . $paidOnBill . '), 0) AS allocated,
			COUNT(*) AS transaction_count,
			COUNT(DISTINCT s.supplierid) AS supplier_count
			' . $baseFrom . $activityWhere . $rowFilter . ' GROUP BY DATE_FORMAT(st.trandate, \'%Y-%m-01\') ORDER BY period_start', $activityTypes, $activityParams);
		biSupplierResponse(array('ok' => true, 'data' => array('trend' => biSupplierCastRows($trendRows), 'metadata' => $metadata)), 200);
	}

	if ($action === 'suppliers') {
		$supplierFilterParams = array();
		$supplierFilterTypes = '';
		$supplierWhere = biSupplierClause($search, $supplierIds, $supplierTypes, $paymentTerms, $transactionTypes, $supplierFilterParams, $supplierFilterTypes, "st.trandate <= '" . $endSql . "'");
		$supplierWhere .= $rowFilter;
		$supplierSortMap = array(
			'supplier' => 's.suppname', 'balance' => 'net_balance', 'paid' => 'paid_allocated',
			'overdue' => 'overdue_total', 'transactions' => 'transaction_count', 'lastpaid' => 's.lastpaiddate',
		);
		$supplierSort = isset($supplierSortMap[$sort]) ? $supplierSortMap[$sort] : 's.suppname';
		$countRows = biSupplierBindAndFetch($db, 'SELECT COUNT(*) AS total_rows FROM (SELECT s.supplierid
			' . $baseFrom . $supplierWhere . ' GROUP BY s.supplierid) supplier_rows', $supplierFilterTypes, $supplierFilterParams);
		$countRow = isset($countRows[0]) ? $countRows[0] : array('total_rows' => 0);
		$totalRows = (int) $countRow['total_rows'];
		$supplierSql = 'SELECT s.supplierid, s.suppname, s.currcode, c.decimalplaces AS currency_decimals,
			s.suppliersince, s.email, COALESCE(s.telephone, s.phn, \'\') AS telephone,
			COALESCE(stp.typename, \'\') AS supplier_type, COALESCE(pt.terms, \'\') AS payment_terms,
			s.lastpaid, s.lastpaiddate,
			(SELECT sc.contact FROM suppliercontacts sc WHERE sc.supplierid = s.supplierid ORDER BY sc.ordercontact DESC, sc.contact LIMIT 1) AS primary_contact,
			(SELECT sc.email FROM suppliercontacts sc WHERE sc.supplierid = s.supplierid ORDER BY sc.ordercontact DESC, sc.contact LIMIT 1) AS primary_contact_email,
			COUNT(*) AS transaction_count,
			COALESCE(SUM(CASE WHEN ' . $amount . ' > 0 THEN ' . $amount . ' ELSE 0 END), 0) AS total_bills,
			COALESCE(SUM(' . $paidOnBill . '), 0) AS paid_allocated,
			COALESCE(SUM(' . $outstanding . '), 0) AS net_balance,
			COALESCE(SUM(' . $billOutstanding . '), 0) AS open_payables,
			COUNT(CASE WHEN ' . $billOutstanding . ' > 0 THEN 1 ELSE NULL END) AS open_bill_count,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $effectiveDueDate . ' > \'' . $asOf . '\' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS current_amount,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $effectiveDueDate . ' <= \'' . $asOf . '\' AND ' . $daysOverdue . ' >= 0 AND ' . $daysOverdue . ' < ' . $threshold1 . ' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS due_now,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . $threshold1 . ' AND ' . $daysOverdue . ' < ' . $threshold2 . ' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS overdue_1,
			COALESCE(SUM(CASE WHEN ' . $billOutstanding . ' > 0 AND ' . $daysOverdue . ' >= ' . $threshold2 . ' THEN ' . $billOutstanding . ' ELSE 0 END), 0) AS overdue_2,
			COALESCE(MAX(CASE WHEN ' . $billOutstanding . ' > 0 THEN ' . $daysOverdue . ' ELSE NULL END), 0) AS max_days_overdue,
			COALESCE(SUM(CASE WHEN ' . $amount . ' > 0 AND st.trandate BETWEEN \'' . $startSql . '\' AND \'' . $endSql . '\' THEN ' . $amount . ' ELSE 0 END), 0) AS activity_bills,
			COALESCE(SUM(CASE WHEN ' . $amount . ' < 0 AND st.trandate BETWEEN \'' . $startSql . '\' AND \'' . $endSql . '\' THEN -' . $amount . ' ELSE 0 END), 0) AS activity_paid,
			MAX(CASE WHEN ' . $amount . ' < 0 THEN st.trandate ELSE NULL END) AS last_payment_date
			' . $baseFrom . $supplierWhere . ' GROUP BY s.supplierid, s.suppname, s.currcode, c.decimalplaces, s.suppliersince, s.email, s.telephone, s.phn, stp.typename, pt.terms, s.lastpaid, s.lastpaiddate
			ORDER BY ' . $supplierSort . ' ' . $direction . ', s.supplierid ASC LIMIT ' . (int) $pageSize . ' OFFSET ' . (int) $offset;
		$supplierRows = biSupplierBindAndFetch($db, $supplierSql, $supplierFilterTypes, $supplierFilterParams);
		biSupplierResponse(array('ok' => true, 'data' => array('rows' => biSupplierCastRows($supplierRows), 'pagination' => array('page' => $page, 'page_size' => $pageSize, 'total_rows' => $totalRows, 'total_pages' => $totalRows > 0 ? (int) ceil($totalRows / $pageSize) : 1), 'metadata' => $metadata)), 200);
	}

	if ($action === 'details' || $action === 'export') {
		$detailParams = array();
		$detailTypes = '';
		$detailWhere = biSupplierClause($search, $supplierIds, $supplierTypes, $paymentTerms, $transactionTypes, $detailParams, $detailTypes, "st.trandate BETWEEN '" . $startSql . "' AND '" . $endSql . "'");
		$detailWhere .= $rowFilter;
		$countRows = biSupplierBindAndFetch($db, 'SELECT COUNT(*) AS total_rows
			' . $baseFrom . $detailWhere, $detailTypes, $detailParams);
		$totalRows = isset($countRows[0]['total_rows']) ? (int) $countRows[0]['total_rows'] : 0;
		if ($action === 'export') {
			if ($totalRows > 50000) {
				biSupplierError('export_too_large', 'This export contains more than 50,000 transactions. Narrow the date range or filters first.', 413, array('total_rows' => $totalRows, 'maximum_rows' => 50000));
			}
			$exportSql = 'SELECT s.supplierid, s.suppname, s.currcode, ty.typename AS transaction_type, st.transno, st.suppreference,
				st.trandate, ' . $effectiveDueDate . ' AS due_date, ' . $dueDateSource . ' AS due_date_source,
				' . $daysOverdue . ' AS days_overdue, ' . $ageBucket . ' AS age_bucket,
				' . $amount . ' AS total_amount, ' . $paidOnBill . ' AS paid_amount, ' . $paymentAmount . ' AS payment_amount,
				' . $outstanding . ' AS outstanding, st.alloc AS allocated_amount, st.settled, st.hold, pt.terms AS payment_terms, st.transtext
				' . $baseFrom . $detailWhere . ' ORDER BY st.trandate ASC, st.id ASC LIMIT 50000';
			$exportRows = biSupplierCastRows(biSupplierBindAndFetch($db, $exportSql, $detailTypes, $detailParams));
			$exporter = new \SAHamid\BI\Export\XlsxExporter();
			$exporter->download('supplier-relationship-intelligence-' . date('Ymd-His') . '.xlsx', 'Supplier Relationship Intelligence', array(
				'Company / database' => $context->getCompanyName() . ' / ' . $context->getDatabaseName(),
				'Date range' => $start . ' to ' . $end,
				'As-of date' => $end,
				'Suppliers' => $supplierIds ? count($supplierIds) . ' selected' : 'All suppliers',
				'Supplier types' => $supplierTypes ? implode(', ', $supplierTypes) : 'All supplier types',
				'Payment terms' => $paymentTerms ? implode(', ', $paymentTerms) : 'All payment terms',
				'Transaction types' => $transactionTypes ? implode(', ', $transactionTypes) : 'All transaction types',
				'Currencies' => 'All currencies; reconciled separately',
				'Rows' => (string) $totalRows,
				'Aging thresholds' => $threshold1 . ' / ' . $threshold2 . ' days',
			), array(
				'supplierid' => 'Supplier code', 'suppname' => 'Supplier', 'currcode' => 'Currency', 'transaction_type' => 'Transaction type',
				'transno' => 'Transaction no.', 'suppreference' => 'Supplier reference', 'trandate' => 'Transaction date', 'due_date' => 'Due date',
				'days_overdue' => 'Days overdue', 'age_bucket' => 'Age bucket', 'due_date_source' => 'Due date source', 'total_amount' => 'Document total', 'paid_amount' => 'Paid / allocated',
				'payment_amount' => 'Payment / credit', 'outstanding' => 'Outstanding', 'allocated_amount' => 'ERP allocation', 'settled' => 'Settled',
				'hold' => 'On hold', 'payment_terms' => 'Payment terms', 'transtext' => 'Comments',
			), $exportRows);
		}
		$sortMap = array('date' => 'st.trandate', 'due_date' => $effectiveDueDate, 'supplier' => 's.suppname', 'total' => 'total_amount', 'paid' => 'paid_amount', 'outstanding' => 'outstanding', 'days' => 'days_overdue');
		$detailSort = isset($sortMap[$sort]) ? $sortMap[$sort] : 'st.trandate';
		$detailSql = 'SELECT s.supplierid, s.suppname, s.currcode, ty.typename AS transaction_type, st.transno, st.suppreference,
			st.trandate, ' . $effectiveDueDate . ' AS due_date, ' . $dueDateSource . ' AS due_date_source,
			' . $daysOverdue . ' AS days_overdue, ' . $ageBucket . ' AS age_bucket,
			' . $amount . ' AS total_amount, ' . $paidOnBill . ' AS paid_amount, ' . $paymentAmount . ' AS payment_amount,
			' . $outstanding . ' AS outstanding, st.alloc AS allocated_amount, st.settled, st.hold, pt.terms AS payment_terms, st.transtext
			' . $baseFrom . $detailWhere . ' ORDER BY ' . $detailSort . ' ' . $direction . ', st.id ASC LIMIT ' . (int) $pageSize . ' OFFSET ' . (int) $offset;
		$detailRows = biSupplierBindAndFetch($db, $detailSql, $detailTypes, $detailParams);
		biSupplierResponse(array('ok' => true, 'data' => array('rows' => biSupplierCastRows($detailRows), 'pagination' => array('page' => $page, 'page_size' => $pageSize, 'total_rows' => $totalRows, 'total_pages' => $totalRows > 0 ? (int) ceil($totalRows / $pageSize) : 1), 'metadata' => $metadata)), 200);
	}
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biSupplierError($exception->getErrorCode(), $exception->getMessage(), $exception->getHttpStatus(), $exception->getDetails());
} catch (\Throwable $exception) {
	error_log('[bi] unhandled supplier report endpoint failure: ' . get_class($exception));
	biSupplierError('bi_unavailable', 'The supplier report service is temporarily unavailable.', 503);
}
