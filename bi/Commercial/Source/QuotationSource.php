<?php

namespace SAHamid\BI\Commercial\Source;

use SAHamid\BI\Commercial\Valuation\TaxPolicy;
use SAHamid\BI\Exception\BIException;

/** One canonical fact per saved quotation document (legacy document-date proxy). */
class QuotationSource
{
	private $taxPolicy;

	public function __construct(TaxPolicy $taxPolicy = null)
	{
		$this->taxPolicy = $taxPolicy === null ? new TaxPolicy() : $taxPolicy;
	}

	public function fetch($db, $from, $through, $salespeople = null)
	{
		$where = 'so.orddate BETWEEN ? AND ?';
		$scopeNames = is_array($salespeople) ? array_values($salespeople) : array();
		$where = 'so.orddate BETWEEN ? AND ?';
		$ordinaryScope = '';
		$quickScope = '';
		$mainScope = '';
		if (is_array($salespeople)) {
			if (count($salespeople) === 0) return array();
			$marks = implode(',', array_fill(0, count($salespeople), '?'));
			$ordinaryScope = ' AND qsc.salesman IN (' . $marks . ')';
			$quickScope = ' AND qsc.salesman IN (' . $marks . ')';
			$mainScope = ' AND sc.salesman IN (' . $marks . ')';
		}

		$hasAdjustments = $this->tableExists($db, 'sales_performance_adjustments');
		$adjustmentJoin = $hasAdjustments
			? "LEFT JOIN (
				SELECT source_key, SUM(excluded_amount) AS excluded_amount
				FROM sales_performance_adjustments
				WHERE metric_key = 'quotation' AND status = 'approved' AND adjustment_type = 'value_exclusion'
				GROUP BY source_key
			) adj ON adj.source_key = CAST(so.orderno AS CHAR)"
			: '';
		$adjustmentSelect = $hasAdjustments ? 'COALESCE(adj.excluded_amount, 0)' : '0.00';

		$sql = "SELECT so.orderno, so.salescaseref, so.debtorno, so.branchcode,
			DATE_FORMAT(so.orddate, '%Y-%m-%d') AS business_date,
			DATE_FORMAT(so.quotedate, '%Y-%m-%d') AS quotedate,
			so.GSTadd AS gst_mode, so.services, so.revision, so.revision_for,
			so.withoutItems, so.quickQuotation, so.quotation, so.poplaced,
			sc.salesman AS credited_salesperson,
			(SELECT MIN(owner.salesmancode) FROM salesman owner WHERE owner.salesmanname=sc.salesman) AS credited_salesman_code,
			dm.currcode AS currency,
			$adjustmentSelect AS approved_exclusion,
			COALESCE(ordinary.detail_count, 0) AS ordinary_detail_count,
			ordinary.source_amount AS ordinary_amount,
			COALESCE(quick.option_count, 0) AS quick_option_count,
			quick.source_amount AS quick_amount
			FROM salesorders so
			INNER JOIN salescase sc ON sc.salescaseref = so.salescaseref
			LEFT JOIN debtorsmaster dm ON dm.debtorno = so.debtorno
			$adjustmentJoin
			LEFT JOIN (
				SELECT d.orderno, COUNT(*) AS detail_count,
					CAST(SUM(CAST(d.unitprice AS DECIMAL(24,6)) * (1 - CAST(d.discountpercent AS DECIMAL(12,8)))
						* CAST(d.quantity AS DECIMAL(24,6)) * CAST(o.quantity AS DECIMAL(24,6))) AS DECIMAL(24,2)) AS source_amount
				FROM salesorderdetails d
				INNER JOIN salesorders qso ON qso.orderno = d.orderno AND qso.quotation = 1
				INNER JOIN salescase qsc ON qsc.salescaseref = qso.salescaseref
				INNER JOIN salesorderoptions o ON o.orderno = d.orderno AND o.lineno = d.orderlineno AND o.optionno = 0
				WHERE d.lineoptionno = 0 AND qso.orddate BETWEEN ? AND ? $ordinaryScope
				GROUP BY d.orderno
			) ordinary ON ordinary.orderno = so.orderno
			LEFT JOIN (
				SELECT qo.orderno, COUNT(*) AS option_count,
					CAST(SUM(CAST(price AS DECIMAL(24,6)) * CAST(quantity AS DECIMAL(24,6))) AS DECIMAL(24,2)) AS source_amount
				FROM salesorderoptions qo
				INNER JOIN salesorders qso ON qso.orderno = qo.orderno AND qso.quotation = 1 AND qso.withoutItems = 1
				INNER JOIN salescase qsc ON qsc.salescaseref = qso.salescaseref
				WHERE qo.optionno = 0 AND qso.orddate BETWEEN ? AND ? $quickScope GROUP BY qo.orderno
			) quick ON quick.orderno = so.orderno
			WHERE $where AND so.quotation = 1 $mainScope
			ORDER BY so.orddate, so.orderno";

		$params = array_merge(array($from, $through), $scopeNames, array($from, $through), $scopeNames, array($from, $through), $scopeNames);
		$rows = $this->query($db, $sql, $params);
		$facts = array();
		foreach ($rows as $row) {
			$withoutItems = (int) $row['withoutItems'] === 1;
			$detailCount = (int) $row['ordinary_detail_count'];
			$optionCount = (int) $row['quick_option_count'];
			if ($withoutItems && $detailCount > 0) {
				$sourceAmount = null;
				$eligibility = 'structure_conflict';
			} elseif ($withoutItems) {
				$sourceAmount = $optionCount > 0 ? $row['quick_amount'] : null;
				$eligibility = $optionCount > 0 ? 'eligible' : 'missing_default_option_price';
			} else {
				$sourceAmount = $detailCount > 0 ? $row['ordinary_amount'] : null;
				$eligibility = $detailCount > 0 ? 'eligible' : 'missing_default_item_detail';
			}

			$tax = $this->taxPolicy->valueWithoutGst($sourceAmount, $row['gst_mode'], $row['services'], $row['quotedate'], $row['currency']);
			$revision = trim((string) $row['revision']) !== '' || trim((string) $row['revision_for']) !== '';
			$normalizedAmount = $hasAdjustments ? $tax['net_amount'] : null;
			$approvedExclusion = (string) $row['approved_exclusion'];
			$normalizationReferences = array();
			if ($normalizedAmount !== null && \SAHamid\BI\Commercial\DecimalAmount::compare($approvedExclusion, '0') > 0) {
				if (\SAHamid\BI\Commercial\DecimalAmount::compare($approvedExclusion, $normalizedAmount) > 0) {
					$normalizedAmount = null;
					$tax['status'] = 'normalization_conflict';
				} else {
					$normalizedAmount = \SAHamid\BI\Commercial\DecimalAmount::subtract($normalizedAmount, $approvedExclusion);
					$normalizationReferences[] = array('policy' => 'value_exclusion', 'amount' => $approvedExclusion);
				}
			}
			$facts[] = array(
				'fact_id' => 'quotation:' . (string) $row['orderno'],
				'metric_family' => $revision ? 'revised_quotations' : 'quotations',
				'source_type' => $withoutItems ? 'quick_quotation_option_zero' : 'quotation_item_detail_option_zero',
				'source_key' => (string) $row['orderno'],
				'source_version' => 'live_document_not_frozen',
				'salescaseref' => (string) $row['salescaseref'],
				'customer' => (string) $row['debtorno'],
				'branch' => (string) $row['branchcode'],
				'credited_salesperson' => (string) $row['credited_salesperson'],
				'credited_salesman_code' => isset($row['credited_salesman_code']) ? $row['credited_salesman_code'] : null,
				'credited_team' => null,
				'attribution_basis' => 'current_salescase_owner_and_current_team_mapping',
				'business_at' => (string) $row['business_date'],
				'recorded_at' => null,
				'date_basis' => 'legacy_document_date_proxy_orddate_not_confirmed_sent_at',
				'report_timezone' => 'Asia/Karachi',
				'raw_amount' => $sourceAmount === null ? null : (string) $sourceAmount,
				'reporting_raw_amount' => strtoupper((string) $row['currency']) === 'PKR' && $sourceAmount !== null ? (string) $sourceAmount : null,
				'net_amount' => $tax['net_amount'],
				'normalized_amount' => $normalizedAmount,
				'currency' => (string) $row['currency'],
				'revision_for' => trim((string) $row['revision_for']),
				'reporting_currency' => 'PKR',
				'tax_basis' => $tax['tax_basis'],
				'tax_rule_version' => TaxPolicy::VERSION,
				'raw_count_eligible' => 1,
				'normalized_count_eligible' => 1,
				'value_status' => $tax['status'],
				'coverage_status' => 'not_captured_historically',
				'eligibility_status' => $eligibility,
				'warnings' => array_filter(array(
				$sourceAmount === null ? $eligibility : null,
				!$hasAdjustments ? 'normalization_support_not_installed' : null,
					$tax['status'] === 'provisional_tax_rule_applied' ? 'Tax removal follows the current PDF rule and awaits business approval.' : null,
					'Legacy document date proxy; saved document does not prove customer delivery.'
				)),
				'normalization_references' => $normalizationReferences,
				'source_evidence' => array('orddate' => (string) $row['business_date'], 'quotedate' => (string) $row['quotedate'], 'GSTadd' => (string) $row['gst_mode'], 'withoutItems' => (int) $row['withoutItems']),
				'calculation_version' => 'commercial-fact-v1-draft',
				'is_revision' => $revision,
				'raw_document_value' => $sourceAmount === null ? null : (string) $sourceAmount,
				'net_document_value' => $tax['net_amount'],
				'normalized_document_value' => $normalizedAmount,
				'value_status_code' => $tax['status'],
				'eligibility_status_code' => $eligibility
			);
		}
		return $facts;
	}

	/** Freeze one document's source lines for an explicit user-recorded issue event. */
	public function snapshotDocument($db, $orderNo)
	{
		$document = $this->query($db, "SELECT DATE_FORMAT(orddate,'%Y-%m-%d') AS business_date FROM salesorders WHERE orderno=? AND quotation=1 LIMIT 1", array((string) $orderNo));
		if (count($document) === 0) return null;
		$date = $document[0]['business_date'];
		$facts = $this->fetch($db, $date, $date);
		$fact = null;
		foreach ($facts as $candidate) if ($candidate['source_key'] === (string) $orderNo) { $fact = $candidate; break; }
		if ($fact === null) return null;
		if ($fact['source_type'] === 'quick_quotation_option_zero') {
			$lines = $this->query($db, 'SELECT * FROM salesorderoptions WHERE orderno=? AND optionno=0 ORDER BY lineno', array((string) $orderNo));
		} else {
			$lines = $this->query($db,
				'SELECT d.*, o.quantity AS selected_option_quantity FROM salesorderdetails d INNER JOIN salesorderoptions o ON o.orderno=d.orderno AND o.lineno=d.orderlineno AND o.optionno=0 WHERE d.orderno=? AND d.lineoptionno=0 ORDER BY d.orderlineno',
				array((string) $orderNo)
			);
		}
		$lineSnapshot = json_encode($lines, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$sourceHash = hash('sha256', json_encode(array(
			'orderno' => (string) $orderNo,
			'raw_amount' => $fact['raw_amount'],
			'net_amount' => $fact['net_amount'],
			'currency' => $fact['currency'],
			'tax_basis' => $fact['tax_basis'],
			'tax_rule_version' => $fact['tax_rule_version'],
			'lines' => $lines
		), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		return array('fact' => $fact, 'line_snapshot' => $lineSnapshot, 'source_hash' => $sourceHash);
	}

	public function fetchIssuedSnapshots($db, $from, $through, $salespeople = null)
	{
		if (!$this->tableExists($db, 'sales_performance_quotation_issues')) return array();
		$where = '';
		$params = array($from . ' 00:00:00', $through . ' 23:59:59');
		if (is_array($salespeople)) {
			if (count($salespeople) === 0) return array();
			$where = ' AND sm.salesmanname IN (' . implode(',', array_fill(0, count($salespeople), '?')) . ')';
			$params = array_merge($params, $salespeople);
		}
		$rows = $this->query($db,
			"SELECT qi.id, qi.orderno, qi.issue_sequence, qi.source_hash, qi.salescaseref, qi.debtorno, qi.branchcode,
				qi.issue_kind, DATE_FORMAT(qi.issued_at,'%Y-%m-%d %H:%i:%s') AS issued_at,
				DATE_FORMAT(qi.recorded_at,'%Y-%m-%d %H:%i:%s') AS recorded_at, qi.actor_user_id, qi.entered_by_user_id, qi.credited_salesman_code,
				qi.event_team_id, qi.attribution_basis, qi.line_snapshot, qi.raw_amount, qi.net_amount, qi.currency, COALESCE(adj.excluded_amount,0) AS approved_exclusion,
				qi.tax_basis, qi.valuation_policy_version, sm.salesmanname AS credited_salesperson
			 FROM sales_performance_quotation_issues qi
			 LEFT JOIN salesman sm ON sm.salesmancode=qi.credited_salesman_code
			 LEFT JOIN (SELECT source_key, SUM(excluded_amount) AS excluded_amount FROM sales_performance_adjustments WHERE metric_key='quotation' AND status='approved' AND adjustment_type='value_exclusion' GROUP BY source_key) adj ON adj.source_key=CAST(qi.orderno AS CHAR)
			 WHERE qi.issue_kind IN ('issued','revised') AND qi.issued_at BETWEEN ? AND ? $where
			 ORDER BY qi.issued_at, qi.id",
			$params
		);
		$facts = array();
		foreach ($rows as $row) {
			$net = $row['net_amount'] === null ? null : \SAHamid\BI\Commercial\DecimalAmount::normalize((string) $row['net_amount']);
			$raw = $row['raw_amount'] === null ? null : \SAHamid\BI\Commercial\DecimalAmount::normalize((string) $row['raw_amount']);
			$reportingRaw = strtoupper((string) $row['currency']) === 'PKR' ? $raw : null;
			$normalized = $net;
			$normalizationReferences = array();
		if ($normalized !== null && \SAHamid\BI\Commercial\DecimalAmount::compare((string) $row['approved_exclusion'], '0') > 0) {
				$exclusion = \SAHamid\BI\Commercial\DecimalAmount::normalize((string) $row['approved_exclusion']);
				if (\SAHamid\BI\Commercial\DecimalAmount::compare($exclusion, $normalized) > 0) $normalized = null;
				else {
					$normalized = \SAHamid\BI\Commercial\DecimalAmount::subtract($normalized, $exclusion);
					$normalizationReferences[] = array('policy' => 'value_exclusion', 'amount' => $exclusion);
				}
			}
			$valueStatus = $net === null ? 'unknown_tax_treatment' : (strpos($row['tax_basis'], 'inclusive') !== false ? 'provisional_tax_rule_applied' : 'known_source_subtotal');
			if ($net !== null && $normalized === null) $valueStatus = 'normalization_conflict';
			$factId = 'quotation_issue:' . (string) $row['id'];
			$facts[] = array(
				'fact_id' => $factId,
				'metric_family' => $row['issue_kind'] === 'revised' ? 'revised_quotations' : 'quotations',
				'source_type' => 'frozen_quotation_issue',
				'source_key' => (string) $row['orderno'] . '#' . (string) $row['issue_sequence'],
				'source_version' => 'sha256:' . $row['source_hash'],
				'salescaseref' => (string) $row['salescaseref'],
				'customer' => (string) $row['debtorno'],
				'branch' => (string) $row['branchcode'],
				'credited_salesperson' => $row['credited_salesperson'] === null ? '' : (string) $row['credited_salesperson'],
				'credited_team' => $row['event_team_id'] === null ? null : (int) $row['event_team_id'],
				'actor_user_id' => $row['actor_user_id'],
				'entered_by_user_id' => $row['entered_by_user_id'],
				'credited_salesman_code' => $row['credited_salesman_code'],
				'attribution_basis' => (string) $row['attribution_basis'],
				'business_at' => (string) $row['issued_at'],
				'recorded_at' => (string) $row['recorded_at'],
				'date_basis' => 'explicit_issue_timestamp',
				'report_timezone' => 'Asia/Karachi',
				'raw_amount' => $raw,
				'reporting_raw_amount' => $reportingRaw,
				'net_amount' => $net,
				'normalized_amount' => $normalized,
				'currency' => (string) $row['currency'],
				'reporting_currency' => 'PKR',
				'tax_basis' => (string) $row['tax_basis'],
				'tax_rule_version' => (string) $row['valuation_policy_version'],
				'raw_count_eligible' => 1,
				'normalized_count_eligible' => 1,
				'value_status' => $valueStatus,
				'coverage_status' => 'captured_issue_snapshot',
				'eligibility_status' => $raw === null ? 'missing_source_amount' : 'eligible',
				'warnings' => $net !== null && strpos($row['tax_basis'], 'inclusive') !== false ? array('Net value uses a provisional GST rule.') : array(),
				'normalization_references' => $normalizationReferences,
				'source_evidence' => array('issue_id' => (string) $row['id'], 'issue_sequence' => (int) $row['issue_sequence'], 'source_hash' => (string) $row['source_hash'], 'line_snapshot' => json_decode($row['line_snapshot'], true)),
				'calculation_version' => (string) $row['valuation_policy_version'],
				'is_revision' => $row['issue_kind'] === 'revised',
				'raw_document_value' => $raw,
				'net_document_value' => $net,
				'normalized_document_value' => $normalized,
				'value_status_code' => $valueStatus,
				'eligibility_status_code' => $raw === null ? 'missing_source_amount' : 'eligible'
			);
		}
		return $facts;
	}

	public function issuedDocumentIds($db)
	{
		if (!$this->tableExists($db, 'sales_performance_quotation_issues')) return array();
		$rows = $this->query($db, "SELECT DISTINCT orderno FROM sales_performance_quotation_issues WHERE issue_kind IN ('issued','revised')", array());
		$ids = array();
		foreach ($rows as $row) $ids[(string) $row['orderno']] = true;
		return $ids;
	}

	private function tableExists($db, $table)
	{
		$stmt = mysqli_prepare($db, 'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1');
		if (!$stmt) throw new BIException('schema_not_ready', 'The reporting database schema could not be checked.', 503);
		mysqli_stmt_bind_param($stmt, 's', $table);
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			throw new BIException('schema_not_ready', 'The reporting database schema could not be checked.', 503);
		}
		$result = mysqli_stmt_get_result($stmt);
		$exists = $result && mysqli_num_rows($result) > 0;
		if ($result) mysqli_free_result($result);
		mysqli_stmt_close($stmt);
		return $exists;
	}

	private function query($db, $sql, array $params)
	{
		$stmt = mysqli_prepare($db, $sql);
		if (!$stmt) throw new BIException('source_query_failed', 'Quotation records could not be prepared for reporting.', 503);
		if (count($params) > 0) {
			$types = str_repeat('s', count($params));
			$bind = array($types);
			foreach ($params as $index => $value) $bind[] = &$params[$index];
			call_user_func_array(array($stmt, 'bind_param'), $bind);
		}
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'Quotation records could not be loaded for reporting.', 503);
		}
		$result = mysqli_stmt_get_result($stmt);
		if (!$result) {
			mysqli_stmt_close($stmt);
			throw new BIException('source_query_failed', 'Quotation reporting requires the database result driver.', 503);
		}
		$rows = array();
		while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
		mysqli_free_result($result);
		mysqli_stmt_close($stmt);
		return $rows;
	}
}
