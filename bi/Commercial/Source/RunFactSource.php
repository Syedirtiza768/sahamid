<?php

namespace SAHamid\BI\Commercial\Source;

use SAHamid\BI\Commercial\DecimalAmount;

/** Captures the native source records represented in a sales performance run. */
class RunFactSource
{
	const LIMIT = 25000;

	public function fetch($db, $from, $through, $salespeople = null)
	{
		$facts = array();
		$ownerScope = '';
		$ownerParams = array($from, $through);
		$eventScope = '';
		$eventParams = array($from . ' 00:00:00', $through . ' 23:59:59');
		$documentScope = '';
		$documentParams = array();
		$followupScope = '';
		$followupParams = array($from, $through);
		if (is_array($salespeople)) {
			if (!count($salespeople)) return array();
			$marks = implode(',', array_fill(0, count($salespeople), '?'));
			$ownerScope = ' AND COALESCE(sm.salesmanname,sc.salesman) IN (' . $marks . ')';
			$ownerParams = array_merge($ownerParams, $salespeople);
			$eventScope = ' AND COALESCE(sm.salesmanname,sc.salesman) IN (' . $marks . ')';
			$eventParams = array_merge($eventParams, $salespeople);
			$documentScope = ' AND sc.salesman IN (' . $marks . ')';
			$documentParams = $salespeople;
			$followupScope = ' AND f.salesman IN (' . $marks . ')';
			$followupParams = array_merge($followupParams, $salespeople);
		}

		$this->appendEnquiries($db, $from, $through, $salespeople, $facts);
		$this->appendReceipts($db, $from, $through, $ownerScope, $ownerParams, $facts);
		$this->appendOrderConfirmations($db, $from, $through, $documentScope, $documentParams, $facts);
		$this->appendDeliveryChallans($db, $from, $through, $documentScope, $documentParams, $facts);
		$this->appendFollowups($db, $followupScope, $followupParams, $facts);
		$this->appendEnquiryEvents($db, $eventScope, $eventParams, $facts);
		return $facts;
	}

	public function followupFacts($db, $from, $through, $salespeople = null)
	{
		$scope = '';
		$params = array($from, $through);
		if (is_array($salespeople)) {
			if (!count($salespeople)) return array();
			$scope = ' AND f.salesman IN (' . implode(',', array_fill(0, count($salespeople), '?')) . ')';
			$params = array_merge($params, $salespeople);
		}
		$facts = array();
		$this->appendFollowups($db, $scope, $params, $facts);
		return $facts;
	}

	private function appendEnquiries($db, $from, $through, $salespeople, array &$facts)
	{
		$eventScope = '';
		$eventParams = array($from . ' 00:00:00', $through . ' 23:59:59');
		$legacyScope = '';
		$legacyParams = array($from, $through);
		if (is_array($salespeople)) {
			if (!count($salespeople)) return;
			$marks = implode(',', array_fill(0, count($salespeople), '?'));
			$eventScope = ' AND COALESCE(esm.salesmanname,sc.salesman) IN (' . $marks . ')';
			$eventParams = array_merge($eventParams, $salespeople);
			$legacyScope = ' AND sc.salesman IN (' . $marks . ')';
			$legacyParams = array_merge($legacyParams, $salespeople);
		}
		$dateExpr = "CASE WHEN YEAR(sc.enquirydate)>1900 THEN DATE(sc.enquirydate) ELSE DATE(sc.commencementdate) END";
		$effectiveEvent = $this->effectiveEnquiryEventPredicate('e');
		$rows = $this->query($db,
			"SELECT CONCAT('event:',e.id) AS source_key,e.salescaseref,sc.debtorno AS customer,sc.branchcode AS branch,
				e.actor_user_id,e.entered_by_user_id,e.credited_salesman_code,e.event_team_id AS credited_team_id,e.attribution_basis,
				DATE_FORMAT(e.business_at,'%Y-%m-%d %H:%i:%s') AS business_at,DATE_FORMAT(e.recorded_at,'%Y-%m-%d %H:%i:%s') AS recorded_at,
				COALESCE(esm.salesmanname,sc.salesman) AS credited_salesperson,dm.currcode AS currency,sc.enquiryvalue AS source_amount,
				'captured_received_event' AS coverage_status,'enquiry_event' AS source_type,'received' AS event_kind,
				e.corrects_event_id,e.correction_reason,DATE_FORMAT(previous_e.business_at,'%Y-%m-%d %H:%i:%s') AS previous_business_at,
				previous_e.event_type AS previous_event_type,previous_e.response_code AS previous_response_code,previous_e.reason_code AS previous_reason_code,
				previous_e.actor_user_id AS previous_actor_user_id,previous_e.entered_by_user_id AS previous_entered_by_user_id,
				previous_e.credited_salesman_code AS previous_credited_salesman_code,previous_e.event_team_id AS previous_event_team_id,
				previous_e.attribution_basis AS previous_attribution_basis,previous_e.notes AS previous_notes,previous_e.evidence_reference AS previous_evidence_reference
			 FROM sales_performance_enquiry_events e INNER JOIN salescase sc ON sc.salescaseref=e.salescaseref
			 LEFT JOIN salesman esm ON esm.salesmancode=e.credited_salesman_code LEFT JOIN debtorsmaster dm ON dm.debtorno=sc.debtorno
			 LEFT JOIN sales_performance_enquiry_events previous_e ON previous_e.id=e.corrects_event_id
			 WHERE e.event_type='received' AND $effectiveEvent AND e.business_at BETWEEN ? AND ? $eventScope
			 UNION ALL
			 SELECT CONCAT('case:',sc.salescaseref) AS source_key,sc.salescaseref,sc.debtorno AS customer,sc.branchcode AS branch,
				NULL AS actor_user_id,NULL AS entered_by_user_id,(SELECT MIN(owner.salesmancode) FROM salesman owner WHERE owner.salesmanname=sc.salesman) AS credited_salesman_code,NULL AS credited_team_id,
				'legacy_current_owner_fallback' AS attribution_basis,CONCAT(DATE_FORMAT($dateExpr,'%Y-%m-%d'),' 00:00:00') AS business_at,
				NULL AS recorded_at,sc.salesman AS credited_salesperson,dm.currcode AS currency,sc.enquiryvalue AS source_amount,
				'legacy_enquiry_date_proxy' AS coverage_status,'legacy_enquiry_case' AS source_type,'legacy' AS event_kind,
				NULL AS corrects_event_id,NULL AS correction_reason,NULL AS previous_business_at,NULL AS previous_event_type,
				NULL AS previous_response_code,NULL AS previous_reason_code,NULL AS previous_actor_user_id,NULL AS previous_entered_by_user_id,
				NULL AS previous_credited_salesman_code,NULL AS previous_event_team_id,NULL AS previous_attribution_basis,NULL AS previous_notes,NULL AS previous_evidence_reference
			 FROM salescase sc LEFT JOIN debtorsmaster dm ON dm.debtorno=sc.debtorno
			 WHERE $dateExpr BETWEEN ? AND ? $legacyScope
			 AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events e WHERE e.salescaseref=sc.salescaseref AND e.event_type='received' AND $effectiveEvent)
			 LIMIT 25001",
			array_merge($eventParams, $legacyParams)
		);
		foreach ($rows as $row) {
			$sourceAmount = ($row['source_amount'] !== null && DecimalAmount::compare((string) $row['source_amount'], '0') > 0) ? (string) $row['source_amount'] : null;
			$currency = strtoupper((string) $row['currency']);
			$knownPkr = $sourceAmount !== null && $currency === 'PKR';
			$reportingRaw = $knownPkr ? DecimalAmount::normalize($sourceAmount) : null;
			$row['currency'] = $currency === '' ? null : $currency;
			$row['raw_amount'] = $sourceAmount;
			$row['reporting_raw_amount'] = $reportingRaw;
			$row['net_amount'] = $reportingRaw;
			$row['normalized_amount'] = $reportingRaw;
			$row['value_status'] = $knownPkr ? 'complete' : 'unavailable';
			$row['eligibility_status'] = 'eligible_count_value_unknown';
			$row['tax_basis'] = 'not_applicable_enquiry_value';
			$row['source_key'] = 'enquiry:' . $row['source_key'];
			$warnings = $knownPkr ? array() : array('Enquiry value is not a positive amount with a PKR currency classification.');
			$evidence = array('event_kind' => $row['event_kind']);
			if ($row['corrects_event_id'] !== null) {
				$evidence['corrects_event_id'] = (string) $row['corrects_event_id'];
				$evidence['correction_reason'] = $row['correction_reason'];
				$evidence['correction_snapshot'] = array('business_at' => $row['previous_business_at'], 'event_type' => $row['previous_event_type'],
					'response_code' => $row['previous_response_code'], 'reason_code' => $row['previous_reason_code'],
					'actor_user_id' => $row['previous_actor_user_id'], 'entered_by_user_id' => $row['previous_entered_by_user_id'],
					'credited_salesman_code' => $row['previous_credited_salesman_code'], 'event_team_id' => $row['previous_event_team_id'],
					'attribution_basis' => $row['previous_attribution_basis'], 'notes' => $row['previous_notes'], 'evidence_reference' => $row['previous_evidence_reference']);
			}
			$this->append($facts, $this->fact($row, 'enquiries', $row['source_type'], $row['source_key'], 'enquiry-source-v1',
				$row['event_kind'] === 'received' ? 'captured_received_business_at' : 'legacy_enquirydate_or_commencementdate', $evidence, $warnings));
		}
	}

	private function appendReceipts($db, $from, $through, $scope, array $scopeParams, array &$facts)
	{
		$params = array_merge(array($from, $through), array_slice($scopeParams, 2));
		$rows = $this->query($db,
			"SELECT CONCAT('receipt:',r.id) AS source_key,r.salescaseref,r.debtorno AS customer,r.branchcode AS branch,
				r.entered_by_user_id,r.credited_salesman_code,r.event_team_id AS credited_team_id,r.attribution_basis,
				DATE_FORMAT(r.received_at,'%Y-%m-%d %H:%i:%s') AS business_at,DATE_FORMAT(r.recorded_at,'%Y-%m-%d %H:%i:%s') AS recorded_at,
				COALESCE(sm.salesmanname,sc.salesman) AS credited_salesperson,r.stated_amount,r.net_amount AS source_net_amount,r.currency,
				r.customer_reference_original,r.customer_reference_normalized,r.tax_basis,r.evidence_reference,r.receipt_status,
				r.correction_of_id,r.correction_reason,DATE_FORMAT(previous_r.received_at,'%Y-%m-%d %H:%i:%s') AS previous_received_at,
				previous_r.customer_reference_original AS previous_customer_reference,previous_r.stated_amount AS previous_stated_amount,
				previous_r.net_amount AS previous_net_amount,previous_r.currency AS previous_currency,previous_r.tax_basis AS previous_tax_basis,
				previous_r.evidence_reference AS previous_evidence_reference,previous_r.entered_by_user_id AS previous_entered_by_user_id
			 FROM sales_performance_po_receipts r LEFT JOIN salesman sm ON sm.salesmancode=r.credited_salesman_code
			 LEFT JOIN salescase sc ON sc.salescaseref=r.salescaseref
			 LEFT JOIN sales_performance_po_receipts previous_r ON previous_r.id=r.correction_of_id
			 WHERE r.receipt_status='active' AND DATE(r.received_at) BETWEEN ? AND ? $scope LIMIT 25001",
			$params
		);
		foreach ($rows as $row) {
			$currency = strtoupper((string) $row['currency']);
			$raw = ($currency === 'PKR' && $row['stated_amount'] !== null) ? (string) $row['stated_amount'] : null;
			$reportingRaw = $raw === null ? null : DecimalAmount::normalize($raw);
			$rawAmount = $raw;
			$raw = $reportingRaw;
			$net = ($currency === 'PKR' && $row['source_net_amount'] !== null) ? DecimalAmount::normalize((string) $row['source_net_amount']) : null;
			$row['currency'] = $currency === '' ? null : $currency;
			$row['raw_amount'] = $rawAmount; $row['reporting_raw_amount'] = $reportingRaw;
			$row['net_amount'] = $net; $row['normalized_amount'] = $net;
			$row['value_status'] = $net === null ? 'unavailable' : 'complete';
			$row['coverage_status'] = 'captured_po_receipt';
			$row['eligibility_status'] = 'eligible_count_value_unknown';
			$warnings = $net === null ? array('PO receipt count is captured; a PKR net value is unavailable.') : array();
			$refs = $net === null ? array() : array(array('policy' => 'captured_po_receipt_net_amount', 'amount' => $net));
			$evidence = array('customer_reference' => $row['customer_reference_original'], 'normalized_reference' => $row['customer_reference_normalized'],
				'tax_basis' => $row['tax_basis'], 'evidence_reference' => $row['evidence_reference'], 'receipt_status' => $row['receipt_status']);
			if ($row['correction_of_id'] !== null) {
				$evidence['correction_of_id'] = (string) $row['correction_of_id'];
				$evidence['correction_reason'] = $row['correction_reason'];
				$evidence['correction_snapshot'] = array('received_at' => $row['previous_received_at'],
					'customer_reference' => $row['previous_customer_reference'], 'stated_amount' => $row['previous_stated_amount'],
					'net_amount' => $row['previous_net_amount'], 'currency' => $row['previous_currency'],
					'tax_basis' => $row['previous_tax_basis'], 'evidence_reference' => $row['previous_evidence_reference'],
					'entered_by_user_id' => $row['previous_entered_by_user_id']);
			}
			$this->append($facts, $this->fact($row, 'purchase_orders', 'captured_customer_po_receipt', $row['source_key'], 'po-receipt-v1', 'received_at', $evidence, $warnings, $refs));
		}
	}

	private function appendOrderConfirmations($db, $from, $through, $scope, array $scopeParams, array &$facts)
	{
		$innerScope = str_replace('sc.salesman', 'psc.salesman', $scope);
		$params = array_merge(array($from, $through), $scopeParams, array($from, $through), $scopeParams);
		$rows = $this->query($db,
			"SELECT CONCAT(oc.orderno,'|',oc.pono) AS source_key,oc.salescaseref,sc.debtorno AS customer,sc.branchcode AS branch,
				(SELECT MIN(owner.salesmancode) FROM salesman owner WHERE owner.salesmanname=sc.salesman) AS credited_salesman_code,sc.salesman AS credited_salesperson,DATE_FORMAT(oc.orddate,'%Y-%m-%d %H:%i:%s') AS business_at,
				pv.doc_value,COALESCE(adj.excluded_amount,0) AS excluded_amount
			 FROM ocs oc INNER JOIN salescase sc ON sc.salescaseref=oc.salescaseref
			 LEFT JOIN (SELECT d.orderno,d.pono,CAST(SUM(CAST(d.unitprice AS DECIMAL(24,6))*(1-CAST(d.discountpercent AS DECIMAL(12,8)))*CAST(d.quantity AS DECIMAL(24,6))*CAST(o.quantity AS DECIMAL(24,6))) AS DECIMAL(24,2)) AS doc_value
				FROM ocdetails d INNER JOIN ocoptions o ON o.orderno=d.orderno AND o.pono=d.pono AND o.lineno=d.orderlineno AND o.optionno=0
				INNER JOIN ocs pos ON pos.orderno=d.orderno AND pos.pono=d.pono INNER JOIN salescase psc ON psc.salescaseref=pos.salescaseref
				WHERE d.lineoptionno=0 AND DATE(pos.orddate) BETWEEN ? AND ? $innerScope GROUP BY d.orderno,d.pono) pv ON pv.orderno=oc.orderno AND pv.pono=oc.pono
			 LEFT JOIN (SELECT source_key,SUM(excluded_amount) AS excluded_amount FROM sales_performance_adjustments WHERE metric_key='purchase_order' AND status='approved' AND adjustment_type='value_exclusion' GROUP BY source_key) adj
				ON adj.source_key=CONCAT(oc.orderno,'|',oc.pono)
			 WHERE DATE(oc.orddate) BETWEEN ? AND ? $scope LIMIT 25001",
			$params
		);
		foreach ($rows as $row) {
			$this->appendErpDocument($facts, $row, 'order_confirmations', 'erp_order_confirmation_proxy', 'oc:', 'erp-oc-proxy-v1',
				'erp_order_confirmation_date', 'legacy_erp_order_confirmation_proxy');
		}
	}

	private function appendDeliveryChallans($db, $from, $through, $scope, array $scopeParams, array &$facts)
	{
		$innerScope = str_replace('sc.salesman', 'dsc.salesman', $scope);
		$params = array_merge(array($from, $through), $scopeParams, array($from, $through), $scopeParams);
		$rows = $this->query($db,
			"SELECT CAST(dc.orderno AS CHAR) AS source_key,dc.salescaseref,sc.debtorno AS customer,sc.branchcode AS branch,
				(SELECT MIN(owner.salesmancode) FROM salesman owner WHERE owner.salesmanname=sc.salesman) AS credited_salesman_code,sc.salesman AS credited_salesperson,DATE_FORMAT(dc.orddate,'%Y-%m-%d %H:%i:%s') AS business_at,
				dv.doc_value,COALESCE(adj.excluded_amount,0) AS excluded_amount
			 FROM dcs dc INNER JOIN salescase sc ON sc.salescaseref=dc.salescaseref
			 LEFT JOIN (SELECT d.orderno,CAST(SUM(CAST(d.unitprice AS DECIMAL(24,6))*(1-CAST(d.discountpercent AS DECIMAL(12,8)))*CAST(d.quantity AS DECIMAL(24,6))*CAST(o.quantity AS DECIMAL(24,6))) AS DECIMAL(24,2)) AS doc_value
				FROM dcdetails d INNER JOIN dcoptions o ON o.orderno=d.orderno AND o.lineno=d.orderlineno AND o.optionno=0 AND o.dispatchid=d.dispatchid
				INNER JOIN dcs dch ON dch.orderno=d.orderno INNER JOIN salescase dsc ON dsc.salescaseref=dch.salescaseref
				WHERE d.lineoptionno=0 AND DATE(dch.orddate) BETWEEN ? AND ? $innerScope GROUP BY d.orderno) dv ON dv.orderno=dc.orderno
			 LEFT JOIN (SELECT source_key,SUM(excluded_amount) AS excluded_amount FROM sales_performance_adjustments WHERE metric_key='delivery_challan' AND status='approved' AND adjustment_type='value_exclusion' GROUP BY source_key) adj
				ON adj.source_key=CAST(dc.orderno AS CHAR)
			 WHERE DATE(dc.orddate) BETWEEN ? AND ? $scope LIMIT 25001",
			$params
		);
		foreach ($rows as $row) {
			$this->appendErpDocument($facts, $row, 'delivery_challans', 'erp_delivery_challan_proxy', 'dc:', 'erp-dc-proxy-v1',
				'erp_delivery_challan_date', 'legacy_erp_delivery_challan_proxy');
		}
	}

	private function appendErpDocument(array &$facts, array $row, $family, $sourceType, $keyPrefix, $version, $dateBasis, $coverage)
	{
		$raw = $row['doc_value'] === null ? null : DecimalAmount::normalize((string) $row['doc_value']);
		$excluded = DecimalAmount::normalize((string) $row['excluded_amount']);
		$netOfExclusion = $raw === null ? null : DecimalAmount::subtract($raw, $excluded);
		$normalized = $netOfExclusion === null ? null : (DecimalAmount::compare($netOfExclusion, '0') < 0 ? '0.00' : $netOfExclusion);
		$references = (DecimalAmount::compare($excluded, '0') > 0) ? array(array('policy' => 'approved_source_value_exclusion', 'amount' => $excluded)) : array();
		$row['business_at'] = (string) $row['business_at']; $row['currency'] = 'PKR';
		$row['raw_amount'] = $raw; $row['net_amount'] = $raw; $row['normalized_amount'] = $normalized;
		$row['value_status'] = $raw === null ? 'unavailable' : 'provisional_erp_proxy';
		$row['coverage_status'] = $coverage; $row['eligibility_status'] = 'provisional_proxy';
		$row['tax_basis'] = 'unknown_erp_document_tax_treatment'; $row['attribution_basis'] = 'current_salescase_owner';
		$warnings = array('Legacy ERP value proxy; this is a separate commercial stage, not revenue.');
		if ($raw === null) $warnings[] = 'No qualifying option-zero detail value was available.';
		$key = $keyPrefix . $row['source_key'];
		$evidence = array('erp_document_key' => $row['source_key'], 'excluded_amount' => $excluded);
		$this->append($facts, $this->fact($row, $family, $sourceType, $key, $version, $dateBasis, $evidence, $warnings, $references));
	}

	private function appendFollowups($db, $scope, array $params, array &$facts)
	{
		$rows = $this->query($db,
			"SELECT f.id,CONCAT('followup:',f.id) AS source_key,NULLIF(f.salescaseref,'') AS salescaseref,
				f.salesman AS credited_salesperson,f.credited_salesman_code,f.entered_by_user_id,f.actor_user_id,f.event_team_id AS credited_team_id,
				f.attribution_basis,DATE_FORMAT(COALESCE(f.actual_activity_at,CONCAT(f.activity_date,' 00:00:00')),'%Y-%m-%d %H:%i:%s') AS business_at,
				DATE_FORMAT(f.created_at,'%Y-%m-%d %H:%i:%s') AS recorded_at,f.activity_type,f.outcome_code,f.quotation_orderno,f.corrected_from_id,
				DATE_FORMAT(f.next_action_date,'%Y-%m-%d') AS next_action_date,
				previous_f.salesman AS prior_salesman,previous_f.credited_salesman_code AS prior_salesman_code,previous_f.event_team_id AS prior_team_id,
				DATE_FORMAT(COALESCE(previous_f.actual_activity_at,CONCAT(previous_f.activity_date,' 00:00:00')),'%Y-%m-%d %H:%i:%s') AS prior_activity_at,
				previous_f.activity_type AS prior_activity_type,previous_f.outcome AS prior_outcome,previous_f.quotation_orderno AS prior_quotation_orderno,
				DATE_FORMAT(previous_f.next_action_date,'%Y-%m-%d') AS prior_next_action_date,
				DATE_FORMAT(previous_f.voided_at,'%Y-%m-%d %H:%i:%s') AS correction_recorded_at,previous_f.voided_by AS correction_entered_by,previous_f.void_reason AS correction_reason
			 FROM sales_performance_followups f
			 LEFT JOIN sales_performance_followups previous_f ON previous_f.id=f.corrected_from_id
			 WHERE DATE(COALESCE(f.actual_activity_at,CONCAT(f.activity_date,' 00:00:00'))) BETWEEN ? AND ? AND f.voided_at IS NULL $scope LIMIT 25001",
			$params
		);
		foreach ($rows as $row) {
			$row['raw_amount'] = null; $row['net_amount'] = null; $row['normalized_amount'] = null; $row['currency'] = null;
			$row['value_status'] = 'not_applicable'; $row['coverage_status'] = 'captured_followup';
			$row['eligibility_status'] = 'eligible'; $row['tax_basis'] = 'not_applicable_activity_count';
			$correctionSnapshot = null;
			if ($row['corrected_from_id'] !== null) {
				$correctionSnapshot = array('prior_activity_at' => $row['prior_activity_at'], 'prior_activity_type' => $row['prior_activity_type'],
					'prior_outcome' => $row['prior_outcome'], 'prior_salesman' => $row['prior_salesman'], 'prior_salesman_code' => $row['prior_salesman_code'],
					'prior_team_id' => $row['prior_team_id'], 'prior_quotation_orderno' => $row['prior_quotation_orderno'],
					'prior_next_action_date' => $row['prior_next_action_date'], 'correction_recorded_at' => $row['correction_recorded_at'],
					'correction_entered_by' => $row['correction_entered_by'], 'correction_reason' => $row['correction_reason']);
			}
			$evidence = array('activity_type' => $row['activity_type'], 'outcome_code' => $row['outcome_code'],
				'quotation_orderno' => $row['quotation_orderno'], 'next_action_date' => $row['next_action_date'],
				'corrected_from_id' => $row['corrected_from_id'], 'correction_snapshot' => $correctionSnapshot);
			$this->append($facts, $this->fact($row, 'followups', 'captured_followup', $row['source_key'], 'followup-event-v1',
				'actual_activity_at_or_activity_date_fallback', $evidence));
		}
	}

	private function appendEnquiryEvents($db, $scope, array $params, array &$facts)
	{
		$effectiveEvent = $this->effectiveEnquiryEventPredicate('e');
		$rows = $this->query($db,
			"SELECT e.id,CONCAT('event:',e.id) AS source_key,e.salescaseref,sc.debtorno AS customer,sc.branchcode AS branch,
				e.actor_user_id,e.entered_by_user_id,e.credited_salesman_code,e.event_team_id AS credited_team_id,e.attribution_basis,
				DATE_FORMAT(e.business_at,'%Y-%m-%d %H:%i:%s') AS business_at,DATE_FORMAT(e.recorded_at,'%Y-%m-%d %H:%i:%s') AS recorded_at,
				COALESCE(sm.salesmanname,sc.salesman) AS credited_salesperson,e.event_type,e.response_code,e.reason_code,
				e.corrects_event_id,e.correction_reason,DATE_FORMAT(previous_e.business_at,'%Y-%m-%d %H:%i:%s') AS previous_business_at,
				previous_e.event_type AS previous_event_type,previous_e.response_code AS previous_response_code,previous_e.reason_code AS previous_reason_code,
				previous_e.actor_user_id AS previous_actor_user_id,previous_e.entered_by_user_id AS previous_entered_by_user_id,
				previous_e.credited_salesman_code AS previous_credited_salesman_code,previous_e.event_team_id AS previous_event_team_id,
				previous_e.attribution_basis AS previous_attribution_basis,previous_e.notes AS previous_notes,previous_e.evidence_reference AS previous_evidence_reference
			 FROM sales_performance_enquiry_events e INNER JOIN salescase sc ON sc.salescaseref=e.salescaseref
			 LEFT JOIN salesman sm ON sm.salesmancode=e.credited_salesman_code
			 LEFT JOIN sales_performance_enquiry_events previous_e ON previous_e.id=e.corrects_event_id
			 WHERE e.event_type IN ('response','company_decline','customer_rejection') AND $effectiveEvent
			 AND e.business_at BETWEEN ? AND ? $scope LIMIT 25001",
			$params
		);
		$families = array('response' => 'enquiry_responses', 'company_decline' => 'company_regrets', 'customer_rejection' => 'customer_rejections');
		foreach ($rows as $row) {
			$row['raw_amount'] = null; $row['net_amount'] = null; $row['normalized_amount'] = null; $row['currency'] = null;
			$row['value_status'] = 'not_applicable'; $row['coverage_status'] = 'captured_enquiry_event';
			$row['eligibility_status'] = 'eligible'; $row['tax_basis'] = 'not_applicable_activity_count';
			$evidence = array('event_type' => $row['event_type'], 'response_code' => $row['response_code'], 'reason_code' => $row['reason_code']);
			if ($row['corrects_event_id'] !== null) {
				$evidence['corrects_event_id'] = (string) $row['corrects_event_id'];
				$evidence['correction_reason'] = $row['correction_reason'];
				$evidence['correction_snapshot'] = array('business_at' => $row['previous_business_at'], 'event_type' => $row['previous_event_type'],
					'response_code' => $row['previous_response_code'], 'reason_code' => $row['previous_reason_code'],
					'actor_user_id' => $row['previous_actor_user_id'], 'entered_by_user_id' => $row['previous_entered_by_user_id'],
					'credited_salesman_code' => $row['previous_credited_salesman_code'], 'event_team_id' => $row['previous_event_team_id'],
					'attribution_basis' => $row['previous_attribution_basis'], 'notes' => $row['previous_notes'], 'evidence_reference' => $row['previous_evidence_reference']);
			}
			$this->append($facts, $this->fact($row, $families[$row['event_type']], 'captured_enquiry_event',
				$row['source_key'], 'enquiry-event-v1', 'captured_event_business_at', $evidence));
		}
	}

	private function effectiveEnquiryEventPredicate($alias)
	{
		return $alias . ".voids_event_id IS NULL
			AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events event_void WHERE event_void.voids_event_id=" . $alias . ".id)
			AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events event_correction
				WHERE event_correction.corrects_event_id=" . $alias . ".id AND event_correction.voids_event_id IS NULL
				AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events correction_void WHERE correction_void.voids_event_id=event_correction.id))";
	}

	private function fact(array $row, $family, $sourceType, $sourceKey, $version, $dateBasis, array $evidence = array(), array $warnings = array(), array $references = array())
	{
		$team = array_key_exists('credited_team_id', $row) ? $row['credited_team_id'] : null;
		$salesperson = isset($row['credited_salesperson']) ? (string) $row['credited_salesperson'] : null;
		$currency = isset($row['currency']) && trim((string) $row['currency']) !== '' ? strtoupper((string) $row['currency']) : null;
		$evidence['_sales_performance_export'] = array('credited_salesperson' => $salesperson, 'credited_team' => $team,
			'is_revision' => false, 'reporting_raw_amount' => array_key_exists('reporting_raw_amount', $row)
				? $row['reporting_raw_amount'] : (($currency === 'PKR' && isset($row['raw_amount'])) ? $row['raw_amount'] : null));
		return array(
			'fact_id' => $sourceType . ':' . $sourceKey, 'metric_family' => $family, 'source_type' => $sourceType,
			'source_key' => $sourceKey, 'source_version' => $version,
			'salescaseref' => isset($row['salescaseref']) && $row['salescaseref'] !== '' ? (string) $row['salescaseref'] : null,
			'customer' => isset($row['customer']) && $row['customer'] !== '' ? (string) $row['customer'] : null,
			'branch' => isset($row['branch']) && $row['branch'] !== '' ? (string) $row['branch'] : null,
			'actor_user_id' => isset($row['actor_user_id']) && $row['actor_user_id'] !== '' ? (string) $row['actor_user_id'] : null,
			'entered_by_user_id' => isset($row['entered_by_user_id']) && $row['entered_by_user_id'] !== '' ? (string) $row['entered_by_user_id'] : null,
			'credited_salesman_code' => isset($row['credited_salesman_code']) && $row['credited_salesman_code'] !== '' ? (string) $row['credited_salesman_code'] : null,
			'credited_team_id' => ($team === null || $team === '') ? null : (int) $team,
			'attribution_basis' => isset($row['attribution_basis']) ? (string) $row['attribution_basis'] : 'legacy_current_owner_fallback',
			'attribution_version' => 'effective-membership-v1-current-fallback',
			'business_at' => isset($row['business_at']) ? (string) $row['business_at'] : null,
			'recorded_at' => isset($row['recorded_at']) && $row['recorded_at'] !== '' ? (string) $row['recorded_at'] : null,
			'date_basis' => $dateBasis, 'raw_amount' => array_key_exists('raw_amount', $row) ? $row['raw_amount'] : null,
			'net_amount' => array_key_exists('net_amount', $row) ? $row['net_amount'] : null,
			'normalized_amount' => array_key_exists('normalized_amount', $row) ? $row['normalized_amount'] : null,
			'currency' => $currency, 'reporting_currency' => 'PKR',
			'tax_basis' => isset($row['tax_basis']) ? $row['tax_basis'] : 'not_applicable_or_unknown',
			'tax_rule_version' => isset($row['tax_rule_version']) ? $row['tax_rule_version'] : null,
			'raw_count_eligible' => 1, 'normalized_count_eligible' => 1,
			'value_status' => isset($row['value_status']) ? $row['value_status'] : 'not_applicable',
			'coverage_status' => isset($row['coverage_status']) ? $row['coverage_status'] : 'captured_source_record',
			'eligibility_status' => isset($row['eligibility_status']) ? $row['eligibility_status'] : 'eligible',
			'warnings' => array_values($warnings), 'normalization_references' => array_values($references),
			'source_evidence' => $evidence, 'calculation_version' => 'commercial-fact-v1-draft',
			'credited_salesperson' => $salesperson, 'credited_team' => $team, 'is_revision' => false,
			'reporting_raw_amount' => array_key_exists('reporting_raw_amount', $row)
				? $row['reporting_raw_amount'] : (($currency === 'PKR' && isset($row['raw_amount'])) ? $row['raw_amount'] : null)
		);
	}

	private function append(array &$facts, array $fact)
	{
		$facts[] = $fact;
		if (count($facts) > self::LIMIT) {
			throw new \SAHamid\BI\Exception\BIException('export_limit', 'This period has more than 25,000 supporting facts. Narrow the date range before freezing it.', 413);
		}
	}

	private function query($db, $sql, array $params)
	{
		$stmt = mysqli_prepare($db, $sql);
		if (!$stmt) throw new \RuntimeException('Could not prepare frozen source fact query: ' . mysqli_error($db));
		if (count($params)) {
			$types = str_repeat('s', count($params));
			$bind = array($types);
			foreach ($params as $index => $value) $bind[] = &$params[$index];
			call_user_func_array(array($stmt, 'bind_param'), $bind);
		}
		if (!mysqli_stmt_execute($stmt)) {
			$error = mysqli_stmt_error($stmt);
			mysqli_stmt_close($stmt);
			throw new \RuntimeException('Could not read frozen source facts: ' . $error);
		}
		$result = mysqli_stmt_get_result($stmt);
		$rows = array();
		if ($result) {
			while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
			mysqli_free_result($result);
		}
		mysqli_stmt_close($stmt);
		return $rows;
	}
}
