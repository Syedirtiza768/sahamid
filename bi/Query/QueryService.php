<?php

namespace SAHamid\BI\Query;

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Metrics\MetricRegistry;
use SAHamid\BI\Security\AuthorizationContext;

class QueryService
{
	private $db;
	private $registry;
	private $cache;
	private $logger;
	private $cacheTtlSeconds;

	public function __construct($db, MetricRegistry $registry = null, CacheStore $cache = null, QueryLogger $logger = null, $cacheTtlSeconds = 900)
	{
		$this->db = $db;
		$this->registry = $registry === null ? new MetricRegistry() : $registry;
		$this->cache = $cache === null ? new CacheStore($db) : $cache;
		$this->logger = $logger === null ? new QueryLogger($db) : $logger;
		$this->cacheTtlSeconds = max(60, (int) $cacheTtlSeconds);
	}

	public function execute(QueryRequest $request, AuthorizationContext $context)
	{
		$startedAt = microtime(true);
		try {
			$result = $this->executeInternal($request, $context);
			$this->logger->log($context, $request, $result->getMetadata()['cache'] === 'hit' ? 'cache_hit' : 'success', (int) round((microtime(true) - $startedAt) * 1000), count($result->getRows()));
			return $result;
		} catch (BIException $exception) {
			$this->logger->log($context, $request, 'error', (int) round((microtime(true) - $startedAt) * 1000), 0, $exception->getErrorCode());
			throw $exception;
		}
	}

	/**
	 * Return source rows that support the invoice-value aggregate.
	 *
	 * Drill-through uses the same metric definition, date rules, join keys, and
	 * authorization scope as the aggregate query. It is intentionally not
	 * cached: supporting rows should reflect the active source at request time.
	 */
	public function executeInvoiceDrillThrough(QueryRequest $request, AuthorizationContext $context, $invoiceNo = null)
	{
		$startedAt = microtime(true);
		try {
			$result = $this->executeInvoiceDrillThroughInternal($request, $context, $invoiceNo);
			$this->logger->log($context, $request, 'success', (int) round((microtime(true) - $startedAt) * 1000), count($result->getRows()));
			return $result;
		} catch (BIException $exception) {
			$this->logger->log($context, $request, 'error', (int) round((microtime(true) - $startedAt) * 1000), 0, $exception->getErrorCode());
			throw $exception;
		}
	}

	private function executeInternal(QueryRequest $request, AuthorizationContext $context)
	{
		if (!$context->canUseSalesAnalytics()) {
			throw new BIException('forbidden', 'You are not authorized to use sales analytics.', 403);
		}
		if (count($request->getMetricIds()) !== 1) {
			throw new BIException('unsupported_request', 'The first BI query slice accepts exactly one metric.', 400);
		}
		if ($request->getDimensions()) {
			throw new BIException('unsupported_request', 'Breakdowns are not available in the first BI query slice.', 400);
		}

		$metric = $this->registry->get($request->getMetricIds()[0]);
		if (!$metric->isExecutable()) {
			throw new BIException(
				'metric_unavailable',
				'This metric is ' . $metric->getStatus() . ' and has no approved numeric result yet.',
				409,
				array('metric' => $metric->toArray())
			);
		}
		if ($metric->getPermission() !== '' && !$context->canUseSalesAnalytics()) {
			throw new BIException('forbidden', 'You are not authorized to use this metric.', 403);
		}

		$salespersonCode = $this->resolveSalespersonFilter($request, $context);
		$cacheKey = CacheKey::forRequest($request, $context, array($metric->getId() => $metric->getVersion()));
		$cached = $this->cache->read($cacheKey);
		if ($cached !== null && isset($cached['rows'], $cached['metadata'])) {
			$metadata = $cached['metadata'];
			$metadata['cache'] = 'hit';
			return new QueryResult($cached['rows'], $metadata, isset($cached['warnings']) ? $cached['warnings'] : array());
		}

		if ($metric->getHandler() === 'sales_invoice_value') {
			$result = $this->executeInvoiceValue($request, $context, $metric, $salespersonCode);
		} else {
			throw new BIException('metric_unavailable', 'The registered metric implementation is not available.', 409);
		}

		$warnings = $result->getWarnings();
		if (!$this->cache->isAvailable()) {
			$warnings[] = 'BI cache migration has not been installed; this result was computed live.';
		}
		$metadata = $result->getMetadata();
		$metadata['cache'] = 'miss';
		$cachePayload = array('rows' => $result->getRows(), 'metadata' => $metadata, 'warnings' => $warnings);
		$this->cache->write($cacheKey, $cachePayload, $this->cacheTtlSeconds);
		return new QueryResult($result->getRows(), $metadata, $warnings);
	}

	private function executeInvoiceDrillThroughInternal(QueryRequest $request, AuthorizationContext $context, $invoiceNo)
	{
		if (!$context->canUseSalesAnalytics()) {
			throw new BIException('forbidden', 'You are not authorized to use sales analytics.', 403);
		}
		if ($request->getMetricIds() !== array('sales.invoice_value')) {
			throw new BIException('unsupported_request', 'Invoice evidence is available only for the invoice-value metric.', 400);
		}
		if ($request->getDimensions()) {
			throw new BIException('unsupported_request', 'Breakdowns are not available for invoice evidence.', 400);
		}

		$metric = $this->registry->get('sales.invoice_value');
		if (!$metric->isExecutable()) {
			throw new BIException(
				'metric_unavailable',
				'The invoice-value metric is ' . $metric->getStatus() . ' and its evidence is not published yet.',
				409,
				array('metric' => $metric->toArray())
			);
		}

		if ($invoiceNo !== null) {
			$invoiceNo = trim((string) $invoiceNo);
			if ($invoiceNo === '' || preg_match('/^\d{1,20}$/', $invoiceNo) !== 1) {
				throw new BIException('invalid_invoice', 'The invoice number must contain only digits.', 400);
			}
		}

		$salespersonCode = $this->resolveSalespersonFilter($request, $context);
		$range = $request->getDateRange();
		$start = $range['start'];
		$end = $range['end'];
		$sql = 'SELECT i.invoiceno, i.invoicesdate, i.salescaseref,
				d.invoicelineno, d.invoiceoptionno, d.stkcode, d.narrative,
				d.unitprice, d.discountpercent, d.quantity, o.quantity AS option_quantity,
				d.unitprice * (1 - d.discountpercent) * d.quantity * o.quantity AS line_value
			FROM invoice i
			INNER JOIN invoiceoptions o ON i.invoiceno = o.invoiceno
			INNER JOIN invoicedetails d ON i.invoiceno = d.invoiceno
				AND d.invoicelineno = o.invoicelineno
				AND d.invoiceoptionno = o.invoiceoptionno
			LEFT JOIN salescase sc ON sc.salescaseref = i.salescaseref
			LEFT JOIN salesman sm ON sm.salesmanname = sc.salesman
			WHERE i.returned = 0
			AND i.inprogress = 0
			AND i.invoicesdate BETWEEN ? AND ?';
		if ($invoiceNo !== null) {
			$sql .= ' AND i.invoiceno = ?';
		}
		if ($salespersonCode !== null) {
			$sql .= ' AND sm.salesmancode = ?';
		}
		$sql .= ' ORDER BY i.invoicesdate, i.invoiceno, d.invoicelineno, d.invoiceoptionno LIMIT ' . (int) $request->getLimit();

		$stmt = @mysqli_prepare($this->db, $sql);
		if (!$stmt) {
			throw new BIException('source_query_failed', 'The invoice evidence query could not be prepared.', 503);
		}
		if ($invoiceNo !== null && $salespersonCode !== null) {
			mysqli_stmt_bind_param($stmt, 'ssss', $start, $end, $invoiceNo, $salespersonCode);
		} elseif ($invoiceNo !== null) {
			mysqli_stmt_bind_param($stmt, 'sss', $start, $end, $invoiceNo);
		} elseif ($salespersonCode !== null) {
			mysqli_stmt_bind_param($stmt, 'sss', $start, $end, $salespersonCode);
		} else {
			mysqli_stmt_bind_param($stmt, 'ss', $start, $end);
		}
		$startedQueryAt = microtime(true);
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'The invoice evidence query failed; no rows were returned.', 503);
		}
		$result = mysqli_stmt_get_result($stmt);
		if (!$result) {
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'The invoice evidence result could not be read.', 503);
		}
		$rows = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$row['invoiceno'] = (int) $row['invoiceno'];
			$row['unitprice'] = (float) $row['unitprice'];
			$row['discountpercent'] = (float) $row['discountpercent'];
			$row['quantity'] = (float) $row['quantity'];
			$row['option_quantity'] = (float) $row['option_quantity'];
			$row['line_value'] = (float) $row['line_value'];
			$rows[] = $row;
		}
		mysqli_free_result($result);
		mysqli_stmt_close($stmt);

		return new QueryResult($rows, array(
			'metric' => $metric->toArray(),
			'date_range' => $range,
			'authorization' => array(
				'database_name' => $context->getDatabaseName(),
				'salesperson_scope' => $salespersonCode,
			),
			'freshness' => array('mode' => 'live_source', 'as_of_utc' => gmdate('Y-m-d H:i:s')),
			'performance' => array('elapsed_ms' => (int) round((microtime(true) - $startedQueryAt) * 1000)),
			'lineage' => $metric->getLineage(),
			'validation_status' => $metric->getStatus(),
			'drill_through' => array(
				'invoice_no' => $invoiceNo,
				'limit' => $request->getLimit(),
				'grain' => $metric->getGrain(),
			),
		));
	}

	private function resolveSalespersonFilter(QueryRequest $request, AuthorizationContext $context)
	{
		$filter = $request->getFilter('salesperson');
		$requested = null;
		if ($filter !== null && $filter['operator'] !== 'in_scope') {
			if ($filter['operator'] !== 'eq' || !is_string($filter['value']) || $filter['value'] === '') {
				throw new BIException('invalid_filter', 'The first BI query slice accepts one salesperson code.', 400);
			}
			$requested = $filter['value'];
		}

		if ($context->hasSalespersonScope()) {
			$scope = $context->getSalespersonCode();
			if ($scope === null || $scope === '') {
				throw new BIException('scope_unavailable', 'Your ERP user is not mapped to a salesperson scope.', 403);
			}
			if ($requested !== null && $requested !== $scope) {
				throw new BIException('forbidden', 'The requested salesperson is outside your authorized scope.', 403);
			}
			return $scope;
		}
		return $requested;
	}

	private function executeInvoiceValue(QueryRequest $request, AuthorizationContext $context, $metric, $salespersonCode)
	{
		$range = $request->getDateRange();
		$sql = 'SELECT COALESCE(SUM(d.unitprice * (1 - d.discountpercent) * d.quantity * o.quantity), 0) AS metric_value,
				COUNT(DISTINCT i.invoiceno) AS invoice_count,
				COUNT(*) AS detail_option_rows,
				MIN(i.invoicesdate) AS first_invoice_date,
				MAX(i.invoicesdate) AS last_invoice_date
			FROM invoice i
			INNER JOIN invoiceoptions o ON i.invoiceno = o.invoiceno
			INNER JOIN invoicedetails d ON i.invoiceno = d.invoiceno
				AND d.invoicelineno = o.invoicelineno
				AND d.invoiceoptionno = o.invoiceoptionno
			LEFT JOIN salescase sc ON sc.salescaseref = i.salescaseref
			LEFT JOIN salesman sm ON sm.salesmanname = sc.salesman
			WHERE i.returned = 0
			AND i.inprogress = 0
			AND i.invoicesdate BETWEEN ? AND ?';
		$types = 'ss';
		$start = $range['start'];
		$end = $range['end'];
		if ($salespersonCode !== null) {
			// invoice.salesperson is blank in the live data; case ownership is
			// the canonical populated assignment used by existing reports.
			$sql .= ' AND sm.salesmancode = ?';
			$types .= 's';
		}

		$stmt = @mysqli_prepare($this->db, $sql);
		if (!$stmt) {
			throw new BIException('source_query_failed', 'The invoice source query could not be prepared.', 503);
		}
		if ($salespersonCode !== null) {
			mysqli_stmt_bind_param($stmt, $types, $start, $end, $salespersonCode);
		} else {
			mysqli_stmt_bind_param($stmt, $types, $start, $end);
		}
		$startedAt = microtime(true);
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'The invoice source query failed; no value was returned.', 503);
		}
		mysqli_stmt_bind_result($stmt, $metricValue, $invoiceCount, $detailOptionRows, $firstDate, $lastDate);
		$hasRow = mysqli_stmt_fetch($stmt);
		mysqli_stmt_close($stmt);
		$elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);
		if (!$hasRow) {
			throw new BIException('source_query_failed', 'The invoice source returned no aggregate row.', 503);
		}

		$metadata = array(
			'metric' => $metric->toArray(),
			'date_range' => $range,
			'authorization' => array(
				'database_name' => $context->getDatabaseName(),
				'salesperson_scope' => $salespersonCode,
			),
			'freshness' => array('mode' => 'live_source', 'as_of_utc' => gmdate('Y-m-d H:i:s')),
			'performance' => array('elapsed_ms' => $elapsedMs),
			'lineage' => $metric->getLineage(),
			'validation_status' => $metric->getStatus(),
		);
		$rows = array(array(
			'metric_id' => $metric->getId(),
			'value' => (float) $metricValue,
			'invoice_count' => (int) $invoiceCount,
			'detail_option_rows' => (int) $detailOptionRows,
			'first_invoice_date' => $firstDate,
			'last_invoice_date' => $lastDate,
		));
		return new QueryResult($rows, $metadata);
	}
}
