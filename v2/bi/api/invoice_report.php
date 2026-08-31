<?php

/**
 * Enhanced Invoice Value Analysis endpoint.
 *
 * The published invoice-value formula is kept identical to the governed
 * MetricRegistry/QueryService implementation. All operations are read-only,
 * scoped through the ERP session, and bounded for browser performance.
 */

$BiRootPath = dirname(__DIR__, 3);
$PathPrefix = $BiRootPath . DIRECTORY_SEPARATOR;
include_once($BiRootPath . '/config.php');
if (isset($SessionSavePath)) {
	session_save_path($SessionSavePath);
}
session_start();
require_once($BiRootPath . '/bi/bootstrap.php');

header('Cache-Control: no-store');

function biInvoiceResponse(array $payload, $status)
{
	header('Content-Type: application/json; charset=utf-8');
	http_response_code((int) $status);
	echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	exit;
}

function biInvoiceError($code, $message, $status, array $details = array())
{
	biInvoiceResponse(array(
		'ok' => false,
		'error' => array('code' => $code, 'message' => $message, 'details' => $details),
	), $status);
}

function biInvoiceInput()
{
	$input = array();
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$raw = file_get_contents('php://input');
		if ($raw !== false && trim($raw) !== '') {
			$decoded = json_decode($raw, true);
			if (!is_array($decoded)) {
				biInvoiceError('invalid_json', 'The request body must be a JSON object.', 400);
			}
			$input = $decoded;
		}
	}
	if (!$input) {
		$input = $_GET;
	}
	return $input;
}

function biInvoiceDate($value, $fallback)
{
	$value = trim((string) $value);
	if ($value === '') {
		return $fallback;
	}
	$date = \DateTime::createFromFormat('!Y-m-d', $value);
	$errors = \DateTime::getLastErrors();
	if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) || $date->format('Y-m-d') !== $value) {
		biInvoiceError('invalid_date', 'Dates must use the YYYY-MM-DD format.', 400);
	}
	return $value;
}

function biInvoiceBindAndFetch($db, $sql, $types, array $params)
{
	$stmt = @mysqli_prepare($db, $sql);
	if (!$stmt) {
		throw new \SAHamid\BI\Exception\BIException('source_query_failed', 'The invoice report query could not be prepared.', 503);
	}
	$bindings = array($stmt, $types);
	foreach ($params as $index => $value) {
		$bindings[] = &$params[$index];
	}
	if (!call_user_func_array('mysqli_stmt_bind_param', $bindings)) {
		mysqli_stmt_close($stmt);
		throw new \SAHamid\BI\Exception\BIException('source_query_failed', 'The invoice report query could not bind its filters.', 503);
	}
	if (!mysqli_stmt_execute($stmt)) {
		mysqli_stmt_close($stmt);
		throw new \SAHamid\BI\Exception\BIException('source_query_failed', 'The invoice report query failed; no rows were returned.', 503);
	}
	$result = mysqli_stmt_get_result($stmt);
	if (!$result) {
		mysqli_stmt_close($stmt);
		throw new \SAHamid\BI\Exception\BIException('source_query_failed', 'The invoice report result could not be read.', 503);
	}
	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
	mysqli_free_result($result);
	mysqli_stmt_close($stmt);
	return $rows;
}

function biInvoiceWhere($start, $end, $salesperson, $search, $invoiceNo, &$params, &$types)
{
	$params = array($start, $end);
	$types = 'ss';
	$where = ' WHERE i.returned = 0
		AND i.inprogress = 0
		AND i.invoicesdate BETWEEN ? AND ?';
	if ($salesperson !== null) {
		$where .= ' AND sm.salesmancode = ?';
		$types .= 's';
		$params[] = $salesperson;
	}
	if ($invoiceNo !== '') {
		$where .= ' AND i.invoiceno = ?';
		$types .= 's';
		$params[] = $invoiceNo;
	}
	if ($search !== '') {
		$where .= ' AND (CAST(i.invoiceno AS CHAR) LIKE ? OR d.stkcode LIKE ? OR d.narrative LIKE ?)';
		$types .= 'sss';
		$needle = '%' . $search . '%';
		$params[] = $needle;
		$params[] = $needle;
		$params[] = $needle;
	}
	return $where;
}

function biInvoiceBaseFrom()
{
	return ' FROM invoice i
		INNER JOIN invoiceoptions o ON i.invoiceno = o.invoiceno
		INNER JOIN invoicedetails d ON i.invoiceno = d.invoiceno
			AND d.invoicelineno = o.invoicelineno
			AND d.invoiceoptionno = o.invoiceoptionno
		LEFT JOIN salescase sc ON sc.salescaseref = i.salescaseref
		LEFT JOIN salesman sm ON sm.salesmanname = sc.salesman';
}

function biInvoiceDetailRows(array $rows)
{
	foreach ($rows as &$row) {
		$row['invoiceno'] = (int) $row['invoiceno'];
		$row['unitprice'] = (float) $row['unitprice'];
		$row['discountpercent'] = (float) $row['discountpercent'];
		$row['quantity'] = (float) $row['quantity'];
		$row['option_quantity'] = (float) $row['option_quantity'];
		$row['line_value'] = (float) $row['line_value'];
	}
	unset($row);
	return $rows;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
	biInvoiceError('method_not_allowed', 'Use GET or POST for read-only invoice report queries.', 405);
}
if (empty($_SESSION['UserID']) || empty($_SESSION['DatabaseName'])) {
	biInvoiceError('unauthorized', 'An authenticated ERP session is required.', 401);
}

session_write_close();
$AllowAnyone = true;
include_once($BiRootPath . '/includes/session.inc');

$input = biInvoiceInput();
$action = isset($input['action']) ? strtolower(trim((string) $input['action'])) : 'summary';
if (!in_array($action, array('summary', 'trend', 'details', 'export'), true)) {
	biInvoiceError('invalid_action', 'The requested invoice report action is not supported.', 400);
}

$today = date('Y-m-d');
$requestedPreset = isset($input['preset']) ? strtolower(trim((string) $input['preset'])) : '';
$defaultStart = $requestedPreset === 'all' ? '1000-01-01' : date('Y-01-01');
$start = biInvoiceDate(isset($input['start']) ? $input['start'] : (isset($input['startDate']) ? $input['startDate'] : ''), $defaultStart);
$end = biInvoiceDate(isset($input['end']) ? $input['end'] : (isset($input['endDate']) ? $input['endDate'] : ''), $today);
if ($start > $end) {
	biInvoiceError('invalid_date_range', 'The start date cannot be after the end date.', 400);
}

$invoiceNo = isset($input['invoiceNo']) ? trim((string) $input['invoiceNo']) : '';
if ($invoiceNo !== '' && preg_match('/^\d{1,20}$/', $invoiceNo) !== 1) {
	biInvoiceError('invalid_invoice', 'The invoice number must contain only digits.', 400);
}

$search = isset($input['search']) ? trim((string) $input['search']) : '';
if (strlen($search) > 100) {
	biInvoiceError('invalid_search', 'Search text is limited to 100 characters.', 400);
}

$requestedSalesperson = isset($input['salesperson']) ? trim((string) $input['salesperson']) : '';
if ($requestedSalesperson !== '' && preg_match('/^[A-Za-z0-9._-]{1,40}$/', $requestedSalesperson) !== 1) {
	biInvoiceError('invalid_salesperson', 'Salesperson codes may contain letters, numbers, dots, underscores, or hyphens.', 400);
}

$sortMap = array(
	'date' => 'i.invoicesdate',
	'invoiceno' => 'i.invoiceno',
	'stkcode' => 'd.stkcode',
	'line_value' => 'line_value',
);
$sort = isset($input['sort']) && isset($sortMap[$input['sort']]) ? $input['sort'] : 'date';
$direction = isset($input['direction']) && strtolower($input['direction']) === 'desc' ? 'DESC' : 'ASC';
$pageSize = isset($input['pageSize']) ? (int) $input['pageSize'] : 50;
if (!in_array($pageSize, array(25, 50, 100, 250, 500), true)) {
	$pageSize = 50;
}
$page = isset($input['page']) ? max(1, min(2000, (int) $input['page'])) : 1;
$offset = ($page - 1) * $pageSize;

try {
	$context = (new \SAHamid\BI\Security\SessionAuthorizationAdapter($db))->resolve();
	if (!$context->canUseSalesAnalytics()) {
		biInvoiceError('forbidden', 'You are not authorized to use sales analytics.', 403);
	}
	$salesperson = null;
	if ($context->hasSalespersonScope()) {
		$salesperson = $context->getSalespersonCode();
		if ($salesperson === null || $salesperson === '') {
			biInvoiceError('scope_unavailable', 'Your ERP user is not mapped to a salesperson scope.', 403);
		}
		if ($requestedSalesperson !== '' && $requestedSalesperson !== $salesperson) {
			biInvoiceError('forbidden', 'The requested salesperson is outside your authorized scope.', 403);
		}
	} elseif ($requestedSalesperson !== '') {
		$salesperson = $requestedSalesperson;
	}

	$params = array();
	$types = '';
	$where = biInvoiceWhere($start, $end, $salesperson, $search, $invoiceNo, $params, $types);
	$baseFrom = biInvoiceBaseFrom();
	$formula = 'd.unitprice * (1 - d.discountpercent) * d.quantity * o.quantity';
	$metadata = array(
		'date_range' => array('start' => $start, 'end' => $end),
		'authorization' => array('database_name' => $context->getDatabaseName(), 'salesperson_scope' => $salesperson),
		'freshness' => array('mode' => 'live_source', 'as_of_utc' => gmdate('Y-m-d H:i:s')),
		'lineage' => 'invoice + invoicedetails + invoiceoptions; published metric formula: ' . $formula,
		'grain' => 'One invoice option detail row',
	);

	if ($action === 'summary') {
		$summaryRows = biInvoiceBindAndFetch($db, 'SELECT COALESCE(SUM(' . $formula . '), 0) AS total_value,
			COUNT(DISTINCT i.invoiceno) AS invoice_count,
			COUNT(*) AS detail_option_rows,
			MIN(i.invoicesdate) AS first_invoice_date,
			MAX(i.invoicesdate) AS last_invoice_date' . $baseFrom . $where, $types, $params);
		$row = isset($summaryRows[0]) ? $summaryRows[0] : array();
		$totalValue = isset($row['total_value']) ? (float) $row['total_value'] : 0.0;
		$invoiceCount = isset($row['invoice_count']) ? (int) $row['invoice_count'] : 0;
		biInvoiceResponse(array('ok' => true, 'data' => array(
			'summary' => array(
				'total_value' => $totalValue,
				'invoice_count' => $invoiceCount,
				'detail_option_rows' => isset($row['detail_option_rows']) ? (int) $row['detail_option_rows'] : 0,
				'average_invoice_value' => $invoiceCount > 0 ? $totalValue / $invoiceCount : 0,
				'first_invoice_date' => isset($row['first_invoice_date']) ? $row['first_invoice_date'] : null,
				'last_invoice_date' => isset($row['last_invoice_date']) ? $row['last_invoice_date'] : null,
			),
			'metadata' => $metadata,
		)), 200);
	}

	if ($action === 'trend') {
		$trendRows = biInvoiceBindAndFetch($db, "SELECT DATE_FORMAT(i.invoicesdate, '%Y-%m-01') AS period_start,
			COALESCE(SUM(" . $formula . "), 0) AS total_value,
			COUNT(DISTINCT i.invoiceno) AS invoice_count,
			COUNT(*) AS detail_option_rows" . $baseFrom . $where . ' GROUP BY DATE_FORMAT(i.invoicesdate, \'%Y-%m-01\') ORDER BY period_start ASC', $types, $params);
		foreach ($trendRows as &$trendRow) {
			$trendRow['total_value'] = (float) $trendRow['total_value'];
			$trendRow['invoice_count'] = (int) $trendRow['invoice_count'];
			$trendRow['detail_option_rows'] = (int) $trendRow['detail_option_rows'];
		}
		unset($trendRow);
		biInvoiceResponse(array('ok' => true, 'data' => array('trend' => $trendRows, 'metadata' => $metadata)), 200);
	}

	$countRows = biInvoiceBindAndFetch($db, 'SELECT COUNT(*) AS total_rows, COUNT(DISTINCT i.invoiceno) AS invoice_count' . $baseFrom . $where, $types, $params);
	$countRow = isset($countRows[0]) ? $countRows[0] : array();
	$totalRows = isset($countRow['total_rows']) ? (int) $countRow['total_rows'] : 0;

	if ($action === 'export') {
		if ($totalRows > 50000) {
			biInvoiceError('export_too_large', 'This export contains more than 50,000 detail rows. Narrow the date range or search first.', 413, array('total_rows' => $totalRows, 'maximum_rows' => 50000));
		}
		$exportSql = 'SELECT i.invoiceno, i.invoicesdate, i.salescaseref, COALESCE(sm.salesmanname, \'\') AS salesperson,
			d.stkcode, d.narrative, d.unitprice, d.discountpercent, d.quantity, o.quantity AS option_quantity,
			' . $formula . ' AS line_value' . $baseFrom . $where . ' ORDER BY i.invoicesdate ASC, i.invoiceno ASC, d.invoicelineno ASC, d.invoiceoptionno ASC LIMIT 50000';
		$exportRows = biInvoiceDetailRows(biInvoiceBindAndFetch($db, $exportSql, $types, $params));
		$exporter = new \SAHamid\BI\Export\XlsxExporter();
		$exporter->download('invoice-value-analysis-' . date('Ymd-His') . '.xlsx', 'Invoice Value Analysis', array(
			'Company / database' => $context->getCompanyName() . ' / ' . $context->getDatabaseName(),
			'Date range' => $start . ' to ' . $end,
			'Salesperson scope' => $salesperson === null ? 'All authorized salespeople' : $salesperson,
			'Rows' => (string) $totalRows,
			'Formula' => $formula,
		), array(
			'invoiceno' => 'Invoice no.',
			'invoicesdate' => 'Invoice date',
			'salescaseref' => 'Sales case',
			'salesperson' => 'Salesperson',
			'stkcode' => 'Item code',
			'narrative' => 'Narrative',
			'unitprice' => 'Unit price',
			'discountpercent' => 'Discount percent',
			'quantity' => 'Quantity',
			'option_quantity' => 'Option quantity',
			'line_value' => 'Line value',
		), $exportRows);
	}

	$detailSql = 'SELECT i.invoiceno, i.invoicesdate, i.salescaseref, COALESCE(sm.salesmanname, \'\') AS salesperson,
		d.stkcode, d.narrative, d.unitprice, d.discountpercent, d.quantity, o.quantity AS option_quantity,
		' . $formula . ' AS line_value' . $baseFrom . $where . ' ORDER BY ' . $sortMap[$sort] . ' ' . $direction . ', i.invoiceno ASC LIMIT ' . (int) $pageSize . ' OFFSET ' . (int) $offset;
	$detailRows = biInvoiceDetailRows(biInvoiceBindAndFetch($db, $detailSql, $types, $params));
	biInvoiceResponse(array('ok' => true, 'data' => array(
		'rows' => $detailRows,
		'pagination' => array(
			'page' => $page,
			'page_size' => $pageSize,
			'total_rows' => $totalRows,
			'total_pages' => $totalRows > 0 ? (int) ceil($totalRows / $pageSize) : 1,
			'invoice_count' => isset($countRow['invoice_count']) ? (int) $countRow['invoice_count'] : 0,
		),
		'metadata' => $metadata,
	)), 200);
} catch (\SAHamid\BI\Exception\BIException $exception) {
	biInvoiceError($exception->getErrorCode(), $exception->getMessage(), $exception->getHttpStatus(), $exception->getDetails());
} catch (\Throwable $exception) {
	error_log('[bi] unhandled invoice report endpoint failure: ' . get_class($exception));
	biInvoiceError('bi_unavailable', 'The invoice report service is temporarily unavailable.', 503);
}
