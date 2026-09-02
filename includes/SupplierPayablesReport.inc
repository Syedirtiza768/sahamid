<?php
/*
 * Shared query and calculation helpers for SupplierPayablesReport.php.
 *
 * The report deliberately uses the existing AP ledger as its source of truth:
 * supptrans, suppallocs, suppliers, banktrans, paymentmethods and GL tags.
 * No production metrics are cached or fabricated here.
 */

if (!function_exists('SP_ReportH')) {

function SP_ReportH($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function SP_ReportToday() {
	return date('Y-m-d');
}

function SP_ReportValidDate($value, $fallback) {
	$value = trim((string)$value);
	if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
		$parts = explode('-', $value);
		if (checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
			return $value;
		}
	}
	return $fallback;
}

function SP_ReportDefaultFilters() {
	$today = SP_ReportToday();
	return array(
		'as_of' => $today,
		'invoice_from' => '',
		'invoice_to' => '',
		'payment_from' => date('Y-m-01'),
		'payment_to' => $today,
		'supplier' => '',
		'supplier_type' => 'all',
		'invoice_status' => 'all',
		'payment_status' => 'all',
		'due_from' => '',
		'due_to' => '',
		'aging_bucket' => 'all',
		'currency' => 'all',
		'payment_method' => 'all',
		'project' => '',
		'cost_center' => 'all',
		'sort' => 'outstanding',
		'direction' => 'desc',
		'page' => 1,
		'view' => 'overview',
	);
}

function SP_ReportReadFilters($source) {
	$filters = SP_ReportDefaultFilters();
	$source = is_array($source) ? $source : array();

	$filters['as_of'] = SP_ReportValidDate(isset($source['as_of']) ? $source['as_of'] : '', $filters['as_of']);
	foreach (array('invoice_from', 'invoice_to', 'due_from', 'due_to') as $key) {
		$filters[$key] = SP_ReportValidDate(isset($source[$key]) ? $source[$key] : '', '');
	}
	$filters['payment_from'] = SP_ReportValidDate(isset($source['payment_from']) ? $source['payment_from'] : '', $filters['payment_from']);
	$filters['payment_to'] = SP_ReportValidDate(isset($source['payment_to']) ? $source['payment_to'] : '', $filters['payment_to']);

	foreach (array('supplier', 'project') as $key) {
		$filters[$key] = isset($source[$key]) ? trim((string)$source[$key]) : '';
		$filters[$key] = mb_substr($filters[$key], 0, 120);
	}

	$filters['supplier_type'] = isset($source['supplier_type']) && preg_match('/^\d+$/', (string)$source['supplier_type']) ? (string)(int)$source['supplier_type'] : 'all';
	$filters['invoice_status'] = isset($source['invoice_status']) && in_array($source['invoice_status'], array('all', 'open', 'paid', 'overdue', 'on_hold', 'current'), true) ? $source['invoice_status'] : 'all';
	$filters['payment_status'] = isset($source['payment_status']) && in_array($source['payment_status'], array('all', 'completed', 'pending'), true) ? $source['payment_status'] : 'all';
	$filters['aging_bucket'] = isset($source['aging_bucket']) && in_array($source['aging_bucket'], array('all', 'current', '1_30', '31_60', '61_90', '90_plus'), true) ? $source['aging_bucket'] : 'all';
	$filters['currency'] = isset($source['currency']) && preg_match('/^[A-Za-z]{3}$/', (string)$source['currency']) ? strtoupper((string)$source['currency']) : 'all';
	$filters['payment_method'] = isset($source['payment_method']) && preg_match('/^\d+$/', (string)$source['payment_method']) ? (string)(int)$source['payment_method'] : 'all';
	$filters['cost_center'] = isset($source['cost_center']) && preg_match('/^\d+$/', (string)$source['cost_center']) ? (string)(int)$source['cost_center'] : 'all';
	$filters['sort'] = isset($source['sort']) && in_array($source['sort'], array('supplier', 'outstanding', 'overdue', 'next_due', 'total_invoiced', 'total_paid', 'open_invoices'), true) ? $source['sort'] : 'outstanding';
	$filters['direction'] = isset($source['direction']) && $source['direction'] === 'asc' ? 'asc' : 'desc';
	$filters['page'] = isset($source['page']) && preg_match('/^\d+$/', (string)$source['page']) ? max(1, (int)$source['page']) : 1;
	$filters['view'] = isset($source['view']) && in_array($source['view'], array('overview', 'suppliers', 'payables', 'payments', 'aging'), true) ? $source['view'] : 'overview';

	if ($filters['invoice_from'] !== '' && $filters['invoice_to'] !== '' && $filters['invoice_from'] > $filters['invoice_to']) {
		$tmp = $filters['invoice_from'];
		$filters['invoice_from'] = $filters['invoice_to'];
		$filters['invoice_to'] = $tmp;
	}
	if ($filters['payment_from'] > $filters['payment_to']) {
		$tmp = $filters['payment_from'];
		$filters['payment_from'] = $filters['payment_to'];
		$filters['payment_to'] = $tmp;
	}
	if ($filters['due_from'] !== '' && $filters['due_to'] !== '' && $filters['due_from'] > $filters['due_to']) {
		$tmp = $filters['due_from'];
		$filters['due_from'] = $filters['due_to'];
		$filters['due_to'] = $tmp;
	}

	return $filters;
}

function SP_ReportCompleteFilters($filters) {
	$complete = SP_ReportDefaultFilters();
	$complete['as_of'] = $filters['as_of'];
	$complete['payment_from'] = $filters['payment_from'];
	$complete['payment_to'] = $filters['payment_to'];
	$complete['view'] = 'overview';
	return $complete;
}

function SP_ReportFiltersQuery($filters, $overrides = array()) {
	$query = $filters;
	foreach ($overrides as $key => $value) {
		$query[$key] = $value;
	}
	foreach ($query as $key => $value) {
		if ($value === '' || $value === 'all' || ($key === 'page' && ((int)$value === 1))) {
			unset($query[$key]);
		}
	}
	return http_build_query($query, '', '&amp;');
}

function SP_ReportSqlQuote($value) {
	return "'" . DB_escape_string((string)$value) . "'";
}

function SP_ReportHasColumn($db, $table, $column) {
	static $cache = array();
	$key = $table . '.' . $column;
	if (isset($cache[$key])) {
		return $cache[$key];
	}
	$result = DB_query("SHOW COLUMNS FROM `" . $table . "` LIKE " . SP_ReportSqlQuote($column), $db, '', '', false, false);
	$cache[$key] = $result && DB_error_no($db) == 0 && DB_num_rows($result) > 0;
	return $cache[$key];
}

function SP_ReportReversalPredicate($db, $alias) {
	return SP_ReportHasColumn($db, 'supptrans', 'reversed') ? " AND COALESCE(" . $alias . ".reversed,0)=0" : '';
}

function SP_ReportAllocationJoin($filters) {
	if ($filters['as_of'] >= SP_ReportToday()) {
		return '';
	}
	$asOf = SP_ReportSqlQuote($filters['as_of']);
	return " LEFT JOIN (
			SELECT transid, SUM(amt) AS alloc_asof
			FROM (
				SELECT transid_allocfrom AS transid, amt, datealloc FROM suppallocs
				UNION ALL
				SELECT transid_allocto AS transid, amt, datealloc FROM suppallocs
			) AS allocation_rows
			WHERE datealloc <= " . $asOf . "
			GROUP BY transid
		) AS alloc_asof ON alloc_asof.transid = st.id ";
}

function SP_ReportBalanceExpression($filters, $alias = 'st') {
	if ($filters['as_of'] >= SP_ReportToday()) {
		return "((" . $alias . ".ovamount + " . $alias . ".ovgst) - " . $alias . ".alloc)";
	}
	return "((" . $alias . ".ovamount + " . $alias . ".ovgst) - COALESCE(alloc_asof.alloc_asof,0))";
}

function SP_ReportDueDateExpression($alias = 'st', $termsAlias = 'pt') {
	return "(CASE WHEN " . $alias . ".duedate IS NULL OR " . $alias . ".duedate='0000-00-00' THEN
		CASE WHEN COALESCE(" . $termsAlias . ".daysbeforedue,0)>0 THEN DATE_ADD(" . $alias . ".trandate, INTERVAL " . $termsAlias . ".daysbeforedue DAY)
		ELSE DATE_ADD(DATE_ADD(" . $alias . ".trandate, INTERVAL 1 MONTH), INTERVAL (COALESCE(" . $termsAlias . ".dayinfollowingmonth,0) - DAYOFMONTH(" . $alias . ".trandate)) DAY) END
		ELSE " . $alias . ".duedate END)";
}

function SP_ReportPaymentJoin() {
	return " LEFT JOIN (
			SELECT type, transno, MAX(banktransid) AS banktransid,
				MAX(bankact) AS bankact, MAX(ref) AS bankref,
				MAX(banktranstype) AS banktranstype, MAX(transdate) AS bankdate,
				MAX(amount) AS bankamount, MAX(currcode) AS bankcurrency
			FROM banktrans
			WHERE type=22
			GROUP BY type, transno
		) AS bt ON bt.type=st.type AND bt.transno=st.transno
		LEFT JOIN paymentmethods AS pm ON pm.paymentname=bt.banktranstype ";
}

function SP_ReportDimensionJoin() {
	return " LEFT JOIN (
			SELECT gt.type, gt.typeno,
				GROUP_CONCAT(DISTINCT NULLIF(gt.jobref,'')) AS project_refs,
				GROUP_CONCAT(DISTINCT CASE WHEN gt.tag > 0 THEN CONCAT(gt.tag, ':', COALESCE(t.tagdescription,'')) END) AS cost_centers
			FROM gltrans AS gt
			LEFT JOIN tags AS t ON t.tagref=gt.tag
			GROUP BY gt.type, gt.typeno
		) AS gl_dim ON gl_dim.type=st.type AND gl_dim.typeno=st.transno ";
}

function SP_ReportSupplierWhere($filters, $supplierAlias = 's') {
	$where = '';
	if ($filters['supplier'] !== '') {
		$search = DB_escape_string($filters['supplier']);
		$where .= " AND (" . $supplierAlias . ".supplierid LIKE '%" . $search . "%' OR " . $supplierAlias . ".suppname LIKE '%" . $search . "%')";
	}
	if ($filters['supplier_type'] !== 'all') {
		$where .= " AND " . $supplierAlias . ".supptype=" . (int)$filters['supplier_type'];
	}
	if ($filters['currency'] !== 'all') {
		$where .= " AND " . $supplierAlias . ".currcode=" . SP_ReportSqlQuote($filters['currency']);
	}
	return $where;
}

function SP_ReportDimensionWhere($filters, $alias = 'st') {
	$where = '';
	if ($filters['project'] !== '') {
		$project = DB_escape_string($filters['project']);
		$where .= " AND EXISTS (SELECT 1 FROM gltrans AS gl_filter_project WHERE gl_filter_project.type=" . $alias . ".type AND gl_filter_project.typeno=" . $alias . ".transno AND gl_filter_project.jobref LIKE '%" . $project . "%')";
	}
	if ($filters['cost_center'] !== 'all') {
		$where .= " AND EXISTS (SELECT 1 FROM gltrans AS gl_filter_tag WHERE gl_filter_tag.type=" . $alias . ".type AND gl_filter_tag.typeno=" . $alias . ".transno AND gl_filter_tag.tag=" . (int)$filters['cost_center'] . ")";
	}
	return $where;
}

function SP_ReportInvoiceWhere($filters, $balanceExpr, $alias = 'st') {
	$dueDateExpr = SP_ReportDueDateExpression($alias, 'pt');
	$where = " AND " . $alias . ".trandate <= " . SP_ReportSqlQuote($filters['as_of']);
	if ($filters['invoice_from'] !== '') {
		$where .= " AND " . $alias . ".trandate >= " . SP_ReportSqlQuote($filters['invoice_from']);
	}
	if ($filters['invoice_to'] !== '') {
		$where .= " AND " . $alias . ".trandate <= " . SP_ReportSqlQuote($filters['invoice_to']);
	}
	if ($filters['due_from'] !== '') {
		$where .= " AND " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['due_from']);
	}
	if ($filters['due_to'] !== '') {
		$where .= " AND " . $dueDateExpr . " <= " . SP_ReportSqlQuote($filters['due_to']);
	}

	switch ($filters['invoice_status']) {
		case 'open':
			$where .= " AND " . $balanceExpr . " > 0.009";
			break;
		case 'paid':
			$where .= " AND ABS(" . $balanceExpr . ") <= 0.009";
			break;
		case 'overdue':
			$where .= " AND " . $balanceExpr . " > 0.009 AND " . $dueDateExpr . " < " . SP_ReportSqlQuote($filters['as_of']);
			break;
		case 'on_hold':
			$where .= " AND " . $alias . ".hold=1 AND " . $balanceExpr . " > 0.009";
			break;
		case 'current':
			$where .= " AND " . $balanceExpr . " > 0.009 AND " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['as_of']);
			break;
	}

		switch ($filters['aging_bucket']) {
		case 'current':
			$where .= " AND " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['as_of']);
			break;
		case '1_30':
			$where .= " AND DATEDIFF(" . SP_ReportSqlQuote($filters['as_of']) . "," . $dueDateExpr . ") BETWEEN 1 AND 30";
			break;
		case '31_60':
			$where .= " AND DATEDIFF(" . SP_ReportSqlQuote($filters['as_of']) . "," . $dueDateExpr . ") BETWEEN 31 AND 60";
			break;
		case '61_90':
			$where .= " AND DATEDIFF(" . SP_ReportSqlQuote($filters['as_of']) . "," . $dueDateExpr . ") BETWEEN 61 AND 90";
			break;
		case '90_plus':
			$where .= " AND DATEDIFF(" . SP_ReportSqlQuote($filters['as_of']) . "," . $dueDateExpr . ") > 90";
			break;
	}
	return $where;
}

function SP_ReportPaymentWhere($filters, $alias = 'st') {
	$where = " AND " . $alias . ".trandate <= " . SP_ReportSqlQuote($filters['as_of']);
	$where .= " AND " . $alias . ".trandate >= " . SP_ReportSqlQuote($filters['payment_from']);
	$where .= " AND " . $alias . ".trandate <= " . SP_ReportSqlQuote($filters['payment_to']);
	if ($filters['payment_status'] === 'completed') {
		$where .= ' AND bt.banktransid IS NOT NULL';
	} elseif ($filters['payment_status'] === 'pending') {
		$where .= ' AND bt.banktransid IS NULL';
	}
	if ($filters['payment_method'] !== 'all') {
		$where .= ' AND pm.paymentid=' . (int)$filters['payment_method'];
	}
	return $where;
}

function SP_ReportRunRows($db, $sql, $errorMessage = '') {
	$result = DB_query($sql, $db, $errorMessage, '', false, false);
	if (!$result || DB_error_no($db) != 0) {
		return array();
	}
	$rows = array();
	while ($row = DB_fetch_assoc($result)) {
		$rows[] = $row;
	}
	return $rows;
}

function SP_ReportRunOne($db, $sql, $errorMessage = '') {
	$rows = SP_ReportRunRows($db, $sql, $errorMessage);
	return isset($rows[0]) ? $rows[0] : array();
}

function SP_ReportBaseDocumentSql($db, $filters, $select, $extraWhere = '', $orderBy = '', $limit = null, $offset = 0) {
	$balanceExpr = SP_ReportBalanceExpression($filters, 'st');
	$reversal = SP_ReportReversalPredicate($db, 'st');
	$allocationJoin = SP_ReportAllocationJoin($filters);
	$sql = "SELECT " . $select . "
		FROM supptrans AS st
		INNER JOIN suppliers AS s ON s.supplierid=st.supplierno
		LEFT JOIN systypes AS ty ON ty.typeid=st.type
		LEFT JOIN currencies AS c ON c.currabrev=s.currcode
		LEFT JOIN paymentterms AS pt ON pt.termsindicator=s.paymentterms
		" . SP_ReportDimensionJoin() . $allocationJoin . 
		" WHERE st.type IN (20,21,23,601)" . $reversal .
		SP_ReportSupplierWhere($filters, 's') .
		SP_ReportDimensionWhere($filters, 'st') .
		SP_ReportInvoiceWhere($filters, $balanceExpr, 'st') .
		$extraWhere;
	if ($orderBy !== '') {
		$sql .= ' ORDER BY ' . $orderBy;
	}
	if ($limit !== null) {
		$sql .= ' LIMIT ' . (int)$offset . ',' . (int)$limit;
	}
	return $sql;
}

function SP_ReportGetPayables($db, $filters, $limit = 50, $offset = 0) {
	$balanceExpr = SP_ReportBalanceExpression($filters, 'st');
	$select = "st.id, st.transno, st.type, COALESCE(ty.typename,'AP transaction') AS type_name,
		st.supplierno, s.suppname, st.suppreference, st.trandate, st.duedate,
		" . SP_ReportDueDateExpression('st', 'pt') . " AS effective_duedate,
		(st.ovamount+st.ovgst) AS original_amount, st.alloc AS current_allocated,
		" . $balanceExpr . " AS outstanding_amount, st.rate, s.currcode AS currency,
		COALESCE(c.decimalplaces,2) AS decimalplaces, COALESCE(pt.terms,'') AS payment_terms,
		st.hold, " . (SP_ReportHasColumn($db, 'supptrans', 'reversed') ? 'COALESCE(st.reversed,0)' : '0') . " AS reversed,
		COALESCE(gl_dim.project_refs,'') AS project_refs, COALESCE(gl_dim.cost_centers,'') AS cost_centers,
		COALESCE(st.transtext,'') AS notes,
		CASE WHEN " . $balanceExpr . " <= 0.009 THEN 'Paid'
			WHEN st.hold=1 THEN 'On hold'
			WHEN " . SP_ReportDueDateExpression('st', 'pt') . " < " . SP_ReportSqlQuote($filters['as_of']) . " THEN 'Overdue'
			ELSE 'Open' END AS payment_status";
	return SP_ReportBaseDocumentSql($db, $filters, $select, '', 'effective_duedate ASC, st.supplierno ASC, st.transno ASC', $limit, $offset);
}

function SP_ReportCountPayables($db, $filters) {
	$balanceExpr = SP_ReportBalanceExpression($filters, 'st');
	$select = 'COUNT(*) AS row_count';
	$row = SP_ReportRunOne($db, SP_ReportBaseDocumentSql($db, $filters, $select), _('Could not count supplier payables'));
	return isset($row['row_count']) ? (int)$row['row_count'] : 0;
}

function SP_ReportGetPayments($db, $filters, $limit = 50, $offset = 0) {
	$select = "st.id, st.transno, st.type, COALESCE(ty.typename,'Creditors Payment') AS type_name,
		st.supplierno, s.suppname, st.suppreference, st.trandate,
		ABS(st.ovamount+st.ovgst) AS payment_amount, ABS(st.ovamount+st.ovgst)/NULLIF(st.rate,0) AS payment_amount_base, st.rate,
		COALESCE(bt.bankdate,st.trandate) AS payment_date,
		COALESCE(s.currcode,bt.bankcurrency,'') AS currency,
		COALESCE(c.decimalplaces,2) AS decimalplaces,
		COALESCE(pm.paymentname,bt.banktranstype,'Not recorded') AS payment_method,
		COALESCE(bt.bankref,st.suppreference,'') AS payment_reference,
		COALESCE(bt.bankact,'') AS bank_account,
		CASE WHEN bt.banktransid IS NULL THEN 'Pending / not recorded' ELSE 'Completed' END AS payment_status,
		COALESCE(gl_dim.project_refs,'') AS project_refs, COALESCE(gl_dim.cost_centers,'') AS cost_centers";
	$reversal = SP_ReportReversalPredicate($db, 'st');
	$sql = "SELECT " . $select . "
		FROM supptrans AS st
		INNER JOIN suppliers AS s ON s.supplierid=st.supplierno
		LEFT JOIN systypes AS ty ON ty.typeid=st.type
		LEFT JOIN currencies AS c ON c.currabrev=s.currcode
		" . SP_ReportPaymentJoin() . SP_ReportDimensionJoin() .
		" WHERE st.type=22" . $reversal .
		SP_ReportSupplierWhere($filters, 's') .
		SP_ReportDimensionWhere($filters, 'st') .
		SP_ReportPaymentWhere($filters, 'st') .
		' ORDER BY COALESCE(bt.bankdate,st.trandate) DESC, st.transno DESC' .
		' LIMIT ' . (int)$offset . ',' . (int)$limit;
	return $sql;
}

function SP_ReportCountPayments($db, $filters) {
	$reversal = SP_ReportReversalPredicate($db, 'st');
	$sql = "SELECT COUNT(*) AS row_count
		FROM supptrans AS st
		INNER JOIN suppliers AS s ON s.supplierid=st.supplierno
		" . SP_ReportPaymentJoin() .
		" WHERE st.type=22" . $reversal .
		SP_ReportSupplierWhere($filters, 's') .
		SP_ReportDimensionWhere($filters, 'st') .
		SP_ReportPaymentWhere($filters, 'st');
	$row = SP_ReportRunOne($db, $sql, _('Could not count supplier payments'));
	return isset($row['row_count']) ? (int)$row['row_count'] : 0;
}

function SP_ReportGetAging($db, $filters) {
	$balanceExpr = SP_ReportBalanceExpression($filters, 'st');
	$dueDateExpr = SP_ReportDueDateExpression('st', 'pt');
	$select = "CASE
			WHEN " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['as_of']) . " THEN 'current'
			WHEN DATEDIFF(" . SP_ReportSqlQuote($filters['as_of']) . "," . $dueDateExpr . ") BETWEEN 1 AND 30 THEN '1_30'
			WHEN DATEDIFF(" . SP_ReportSqlQuote($filters['as_of']) . "," . $dueDateExpr . ") BETWEEN 31 AND 60 THEN '31_60'
			WHEN DATEDIFF(" . SP_ReportSqlQuote($filters['as_of']) . "," . $dueDateExpr . ") BETWEEN 61 AND 90 THEN '61_90'
			ELSE '90_plus' END AS aging_bucket,
		SUM(" . $balanceExpr . "/NULLIF(st.rate,0)) AS amount,
		COUNT(*) AS invoice_count";
	$sql = SP_ReportBaseDocumentSql($db, $filters, $select, '', '');
	$sql .= ' GROUP BY aging_bucket';
	$rows = SP_ReportRunRows($db, $sql, _('Could not retrieve supplier payable aging'));
	$ordered = array('current', '1_30', '31_60', '61_90', '90_plus');
	$byBucket = array();
	foreach ($rows as $row) {
		$byBucket[$row['aging_bucket']] = $row;
	}
	$output = array();
	foreach ($ordered as $bucket) {
		$output[] = array(
			'aging_bucket' => $bucket,
			'amount' => isset($byBucket[$bucket]['amount']) ? (float)$byBucket[$bucket]['amount'] : 0,
			'invoice_count' => isset($byBucket[$bucket]['invoice_count']) ? (int)$byBucket[$bucket]['invoice_count'] : 0,
		);
	}
	return $output;
}

function SP_ReportGetPaymentTiming($db, $filters) {
	$reversal = SP_ReportReversalPredicate($db, 'p');
	$invoiceReversal = SP_ReportReversalPredicate($db, 'i');
	$sql = "SELECT i.supplierno,
			SUM(a.amt/NULLIF(i.rate,0)) AS allocated_amount,
			SUM((a.amt/NULLIF(i.rate,0)) * DATEDIFF(p.trandate,i.trandate)) / NULLIF(SUM(a.amt/NULLIF(i.rate,0)),0) AS average_days,
			SUM(CASE WHEN p.trandate <= (CASE WHEN i.duedate IS NULL OR i.duedate='0000-00-00' THEN CASE WHEN COALESCE(pt.daysbeforedue,0)>0 THEN DATE_ADD(i.trandate, INTERVAL pt.daysbeforedue DAY) ELSE DATE_ADD(DATE_ADD(i.trandate, INTERVAL 1 MONTH), INTERVAL (COALESCE(pt.dayinfollowingmonth,0) - DAYOFMONTH(i.trandate)) DAY) END ELSE i.duedate END) THEN a.amt/NULLIF(i.rate,0) ELSE 0 END) / NULLIF(SUM(a.amt/NULLIF(i.rate,0)),0) * 100 AS on_time_rate,
			SUM(CASE WHEN p.trandate < (CASE WHEN i.duedate IS NULL OR i.duedate='0000-00-00' THEN CASE WHEN COALESCE(pt.daysbeforedue,0)>0 THEN DATE_ADD(i.trandate, INTERVAL pt.daysbeforedue DAY) ELSE DATE_ADD(DATE_ADD(i.trandate, INTERVAL 1 MONTH), INTERVAL (COALESCE(pt.dayinfollowingmonth,0) - DAYOFMONTH(i.trandate)) DAY) END ELSE i.duedate END) THEN a.amt/NULLIF(i.rate,0) ELSE 0 END) AS early_amount,
			SUM(CASE WHEN p.trandate = (CASE WHEN i.duedate IS NULL OR i.duedate='0000-00-00' THEN CASE WHEN COALESCE(pt.daysbeforedue,0)>0 THEN DATE_ADD(i.trandate, INTERVAL pt.daysbeforedue DAY) ELSE DATE_ADD(DATE_ADD(i.trandate, INTERVAL 1 MONTH), INTERVAL (COALESCE(pt.dayinfollowingmonth,0) - DAYOFMONTH(i.trandate)) DAY) END ELSE i.duedate END) THEN a.amt/NULLIF(i.rate,0) ELSE 0 END) AS on_time_amount,
		SUM(CASE WHEN p.trandate > (CASE WHEN i.duedate IS NULL OR i.duedate='0000-00-00' THEN CASE WHEN COALESCE(pt.daysbeforedue,0)>0 THEN DATE_ADD(i.trandate, INTERVAL pt.daysbeforedue DAY) ELSE DATE_ADD(DATE_ADD(i.trandate, INTERVAL 1 MONTH), INTERVAL (COALESCE(pt.dayinfollowingmonth,0) - DAYOFMONTH(i.trandate)) DAY) END ELSE i.duedate END) THEN a.amt/NULLIF(i.rate,0) ELSE 0 END) AS late_amount,
		COUNT(DISTINCT p.id) AS linked_payment_count
		FROM suppallocs AS a
		INNER JOIN supptrans AS p ON p.id=a.transid_allocfrom AND p.type=22
		INNER JOIN supptrans AS i ON i.id=a.transid_allocto AND i.type IN (20,21,23,601)
		INNER JOIN suppliers AS s ON s.supplierid=i.supplierno
		LEFT JOIN paymentterms AS pt ON pt.termsindicator=s.paymentterms
		WHERE a.datealloc <= " . SP_ReportSqlQuote($filters['as_of']) . $reversal . $invoiceReversal .
		" AND p.trandate >= " . SP_ReportSqlQuote($filters['payment_from']) .
		" AND p.trandate <= " . SP_ReportSqlQuote($filters['payment_to']) .
		SP_ReportSupplierWhere($filters, 's') .
		SP_ReportDimensionWhere($filters, 'i') .
		($filters['payment_method'] !== 'all' ? ' AND EXISTS (SELECT 1 FROM banktrans AS bt_filter JOIN paymentmethods AS pm_filter ON pm_filter.paymentname=bt_filter.banktranstype WHERE bt_filter.type=22 AND bt_filter.transno=p.transno AND pm_filter.paymentid=' . (int)$filters['payment_method'] . ')' : '') .
		' GROUP BY i.supplierno';
	return SP_ReportRunRows($db, $sql, _('Could not retrieve supplier payment timing'));
}

function SP_ReportGetSummary($db, $filters) {
	$balanceExpr = SP_ReportBalanceExpression($filters, 'st');
	$dueDateExpr = SP_ReportDueDateExpression('st', 'pt');
	$reversal = SP_ReportReversalPredicate($db, 'st');
	$documentSql = "FROM supptrans AS st
		INNER JOIN suppliers AS s ON s.supplierid=st.supplierno
		LEFT JOIN currencies AS c ON c.currabrev=s.currcode
		LEFT JOIN paymentterms AS pt ON pt.termsindicator=s.paymentterms
		" . SP_ReportDimensionJoin() . SP_ReportAllocationJoin($filters) .
		" WHERE st.type IN (20,21,23,601)" . $reversal .
		SP_ReportSupplierWhere($filters, 's') .
		SP_ReportDimensionWhere($filters, 'st') .
		SP_ReportInvoiceWhere($filters, $balanceExpr, 'st');
	$summaryRow = SP_ReportRunOne($db, "SELECT
			COALESCE(SUM(" . $balanceExpr . "/NULLIF(st.rate,0)),0) AS total_outstanding,
			COALESCE(SUM(CASE WHEN " . $dueDateExpr . " < " . SP_ReportSqlQuote($filters['as_of']) . " THEN " . $balanceExpr . "/NULLIF(st.rate,0) ELSE 0 END),0) AS total_overdue,
			COALESCE(SUM(CASE WHEN " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['as_of']) . " AND " . $dueDateExpr . " <= DATE_ADD(" . SP_ReportSqlQuote($filters['as_of']) . ",INTERVAL 7 DAY) THEN " . $balanceExpr . "/NULLIF(st.rate,0) ELSE 0 END),0) AS due_7,
			COALESCE(SUM(CASE WHEN " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['as_of']) . " AND " . $dueDateExpr . " <= DATE_ADD(" . SP_ReportSqlQuote($filters['as_of']) . ",INTERVAL 30 DAY) THEN " . $balanceExpr . "/NULLIF(st.rate,0) ELSE 0 END),0) AS due_30,
			COALESCE(SUM(CASE WHEN " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['as_of']) . " AND " . $dueDateExpr . " <= DATE_ADD(" . SP_ReportSqlQuote($filters['as_of']) . ",INTERVAL 60 DAY) THEN " . $balanceExpr . "/NULLIF(st.rate,0) ELSE 0 END),0) AS due_60,
			COALESCE(SUM(CASE WHEN " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['as_of']) . " AND " . $dueDateExpr . " <= DATE_ADD(" . SP_ReportSqlQuote($filters['as_of']) . ",INTERVAL 90 DAY) THEN " . $balanceExpr . "/NULLIF(st.rate,0) ELSE 0 END),0) AS due_90,
			COALESCE(SUM(CASE WHEN st.type IN (20,601) THEN (st.ovamount+st.ovgst)/NULLIF(st.rate,0) ELSE 0 END),0) AS total_invoiced,
			SUM(CASE WHEN st.type IN (20,21,601) AND " . $balanceExpr . " > 0.009 THEN 1 ELSE 0 END) AS unpaid_invoices,
			SUM(CASE WHEN st.hold=1 AND " . $balanceExpr . " > 0.009 THEN 1 ELSE 0 END) AS on_hold_invoices
		" . $documentSql, _('Could not retrieve supplier payable summary'));

	$paymentReversal = SP_ReportReversalPredicate($db, 'st');
	$paymentSql = "FROM supptrans AS st
		INNER JOIN suppliers AS s ON s.supplierid=st.supplierno
		" . SP_ReportPaymentJoin() . SP_ReportDimensionJoin() .
		" WHERE st.type=22" . $paymentReversal .
		SP_ReportSupplierWhere($filters, 's') .
		SP_ReportDimensionWhere($filters, 'st') .
		SP_ReportPaymentWhere($filters, 'st');
	$paymentRow = SP_ReportRunOne($db, "SELECT COALESCE(SUM(ABS(st.ovamount+st.ovgst)/NULLIF(st.rate,0)),0) AS paid_period,
			COUNT(*) AS payment_count
		" . $paymentSql, _('Could not retrieve supplier payment summary'));

	$activeWhere = " WHERE s.suppliersince <= " . SP_ReportSqlQuote($filters['as_of']) . SP_ReportSupplierWhere($filters, 's');
	$activeRow = SP_ReportRunOne($db, 'SELECT COUNT(*) AS active_suppliers FROM suppliers AS s' . $activeWhere, _('Could not count active suppliers'));
	$timing = SP_ReportGetPaymentTiming($db, $filters);
	$timingTotal = 0;
	$timingDays = 0;
	$onTimeAmount = 0;
	$earlyAmount = 0;
	$lateAmount = 0;
	$linkedPaymentCount = 0;
	foreach ($timing as $row) {
		$allocated = (float)$row['allocated_amount'];
		$timingTotal += $allocated;
		$timingDays += $allocated * (float)$row['average_days'];
		$earlyAmount += (float)$row['early_amount'];
		$onTimeAmount += (float)$row['on_time_amount'];
		$lateAmount += (float)$row['late_amount'];
		$linkedPaymentCount += isset($row['linked_payment_count']) ? (int)$row['linked_payment_count'] : 0;
	}

	return array(
		'total_outstanding' => isset($summaryRow['total_outstanding']) ? (float)$summaryRow['total_outstanding'] : 0,
		'total_overdue' => isset($summaryRow['total_overdue']) ? (float)$summaryRow['total_overdue'] : 0,
		'due_7' => isset($summaryRow['due_7']) ? (float)$summaryRow['due_7'] : 0,
		'due_30' => isset($summaryRow['due_30']) ? (float)$summaryRow['due_30'] : 0,
		'due_60' => isset($summaryRow['due_60']) ? (float)$summaryRow['due_60'] : 0,
		'due_90' => isset($summaryRow['due_90']) ? (float)$summaryRow['due_90'] : 0,
		'total_invoiced' => isset($summaryRow['total_invoiced']) ? (float)$summaryRow['total_invoiced'] : 0,
		'unpaid_invoices' => isset($summaryRow['unpaid_invoices']) ? (int)$summaryRow['unpaid_invoices'] : 0,
		'on_hold_invoices' => isset($summaryRow['on_hold_invoices']) ? (int)$summaryRow['on_hold_invoices'] : 0,
		'paid_period' => isset($paymentRow['paid_period']) ? (float)$paymentRow['paid_period'] : 0,
		'payment_count' => isset($paymentRow['payment_count']) ? (int)$paymentRow['payment_count'] : 0,
		'active_suppliers' => isset($activeRow['active_suppliers']) ? (int)$activeRow['active_suppliers'] : 0,
		'average_payment_days' => $timingTotal > 0 ? $timingDays / $timingTotal : null,
		'on_time_rate' => $timingTotal > 0 ? (($earlyAmount + $onTimeAmount) / $timingTotal) * 100 : null,
		'early_paid' => $earlyAmount,
		'on_time_paid' => $onTimeAmount,
		'late_paid' => $lateAmount,
		'timing_allocated' => $timingTotal,
		'linked_payment_count' => $linkedPaymentCount,
		'upcoming_cash' => isset($summaryRow['due_30']) ? (float)$summaryRow['due_30'] - (float)$summaryRow['total_overdue'] : 0,
	);
}

function SP_ReportGetSupplierSummary($db, $filters, $limit = 100, $offset = 0) {
	$balanceExpr = SP_ReportBalanceExpression($filters, 'st');
	$dueDateExpr = SP_ReportDueDateExpression('st', 'pt');
	$reversal = SP_ReportReversalPredicate($db, 'st');
	$paymentFrom = SP_ReportSqlQuote($filters['payment_from']);
	$paymentTo = SP_ReportSqlQuote($filters['payment_to']);
	$sql = "SELECT s.supplierid, s.suppname, s.supptype, COALESCE(suppliertype.typename,'Uncategorised') AS supplier_category,
		COALESCE(pt.terms,'') AS payment_terms, s.currcode AS currency, COALESCE(c.decimalplaces,2) AS decimalplaces,
		COALESCE(s.email,'') AS contact_email, COALESCE(s.phn,'') AS contact_phone,
		COALESCE(SUM(CASE WHEN st.type IN (20,601) THEN (st.ovamount+st.ovgst)/NULLIF(st.rate,0) ELSE 0 END),0) AS total_invoiced,
		COALESCE(SUM(CASE WHEN st.type=22 AND st.trandate >= " . $paymentFrom . " AND st.trandate <= " . $paymentTo . " THEN ABS(st.ovamount+st.ovgst)/NULLIF(st.rate,0) ELSE 0 END),0) AS total_paid,
		COALESCE(SUM(CASE WHEN st.type IN (20,21,23,601) THEN " . $balanceExpr . "/NULLIF(st.rate,0) ELSE 0 END),0) AS outstanding,
		COALESCE(SUM(CASE WHEN st.type IN (20,21,23,601) AND " . $dueDateExpr . " < " . SP_ReportSqlQuote($filters['as_of']) . " THEN " . $balanceExpr . "/NULLIF(st.rate,0) ELSE 0 END),0) AS overdue,
		MIN(CASE WHEN st.type IN (20,21,23,601) AND " . $balanceExpr . " > 0.009 AND " . $dueDateExpr . " >= " . SP_ReportSqlQuote($filters['as_of']) . " THEN " . $dueDateExpr . " ELSE NULL END) AS next_due,
		MAX(CASE WHEN st.type=22 THEN st.trandate ELSE NULL END) AS last_payment,
		SUM(CASE WHEN st.type IN (20,21,601) AND " . $balanceExpr . " > 0.009 THEN 1 ELSE 0 END) AS open_invoices,
		SUM(CASE WHEN st.type IN (20,21,601) AND " . $balanceExpr . " > 0.009 AND " . $dueDateExpr . " < " . SP_ReportSqlQuote($filters['as_of']) . " THEN 1 ELSE 0 END) AS overdue_invoices,
		SUM(CASE WHEN st.type IN (20,21,23,601) AND st.hold=1 AND " . $balanceExpr . " > 0.009 THEN 1 ELSE 0 END) AS open_holds,
		GROUP_CONCAT(DISTINCT NULLIF(gl_dim.project_refs,'')) AS project_refs,
		GROUP_CONCAT(DISTINCT NULLIF(gl_dim.cost_centers,'')) AS cost_centers,
		COALESCE(po.po_count,0) AS purchase_order_count
		FROM suppliers AS s
		LEFT JOIN suppliertype ON suppliertype.typeid=s.supptype
		LEFT JOIN paymentterms AS pt ON pt.termsindicator=s.paymentterms
		LEFT JOIN currencies AS c ON c.currabrev=s.currcode
		LEFT JOIN supptrans AS st ON st.supplierno=s.supplierid AND st.trandate <= " . SP_ReportSqlQuote($filters['as_of']) . $reversal .
		SP_ReportDimensionJoin() . SP_ReportAllocationJoin($filters) .
		" LEFT JOIN (SELECT supplierno, COUNT(*) AS po_count FROM purchorders GROUP BY supplierno) AS po ON po.supplierno=s.supplierid
		WHERE s.suppliersince <= " . SP_ReportSqlQuote($filters['as_of']) .
		SP_ReportSupplierWhere($filters, 's') .
		" GROUP BY s.supplierid, s.suppname, s.supptype, suppliertype.typename, pt.terms, s.currcode, c.decimalplaces, s.email, s.phn, po.po_count
		ORDER BY " . SP_ReportSupplierSort($filters) .
		" LIMIT " . (int)$offset . ',' . (int)$limit;
	$rows = SP_ReportRunRows($db, $sql, _('Could not retrieve supplier summary'));
	$timing = SP_ReportGetPaymentTiming($db, $filters);
	$timingBySupplier = array();
	foreach ($timing as $row) {
		$timingBySupplier[$row['supplierno']] = $row;
	}
	foreach ($rows as &$row) {
		$key = $row['supplierid'];
		$row['average_days'] = isset($timingBySupplier[$key]) ? (float)$timingBySupplier[$key]['average_days'] : null;
		$row['on_time_rate'] = isset($timingBySupplier[$key]) ? (float)$timingBySupplier[$key]['on_time_rate'] : null;
		$row['status'] = 'Active';
		$row['exposure'] = (float)$row['outstanding'];
	}
	unset($row);
	return $rows;
}

function SP_ReportSupplierSort($filters) {
	$direction = $filters['direction'] === 'asc' ? 'ASC' : 'DESC';
	switch ($filters['sort']) {
		case 'supplier': return 's.suppname ' . $direction;
		case 'overdue': return 'overdue ' . $direction;
		case 'next_due': return 'next_due ' . ($filters['direction'] === 'asc' ? 'ASC' : 'DESC');
		case 'total_invoiced': return 'total_invoiced ' . $direction;
		case 'total_paid': return 'total_paid ' . $direction;
		case 'open_invoices': return 'open_invoices ' . $direction;
		default: return 'outstanding ' . $direction;
	}
}

function SP_ReportGetTopSuppliers($db, $filters, $limit = 5) {
	$filters['sort'] = 'outstanding';
	$filters['direction'] = 'desc';
	return SP_ReportGetSupplierSummary($db, $filters, $limit, 0);
}

function SP_ReportGetPaymentAnalytics($db, $filters) {
	$reversal = SP_ReportReversalPredicate($db, 'st');
	$base = "FROM supptrans AS st
		INNER JOIN suppliers AS s ON s.supplierid=st.supplierno
		" . SP_ReportPaymentJoin() . SP_ReportDimensionJoin() .
		" WHERE st.type=22" . $reversal . SP_ReportSupplierWhere($filters, 's') . SP_ReportDimensionWhere($filters, 'st') . SP_ReportPaymentWhere($filters, 'st');
	$byMethod = SP_ReportRunRows($db, "SELECT COALESCE(pm.paymentname,bt.banktranstype,'Not recorded') AS label,
			COALESCE(SUM(ABS(st.ovamount+st.ovgst)/NULLIF(st.rate,0)),0) AS amount, COUNT(*) AS count " . $base . ' GROUP BY label ORDER BY amount DESC', _('Could not retrieve payment methods'));
	$byCurrency = SP_ReportRunRows($db, "SELECT COALESCE(s.currcode,bt.bankcurrency,'') AS label,
			COALESCE(SUM(ABS(st.ovamount+st.ovgst)/NULLIF(st.rate,0)),0) AS amount, COUNT(*) AS count " . $base . ' GROUP BY label ORDER BY amount DESC', _('Could not retrieve payment currencies'));
	$trend = SP_ReportRunRows($db, "SELECT DATE_FORMAT(COALESCE(bt.bankdate,st.trandate),'%Y-%m') AS label,
			COALESCE(SUM(ABS(st.ovamount+st.ovgst)/NULLIF(st.rate,0)),0) AS amount, COUNT(*) AS count " . $base . ' GROUP BY label ORDER BY label ASC', _('Could not retrieve payment trend'));
	return array('by_method' => $byMethod, 'by_currency' => $byCurrency, 'trend' => $trend);
}

function SP_ReportGetSupplierDetail($db, $filters, $supplierId) {
	$supplierId = DB_escape_string($supplierId);
	$detailFilters = $filters;
	$detailFilters['supplier'] = '';
	$balanceExpr = SP_ReportBalanceExpression($detailFilters, 'st');
	$detailDueDateExpr = SP_ReportDueDateExpression('st', 'pt');
	$reversal = SP_ReportReversalPredicate($db, 'st');
	$summary = SP_ReportRunOne($db, "SELECT s.supplierid, s.suppname, s.supptype, COALESCE(suppliertype.typename,'Uncategorised') AS supplier_category,
			COALESCE(pt.terms,'') AS payment_terms, s.currcode AS currency, COALESCE(c.decimalplaces,2) AS decimalplaces,
			COALESCE(s.email,'') AS contact_email, COALESCE(s.phn,'') AS contact_phone,
			COALESCE(s.address1,'') AS address1, COALESCE(s.address2,'') AS address2, COALESCE(s.address3,'') AS address3,
			COALESCE(SUM(CASE WHEN st.type IN (20,21,23,601) THEN " . $balanceExpr . "/NULLIF(st.rate,0) ELSE 0 END),0) AS outstanding,
			COALESCE(SUM(CASE WHEN st.type IN (20,601) THEN (st.ovamount+st.ovgst)/NULLIF(st.rate,0) ELSE 0 END),0) AS total_invoiced,
			COALESCE(SUM(CASE WHEN st.type=22 THEN ABS(st.ovamount+st.ovgst)/NULLIF(st.rate,0) ELSE 0 END),0) AS total_paid
		FROM suppliers AS s
		LEFT JOIN suppliertype ON suppliertype.typeid=s.supptype
		LEFT JOIN paymentterms AS pt ON pt.termsindicator=s.paymentterms
		LEFT JOIN currencies AS c ON c.currabrev=s.currcode
		LEFT JOIN supptrans AS st ON st.supplierno=s.supplierid AND st.trandate <= " . SP_ReportSqlQuote($filters['as_of']) . $reversal . SP_ReportDimensionJoin() . SP_ReportAllocationJoin($detailFilters) .
		" WHERE s.supplierid='" . $supplierId . "' GROUP BY s.supplierid,s.suppname,s.supptype,suppliertype.typename,pt.terms,s.currcode,c.decimalplaces,s.email,s.phn,s.address1,s.address2,s.address3", _('Could not retrieve supplier profile'));
	$transactions = SP_ReportRunRows($db, "SELECT st.id, st.transno, st.type, COALESCE(ty.typename,'AP transaction') AS type_name,
			st.suppreference, st.trandate, st.duedate, " . $detailDueDateExpr . " AS effective_duedate, (st.ovamount+st.ovgst) AS original_amount,
			" . $balanceExpr . " AS outstanding_amount, s.currcode AS currency, st.hold,
			CASE WHEN st.type=22 THEN 'Payment' WHEN st.hold=1 THEN 'On hold' WHEN " . $balanceExpr . " <= 0.009 THEN 'Paid' WHEN " . $detailDueDateExpr . " < " . SP_ReportSqlQuote($filters['as_of']) . " THEN 'Overdue' ELSE 'Open' END AS status
			FROM supptrans AS st INNER JOIN suppliers AS s ON s.supplierid=st.supplierno LEFT JOIN systypes AS ty ON ty.typeid=st.type LEFT JOIN paymentterms AS pt ON pt.termsindicator=s.paymentterms
			" . SP_ReportDimensionJoin() . SP_ReportAllocationJoin($detailFilters) .
			" WHERE st.supplierno='" . $supplierId . "' AND st.trandate <= " . SP_ReportSqlQuote($filters['as_of']) . $reversal . ' ORDER BY st.trandate DESC, st.id DESC LIMIT 20', _('Could not retrieve supplier activity'));
	$purchaseOrders = SP_ReportRunRows($db, "SELECT orderno, orddate, status, initiator, deliverydate FROM purchorders WHERE supplierno='" . $supplierId . "' ORDER BY orddate DESC LIMIT 10", _('Could not retrieve supplier purchase orders'));
	$timing = SP_ReportGetPaymentTiming($db, $detailFilters);
	$timingRow = array();
	foreach ($timing as $row) {
		if ($row['supplierno'] === $supplierId) {
			$timingRow = $row;
			break;
		}
	}
	$summary['average_days'] = isset($timingRow['average_days']) ? (float)$timingRow['average_days'] : null;
	$summary['on_time_rate'] = isset($timingRow['on_time_rate']) ? (float)$timingRow['on_time_rate'] : null;
	return array('summary' => $summary, 'transactions' => $transactions, 'purchase_orders' => $purchaseOrders);
}

function SP_ReportAgeBucket($dueDate, $asOfDate) {
	$due = new DateTime($dueDate);
	$asOf = new DateTime($asOfDate);
	$days = (int)$due->diff($asOf)->format('%r%a');
	if ($days <= 0) return 'current';
	if ($days <= 30) return '1_30';
	if ($days <= 60) return '31_60';
	if ($days <= 90) return '61_90';
	return '90_plus';
}

function SP_ReportAgeBucketLabel($bucket) {
	$labels = array('current' => 'Current', '1_30' => '1–30 days overdue', '31_60' => '31–60 days overdue', '61_90' => '61–90 days overdue', '90_plus' => 'More than 90 days overdue');
	return isset($labels[$bucket]) ? $labels[$bucket] : 'All aging buckets';
}

function SP_ReportFormatAmount($amount, $decimalPlaces = 2) {
	return number_format((float)$amount, (int)$decimalPlaces, '.', ',');
}

}
