<?php

namespace SAHamid\BI\Reconciliation;

use SAHamid\BI\Exception\BIException;
use SAHamid\BI\Metrics\MetricRegistry;
use SAHamid\BI\Query\QueryLogger;
use SAHamid\BI\Query\QueryRequest;
use SAHamid\BI\Query\QueryResult;
use SAHamid\BI\Security\AuthorizationContext;

/**
 * Read-only evidence service for the invoice-value certification decision.
 *
 * This intentionally remains separate from the executable metric handler. It
 * computes live evidence for Finance/Sales review while the metric is still
 * awaiting validation; it never changes registry status or writes source data.
 */
class InvoiceReconciliationService
{
	private $db;
	private $registry;
	private $logger;
	private $tolerance = 0.05;

	public function __construct($db, MetricRegistry $registry = null, QueryLogger $logger = null)
	{
		$this->db = $db;
		$this->registry = $registry === null ? new MetricRegistry() : $registry;
		$this->logger = $logger === null ? new QueryLogger($db) : $logger;
	}

	public function reconcile(QueryRequest $request, AuthorizationContext $context)
	{
		$startedAt = microtime(true);
		try {
			$result = $this->reconcileInternal($request, $context);
			$this->logger->log($context, $request, 'success', (int) round((microtime(true) - $startedAt) * 1000), count($result->getRows()));
			return $result;
		} catch (BIException $exception) {
			$this->logger->log($context, $request, 'error', (int) round((microtime(true) - $startedAt) * 1000), 0, $exception->getErrorCode());
			throw $exception;
		}
	}

	private function reconcileInternal(QueryRequest $request, AuthorizationContext $context)
	{
		if (!$context->canUseSalesAnalytics()) {
			throw new BIException('forbidden', 'You are not authorized to use sales analytics.', 403);
		}
		if ($request->getMetricIds() !== array('sales.invoice_value')) {
			throw new BIException('unsupported_request', 'Invoice reconciliation is available only for the invoice-value metric.', 400);
		}
		if ($request->getDimensions()) {
			throw new BIException('unsupported_request', 'Breakdowns are not available in invoice reconciliation.', 400);
		}

		$metric = $this->registry->get('sales.invoice_value');
		$salespersonCode = $this->resolveSalespersonFilter($request, $context);
		$bindings = $this->buildBindings($request, $salespersonCode);
		$buckets = $this->loadTaxBuckets($bindings);
		$coverage = $this->loadDetailCoverage($bindings);

		$summary = array(
			'invoice_count' => 0,
			'detail_formula_value' => 0.0,
			'detail_option_rows' => 0,
			'ar_value' => 0.0,
			'ar_row_count' => 0,
			'missing_ar_count' => 0,
			'multiple_ar_count' => 0,
			'missing_detail_count' => 0,
			'expected_ar_value' => 0.0,
			'model_comparable' => true,
		);

		foreach ($buckets as &$bucket) {
			$summary['invoice_count'] += $bucket['invoice_count'];
			$summary['detail_formula_value'] += $bucket['detail_formula_value'];
			$summary['detail_option_rows'] += $bucket['detail_option_rows'];
			$summary['ar_value'] += $bucket['ar_value'];
			$summary['ar_row_count'] += $bucket['ar_row_count'];
			$summary['missing_ar_count'] += $bucket['missing_ar_count'];
			$summary['multiple_ar_count'] += $bucket['multiple_ar_count'];
			$summary['missing_detail_count'] += $bucket['missing_detail_count'];
			if ($bucket['expected_ar_value'] === null) {
				$summary['model_comparable'] = false;
			} else {
				$summary['expected_ar_value'] += $bucket['expected_ar_value'];
			}
		}
		unset($bucket);

		$summary['unmatched_detail_rows'] = $coverage['unmatched_detail_rows'];
		$summary['unmatched_detail_invoice_count'] = $coverage['unmatched_detail_invoice_count'];
		$summary['observed_variance'] = $summary['ar_value'] - $summary['detail_formula_value'];
		$summary['model_variance'] = $summary['model_comparable']
			? $summary['ar_value'] - $summary['expected_ar_value'] : null;
		$summary['tolerance'] = $this->tolerance;

		$checks = array(
			array(
				'id' => 'ar_linkage',
				'label' => 'Invoice to type-10 AR linkage',
				'status' => ($summary['missing_ar_count'] === 0 && $summary['multiple_ar_count'] === 0) ? 'pass' : 'exception',
				'detail' => $summary['missing_ar_count'] . ' invoices without a non-reversed AR row; ' . $summary['multiple_ar_count'] . ' with multiple rows.',
			),
			array(
				'id' => 'detail_option_coverage',
				'label' => 'Invoice detail to option coverage',
				'status' => $summary['unmatched_detail_rows'] === 0 ? 'pass' : 'exception',
				'detail' => $summary['unmatched_detail_rows'] . ' detail rows are not matched to an invoice option.',
			),
			array(
				'id' => 'tax_basis_model',
				'label' => 'Observed tax-basis model',
				'status' => ($summary['model_comparable'] && abs($summary['model_variance']) <= $this->tolerance) ? 'pass' : 'review',
				'detail' => $summary['model_comparable']
					? 'AR versus the observed 16%/18% category model leaves PKR ' . number_format(abs($summary['model_variance']), 2, '.', ',') . ' residual.'
					: 'One or more GST/services categories do not have an observed comparison rule.',
			),
			array(
				'id' => 'business_approval',
				'label' => 'Business definition approval',
				'status' => 'pending',
				'detail' => 'Finance/Sales must choose the governed net or gross definition and approve the date/tax policy.',
			),
		);

		$hasExceptions = $summary['missing_ar_count'] > 0
			|| $summary['multiple_ar_count'] > 0
			|| $summary['unmatched_detail_rows'] > 0
			|| !$summary['model_comparable']
			|| $summary['model_variance'] === null
			|| abs($summary['model_variance']) > $this->tolerance;
		$reconciliationStatus = $summary['invoice_count'] === 0
			? 'no_population'
			: ($hasExceptions ? 'exceptions_found' : 'formula_explained_pending_approval');

		$summary['reconciliation_status'] = $reconciliationStatus;
		$summary['approval_required'] = true;

		return new QueryResult($buckets, array(
			'metric' => $metric->toArray(),
			'date_range' => $request->getDateRange(),
			'authorization' => array(
				'database_name' => $context->getDatabaseName(),
				'salesperson_scope' => $salespersonCode,
			),
			'freshness' => array('mode' => 'live_source', 'as_of_utc' => gmdate('Y-m-d H:i:s')),
			'lineage' => array(
				'invoice', 'invoicedetails', 'invoiceoptions', 'debtortrans', 'salescase', 'salesman',
			),
			'validation_status' => $metric->getStatus(),
			'reconciliation' => array(
				'status' => $reconciliationStatus,
				'approval_required' => true,
				'summary' => $summary,
				'checks' => $checks,
				'tax_basis' => array(
					'exclusive_goods' => 'Observed AR relationship: raw detail value × 1.18 in this population; not a global approved policy.',
					'exclusive_services' => 'Observed AR relationship: raw detail value × 1.16 in this population; not a global approved policy.',
					'inclusive_or_blank' => 'Observed AR relationship: no additional gross-up in this population.',
				),
				'notes' => array(
					'This is read-only evidence. It does not publish or change the metric definition.',
					'Tax rules must be versioned by invoice date and posting path; legacy 17% logic exists elsewhere in the application.',
					'Invoice date is the population date. Debtor transaction and GL posting dates are not substituted here.',
				),
			),
		));
	}

	private function buildBindings(QueryRequest $request, $salespersonCode)
	{
		$range = $request->getDateRange();
		$where = 'WHERE i.returned = 0 AND i.inprogress = 0 AND i.invoicesdate BETWEEN ? AND ?';
		$types = 'ss';
		$values = array($range['start'], $range['end']);
		if ($salespersonCode !== null) {
			$where .= ' AND EXISTS (SELECT 1 FROM salescase scope_sc INNER JOIN salesman scope_sm ON scope_sm.salesmanname = scope_sc.salesman WHERE scope_sc.salescaseref = i.salescaseref AND scope_sm.salesmancode = ?)';
			$types .= 's';
			$values[] = $salespersonCode;
		}
		return array('where' => $where, 'types' => $types, 'values' => $values);
	}

	private function loadTaxBuckets(array $bindings)
	{
		$taxBucket = "CASE
			WHEN LOWER(TRIM(COALESCE(i.gst, ''))) = 'exclusive' AND COALESCE(i.services, 0) = 1 THEN 'exclusive_services'
			WHEN LOWER(TRIM(COALESCE(i.gst, ''))) = 'exclusive' THEN 'exclusive_goods'
			WHEN LOWER(TRIM(COALESCE(i.gst, ''))) = 'inclusive' THEN 'inclusive'
			WHEN LOWER(TRIM(COALESCE(i.gst, ''))) = '' THEN 'blank_or_no_gst'
			ELSE 'other'
		END";
		$sql = 'SELECT ' . $taxBucket . ' AS tax_bucket,
				COUNT(*) AS invoice_count,
				COALESCE(SUM(COALESCE(invoice_lines.detail_value, 0)), 0) AS detail_formula_value,
				COALESCE(SUM(COALESCE(invoice_lines.detail_option_rows, 0)), 0) AS detail_option_rows,
				COALESCE(SUM(COALESCE(ar.ar_value, 0)), 0) AS ar_value,
				COALESCE(SUM(COALESCE(ar.ar_rows, 0)), 0) AS ar_row_count,
				SUM(CASE WHEN ar.transno IS NULL THEN 1 ELSE 0 END) AS missing_ar_count,
				SUM(CASE WHEN ar.ar_rows > 1 THEN 1 ELSE 0 END) AS multiple_ar_count,
				SUM(CASE WHEN invoice_lines.invoiceno IS NULL THEN 1 ELSE 0 END) AS missing_detail_count
			FROM invoice i
			LEFT JOIN (
				SELECT d.invoiceno,
					SUM(d.unitprice * (1 - d.discountpercent) * d.quantity * o.quantity) AS detail_value,
					COUNT(*) AS detail_option_rows
				FROM invoiceoptions o
				INNER JOIN invoicedetails d ON d.invoiceno = o.invoiceno
					AND d.invoicelineno = o.invoicelineno
					AND d.invoiceoptionno = o.invoiceoptionno
				GROUP BY d.invoiceno
			) invoice_lines ON invoice_lines.invoiceno = i.invoiceno
			LEFT JOIN (
				SELECT transno, SUM(ovamount) AS ar_value, COUNT(*) AS ar_rows
				FROM debtortrans
				WHERE type = 10 AND reversed = 0
				GROUP BY transno
			) ar ON ar.transno = i.invoiceno
			' . $bindings['where'] . '
			GROUP BY ' . $taxBucket . '
			ORDER BY tax_bucket';

		$rows = $this->selectRows($sql, $bindings['types'], $bindings['values']);
		$definitions = array(
			'blank_or_no_gst' => array('label' => 'Blank GST / services 0', 'factor' => 1.0),
			'exclusive_goods' => array('label' => 'Exclusive / services 0', 'factor' => 1.18),
			'exclusive_services' => array('label' => 'Exclusive / services 1', 'factor' => 1.16),
			'inclusive' => array('label' => 'Inclusive', 'factor' => 1.0),
			'other' => array('label' => 'Other GST/services combination', 'factor' => null),
		);
		$normalized = array();
		foreach ($rows as $row) {
			$bucket = isset($row['tax_bucket']) ? $row['tax_bucket'] : 'other';
			$definition = isset($definitions[$bucket]) ? $definitions[$bucket] : $definitions['other'];
			$detailValue = (float) $row['detail_formula_value'];
			$arValue = (float) $row['ar_value'];
			$expected = $definition['factor'] === null ? null : $detailValue * $definition['factor'];
			$normalized[] = array(
				'tax_bucket' => $bucket,
				'label' => $definition['label'],
				'expected_factor' => $definition['factor'],
				'invoice_count' => (int) $row['invoice_count'],
				'detail_formula_value' => $detailValue,
				'detail_option_rows' => (int) $row['detail_option_rows'],
				'ar_value' => $arValue,
				'ar_row_count' => (int) $row['ar_row_count'],
				'missing_ar_count' => (int) $row['missing_ar_count'],
				'multiple_ar_count' => (int) $row['multiple_ar_count'],
				'missing_detail_count' => (int) $row['missing_detail_count'],
				'expected_ar_value' => $expected,
				'observed_variance' => $arValue - $detailValue,
				'model_variance' => $expected === null ? null : $arValue - $expected,
			);
		}
		return $normalized;
	}

	private function loadDetailCoverage(array $bindings)
	{
		$sql = 'SELECT COUNT(*) AS unmatched_detail_rows, COUNT(DISTINCT d.invoiceno) AS unmatched_detail_invoice_count
			FROM invoice i
			INNER JOIN invoicedetails d ON d.invoiceno = i.invoiceno
			LEFT JOIN invoiceoptions o ON d.invoiceno = o.invoiceno
				AND d.invoicelineno = o.invoicelineno
				AND d.invoiceoptionno = o.invoiceoptionno
			' . $bindings['where'] . '
			AND o.invoiceno IS NULL';
		$rows = $this->selectRows($sql, $bindings['types'], $bindings['values']);
		$row = isset($rows[0]) ? $rows[0] : array();
		return array(
			'unmatched_detail_rows' => isset($row['unmatched_detail_rows']) ? (int) $row['unmatched_detail_rows'] : 0,
			'unmatched_detail_invoice_count' => isset($row['unmatched_detail_invoice_count']) ? (int) $row['unmatched_detail_invoice_count'] : 0,
		);
	}

	private function selectRows($sql, $types, array $values)
	{
		$stmt = @mysqli_prepare($this->db, $sql);
		if (!$stmt) {
			throw new BIException('source_query_failed', 'The invoice reconciliation query could not be prepared.', 503);
		}
		if ($types !== '') {
			$bind = array($stmt, $types);
			foreach ($values as $index => &$value) {
				$bind[] =& $value;
			}
			call_user_func_array('mysqli_stmt_bind_param', $bind);
			unset($value);
		}
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'The invoice reconciliation query failed.', 503);
		}
		$result = mysqli_stmt_get_result($stmt);
		if (!$result) {
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'The invoice reconciliation result could not be read.', 503);
		}
		$rows = array();
		while ($row = mysqli_fetch_assoc($result)) {
			$rows[] = $row;
		}
		mysqli_free_result($result);
		mysqli_stmt_close($stmt);
		return $rows;
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
}
