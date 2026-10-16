<?php
// The report page uses the application's own permission slugs rather than
// webERP's legacy script-token registry. Keep that same authorization model
// for its JSON endpoint; session.inc still requires a signed-in company session,
// and every API action checks the report/capability/scope permissions below.
$AllowAnyone = true;
$PathPrefix = '../../';
include_once('../../includes/session.inc');

header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Karachi');
require_once '../../bi/bootstrap.php';

function spRespond($status, $payload) {
	http_response_code($status);
	echo json_encode($payload);
	exit;
}

function spRows($db, $sql, $params) {
	$stmt = mysqli_prepare($db, $sql);
	if (!$stmt) {
		throw new Exception('Could not prepare report query.');
	}
	if (count($params) > 0) {
		$types = str_repeat('s', count($params));
		$bind = array($types);
		foreach ($params as $index => $value) {
			$bind[] = &$params[$index];
		}
		call_user_func_array(array($stmt, 'bind_param'), $bind);
	}
	if (!mysqli_stmt_execute($stmt)) {
		mysqli_stmt_close($stmt);
		throw new Exception('Could not run report query.');
	}
	$result = mysqli_stmt_get_result($stmt);
	$rows = array();
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
			$rows[] = $row;
		}
		mysqli_free_result($result);
	}
	mysqli_stmt_close($stmt);
	return $rows;
}

function spExecute($db, $sql, $params) {
	$stmt = mysqli_prepare($db, $sql);
	if (!$stmt) {
		throw new Exception('Could not prepare update.');
	}
	if (count($params) > 0) {
		$types = str_repeat('s', count($params));
		$bind = array($types);
		foreach ($params as $index => $value) {
			$bind[] = &$params[$index];
		}
		call_user_func_array(array($stmt, 'bind_param'), $bind);
	}
	$ok = mysqli_stmt_execute($stmt);
	$affected = mysqli_stmt_affected_rows($stmt);
	mysqli_stmt_close($stmt);
	if (!$ok) {
		throw new Exception('Could not save the requested change.');
	}
	return $affected;
}

function spValidDate($value) {
	return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && date('Y-m-d', strtotime($value)) === $value;
}

function spDatePeriod($from, $to) {
	if (!spValidDate($from) || !spValidDate($to) || $from > $to) {
		throw new InvalidArgumentException('Choose valid start and end dates.');
	}
	$days = (int) floor((strtotime($to) - strtotime($from)) / 86400) + 1;
	if ($days > 400) {
		throw new InvalidArgumentException('Choose a date range of 400 days or less.');
	}
	return array('from' => $from, 'to' => $to);
}

function spMetricKeys() {
	return array('enquiries', 'quotations', 'revised_quotations', 'purchase_orders', 'order_confirmations', 'delivery_challans', 'followups', 'enquiry_responses', 'company_regrets', 'customer_rejections');
}

function spEmptyMetrics() {
	$out = array();
	foreach (spMetricKeys() as $metric) {
		$out[$metric] = array('count' => 0, 'raw_value' => '0.00', 'net_value' => '0.00', 'normalized_value' => '0.00', 'adjusted_documents' => 0, 'net_known_count' => 0, 'net_unknown_count' => 0, 'normalized_unknown_count' => 0, 'raw_unknown_count' => 0, 'value_status' => 'complete');
	}
	return $out;
}

function spAddMetric(&$metrics, $metric, $count, $raw, $normalized, $adjusted, $net = null, $netKnownCount = null, $netUnknownCount = 0, $normalizedUnknownCount = 0, $rawUnknownCount = 0, $valueStatus = 'complete') {
	if (!isset($metrics[$metric])) {
		$metrics[$metric] = array('count' => 0, 'raw_value' => '0.00', 'net_value' => '0.00', 'normalized_value' => '0.00', 'adjusted_documents' => 0, 'net_known_count' => 0, 'net_unknown_count' => 0, 'normalized_unknown_count' => 0, 'raw_unknown_count' => 0, 'value_status' => 'complete');
	}
	$metrics[$metric]['count'] += (int) $count;
	if (in_array($metric, array('followups', 'enquiry_responses', 'company_regrets', 'customer_rejections'), true)) {
		$metrics[$metric]['value_status'] = 'not_applicable';
		return;
	}
	if ($raw !== null) $metrics[$metric]['raw_value'] = \SAHamid\BI\Commercial\DecimalAmount::add($metrics[$metric]['raw_value'], (string) $raw);
	if ($net === null && $netKnownCount === null) $net = $raw;
	if ($net !== null) $metrics[$metric]['net_value'] = \SAHamid\BI\Commercial\DecimalAmount::add($metrics[$metric]['net_value'], (string) $net);
	if ($normalized !== null) $metrics[$metric]['normalized_value'] = \SAHamid\BI\Commercial\DecimalAmount::add($metrics[$metric]['normalized_value'], (string) $normalized);
	$metrics[$metric]['adjusted_documents'] += (int) $adjusted;
	$metrics[$metric]['net_known_count'] += $netKnownCount === null ? (int) $count : (int) $netKnownCount;
	$metrics[$metric]['net_unknown_count'] += (int) $netUnknownCount;
	$metrics[$metric]['normalized_unknown_count'] += (int) $normalizedUnknownCount;
	$metrics[$metric]['raw_unknown_count'] += (int) $rawUnknownCount;
	if ($metric === 'followups') {
		$metrics[$metric]['value_status'] = 'not_applicable';
	} elseif ($metrics[$metric]['net_unknown_count'] > 0 || $metrics[$metric]['normalized_unknown_count'] > 0 || $metrics[$metric]['raw_unknown_count'] > 0 || $valueStatus !== 'complete') {
		$metrics[$metric]['value_status'] = $metrics[$metric]['net_known_count'] > 0 ? 'partial' : 'unavailable';
	}
}

function spWeekStart($date) {
	$dayNumber = (int) date('N', strtotime($date));
	return date('Y-m-d', strtotime($date . ' -' . ($dayNumber - 1) . ' days'));
}

function spReportingDays($from, $to, array $calendarOverrides = array()) {
	$days = 0;
	$cursor = strtotime($from);
	$end = strtotime($to);
	while ($cursor <= $end) {
		if (spIsPlannedWorkday(date('Y-m-d', $cursor), $calendarOverrides)) $days++;
		$cursor = strtotime('+1 day', $cursor);
	}
	return $days;
}

function spCurrentCalendarVersion($db) {
	$rows = spRows($db, "SELECT setting_value FROM sales_performance_settings WHERE setting_key='report_calendar_version' LIMIT 1", array());
	return count($rows) ? (string) $rows[0]['setting_value'] : 'weekday-mon-sat-v1';
}

function spLoadCalendarOverrides($db, $from, $through, $calendarVersion) {
	$rows = spRows($db, "SELECT DATE_FORMAT(business_date,'%Y-%m-%d') AS business_date, planned_workday, override_reason FROM sales_performance_calendar_days WHERE calendar_version=? AND business_date BETWEEN ? AND ?", array($calendarVersion, $from, $through));
	$byDate = array();
	foreach ($rows as $row) $byDate[$row['business_date']] = array('planned_workday' => (bool) $row['planned_workday'], 'reason' => $row['override_reason']);
	return $byDate;
}

function spIsPlannedWorkday($date, array $calendarOverrides) {
	if (isset($calendarOverrides[$date])) return (bool) $calendarOverrides[$date]['planned_workday'];
	$weekday = (int) date('N', strtotime($date));
	return $weekday >= 1 && $weekday <= 6;
}

function spCoverageStatuses($db, $from, $through, $scopeKey, array $teamIds, $notCapturedBefore) {
	$statuses = array();
	$params = array($scopeKey, $from, $through);
	$teamClause = ' AND team_id IS NULL';
	$expectedTeams = 1;
	if (count($teamIds) > 0) {
		$teamClause = ' AND team_id IN (' . implode(',', array_fill(0, count($teamIds), '?')) . ')';
		$params = array_merge($params, array_map('strval', $teamIds));
		$expectedTeams = count(array_unique($teamIds));
	}
	$rows = spRows($db,
		"SELECT coverage_date, coverage_state, team_id FROM sales_performance_coverage
		 WHERE scope_key = ? AND metric_key = 'report_submission' AND coverage_date BETWEEN ? AND ? $teamClause",
		$params
	);
	$byDate = array();
	foreach ($rows as $row) {
		$date = $row['coverage_date'];
		if (!isset($byDate[$date])) $byDate[$date] = array('complete' => array(), 'partial' => false, 'unresolved' => false, 'not_submitted' => false);
		$state = $row['coverage_state'];
		if ($state === 'complete') $byDate[$date]['complete'][(string) $row['team_id']] = true;
		elseif ($state === 'partial') $byDate[$date]['partial'] = true;
		elseif ($state === 'unresolved') $byDate[$date]['unresolved'] = true;
		elseif ($state === 'not_submitted') $byDate[$date]['not_submitted'] = true;
	}
	$cursor = strtotime($from);
	$end = strtotime($through);
	while ($cursor <= $end) {
		$date = date('Y-m-d', $cursor);
		if (!isset($byDate[$date])) {
			$statuses[$date] = $date < $notCapturedBefore ? 'not_captured_historically' : 'not_submitted';
		} elseif (($scopeKey === 'erp_company' && isset($byDate[$date]['complete'][''])) || (count($byDate[$date]['complete']) >= $expectedTeams && !$byDate[$date]['partial'] && !$byDate[$date]['unresolved'])) {
			$statuses[$date] = 'complete';
		} elseif ($byDate[$date]['unresolved']) {
			$statuses[$date] = 'unresolved';
		} elseif ($byDate[$date]['partial'] || count($byDate[$date]['complete']) > 0) {
			$statuses[$date] = 'partial';
		} else {
			$statuses[$date] = 'not_submitted';
		}
		$cursor = strtotime('+1 day', $cursor);
	}
	return $statuses;
}

function spEffectiveEnquiryEventPredicate($alias) {
	return $alias . ".voids_event_id IS NULL
		AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events event_void WHERE event_void.voids_event_id=" . $alias . ".id)
		AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events event_correction
			WHERE event_correction.corrects_event_id=" . $alias . ".id AND event_correction.voids_event_id IS NULL
			AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events correction_void WHERE correction_void.voids_event_id=event_correction.id))";
}

function spEnquiryEventSequenceIsValid(array $events, $episodeNo) {
	if (count($events) === 0) return true;
	$startTypes = array('received', 'reopen');
	$terminalTypes = array('company_decline', 'customer_rejection', 'cancellation', 'duplicate', 'success');
	$requiredStart = (int) $episodeNo === 1 ? 'received' : 'reopen';
	if (!in_array($events[0]['event_type'], $startTypes, true) || $events[0]['event_type'] !== $requiredStart) return false;
	$seenResponse = false;
	$seenTerminal = false;
	for ($i = 1; $i < count($events); $i++) {
		$type = $events[$i]['event_type'];
		if ($seenTerminal) return false;
		if ($type === 'response' && !$seenResponse) { $seenResponse = true; continue; }
		if (in_array($type, $terminalTypes, true) && !$seenTerminal) { $seenTerminal = true; continue; }
		return false;
	}
	return true;
}

function spEnquiryStatus($db, $from, $cutoff, $salespeople, $namedPopulation = null) {
	$scope = '';
	if (is_array($salespeople)) {
		if (count($salespeople) === 0) return array('new_unanswered_count' => 0, 'backlog_count' => 0, 'daily_new_unanswered' => array(), 'coverage_status' => 'incomplete_event_capture');
		$scope = ' AND COALESCE(sm.salesmanname,sc.salesman) IN (' . implode(',', array_fill(0, count($salespeople), '?')) . ')';
	}
	$effectiveOpen = spEffectiveEnquiryEventPredicate('e');
	$effectiveResponse = spEffectiveEnquiryEventPredicate('response');
	$effectiveClosed = spEffectiveEnquiryEventPredicate('closed');
	$effectiveLater = spEffectiveEnquiryEventPredicate('later');
	$openEpisodes = "SELECT e.salescaseref, e.episode_no, DATE(e.business_at) AS opened_date, e.business_at AS opened_at, e.credited_salesman_code, e.event_team_id
		FROM sales_performance_enquiry_events e
		WHERE e.event_type IN ('received','reopen') AND $effectiveOpen AND e.business_at <= CONCAT(?, ' 23:59:59')";
	$where = "NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events response
		WHERE response.salescaseref=o.salescaseref AND response.episode_no=o.episode_no AND response.event_type='response'
		AND $effectiveResponse AND response.business_at >= o.opened_at AND response.business_at <= CONCAT(?, ' 23:59:59'))
		AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events closed
		WHERE closed.salescaseref=o.salescaseref AND closed.episode_no=o.episode_no
		AND closed.event_type IN ('company_decline','customer_rejection','cancellation','duplicate','success')
		AND $effectiveClosed AND closed.business_at >= o.opened_at AND closed.business_at <= CONCAT(?, ' 23:59:59'))
		AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events later
		WHERE later.salescaseref=o.salescaseref AND later.episode_no>o.episode_no
		AND later.event_type IN ('received','reopen') AND $effectiveLater AND later.business_at <= CONCAT(?, ' 23:59:59'))";
	$populationFilter = is_array($namedPopulation) ? spNamedPopulationSnapshotSql('o.event_team_id', $namedPopulation) : '';
	$dailySql = "SELECT o.opened_date AS report_date, COALESCE(sm.salesmanname,sc.salesman) AS salesman, o.event_team_id, 1 AS attribution_snapshot, COUNT(*) AS record_count,
		NULL AS raw_value, NULL AS normalized_value, 0 AS adjusted_count
		FROM ($openEpisodes) o INNER JOIN salescase sc ON sc.salescaseref=o.salescaseref LEFT JOIN salesman sm ON sm.salesmancode=o.credited_salesman_code
		WHERE o.opened_date BETWEEN ? AND ? AND $where $scope $populationFilter GROUP BY o.opened_date, COALESCE(sm.salesmanname,sc.salesman), o.event_team_id";
	$dailyParams = array_merge(array($cutoff, $from, $cutoff, $cutoff, $cutoff, $cutoff), $salespeople === null ? array() : $salespeople);
	$daily = spRows($db, $dailySql, $dailyParams);
	$backlogSql = "SELECT COUNT(*) AS record_count FROM ($openEpisodes) o INNER JOIN salescase sc ON sc.salescaseref=o.salescaseref LEFT JOIN salesman sm ON sm.salesmancode=o.credited_salesman_code WHERE $where $scope $populationFilter";
	$backlogParams = array_merge(array($cutoff, $cutoff, $cutoff, $cutoff), $salespeople === null ? array() : $salespeople);
	$backlogRows = spRows($db, $backlogSql, $backlogParams);
	$newCount = 0;
	foreach ($daily as $row) $newCount += (int) $row['record_count'];
	return array('new_unanswered_count' => $newCount, 'backlog_count' => count($backlogRows) ? (int) $backlogRows[0]['record_count'] : 0,
		'daily_new_unanswered' => $daily, 'coverage_status' => 'incomplete_event_capture');
}

function spMetricRows($db, $from, $to, $salespeople) {
	if (is_array($salespeople) && count($salespeople) === 0) {
		return array(
			'enquiries' => array(),
			'quotations' => array(),
			'purchase_orders' => array(),
			'order_confirmations' => array(),
			'delivery_challans' => array(),
			'followups' => array(),
			'enquiry_responses' => array(), 'company_regrets' => array(), 'customer_rejections' => array()
		);
	}
	$personWhere = '';
	$personParams = array();
	if (is_array($salespeople)) {
		$personWhere = ' AND sc.salesman IN (' . implode(',', array_fill(0, count($salespeople), '?')) . ')';
		$personParams = $salespeople;
	}
	$quoteSubPersonWhere = str_replace('sc.salesman', 'qsc.salesman', $personWhere);
	$poSubPersonWhere = str_replace('sc.salesman', 'psc.salesman', $personWhere);
	$dcSubPersonWhere = str_replace('sc.salesman', 'dsc.salesman', $personWhere);

	$enquiryDate = "CASE WHEN YEAR(sc.enquirydate) > 1900 THEN DATE(sc.enquirydate) ELSE DATE(sc.commencementdate) END";
	$enquiryEventPersonWhere = '';
	$enquiryEventParams = array($from, $to);
	$effectiveEnquiryEvent = spEffectiveEnquiryEventPredicate('e');
	if (is_array($salespeople)) {
		$enquiryEventPersonWhere = ' AND COALESCE(esm.salesmanname,sc.salesman) IN (' . implode(',', array_fill(0, count($salespeople), '?')) . ')';
		$enquiryEventParams = array_merge($enquiryEventParams, $salespeople);
	}
	$enquirySql = "SELECT DATE_FORMAT(x.report_date, '%Y-%m-%d') AS report_date, x.salesman, x.event_team_id, x.attribution_snapshot, COUNT(*) AS record_count,
			CAST(COALESCE(SUM(CASE WHEN UPPER(x.enquiry_currency) = 'PKR' AND x.enquiry_value > 0 THEN CAST(x.enquiry_value AS DECIMAL(24,2)) ELSE 0 END), 0) AS DECIMAL(24,2)) AS raw_value,
			CAST(COALESCE(SUM(CASE WHEN UPPER(x.enquiry_currency) = 'PKR' AND x.enquiry_value > 0 THEN CAST(x.enquiry_value AS DECIMAL(24,2)) ELSE 0 END), 0) AS DECIMAL(24,2)) AS normalized_value,
			SUM(CASE WHEN UPPER(x.enquiry_currency) = 'PKR' AND x.enquiry_value > 0 THEN 1 ELSE 0 END) AS net_known_count,
			SUM(CASE WHEN UPPER(x.enquiry_currency) <> 'PKR' OR x.enquiry_currency IS NULL OR x.enquiry_value IS NULL OR x.enquiry_value <= 0 THEN 1 ELSE 0 END) AS net_unknown_count,
			SUM(CASE WHEN UPPER(x.enquiry_currency) <> 'PKR' OR x.enquiry_currency IS NULL OR x.enquiry_value IS NULL OR x.enquiry_value <= 0 THEN 1 ELSE 0 END) AS normalized_unknown_count,
			SUM(CASE WHEN UPPER(x.enquiry_currency) <> 'PKR' OR x.enquiry_currency IS NULL OR x.enquiry_value IS NULL OR x.enquiry_value <= 0 THEN 1 ELSE 0 END) AS raw_unknown_count,
			IF(SUM(CASE WHEN UPPER(x.enquiry_currency) = 'PKR' AND x.enquiry_value > 0 THEN 1 ELSE 0 END) = 0, 'unavailable', IF(SUM(CASE WHEN UPPER(x.enquiry_currency) <> 'PKR' OR x.enquiry_currency IS NULL OR x.enquiry_value IS NULL OR x.enquiry_value <= 0 THEN 1 ELSE 0 END) > 0, 'partial', 'complete')) AS value_status,
			0 AS adjusted_count
		FROM (
			SELECT DATE(e.business_at) AS report_date, COALESCE(esm.salesmanname,sc.salesman) AS salesman, sc.enquiryvalue AS enquiry_value, dm.currcode AS enquiry_currency, e.event_team_id, 1 AS attribution_snapshot
			FROM sales_performance_enquiry_events e INNER JOIN salescase sc ON sc.salescaseref=e.salescaseref
			LEFT JOIN debtorsmaster dm ON dm.debtorno=sc.debtorno
			LEFT JOIN salesman esm ON esm.salesmancode=e.credited_salesman_code
			WHERE e.event_type='received' AND $effectiveEnquiryEvent AND DATE(e.business_at) BETWEEN ? AND ? $enquiryEventPersonWhere
			UNION ALL
			SELECT $enquiryDate AS report_date, sc.salesman, sc.enquiryvalue AS enquiry_value, dm.currcode AS enquiry_currency, NULL AS event_team_id, 0 AS attribution_snapshot
			FROM salescase sc LEFT JOIN debtorsmaster dm ON dm.debtorno=sc.debtorno WHERE $enquiryDate BETWEEN ? AND ? $personWhere
			AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events e WHERE e.salescaseref=sc.salescaseref AND e.event_type='received' AND $effectiveEnquiryEvent)
		) x GROUP BY x.report_date, x.salesman, x.event_team_id, x.attribution_snapshot";
	$enquiries = spRows($db, $enquirySql, array_merge($enquiryEventParams, array($from, $to), $personParams));

	$quoteRequest = \SAHamid\BI\Commercial\ReportingRequest::fromArray(array('from' => $from, 'through' => $to), $salespeople);
	$commercialReporting = new \SAHamid\BI\Commercial\ReportingService();
	$quoteFacts = $commercialReporting->quotationFacts($db, $quoteRequest);
	$quotations = $commercialReporting->aggregateQuotationFacts($quoteFacts);

	$ocSql = "SELECT DATE_FORMAT(oc.orddate, '%Y-%m-%d') AS report_date, sc.salesman,
			COUNT(DISTINCT CONCAT(oc.orderno, '|', oc.pono)) AS record_count,
			CAST(COALESCE(SUM(CASE WHEN pv.doc_value IS NULL THEN 0 ELSE pv.doc_value END), 0) AS DECIMAL(24,2)) AS raw_value,
			CAST(COALESCE(SUM(CASE WHEN pv.doc_value IS NULL THEN 0 ELSE GREATEST(pv.doc_value - COALESCE(adj.excluded_amount, 0), 0) END), 0) AS DECIMAL(24,2)) AS normalized_value,
			SUM(CASE WHEN pv.doc_value IS NULL THEN 1 ELSE 0 END) AS raw_unknown_count,
			SUM(CASE WHEN pv.doc_value IS NULL THEN 1 ELSE 0 END) AS net_unknown_count,
			SUM(CASE WHEN pv.doc_value IS NULL THEN 1 ELSE 0 END) AS normalized_unknown_count,
			SUM(CASE WHEN pv.doc_value IS NULL THEN 0 ELSE 1 END) AS net_known_count,
			IF(SUM(CASE WHEN pv.doc_value IS NULL THEN 1 ELSE 0 END)>0, IF(SUM(CASE WHEN pv.doc_value IS NULL THEN 0 ELSE 1 END)>0,'partial','unavailable'),'complete') AS value_status,
			COALESCE(SUM(CASE WHEN COALESCE(adj.adjustment_count, 0) = 0 THEN 0 ELSE 1 END), 0) AS adjusted_count
		FROM ocs oc
		INNER JOIN salescase sc ON sc.salescaseref = oc.salescaseref
		LEFT JOIN (
			SELECT d.orderno, d.pono, CAST(SUM(CAST(d.unitprice AS DECIMAL(24,6)) * (1 - CAST(d.discountpercent AS DECIMAL(12,8))) * CAST(d.quantity AS DECIMAL(24,6)) * CAST(o.quantity AS DECIMAL(24,6))) AS DECIMAL(24,2)) AS doc_value
			FROM ocdetails d
			INNER JOIN ocoptions o ON o.orderno = d.orderno AND o.pono = d.pono AND o.lineno = d.orderlineno AND o.optionno = 0
			INNER JOIN ocs pos ON pos.orderno = d.orderno AND pos.pono = d.pono
			INNER JOIN salescase psc ON psc.salescaseref = pos.salescaseref
			WHERE d.lineoptionno = 0 AND DATE(pos.orddate) BETWEEN ? AND ? $poSubPersonWhere
			GROUP BY d.orderno, d.pono
		) pv ON pv.orderno = oc.orderno AND pv.pono = oc.pono
		LEFT JOIN (
			SELECT metric_key, source_key, SUM(excluded_amount) AS excluded_amount, IF(SUM(excluded_amount) > 0, COUNT(*), 0) AS adjustment_count
			FROM sales_performance_adjustments WHERE status = 'approved' AND adjustment_type='value_exclusion' GROUP BY metric_key, source_key
		) adj ON adj.metric_key = 'purchase_order' AND adj.source_key = CONCAT(oc.orderno, '|', oc.pono)
		WHERE DATE(oc.orddate) BETWEEN ? AND ? $personWhere
		GROUP BY report_date, sc.salesman";
	$orderConfirmations = spRows($db, $ocSql, array_merge(array($from, $to), $personParams, array($from, $to), $personParams));
	$receiptPersonWhere = '';
	$receiptParams = array($from, $to);
	if (is_array($salespeople)) {
		$receiptPersonWhere = ' AND COALESCE(sm.salesmanname, sc.salesman) IN (' . implode(',', array_fill(0, count($salespeople), '?')) . ')';
		$receiptParams = array_merge($receiptParams, $salespeople);
	}
	$receiptSql = "SELECT DATE_FORMAT(r.received_at, '%Y-%m-%d') AS report_date, COALESCE(sm.salesmanname, sc.salesman) AS salesman,
			r.event_team_id, 1 AS attribution_snapshot,
			COUNT(DISTINCT r.id) AS record_count,
			CAST(SUM(CASE WHEN r.currency='PKR' AND r.stated_amount IS NOT NULL THEN r.stated_amount ELSE 0 END) AS DECIMAL(24,2)) AS raw_value,
			CAST(SUM(CASE WHEN r.currency='PKR' AND r.net_amount IS NOT NULL THEN r.net_amount ELSE 0 END) AS DECIMAL(24,2)) AS net_value,
			CAST(SUM(CASE WHEN r.currency='PKR' AND r.net_amount IS NOT NULL THEN r.net_amount ELSE 0 END) AS DECIMAL(24,2)) AS normalized_value,
			SUM(CASE WHEN r.currency='PKR' AND r.stated_amount IS NOT NULL THEN 1 ELSE 0 END) AS raw_known_count,
			SUM(CASE WHEN r.currency<>'PKR' OR r.currency IS NULL OR r.stated_amount IS NULL THEN 1 ELSE 0 END) AS raw_unknown_count,
			SUM(CASE WHEN r.currency='PKR' AND r.net_amount IS NOT NULL THEN 1 ELSE 0 END) AS net_known_count,
			SUM(CASE WHEN r.currency<>'PKR' OR r.currency IS NULL OR r.net_amount IS NULL THEN 1 ELSE 0 END) AS net_unknown_count,
			SUM(CASE WHEN r.currency<>'PKR' OR r.currency IS NULL OR r.net_amount IS NULL THEN 1 ELSE 0 END) AS normalized_unknown_count,
			IF(SUM(CASE WHEN r.currency='PKR' AND r.net_amount IS NOT NULL THEN 1 ELSE 0 END)=0, 'unavailable', IF(SUM(CASE WHEN r.currency<>'PKR' OR r.currency IS NULL OR r.net_amount IS NULL THEN 1 ELSE 0 END)>0, 'partial', 'complete')) AS value_status,
			0 AS adjusted_count
		FROM sales_performance_po_receipts r
		LEFT JOIN salesman sm ON sm.salesmancode=r.credited_salesman_code
		LEFT JOIN salescase sc ON sc.salescaseref=r.salescaseref
		WHERE r.receipt_status='active' AND DATE(r.received_at) BETWEEN ? AND ? $receiptPersonWhere
		GROUP BY report_date, COALESCE(sm.salesmanname, sc.salesman), r.event_team_id";
	$purchaseOrders = spRows($db, $receiptSql, $receiptParams);

	$dcSql = "SELECT DATE_FORMAT(dc.orddate, '%Y-%m-%d') AS report_date, sc.salesman,
			COUNT(DISTINCT dc.orderno) AS record_count,
			CAST(COALESCE(SUM(CASE WHEN dv.doc_value IS NULL THEN 0 ELSE dv.doc_value END), 0) AS DECIMAL(24,2)) AS raw_value,
			CAST(COALESCE(SUM(CASE WHEN dv.doc_value IS NULL THEN 0 ELSE GREATEST(dv.doc_value - COALESCE(adj.excluded_amount, 0), 0) END), 0) AS DECIMAL(24,2)) AS normalized_value,
			SUM(CASE WHEN dv.doc_value IS NULL THEN 1 ELSE 0 END) AS raw_unknown_count,
			SUM(CASE WHEN dv.doc_value IS NULL THEN 1 ELSE 0 END) AS net_unknown_count,
			SUM(CASE WHEN dv.doc_value IS NULL THEN 1 ELSE 0 END) AS normalized_unknown_count,
			SUM(CASE WHEN dv.doc_value IS NULL THEN 0 ELSE 1 END) AS net_known_count,
			IF(SUM(CASE WHEN dv.doc_value IS NULL THEN 1 ELSE 0 END)>0, IF(SUM(CASE WHEN dv.doc_value IS NULL THEN 0 ELSE 1 END)>0,'partial','unavailable'),'complete') AS value_status,
			COALESCE(SUM(CASE WHEN COALESCE(adj.adjustment_count, 0) = 0 THEN 0 ELSE 1 END), 0) AS adjusted_count
		FROM dcs dc
		INNER JOIN salescase sc ON sc.salescaseref = dc.salescaseref
		LEFT JOIN (
			SELECT d.orderno, CAST(SUM(CAST(d.unitprice AS DECIMAL(24,6)) * (1 - CAST(d.discountpercent AS DECIMAL(12,8))) * CAST(d.quantity AS DECIMAL(24,6)) * CAST(o.quantity AS DECIMAL(24,6))) AS DECIMAL(24,2)) AS doc_value
			FROM dcdetails d
			INNER JOIN dcoptions o ON o.orderno = d.orderno AND o.lineno = d.orderlineno
				AND o.optionno = 0 AND o.dispatchid = d.dispatchid
			INNER JOIN dcs dch ON dch.orderno = d.orderno
			INNER JOIN salescase dsc ON dsc.salescaseref = dch.salescaseref
			WHERE d.lineoptionno = 0 AND DATE(dch.orddate) BETWEEN ? AND ? $dcSubPersonWhere
			GROUP BY d.orderno
		) dv ON dv.orderno = dc.orderno
		LEFT JOIN (
			SELECT metric_key, source_key, SUM(excluded_amount) AS excluded_amount, IF(SUM(excluded_amount) > 0, COUNT(*), 0) AS adjustment_count
			FROM sales_performance_adjustments WHERE status = 'approved' AND adjustment_type='value_exclusion' GROUP BY metric_key, source_key
		) adj ON adj.metric_key = 'delivery_challan' AND adj.source_key = CAST(dc.orderno AS CHAR)
		WHERE DATE(dc.orddate) BETWEEN ? AND ? $personWhere
		GROUP BY report_date, sc.salesman";
	$deliveryChallans = spRows($db, $dcSql, array_merge(array($from, $to), $personParams, array($from, $to), $personParams));

	$followupSql = "SELECT DATE_FORMAT(COALESCE(f.actual_activity_at, CONCAT(f.activity_date, ' 00:00:00')), '%Y-%m-%d') AS report_date, f.salesman,
			f.event_team_id, 1 AS attribution_snapshot,
			COUNT(*) AS record_count, 0 AS raw_value, 0 AS normalized_value, 0 AS adjusted_count
		FROM sales_performance_followups f
		WHERE DATE(COALESCE(f.actual_activity_at, CONCAT(f.activity_date, ' 00:00:00'))) BETWEEN ? AND ? AND f.voided_at IS NULL";
	$followupParams = array($from, $to);
	if (is_array($salespeople)) {
		$followupSql .= ' AND f.salesman IN (' . implode(',', array_fill(0, count($salespeople), '?')) . ')';
		$followupParams = array_merge($followupParams, $salespeople);
	}
	$followupSql .= ' GROUP BY DATE(COALESCE(f.actual_activity_at, CONCAT(f.activity_date, " 00:00:00"))), f.salesman, f.event_team_id';
	$followups = spRows($db, $followupSql, $followupParams);
	$eventPersonWhere = '';
	$eventParams = array($from, $to);
	if (is_array($salespeople)) {
		$eventPersonWhere = ' AND COALESCE(sm.salesmanname,sc.salesman) IN (' . implode(',', array_fill(0, count($salespeople), '?')) . ')';
		$eventParams = array_merge($eventParams, $salespeople);
	}
	$eventSql = "SELECT DATE_FORMAT(e.business_at, '%Y-%m-%d') AS report_date, COALESCE(sm.salesmanname,sc.salesman) AS salesman, e.event_team_id, 1 AS attribution_snapshot, e.event_type, COUNT(*) AS record_count,
			NULL AS raw_value, NULL AS normalized_value, 0 AS adjusted_count
		FROM sales_performance_enquiry_events e INNER JOIN salescase sc ON sc.salescaseref=e.salescaseref
		LEFT JOIN salesman sm ON sm.salesmancode=e.credited_salesman_code
		WHERE e.business_at >= CONCAT(?, ' 00:00:00') AND e.business_at < DATE_ADD(?, INTERVAL 1 DAY) AND " . spEffectiveEnquiryEventPredicate('e') . " $eventPersonWhere
		GROUP BY DATE(e.business_at), COALESCE(sm.salesmanname,sc.salesman), e.event_team_id, e.event_type";
	$eventRows = spRows($db, $eventSql, $eventParams);
	$enquiryResponses = $companyRegrets = $customerRejections = array();
	foreach ($eventRows as $eventRow) {
		if ($eventRow['event_type'] === 'response') $enquiryResponses[] = $eventRow;
		elseif ($eventRow['event_type'] === 'company_decline') $companyRegrets[] = $eventRow;
		elseif ($eventRow['event_type'] === 'customer_rejection') $customerRejections[] = $eventRow;
	}

	return array(
		'enquiries' => $enquiries,
		'quotations' => $quotations,
		'purchase_orders' => $purchaseOrders,
		'order_confirmations' => $orderConfirmations,
		'delivery_challans' => $deliveryChallans,
		'followups' => $followups,
		'enquiry_responses' => $enquiryResponses,
		'company_regrets' => $companyRegrets,
		'customer_rejections' => $customerRejections
	);
}

function spAddRowToBuckets(&$allDaily, &$allWeekly, &$teamDaily, &$teamWeekly, &$teamWeekdays, &$people, $row, $metric, $teamKey) {
	$date = $row['report_date'];
	$week = spWeekStart($date);
	$weekday = date('N', strtotime($date));
	$count = isset($row['record_count']) ? (int) $row['record_count'] : 0;
	$raw = array_key_exists('raw_value', $row) ? (string) $row['raw_value'] : null;
	$net = array_key_exists('net_value', $row) ? $row['net_value'] : $raw;
	$normalized = array_key_exists('normalized_value', $row) ? $row['normalized_value'] : $net;
	$adjusted = isset($row['adjusted_count']) ? (int) $row['adjusted_count'] : 0;
	$netKnown = isset($row['net_known_count']) ? (int) $row['net_known_count'] : $count;
	$netUnknown = isset($row['net_unknown_count']) ? (int) $row['net_unknown_count'] : 0;
	$normalizedUnknown = isset($row['normalized_unknown_count']) ? (int) $row['normalized_unknown_count'] : 0;
	$rawUnknown = isset($row['raw_unknown_count']) ? (int) $row['raw_unknown_count'] : 0;
	$valueStatus = isset($row['value_status']) ? $row['value_status'] : 'complete';
	$salesman = isset($row['salesman']) ? $row['salesman'] : '';
	if (!isset($allDaily[$date])) $allDaily[$date] = spEmptyMetrics();
	if (!isset($allWeekly[$week])) $allWeekly[$week] = spEmptyMetrics();
	if (!isset($teamDaily[$teamKey][$date])) $teamDaily[$teamKey][$date] = spEmptyMetrics();
	if (!isset($teamWeekly[$teamKey][$week])) $teamWeekly[$teamKey][$week] = spEmptyMetrics();
	if (!isset($teamWeekdays[$teamKey][$week][$weekday])) $teamWeekdays[$teamKey][$week][$weekday] = spEmptyMetrics();
	if (!isset($people[$salesman])) $people[$salesman] = array('name' => $salesman, 'metrics' => spEmptyMetrics());
	spAddMetric($allDaily[$date], $metric, $count, $raw, $normalized, $adjusted, $net, $netKnown, $netUnknown, $normalizedUnknown, $rawUnknown, $valueStatus);
	spAddMetric($allWeekly[$week], $metric, $count, $raw, $normalized, $adjusted, $net, $netKnown, $netUnknown, $normalizedUnknown, $rawUnknown, $valueStatus);
	spAddMetric($teamDaily[$teamKey][$date], $metric, $count, $raw, $normalized, $adjusted, $net, $netKnown, $netUnknown, $normalizedUnknown, $rawUnknown, $valueStatus);
	spAddMetric($teamWeekly[$teamKey][$week], $metric, $count, $raw, $normalized, $adjusted, $net, $netKnown, $netUnknown, $normalizedUnknown, $rawUnknown, $valueStatus);
	spAddMetric($teamWeekdays[$teamKey][$week][$weekday], $metric, $count, $raw, $normalized, $adjusted, $net, $netKnown, $netUnknown, $normalizedUnknown, $rawUnknown, $valueStatus);
	spAddMetric($people[$salesman]['metrics'], $metric, $count, $raw, $normalized, $adjusted, $net, $netKnown, $netUnknown, $normalizedUnknown, $rawUnknown, $valueStatus);
}

function spHistoricalTeamMembership($db) {
	$rows = spRows($db, 'SELECT m.salesmancode, s.salesmanname, m.team_id, DATE_FORMAT(m.valid_from,\'%Y-%m-%d\') AS valid_from, DATE_FORMAT(m.valid_to,\'%Y-%m-%d\') AS valid_to FROM sales_performance_team_membership m LEFT JOIN salesman s ON s.salesmancode=m.salesmancode ORDER BY m.salesmancode, m.valid_from, m.id', array());
	$byName = array();
	$membersByTeam = array();
	$fallbackByName = array();
	foreach ($rows as $row) {
		if ($row['salesmanname'] === null || trim($row['salesmanname']) === '') continue;
		$name = $row['salesmanname'];
		if (!isset($byName[$name])) $byName[$name] = array();
		$byName[$name][] = array('team_id' => (int) $row['team_id'], 'valid_from' => $row['valid_from'], 'valid_to' => $row['valid_to']);
		$membersByTeam[(int) $row['team_id']][$name] = $name;
	}
	$legacyTeams = spRows($db, 'SELECT id, members FROM salesteam ORDER BY id', array());
	$legacyCounts = array();
	foreach ($legacyTeams as $team) {
		foreach (array_filter(array_map('trim', explode(',', (string) $team['members']))) as $name) {
			if (!isset($legacyCounts[$name])) $legacyCounts[$name] = array();
			$legacyCounts[$name][] = (int) $team['id'];
		}
	}
	foreach ($legacyCounts as $name => $teamIds) $fallbackByName[$name] = count($teamIds) === 1 ? 't' . $teamIds[0] : 'unassigned';
	return array('by_name' => $byName, 'members_by_team' => $membersByTeam, 'fallback_by_name' => $fallbackByName);
}

function spTeamKeyForDate($salesman, $date, $currentMapping, $history, $visibleTeams) {
	if (isset($history['by_name'][$salesman])) {
		$matches = array();
		foreach ($history['by_name'][$salesman] as $interval) {
			if ($interval['valid_from'] <= $date && ($interval['valid_to'] === null || $interval['valid_to'] > $date)) $matches[] = (int) $interval['team_id'];
		}
		if (count($matches) === 0) {
			$firstInterval = $history['by_name'][$salesman][0];
			if ($date < $firstInterval['valid_from'] && isset($history['fallback_by_name'])) return isset($history['fallback_by_name'][$salesman]) ? $history['fallback_by_name'][$salesman] : 'unassigned';
			if ($date >= $firstInterval['valid_from']) return 'unassigned';
			return isset($currentMapping[$salesman]) ? $currentMapping[$salesman] : 'unassigned';
		}
		if (count($matches) !== 1) return 'unassigned';
		foreach ($visibleTeams as $team) if ((int) $team['id'] === $matches[0]) return 't' . $matches[0];
		return 'unassigned';
	}
	return isset($currentMapping[$salesman]) ? $currentMapping[$salesman] : 'unassigned';
}

function spTeamKeyForFact(array $row, $date, $currentMapping, $history, $visibleTeams) {
	if (!empty($row['attribution_snapshot'])) {
		if (!isset($row['event_team_id']) || $row['event_team_id'] === null || $row['event_team_id'] === '') return 'unassigned';
		foreach ($visibleTeams as $team) if ((int) $team['id'] === (int) $row['event_team_id']) return 't' . (int) $row['event_team_id'];
		return 'unassigned';
	}
	return spTeamKeyForDate(isset($row['salesman']) ? $row['salesman'] : '', $date, $currentMapping, $history, $visibleTeams);
}

function spApprovedNamedScope($db) {
	$rows = spRows($db, "SELECT id, scope_key, label, scope_version, team_ids_text, include_unassigned, validation_status FROM sales_performance_scopes WHERE scope_key='core_sales_teams' AND validation_status='approved' ORDER BY scope_version DESC LIMIT 1", array());
	return count($rows) ? $rows[0] : null;
}

function spNamedScopeTeamIds(array $scope) {
	$ids = array();
	foreach (explode(',', (string) $scope['team_ids_text']) as $rawId) {
		$rawId = trim($rawId);
		if (!ctype_digit($rawId) || (int) $rawId < 1) throw new \SAHamid\BI\Exception\BIException('named_scope_invalid', 'The approved named population contains an invalid team mapping. Ask an administrator to review it.', 503);
		$id = (int) $rawId;
		if (in_array($id, $ids, true)) throw new \SAHamid\BI\Exception\BIException('named_scope_invalid', 'The approved named population contains a duplicate team mapping. Ask an administrator to review it.', 503);
		$ids[] = $id;
	}
	if (count($ids) === 0) throw new \SAHamid\BI\Exception\BIException('named_scope_invalid', 'The approved named population has no teams. Ask an administrator to review it.', 503);
	sort($ids, SORT_NUMERIC);
	return $ids;
}

function spNamedPopulationTeamKey(array $row, $date, $currentMapping, $history, $allTeams, array $population) {
	$snapshotSources = array('frozen_quotation_issue', 'captured_customer_po_receipt', 'captured_followup', 'captured_enquiry_event', 'enquiry_event');
	$isSnapshot = !empty($row['attribution_snapshot']) || (isset($row['source_type']) && in_array($row['source_type'], $snapshotSources, true));
	if ($isSnapshot) {
		$teamId = null;
		if (isset($row['event_team_id']) && $row['event_team_id'] !== '' && $row['event_team_id'] !== null) $teamId = (int) $row['event_team_id'];
		elseif (isset($row['credited_team_id']) && $row['credited_team_id'] !== '' && $row['credited_team_id'] !== null) $teamId = (int) $row['credited_team_id'];
		elseif (isset($row['credited_team']) && $row['credited_team'] !== '' && $row['credited_team'] !== null) $teamId = (int) $row['credited_team'];
		$teamKey = $teamId === null ? 'unassigned' : 't' . $teamId;
	} else {
		$salesperson = isset($row['salesman']) ? $row['salesman'] : (isset($row['credited_salesperson']) ? $row['credited_salesperson'] : '');
		if (isset($history['by_name'][$salesperson])) {
			$matches = array();
			foreach ($history['by_name'][$salesperson] as $interval) {
				if ($interval['valid_from'] <= $date && ($interval['valid_to'] === null || $interval['valid_to'] > $date)) $matches[] = (int) $interval['team_id'];
			}
			if (count($matches) === 1) $teamKey = 't' . $matches[0];
			elseif (count($matches) > 1) $teamKey = 'unassigned';
			else {
				$firstInterval = $history['by_name'][$salesperson][0];
				if ($date < $firstInterval['valid_from']) $teamKey = isset($history['fallback_by_name'][$salesperson]) ? $history['fallback_by_name'][$salesperson] : 'unassigned';
				elseif ($date >= $firstInterval['valid_from']) $teamKey = 'unassigned';
				else $teamKey = isset($currentMapping[$salesperson]) ? $currentMapping[$salesperson] : 'unassigned';
			}
		} else $teamKey = isset($currentMapping[$salesperson]) ? $currentMapping[$salesperson] : 'unassigned';
	}
	if ($teamKey === 'unassigned') return !empty($population['include_unassigned']) ? 'unassigned' : null;
	if (preg_match('/^t([0-9]+)$/', $teamKey, $match) && in_array((int) $match[1], $population['team_ids'], true)) return $teamKey;
	return null;
}

function spNamedPopulationSnapshotSql($teamColumn, array $population) {
	$selected = array_map('intval', $population['team_ids']);
	$condition = $teamColumn . ' IN (' . implode(',', $selected) . ')';
	if (!empty($population['include_unassigned'])) $condition = '(' . $condition . ' OR ' . $teamColumn . ' IS NULL)';
	return ' AND ' . $condition;
}

function spAssignFrozenFactTeams(array &$facts, $currentMapping, $history, $visibleTeams) {
	$snapshotSources = array('frozen_quotation_issue', 'captured_customer_po_receipt', 'captured_followup', 'captured_enquiry_event', 'enquiry_event');
	foreach ($facts as &$fact) {
		if (!empty($fact['credited_team_id']) || in_array($fact['source_type'], $snapshotSources, true)) continue;
		$date = isset($fact['business_at']) ? substr((string) $fact['business_at'], 0, 10) : '';
		if ($date === '') continue;
		$teamKey = spTeamKeyForFact(array(
			'salesman' => isset($fact['credited_salesperson']) ? $fact['credited_salesperson'] : '',
			'attribution_snapshot' => 0
		), $date, $currentMapping, $history, $visibleTeams);
		if (preg_match('/^t([0-9]+)$/', $teamKey, $match)) {
			$fact['credited_team_id'] = (int) $match[1];
			$fact['credited_team'] = (int) $match[1];
		}
	}
	unset($fact);
}

function spLoadPeriod($db, $from, $to, $salespeople, $teamRows, $teamBySalesman, $followupStart, $businessCutoff = null, $coverageScope = 'erp_company', $teamMembershipHistory = array('by_name' => array(), 'members_by_team' => array()), $calendarOverrides = array(), $namedPopulation = null, $allTeamRows = array()) {
	$today = date('Y-m-d');
	$effectiveTo = ($to < $today) ? $to : $today;
	if ($businessCutoff !== null && $businessCutoff < $effectiveTo) $effectiveTo = $businessCutoff;
	$sourceRows = $from > $today || $effectiveTo < $from
		? array('enquiries' => array(), 'quotations' => array(), 'purchase_orders' => array(), 'order_confirmations' => array(), 'delivery_challans' => array(), 'followups' => array(), 'enquiry_responses' => array(), 'company_regrets' => array(), 'customer_rejections' => array())
		: spMetricRows($db, $from, $effectiveTo, $salespeople);
	$allDaily = array();
	$allWeekly = array();
	$teamDaily = array();
	$teamWeekly = array();
	$teamWeekdays = array();
	$people = array();
	foreach ($teamRows as $team) {
		$key = 't' . $team['id'];
		$teamDaily[$key] = array();
		$teamWeekly[$key] = array();
		$teamWeekdays[$key] = array();
	}
	if ($namedPopulation === null || !empty($namedPopulation['include_unassigned'])) {
		$teamDaily['unassigned'] = array();
		$teamWeekly['unassigned'] = array();
		$teamWeekdays['unassigned'] = array();
	}

	$mapping = array(
		'enquiries' => 'enquiries',
		'quotations' => 'quotations',
		'purchase_orders' => 'purchase_orders',
		'order_confirmations' => 'order_confirmations',
		'delivery_challans' => 'delivery_challans',
		'followups' => 'followups',
		'enquiry_responses' => 'enquiry_responses',
		'company_regrets' => 'company_regrets',
		'customer_rejections' => 'customer_rejections'
	);
	foreach ($mapping as $sourceKey => $metric) {
		foreach ($sourceRows[$sourceKey] as $row) {
			$date = $row['report_date'];
			if ($namedPopulation !== null) {
				$teamKey = spNamedPopulationTeamKey($row, $date, $teamBySalesman, $teamMembershipHistory, $allTeamRows, $namedPopulation);
				if ($teamKey === null) continue;
			} else {
				$teamKey = spTeamKeyForFact($row, $date, $teamBySalesman, $teamMembershipHistory, $teamRows);
			}
			spAddRowToBuckets($allDaily, $allWeekly, $teamDaily, $teamWeekly, $teamWeekdays, $people, $row, $metric, $teamKey);
			if ($sourceKey === 'quotations' && isset($row['revised_count']) && (int) $row['revised_count'] > 0) {
				$revisionRow = $row;
				$revisionRow['record_count'] = $row['revised_count'];
				$revisionRow['raw_value'] = $row['revised_raw_value'];
				$revisionRow['net_value'] = $row['revised_net_value'];
				$revisionRow['normalized_value'] = $row['revised_normalized_value'];
				$revisionRow['adjusted_count'] = $row['revised_adjusted_count'];
				$revisionRow['net_known_count'] = $row['revised_net_known_count'];
				$revisionRow['net_unknown_count'] = $row['revised_net_unknown_count'];
				$revisionRow['normalized_unknown_count'] = $row['revised_normalized_unknown_count'];
				$revisionRow['raw_unknown_count'] = $row['revised_raw_unknown_count'];
				$revisionRow['value_status'] = $row['revised_value_status'];
				spAddRowToBuckets($allDaily, $allWeekly, $teamDaily, $teamWeekly, $teamWeekdays, $people, $revisionRow, 'revised_quotations', $teamKey);
			}
		}
	}

	$asOf = $effectiveTo;
	$coverage = $from > $asOf ? array() : spCoverageStatuses($db, $from, $asOf, $coverageScope, array_map(function($team) { return (int) $team['id']; }, $teamRows), $followupStart);
	$reportingDays = 0;
	foreach ($coverage as $coverageDate => $coverageState) {
		if ($coverageState === 'complete' && spIsPlannedWorkday($coverageDate, $calendarOverrides)) $reportingDays++;
	}
	$calendar = array();
	$daily = array();
	$cursor = strtotime($from);
	$end = strtotime($to);
	while ($cursor <= $end) {
		$date = date('Y-m-d', $cursor);
		$week = spWeekStart($date);
		$weekday = (int) date('N', $cursor);
		$plannedWorkday = spIsPlannedWorkday($date, $calendarOverrides);
		$state = ($date > $today) ? 'future' : (($date > $asOf) ? 'after_cutoff' : 'reported');
		$calendar[] = array(
			'date' => $date,
			'week_start' => $week,
			'weekday' => $weekday,
			'planned_workday' => $plannedWorkday,
			'calendar_override_reason' => isset($calendarOverrides[$date]) ? $calendarOverrides[$date]['reason'] : null,
			'state' => $state,
			'coverage_status' => isset($coverage[$date]) ? $coverage[$date] : ($date > $asOf ? 'after_cutoff' : ($date < $followupStart ? 'not_captured_historically' : 'not_submitted')),
			'followup_state' => ($date > $today) ? 'future' : (($date > $asOf) ? 'after_cutoff' : (($date < $followupStart) ? 'not_captured' : 'reported'))
		);
		$daily[] = array('date' => $date, 'metrics' => isset($allDaily[$date]) ? $allDaily[$date] : spEmptyMetrics(), 'planned_workday' => $plannedWorkday,
			'calendar_override_reason' => isset($calendarOverrides[$date]) ? $calendarOverrides[$date]['reason'] : null,
			'coverage_status' => isset($coverage[$date]) ? $coverage[$date] : ($date > $asOf ? 'after_cutoff' : ($date < $followupStart ? 'not_captured_historically' : 'not_submitted')));
		if (!isset($allWeekly[$week])) $allWeekly[$week] = spEmptyMetrics();
		$cursor = strtotime('+1 day', $cursor);
	}

	$totals = spEmptyMetrics();
	foreach ($daily as $day) {
		foreach (spMetricKeys() as $metric) {
			$item = $day['metrics'][$metric];
			spAddMetric($totals, $metric, $item['count'], $item['raw_value'], $item['normalized_value'], $item['adjusted_documents'], $item['net_value'], $item['net_known_count'], $item['net_unknown_count'], $item['normalized_unknown_count'], $item['raw_unknown_count'], $item['value_status']);
		}
	}
	$weekly = array();
	ksort($allWeekly);
	foreach ($allWeekly as $week => $metrics) {
		$weekly[] = array('week_start' => $week, 'metrics' => $metrics);
	}

	$visibleTeamIds = array();
	foreach ($teamRows as $team) $visibleTeamIds['t' . $team['id']] = true;
	if ($namedPopulation === null || !empty($namedPopulation['include_unassigned'])) $visibleTeamIds['unassigned'] = true;
	$teams = array();
	foreach ($teamRows as $team) {
		$key = 't' . $team['id'];
		$teams[] = spBuildTeam($key, (int) $team['id'], $team['name'], $from, $to, $teamDaily, $teamWeekly, $teamWeekdays, $calendar, $followupStart);
	}
	if ($namedPopulation === null || !empty($namedPopulation['include_unassigned'])) $teams[] = spBuildTeam('unassigned', 0, 'Unassigned', $from, $to, $teamDaily, $teamWeekly, $teamWeekdays, $calendar, $followupStart);

	$peopleList = array();
	foreach ($people as $person) $peopleList[] = $person;
	usort($peopleList, function($a, $b) { return strcasecmp($a['name'], $b['name']); });

	return array(
		'from' => $from,
		'to' => $to,
		'as_of' => $asOf,
		'reporting_days' => $reportingDays,
		'metrics' => $totals,
		'daily' => $daily,
		'weekly' => $weekly,
		'teams' => $teams,
		'salespeople' => $peopleList,
		'calendar' => $calendar
	);
}

function spBuildTeam($key, $id, $name, $from, $to, $teamDaily, $teamWeekly, $teamWeekdays, $calendar, $followupStart) {
	$metrics = spEmptyMetrics();
	$weeks = array();
	$calendarByDate = array();
	foreach ($calendar as $day) {
		$calendarByDate[$day['date']] = $day;
		$week = $day['week_start'];
		if (!isset($weeks[$week])) $weeks[$week] = array('week_start' => $week, 'metrics' => spEmptyMetrics(), 'days' => array());
	}
	foreach ($calendar as $day) {
		$date = $day['date'];
		$week = $day['week_start'];
		$dayMetrics = isset($teamDaily[$key][$date]) ? $teamDaily[$key][$date] : spEmptyMetrics();
		foreach (spMetricKeys() as $metric) {
			$item = $dayMetrics[$metric];
			spAddMetric($metrics, $metric, $item['count'], $item['raw_value'], $item['normalized_value'], $item['adjusted_documents'], $item['net_value'], $item['net_known_count'], $item['net_unknown_count'], $item['normalized_unknown_count'], $item['raw_unknown_count'], $item['value_status']);
			spAddMetric($weeks[$week]['metrics'], $metric, $item['count'], $item['raw_value'], $item['normalized_value'], $item['adjusted_documents'], $item['net_value'], $item['net_known_count'], $item['net_unknown_count'], $item['normalized_unknown_count'], $item['raw_unknown_count'], $item['value_status']);
		}
	}
	foreach ($weeks as $week => $weekData) {
		for ($weekday = 1; $weekday <= 6; $weekday++) {
			$date = date('Y-m-d', strtotime($week . ' +' . ($weekday - 1) . ' days'));
			$dayMetrics = isset($teamDaily[$key][$date]) ? $teamDaily[$key][$date] : spEmptyMetrics();
			if ($date < $from) $state = 'outside_period';
			elseif ($date > $to) $state = 'after_cutoff';
			elseif (isset($calendarByDate[$date])) $state = $calendarByDate[$date]['state'];
			else $state = 'future';
			$coverageState = isset($calendarByDate[$date]) ? $calendarByDate[$date]['coverage_status'] : $state;
			$followupState = isset($calendarByDate[$date]) ? $calendarByDate[$date]['followup_state'] : $state;
			$weeks[$week]['days'][$weekday] = array(
				'date' => $date,
				'state' => $state,
				'planned_workday' => isset($calendarByDate[$date]) ? $calendarByDate[$date]['planned_workday'] : ((int) date('N', strtotime($date)) <= 6),
				'calendar_override_reason' => isset($calendarByDate[$date]) ? $calendarByDate[$date]['calendar_override_reason'] : null,
				'coverage_status' => $coverageState,
				'followup_state' => $followupState,
				'metrics' => $dayMetrics
			);
		}
	}
	return array('id' => $id, 'name' => $name, 'metrics' => $metrics, 'weeks' => array_values($weeks));
}

function spPostText($key, $maxLength) {
	$value = isset($_POST[$key]) && !is_array($_POST[$key]) ? trim(stripslashes($_POST[$key])) : '';
	if (strlen($value) > $maxLength) $value = substr($value, 0, $maxLength);
	return $value;
}

function spCoverageReviewContext(array $metrics, $date, $scopeKey, $teamId, $userId, $periodFrom, $periodThrough, $businessCutoff, $calendarVersion, $issuedAt, array $reportTeamIds) {
	$metricsJson = json_encode($metrics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if ($metricsJson === false) throw new Exception('Could not encode the coverage review metrics.');
	sort($reportTeamIds, SORT_NUMERIC);
	return array(
		'version' => 1,
		'user_id' => (string) $userId,
		'coverage_date' => (string) $date,
		'scope_key' => (string) $scopeKey,
		'team_id' => $teamId === null ? null : (int) $teamId,
		'data_basis' => 'live_erp_legacy_document_proxies',
		'definition_version' => 'commercial-fact-v1-draft',
		'report_team_ids' => array_values(array_unique(array_map('intval', $reportTeamIds))),
		'period_from' => (string) $periodFrom,
		'period_through' => (string) $periodThrough,
		'business_cutoff' => (string) $businessCutoff,
		'calendar_version' => (string) $calendarVersion,
		'issued_at' => (int) $issuedAt,
		'metrics_sha256' => hash('sha256', $metricsJson)
	);
}

function spCoverageReviewSignature(array $context, $sessionKey) {
	$contextJson = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if ($contextJson === false) throw new Exception('Could not encode the coverage review signature.');
	return array($contextJson, hash_hmac('sha256', $contextJson, $sessionKey));
}

function spAdjustmentSourceValue($db, $metric, $sourceKey) {
	if ($metric === 'quotation') {
		if (!preg_match('/^\d+$/', $sourceKey)) return null;
		$snapshot = (new \SAHamid\BI\Commercial\Source\QuotationSource())->snapshotDocument($db, $sourceKey);
		return $snapshot === null ? null : $snapshot['fact']['net_amount'];
	} elseif ($metric === 'delivery_challan') {
		if (!preg_match('/^\d+$/', $sourceKey)) return null;
		$rows = spRows($db,
			'SELECT CAST(SUM(CAST(d.unitprice AS DECIMAL(24,6)) * (1 - CAST(d.discountpercent AS DECIMAL(12,8))) * CAST(d.quantity AS DECIMAL(24,6)) * CAST(o.quantity AS DECIMAL(24,6))) AS DECIMAL(24,2)) AS doc_value
			 FROM dcs dc
			 LEFT JOIN dcdetails d ON d.orderno = dc.orderno AND d.lineoptionno = 0
			 LEFT JOIN dcoptions o ON o.orderno = d.orderno AND o.lineno = d.orderlineno AND o.optionno = 0 AND o.dispatchid = d.dispatchid
			 WHERE dc.orderno = ?',
			array($sourceKey)
		);
	} elseif ($metric === 'purchase_order') {
		$parts = explode('|', $sourceKey, 2);
		if (count($parts) !== 2 || !preg_match('/^\d+$/', $parts[0]) || trim($parts[1]) === '') return null;
		$rows = spRows($db,
			'SELECT CAST(SUM(CAST(d.unitprice AS DECIMAL(24,6)) * (1 - CAST(d.discountpercent AS DECIMAL(12,8))) * CAST(d.quantity AS DECIMAL(24,6)) * CAST(o.quantity AS DECIMAL(24,6))) AS DECIMAL(24,2)) AS doc_value
			 FROM ocs oc
			 LEFT JOIN ocdetails d ON d.orderno = oc.orderno AND d.pono = oc.pono AND d.lineoptionno = 0
			 LEFT JOIN ocoptions o ON o.orderno = d.orderno AND o.pono = d.pono AND o.lineno = d.orderlineno AND o.optionno = 0
			 WHERE oc.orderno = ? AND oc.pono = ?',
			array($parts[0], $parts[1])
		);
	} else {
		return null;
	}
	return count($rows) > 0 && $rows[0]['doc_value'] !== null ? (string) $rows[0]['doc_value'] : null;
}

function spAcquireAdjustmentLock($db, $metric, $sourceKey) {
	spExecute($db, "INSERT IGNORE INTO sales_performance_normalization_locks (source_type, source_key, policy_version, revision_no, updated_at) VALUES (?, ?, 'normalized-value-v1', 0, NOW())", array($metric, $sourceKey));
	$locked = spRows($db, "SELECT revision_no FROM sales_performance_normalization_locks WHERE source_type=? AND source_key=? AND policy_version='normalized-value-v1' FOR UPDATE", array($metric, $sourceKey));
	if (count($locked) === 0) throw new Exception('Could not secure the normalization review.');
}

function spExecuteStatement($stmt, array $params) {
	if (count($params) > 0) {
		$types = str_repeat('s', count($params));
		$bind = array($types);
		foreach ($params as $index => $value) $bind[] = &$params[$index];
		call_user_func_array(array($stmt, 'bind_param'), $bind);
	}
	return mysqli_stmt_execute($stmt);
}

function spCreateFrozenRun($db, $userId, $companyName, $databaseName, array $scope, array $filters, $cutoff, $calendarVersion, array $payload, array $runFacts) {
	if (count($runFacts) > 25000) throw new \SAHamid\BI\Exception\BIException('export_limit', 'The selected current and comparison periods contain more than 25,000 supporting facts. Narrow their date ranges before freezing the report.', 413);
	foreach ($runFacts as &$fact) {
		$periodRole = isset($fact['period_role']) && $fact['period_role'] === 'comparison' ? 'comparison' : 'current';
		$fact['fact_id'] = hash('sha256', $periodRole . "\n" . (string) $fact['fact_id']);
		$evidence = isset($fact['source_evidence']) && is_array($fact['source_evidence']) ? $fact['source_evidence'] : array();
		$exportMetadata = array(
			'period_role' => $periodRole,
			'credited_salesperson' => isset($fact['credited_salesperson']) ? $fact['credited_salesperson'] : null,
			'credited_team' => isset($fact['credited_team']) ? $fact['credited_team'] : null,
			'is_revision' => !empty($fact['is_revision']),
			'reporting_raw_amount' => isset($fact['reporting_raw_amount']) ? $fact['reporting_raw_amount'] : null
		);
		$evidence['_sales_performance_export'] = isset($evidence['_sales_performance_export']) && is_array($evidence['_sales_performance_export'])
			? array_merge($evidence['_sales_performance_export'], $exportMetadata) : $exportMetadata;
		$fact['source_evidence'] = $evidence;
		if (!isset($fact['attribution_version'])) $fact['attribution_version'] = 'effective-membership-v1-current-fallback';
		if (!array_key_exists('actor_user_id', $fact)) $fact['actor_user_id'] = null;
		if (!array_key_exists('entered_by_user_id', $fact)) $fact['entered_by_user_id'] = null;
		if (!array_key_exists('credited_salesman_code', $fact)) $fact['credited_salesman_code'] = null;
		if (!array_key_exists('credited_team_id', $fact)) $fact['credited_team_id'] = isset($fact['credited_team']) && is_numeric($fact['credited_team']) ? (int) $fact['credited_team'] : null;
	}
	unset($fact);
	$key = sprintf('%s-%s-%s-%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(2)), bin2hex(random_bytes(6)));
	$scopeText = json_encode($scope, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	$filterText = json_encode($filters, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	$totalsText = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	$factsText = json_encode($runFacts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if ($scopeText === false || $filterText === false || $totalsText === false || $factsText === false) throw new Exception('The reporting result could not be serialized.');
	$scopeFingerprint = hash('sha256', $scopeText);
	$completeness = json_encode(array('current_reporting_days' => $payload['current']['reporting_days'], 'comparison_reporting_days' => $payload['comparison']['reporting_days'], 'current_daily_coverage' => array_map(function($day) { return array($day['date'], $day['coverage_status']); }, $payload['current']['daily'])), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the frozen report run.');
	try {
		spExecute($db,
			"INSERT INTO sales_performance_report_runs (run_key, run_state, requested_by, company_name, database_name, authorized_scope_text, scope_fingerprint, filters_text, business_cutoff, recorded_data_cutoff, report_timezone, definition_version, calendar_version, attribution_version, normalization_version, generated_at, validation_state, completeness_text, totals_text, export_manifest_text, content_hash, ready_at) VALUES (?, 'building', ?, ?, ?, ?, ?, ?, ?, NOW(), 'Asia/Karachi', 'commercial-fact-v1-draft', ?, 'effective-membership-v1-current-fallback', 'normalized-value-v1', NOW(), 'provisional', ?, ?, ?, NULL, NULL)",
			array($key, $userId, $companyName, $databaseName, $scopeText, $scopeFingerprint, $filterText, $cutoff . ' 23:59:59', $calendarVersion, $completeness, $totalsText, '{"screen":"ready","csv":"available_from_run","xlsx":"available_from_run","pdf":"available_from_run","content_hash_version":"stored_fact_rows_v1"}')
		);
		$runId = mysqli_insert_id($db);
		$factStmt = mysqli_prepare($db,
			'INSERT INTO sales_performance_report_run_facts (run_id, fact_id, metric_family, source_type, source_key, source_version, salescaseref, customer, branch, actor_user_id, entered_by_user_id, credited_salesperson_code, credited_team_id, attribution_basis, attribution_version, business_at, recorded_at, date_basis, raw_amount, net_amount, normalized_amount, currency, reporting_currency, tax_basis, tax_rule_version, raw_count_eligible, normalized_count_eligible, value_status, coverage_status, eligibility_status, warnings_text, normalization_references, source_evidence, calculation_version) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
		);
		if (!$factStmt) throw new Exception('Could not prepare frozen supporting evidence.');
		foreach ($runFacts as $fact) {
			$businessAt = null;
			if (isset($fact['business_at'])) {
				$businessAt = strlen((string) $fact['business_at']) > 10
					? substr((string) $fact['business_at'], 0, 19)
					: substr((string) $fact['business_at'], 0, 10) . ' 00:00:00';
			}
			$values = array(
				(string) $runId, $fact['fact_id'], $fact['metric_family'], $fact['source_type'], $fact['source_key'], $fact['source_version'],
				$fact['salescaseref'], $fact['customer'], $fact['branch'], $fact['actor_user_id'], $fact['entered_by_user_id'], $fact['credited_salesman_code'],
				$fact['credited_team_id'], $fact['attribution_basis'], $fact['attribution_version'], $businessAt, $fact['recorded_at'],
				$fact['date_basis'], $fact['raw_amount'], $fact['net_amount'], $fact['normalized_amount'], $fact['currency'], $fact['reporting_currency'],
				$fact['tax_basis'], $fact['tax_rule_version'], (string) $fact['raw_count_eligible'], (string) $fact['normalized_count_eligible'],
				$fact['value_status'], $fact['coverage_status'], $fact['eligibility_status'], json_encode($fact['warnings']),
				json_encode($fact['normalization_references']), json_encode($fact['source_evidence']), $fact['calculation_version']
			);
			if (!spExecuteStatement($factStmt, $values)) throw new Exception('Could not store all frozen supporting evidence.');
		}
		mysqli_stmt_close($factStmt);
		$contentHash = spFrozenRunContentHash($db, $runId, $totalsText);
		spExecute($db, "UPDATE sales_performance_report_runs SET run_state='ready', content_hash=?, ready_at=NOW() WHERE id=? AND run_state='building'", array($contentHash, (string) $runId));
		if (!mysqli_commit($db)) throw new Exception('Could not finish the frozen report run.');
	} catch (Exception $e) {
		mysqli_rollback($db);
		throw $e;
	}
	return array('run_key' => $key, 'content_hash' => $contentHash, 'state' => 'ready', 'integrity_status' => 'verified', 'frozen_at' => date('c'));
}

function spFrozenRunContentHash($db, $runId, $totalsText) {
	$columns = 'fact_id, metric_family, source_type, source_key, source_version, salescaseref, customer, branch, actor_user_id, entered_by_user_id, credited_salesperson_code AS credited_salesman_code, credited_team_id, attribution_basis, attribution_version, business_at, recorded_at, date_basis, raw_amount, net_amount, normalized_amount, currency, reporting_currency, tax_basis, tax_rule_version, raw_count_eligible, normalized_count_eligible, value_status, coverage_status, eligibility_status, warnings_text, normalization_references, source_evidence, calculation_version';
	$rows = spRows($db, 'SELECT ' . $columns . ' FROM sales_performance_report_run_facts WHERE run_id=? ORDER BY fact_id', array((string) $runId));
	return spFrozenRunContentHashFromFacts($totalsText, $rows);
}

function spFrozenRunContentHashFromFacts($totalsText, array $rows) {
	$factsText = json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	if ($factsText === false) throw new Exception('The frozen report evidence could not be verified.');
	return hash('sha256', (string) $totalsText . "\n" . $factsText);
}

function spRequireFormToken() {
	$posted = isset($_POST['FormID']) ? $_POST['FormID'] : '';
	$session = isset($_SESSION['FormID']) ? $_SESSION['FormID'] : '';
	if ($posted === '' || $session === '' || !hash_equals($session, $posted)) {
		spRespond(403, array('status' => 'error', 'message' => 'The form session expired. Refresh the page and try again.'));
	}
}

function spCanAccessCase($db, $caseRef, $userName, $userId, $canViewCompany, $canViewTeams, $canViewSelf) {
	$rows = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref = ? LIMIT 1', array($caseRef));
	if (count($rows) === 0) return null;
	$case = $rows[0];
	if (spCanAccessCaseRow($db, $case, $userName, $userId, $canViewCompany, $canViewTeams, $canViewSelf)) return $case;
	return false;
}

function spCanAccessCaseRow($db, array $case, $userName, $userId, $canViewCompany, $canViewTeams, $canViewSelf) {
	if ($canViewCompany || ($canViewSelf && $case['salesman'] === $userName)) return true;
	if ($canViewTeams) {
		$grants = spRows($db, "SELECT team_id FROM sales_performance_scope_grants WHERE user_id = ? AND capability = 'view_team' AND revoked_at IS NULL AND team_id IS NOT NULL", array($userId));
		$ids = array_map(function($row) { return (int) $row['team_id']; }, $grants);
		$teams = mysqli_query($db, 'SELECT id, members FROM salesteam');
		if ($teams) {
			while ($team = mysqli_fetch_assoc($teams)) {
				if (in_array((int) $team['id'], $ids, true) && in_array($case['salesman'], array_map('trim', explode(',', $team['members'])), true)) {
					mysqli_free_result($teams);
					return true;
				}
			}
			mysqli_free_result($teams);
		}
	}
	return false;
}

function spEventAttribution($db, $salesman, $businessDate = null) {
	$codeRows = spRows($db, 'SELECT salesmancode FROM salesman WHERE salesmanname = ? LIMIT 1', array($salesman));
	$salesmanCode = count($codeRows) ? $codeRows[0]['salesmancode'] : null;
	if ($salesmanCode !== null && $businessDate !== null) {
		$history = spRows($db, 'SELECT team_id FROM sales_performance_team_membership WHERE salesmancode=? ORDER BY valid_from, id', array($salesmanCode));
		if (count($history) > 0) {
			$effective = spRows($db, 'SELECT team_id FROM sales_performance_team_membership WHERE salesmancode=? AND valid_from<=? AND (valid_to IS NULL OR valid_to>?) ORDER BY valid_from, id', array($salesmanCode, $businessDate, $businessDate));
			return array('salesman_code' => $salesmanCode, 'team_id' => count($effective) === 1 ? $effective[0]['team_id'] : null, 'basis' => count($effective) === 1 ? 'history' : 'history_gap_or_overlap');
		}
	}
	$teamRows = spRows($db, 'SELECT id FROM salesteam WHERE FIND_IN_SET(?, REPLACE(members, ", ", ",")) > 0 ORDER BY id', array($salesman));
	return array('salesman_code' => $salesmanCode, 'team_id' => count($teamRows) === 1 ? $teamRows[0]['id'] : null, 'basis' => count($teamRows) === 1 ? 'current_fallback' : 'current_overlap_or_unmapped');
}

function spQuotationFamilyKey($db, $orderNo, $revisionFor) {
	$root = trim((string) $revisionFor);
	if ($root === '' || !ctype_digit($root)) return (string) $orderNo;
	$seen = array((string) $orderNo => true);
	for ($step = 0; $step < 20; $step++) {
		if (isset($seen[$root])) return $root;
		$seen[$root] = true;
		$rows = spRows($db, 'SELECT revision_for FROM salesorders WHERE orderno=? AND quotation=1 LIMIT 1', array($root));
		if (count($rows) === 0 || trim((string) $rows[0]['revision_for']) === '') return $root;
		$next = trim((string) $rows[0]['revision_for']);
		if (!ctype_digit($next)) return $root;
		$root = $next;
	}
	return $root;
}

function spValidIdempotencyKey($key) {
	return preg_match('/^[A-Za-z0-9_-]{16,80}$/', $key) === 1;
}

function spReferenceDecimal($value, $label, $required = false) {
	$value = trim((string) $value);
	if ($value === '') {
		if ($required) throw new InvalidArgumentException($label . ' is required.');
		return null;
	}
	if (strlen($value) > 25 || !preg_match('/^\d{1,18}(?:\.\d{1,6})?$/', $value)) {
		throw new InvalidArgumentException($label . ' must be a non-negative decimal with at most six decimal places.');
	}
	return $value;
}

function spParseReferenceCsv($csv) {
	$headers = array('observation_key', 'period_from', 'period_through', 'cutoff_at', 'granularity', 'scope_key', 'team_name', 'salesperson_name', 'metric_key', 'observed_count', 'observed_value', 'value_basis', 'value_currency', 'denominator', 'notes');
	$stream = fopen('php://temp', 'r+');
	if (!$stream) throw new Exception('Could not read the reference observation rows.');
	fwrite($stream, $csv);
	rewind($stream);
	$header = fgetcsv($stream, 0, ',', '"', '\\');
	if (is_array($header) && isset($header[0])) $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
	if ($header !== $headers) { fclose($stream); throw new InvalidArgumentException('Use the provided CSV columns in the shown order.'); }
	$rows = array();
	$allowedGranularity = array('daily', 'weekly', 'monthly', 'period', 'summary');
	$allowedScopes = array('', 'erp_company', 'core_sales_teams', 'team', 'salesperson');
	$allowedBases = array('', 'raw', 'net', 'normalized', 'count');
	$seenKeys = array();
	$line = 1;
	while (($cells = fgetcsv($stream, 0, ',', '"', '\\')) !== false) {
		$line++;
		if ($cells === array(null) || (count($cells) === 1 && trim((string) $cells[0]) === '')) continue;
		if (count($cells) !== count($headers)) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' must contain exactly ' . count($headers) . ' columns.'); }
		if (count($rows) >= 500) { fclose($stream); throw new InvalidArgumentException('Import at most 500 observations at a time.'); }
		$row = array_combine($headers, array_map(function($cell) { return trim((string) $cell); }, $cells));
		if ($row['observation_key'] === '' || strlen($row['observation_key']) > 255 || preg_match('/[\x00-\x1F]/', $row['observation_key'])) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' needs a unique, readable observation key.'); }
		if (isset($seenKeys[$row['observation_key']])) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' repeats an observation key.'); }
		$seenKeys[$row['observation_key']] = true;
		if (!spValidDate($row['period_from']) || !spValidDate($row['period_through']) || $row['period_from'] > $row['period_through']) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has an invalid observation period.'); }
		$periodDays = (int) floor((strtotime($row['period_through']) - strtotime($row['period_from'])) / 86400) + 1;
		if ($periodDays > 400) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' covers more than 400 days.'); }
		if (!in_array($row['granularity'], $allowedGranularity, true)) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has an unsupported granularity.'); }
		if (!in_array($row['scope_key'], $allowedScopes, true)) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has an unsupported scope.'); }
		if (!in_array($row['metric_key'], spMetricKeys(), true)) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has an unsupported metric key.'); }
		if (!in_array($row['value_basis'], $allowedBases, true)) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has an unsupported value basis.'); }
		if ($row['cutoff_at'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2} (?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $row['cutoff_at'])) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has an invalid cutoff timestamp.'); }
		if ($row['cutoff_at'] !== '' && !spValidDate(substr($row['cutoff_at'], 0, 10))) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has an invalid cutoff date.'); }
		$row['observed_count'] = spReferenceDecimal($row['observed_count'], 'Observed count');
		$row['observed_value'] = spReferenceDecimal($row['observed_value'], 'Observed value');
		$row['denominator'] = spReferenceDecimal($row['denominator'], 'Denominator');
		if ($row['observed_count'] === null && $row['observed_value'] === null) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' must include a count or value.'); }
		if ($row['observed_value'] !== null && !in_array($row['value_basis'], array('raw', 'net', 'normalized'), true)) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' needs raw, net or normalized as its value basis.'); }
		if ($row['observed_value'] !== null && !preg_match('/^[A-Z]{3}$/', $row['value_currency'])) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' needs a three-letter value currency.'); }
		if ($row['observed_value'] === null && $row['value_currency'] !== '') { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has a value currency but no observed value.'); }
		if ($row['observed_value'] === null && $row['value_basis'] !== '' && $row['value_basis'] !== 'count') { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has a value basis but no observed value.'); }
		foreach (array('team_name', 'salesperson_name') as $nameField) if (strlen($row[$nameField]) > 120) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has an overlong ' . str_replace('_', ' ', $nameField) . '.'); }
		if (($row['scope_key'] === 'team' && $row['team_name'] === '') || ($row['scope_key'] === 'salesperson' && $row['salesperson_name'] === '')
			|| ($row['team_name'] !== '' && $row['scope_key'] !== 'team') || ($row['salesperson_name'] !== '' && $row['scope_key'] !== 'salesperson')) {
			fclose($stream);
			throw new InvalidArgumentException('CSV row ' . $line . ' must match team/person names to its scope key.');
		}
		if (strlen($row['notes']) > 60000) { fclose($stream); throw new InvalidArgumentException('CSV row ' . $line . ' has notes that are too long.'); }
		$rows[] = $row;
	}
	fclose($stream);
	if (count($rows) === 0) throw new InvalidArgumentException('The CSV contains no observation rows.');
	return $rows;
}

function spReferenceRunMatchesScope($db, array $reference, array $runScope, array $runFilters) {
	if (!isset($runScope['kind']) || $runScope['kind'] !== 'erp_company') return false;
	$teamFilters = array_key_exists('team_ids', $runFilters) ? $runFilters['team_ids'] : null;
	$peopleFilters = array_key_exists('salespeople', $runFilters) ? $runFilters['salespeople'] : null;
	if (($teamFilters !== null && !is_array($teamFilters)) || ($peopleFilters !== null && !is_array($peopleFilters))) return false;
	$teamFilters = is_array($teamFilters) ? $teamFilters : array();
	$peopleFilters = is_array($peopleFilters) ? $peopleFilters : array();
	$populationScope = isset($runFilters['population_scope']) ? trim((string) $runFilters['population_scope']) : '';
	if ($populationScope !== '') return false;

	$scopeKey = isset($reference['scope_key']) ? (string) $reference['scope_key'] : '';
	if ($scopeKey === 'erp_company') {
		return count($teamFilters) === 0 && count($peopleFilters) === 0
			? array('scope_key' => 'erp_company', 'scope_label' => 'Company-wide', 'team_id' => null)
			: false;
	}

	if ($scopeKey === 'team') {
		$teamName = trim((string) $reference['team_name']);
		if ($teamName === '' || trim((string) $reference['salesperson_name']) !== '' || count($teamFilters) !== 1 || count($peopleFilters) !== 0) return false;
		$teams = spRows($db, 'SELECT id, name FROM salesteam ORDER BY id', array());
		$matchingTeamIds = array();
		foreach ($teams as $team) if ((string) $team['name'] === $teamName) $matchingTeamIds[] = (int) $team['id'];
		if (count($matchingTeamIds) !== 1) return false;
		$teamId = $matchingTeamIds[0];
		$filterTeamId = is_numeric($teamFilters[0]) ? (int) $teamFilters[0] : 0;
		$scopeTeamIds = isset($runScope['team_ids']) && is_array($runScope['team_ids']) ? array_map('intval', $runScope['team_ids']) : array();
		if ($teamId < 1 || $filterTeamId !== $teamId || count($scopeTeamIds) !== 1 || $scopeTeamIds[0] !== $teamId) return false;
		return array('scope_key' => 'team', 'scope_label' => $teamName, 'team_id' => $teamId);
	}

	if ($scopeKey === 'salesperson') {
		$salesperson = trim((string) $reference['salesperson_name']);
		$scopePeople = isset($runScope['salespeople']) && is_array($runScope['salespeople']) ? $runScope['salespeople'] : array();
		if ($salesperson === '' || trim((string) $reference['team_name']) !== '' || count($teamFilters) !== 0 || count($peopleFilters) !== 1
			|| (string) $peopleFilters[0] !== $salesperson || count($scopePeople) !== 1 || (string) $scopePeople[0] !== $salesperson) return false;
		return array('scope_key' => 'salesperson', 'scope_label' => $salesperson, 'team_id' => null);
	}

	return false;
}

function spAdminSetupPayload($db, $userId = '', $canApproveNamedScope = false) {
	$calendarVersion = spCurrentCalendarVersion($db);
	$availableScopeTeams = spRows($db, 'SELECT id, name FROM salesteam ORDER BY name, id', array());
	$namedScopes = spRows($db,
		"SELECT id, scope_key, label, scope_version, team_ids_text, include_unassigned, validation_status, updated_by,
			DATE_FORMAT(updated_at,'%Y-%m-%d %H:%i') AS updated_at
		 FROM sales_performance_scopes WHERE scope_key='core_sales_teams' ORDER BY scope_version DESC LIMIT 30", array());
	$latestNamedScopeVersion = count($namedScopes) ? (int) $namedScopes[0]['scope_version'] : 0;
	foreach ($namedScopes as &$namedScope) {
		$namedScope['can_approve'] = $canApproveNamedScope && $namedScope['validation_status'] === 'pending_review'
			&& (int) $namedScope['scope_version'] === $latestNamedScopeVersion
			&& (string) $namedScope['updated_by'] !== (string) $userId;
	}
	unset($namedScope);
	return array(
		'named_reporting_scope_teams' => $availableScopeTeams,
		'named_reporting_scopes' => $namedScopes,
		'named_reporting_scope_history' => spRows($db,
			"SELECT scope_id, scope_key, scope_version, action_type, previous_label, new_label, previous_team_ids_text, new_team_ids_text,
				previous_include_unassigned, new_include_unassigned, previous_validation_status, new_validation_status, change_note, changed_by,
				DATE_FORMAT(changed_at,'%Y-%m-%d %H:%i') AS changed_at
			 FROM sales_performance_scope_history WHERE scope_key='core_sales_teams' ORDER BY changed_at DESC, id DESC LIMIT 300", array()),
		'team_memberships' => spRows($db,
			"SELECT m.id, m.salesmancode, s.salesmanname, m.team_id, t.name AS team_name, DATE_FORMAT(m.valid_from,'%Y-%m-%d') AS valid_from,
				DATE_FORMAT(m.valid_to,'%Y-%m-%d') AS valid_to, m.reporting_group, m.provenance, m.recorded_by,
				DATE_FORMAT(m.recorded_at,'%Y-%m-%d %H:%i') AS recorded_at, m.supersedes_id
			 FROM sales_performance_team_membership m LEFT JOIN salesman s ON s.salesmancode=m.salesmancode
			 LEFT JOIN salesteam t ON t.id=m.team_id ORDER BY m.valid_from DESC, m.id DESC LIMIT 300", array()),
		'team_membership_history' => spRows($db,
			"SELECT h.membership_id, h.action_type, h.salesmancode, s.salesmanname, h.team_id, t.name AS team_name,
				DATE_FORMAT(h.valid_from,'%Y-%m-%d') AS valid_from, DATE_FORMAT(h.previous_valid_to,'%Y-%m-%d') AS previous_valid_to,
				DATE_FORMAT(h.new_valid_to,'%Y-%m-%d') AS new_valid_to, h.provenance, h.changed_by,
				DATE_FORMAT(h.changed_at,'%Y-%m-%d %H:%i') AS changed_at
			 FROM sales_performance_team_membership_history h LEFT JOIN salesman s ON s.salesmancode=h.salesmancode
			 LEFT JOIN salesteam t ON t.id=h.team_id ORDER BY h.changed_at DESC, h.id DESC LIMIT 500", array()),
		'calendar_version' => $calendarVersion,
		'calendar_overrides' => spRows($db,
			"SELECT id, calendar_version, DATE_FORMAT(business_date,'%Y-%m-%d') AS business_date, planned_workday, override_reason, recorded_by,
				DATE_FORMAT(recorded_at,'%Y-%m-%d %H:%i') AS recorded_at FROM sales_performance_calendar_days WHERE calendar_version=?
			 ORDER BY business_date DESC LIMIT 500", array($calendarVersion)),
		'calendar_history' => spRows($db,
			"SELECT calendar_day_id, calendar_version, DATE_FORMAT(business_date,'%Y-%m-%d') AS business_date, previous_workday, new_workday,
				previous_reason, new_reason, changed_by, DATE_FORMAT(changed_at,'%Y-%m-%d %H:%i') AS changed_at
			 FROM sales_performance_calendar_day_history ORDER BY changed_at DESC, id DESC LIMIT 500", array()),
		'reference_observations' => spRows($db,
			"SELECT id, source_name, source_sha256, source_version, evidence_location, observation_key, DATE_FORMAT(period_from,'%Y-%m-%d') AS period_from,
				DATE_FORMAT(period_through,'%Y-%m-%d') AS period_through, granularity, scope_key, team_name, salesperson_name, metric_key, observed_count, observed_value,
				value_basis, value_currency, denominator, verification_state, verification_note, notes FROM sales_performance_reference_observations ORDER BY imported_at DESC, id DESC LIMIT 200", array()),
		'reference_observation_history' => spRows($db,
			"SELECT observation_id, previous_state, new_state, review_note, changed_by, DATE_FORMAT(changed_at,'%Y-%m-%d %H:%i') AS changed_at
			 FROM sales_performance_reference_observation_history ORDER BY changed_at DESC, id DESC LIMIT 500", array()),
		'reconciliations' => spRows($db,
			"SELECT r.id, r.run_id, run.run_key, r.reference_observation_id, ref.source_name, ref.observation_key, ref.scope_key AS reference_scope_key,
				ref.team_name, ref.salesperson_name, r.metric_key, r.scope_key,
				DATE_FORMAT(r.period_from,'%Y-%m-%d') AS period_from, DATE_FORMAT(r.period_through,'%Y-%m-%d') AS period_through,
				r.erp_count, r.erp_value, r.reference_count, r.reference_value, r.difference_count, r.difference_value,
				r.classification, r.source_basis, r.supporting_documents, r.owner_user_id, r.resolution_note, r.status,
				DATE_FORMAT(r.created_at,'%Y-%m-%d %H:%i') AS created_at, r.created_by
			 FROM sales_performance_reconciliation r LEFT JOIN sales_performance_report_runs run ON run.id=r.run_id
			 LEFT JOIN sales_performance_reference_observations ref ON ref.id=r.reference_observation_id
			 ORDER BY r.created_at DESC, r.id DESC LIMIT 100", array()),
		'reconciliation_history' => spRows($db,
			"SELECT reconciliation_id, action_type, classification, status, owner_user_id, resolution_note, changed_by,
				DATE_FORMAT(changed_at,'%Y-%m-%d %H:%i') AS changed_at FROM sales_performance_reconciliation_history ORDER BY changed_at DESC, id DESC LIMIT 500", array()),
		'coverage_submissions' => spRows($db,
			"SELECT c.id, c.scope_key, c.team_id, t.name AS team_name, DATE_FORMAT(c.coverage_date,'%Y-%m-%d') AS coverage_date,
				c.coverage_state, c.reporter_user_id, DATE_FORMAT(c.submitted_at,'%Y-%m-%d %H:%i') AS submitted_at, c.evidence_reference, c.note, c.review_snapshot_json
			 FROM sales_performance_coverage c LEFT JOIN salesteam t ON t.id=c.team_id
			 WHERE c.metric_key='report_submission' ORDER BY c.coverage_date DESC, c.id DESC LIMIT 200", array()),
		'coverage_history' => spRows($db,
			"SELECT coverage_id, action_type, previous_state, new_state, previous_note, new_note,
				previous_evidence_reference, new_evidence_reference, previous_reporter_user_id, new_reporter_user_id, changed_by,
				DATE_FORMAT(previous_business_cutoff,'%Y-%m-%d %H:%i:%s') AS previous_business_cutoff, DATE_FORMAT(new_business_cutoff,'%Y-%m-%d %H:%i:%s') AS new_business_cutoff,
				DATE_FORMAT(previous_submitted_at,'%Y-%m-%d %H:%i:%s') AS previous_submitted_at, DATE_FORMAT(new_submitted_at,'%Y-%m-%d %H:%i:%s') AS new_submitted_at,
				previous_review_snapshot_json, new_review_snapshot_json,
				DATE_FORMAT(changed_at,'%Y-%m-%d %H:%i') AS changed_at FROM sales_performance_coverage_history ORDER BY changed_at DESC, id DESC LIMIT 200", array())
	);
}

function spValidBusinessAt($date, $time) {
	return spValidDate($date) && preg_match('/^(?:[01]\\d|2[0-3]):[0-5]\\d$/', $time) && ($date . ' ' . $time . ':00') <= date('Y-m-d H:i:s');
}

function spFollowupRequestMatches(array $row, array $expected) {
	foreach ($expected as $key => $value) {
		if (!array_key_exists($key, $row)) return false;
		if ($value === null) {
			if ($row[$key] !== null) return false;
		} elseif ((string) $row[$key] !== (string) $value) return false;
	}
	return true;
}

function spPoReceiptRequestMatches(array $row, array $expected) {
	foreach ($expected as $key => $value) {
		if (!array_key_exists($key, $row)) return false;
		if ($value === null) {
			if ($row[$key] !== null) return false;
		} elseif ($key === 'stated_amount' || $key === 'net_amount') {
			if ($row[$key] === null || \SAHamid\BI\Commercial\DecimalAmount::compare((string) $row[$key], (string) $value) !== 0) return false;
		} elseif ((string) $row[$key] !== (string) $value) return false;
	}
	return true;
}

function spSchemaReadiness($db) {
	$tables = array(
		'sales_performance_followups', 'sales_performance_adjustments', 'sales_performance_settings',
		'sales_performance_definitions', 'sales_performance_scopes', 'sales_performance_scope_grants',
		'sales_performance_enquiry_events', 'sales_performance_quotation_issues', 'sales_performance_po_receipts',
		'sales_performance_po_bridges', 'sales_performance_team_membership', 'sales_performance_calendar_days',
		'sales_performance_coverage', 'sales_performance_normalization_locks', 'sales_performance_reference_observations',
		'sales_performance_reconciliation', 'sales_performance_reconciliation_history', 'sales_performance_reference_observation_history', 'sales_performance_report_runs', 'sales_performance_report_run_facts'
	);
	$tables[] = 'sales_performance_team_membership_history';
	$tables[] = 'sales_performance_calendar_day_history';
	$tables[] = 'sales_performance_coverage_history';
	$tables[] = 'sales_performance_scope_history';
	$missingTables = array();
	$stmt = mysqli_prepare($db, 'SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1');
	if (!$stmt) throw new \SAHamid\BI\Exception\BIException('schema_not_ready', 'The reporting schema could not be checked.', 503);
	foreach ($tables as $table) {
		mysqli_stmt_bind_param($stmt, 's', $table);
		if (!mysqli_stmt_execute($stmt)) {
			mysqli_stmt_close($stmt);
			throw new \SAHamid\BI\Exception\BIException('schema_not_ready', 'The reporting schema could not be checked.', 503);
		}
		$result = mysqli_stmt_get_result($stmt);
		if (!$result || mysqli_num_rows($result) === 0) $missingTables[] = $table;
		if ($result) mysqli_free_result($result);
	}
	mysqli_stmt_close($stmt);
	$requiredColumns = array(
		'sales_performance_adjustments' => array('adjustment_type', 'count_effect', 'policy_version', 'reversal_of_id'),
		'sales_performance_followups' => array('corrected_from_id', 'voided_at', 'voided_by', 'void_reason'),
		'sales_performance_enquiry_events' => array('correction_reason', 'void_reason'),
		'sales_performance_po_receipts' => array('correction_reason', 'voided_at', 'voided_by', 'void_reason', 'void_idempotency_key'),
		'sales_performance_reference_observations' => array('value_currency', 'verification_note'),
		'sales_performance_reference_observation_history' => array('observation_id', 'previous_state', 'new_state', 'review_note', 'changed_by', 'changed_at'),
		'sales_performance_team_membership_history' => array('membership_id', 'action_type', 'salesmancode', 'team_id', 'valid_from', 'previous_valid_to', 'new_valid_to', 'changed_by'),
		'sales_performance_calendar_day_history' => array('calendar_day_id', 'calendar_version', 'business_date', 'previous_workday', 'new_workday', 'new_reason', 'changed_by'),
		'sales_performance_reconciliation_history' => array('reconciliation_id', 'action_type', 'classification', 'status', 'owner_user_id', 'resolution_note', 'changed_by', 'changed_at'),
		'sales_performance_coverage' => array('review_snapshot_json'),
		'sales_performance_coverage_history' => array('coverage_id', 'action_type', 'previous_state', 'new_state', 'previous_note', 'new_note', 'previous_evidence_reference', 'new_evidence_reference', 'previous_reporter_user_id', 'new_reporter_user_id', 'previous_business_cutoff', 'new_business_cutoff', 'previous_submitted_at', 'new_submitted_at', 'previous_review_snapshot_json', 'new_review_snapshot_json', 'changed_by', 'changed_at'),
		'sales_performance_scope_history' => array('scope_id', 'scope_key', 'scope_version', 'action_type', 'previous_label', 'new_label', 'previous_team_ids_text', 'new_team_ids_text', 'previous_include_unassigned', 'new_include_unassigned', 'previous_validation_status', 'new_validation_status', 'change_note', 'changed_by', 'changed_at')
	);
	$missingColumns = array();
	foreach ($requiredColumns as $table => $columns) {
		foreach ($columns as $column) {
			$rows = spRows($db, 'SELECT 1 AS present FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1', array($table, $column));
			if (count($rows) === 0) $missingColumns[] = $table . '.' . $column;
		}
	}
	$requiredIndexes = array('sales_performance_followups' => array('idx_sp_followup_correction'),
		'sales_performance_enquiry_events' => array('idx_sp_enquiry_correction', 'idx_sp_enquiry_void'),
		'sales_performance_po_receipts' => array('idx_sp_po_receipt_correction', 'uq_sp_po_receipt_void_idempotency'),
		'sales_performance_team_membership_history' => array('idx_sp_team_history_item'),
		'sales_performance_calendar_day_history' => array('idx_sp_calendar_history_item'),
		'sales_performance_reference_observation_history' => array('idx_sp_reference_history_item'),
		'sales_performance_reconciliation_history' => array('idx_sp_recon_history_item'),
		'sales_performance_coverage_history' => array('idx_sp_coverage_history_item'),
		'sales_performance_scope_history' => array('idx_sp_scope_history_item'));
	$missingIndexes = array();
	foreach ($requiredIndexes as $table => $indexes) {
		foreach ($indexes as $index) {
			$rows = spRows($db, 'SELECT 1 AS present FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1', array($table, $index));
			if (count($rows) === 0) $missingIndexes[] = $table . '.' . $index;
		}
	}
	$missingSettings = array();
	if (in_array('sales_performance_settings', $missingTables, true)) {
		$missingSettings[] = 'report_calendar_version';
	} else {
		$calendarVersionSetting = spRows($db, "SELECT setting_value FROM sales_performance_settings WHERE setting_key='report_calendar_version' LIMIT 1", array());
		if (count($calendarVersionSetting) === 0 || trim((string) $calendarVersionSetting[0]['setting_value']) === '') $missingSettings[] = 'report_calendar_version';
	}
	$reportReady = count($missingTables) === 0 && count($missingColumns) === 0 && count($missingIndexes) === 0 && count($missingSettings) === 0;
	return array(
		'report_ready' => $reportReady,
		'full_capture_foundation_ready' => $reportReady,
		'missing_tables' => $missingTables,
		'missing_columns' => $missingColumns,
		'missing_indexes' => $missingIndexes,
		'missing_settings' => $missingSettings,
		'next_step' => $reportReady ? null : 'Apply and verify migrations/sales_performance_reporting.sql and migrations/sales_performance_reporting_v2.sql through migrations/sales_performance_reporting_v9.sql in the target development database before enabling the report.'
	);
}

function spRequestList($key) {
	if (!array_key_exists($key, $_GET)) return null;
	$values = is_array($_GET[$key]) ? $_GET[$key] : array($_GET[$key]);
	$out = array();
	foreach ($values as $value) {
		if (!is_string($value) || trim($value) === '') throw new InvalidArgumentException('A reporting filter is invalid.');
		$out[] = trim($value);
	}
	return array_values(array_unique($out));
}

if (!userHasPermission($db, 'salesperson_daily_activity_report')) {
	spRespond(403, array('status' => 'error', 'message' => 'You do not have permission to view this report.'));
}

$httpMethod = isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
$action = $httpMethod === 'POST' && isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'report');
$userId = isset($_SESSION['UserID']) ? $_SESSION['UserID'] : '';
$userName = isset($_SESSION['UsersRealName']) ? $_SESSION['UsersRealName'] : $userId;
$canManageAdjustments = userHasPermission($db, 'sales_performance_adjustments');
$canApproveAdjustments = userHasPermission($db, 'sales_performance_adjustments_approve');
$canViewCompany = userHasPermission($db, 'sales_performance_view_all');
$canViewTeams = userHasPermission($db, 'sales_performance_view_teams');
$canViewSelf = userHasPermission($db, 'sales_performance_view_self');
$canCaptureEvents = userHasPermission($db, 'sales_performance_capture_events');
$canAdminSetup = userHasPermission($db, 'sales_performance_admin') && $canViewCompany;
$canApproveNamedScope = userHasPermission($db, 'sales_performance_scopes_approve') && $canAdminSetup;
$canCreateRun = userHasPermission($db, 'sales_performance_create_run');
// Activity is recorded by the salesperson who owns the case, whatever their wider view scope.
// Managers and team leads still read and correct through their own scope; they never capture for others.
$captureScopeCompany = false;
$captureScopeTeams = false;
$captureScopeSelf = true;

try {
	if ($httpMethod === 'GET' && in_array($action, array('add_followup', 'void_followup', 'request_adjustment', 'request_adjustment_reversal', 'review_adjustment', 'capture_enquiry_event', 'correct_enquiry_event', 'void_enquiry_event', 'record_quote_issue', 'capture_po_receipt', 'correct_po_receipt', 'void_po_receipt', 'submit_coverage', 'save_team_membership', 'save_named_scope', 'approve_named_scope', 'save_calendar_override', 'preview_reference_observations', 'import_reference_observations', 'update_reference_verification', 'create_reconciliation', 'update_reconciliation', 'create_run'), true)) {
		spRespond(405, array('status' => 'error', 'code' => 'method_not_allowed', 'message' => 'Submit this reporting action through the secured form.'));
	}
	if ($action === 'readiness') {
		spRespond(200, array('status' => 'success', 'readiness' => spSchemaReadiness($db)));
	}
	if ($action === 'definitions') {
		$service = new \SAHamid\BI\Commercial\ReportingService();
		spRespond(200, array('status' => 'success', 'validation_status' => 'provisional', 'definitions' => $service->getDefinitions()));
	}
	$schemaActions = array('add_followup', 'void_followup', 'request_adjustment', 'request_adjustment_reversal', 'review_adjustment', 'capture_enquiry_event', 'correct_enquiry_event', 'void_enquiry_event', 'record_quote_issue', 'capture_po_receipt', 'correct_po_receipt', 'void_po_receipt', 'submit_coverage', 'save_team_membership', 'save_named_scope', 'approve_named_scope', 'save_calendar_override', 'preview_reference_observations', 'import_reference_observations', 'update_reference_verification', 'create_reconciliation', 'update_reconciliation', 'create_run', 'get_run', 'export_run', 'supporting_documents', 'report');
	if (in_array($action, $schemaActions, true)) {
		$actionReadiness = spSchemaReadiness($db);
		if (!$actionReadiness['report_ready']) spRespond(503, array('status' => 'error', 'code' => 'schema_not_ready', 'message' => 'Sales Performance reporting setup is incomplete.', 'readiness' => $actionReadiness));
	}
	if ($action === 'my_activity') {
		// Self-service feed for the salesperson's own activity page. Scope is the signed-in salesperson's own cases and entries, whatever their view scope.
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to record sales activity.'));
		$myCases = spRows($db,
			"SELECT sc.salescaseref, sc.debtorno, sc.branchcode, sc.salescasedescription, dm.name AS customer_name, cb.brname AS branch_name
			 FROM salescase sc
			 LEFT JOIN debtorsmaster dm ON dm.debtorno = sc.debtorno
			 LEFT JOIN custbranch cb ON cb.debtorno = sc.debtorno AND cb.branchcode = sc.branchcode
			 WHERE sc.salesman = ?
			 ORDER BY sc.commencementdate DESC, sc.salescaseref DESC LIMIT 500",
			array($userName));
		$myQuotations = spRows($db,
			"SELECT so.orderno, so.salescaseref, DATE_FORMAT(so.orddate,'%Y-%m-%d') AS orddate
			 FROM salesorders so
			 INNER JOIN salescase sc ON sc.salescaseref = so.salescaseref
			 WHERE sc.salesman = ? AND so.quotation = 1 AND so.orddate >= DATE_SUB(CURDATE(), INTERVAL 180 DAY)
			 ORDER BY so.orderno DESC LIMIT 300",
			array($userName));
		$myFollowups = spRows($db,
			"SELECT f.id, DATE_FORMAT(COALESCE(f.actual_activity_at, CONCAT(f.activity_date, ' 00:00:00')), '%Y-%m-%d %H:%i') AS activity_at,
				f.salescaseref, f.quotation_orderno, f.activity_type, f.outcome, f.next_action,
				DATE_FORMAT(f.activity_date, '%Y-%m-%d') AS activity_date,
				DATE_FORMAT(f.next_action_date, '%Y-%m-%d') AS next_action_date, f.notes,
				f.entered_by_user_id, f.created_by,
				DATE_FORMAT(f.voided_at, '%Y-%m-%d %H:%i') AS voided_at, f.void_reason,
				EXISTS (SELECT 1 FROM sales_performance_followups c WHERE c.corrected_from_id = f.id) AS has_correction
			 FROM sales_performance_followups f
			 WHERE f.salesman = ? AND f.activity_date >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
			 ORDER BY f.actual_activity_at DESC, f.id DESC LIMIT 200",
			array($userName));
		foreach ($myFollowups as &$myFollowup) {
			$enteredBy = (string) ($myFollowup['entered_by_user_id'] !== null ? $myFollowup['entered_by_user_id'] : $myFollowup['created_by']);
			$myFollowup['can_edit'] = $myFollowup['voided_at'] === null && !(bool) $myFollowup['has_correction'] && $enteredBy === (string) $userId;
			$myFollowup['has_correction'] = (bool) $myFollowup['has_correction'];
		}
		unset($myFollowup);
		$myEnquiryEvents = spRows($db,
			"SELECT e.id, e.salescaseref, e.event_type, DATE_FORMAT(e.business_at, '%Y-%m-%d %H:%i') AS business_at,
				e.response_code, e.reason_code, e.notes, e.evidence_reference,
				EXISTS (SELECT 1 FROM sales_performance_enquiry_events v WHERE v.voids_event_id = e.id AND v.event_type = 'void') AS is_voided
			 FROM sales_performance_enquiry_events e
			 INNER JOIN salescase sc ON sc.salescaseref = e.salescaseref
			 WHERE sc.salesman = ? AND e.event_type <> 'void' AND e.business_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
			 ORDER BY e.business_at DESC, e.id DESC LIMIT 200",
			array($userName));
		$myPoReceipts = spRows($db,
			"SELECT r.id, r.salescaseref, r.customer_reference_original AS customer_reference, DATE_FORMAT(r.received_at, '%Y-%m-%d %H:%i') AS received_at,
				r.stated_amount, r.currency, r.receipt_status, r.void_reason
			 FROM sales_performance_po_receipts r
			 INNER JOIN salescase sc ON sc.salescaseref = r.salescaseref
			 WHERE sc.salesman = ? AND r.received_at >= DATE_SUB(NOW(), INTERVAL 60 DAY)
			 ORDER BY r.received_at DESC, r.id DESC LIMIT 200",
			array($userName));
		spRespond(200, array('status' => 'success', 'salesperson' => $userName, 'cases' => $myCases, 'quotations' => $myQuotations,
			'followups' => $myFollowups, 'enquiry_events' => $myEnquiryEvents, 'po_receipts' => $myPoReceipts));
	}
	if ($action === 'lookup_cases' || $action === 'lookup_quotations') {
		// Type-ahead for the activity page. Results are limited to the signed-in salesperson's own cases (salesman match) and their quotations.
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to record sales activity.'));
		$term = isset($_GET['q']) && is_string($_GET['q']) ? trim(substr($_GET['q'], 0, 60)) : '';
		$like = '%' . addcslashes($term, '%_\\') . '%';
		$limit = isset($_GET['limit']) && ctype_digit((string) $_GET['limit']) ? min(50, max(1, (int) $_GET['limit'])) : 20;
		if ($action === 'lookup_cases') {
			$cases = spRows($db,
				"SELECT sc.salescaseref, sc.debtorno, sc.branchcode, sc.salescasedescription, dm.name AS customer_name, cb.brname AS branch_name
				 FROM salescase sc
				 LEFT JOIN debtorsmaster dm ON dm.debtorno = sc.debtorno
				 LEFT JOIN custbranch cb ON cb.debtorno = sc.debtorno AND cb.branchcode = sc.branchcode
				 WHERE sc.salesman = ? AND (sc.salescaseref LIKE ? OR dm.name LIKE ? OR sc.salescasedescription LIKE ?)
				 ORDER BY sc.commencementdate DESC, sc.salescaseref DESC LIMIT $limit",
				array($userName, $like, $like, $like));
			spRespond(200, array('status' => 'success', 'cases' => $cases));
		}
		$caseFilter = isset($_GET['case']) && is_string($_GET['case']) ? trim(substr($_GET['case'], 0, 40)) : '';
		$quoteWhere = 'sc.salesman = ? AND so.quotation = 1';
		$quoteParams = array($userName);
		if ($caseFilter !== '') {
			$quoteWhere .= ' AND so.salescaseref = ?';
			$quoteParams[] = $caseFilter;
		}
		if ($term !== '') {
			$quoteWhere .= ' AND (CAST(so.orderno AS CHAR) LIKE ? OR so.salescaseref LIKE ? OR dm.name LIKE ?)';
			array_push($quoteParams, $like, $like, $like);
		}
		$quotations = spRows($db,
			"SELECT so.orderno, so.salescaseref, DATE_FORMAT(so.orddate, '%Y-%m-%d') AS orddate, dm.name AS customer_name
			 FROM salesorders so
			 INNER JOIN salescase sc ON sc.salescaseref = so.salescaseref
			 LEFT JOIN debtorsmaster dm ON dm.debtorno = sc.debtorno
			 WHERE $quoteWhere
			 ORDER BY so.orderno DESC LIMIT $limit",
			$quoteParams);
		spRespond(200, array('status' => 'success', 'quotations' => $quotations));
	}
	if ($action === 'add_followup') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to capture reporting activity.'));
		$activityDate = spPostText('activity_date', 10);
		$activityTime = spPostText('activity_time', 5);
		$caseRef = spPostText('salescaseref', 40);
		$quotationOrder = spPostText('quotation_orderno', 12);
		$type = spPostText('activity_type', 24);
		$outcome = spPostText('outcome', 255);
		$nextAction = spPostText('next_action', 500);
		$nextDate = spPostText('next_action_date', 10);
		$notes = spPostText('notes', 4000);
		$idempotencyKey = spPostText('idempotency_key', 80);
		$correctionIdText = spPostText('corrected_from_id', 12);
		$correctionReason = spPostText('correction_reason', 500);
		$correctionId = $correctionIdText === '' ? 0 : (ctype_digit($correctionIdText) ? (int) $correctionIdText : -1);
		$allowedTypes = array('call', 'visit', 'email', 'meeting', 'other');
		if (!spValidBusinessAt($activityDate, $activityTime)
			|| !in_array($type, $allowedTypes, true) || !spValidIdempotencyKey($idempotencyKey) || $correctionId < 0
			|| ($correctionId > 0 && trim($correctionReason) === '')) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_filter', 'message' => 'Enter a valid activity date, time, type and retry key.'));
		}
		if ($nextDate !== '' && !spValidDate($nextDate)) {
			spRespond(400, array('status' => 'error', 'message' => 'Enter a valid next action date.'));
		}
		$creditedName = $userName;
		if ($caseRef !== '') {
			$case = spCanAccessCase($db, $caseRef, $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf);
			if ($case === null) spRespond(400, array('status' => 'error', 'message' => 'That sales case could not be found.'));
			if ($case === false) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'That case is outside your authorized reporting scope.'));
			$creditedName = $case['salesman'];
		}
		$quotationOrderValue = null;
		if ($quotationOrder !== '') {
			if (!preg_match('/^\d+$/', $quotationOrder) || $caseRef === '') spRespond(400, array('status' => 'error', 'message' => 'Link the quotation to a sales case and enter a valid quotation number.'));
			$quoteRows = spRows($db, 'SELECT orderno FROM salesorders WHERE orderno = ? AND salescaseref = ? AND quotation = 1 AND orddate <= ? LIMIT 1', array($quotationOrder, $caseRef, $activityDate));
			if (count($quoteRows) === 0) spRespond(400, array('status' => 'error', 'message' => 'The quotation must belong to the selected case and precede the follow-up date.'));
			$quotationOrderValue = $quotationOrder;
		}
		$parent = null;
		$inCorrection = $correctionId > 0;
		if ($inCorrection) {
			if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the follow-up correction.');
			try {
				$parentRows = spRows($db, 'SELECT id, salesman, salescaseref, credited_salesman_code, actor_user_id, event_team_id, attribution_basis, entered_by_user_id, created_by, voided_at, voided_by, void_reason FROM sales_performance_followups WHERE id=? LIMIT 1 FOR UPDATE', array((string) $correctionId));
				if (!count($parentRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'message' => 'The follow-up to correct was not found.')); }
				$parent = $parentRows[0];
				if ($parent['voided_at'] !== null) {
					$retry = spRows($db, 'SELECT activity_date, actual_activity_at, salesman, salescaseref, quotation_orderno, activity_type, outcome, next_action_date, next_action, notes, credited_salesman_code, actor_user_id, entered_by_user_id, event_team_id, attribution_basis, corrected_from_id FROM sales_performance_followups WHERE corrected_from_id=? AND idempotency_key=? AND entered_by_user_id=? LIMIT 1', array((string) $correctionId, $idempotencyKey, $userId));
					$retryExpected = array(
						'activity_date' => $activityDate, 'actual_activity_at' => $activityDate . ' ' . $activityTime . ':00',
						'salesman' => $parent['salesman'], 'salescaseref' => $caseRef, 'quotation_orderno' => $quotationOrderValue,
						'activity_type' => $type, 'outcome' => $outcome, 'next_action_date' => $nextDate === '' ? null : $nextDate,
						'next_action' => $nextAction, 'notes' => $notes, 'credited_salesman_code' => $parent['credited_salesman_code'],
						'actor_user_id' => $parent['actor_user_id'], 'entered_by_user_id' => $userId, 'event_team_id' => $parent['event_team_id'],
						'attribution_basis' => 'preserved_from_corrected_followup', 'corrected_from_id' => (string) $correctionId
					);
					$wasSameCorrection = count($retry) && spFollowupRequestMatches($retry[0], $retryExpected)
						&& (string) $parent['voided_by'] === (string) $userId && (string) $parent['void_reason'] === $correctionReason;
					mysqli_rollback($db);
					if ($wasSameCorrection) spRespond(200, array('status' => 'success', 'message' => 'This follow-up correction was already saved.', 'duplicate' => true));
					spRespond(409, array('status' => 'error', 'code' => 'followup_already_changed', 'message' => 'That follow-up has already been voided or corrected. Refresh the report before making another change.'));
				}
				if ($parent['salescaseref'] !== $caseRef) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'followup_case_changed', 'message' => 'A correction must remain linked to the original sales case.')); }
				$enteredByParent = (string) ($parent['entered_by_user_id'] !== null ? $parent['entered_by_user_id'] : $parent['created_by']);
				if ($enteredByParent !== (string) $userId) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'Only the person who recorded this follow-up can edit it.')); }
				$children = spRows($db, 'SELECT id FROM sales_performance_followups WHERE corrected_from_id=? LIMIT 1 FOR UPDATE', array((string) $correctionId));
				if (count($children)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'followup_already_corrected', 'message' => 'A correction already exists for this follow-up. Correct the latest version instead.')); }
				if ($caseRef !== '') {
					$caseLock = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? LIMIT 1 FOR UPDATE', array($caseRef));
					if (!count($caseLock) || !spCanAccessCaseRow($db, $caseLock[0], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf)) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'The sales case is outside your authorized reporting scope.')); }
				}
			} catch (Exception $e) {
				mysqli_rollback($db);
				throw $e;
			}
			$creditedName = $parent['salesman'];
			$attribution = array('salesman_code' => $parent['credited_salesman_code'], 'team_id' => $parent['event_team_id'], 'basis' => 'preserved_from_corrected_followup');
			$actorUserId = $parent['actor_user_id'];
			$attributionBasis = 'preserved_from_corrected_followup';
		} else {
			$attribution = spEventAttribution($db, $creditedName, $activityDate);
			$actorUserId = $userId;
			$attributionBasis = 'case_owner@' . $attribution['basis'];
		}
		$expectedSavedFollowup = array(
			'activity_date' => $activityDate, 'actual_activity_at' => $activityDate . ' ' . $activityTime . ':00',
			'salesman' => $creditedName, 'salescaseref' => $caseRef, 'quotation_orderno' => $quotationOrderValue,
			'activity_type' => $type, 'outcome' => $outcome, 'next_action_date' => $nextDate === '' ? null : $nextDate,
			'next_action' => $nextAction, 'notes' => $notes, 'credited_salesman_code' => $attribution['salesman_code'],
			'actor_user_id' => $actorUserId, 'entered_by_user_id' => $userId, 'event_team_id' => $attribution['team_id'],
			'attribution_basis' => $attributionBasis, 'corrected_from_id' => $inCorrection ? (string) $correctionId : null
		);
		try {
			spExecute($db,
				"INSERT INTO sales_performance_followups
					(activity_date, salesman, salescaseref, activity_type, outcome, next_action_date, notes, created_by, created_at,
					 actual_activity_at, quotation_orderno, credited_salesman_code, actor_user_id, entered_by_user_id, event_team_id,
					 attribution_basis, next_action, idempotency_key, corrected_from_id)
				 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				 ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)",
				array($activityDate, $creditedName, $caseRef, $type, $outcome, ($nextDate === '' ? null : $nextDate), $notes, $userId,
					$activityDate . ' ' . $activityTime . ':00', $quotationOrderValue, $attribution['salesman_code'], $actorUserId, $userId, $attribution['team_id'],
					$attributionBasis, $nextAction, $idempotencyKey,
					$inCorrection ? (string) $correctionId : null)
			);
			$saved = spRows($db, 'SELECT activity_date, actual_activity_at, salesman, salescaseref, quotation_orderno, activity_type, outcome, next_action_date, next_action, notes, credited_salesman_code, actor_user_id, entered_by_user_id, event_team_id, attribution_basis, corrected_from_id FROM sales_performance_followups WHERE idempotency_key=? LIMIT 1' . ($inCorrection ? ' FOR UPDATE' : ''), array($idempotencyKey));
			if (!count($saved) || !spFollowupRequestMatches($saved[0], $expectedSavedFollowup)) {
				if ($inCorrection) mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another activity. Refresh the form and try again.'));
			}
			if ($inCorrection) {
				$voided = spExecute($db, 'UPDATE sales_performance_followups SET voided_at=NOW(), voided_by=?, void_reason=?, updated_at=NOW() WHERE id=? AND voided_at IS NULL', array($userId, $correctionReason, (string) $correctionId));
				if ($voided !== 1) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'followup_already_changed', 'message' => 'That follow-up changed while the correction was being saved. Refresh the report and try again.')); }
				if (!mysqli_commit($db)) throw new Exception('Could not finish the follow-up correction.');
			}
		} catch (Exception $e) {
			if ($inCorrection) mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => $inCorrection ? 'Follow-up corrected. The previous version remains in the audit history.' : 'Follow-up saved.'));
	}

	if ($action === 'void_followup') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to correct reporting activity.'));
		$idText = spPostText('record_id', 12);
		$reason = spPostText('reason', 500);
		if (!ctype_digit($idText) || (int) $idText < 1 || trim($reason) === '') spRespond(400, array('status' => 'error', 'code' => 'invalid_correction', 'message' => 'Choose a follow-up and enter a reason for voiding it.'));
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the follow-up void.');
		try {
			$targetRows = spRows($db, 'SELECT id, salescaseref, entered_by_user_id, created_by, voided_at, voided_by FROM sales_performance_followups WHERE id=? LIMIT 1 FOR UPDATE', array($idText));
			if (!count($targetRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'message' => 'That follow-up was not found.')); }
			$target = $targetRows[0];
			$enteredBy = (string) ($target['entered_by_user_id'] !== null ? $target['entered_by_user_id'] : $target['created_by']);
			// Only the person who recorded a follow-up may void it; managers see it in reports but do not change it.
			if ($enteredBy !== (string) $userId) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'Only the person who recorded this follow-up can void it.')); }
			if ($target['voided_at'] !== null) {
				mysqli_rollback($db);
				spRespond(200, array('status' => 'success', 'message' => 'That follow-up was already voided.', 'duplicate' => true));
			}
			$affected = spExecute($db, 'UPDATE sales_performance_followups SET voided_at=NOW(), voided_by=?, void_reason=?, updated_at=NOW() WHERE id=? AND voided_at IS NULL', array($userId, $reason, $idText));
			if ($affected !== 1) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'followup_already_changed', 'message' => 'That follow-up changed while the request was being saved. Refresh the report.')); }
			if (!mysqli_commit($db)) throw new Exception('Could not finish the follow-up void.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'Follow-up voided. Its audit record remains available.'));
	}

	if ($action === 'capture_enquiry_event') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to capture reporting events.'));
		$caseRef = spPostText('salescaseref', 40);
		$eventType = spPostText('event_type', 32);
		$eventDate = spPostText('event_date', 10);
		$eventTime = spPostText('event_time', 5);
		$reason = spPostText('reason_code', 40);
		$response = spPostText('response_code', 40);
		$notes = spPostText('notes', 2000);
		$evidence = spPostText('evidence_reference', 500);
		$key = spPostText('idempotency_key', 80);
		$allowed = array('received', 'response', 'company_decline', 'customer_rejection', 'cancellation', 'duplicate', 'success', 'reopen');
		if ($caseRef === '' || !in_array($eventType, $allowed, true) || !spValidBusinessAt($eventDate, $eventTime) || !spValidIdempotencyKey($key)) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_event', 'message' => 'Enter a case, valid event type, past or current date and time, and retry key.'));
		}
		if ($eventType === 'company_decline' && $reason === '') spRespond(400, array('status' => 'error', 'code' => 'reason_required', 'message' => 'A company decline requires a reason.'));
		if ($eventType === 'response' && $response === '') spRespond(400, array('status' => 'error', 'code' => 'response_type_required', 'message' => 'Choose the type of response that was made.'));
		if ($eventType !== 'response') $response = '';
		$case = spCanAccessCase($db, $caseRef, $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf);
		if ($case === null) spRespond(400, array('status' => 'error', 'message' => 'That sales case could not be found.'));
		if ($case === false) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'That case is outside your authorized reporting scope.'));
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the enquiry event.');
		try {
			$caseLock = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? FOR UPDATE', array($caseRef));
			$known = spRows($db, 'SELECT id, salescaseref, entered_by_user_id FROM sales_performance_enquiry_events WHERE idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (count($known)) {
				mysqli_rollback($db);
				if ($known[0]['salescaseref'] === $caseRef && $known[0]['entered_by_user_id'] === $userId) spRespond(200, array('status' => 'success', 'message' => 'This event was already recorded.', 'duplicate' => true));
				spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another reporting event. Refresh the form and try again.'));
			}
			$latestPredicate = spEffectiveEnquiryEventPredicate('e');
			$latest = spRows($db, 'SELECT e.episode_no, e.event_type, e.business_at FROM sales_performance_enquiry_events e WHERE e.salescaseref=? AND ' . $latestPredicate . ' ORDER BY e.episode_no DESC, e.business_at DESC, e.id DESC LIMIT 1 FOR UPDATE', array($caseRef));
			$episode = count($latest) ? (int) $latest[0]['episode_no'] : 1;
			$terminalTypes = array('company_decline', 'customer_rejection', 'cancellation', 'duplicate', 'success');
			$lastType = count($latest) ? $latest[0]['event_type'] : null;
			$businessAt = $eventDate . ' ' . $eventTime . ':00';
			$error = null;
			if (count($caseLock) === 0) $error = array(400, 'case_missing', 'That sales case could not be found.');
			elseif (!spCanAccessCaseRow($db, $caseLock[0], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf)) $error = array(403, 'forbidden', 'That case is outside your authorized reporting scope.');
			elseif (count($latest) && $businessAt < $latest[0]['business_at']) $error = array(409, 'event_order', 'Enter events in actual chronological order for this case.');
			elseif ($eventType === 'received' && count($latest)) $error = array(409, 'episode_exists', 'This case already has an enquiry episode. Record a reopen event before recording another receipt.');
			elseif ($eventType === 'reopen' && (!count($latest) || !in_array($lastType, $terminalTypes, true))) $error = array(409, 'reopen_not_allowed', 'Reopen an enquiry only after a recorded terminal outcome.');
			elseif ($eventType !== 'received' && $eventType !== 'reopen' && (!count($latest) || in_array($lastType, $terminalTypes, true))) $error = array(409, 'receipt_required', 'Record a receipt or reopen before adding a response or outcome.');
			elseif ($eventType === 'response' && !in_array($lastType, array('received', 'reopen'), true)) $error = array(409, 'response_already_recorded', 'This enquiry episode already has a response or outcome.');
			elseif (in_array($eventType, $terminalTypes, true) && !in_array($lastType, array('received', 'reopen', 'response'), true)) $error = array(409, 'outcome_not_allowed', 'Record a receipt or response before closing this enquiry episode.');
			if ($error !== null) {
				mysqli_rollback($db);
				spRespond($error[0], array('status' => 'error', 'code' => $error[1], 'message' => $error[2]));
			}
			$case = $caseLock[0];
			if ($eventType === 'reopen') $episode++;
			$attribution = spEventAttribution($db, $case['salesman'], $eventDate);
			spExecute($db,
				'INSERT INTO sales_performance_enquiry_events (salescaseref, episode_no, event_type, business_at, recorded_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, response_code, reason_code, notes, evidence_reference, idempotency_key) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)',
				array($caseRef, (string) $episode, $eventType, $businessAt, $userId, $userId, $attribution['salesman_code'], $attribution['team_id'], 'case_owner@' . $attribution['basis'], $response, $reason, $notes, $evidence, $key)
			);
			if (!mysqli_commit($db)) throw new Exception('Could not commit the enquiry event.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'Enquiry event saved.', 'episode_no' => $episode));
	}

	if ($action === 'correct_enquiry_event') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to correct enquiry events.'));
		$idText = spPostText('event_id', 20);
		$caseRef = spPostText('salescaseref', 40);
		$eventType = spPostText('event_type', 32);
		$eventDate = spPostText('event_date', 10);
		$eventTime = spPostText('event_time', 5);
		$reason = spPostText('reason_code', 40);
		$response = spPostText('response_code', 40);
		$notes = spPostText('notes', 2000);
		$evidence = spPostText('evidence_reference', 500);
		$correctionReason = spPostText('correction_reason', 500);
		$key = spPostText('idempotency_key', 80);
		$allowed = array('received', 'response', 'company_decline', 'customer_rejection', 'cancellation', 'duplicate', 'success', 'reopen');
		if (!ctype_digit($idText) || (int) $idText < 1 || $caseRef === '' || !in_array($eventType, $allowed, true)
			|| !spValidBusinessAt($eventDate, $eventTime) || !spValidIdempotencyKey($key) || trim($correctionReason) === '') {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_correction', 'message' => 'Enter the corrected event details, retry key and reason for the correction.'));
		}
		if ($eventType === 'company_decline' && $reason === '') spRespond(400, array('status' => 'error', 'code' => 'reason_required', 'message' => 'A company decline requires a reason.'));
		if ($eventType === 'response' && $response === '') spRespond(400, array('status' => 'error', 'code' => 'response_type_required', 'message' => 'Choose the type of response that was made.'));
		if ($eventType !== 'response') $response = '';
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the enquiry event correction.');
		try {
			$referenceRows = spRows($db, 'SELECT salescaseref FROM sales_performance_enquiry_events WHERE id=? LIMIT 1', array($idText));
			if (!count($referenceRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'event_not_found', 'message' => 'That enquiry event could not be found.')); }
			$caseRows = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? LIMIT 1 FOR UPDATE', array($referenceRows[0]['salescaseref']));
			if (!count($caseRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'case_missing', 'message' => 'The sales case for this event no longer exists.')); }
			$parentRows = spRows($db, 'SELECT * FROM sales_performance_enquiry_events WHERE id=? LIMIT 1 FOR UPDATE', array($idText));
			if (!count($parentRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'event_not_found', 'message' => 'That enquiry event could not be found.')); }
			$parent = $parentRows[0];
			if ((string) $parent['salescaseref'] !== $caseRef || (string) $parent['salescaseref'] !== (string) $referenceRows[0]['salescaseref'] || $parent['event_type'] === 'void') { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'event_link_mismatch', 'message' => 'A correction must stay linked to the selected enquiry event.')); }
			if (!spCanAccessCaseRow($db, $caseRows[0], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf)) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'The sales case is outside your authorized reporting scope.')); }
			$enteredByParent = (string) $parent['entered_by_user_id'];
			if ($enteredByParent !== (string) $userId && !$canViewCompany) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'Only the original reporter or a company reporting manager can correct this event.')); }
			$attribution = array('salesman_code' => $parent['credited_salesman_code'], 'team_id' => $parent['event_team_id']);
			$businessAt = $eventDate . ' ' . $eventTime . ':00';
			$expected = array('salescaseref' => $caseRef, 'episode_no' => (string) $parent['episode_no'], 'event_type' => $eventType,
				'business_at' => $businessAt, 'actor_user_id' => $parent['actor_user_id'], 'entered_by_user_id' => $userId,
				'credited_salesman_code' => $attribution['salesman_code'], 'event_team_id' => $attribution['team_id'],
				'response_code' => $response === '' ? null : $response, 'reason_code' => $reason === '' ? null : $reason,
				'notes' => $notes === '' ? null : $notes, 'evidence_reference' => $evidence === '' ? null : $evidence,
				'corrects_event_id' => (string) $parent['id'], 'correction_reason' => $correctionReason, 'idempotency_key' => $key);
			$retry = spRows($db, 'SELECT salescaseref, episode_no, event_type, business_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, response_code, reason_code, notes, evidence_reference, corrects_event_id, correction_reason, idempotency_key FROM sales_performance_enquiry_events WHERE idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (count($retry)) {
				$wasSameCorrection = spFollowupRequestMatches($retry[0], $expected);
				mysqli_rollback($db);
				if ($wasSameCorrection) spRespond(200, array('status' => 'success', 'message' => 'This enquiry correction was already saved.', 'duplicate' => true));
				spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another reporting event. Refresh the form and try again.'));
			}
			$voids = spRows($db, 'SELECT id FROM sales_performance_enquiry_events WHERE voids_event_id=? LIMIT 1 FOR UPDATE', array((string) $parent['id']));
			$corrections = spRows($db, 'SELECT child.id FROM sales_performance_enquiry_events child WHERE child.corrects_event_id=? AND child.voids_event_id IS NULL AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events child_void WHERE child_void.voids_event_id=child.id) LIMIT 1 FOR UPDATE', array((string) $parent['id']));
			if ($parent['voids_event_id'] !== null || count($voids) || count($corrections)) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'event_already_changed', 'message' => 'That event already has a correction or void audit entry. Refresh the report before changing it again.'));
			}
			$effective = spEffectiveEnquiryEventPredicate('e');
			$episodeRows = spRows($db, 'SELECT e.id, e.event_type, e.business_at FROM sales_performance_enquiry_events e WHERE e.salescaseref=? AND e.episode_no=? AND ' . $effective . ' AND e.id<>? ORDER BY e.business_at ASC, e.id ASC FOR UPDATE', array($caseRef, (string) $parent['episode_no'], (string) $parent['id']));
			$otherEpisodes = spRows($db, 'SELECT e.episode_no,e.business_at FROM sales_performance_enquiry_events e WHERE e.salescaseref=? AND e.episode_no<>? AND ' . $effective . ' FOR UPDATE', array($caseRef, (string) $parent['episode_no']));
			$crossEpisodeChronologyValid = true;
			foreach ($otherEpisodes as $otherEpisode) {
				if ((int) $otherEpisode['episode_no'] < (int) $parent['episode_no'] && (string) $otherEpisode['business_at'] > $businessAt) $crossEpisodeChronologyValid = false;
				if ((int) $otherEpisode['episode_no'] > (int) $parent['episode_no'] && (string) $otherEpisode['business_at'] < $businessAt) $crossEpisodeChronologyValid = false;
			}
			$sequence = $episodeRows;
			$sequence[] = array('id' => 'candidate', 'event_type' => $eventType, 'business_at' => $businessAt);
			usort($sequence, function ($left, $right) {
				$byTime = strcmp((string) $left['business_at'], (string) $right['business_at']);
				if ($byTime !== 0) return $byTime;
				if ($left['id'] === 'candidate') return 1;
				if ($right['id'] === 'candidate') return -1;
				return strcmp((string) $left['id'], (string) $right['id']);
			});
			if (!$crossEpisodeChronologyValid || !spEnquiryEventSequenceIsValid($sequence, $parent['episode_no'])) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'event_sequence_invalid', 'message' => 'Those corrected details would make the enquiry episode chronology invalid. Adjust the event date or type and try again.'));
			}
			$basis = (string) $parent['attribution_basis'];
			spExecute($db,
				'INSERT INTO sales_performance_enquiry_events (salescaseref, episode_no, event_type, business_at, recorded_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, response_code, reason_code, notes, evidence_reference, corrects_event_id, correction_reason, idempotency_key) VALUES (?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array($caseRef, (string) $parent['episode_no'], $eventType, $businessAt, $parent['actor_user_id'], $userId,
					$attribution['salesman_code'], $attribution['team_id'], $basis, $response === '' ? null : $response, $reason === '' ? null : $reason,
					$notes === '' ? null : $notes, $evidence === '' ? null : $evidence, (string) $parent['id'], $correctionReason, $key)
			);
			$saved = spRows($db, 'SELECT salescaseref, episode_no, event_type, business_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, response_code, reason_code, notes, evidence_reference, corrects_event_id, correction_reason, idempotency_key FROM sales_performance_enquiry_events WHERE idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (!count($saved) || !spFollowupRequestMatches($saved[0], $expected)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another reporting event. Refresh the form and try again.')); }
			if (!mysqli_commit($db)) throw new Exception('Could not commit the enquiry event correction.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'Enquiry event corrected. The previous version remains in the audit history.'));
	}

	if ($action === 'void_enquiry_event') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to void enquiry events.'));
		$idText = spPostText('event_id', 20);
		$reason = spPostText('void_reason', 500);
		$key = spPostText('idempotency_key', 80);
		if (!ctype_digit($idText) || (int) $idText < 1 || trim($reason) === '' || !spValidIdempotencyKey($key)) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_void', 'message' => 'Choose an enquiry event, enter a void reason and include a retry key.'));
		}
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the enquiry event void.');
		try {
			$referenceRows = spRows($db, 'SELECT salescaseref FROM sales_performance_enquiry_events WHERE id=? LIMIT 1', array($idText));
			if (!count($referenceRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'event_not_found', 'message' => 'That enquiry event could not be found.')); }
			$caseRows = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? LIMIT 1 FOR UPDATE', array($referenceRows[0]['salescaseref']));
			if (!count($caseRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'case_missing', 'message' => 'The sales case for this event no longer exists.')); }
			$parentRows = spRows($db, 'SELECT * FROM sales_performance_enquiry_events WHERE id=? LIMIT 1 FOR UPDATE', array($idText));
			if (!count($parentRows) || $parentRows[0]['event_type'] === 'void') { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'event_not_found', 'message' => 'That enquiry event could not be found.')); }
			$parent = $parentRows[0];
			if ((string) $parent['salescaseref'] !== (string) $referenceRows[0]['salescaseref']) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'event_link_mismatch', 'message' => 'That enquiry event changed while the void request was being saved.')); }
			if (!spCanAccessCaseRow($db, $caseRows[0], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf)) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'The sales case is outside your authorized reporting scope.')); }
			$expected = array('salescaseref' => $parent['salescaseref'], 'episode_no' => (string) $parent['episode_no'], 'event_type' => 'void',
				'business_at' => $parent['business_at'], 'actor_user_id' => $userId, 'entered_by_user_id' => $userId,
				'credited_salesman_code' => $parent['credited_salesman_code'], 'event_team_id' => $parent['event_team_id'],
				'voids_event_id' => (string) $parent['id'], 'void_reason' => $reason, 'idempotency_key' => $key);
			$retry = spRows($db, 'SELECT salescaseref, episode_no, event_type, business_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, voids_event_id, void_reason, idempotency_key FROM sales_performance_enquiry_events WHERE idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (count($retry)) {
				$wasSameVoid = spFollowupRequestMatches($retry[0], $expected);
				mysqli_rollback($db);
				if ($wasSameVoid) spRespond(200, array('status' => 'success', 'message' => 'This enquiry event was already voided.', 'duplicate' => true));
				spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another reporting event. Refresh the report and try again.'));
			}
			$voids = spRows($db, 'SELECT id FROM sales_performance_enquiry_events WHERE voids_event_id=? LIMIT 1 FOR UPDATE', array((string) $parent['id']));
			$corrections = spRows($db, 'SELECT child.id FROM sales_performance_enquiry_events child WHERE child.corrects_event_id=? AND child.voids_event_id IS NULL AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events child_void WHERE child_void.voids_event_id=child.id) LIMIT 1 FOR UPDATE', array((string) $parent['id']));
			if ($parent['voids_event_id'] !== null || count($voids) || count($corrections)) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'event_already_changed', 'message' => 'That event already has a correction or void audit entry. Refresh the report before changing it again.'));
			}
			$effective = spEffectiveEnquiryEventPredicate('later');
			$later = spRows($db, 'SELECT later.id FROM sales_performance_enquiry_events later WHERE later.salescaseref=? AND ' . $effective . ' AND (later.episode_no>? OR (later.episode_no=? AND (later.business_at>? OR (later.business_at=? AND later.id>?)))) LIMIT 1 FOR UPDATE',
				array($parent['salescaseref'], (string) $parent['episode_no'], (string) $parent['episode_no'], $parent['business_at'], $parent['business_at'], (string) $parent['id']));
			if (count($later)) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'event_has_later_activity', 'message' => 'Void later events in this enquiry history first, then retry this event.'));
			}
			spExecute($db,
				"INSERT INTO sales_performance_enquiry_events (salescaseref, episode_no, event_type, business_at, recorded_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, voids_event_id, void_reason, idempotency_key) VALUES (?, ?, 'void', ?, NOW(), ?, ?, ?, ?, 'enquiry_event_void', ?, ?, ?)",
				array($parent['salescaseref'], (string) $parent['episode_no'], $parent['business_at'], $userId, $userId,
					$parent['credited_salesman_code'], $parent['event_team_id'], (string) $parent['id'], $reason, $key)
			);
			$saved = spRows($db, 'SELECT salescaseref, episode_no, event_type, business_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, voids_event_id, void_reason, idempotency_key FROM sales_performance_enquiry_events WHERE idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (!count($saved) || !spFollowupRequestMatches($saved[0], $expected)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another reporting event. Refresh the report and try again.')); }
			if (!mysqli_commit($db)) throw new Exception('Could not commit the enquiry event void.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'Enquiry event voided. Its audit record remains available.'));
	}

	if ($action === 'record_quote_issue') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to capture quotation issue events.'));
		$orderNo = spPostText('orderno', 12);
		$issueKind = spPostText('issue_kind', 16);
		$issueDate = spPostText('issued_date', 10);
		$issueTime = spPostText('issued_time', 5);
		$evidence = spPostText('evidence_reference', 500);
		$key = spPostText('idempotency_key', 80);
		if (!ctype_digit($orderNo) || !in_array($issueKind, array('issued', 'revised', 'resent'), true) || !spValidBusinessAt($issueDate, $issueTime) || !spValidIdempotencyKey($key)) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_issue', 'message' => 'Choose a quotation, issue type and valid actual issue date and time.'));
		}
		$source = new \SAHamid\BI\Commercial\Source\QuotationSource();
		$snapshot = $source->snapshotDocument($db, $orderNo);
		if ($snapshot === null) spRespond(404, array('status' => 'error', 'code' => 'quote_not_found', 'message' => 'That quotation was not found in the reporting source.'));
		$fact = $snapshot['fact'];
		$expectedKind = $fact['is_revision'] ? 'revised' : 'issued';
		if ($issueKind !== 'resent' && $issueKind !== $expectedKind) spRespond(400, array('status' => 'error', 'code' => 'issue_kind_mismatch', 'message' => 'Choose the issue type that matches the quotation revision marker.'));
		if ($issueKind === 'resent' && $fact['is_revision']) $expectedKind = 'revised';
		$case = spCanAccessCase($db, $fact['salescaseref'], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf);
		if ($case === null) spRespond(400, array('status' => 'error', 'message' => 'The quotation case could not be found.'));
		if ($case === false) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'That quotation is outside your authorized reporting scope.'));
		$canonicalFamily = spQuotationFamilyKey($db, $orderNo, isset($fact['revision_for']) ? $fact['revision_for'] : '');
		$taxAmount = null;
		if ($fact['net_amount'] !== null && $fact['raw_amount'] !== null && strpos($fact['tax_basis'], 'inclusive') !== false) $taxAmount = \SAHamid\BI\Commercial\DecimalAmount::subtract($fact['raw_amount'], $fact['net_amount']);
		elseif ($fact['net_amount'] !== null && strpos($fact['tax_basis'], 'exclusive') !== false) $taxAmount = '0.00';
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the quotation issue snapshot.');
		try {
			spAcquireAdjustmentLock($db, 'quotation_issue', $orderNo);
			$quoteLock = spRows($db, 'SELECT orderno FROM salesorders WHERE orderno=? AND quotation=1 FOR UPDATE', array($orderNo));
			if (!count($quoteLock)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'quote_not_found', 'message' => 'That quotation was not found in the reporting source.')); }
			$lockedSnapshot = $source->snapshotDocument($db, $orderNo);
			if ($lockedSnapshot === null || $lockedSnapshot['source_hash'] !== $snapshot['source_hash']) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'changed_content', 'message' => 'The quotation changed while its issue snapshot was being prepared. Reload the document and try again.')); }
			$snapshot = $lockedSnapshot;
			$fact = $snapshot['fact'];
			$canonicalFamily = spQuotationFamilyKey($db, $orderNo, isset($fact['revision_for']) ? $fact['revision_for'] : '');
			$caseLock = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? FOR UPDATE', array($fact['salescaseref']));
			if (!count($caseLock)) { mysqli_rollback($db); spRespond(400, array('status' => 'error', 'message' => 'The quotation case could not be found.')); }
			if (!spCanAccessCaseRow($db, $caseLock[0], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf)) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'That quotation is outside your authorized reporting scope.')); }
			$fact['credited_salesperson'] = $caseLock[0]['salesman'];
			$idempotent = spRows($db, 'SELECT id, orderno, entered_by_user_id FROM sales_performance_quotation_issues WHERE idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (count($idempotent)) {
				mysqli_rollback($db);
				if ((string) $idempotent[0]['orderno'] === $orderNo && $idempotent[0]['entered_by_user_id'] === $userId) spRespond(200, array('status' => 'success', 'message' => 'This quotation issue was already recorded.', 'duplicate' => true));
				spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another quotation issue. Refresh and try again.'));
			}
			$sameHash = spRows($db, "SELECT id FROM sales_performance_quotation_issues WHERE orderno=? AND source_hash=? AND issue_kind<>'resent' LIMIT 1 FOR UPDATE", array($orderNo, $snapshot['source_hash']));
			if ($issueKind !== 'resent' && count($sameHash)) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'unchanged_resend', 'message' => 'This quotation version was already recorded. Record it as a resend if it was sent again unchanged.'));
			}
			if ($issueKind === 'resent') {
				$hasIssue = spRows($db, "SELECT id, source_hash FROM sales_performance_quotation_issues WHERE orderno=? AND issue_kind<>'resent' ORDER BY issue_sequence DESC LIMIT 1 FOR UPDATE", array($orderNo));
				if (!count($hasIssue)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'issue_required', 'message' => 'Record the first issue before recording a resend.')); }
				if ($hasIssue[0]['source_hash'] !== $snapshot['source_hash']) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'changed_content', 'message' => 'This quotation changed since it was last issued. Record the changed version as a revision, not a resend.')); }
			}
			$sequenceRows = spRows($db, 'SELECT issue_sequence FROM sales_performance_quotation_issues WHERE orderno=? ORDER BY issue_sequence DESC LIMIT 1 FOR UPDATE', array($orderNo));
			$sequence = count($sequenceRows) ? (int) $sequenceRows[0]['issue_sequence'] + 1 : 1;
			$priorRows = spRows($db, "SELECT id FROM sales_performance_quotation_issues WHERE canonical_family_key=? AND issue_kind<>'resent' ORDER BY issued_at DESC, id DESC LIMIT 1 FOR UPDATE", array($canonicalFamily));
			$attribution = spEventAttribution($db, $fact['credited_salesperson'], $issueDate);
			spExecute($db,
				'INSERT INTO sales_performance_quotation_issues (orderno, issue_sequence, source_hash, salescaseref, debtorno, branchcode, canonical_family_key, preceding_issue_id, issue_kind, issued_at, recorded_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, line_snapshot, raw_amount, net_amount, tax_amount, currency, tax_basis, valuation_policy_version, evidence_reference, idempotency_key) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
				array($orderNo, (string) $sequence, $snapshot['source_hash'], $fact['salescaseref'], $fact['customer'], $fact['branch'], $canonicalFamily,
					count($priorRows) ? $priorRows[0]['id'] : null, $issueKind, $issueDate . ' ' . $issueTime . ':00', $userId, $userId, $attribution['salesman_code'], $attribution['team_id'],
					'case_owner@' . $attribution['basis'], $snapshot['line_snapshot'], $fact['raw_amount'], $fact['net_amount'], $taxAmount,
					$fact['currency'], $fact['tax_basis'], $fact['tax_rule_version'], $evidence, $key)
			);
			spExecute($db, "UPDATE sales_performance_normalization_locks SET revision_no=revision_no+1, updated_at=NOW() WHERE source_type='quotation_issue' AND source_key=? AND policy_version='normalized-value-v1'", array($orderNo));
			if (!mysqli_commit($db)) throw new Exception('Could not commit the quotation issue snapshot.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => $issueKind === 'resent' ? 'Quotation resend recorded.' : 'Quotation issue snapshot recorded.', 'source_hash' => $snapshot['source_hash']));
	}

	if ($action === 'capture_po_receipt') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to capture reporting events.'));
		$caseRef = spPostText('salescaseref', 40);
		$reference = spPostText('customer_reference', 150);
		$receivedDate = spPostText('received_date', 10);
		$receivedTime = spPostText('received_time', 5);
		$amountInput = spPostText('stated_amount', 40);
		$currency = strtoupper(spPostText('currency', 3));
		$taxBasis = spPostText('tax_basis', 20);
		$quotationOrder = spPostText('quotation_orderno', 12);
		$ocOrder = spPostText('oc_orderno', 12);
		$ocPo = spPostText('oc_pono', 12);
		$evidence = spPostText('evidence_reference', 500);
		$key = spPostText('idempotency_key', 80);
		if ($caseRef === '' || trim($reference) === '' || !spValidBusinessAt($receivedDate, $receivedTime) || !spValidIdempotencyKey($key)
			|| !in_array($taxBasis, array('inclusive', 'exclusive', 'unknown'), true) || ($currency !== '' && !preg_match('/^[A-Z]{3}$/', $currency))) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_receipt', 'message' => 'Enter a case, customer PO reference, actual receipt date and time, currency, tax basis and retry key.'));
		}
		$case = spCanAccessCase($db, $caseRef, $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf);
		if ($case === null) spRespond(400, array('status' => 'error', 'message' => 'That sales case could not be found.'));
		if ($case === false) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'That case is outside your authorized reporting scope.'));
		$amount = null;
		if ($amountInput !== '') {
			try { $amount = \SAHamid\BI\Commercial\DecimalAmount::normalize($amountInput); }
			catch (InvalidArgumentException $e) { spRespond(400, array('status' => 'error', 'code' => 'invalid_amount', 'message' => 'Enter a valid non-negative amount.')); }
			if (\SAHamid\BI\Commercial\DecimalAmount::compare($amount, '0.00') < 0) spRespond(400, array('status' => 'error', 'code' => 'invalid_amount', 'message' => 'Enter a valid non-negative amount.'));
		}
		$normalizedRef = strtoupper(preg_replace('/\\s+/', ' ', trim($reference)));
		if ($quotationOrder !== '' && (!ctype_digit($quotationOrder) || !count(spRows($db, 'SELECT orderno FROM salesorders WHERE orderno=? AND salescaseref=? AND quotation=1 LIMIT 1', array($quotationOrder, $caseRef))))) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_bridge', 'message' => 'The linked quotation must belong to this sales case.'));
		}
		$hasOcPair = $ocOrder !== '' && $ocPo !== '';
		$validOcPair = false;
		if ($hasOcPair && ctype_digit($ocOrder)) {
			$validOcPair = count(spRows($db, 'SELECT orderno FROM ocs WHERE orderno=? AND pono=? AND salescaseref=? LIMIT 1', array($ocOrder, $ocPo, $caseRef))) > 0;
		}
		if (($ocOrder === '') !== ($ocPo === '') || ($hasOcPair && !$validOcPair)) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_bridge', 'message' => 'The linked order confirmation must belong to this sales case and include its PO number.'));
		}
		$netAmount = ($amount !== null && $currency === 'PKR' && $taxBasis === 'exclusive') ? $amount : null;
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the customer PO receipt.');
		try {
			$caseLock = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? FOR UPDATE', array($caseRef));
			if (!count($caseLock)) { mysqli_rollback($db); spRespond(400, array('status' => 'error', 'message' => 'That sales case could not be found.')); }
			if (!spCanAccessCaseRow($db, $caseLock[0], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf)) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'That case is outside your authorized reporting scope.')); }
			$case = $caseLock[0];
			$receiptLockKey = hash('sha256', $case['debtorno'] . '|' . $case['branchcode'] . '|' . $normalizedRef);
			spAcquireAdjustmentLock($db, 'po_receipt', $receiptLockKey);
			$idempotent = spRows($db, 'SELECT id, debtorno, branchcode, customer_reference_normalized, entered_by_user_id FROM sales_performance_po_receipts WHERE idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (count($idempotent)) {
				mysqli_rollback($db);
				if ($idempotent[0]['debtorno'] === $case['debtorno'] && $idempotent[0]['branchcode'] === $case['branchcode'] && $idempotent[0]['customer_reference_normalized'] === $normalizedRef && $idempotent[0]['entered_by_user_id'] === $userId) spRespond(200, array('status' => 'success', 'message' => 'This customer PO receipt was already recorded.', 'duplicate' => true));
				spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another reporting receipt. Refresh the form and try again.'));
			}
			$duplicate = spRows($db, "SELECT id FROM sales_performance_po_receipts WHERE debtorno=? AND branchcode=? AND customer_reference_normalized=? AND receipt_status='active' LIMIT 1 FOR UPDATE", array($case['debtorno'], $case['branchcode'], $normalizedRef));
			if (count($duplicate)) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'receipt_exists', 'message' => 'This customer PO is already recorded for the customer and branch. Use its existing receipt record for additional evidence.'));
			}
			$attribution = spEventAttribution($db, $case['salesman'], $receivedDate);
			spExecute($db,
				"INSERT INTO sales_performance_po_receipts (debtorno, branchcode, customer_reference_original, customer_reference_normalized, received_at, recorded_at, salescaseref, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, stated_amount, net_amount, currency, tax_basis, evidence_reference, receipt_status, idempotency_key) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)",
				array($case['debtorno'], $case['branchcode'], $reference, $normalizedRef, $receivedDate . ' ' . $receivedTime . ':00', $caseRef, $userId, $attribution['salesman_code'], $attribution['team_id'], 'case_owner@' . $attribution['basis'], $amount, $netAmount, ($currency === '' ? null : $currency), $taxBasis, $evidence, $key)
			);
			$receiptId = mysqli_insert_id($db);
			if ($quotationOrder !== '') spExecute($db, "INSERT INTO sales_performance_po_bridges (receipt_id, source_type, source_key, created_by, created_at) VALUES (?, 'quotation', ?, ?, NOW())", array((string) $receiptId, $quotationOrder, $userId));
			if ($ocOrder !== '') spExecute($db, "INSERT INTO sales_performance_po_bridges (receipt_id, source_type, source_key, created_by, created_at) VALUES (?, 'order_confirmation', ?, ?, NOW())", array((string) $receiptId, $ocOrder . '|' . $ocPo, $userId));
			spExecute($db, "UPDATE sales_performance_normalization_locks SET revision_no=revision_no+1, updated_at=NOW() WHERE source_type='po_receipt' AND source_key=? AND policy_version='normalized-value-v1'", array($receiptLockKey));
			if (!mysqli_commit($db)) throw new Exception('Could not save the customer PO receipt.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'Customer PO receipt saved. Values without an approved tax basis remain unavailable.'));
	}

	if ($action === 'correct_po_receipt') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to correct customer PO receipts.'));
		$idText = spPostText('receipt_id', 20);
		$caseRef = spPostText('salescaseref', 40);
		$reference = spPostText('customer_reference', 150);
		$receivedDate = spPostText('received_date', 10);
		$receivedTime = spPostText('received_time', 5);
		$amountInput = spPostText('stated_amount', 40);
		$currency = strtoupper(spPostText('currency', 3));
		$taxBasis = spPostText('tax_basis', 20);
		$evidence = spPostText('evidence_reference', 500);
		$correctionReason = spPostText('correction_reason', 500);
		$key = spPostText('idempotency_key', 80);
		if (!ctype_digit($idText) || (int) $idText < 1 || $caseRef === '' || trim($reference) === '' || !spValidBusinessAt($receivedDate, $receivedTime)
			|| !spValidIdempotencyKey($key) || trim($correctionReason) === '' || !in_array($taxBasis, array('inclusive', 'exclusive', 'unknown'), true)
			|| ($currency !== '' && !preg_match('/^[A-Z]{3}$/', $currency))) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_correction', 'message' => 'Enter the corrected receipt details, retry key and reason for the correction.'));
		}
		$amount = null;
		if ($amountInput !== '') {
			try { $amount = \SAHamid\BI\Commercial\DecimalAmount::normalize($amountInput); }
			catch (InvalidArgumentException $e) { spRespond(400, array('status' => 'error', 'code' => 'invalid_amount', 'message' => 'Enter a valid non-negative amount.')); }
			if (\SAHamid\BI\Commercial\DecimalAmount::compare($amount, '0.00') < 0) spRespond(400, array('status' => 'error', 'code' => 'invalid_amount', 'message' => 'Enter a valid non-negative amount.'));
		}
		$normalizedRef = strtoupper(preg_replace('/\s+/', ' ', trim($reference)));
		$netAmount = ($amount !== null && $currency === 'PKR' && $taxBasis === 'exclusive') ? $amount : null;
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the customer PO receipt correction.');
		try {
			$caseLock = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? FOR UPDATE', array($caseRef));
			if (!count($caseLock)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'case_missing', 'message' => 'That sales case could not be found.')); }
			if (!spCanAccessCaseRow($db, $caseLock[0], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf)) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'That case is outside your authorized reporting scope.')); }
			$parentRows = spRows($db, 'SELECT * FROM sales_performance_po_receipts WHERE id=? LIMIT 1 FOR UPDATE', array($idText));
			if (!count($parentRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'receipt_not_found', 'message' => 'That customer PO receipt could not be found.')); }
			$parent = $parentRows[0];
			if ((string) $parent['salescaseref'] !== $caseRef) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_case_mismatch', 'message' => 'A correction must remain linked to the original sales case.')); }
			if ((string) $parent['entered_by_user_id'] !== (string) $userId && !$canViewCompany) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'Only the original reporter or a company reporting manager can correct this receipt.')); }
			$oldLockKey = hash('sha256', $parent['debtorno'] . '|' . $parent['branchcode'] . '|' . $parent['customer_reference_normalized']);
			$newLockKey = hash('sha256', $parent['debtorno'] . '|' . $parent['branchcode'] . '|' . $normalizedRef);
			$lockKeys = array_values(array_unique(array($oldLockKey, $newLockKey)));
			sort($lockKeys, SORT_STRING);
			foreach ($lockKeys as $lockKey) spAcquireAdjustmentLock($db, 'po_receipt', $lockKey);
			$expected = array('debtorno' => $parent['debtorno'], 'branchcode' => $parent['branchcode'],
				'customer_reference_original' => $reference, 'customer_reference_normalized' => $normalizedRef,
				'received_at' => $receivedDate . ' ' . $receivedTime . ':00', 'salescaseref' => $caseRef,
				'entered_by_user_id' => $userId, 'credited_salesman_code' => $parent['credited_salesman_code'], 'event_team_id' => $parent['event_team_id'],
				'attribution_basis' => $parent['attribution_basis'], 'stated_amount' => $amount, 'net_amount' => $netAmount,
				'currency' => $currency === '' ? null : $currency, 'tax_basis' => $taxBasis, 'evidence_reference' => $evidence === '' ? null : $evidence,
				'correction_of_id' => (string) $parent['id'], 'correction_reason' => $correctionReason, 'idempotency_key' => $key);
			$retry = spRows($db, 'SELECT debtorno, branchcode, customer_reference_original, customer_reference_normalized, received_at, salescaseref, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, stated_amount, net_amount, currency, tax_basis, evidence_reference, correction_of_id, correction_reason, idempotency_key FROM sales_performance_po_receipts WHERE idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (count($retry)) {
				$wasSameCorrection = spPoReceiptRequestMatches($retry[0], $expected);
				mysqli_rollback($db);
				if ($wasSameCorrection) spRespond(200, array('status' => 'success', 'message' => 'This customer PO correction was already saved.', 'duplicate' => true));
				spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another reporting receipt. Refresh the form and try again.'));
			}
			if ($parent['receipt_status'] !== 'active' || $parent['voided_at'] !== null) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_already_changed', 'message' => 'That receipt has already been corrected or voided. Refresh the report before changing it again.')); }
			$children = spRows($db, 'SELECT id FROM sales_performance_po_receipts WHERE correction_of_id=? LIMIT 1 FOR UPDATE', array((string) $parent['id']));
			if (count($children)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_already_corrected', 'message' => 'A correction already exists for this receipt. Correct the current active version.')); }
			$duplicate = spRows($db, "SELECT id FROM sales_performance_po_receipts WHERE debtorno=? AND branchcode=? AND customer_reference_normalized=? AND receipt_status='active' AND id<>? LIMIT 1 FOR UPDATE", array($parent['debtorno'], $parent['branchcode'], $normalizedRef, (string) $parent['id']));
			if (count($duplicate)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_exists', 'message' => 'This customer PO is already recorded for the customer and branch. Use its existing receipt record for additional evidence.')); }
			spExecute($db,
				'INSERT INTO sales_performance_po_receipts (debtorno, branchcode, customer_reference_original, customer_reference_normalized, received_at, recorded_at, salescaseref, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, stated_amount, net_amount, currency, tax_basis, evidence_reference, receipt_status, idempotency_key, correction_of_id, correction_reason) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, \'active\', ?, ?, ?)',
				array($parent['debtorno'], $parent['branchcode'], $reference, $normalizedRef, $receivedDate . ' ' . $receivedTime . ':00', $caseRef,
					$userId, $parent['credited_salesman_code'], $parent['event_team_id'], $parent['attribution_basis'], $amount, $netAmount,
					$currency === '' ? null : $currency, $taxBasis, $evidence === '' ? null : $evidence, $key, (string) $parent['id'], $correctionReason)
			);
			$receiptId = mysqli_insert_id($db);
			spExecute($db, 'INSERT INTO sales_performance_po_bridges (receipt_id, source_type, source_key, created_by, created_at) SELECT ?, source_type, source_key, ?, NOW() FROM sales_performance_po_bridges WHERE receipt_id=?', array((string) $receiptId, $userId, (string) $parent['id']));
			$changed = spExecute($db, "UPDATE sales_performance_po_receipts SET receipt_status='corrected', voided_at=NOW(), voided_by=?, void_reason=? WHERE id=? AND receipt_status='active' AND voided_at IS NULL", array($userId, $correctionReason, (string) $parent['id']));
			if ($changed !== 1) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_already_changed', 'message' => 'That receipt changed while the correction was being saved. Refresh the report and try again.')); }
			foreach ($lockKeys as $lockKey) spExecute($db, "UPDATE sales_performance_normalization_locks SET revision_no=revision_no+1, updated_at=NOW() WHERE source_type='po_receipt' AND source_key=? AND policy_version='normalized-value-v1'", array($lockKey));
			if (!mysqli_commit($db)) throw new Exception('Could not finish the customer PO receipt correction.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'Customer PO receipt corrected. The prior receipt and its linked ERP evidence remain in the audit history.'));
	}

	if ($action === 'void_po_receipt') {
		spRequireFormToken();
		if (!$canCaptureEvents) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to void customer PO receipts.'));
		$idText = spPostText('receipt_id', 20);
		$reason = spPostText('void_reason', 500);
		$key = spPostText('idempotency_key', 80);
		if (!ctype_digit($idText) || (int) $idText < 1 || trim($reason) === '' || !spValidIdempotencyKey($key)) spRespond(400, array('status' => 'error', 'code' => 'invalid_void', 'message' => 'Choose a receipt, enter a void reason and include a retry key.'));
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the customer PO receipt void.');
		try {
			$referenceRows = spRows($db, 'SELECT salescaseref FROM sales_performance_po_receipts WHERE id=? LIMIT 1', array($idText));
			if (!count($referenceRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'receipt_not_found', 'message' => 'That customer PO receipt could not be found.')); }
			$caseRows = spRows($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? LIMIT 1 FOR UPDATE', array($referenceRows[0]['salescaseref']));
			if (!count($caseRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'case_missing', 'message' => 'The sales case for this receipt no longer exists.')); }
			if (!spCanAccessCaseRow($db, $caseRows[0], $userName, $userId, $captureScopeCompany, $captureScopeTeams, $captureScopeSelf)) { mysqli_rollback($db); spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'That case is outside your authorized reporting scope.')); }
			$receiptRows = spRows($db, 'SELECT * FROM sales_performance_po_receipts WHERE id=? LIMIT 1 FOR UPDATE', array($idText));
			if (!count($receiptRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'receipt_not_found', 'message' => 'That customer PO receipt could not be found.')); }
			$receipt = $receiptRows[0];
			if ((string) $receipt['salescaseref'] !== (string) $referenceRows[0]['salescaseref']) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_case_mismatch', 'message' => 'That receipt changed while the void request was being saved.')); }
			$retry = spRows($db, 'SELECT id, void_reason, voided_by FROM sales_performance_po_receipts WHERE void_idempotency_key=? LIMIT 1 FOR UPDATE', array($key));
			if (count($retry)) {
				$wasSameVoid = (string) $retry[0]['id'] === (string) $receipt['id'] && (string) $retry[0]['voided_by'] === (string) $userId && (string) $retry[0]['void_reason'] === $reason;
				mysqli_rollback($db);
				if ($wasSameVoid) spRespond(200, array('status' => 'success', 'message' => 'This customer PO receipt was already voided.', 'duplicate' => true));
				spRespond(409, array('status' => 'error', 'code' => 'idempotency_conflict', 'message' => 'That retry key belongs to another reporting receipt. Refresh the report and try again.'));
			}
			if ($receipt['receipt_status'] !== 'active' || $receipt['voided_at'] !== null) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_already_changed', 'message' => 'That receipt has already been corrected or voided. Refresh the report before changing it again.')); }
			$children = spRows($db, 'SELECT id FROM sales_performance_po_receipts WHERE correction_of_id=? LIMIT 1 FOR UPDATE', array((string) $receipt['id']));
			if (count($children)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_already_corrected', 'message' => 'That receipt has already been replaced by a correction. Void or correct the current active version.')); }
			$lockKey = hash('sha256', $receipt['debtorno'] . '|' . $receipt['branchcode'] . '|' . $receipt['customer_reference_normalized']);
			spAcquireAdjustmentLock($db, 'po_receipt', $lockKey);
			$changed = spExecute($db, "UPDATE sales_performance_po_receipts SET receipt_status='void', voided_at=NOW(), voided_by=?, void_reason=?, void_idempotency_key=? WHERE id=? AND receipt_status='active' AND voided_at IS NULL", array($userId, $reason, $key, (string) $receipt['id']));
			if ($changed !== 1) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'receipt_already_changed', 'message' => 'That receipt changed while the void was being saved. Refresh the report and try again.')); }
			spExecute($db, "UPDATE sales_performance_normalization_locks SET revision_no=revision_no+1, updated_at=NOW() WHERE source_type='po_receipt' AND source_key=? AND policy_version='normalized-value-v1'", array($lockKey));
			if (!mysqli_commit($db)) throw new Exception('Could not finish the customer PO receipt void.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'Customer PO receipt voided. Its audit record and linked ERP evidence remain available.'));
	}

	if ($action === 'save_named_scope') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to propose a named reporting scope.'));
		$scopeKey = 'core_sales_teams';
		$label = spPostText('scope_label', 120);
		$changeNote = spPostText('scope_change_note', 1000);
		$includeUnassignedText = isset($_POST['include_unassigned']) && is_string($_POST['include_unassigned']) ? $_POST['include_unassigned'] : '0';
		$rawTeamIds = isset($_POST['team_ids']) && is_array($_POST['team_ids']) ? $_POST['team_ids'] : array();
		if ($label === '' || trim($changeNote) === '' || !in_array($includeUnassignedText, array('0', '1'), true)
			|| count($rawTeamIds) < 1 || count($rawTeamIds) > 100) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_named_scope', 'message' => 'Enter a scope label and evidence note, choose at least one team, and select whether to include unassigned activity.'));
		}
		$teamIds = array();
		foreach ($rawTeamIds as $rawTeamId) {
			if (!is_string($rawTeamId) || !ctype_digit($rawTeamId) || (int) $rawTeamId < 1) spRespond(400, array('status' => 'error', 'code' => 'invalid_named_scope', 'message' => 'The selected team list contains an invalid team.'));
			$teamId = (int) $rawTeamId;
			if (in_array($teamId, $teamIds, true)) spRespond(400, array('status' => 'error', 'code' => 'invalid_named_scope', 'message' => 'The selected team list contains a duplicate.'));
			$teamIds[] = $teamId;
		}
		sort($teamIds, SORT_NUMERIC);
		$teamParams = array_map('strval', $teamIds);
		$existingTeams = spRows($db, 'SELECT id FROM salesteam WHERE id IN (' . implode(',', array_fill(0, count($teamIds), '?')) . ')', $teamParams);
		if (count($existingTeams) !== count($teamIds)) spRespond(409, array('status' => 'error', 'code' => 'named_scope_team_missing', 'message' => 'A selected ERP team no longer exists. Refresh the setup page and choose from the current team list.'));
		$teamIdsText = implode(',', $teamIds);
		$includeUnassigned = (int) $includeUnassignedText;
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the named scope proposal.');
		try {
			$currentRows = spRows($db, "SELECT id, label, scope_version, team_ids_text, include_unassigned, validation_status, updated_by FROM sales_performance_scopes WHERE scope_key=? ORDER BY scope_version DESC LIMIT 1 FOR UPDATE", array($scopeKey));
			if (!count($currentRows)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'named_scope_seed_missing', 'message' => 'The named commercial scope seed is missing. Apply the base reporting migrations before configuring it.')); }
			$current = $currentRows[0];
			if ((string) $current['label'] === $label && (string) $current['team_ids_text'] === $teamIdsText && (int) $current['include_unassigned'] === $includeUnassigned) {
				mysqli_rollback($db);
				spRespond(200, array('status' => 'success', 'duplicate' => true, 'message' => 'That team population is already recorded as the latest scope version.', 'scope_version' => (int) $current['scope_version'], 'validation_status' => $current['validation_status']));
			}
			$nextVersion = (int) $current['scope_version'] + 1;
			if ($current['validation_status'] === 'pending_review') {
				$superseded = spExecute($db, "UPDATE sales_performance_scopes SET validation_status='superseded', updated_at=NOW() WHERE id=? AND validation_status='pending_review'", array((string) $current['id']));
				if ($superseded !== 1) throw new Exception('The pending named scope changed before the replacement was saved.');
				spExecute($db, "INSERT INTO sales_performance_scope_history (scope_id, scope_key, scope_version, action_type, previous_label, new_label, previous_team_ids_text, new_team_ids_text, previous_include_unassigned, new_include_unassigned, previous_validation_status, new_validation_status, change_note, changed_by, changed_at) VALUES (?, ?, ?, 'superseded', ?, ?, ?, ?, ?, ?, 'pending_review', 'superseded', ?, ?, NOW())",
					array((string) $current['id'], $scopeKey, (string) $current['scope_version'], $current['label'], $current['label'], $current['team_ids_text'], $current['team_ids_text'],
						(string) $current['include_unassigned'], (string) $current['include_unassigned'], $changeNote, $userId));
				$current['validation_status'] = 'superseded';
			}
			spExecute($db, "INSERT INTO sales_performance_scopes (scope_key, label, scope_version, team_ids_text, include_unassigned, validation_status, effective_from, effective_to, updated_by, updated_at) VALUES (?, ?, ?, ?, ?, 'pending_review', NULL, NULL, ?, NOW())",
				array($scopeKey, $label, (string) $nextVersion, $teamIdsText, (string) $includeUnassigned, $userId));
			$scopeId = (string) mysqli_insert_id($db);
			spExecute($db, "INSERT INTO sales_performance_scope_history (scope_id, scope_key, scope_version, action_type, previous_label, new_label, previous_team_ids_text, new_team_ids_text, previous_include_unassigned, new_include_unassigned, previous_validation_status, new_validation_status, change_note, changed_by, changed_at) VALUES (?, ?, ?, 'proposed', ?, ?, ?, ?, ?, ?, ?, 'pending_review', ?, ?, NOW())",
				array($scopeId, $scopeKey, (string) $nextVersion, $current['label'], $label, $current['team_ids_text'], $teamIdsText,
					(string) $current['include_unassigned'], (string) $includeUnassigned, $current['validation_status'], $changeNote, $userId));
			if (!mysqli_commit($db)) throw new Exception('Could not save the named scope proposal.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'Named team population saved as a pending review version. This proposal does not approve business definitions or publish a report.', 'scope_version' => $nextVersion, 'validation_status' => 'pending_review'));
	}

	if ($action === 'approve_named_scope') {
		spRequireFormToken();
		if (!$canApproveNamedScope) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A separate company-scope named-scope approval permission is required.'));
		$scopeVersionText = spPostText('scope_version', 12);
		$reviewNote = spPostText('scope_review_note', 1000);
		if (!ctype_digit($scopeVersionText) || (int) $scopeVersionText < 1 || trim($reviewNote) === '') spRespond(400, array('status' => 'error', 'code' => 'invalid_named_scope_review', 'message' => 'Choose a scope version and record the mapping review.'));
		$scopeKey = 'core_sales_teams';
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the named scope review.');
		try {
			$latestRows = spRows($db, 'SELECT id, label, scope_version, team_ids_text, include_unassigned, validation_status, updated_by FROM sales_performance_scopes WHERE scope_key=? ORDER BY scope_version DESC LIMIT 1 FOR UPDATE', array($scopeKey));
			if (!count($latestRows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'named_scope_missing', 'message' => 'The named reporting scope was not found.')); }
			$scope = $latestRows[0];
			if ((int) $scope['scope_version'] !== (int) $scopeVersionText || $scope['validation_status'] !== 'pending_review') {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'named_scope_changed', 'message' => 'That scope version is no longer the latest pending proposal. Refresh the setup page.'));
			}
			if ((string) $scope['updated_by'] === (string) $userId) {
				mysqli_rollback($db);
				spRespond(403, array('status' => 'error', 'code' => 'named_scope_self_review', 'message' => 'The person who proposed a team population cannot approve that version.'));
			}
			spExecute($db, "INSERT INTO sales_performance_scope_history (scope_id, scope_key, scope_version, action_type, previous_label, new_label, previous_team_ids_text, new_team_ids_text, previous_include_unassigned, new_include_unassigned, previous_validation_status, new_validation_status, change_note, changed_by, changed_at) VALUES (?, ?, ?, 'approved', ?, ?, ?, ?, ?, ?, 'pending_review', 'approved', ?, ?, NOW())",
				array((string) $scope['id'], $scopeKey, (string) $scope['scope_version'], $scope['label'], $scope['label'], $scope['team_ids_text'], $scope['team_ids_text'],
					(string) $scope['include_unassigned'], (string) $scope['include_unassigned'], $reviewNote, $userId));
			$approvedRows = spExecute($db, "UPDATE sales_performance_scopes SET validation_status='approved', updated_at=NOW() WHERE id=? AND validation_status='pending_review'", array((string) $scope['id']));
			if ($approvedRows !== 1) throw new Exception('The named scope proposal changed before approval could be recorded.');
			if (!mysqli_commit($db)) throw new Exception('Could not approve the named scope mapping.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'The team population mapping was approved and audited. Business metric definitions remain separately governed.', 'scope_version' => (int) $scopeVersionText, 'validation_status' => 'approved'));
	}

	if ($action === 'save_team_membership') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to manage team membership.'));
		$salesmanCode = spPostText('salesmancode', 20);
		$teamText = spPostText('team_id', 12);
		$validFrom = spPostText('valid_from', 10);
		$provenance = spPostText('provenance', 120);
		if ($salesmanCode === '' || !ctype_digit($teamText) || !spValidDate($validFrom) || $validFrom < date('Y-m-d') || $provenance === '') {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_team_membership', 'message' => 'Choose a salesperson, team, today or a future effective date, and an evidence or approval reference.'));
		}
		$personRows = spRows($db, 'SELECT salesmancode, salesmanname FROM salesman WHERE salesmancode=? LIMIT 1', array($salesmanCode));
		$teamRows = spRows($db, 'SELECT id, name FROM salesteam WHERE id=? LIMIT 1', array($teamText));
		if (!count($personRows) || !count($teamRows)) spRespond(404, array('status' => 'error', 'code' => 'membership_target_missing', 'message' => 'The selected salesperson or team no longer exists.'));
		$salesmanName = (string) $personRows[0]['salesmanname'];
		$legacyTeamRows = spRows($db, 'SELECT id, members FROM salesteam ORDER BY id', array());
		$legacyTeamIds = array();
		foreach ($legacyTeamRows as $legacyTeam) {
			if (in_array($salesmanName, array_map('trim', explode(',', (string) $legacyTeam['members'])), true)) $legacyTeamIds[] = (int) $legacyTeam['id'];
		}
		$teamId = (int) $teamText;
		if (count($legacyTeamIds) !== 1 || $legacyTeamIds[0] !== $teamId) {
			spRespond(409, array('status' => 'error', 'code' => 'legacy_team_conflict', 'message' => 'This screen can only record an evidence-backed effective start date for a salesperson already listed in exactly one matching ERP team. Unassigned memberships and team transfers are unavailable while team-view access is not date-scoped.'));
		}
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the team membership change.');
		try {
			$lock = spRows($db, "SELECT setting_value FROM sales_performance_settings WHERE setting_key='report_timezone' LIMIT 1 FOR UPDATE", array());
			if (!count($lock)) throw new Exception('Could not secure the team membership change.');
			$historyRows = spRows($db, 'SELECT id, team_id, valid_from, valid_to, reporting_group, provenance FROM sales_performance_team_membership WHERE salesmancode=? ORDER BY valid_from, id FOR UPDATE', array($salesmanCode));
			$future = array();
			$overlapping = false;
			foreach ($historyRows as $membership) {
				if ((int) $membership['team_id'] !== $teamId) {
					mysqli_rollback($db);
					spRespond(409, array('status' => 'error', 'code' => 'cross_team_change_requires_review', 'message' => 'This screen cannot move a salesperson between teams until team-view authorization is date-scoped. Preserve this assignment for a reviewed migration instead.'));
				}
				if ($membership['valid_from'] >= $validFrom) $future[] = $membership;
				if ($membership['valid_from'] <= $validFrom && ($membership['valid_to'] === null || $membership['valid_to'] > $validFrom)) $overlapping = true;
			}
			if (count($future) > 0 || $overlapping) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'membership_interval_conflict', 'message' => 'An existing or future membership already conflicts with that start date. Review the effective-date history first.'));
			}
			spExecute($db, "INSERT INTO sales_performance_team_membership (salesmancode, team_id, valid_from, valid_to, reporting_group, provenance, recorded_by, recorded_at, supersedes_id) VALUES (?, ?, ?, NULL, 'commercial_sales', ?, ?, NOW(), NULL)",
				array($salesmanCode, (string) $teamId, $validFrom, $provenance, $userId));
			$membershipId = (string) mysqli_insert_id($db);
			spExecute($db, "INSERT INTO sales_performance_team_membership_history (membership_id, action_type, salesmancode, team_id, valid_from, previous_valid_to, new_valid_to, reporting_group, provenance, changed_by, changed_at) VALUES (?, 'created', ?, ?, ?, NULL, NULL, 'commercial_sales', ?, ?, NOW())",
				array($membershipId, $salesmanCode, (string) $teamId, $validFrom, $provenance, $userId));
			if (!mysqli_commit($db)) throw new Exception('Could not finish the team membership change.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'Effective team membership saved with its prior interval and audit entry retained.'));
	}

	if ($action === 'save_calendar_override') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to manage calendar overrides.'));
		$businessDate = spPostText('business_date', 10);
		$workdayText = spPostText('planned_workday', 1);
		$reason = spPostText('override_reason', 255);
		if (!spValidDate($businessDate) || !in_array($workdayText, array('0', '1'), true) || trim($reason) === '') {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_calendar_override', 'message' => 'Choose a date, workday state and reason or evidence note.'));
		}
		$plannedWorkday = (int) $workdayText;
		if ($plannedWorkday === 1 && (int) date('N', strtotime($businessDate)) === 7) {
			spRespond(400, array('status' => 'error', 'code' => 'sunday_workday_unavailable', 'message' => 'Sunday workdays are not supported by the current six-day weekday view.'));
		}
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the calendar override.');
		try {
			$settings = spRows($db, "SELECT setting_key, setting_value FROM sales_performance_settings WHERE setting_key IN ('report_timezone','report_calendar_version') FOR UPDATE", array());
			$calendarVersion = null;
			foreach ($settings as $setting) if ($setting['setting_key'] === 'report_calendar_version') $calendarVersion = (string) $setting['setting_value'];
			if ($calendarVersion === null) throw new Exception('Could not secure the calendar version. Apply the v6 setup migration first.');
			$existing = spRows($db, 'SELECT id, planned_workday, override_reason FROM sales_performance_calendar_days WHERE calendar_version=? AND business_date=? LIMIT 1 FOR UPDATE', array($calendarVersion, $businessDate));
			if (count($existing) && (int) $existing[0]['planned_workday'] === $plannedWorkday && (string) $existing[0]['override_reason'] === $reason) {
				mysqli_rollback($db);
				spRespond(200, array('status' => 'success', 'message' => 'That calendar override is already saved.', 'duplicate' => true));
			}
			$nextCalendarVersion = 'weekday-' . date('YmdHis') . '-' . bin2hex(random_bytes(3));
			spExecute($db, 'INSERT INTO sales_performance_calendar_days (calendar_version, business_date, planned_workday, override_reason, recorded_by, recorded_at) SELECT ?, business_date, planned_workday, override_reason, recorded_by, recorded_at FROM sales_performance_calendar_days WHERE calendar_version=?', array($nextCalendarVersion, $calendarVersion));
			$nextExisting = spRows($db, 'SELECT id, planned_workday, override_reason FROM sales_performance_calendar_days WHERE calendar_version=? AND business_date=? LIMIT 1 FOR UPDATE', array($nextCalendarVersion, $businessDate));
			if (count($nextExisting)) {
				$dayId = (string) $nextExisting[0]['id'];
				spExecute($db, 'INSERT INTO sales_performance_calendar_day_history (calendar_day_id, calendar_version, business_date, previous_workday, new_workday, previous_reason, new_reason, changed_by, changed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
					array($dayId, $nextCalendarVersion, $businessDate, (string) $nextExisting[0]['planned_workday'], (string) $plannedWorkday, $nextExisting[0]['override_reason'], $reason, $userId));
				spExecute($db, 'UPDATE sales_performance_calendar_days SET planned_workday=?, override_reason=?, recorded_by=?, recorded_at=NOW() WHERE id=?', array((string) $plannedWorkday, $reason, $userId, $dayId));
			} else {
				spExecute($db, 'INSERT INTO sales_performance_calendar_days (calendar_version, business_date, planned_workday, override_reason, recorded_by, recorded_at) VALUES (?, ?, ?, ?, ?, NOW())', array($nextCalendarVersion, $businessDate, (string) $plannedWorkday, $reason, $userId));
				$dayId = (string) mysqli_insert_id($db);
				$previousReason = count($existing) ? $existing[0]['override_reason'] : null;
				$previousWorkday = count($existing) ? (string) $existing[0]['planned_workday'] : null;
				spExecute($db, 'INSERT INTO sales_performance_calendar_day_history (calendar_day_id, calendar_version, business_date, previous_workday, new_workday, previous_reason, new_reason, changed_by, changed_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
					array($dayId, $nextCalendarVersion, $businessDate, $previousWorkday, (string) $plannedWorkday, $previousReason, $reason, $userId));
			}
			spExecute($db, "UPDATE sales_performance_settings SET setting_value=? WHERE setting_key='report_calendar_version'", array($nextCalendarVersion));
			if (!mysqli_commit($db)) throw new Exception('Could not finish the calendar override.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'Calendar override saved as a new version. Existing frozen reports retain their original calendar version.', 'calendar_version' => $nextCalendarVersion));
	}

	if ($action === 'preview_reference_observations') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to preview reference observations.'));
		$sourceName = isset($_POST['source_name']) && is_string($_POST['source_name']) ? trim(stripslashes($_POST['source_name'])) : '';
		$sourceVersion = isset($_POST['source_version']) && is_string($_POST['source_version']) ? trim(stripslashes($_POST['source_version'])) : '';
		$evidenceLocation = isset($_POST['evidence_location']) && is_string($_POST['evidence_location']) ? trim(stripslashes($_POST['evidence_location'])) : '';
		$csv = isset($_POST['observations_csv']) && is_string($_POST['observations_csv']) ? $_POST['observations_csv'] : '';
		if ($sourceName === '' || strlen($sourceName) > 255 || $sourceVersion === '' || strlen($sourceVersion) > 80 || $evidenceLocation === '' || strlen($evidenceLocation) > 120 || strlen($csv) > 200000) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_reference_import', 'message' => 'Enter source name, version, evidence location within their field limits, and no more than 200 KB of CSV data.'));
		}
		try { $observations = spParseReferenceCsv($csv); }
		catch (InvalidArgumentException $e) { spRespond(400, array('status' => 'error', 'code' => 'invalid_reference_import', 'message' => $e->getMessage())); }
		$sourceHash = hash('sha256', $csv);
		$keys = array_map(function($observation) { return $observation['observation_key']; }, $observations);
		$existingRows = spRows($db, 'SELECT observation_key FROM sales_performance_reference_observations WHERE source_sha256=? AND observation_key IN (' . implode(',', array_fill(0, count($keys), '?')) . ')', array_merge(array($sourceHash), $keys));
		$existingKeys = array();
		foreach ($existingRows as $existingRow) $existingKeys[(string) $existingRow['observation_key']] = true;
		$previewRows = array();
		$newCount = 0;
		$duplicateCount = 0;
		foreach ($observations as $observation) {
			$duplicate = isset($existingKeys[$observation['observation_key']]);
			if ($duplicate) $duplicateCount++; else $newCount++;
			$observation['already_imported'] = $duplicate;
			$previewRows[] = $observation;
		}
		$previewToken = bin2hex(random_bytes(32));
		$_SESSION['sales_performance_reference_preview'] = array(
			'token' => $previewToken, 'user_id' => (string) $userId, 'source_name' => $sourceName,
			'source_version' => $sourceVersion, 'evidence_location' => $evidenceLocation,
			'source_sha256' => $sourceHash, 'expires_at' => time() + 900
		);
		spRespond(200, array('status' => 'success', 'message' => 'Preview is ready. These rows have not been imported.',
			'source_sha256' => $sourceHash, 'preview_token' => $previewToken, 'expires_in_seconds' => 900,
			'row_count' => count($previewRows), 'new_count' => $newCount, 'duplicate_count' => $duplicateCount, 'rows' => $previewRows));
	}

	if ($action === 'import_reference_observations') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to import reference observations.'));
		$sourceName = isset($_POST['source_name']) && is_string($_POST['source_name']) ? trim(stripslashes($_POST['source_name'])) : '';
		$sourceVersion = isset($_POST['source_version']) && is_string($_POST['source_version']) ? trim(stripslashes($_POST['source_version'])) : '';
		$evidenceLocation = isset($_POST['evidence_location']) && is_string($_POST['evidence_location']) ? trim(stripslashes($_POST['evidence_location'])) : '';
		$csv = isset($_POST['observations_csv']) && is_string($_POST['observations_csv']) ? $_POST['observations_csv'] : '';
		if ($sourceName === '' || strlen($sourceName) > 255 || $sourceVersion === '' || strlen($sourceVersion) > 80 || $evidenceLocation === '' || strlen($evidenceLocation) > 120 || strlen($csv) > 200000) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_reference_import', 'message' => 'Enter source name, version, evidence location within their field limits, and no more than 200 KB of CSV data.'));
		}
		try { $observations = spParseReferenceCsv($csv); }
		catch (InvalidArgumentException $e) { spRespond(400, array('status' => 'error', 'code' => 'invalid_reference_import', 'message' => $e->getMessage())); }
		$sourceHash = hash('sha256', $csv);
		$previewToken = isset($_POST['preview_token']) && is_string($_POST['preview_token']) ? $_POST['preview_token'] : '';
		$preview = isset($_SESSION['sales_performance_reference_preview']) && is_array($_SESSION['sales_performance_reference_preview']) ? $_SESSION['sales_performance_reference_preview'] : array();
		$previewMatches = isset($preview['token'], $preview['user_id'], $preview['source_name'], $preview['source_version'], $preview['evidence_location'], $preview['source_sha256'], $preview['expires_at'])
			&& preg_match('/^[a-f0-9]{64}$/', $previewToken) === 1 && hash_equals((string) $preview['token'], $previewToken)
			&& (string) $preview['user_id'] === (string) $userId && (int) $preview['expires_at'] >= time()
			&& (string) $preview['source_name'] === $sourceName && (string) $preview['source_version'] === $sourceVersion
			&& (string) $preview['evidence_location'] === $evidenceLocation && hash_equals((string) $preview['source_sha256'], $sourceHash);
		if (!$previewMatches) spRespond(409, array('status' => 'error', 'code' => 'reference_preview_required', 'message' => 'Preview this exact source and CSV before importing. Previews expire after 15 minutes.'));
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the reference import.');
		try {
			$inserted = 0;
			$duplicates = 0;
			foreach ($observations as $observation) {
				$cutoff = $observation['cutoff_at'] === '' ? null : (strlen($observation['cutoff_at']) === 16 ? $observation['cutoff_at'] . ':00' : $observation['cutoff_at']);
				$insertedRow = spExecute($db,
					"INSERT IGNORE INTO sales_performance_reference_observations (source_name, source_sha256, source_version, evidence_location, observation_key, period_from, period_through, cutoff_at, granularity, scope_key, team_name, salesperson_name, metric_key, observed_count, observed_value, value_basis, value_currency, denominator, verification_state, notes, imported_by, imported_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'transcribed_unverified', ?, ?, NOW())",
					array($sourceName, $sourceHash, $sourceVersion, $evidenceLocation, $observation['observation_key'], $observation['period_from'], $observation['period_through'], $cutoff,
						$observation['granularity'], $observation['scope_key'] === '' ? null : $observation['scope_key'], $observation['team_name'] === '' ? null : $observation['team_name'],
						$observation['salesperson_name'] === '' ? null : $observation['salesperson_name'], $observation['metric_key'], $observation['observed_count'], $observation['observed_value'],
						$observation['value_basis'] === '' ? null : $observation['value_basis'], $observation['value_currency'] === '' ? null : $observation['value_currency'],
						$observation['denominator'], $observation['notes'] === '' ? null : $observation['notes'], $userId)
				);
				if ($insertedRow === 1) $inserted++;
				else $duplicates++;
			}
			if (!mysqli_commit($db)) throw new Exception('Could not finish the reference import.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		unset($_SESSION['sales_performance_reference_preview']);
		spRespond(200, array('status' => 'success', 'message' => 'Reference observations imported as unverified transcription.', 'source_sha256' => $sourceHash, 'inserted' => $inserted, 'duplicates' => $duplicates));
	}

	if ($action === 'update_reference_verification') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to review a reference observation.'));
		$id = spPostText('observation_id', 20);
		$state = spPostText('verification_state', 24);
		$note = spPostText('verification_note', 2000);
		if (!ctype_digit($id) || (int) $id < 1 || !in_array($state, array('source_verified', 'disputed'), true) || trim($note) === '') {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_reference_review', 'message' => 'Choose source verified or disputed, and record the evidence reviewed.'));
		}
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the reference review.');
		try {
			$rows = spRows($db, 'SELECT id, verification_state, verification_note FROM sales_performance_reference_observations WHERE id=? LIMIT 1 FOR UPDATE', array($id));
			if (!count($rows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'reference_missing', 'message' => 'That reference observation was not found.')); }
			$current = $rows[0];
			if ((string) $current['verification_state'] === $state && (string) $current['verification_note'] === $note) {
				mysqli_rollback($db);
				spRespond(200, array('status' => 'success', 'message' => 'This reference review is already recorded.', 'duplicate' => true));
			}
			spExecute($db, 'INSERT INTO sales_performance_reference_observation_history (observation_id, previous_state, new_state, review_note, changed_by, changed_at) VALUES (?, ?, ?, ?, ?, NOW())', array($id, $current['verification_state'], $state, $note, $userId));
			spExecute($db, 'UPDATE sales_performance_reference_observations SET verification_state=?, verification_note=? WHERE id=?', array($state, $note, $id));
			if (!mysqli_commit($db)) throw new Exception('Could not finish the reference review.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'Reference verification state and review history saved.'));
	}

	if ($action === 'create_reconciliation') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to compare a frozen report with a reference.'));
		$referenceId = spPostText('reference_observation_id', 20);
		$runKey = spPostText('run_key', 36);
		$classification = spPostText('classification', 40);
		$owner = spPostText('owner_user_id', 50);
		$supporting = spPostText('supporting_documents', 2000);
		$allowedClassifications = array('unresolved', 'rounding', 'source_variance', 'definition_difference', 'data_quality', 'explained');
		if (!ctype_digit($referenceId) || (int) $referenceId < 1 || !preg_match('/^[a-f0-9-]{36}$/', $runKey) || !in_array($classification, $allowedClassifications, true)) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_reconciliation', 'message' => 'Choose a reference observation, a frozen report key and a discrepancy classification.'));
		}
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the reconciliation comparison.');
		try {
			$referenceRows = spRows($db, 'SELECT * FROM sales_performance_reference_observations WHERE id=? LIMIT 1 FOR UPDATE', array($referenceId));
			$runs = spRows($db, "SELECT id, run_key, run_state, authorized_scope_text, filters_text, totals_text, content_hash FROM sales_performance_report_runs WHERE run_key=? AND run_state='ready' LIMIT 1 FOR UPDATE", array($runKey));
			if (!count($referenceRows) || !count($runs)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'reconciliation_source_missing', 'message' => 'The selected observation or frozen report could not be found.')); }
			$reference = $referenceRows[0];
			$run = $runs[0];
			$runScope = json_decode($run['authorized_scope_text'], true);
			$runFilters = json_decode($run['filters_text'], true);
			$report = json_decode($run['totals_text'], true);
			if (!is_array($runScope) || !is_array($runFilters) || !is_array($report) || !isset($report['meta'], $report['current']['metrics'])) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'invalid_frozen_report', 'message' => 'The frozen report does not contain a comparable current-period summary.')); }
			$validHash = hash_equals((string) $run['content_hash'], spFrozenRunContentHash($db, $run['id'], $run['totals_text']));
			$comparisonScope = spReferenceRunMatchesScope($db, $reference, $runScope, $runFilters);
			$periodMatches = isset($report['meta']['from'], $report['meta']['through'], $report['meta']['business_cutoff'])
				&& $reference['period_from'] === $report['meta']['from'] && $reference['period_through'] === $report['meta']['through']
				&& $report['meta']['business_cutoff'] === $reference['period_through'];
		$referenceCutoffMatches = $reference['cutoff_at'] === null || (string) $reference['cutoff_at'] === $reference['period_through'] . ' 23:59:59';
			if (!$validHash) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'run_integrity_failed', 'message' => 'The frozen report facts do not match their saved content hash.')); }
			if ($comparisonScope === false || !$periodMatches || !$referenceCutoffMatches) { mysqli_rollback($db); spRespond(400, array('status' => 'error', 'code' => 'comparison_scope_mismatch', 'message' => 'Choose a frozen company, single-team or single-salesperson report that exactly matches the observation scope, period and end-of-day cutoff. Named-population reports and partial-day cutoffs cannot be compared here.')); }
			$existing = spRows($db, 'SELECT id FROM sales_performance_reconciliation WHERE run_id=? AND reference_observation_id=? LIMIT 1 FOR UPDATE', array((string) $run['id'], $referenceId));
			if (count($existing)) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'comparison_exists', 'message' => 'This reference observation is already linked to that frozen report.')); }
			$metricKey = (string) $reference['metric_key'];
			if (!isset($report['current']['metrics'][$metricKey])) { mysqli_rollback($db); spRespond(400, array('status' => 'error', 'code' => 'metric_missing', 'message' => 'The frozen report does not include the observation metric.')); }
			$metric = $report['current']['metrics'][$metricKey];
			$valueField = null;
			$unknownField = null;
			if ($reference['observed_value'] !== null) {
				$basisFields = array('raw' => array('raw_value', 'raw_unknown_count'), 'net' => array('net_value', 'net_unknown_count'), 'normalized' => array('normalized_value', 'normalized_unknown_count'));
				$basis = (string) $reference['value_basis'];
				if (!isset($basisFields[$basis]) || (string) $reference['value_currency'] !== 'PKR' || in_array($metricKey, array('followups', 'enquiry_responses', 'company_regrets', 'customer_rejections'), true)) { mysqli_rollback($db); spRespond(400, array('status' => 'error', 'code' => 'value_basis_unavailable', 'message' => 'Only PKR observations with a supported monetary metric and value basis can be compared.')); }
				$valueField = $basisFields[$basis][0];
				$unknownField = $basisFields[$basis][1];
				if (!array_key_exists($valueField, $metric) || !empty($metric[$unknownField])) { mysqli_rollback($db); spRespond(409, array('status' => 'error', 'code' => 'erp_value_incomplete', 'message' => 'The frozen ERP value has unknown source amounts for this basis. Compare its count only or use a complete frozen run.')); }
			}
			$erpCount = $reference['observed_count'] === null ? null : (string) $metric['count'];
			$erpValue = $reference['observed_value'] === null ? null : (string) $metric[$valueField];
			$differenceCount = null;
			$differenceValue = null;
			if ($erpCount !== null) $differenceCount = spRows($db, 'SELECT CAST(CAST(? AS DECIMAL(24,6))-CAST(? AS DECIMAL(24,6)) AS DECIMAL(24,6)) AS difference_value', array($erpCount, (string) $reference['observed_count']))[0]['difference_value'];
			if ($erpValue !== null) $differenceValue = spRows($db, 'SELECT CAST(CAST(? AS DECIMAL(24,6))-CAST(? AS DECIMAL(24,6)) AS DECIMAL(24,6)) AS difference_value', array($erpValue, (string) $reference['observed_value']))[0]['difference_value'];
			$sourceBasis = json_encode(array('run_key' => $run['run_key'], 'run_content_hash' => $run['content_hash'], 'metric_key' => $metricKey,
				'period_role' => 'current', 'business_cutoff' => $report['meta']['business_cutoff'], 'calendar_version' => isset($report['meta']['calendar_version']) ? $report['meta']['calendar_version'] : null,
				'value_basis' => $reference['value_basis'], 'value_currency' => $reference['value_currency'], 'reference_source_sha256' => $reference['source_sha256'],
				'reference_cutoff_at' => $reference['cutoff_at'], 'reference_granularity' => $reference['granularity'], 'reference_verification_state' => $reference['verification_state'],
				'report_validation_status' => isset($report['meta']['validation_status']) ? $report['meta']['validation_status'] : 'unknown',
				'current_reporting_days' => isset($report['current']['reporting_days']) ? $report['current']['reporting_days'] : null,
				'comparison_scope' => $comparisonScope, 'run_scope' => $runScope, 'run_filters' => $runFilters), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			if ($sourceBasis === false) throw new Exception('The reconciliation evidence could not be serialized.');
			spExecute($db,
				"INSERT INTO sales_performance_reconciliation (run_id, reference_observation_id, metric_key, scope_key, period_from, period_through, erp_count, erp_value, reference_count, reference_value, difference_count, difference_value, classification, source_basis, supporting_documents, owner_user_id, resolution_note, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULL, 'open', ?, NOW())",
				array((string) $run['id'], $referenceId, $metricKey, $comparisonScope['scope_key'], $reference['period_from'], $reference['period_through'], $erpCount, $erpValue,
					$reference['observed_count'], $reference['observed_value'], $differenceCount, $differenceValue, $classification, $sourceBasis,
					$supporting === '' ? null : $supporting, $owner === '' ? null : $owner, $userId)
			);
			$reconciliationId = (string) mysqli_insert_id($db);
			spExecute($db, "INSERT INTO sales_performance_reconciliation_history (reconciliation_id, action_type, classification, status, owner_user_id, resolution_note, changed_by, changed_at) VALUES (?, 'created', ?, 'open', ?, NULL, ?, NOW())", array($reconciliationId, $classification, $owner === '' ? null : $owner, $userId));
			if (!mysqli_commit($db)) throw new Exception('Could not finish the reconciliation comparison.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'Frozen ' . strtolower($comparisonScope['scope_label'] === 'Company-wide' ? 'company' : $comparisonScope['scope_key']) . ' result linked to the reference observation and added to the open discrepancy register.', 'reconciliation_id' => $reconciliationId));
	}

	if ($action === 'update_reconciliation') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to update a reconciliation.'));
		$id = spPostText('reconciliation_id', 20);
		$status = spPostText('status', 20);
		$classification = spPostText('classification', 40);
		$owner = spPostText('owner_user_id', 50);
		$resolution = spPostText('resolution_note', 2000);
		if (!ctype_digit($id) || (int) $id < 1 || !in_array($status, array('open', 'in_review', 'resolved', 'closed'), true)
			|| !in_array($classification, array('unresolved', 'rounding', 'source_variance', 'definition_difference', 'data_quality', 'explained'), true)
			|| (in_array($status, array('resolved', 'closed'), true) && trim($resolution) === '')) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_reconciliation_update', 'message' => 'Choose a valid status and classification. Add a resolution note before marking a discrepancy resolved or closed.'));
		}
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the reconciliation update.');
		try {
			$rows = spRows($db, 'SELECT id, reference_observation_id, classification, status, owner_user_id, resolution_note FROM sales_performance_reconciliation WHERE id=? LIMIT 1 FOR UPDATE', array($id));
			if (!count($rows)) { mysqli_rollback($db); spRespond(404, array('status' => 'error', 'code' => 'reconciliation_missing', 'message' => 'That discrepancy was not found.')); }
			$current = $rows[0];
			if (in_array($status, array('resolved', 'closed'), true)) {
				$referenceState = spRows($db, 'SELECT verification_state FROM sales_performance_reference_observations WHERE id=? LIMIT 1 FOR UPDATE', array((string) $current['reference_observation_id']));
				if (!count($referenceState) || $referenceState[0]['verification_state'] !== 'source_verified') {
					mysqli_rollback($db);
					spRespond(409, array('status' => 'error', 'code' => 'reference_not_verified', 'message' => 'Verify the reference against its cited source before resolving or closing this discrepancy.'));
				}
			}
			$newOwner = $owner === '' ? null : $owner;
			$newResolution = $resolution === '' ? null : $resolution;
			if ((string) $current['classification'] === $classification && (string) $current['status'] === $status
				&& (string) $current['owner_user_id'] === (string) $newOwner && (string) $current['resolution_note'] === (string) $newResolution) {
				mysqli_rollback($db);
				spRespond(200, array('status' => 'success', 'message' => 'No reconciliation fields changed.', 'duplicate' => true));
			}
			spExecute($db, 'INSERT INTO sales_performance_reconciliation_history (reconciliation_id, action_type, classification, status, owner_user_id, resolution_note, changed_by, changed_at) VALUES (?, \'updated\', ?, ?, ?, ?, ?, NOW())', array($id, $classification, $status, $newOwner, $newResolution, $userId));
			spExecute($db, 'UPDATE sales_performance_reconciliation SET classification=?, status=?, owner_user_id=?, resolution_note=? WHERE id=?', array($classification, $status, $newOwner, $newResolution, $id));
			if (!mysqli_commit($db)) throw new Exception('Could not finish the reconciliation update.');
		} catch (Exception $e) { mysqli_rollback($db); throw $e; }
		spRespond(200, array('status' => 'success', 'message' => 'Reconciliation status and resolution history saved.'));
	}

	if ($action === 'submit_coverage') {
		spRequireFormToken();
		if (!$canAdminSetup) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A company-scope reporting administrator is required to submit coverage.'));
		$scopeKey = spPostText('scope_key', 64);
		$teamText = spPostText('team_id', 12);
		$coverageDate = spPostText('coverage_date', 10);
		$state = spPostText('coverage_state', 24);
		$note = spPostText('coverage_note', 1000);
		if ($scopeKey !== 'erp_company' || !spValidDate($coverageDate) || $coverageDate > date('Y-m-d')
			|| !in_array($state, array('complete', 'partial', 'unresolved', 'not_submitted'), true)) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_coverage', 'message' => 'Choose a valid scope, past or current date and coverage state.'));
		}
		$teamId = null;
		if ($teamText !== '') {
			if (!ctype_digit($teamText)) spRespond(400, array('status' => 'error', 'code' => 'invalid_coverage', 'message' => 'Choose a valid team.'));
			$teamId = (int) $teamText;
			$teamCheck = spRows($db, 'SELECT id FROM salesteam WHERE id = ? LIMIT 1', array((string) $teamId));
			if (!count($teamCheck)) spRespond(400, array('status' => 'error', 'code' => 'invalid_coverage', 'message' => 'That team no longer exists.'));
		}
		if (trim($note) === '') spRespond(400, array('status' => 'error', 'code' => 'coverage_evidence_required', 'message' => 'Add a short evidence or unresolved-items note for every coverage submission.'));
		$reviewMetricsJson = isset($_POST['coverage_review_metrics_json']) && is_string($_POST['coverage_review_metrics_json']) ? htmlspecialchars_decode(stripslashes($_POST['coverage_review_metrics_json']), ENT_COMPAT) : '';
		$reviewContextJson = isset($_POST['coverage_review_context_json']) && is_string($_POST['coverage_review_context_json']) ? htmlspecialchars_decode(stripslashes($_POST['coverage_review_context_json']), ENT_COMPAT) : '';
		$reviewSignature = spPostText('coverage_review_signature', 64);
		$reviewMetrics = $reviewMetricsJson !== '' && strlen($reviewMetricsJson) <= 30000 ? json_decode($reviewMetricsJson, true) : null;
		$reviewContext = $reviewContextJson !== '' && strlen($reviewContextJson) <= 3000 ? json_decode($reviewContextJson, true) : null;
		$reviewReason = 'Choose the date and team in the loaded full-company report, review its calculated totals, and submit again.';
		if (!is_array($reviewMetrics) || !isset($reviewMetrics['metrics']) || !is_array($reviewMetrics['metrics']) || !is_array($reviewContext)
			|| !isset($_SESSION['sales_performance_coverage_review_key']) || !is_string($_SESSION['sales_performance_coverage_review_key'])
			|| !preg_match('/^[a-f0-9]{64}$/', $reviewSignature)) {
			spRespond(409, array('status' => 'error', 'code' => 'coverage_review_required', 'message' => $reviewReason));
		}
		$canonicalMetricsJson = json_encode($reviewMetrics, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$canonicalContextJson = json_encode($reviewContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$expectedMetricKeys = spMetricKeys();
		$providedMetricKeys = array_keys($reviewMetrics['metrics']);
		$missingMetricKeys = array_diff($expectedMetricKeys, $providedMetricKeys);
		if ($canonicalMetricsJson !== $reviewMetricsJson || $canonicalContextJson !== $reviewContextJson
			|| count($missingMetricKeys) > 0 || count($providedMetricKeys) !== count($expectedMetricKeys)
			|| !array_key_exists('unanswered_new_count', $reviewMetrics)) {
			spRespond(409, array('status' => 'error', 'code' => 'coverage_review_invalid', 'message' => 'The calculated review snapshot is invalid. Reload the full-company report and review the totals again.'));
		}
		$periodFrom = isset($reviewContext['period_from']) ? $reviewContext['period_from'] : '';
		$periodThrough = isset($reviewContext['period_through']) ? $reviewContext['period_through'] : '';
		$reviewCutoff = isset($reviewContext['business_cutoff']) ? $reviewContext['business_cutoff'] : '';
		$reviewCalendarVersion = isset($reviewContext['calendar_version']) ? $reviewContext['calendar_version'] : '';
		$reviewIssuedAt = isset($reviewContext['issued_at']) && is_numeric($reviewContext['issued_at']) ? (int) $reviewContext['issued_at'] : 0;
		$reviewTeamIds = isset($reviewContext['report_team_ids']) && is_array($reviewContext['report_team_ids']) ? $reviewContext['report_team_ids'] : null;
		if (!is_string($periodFrom) || !is_string($periodThrough) || !is_string($reviewCutoff) || !is_string($reviewCalendarVersion) || $reviewTeamIds === null) {
			spRespond(409, array('status' => 'error', 'code' => 'coverage_review_invalid', 'message' => 'The report review context is invalid. Reload the full-company report and review the totals again.'));
		}
		foreach ($reviewTeamIds as $reviewTeamId) if (!is_numeric($reviewTeamId) || (int) $reviewTeamId < 1) spRespond(409, array('status' => 'error', 'code' => 'coverage_review_invalid', 'message' => 'The report team scope is invalid. Reload the full-company report and review the totals again.'));
		$reviewTeamIds = array_map('intval', $reviewTeamIds);
		if ($teamId !== null && !in_array($teamId, $reviewTeamIds, true)) spRespond(409, array('status' => 'error', 'code' => 'coverage_review_invalid', 'message' => 'The selected team is outside the reviewed report scope. Reload the report and review the totals again.'));
		$expectedContext = spCoverageReviewContext($reviewMetrics, $coverageDate, $scopeKey, $teamId, $userId, $periodFrom, $periodThrough, $reviewCutoff, $reviewCalendarVersion, $reviewIssuedAt, $reviewTeamIds);
		$expectedContextJson = json_encode($expectedContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$expectedSignature = hash_hmac('sha256', $expectedContextJson, $_SESSION['sales_performance_coverage_review_key']);
		if (!hash_equals($expectedContextJson, $reviewContextJson) || !hash_equals($expectedSignature, $reviewSignature)
			|| !spValidDate($periodFrom) || !spValidDate($periodThrough) || $coverageDate < $periodFrom || $coverageDate > $periodThrough
			|| !spValidDate($reviewCutoff) || $coverageDate > $reviewCutoff || $reviewIssuedAt > time() || time() - $reviewIssuedAt > 900
			|| $reviewCalendarVersion !== spCurrentCalendarVersion($db)) {
			spRespond(409, array('status' => 'error', 'code' => 'coverage_review_expired', 'message' => 'The report review has expired or does not match this date and scope. Reload the current report, review the totals, and submit again.'));
		}
		$reviewSnapshotJson = json_encode(array('context' => $reviewContext, 'metrics' => $reviewMetrics, 'signature' => $reviewSignature), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		if ($reviewSnapshotJson === false) throw new Exception('Could not store the coverage review snapshot.');
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the coverage submission.');
		try {
			$settings = spRows($db, "SELECT setting_key, setting_value FROM sales_performance_settings WHERE setting_key IN ('report_timezone','report_calendar_version') FOR UPDATE", array());
			$lockedCalendarVersion = null;
			foreach ($settings as $setting) if ($setting['setting_key'] === 'report_calendar_version') $lockedCalendarVersion = (string) $setting['setting_value'];
			if ($lockedCalendarVersion === null || $lockedCalendarVersion !== $reviewCalendarVersion) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'code' => 'coverage_review_expired', 'message' => 'The reporting calendar changed. Reload the report, review the totals again, and resubmit.'));
			}
			$coverageAtRows = spRows($db, 'SELECT NOW() AS coverage_at', array());
			$coverageAt = $coverageAtRows[0]['coverage_at'];
			$existing = $teamId === null
				? spRows($db, "SELECT id, coverage_state, reporter_user_id, evidence_reference, note, business_cutoff, submitted_at, review_snapshot_json FROM sales_performance_coverage WHERE scope_key=? AND team_id IS NULL AND metric_key='report_submission' AND coverage_date=? LIMIT 1 FOR UPDATE", array($scopeKey, $coverageDate))
				: spRows($db, "SELECT id, coverage_state, reporter_user_id, evidence_reference, note, business_cutoff, submitted_at, review_snapshot_json FROM sales_performance_coverage WHERE scope_key=? AND team_id=? AND metric_key='report_submission' AND coverage_date=? LIMIT 1 FOR UPDATE", array($scopeKey, (string) $teamId, $coverageDate));
			if (count($existing)) {
				$old = $existing[0];
				if ((string) $old['coverage_state'] === $state && (string) $old['reporter_user_id'] === (string) $userId
					&& (string) $old['evidence_reference'] === $note && (string) $old['note'] === $note && (string) $old['review_snapshot_json'] === $reviewSnapshotJson) {
					mysqli_rollback($db);
					spRespond(200, array('status' => 'success', 'message' => 'That coverage submission is already saved.', 'duplicate' => true));
				}
				$coverageId = (string) $old['id'];
				spExecute($db, "INSERT INTO sales_performance_coverage_history (coverage_id, action_type, previous_state, new_state, previous_note, new_note, previous_evidence_reference, new_evidence_reference, previous_reporter_user_id, new_reporter_user_id, previous_business_cutoff, new_business_cutoff, previous_submitted_at, new_submitted_at, previous_review_snapshot_json, new_review_snapshot_json, changed_by, changed_at) VALUES (?, 'updated', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
					array($coverageId, $old['coverage_state'], $state, $old['note'], $note, $old['evidence_reference'], $note, $old['reporter_user_id'], $userId, $old['business_cutoff'], $coverageAt, $old['submitted_at'], $coverageAt, $old['review_snapshot_json'], $reviewSnapshotJson, $userId, $coverageAt));
				spExecute($db, 'UPDATE sales_performance_coverage SET coverage_state=?, business_cutoff=?, reporter_user_id=?, submitted_at=?, evidence_reference=?, note=?, review_snapshot_json=? WHERE id=?', array($state, $coverageAt, $userId, $coverageAt, $note, $note, $reviewSnapshotJson, $coverageId));
			} else {
				spExecute($db, 'INSERT INTO sales_performance_coverage (scope_key, team_id, metric_key, coverage_date, coverage_state, business_cutoff, reporter_user_id, submitted_at, evidence_reference, note, review_snapshot_json) VALUES (?, ?, \'report_submission\', ?, ?, ?, ?, ?, ?, ?, ?)', array($scopeKey, $teamId, $coverageDate, $state, $coverageAt, $userId, $coverageAt, $note, $note, $reviewSnapshotJson));
				$coverageId = (string) mysqli_insert_id($db);
				spExecute($db, "INSERT INTO sales_performance_coverage_history (coverage_id, action_type, previous_state, new_state, previous_note, new_note, previous_evidence_reference, new_evidence_reference, previous_reporter_user_id, new_reporter_user_id, previous_business_cutoff, new_business_cutoff, previous_submitted_at, new_submitted_at, previous_review_snapshot_json, new_review_snapshot_json, changed_by, changed_at) VALUES (?, 'created', NULL, ?, NULL, ?, NULL, ?, NULL, ?, NULL, ?, NULL, ?, NULL, ?, ?, ?)",
					array($coverageId, $state, $note, $note, $userId, $coverageAt, $coverageAt, $reviewSnapshotJson, $userId, $coverageAt));
			}
			if (!mysqli_commit($db)) throw new Exception('Could not complete the coverage submission.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'Coverage submission saved with its audit history.'));
	}

	if ($action === 'request_adjustment') {
		spRequireFormToken();
		if (!$canManageAdjustments || !$canViewCompany) spRespond(403, array('status' => 'error', 'message' => 'An authorized company-scope reviewer is required to request normalization changes.'));
		$metric = spPostText('metric_key', 32);
		$sourceKey = spPostText('source_key', 100);
		$amountText = spPostText('excluded_amount', 40);
		$reason = spPostText('reason', 500);
		$allowedMetrics = array('quotation', 'purchase_order', 'delivery_challan');
		try { $amount = \SAHamid\BI\Commercial\DecimalAmount::normalize($amountText); }
		catch (InvalidArgumentException $e) { $amount = null; }
		if (!in_array($metric, $allowedMetrics, true) || !preg_match('/^[A-Za-z0-9_.|\/-]{1,100}$/', $sourceKey)
			|| $amount === null || \SAHamid\BI\Commercial\DecimalAmount::compare($amount, '0.00') <= 0 || $reason === '') {
			spRespond(400, array('status' => 'error', 'message' => 'Enter a document key, a positive excluded amount, and a reason.'));
		}
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the normalization request.');
		try {
			spAcquireAdjustmentLock($db, $metric, $sourceKey);
			$documentValue = spAdjustmentSourceValue($db, $metric, $sourceKey);
			if ($documentValue === null || \SAHamid\BI\Commercial\DecimalAmount::compare($documentValue, '0.00') <= 0) {
				mysqli_rollback($db);
				spRespond(400, array('status' => 'error', 'message' => 'The document was not found or has no reportable value. Check the document key format.'));
			}
			$approved = spRows($db, "SELECT CAST(COALESCE(SUM(excluded_amount),0) AS DECIMAL(24,2)) AS excluded_total FROM sales_performance_adjustments WHERE metric_key=? AND source_key=? AND adjustment_type='value_exclusion' AND status='approved'", array($metric, $sourceKey));
			$pending = spRows($db, "SELECT id FROM sales_performance_adjustments WHERE metric_key=? AND source_key=? AND adjustment_type='value_exclusion' AND status='pending' LIMIT 1 FOR UPDATE", array($metric, $sourceKey));
			if (count($pending)) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'message' => 'This document already has an adjustment request waiting for review.'));
			}
			$alreadyExcluded = count($approved) ? (string) $approved[0]['excluded_total'] : '0.00';
			$remaining = \SAHamid\BI\Commercial\DecimalAmount::subtract($documentValue, $alreadyExcluded);
			if (\SAHamid\BI\Commercial\DecimalAmount::compare($amount, $remaining) > 0) {
				mysqli_rollback($db);
				spRespond(400, array('status' => 'error', 'message' => 'The exclusion cannot exceed the document value remaining after approved adjustments.'));
			}
			spExecute($db,
				"INSERT INTO sales_performance_adjustments (metric_key, source_key, canonical_source_key, excluded_amount, reason, status, requested_by, requested_at, adjustment_type, count_effect, source_amount_basis, source_version) VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), 'value_exclusion', 'retain', ?, 'live_source_at_request')",
				array($metric, $sourceKey, $sourceKey, $amount, $reason, $userId, $documentValue)
			);
			spExecute($db, "UPDATE sales_performance_normalization_locks SET revision_no=revision_no+1, updated_at=NOW() WHERE source_type=? AND source_key=? AND policy_version='normalized-value-v1'", array($metric, $sourceKey));
			if (!mysqli_commit($db)) throw new Exception('Could not commit the normalization request.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'Adjustment submitted for approval. It will affect normalized values after approval.'));
	}

	if ($action === 'request_adjustment_reversal') {
		spRequireFormToken();
		if (!$canManageAdjustments || !$canViewCompany) spRespond(403, array('status' => 'error', 'message' => 'An authorized company-scope reviewer is required to request an adjustment reversal.'));
		$id = isset($_POST['adjustment_id']) ? (int) $_POST['adjustment_id'] : 0;
		if ($id < 1) spRespond(400, array('status' => 'error', 'message' => 'Choose a valid approved adjustment to reverse.'));
		$target = spRows($db, 'SELECT metric_key, source_key FROM sales_performance_adjustments WHERE id=? LIMIT 1', array((string) $id));
		if (!count($target)) spRespond(404, array('status' => 'error', 'message' => 'That adjustment was not found.'));
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the reversal request.');
		try {
			spAcquireAdjustmentLock($db, $target[0]['metric_key'], $target[0]['source_key']);
			$original = spRows($db, 'SELECT id, metric_key, source_key, excluded_amount, source_amount_basis, source_version, policy_version, adjustment_type, reversal_of_id, status FROM sales_performance_adjustments WHERE id=? LIMIT 1 FOR UPDATE', array((string) $id));
			if (!count($original) || $original[0]['status'] !== 'approved' || $original[0]['adjustment_type'] !== 'value_exclusion' || $original[0]['reversal_of_id'] !== null
				|| \SAHamid\BI\Commercial\DecimalAmount::compare($original[0]['excluded_amount'], '0') <= 0) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'message' => 'Only an approved value exclusion can be reversed.'));
			}
			$pending = spRows($db, "SELECT id FROM sales_performance_adjustments WHERE reversal_of_id=? AND adjustment_type='value_exclusion' AND status='pending' LIMIT 1 FOR UPDATE", array((string) $id));
			if (count($pending)) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'message' => 'A reversal request for this adjustment is already waiting for review.'));
			}
			$reversedRows = spRows($db, "SELECT CAST(COALESCE(SUM(-excluded_amount),0) AS DECIMAL(24,2)) AS reversed_amount FROM sales_performance_adjustments WHERE reversal_of_id=? AND adjustment_type='value_exclusion' AND status='approved'", array((string) $id));
			$reversedAmount = count($reversedRows) ? (string) $reversedRows[0]['reversed_amount'] : '0.00';
			$remaining = \SAHamid\BI\Commercial\DecimalAmount::subtract($original[0]['excluded_amount'], $reversedAmount);
			if (\SAHamid\BI\Commercial\DecimalAmount::compare($remaining, '0') <= 0) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'message' => 'That approved adjustment has already been fully reversed.'));
			}
			$negativeAmount = \SAHamid\BI\Commercial\DecimalAmount::subtract('0', $remaining);
			spExecute($db,
				"INSERT INTO sales_performance_adjustments (metric_key, source_key, canonical_source_key, excluded_amount, reason, status, requested_by, requested_at, adjustment_type, policy_version, count_effect, source_amount_basis, source_version, reversal_of_id) VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW(), 'value_exclusion', ?, 'retain', ?, ?, ?)",
				array($original[0]['metric_key'], $original[0]['source_key'], $original[0]['source_key'], $negativeAmount,
					'Reversal request for approved adjustment #' . $id, $userId, $original[0]['policy_version'],
					$original[0]['source_amount_basis'], 'reversal_of_adjustment:' . $id, (string) $id)
			);
			spExecute($db, "UPDATE sales_performance_normalization_locks SET revision_no=revision_no+1, updated_at=NOW() WHERE source_type=? AND source_key=? AND policy_version='normalized-value-v1'", array($original[0]['metric_key'], $original[0]['source_key']));
			if (!mysqli_commit($db)) throw new Exception('Could not commit the reversal request.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'A reversal for the remaining excluded value was submitted for independent approval.'));
	}

	if ($action === 'review_adjustment') {
		spRequireFormToken();
		if (!$canApproveAdjustments || !$canViewCompany) spRespond(403, array('status' => 'error', 'message' => 'An independently authorized company-scope reviewer is required.'));
		$id = isset($_POST['adjustment_id']) ? (int) $_POST['adjustment_id'] : 0;
		$decision = spPostText('decision', 16);
		$note = spPostText('review_note', 500);
		if ($id < 1 || !in_array($decision, array('approved', 'rejected'), true)) {
			spRespond(400, array('status' => 'error', 'message' => 'Choose a valid adjustment decision.'));
		}
		$target = spRows($db, 'SELECT metric_key, source_key FROM sales_performance_adjustments WHERE id=? AND status="pending" LIMIT 1', array((string) $id));
		if (!count($target)) spRespond(409, array('status' => 'error', 'message' => 'That adjustment has already been reviewed.'));
		if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the adjustment review.');
		try {
			spAcquireAdjustmentLock($db, $target[0]['metric_key'], $target[0]['source_key']);
			$pending = spRows($db, 'SELECT requested_by, excluded_amount, metric_key, source_key, adjustment_type, reversal_of_id FROM sales_performance_adjustments WHERE id=? AND status="pending" LIMIT 1 FOR UPDATE', array((string) $id));
			if (!count($pending)) {
				mysqli_rollback($db);
				spRespond(409, array('status' => 'error', 'message' => 'That adjustment has already been reviewed.'));
			}
			if ($pending[0]['requested_by'] === $userId) {
				mysqli_rollback($db);
				spRespond(403, array('status' => 'error', 'message' => 'Another authorized user must review your adjustment.'));
			}
			$documentValue = null;
			if ($decision === 'approved' && $pending[0]['reversal_of_id'] !== null) {
				$originalRows = spRows($db, 'SELECT id, excluded_amount, source_amount_basis, adjustment_type, reversal_of_id, status FROM sales_performance_adjustments WHERE id=? LIMIT 1 FOR UPDATE', array((string) $pending[0]['reversal_of_id']));
				if (!count($originalRows) || $originalRows[0]['status'] !== 'approved' || $originalRows[0]['adjustment_type'] !== 'value_exclusion' || $originalRows[0]['reversal_of_id'] !== null) {
					mysqli_rollback($db);
					spRespond(409, array('status' => 'error', 'code' => 'reversal_source_changed', 'message' => 'The original approved exclusion is no longer available to reverse.'));
				}
				$reversedRows = spRows($db, "SELECT CAST(COALESCE(SUM(-excluded_amount),0) AS DECIMAL(24,2)) AS reversed_amount FROM sales_performance_adjustments WHERE reversal_of_id=? AND adjustment_type='value_exclusion' AND status='approved'", array((string) $pending[0]['reversal_of_id']));
				$reversedAmount = count($reversedRows) ? (string) $reversedRows[0]['reversed_amount'] : '0.00';
				$remaining = \SAHamid\BI\Commercial\DecimalAmount::subtract($originalRows[0]['excluded_amount'], $reversedAmount);
				$reversalAmount = \SAHamid\BI\Commercial\DecimalAmount::subtract('0', (string) $pending[0]['excluded_amount']);
				if ($pending[0]['adjustment_type'] !== 'value_exclusion'
					|| \SAHamid\BI\Commercial\DecimalAmount::compare($pending[0]['excluded_amount'], '0') >= 0
					|| \SAHamid\BI\Commercial\DecimalAmount::compare($reversalAmount, $remaining) > 0) {
					mysqli_rollback($db);
					spRespond(409, array('status' => 'error', 'code' => 'reversal_amount_changed', 'message' => 'The approved adjustment no longer supports this reversal amount.'));
				}
				$documentValue = $originalRows[0]['source_amount_basis'];
			} elseif ($decision === 'approved') {
				if ($pending[0]['adjustment_type'] !== 'value_exclusion') {
					mysqli_rollback($db);
					spRespond(409, array('status' => 'error', 'code' => 'unsupported_adjustment_type', 'message' => 'This adjustment type does not have an approved normalization rule.'));
				}
				$documentValue = spAdjustmentSourceValue($db, $pending[0]['metric_key'], $pending[0]['source_key']);
				$already = spRows($db, "SELECT CAST(COALESCE(SUM(excluded_amount),0) AS DECIMAL(24,2)) AS excluded_total FROM sales_performance_adjustments WHERE metric_key=? AND source_key=? AND adjustment_type='value_exclusion' AND status='approved'", array($pending[0]['metric_key'], $pending[0]['source_key']));
				$approvedAmount = count($already) ? (string) $already[0]['excluded_total'] : '0.00';
				$remaining = $documentValue === null ? '0.00' : \SAHamid\BI\Commercial\DecimalAmount::subtract($documentValue, $approvedAmount);
				if ($documentValue === null || \SAHamid\BI\Commercial\DecimalAmount::compare($pending[0]['excluded_amount'], $remaining) > 0) {
					mysqli_rollback($db);
					spRespond(409, array('status' => 'error', 'code' => 'source_changed', 'message' => 'The current source value no longer supports this exclusion. Refresh the request and reconcile the document before deciding.'));
				}
			}
			$affected = spExecute($db, 'UPDATE sales_performance_adjustments SET status=?, reviewed_by=?, reviewed_at=NOW(), review_note=?, source_amount_basis=COALESCE(?,source_amount_basis), source_version=\'live_source_at_review\' WHERE id=? AND status="pending"',
				array($decision, $userId, $note, isset($documentValue) ? $documentValue : null, (string) $id));
			if ($affected !== 1) throw new Exception('The adjustment could not be updated.');
			spExecute($db, "UPDATE sales_performance_normalization_locks SET revision_no=revision_no+1, updated_at=NOW() WHERE source_type=? AND source_key=? AND policy_version='normalized-value-v1'", array($pending[0]['metric_key'], $pending[0]['source_key']));
			if (!mysqli_commit($db)) throw new Exception('Could not commit the adjustment review.');
		} catch (Exception $e) {
			mysqli_rollback($db);
			throw $e;
		}
		spRespond(200, array('status' => 'success', 'message' => 'Adjustment ' . $decision . '.'));
	}

	if (!in_array($action, array('report', 'supporting_documents', 'create_run', 'get_run', 'export_run'), true)) spRespond(400, array('status' => 'error', 'code' => 'invalid_filter', 'message' => 'Unknown report action.'));
	if ($action === 'create_run') {
		spRequireFormToken();
		if (!$canCreateRun) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'You do not have permission to create frozen sales reports.'));
	}
	if (!$canViewCompany && !$canViewTeams && !$canViewSelf) {
		spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'A self, team or company sales performance viewing capability has not been granted.'));
	}
	$readiness = spSchemaReadiness($db);
	if (!$readiness['report_ready']) {
		spRespond(503, array('status' => 'error', 'code' => 'schema_not_ready', 'message' => 'Sales Performance reporting setup is incomplete.', 'readiness' => $readiness));
	}

	$from = isset($_GET['from']) ? $_GET['from'] : date('Y-m-01');
	$to = isset($_GET['to']) ? $_GET['to'] : date('Y-m-d');
	$currentPeriod = spDatePeriod($from, $to);
	$today = date('Y-m-d');
	$businessCutoff = isset($_GET['business_cutoff']) ? $_GET['business_cutoff'] : (($to < $today) ? $to : (($from > $today) ? $from : $today));
	if (!spValidDate($businessCutoff) || $businessCutoff < $from || $businessCutoff > $to || $businessCutoff > $today) {
		throw new InvalidArgumentException('The business cutoff must fall within the selected reporting period.');
	}
	$compareFrom = isset($_GET['compare_from']) ? $_GET['compare_from'] : '';
	$compareTo = isset($_GET['compare_to']) ? $_GET['compare_to'] : '';
	if ($compareFrom === '' && $compareTo === '') {
		$length = (int) floor((strtotime($to) - strtotime($from)) / 86400) + 1;
		$compareTo = date('Y-m-d', strtotime($from . ' -1 day'));
		$compareFrom = date('Y-m-d', strtotime($compareTo . ' -' . ($length - 1) . ' days'));
	}
	$comparisonPeriod = spDatePeriod($compareFrom, $compareTo);
	$elapsedDays = (int) floor((strtotime($businessCutoff) - strtotime($from)) / 86400);
	$comparisonCutoff = min($comparisonPeriod['to'], date('Y-m-d', strtotime($comparisonPeriod['from'] . ' +' . $elapsedDays . ' days')));

	$teamResult = mysqli_query($db, 'SELECT id, name, members FROM salesteam ORDER BY name');
	if (!$teamResult) throw new \SAHamid\BI\Exception\BIException('scope_unavailable', 'The reporting team list could not be read.', 503);
	$allTeamRows = array();
	while ($row = mysqli_fetch_assoc($teamResult)) $allTeamRows[] = array('id' => (int) $row['id'], 'name' => $row['name'], 'members' => $row['members']);
	$teamMembershipHistory = spHistoricalTeamMembership($db);
	foreach ($allTeamRows as &$teamRow) {
		$currentMembers = array_filter(array_map('trim', explode(',', (string) $teamRow['members'])));
		$historicMembers = isset($teamMembershipHistory['members_by_team'][(int) $teamRow['id']]) ? array_values($teamMembershipHistory['members_by_team'][(int) $teamRow['id']]) : array();
		$teamRow['members'] = implode(',', array_values(array_unique(array_merge($currentMembers, $historicMembers))));
	}
	unset($teamRow);

	$salesmanResult = mysqli_query($db, 'SELECT salesmanname FROM salesman ORDER BY salesmanname');
	if (!$salesmanResult) throw new \SAHamid\BI\Exception\BIException('scope_unavailable', 'The reporting salesperson list could not be read.', 503);
	$knownSalespeople = array();
	while ($row = mysqli_fetch_assoc($salesmanResult)) $knownSalespeople[] = $row['salesmanname'];
	$requestedPopulationKey = isset($_GET['population_scope']) && is_string($_GET['population_scope']) ? trim($_GET['population_scope']) : '';
	if ($requestedPopulationKey !== '' && $requestedPopulationKey !== 'core_sales_teams') throw new InvalidArgumentException('Choose an approved named reporting population.');
	if ($requestedPopulationKey !== '' && !$canViewCompany) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'Named reporting populations are available only within your authorized company-wide reporting access.'));
	$latestApprovedNamedScope = $canViewCompany ? spApprovedNamedScope($db) : null;
	$requestedTeamIds = spRequestList('team_ids');
	if ($requestedTeamIds !== null) {
		foreach ($requestedTeamIds as $teamId) if (!preg_match('/^\d+$/', $teamId)) throw new InvalidArgumentException('A selected reporting team is invalid.');
		$requestedTeamIds = array_map('intval', $requestedTeamIds);
	}
	$requestedSalespeople = spRequestList('salespeople');
	if ($requestedPopulationKey !== '' && (($requestedTeamIds !== null && count($requestedTeamIds) > 0) || ($requestedSalespeople !== null && count($requestedSalespeople) > 0))) {
		throw new InvalidArgumentException('Use either the approved population or direct team/person filters, not both.');
	}
	$namedPopulation = null;
	if ($requestedPopulationKey !== '') {
		if ($latestApprovedNamedScope === null) spRespond(409, array('status' => 'error', 'code' => 'named_scope_not_approved', 'message' => 'There is no approved version of this named population. Ask an administrator to propose it and a different reviewer to approve it.'));
		$namedTeamIds = spNamedScopeTeamIds($latestApprovedNamedScope);
		$namedTeamMap = array();
		foreach ($allTeamRows as $teamRow) $namedTeamMap[(int) $teamRow['id']] = $teamRow;
		if (count(array_diff($namedTeamIds, array_keys($namedTeamMap))) > 0) spRespond(409, array('status' => 'error', 'code' => 'named_scope_team_missing', 'message' => 'An approved team in this population no longer exists. Ask an administrator to review the mapping.'));
		$namedTeamRows = array();
		foreach ($namedTeamIds as $namedTeamId) $namedTeamRows[] = $namedTeamMap[$namedTeamId];
		$namedPopulation = array('scope_key' => 'core_sales_teams', 'label' => (string) $latestApprovedNamedScope['label'], 'version' => (int) $latestApprovedNamedScope['scope_version'],
			'team_ids' => $namedTeamIds, 'include_unassigned' => (int) $latestApprovedNamedScope['include_unassigned'] === 1);
	}
	$grantedTeamIds = array();
	if ($canViewTeams && !$canViewCompany) {
		$grants = spRows($db, "SELECT DISTINCT team_id FROM sales_performance_scope_grants WHERE user_id = ? AND capability = 'view_team' AND revoked_at IS NULL AND team_id IS NOT NULL", array($userId));
		foreach ($grants as $grant) $grantedTeamIds[] = (int) $grant['team_id'];
	}
	$scope = \SAHamid\BI\Commercial\Security\SalesPerformanceScope::resolve(
		$userName,
		$canViewCompany,
		$canViewTeams,
		$canViewSelf,
		$grantedTeamIds,
		$allTeamRows,
		$knownSalespeople,
		$namedPopulation === null ? $requestedTeamIds : null,
		$namedPopulation === null ? $requestedSalespeople : null
	);
	$reportTeamRows = $scope['team_rows'];
	$reportTeamBySalesman = $scope['team_by_salesperson'];
	$scopeNames = $scope['salespeople'];
	if ($namedPopulation !== null) {
		$reportTeamRows = $namedTeamRows;
		$scopeNames = null;
	} elseif ($canViewCompany && ($requestedTeamIds === null || count($requestedTeamIds) === 0) && ($requestedSalespeople === null || count($requestedSalespeople) === 0)) {
		$scopeNames = null;
	}
	$effectiveTeamIds = $namedPopulation === null ? $scope['team_ids'] : $namedPopulation['team_ids'];
	sort($effectiveTeamIds, SORT_NUMERIC);
	if (is_array($scopeNames)) sort($scopeNames, SORT_STRING);
	$effectiveScope = $namedPopulation === null
		? array('kind' => $scope['kind'], 'team_ids' => $effectiveTeamIds, 'salespeople' => $scopeNames)
		: array('kind' => 'named_population', 'scope_key' => $namedPopulation['scope_key'], 'scope_version' => $namedPopulation['version'], 'label' => $namedPopulation['label'],
			'team_ids' => $effectiveTeamIds, 'include_unassigned' => $namedPopulation['include_unassigned'], 'salespeople' => 'attributed_at_business_date');
	if ($action === 'get_run' || $action === 'export_run') {
		$runKey = isset($_GET['run_key']) && is_string($_GET['run_key']) ? $_GET['run_key'] : '';
		if (!preg_match('/^[a-f0-9-]{36}$/', $runKey)) spRespond(400, array('status' => 'error', 'code' => 'invalid_run_key', 'message' => 'Enter a valid frozen report key.'));
		$runs = spRows($db, "SELECT id, run_key, requested_by, authorized_scope_text, scope_fingerprint, totals_text, export_manifest_text, content_hash, DATE_FORMAT(ready_at,'%Y-%m-%dT%H:%i:%s') AS ready_at FROM sales_performance_report_runs WHERE run_key=? AND run_state='ready' LIMIT 1", array($runKey));
		if (!count($runs)) spRespond(404, array('status' => 'error', 'code' => 'run_not_found', 'message' => 'That frozen report was not found.'));
		$storedScope = json_decode($runs[0]['authorized_scope_text'], true);
		$authorized = $canViewCompany;
		if (!$authorized && is_array($storedScope)) {
			$currentNames = is_array($scope['salespeople']) ? $scope['salespeople'] : array();
			$currentTeams = array_map('intval', $scope['team_ids']);
			$storedNames = isset($storedScope['salespeople']) && is_array($storedScope['salespeople']) ? $storedScope['salespeople'] : array();
			$storedTeams = isset($storedScope['team_ids']) && is_array($storedScope['team_ids']) ? array_map('intval', $storedScope['team_ids']) : array();
			$authorized = count(array_diff($storedNames, $currentNames)) === 0 && count(array_diff($storedTeams, $currentTeams)) === 0;
		}
		if (!$authorized) spRespond(403, array('status' => 'error', 'code' => 'forbidden', 'message' => 'Your current reporting access does not include the frozen report scope.'));
		$manifest = json_decode((string) $runs[0]['export_manifest_text'], true);
		$integrityStatus = 'legacy_unverified';
		if (is_array($manifest) && isset($manifest['content_hash_version']) && $manifest['content_hash_version'] === 'stored_fact_rows_v1') {
			if ($action !== 'export_run' && !hash_equals((string) $runs[0]['content_hash'], spFrozenRunContentHash($db, $runs[0]['id'], $runs[0]['totals_text']))) {
				spRespond(409, array('status' => 'error', 'code' => 'run_integrity_failed', 'message' => 'The saved report evidence no longer matches its frozen content hash.'));
			}
			$integrityStatus = 'verified';
		}
		$payload = json_decode($runs[0]['totals_text'], true);
		if (!is_array($payload)) throw new Exception('The frozen report data could not be read.');
		$payload['frozen_run'] = array('run_key' => $runs[0]['run_key'], 'content_hash' => $runs[0]['content_hash'], 'state' => 'ready', 'integrity_status' => $integrityStatus, 'frozen_at' => $runs[0]['ready_at']);
		$mayReviewAdjustments = $canViewCompany && ($canManageAdjustments || $canApproveAdjustments);
		if (!$mayReviewAdjustments) {
			$payload['pending_adjustments'] = array();
			$payload['adjustment_history'] = array();
		}
		$payload['can_manage_adjustments'] = $canViewCompany && $canManageAdjustments;
		$payload['can_approve_adjustments'] = $canViewCompany && $canApproveAdjustments;
		$payload['named_reporting_scope_options'] = $canViewCompany && $latestApprovedNamedScope !== null
			? array(array('scope_key' => 'core_sales_teams', 'label' => (string) $latestApprovedNamedScope['label'], 'version' => (int) $latestApprovedNamedScope['scope_version'], 'include_unassigned' => (int) $latestApprovedNamedScope['include_unassigned']))
			: array();
		if ($canAdminSetup && $action === 'get_run') {
			$payload = array_merge($payload, array('can_admin_setup' => true), spAdminSetupPayload($db, $userId, $canApproveNamedScope));
		}
		if ($action === 'export_run') {
			$format = isset($_GET['format']) && is_string($_GET['format']) ? strtolower($_GET['format']) : '';
			if (!in_array($format, array('csv', 'xlsx', 'pdf'), true)) spRespond(400, array('status' => 'error', 'code' => 'invalid_export_format', 'message' => 'Choose CSV, Excel workbook or PDF.'));
			$factColumns = 'fact_id, metric_family, source_type, source_key, source_version, salescaseref, customer, branch, actor_user_id, entered_by_user_id, credited_salesperson_code AS credited_salesman_code, credited_team_id, attribution_basis, attribution_version, business_at, recorded_at, date_basis, raw_amount, net_amount, normalized_amount, currency, reporting_currency, tax_basis, tax_rule_version, raw_count_eligible, normalized_count_eligible, value_status, coverage_status, eligibility_status, warnings_text, normalization_references, source_evidence, calculation_version';
			$factRows = spRows($db, 'SELECT ' . $factColumns . ' FROM sales_performance_report_run_facts WHERE run_id=? ORDER BY fact_id', array((string) $runs[0]['id']));
			if ($integrityStatus === 'verified' && !hash_equals((string) $runs[0]['content_hash'], spFrozenRunContentHashFromFacts($runs[0]['totals_text'], $factRows))) {
				spRespond(409, array('status' => 'error', 'code' => 'run_integrity_failed', 'message' => 'The saved report evidence no longer matches its frozen content hash.'));
			}
			usort($factRows, function($left, $right) {
				$byDate = strcmp((string) $left['business_at'], (string) $right['business_at']);
				if ($byDate !== 0) return $byDate;
				$byMetric = strcmp((string) $left['metric_family'], (string) $right['metric_family']);
				return $byMetric !== 0 ? $byMetric : strcmp((string) $left['source_key'], (string) $right['source_key']);
			});
			foreach ($factRows as &$factRow) {
				$evidence = json_decode((string) $factRow['source_evidence'], true);
				$exportEvidence = is_array($evidence) && isset($evidence['_sales_performance_export']) && is_array($evidence['_sales_performance_export']) ? $evidence['_sales_performance_export'] : array();
				$factRow['period_role'] = isset($exportEvidence['period_role']) && $exportEvidence['period_role'] === 'comparison' ? 'comparison' : 'current';
				$factRow['credited_salesperson'] = isset($exportEvidence['credited_salesperson']) ? $exportEvidence['credited_salesperson'] : $factRow['credited_salesman_code'];
				$factRow['credited_team'] = isset($exportEvidence['credited_team']) ? $exportEvidence['credited_team'] : $factRow['credited_team_id'];
				$factRow['is_revision'] = !empty($exportEvidence['is_revision']);
				$factRow['reporting_raw_amount'] = isset($exportEvidence['reporting_raw_amount']) ? $exportEvidence['reporting_raw_amount'] : null;
				$warnings = json_decode((string) $factRow['warnings_text'], true);
				$factRow['warnings_text'] = is_array($warnings) ? implode('; ', $warnings) : (string) $factRow['warnings_text'];
				$normalization = json_decode((string) $factRow['normalization_references'], true);
				$factRow['normalization_references'] = is_array($normalization) ? implode('; ', array_map(function($reference) {
					return isset($reference['policy']) ? $reference['policy'] . (isset($reference['amount']) ? ': ' . $reference['amount'] : '') : '';
				}, $normalization)) : (string) $factRow['normalization_references'];
				$summary = array();
				foreach (array('issue_id', 'issue_sequence', 'source_hash', 'orddate', 'quotedate', 'GSTadd', 'withoutItems',
					'event_kind', 'event_type', 'response_code', 'reason_code', 'activity_type', 'outcome_code', 'quotation_orderno',
					'next_action_date', 'customer_reference', 'normalized_reference', 'erp_document_key', 'excluded_amount', 'corrected_from_id',
					'correction_of_id', 'correction_reason', 'void_reason', 'correction_snapshot') as $summaryKey) {
					if (is_array($evidence) && isset($evidence[$summaryKey])) $summary[$summaryKey] = $evidence[$summaryKey];
				}
				$factRow['source_evidence'] = json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			}
			unset($factRow);
			$exporter = new \SAHamid\BI\Commercial\Export\SalesPerformanceExporter();
			$file = $exporter->render($format, $payload, $factRows);
			$types = array('csv' => 'text/csv; charset=utf-8', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'pdf' => 'application/pdf');
			header('Content-Type: ' . $types[$format]);
			header('Content-Disposition: attachment; filename="sales-performance-' . $runKey . '.' . $format . '"');
			header('Cache-Control: private, no-store, max-age=0');
			header('Pragma: no-cache');
			header('X-Content-Type-Options: nosniff');
			echo $file;
			exit;
		}
		spRespond(200, $payload);
	}
	if ($action === 'supporting_documents') {
		$metric = isset($_GET['metric']) && is_string($_GET['metric']) ? $_GET['metric'] : '';
		$date = isset($_GET['date']) && is_string($_GET['date']) ? $_GET['date'] : '';
		$page = isset($_GET['page']) && ctype_digit((string) $_GET['page']) ? (int) $_GET['page'] : 1;
		$pageSize = isset($_GET['page_size']) && ctype_digit((string) $_GET['page_size']) ? (int) $_GET['page_size'] : 50;
		if (!in_array($metric, array('quotations', 'revised_quotations'), true)) {
			spRespond(422, array('status' => 'error', 'code' => 'metric_unavailable', 'message' => 'Supporting rows are currently available for quotation documents only.'));
		}
		if (!spValidDate($date) || $date < $currentPeriod['from'] || $date > $currentPeriod['to'] || $date > $businessCutoff) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_filter', 'message' => 'Choose a date inside the selected period and on or before the business cutoff.'));
		}
		if ($page < 1 || $page > 200 || $pageSize < 1 || $pageSize > 100) {
			spRespond(400, array('status' => 'error', 'code' => 'invalid_filter', 'message' => 'Choose a page from 1 to 200 and a page size from 1 to 100.'));
		}
		$detailRequest = \SAHamid\BI\Commercial\ReportingRequest::fromArray(array('from' => $date, 'through' => $date), $scopeNames);
		$commercialReporting = new \SAHamid\BI\Commercial\ReportingService();
		$allFacts = $commercialReporting->quotationFacts($db, $detailRequest);
		$filteredFacts = array();
		foreach ($allFacts as $fact) {
			if ($fact['metric_family'] !== $metric) continue;
			if ($namedPopulation !== null && spNamedPopulationTeamKey($fact, substr((string) $fact['business_at'], 0, 10), $reportTeamBySalesman, $teamMembershipHistory, $allTeamRows, $namedPopulation) === null) continue;
			$filteredFacts[] = $fact;
		}
		$offset = ($page - 1) * $pageSize;
		$documents = array_slice($filteredFacts, $offset, $pageSize);
		spRespond(200, array(
			'status' => 'success',
			'metric' => $metric,
			'date' => $date,
			'page' => $page,
			'page_size' => $pageSize,
			'total' => count($filteredFacts),
			'has_more' => count($filteredFacts) > $offset + count($documents),
			'effective_scope' => array('kind' => $namedPopulation === null ? $scope['kind'] : 'named_population', 'team_ids' => $effectiveTeamIds,
				'named_population' => $namedPopulation === null ? null : array('label' => $namedPopulation['label'], 'version' => $namedPopulation['version'], 'include_unassigned' => $namedPopulation['include_unassigned'])),
			'documents' => $documents
		));
	}

	$settingRows = spRows($db, "SELECT setting_value FROM sales_performance_settings WHERE setting_key = 'followup_start_date' LIMIT 1", array());
	$followupStart = count($settingRows) > 0 ? $settingRows[0]['setting_value'] : date('Y-m-d');
	$coverageScope = $canViewCompany ? 'erp_company' : ($canViewTeams ? 'core_sales_teams' : 'self');
	$reportCalendarVersion = spCurrentCalendarVersion($db);
	$calendarFrom = min($currentPeriod['from'], $comparisonPeriod['from']);
	$calendarThrough = max($currentPeriod['to'], $comparisonPeriod['to']);
	$calendarOverrides = spLoadCalendarOverrides($db, $calendarFrom, $calendarThrough, $reportCalendarVersion);
	$current = spLoadPeriod($db, $currentPeriod['from'], $currentPeriod['to'], $scopeNames, $reportTeamRows, $reportTeamBySalesman, $followupStart, $businessCutoff, $coverageScope, $teamMembershipHistory, $calendarOverrides, $namedPopulation, $allTeamRows);
	$comparison = spLoadPeriod($db, $comparisonPeriod['from'], $comparisonPeriod['to'], $scopeNames, $reportTeamRows, $reportTeamBySalesman, $followupStart, $comparisonCutoff, $coverageScope, $teamMembershipHistory, $calendarOverrides, $namedPopulation, $allTeamRows);
	$currentEnquiryStatus = spEnquiryStatus($db, $currentPeriod['from'], $businessCutoff, $scopeNames, $namedPopulation);
	$comparisonEnquiryStatus = spEnquiryStatus($db, $comparisonPeriod['from'], $comparisonCutoff, $scopeNames, $namedPopulation);
	$newUnansweredByDate = array();
	foreach ($currentEnquiryStatus['daily_new_unanswered'] as $row) $newUnansweredByDate[$row['report_date']] = (int) $row['record_count'];
	foreach ($current['daily'] as &$dailyItem) $dailyItem['unanswered_new_count'] = isset($newUnansweredByDate[$dailyItem['date']]) ? $newUnansweredByDate[$dailyItem['date']] : 0;
	unset($dailyItem);

	$followupParams = array($currentPeriod['from'], $businessCutoff);
	$followupFilter = '';
	if ($namedPopulation !== null) $followupFilter .= spNamedPopulationSnapshotSql('f.event_team_id', $namedPopulation);
	if (is_array($scopeNames)) {
		if (count($scopeNames) === 0) {
			$followups = array();
		} else {
			$followupFilter = ' AND f.salesman IN (' . implode(',', array_fill(0, count($scopeNames), '?')) . ')';
			$followupParams = array_merge($followupParams, $scopeNames);
		}
	}
	if (!isset($followups)) {
		$followups = spRows($db,
			"SELECT f.id, DATE_FORMAT(COALESCE(f.actual_activity_at, CONCAT(f.activity_date, ' 00:00:00')), '%Y-%m-%d %H:%i') AS activity_at,
				f.salesman, f.salescaseref, f.quotation_orderno, f.actor_user_id, f.entered_by_user_id,
				f.activity_type, f.outcome, f.next_action, DATE_FORMAT(f.next_action_date, '%Y-%m-%d') AS next_action_date,
				f.notes, f.created_by, f.corrected_from_id, f.voided_by, f.void_reason,
				DATE_FORMAT(f.voided_at, '%Y-%m-%d %H:%i') AS voided_at,
				EXISTS (SELECT 1 FROM sales_performance_followups replacement WHERE replacement.corrected_from_id=f.id) AS has_correction
			 FROM sales_performance_followups f
			 WHERE DATE(COALESCE(f.actual_activity_at, CONCAT(f.activity_date, ' 00:00:00'))) BETWEEN ? AND ? $followupFilter
			 ORDER BY f.actual_activity_at DESC, f.activity_date DESC, f.id DESC LIMIT 250",
			$followupParams
		);
		foreach ($followups as &$followupRow) {
			$entryUser = (string) ($followupRow['entered_by_user_id'] !== null ? $followupRow['entered_by_user_id'] : $followupRow['created_by']);
			$active = $followupRow['voided_at'] === null;
			$followupRow['can_void'] = $active && $canCaptureEvents && $entryUser === (string) $userId;
			$followupRow['can_correct'] = $active && $canCaptureEvents && $entryUser === (string) $userId;
			$followupRow['has_correction'] = (bool) $followupRow['has_correction'];
		}
		unset($followupRow);
	}

	$enquiryEventHistory = array();
	if ($canCaptureEvents) {
		$eventHistoryScope = '';
		$eventHistoryParams = array();
		if ($namedPopulation !== null) $eventHistoryScope .= spNamedPopulationSnapshotSql('e.event_team_id', $namedPopulation);
		if (is_array($scopeNames)) {
			if (count($scopeNames) === 0) $eventHistoryScope = ' AND 1=0';
			else {
				$eventHistoryScope = ' AND COALESCE(sm.salesmanname,sc.salesman) IN (' . implode(',', array_fill(0, count($scopeNames), '?')) . ')';
				$eventHistoryParams = $scopeNames;
			}
		}
		$effectiveHistory = spEffectiveEnquiryEventPredicate('e');
		$effectiveCorrection = spEffectiveEnquiryEventPredicate('active_correction');
		$effectiveLater = spEffectiveEnquiryEventPredicate('later');
		$enquiryEventHistory = spRows($db,
			"SELECT e.id,e.salescaseref,e.episode_no,e.event_type,
				DATE_FORMAT(e.business_at,'%Y-%m-%d %H:%i') AS business_at,DATE_FORMAT(e.recorded_at,'%Y-%m-%d %H:%i') AS recorded_at,
				e.actor_user_id,e.entered_by_user_id,e.credited_salesman_code,e.event_team_id,e.response_code,e.reason_code,e.notes,e.evidence_reference,
				e.corrects_event_id,e.voids_event_id,e.correction_reason,e.void_reason,
				COALESCE(sm.salesmanname,sc.salesman) AS credited_salesperson,
				IF($effectiveHistory,1,0) AS is_effective,
				(SELECT DATE_FORMAT(void_row.recorded_at,'%Y-%m-%d %H:%i') FROM sales_performance_enquiry_events void_row WHERE void_row.voids_event_id=e.id AND void_row.event_type='void' ORDER BY void_row.id DESC LIMIT 1) AS voided_at,
				(SELECT void_row.entered_by_user_id FROM sales_performance_enquiry_events void_row WHERE void_row.voids_event_id=e.id AND void_row.event_type='void' ORDER BY void_row.id DESC LIMIT 1) AS voided_by,
				(SELECT void_row.void_reason FROM sales_performance_enquiry_events void_row WHERE void_row.voids_event_id=e.id AND void_row.event_type='void' ORDER BY void_row.id DESC LIMIT 1) AS linked_void_reason,
				EXISTS (SELECT 1 FROM sales_performance_enquiry_events child WHERE child.corrects_event_id=e.id AND child.voids_event_id IS NULL
					AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events child_void WHERE child_void.voids_event_id=child.id)) AS has_correction_child,
				EXISTS (SELECT 1 FROM sales_performance_enquiry_events child WHERE child.voids_event_id=e.id) AS has_void_child,
				(SELECT active_correction.correction_reason FROM sales_performance_enquiry_events active_correction WHERE active_correction.corrects_event_id=e.id AND $effectiveCorrection ORDER BY active_correction.id DESC LIMIT 1) AS replacement_reason,
				NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events later WHERE later.salescaseref=e.salescaseref AND $effectiveLater
					AND (later.episode_no>e.episode_no OR (later.episode_no=e.episode_no AND (later.business_at>e.business_at OR (later.business_at=e.business_at AND later.id>e.id))))) AS has_later_event
			 FROM sales_performance_enquiry_events e INNER JOIN salescase sc ON sc.salescaseref=e.salescaseref
			 LEFT JOIN salesman sm ON sm.salesmancode=e.credited_salesman_code
			 WHERE 1=1 $eventHistoryScope
			 ORDER BY e.recorded_at DESC,e.id DESC LIMIT 250",
			$eventHistoryParams
		);
		foreach ($enquiryEventHistory as &$enquiryEventRow) {
			$active = (int) $enquiryEventRow['is_effective'] === 1 && $enquiryEventRow['event_type'] !== 'void';
			$hasChildren = (bool) $enquiryEventRow['has_correction_child'] || (bool) $enquiryEventRow['has_void_child'];
			$enquiryEventRow['can_correct'] = $active && !$hasChildren && $canCaptureEvents
				&& ((string) $enquiryEventRow['entered_by_user_id'] === (string) $userId || $canViewCompany);
			$enquiryEventRow['can_void'] = $active && !$hasChildren && (int) $enquiryEventRow['has_later_event'] === 0 && $canCaptureEvents;
			$enquiryEventRow['has_active_correction'] = $enquiryEventRow['replacement_reason'] !== null || ((bool) $enquiryEventRow['has_correction_child'] && (int) $enquiryEventRow['is_effective'] === 0 && !(bool) $enquiryEventRow['has_void_child']);
		}
		unset($enquiryEventRow);
	}

	$poReceiptHistory = array();
	if ($canCaptureEvents) {
		$receiptHistoryScope = '';
		$receiptHistoryParams = array();
		if ($namedPopulation !== null) $receiptHistoryScope .= spNamedPopulationSnapshotSql('r.event_team_id', $namedPopulation);
		if (is_array($scopeNames)) {
			if (count($scopeNames) === 0) $receiptHistoryScope = ' AND 1=0';
			else {
				$receiptHistoryScope = ' AND COALESCE(sm.salesmanname,sc.salesman) IN (' . implode(',', array_fill(0, count($scopeNames), '?')) . ')';
				$receiptHistoryParams = $scopeNames;
			}
		}
		$poReceiptHistory = spRows($db,
			"SELECT r.id,r.debtorno,r.branchcode,r.customer_reference_original,r.customer_reference_normalized,
				DATE_FORMAT(r.received_at,'%Y-%m-%d %H:%i') AS received_at,DATE_FORMAT(r.recorded_at,'%Y-%m-%d %H:%i') AS recorded_at,
				r.salescaseref,r.entered_by_user_id,r.credited_salesman_code,r.event_team_id,r.stated_amount,r.net_amount,r.currency,r.tax_basis,
				r.evidence_reference,r.receipt_status,r.correction_of_id,r.correction_reason,r.void_reason,r.voided_by,
				DATE_FORMAT(r.voided_at,'%Y-%m-%d %H:%i') AS voided_at,COALESCE(sm.salesmanname,sc.salesman) AS credited_salesperson,
				EXISTS (SELECT 1 FROM sales_performance_po_receipts child WHERE child.correction_of_id=r.id) AS has_correction_child
			 FROM sales_performance_po_receipts r LEFT JOIN salescase sc ON sc.salescaseref=r.salescaseref
			 LEFT JOIN salesman sm ON sm.salesmancode=r.credited_salesman_code
			 WHERE 1=1 $receiptHistoryScope ORDER BY r.recorded_at DESC,r.id DESC LIMIT 250",
			$receiptHistoryParams
		);
		$receiptIds = array();
		foreach ($poReceiptHistory as $receiptRow) $receiptIds[] = (string) $receiptRow['id'];
		$receiptBridges = array();
		if (count($receiptIds)) {
			$bridgeRows = spRows($db, 'SELECT receipt_id,source_type,source_key FROM sales_performance_po_bridges WHERE receipt_id IN (' . implode(',', array_fill(0, count($receiptIds), '?')) . ') ORDER BY id', $receiptIds);
			foreach ($bridgeRows as $bridgeRow) {
				$id = (string) $bridgeRow['receipt_id'];
				if (!isset($receiptBridges[$id])) $receiptBridges[$id] = array();
				$receiptBridges[$id][] = array('source_type' => $bridgeRow['source_type'], 'source_key' => $bridgeRow['source_key']);
			}
		}
		foreach ($poReceiptHistory as &$receiptRow) {
			$receiptRow['bridges'] = isset($receiptBridges[(string) $receiptRow['id']]) ? $receiptBridges[(string) $receiptRow['id']] : array();
			$receiptRow['can_correct'] = $receiptRow['receipt_status'] === 'active' && !(bool) $receiptRow['has_correction_child']
				&& ((string) $receiptRow['entered_by_user_id'] === (string) $userId || $canViewCompany);
			$receiptRow['can_void'] = $receiptRow['receipt_status'] === 'active' && !(bool) $receiptRow['has_correction_child'];
		}
		unset($receiptRow);
	}

	$pendingAdjustments = array();
	$adjustmentHistory = array();
	if ($canManageAdjustments || $canApproveAdjustments) {
		$pendingAdjustments = spRows($db,
			"SELECT id, metric_key, source_key, excluded_amount, reason, requested_by, adjustment_type, count_effect, policy_version, reversal_of_id, DATE_FORMAT(requested_at, '%Y-%m-%d %H:%i') AS requested_at
			 FROM sales_performance_adjustments WHERE status = 'pending' ORDER BY requested_at ASC LIMIT 100",
			array()
		);
		$adjustmentHistory = spRows($db,
			"SELECT a.id, a.metric_key, a.source_key, a.excluded_amount, a.reason, a.status, a.requested_by, a.adjustment_type, a.count_effect, a.policy_version, a.reversal_of_id,
				DATE_FORMAT(a.requested_at, '%Y-%m-%d %H:%i') AS requested_at, a.reviewed_by,
				DATE_FORMAT(a.reviewed_at, '%Y-%m-%d %H:%i') AS reviewed_at, a.review_note,
				(a.status='approved' AND a.adjustment_type='value_exclusion' AND a.reversal_of_id IS NULL
					AND NOT EXISTS (SELECT 1 FROM sales_performance_adjustments pending_rev WHERE pending_rev.reversal_of_id=a.id AND pending_rev.status='pending')
					AND NOT EXISTS (SELECT 1 FROM sales_performance_adjustments approved_rev WHERE approved_rev.reversal_of_id=a.id AND approved_rev.status='approved')) AS can_reverse
			 FROM sales_performance_adjustments a WHERE a.status <> 'pending' ORDER BY a.requested_at DESC LIMIT 100",
			array()
		);
	}

	$responsePayload = array(
		'status' => 'success',
		'meta' => array(
			'timezone' => 'Asia/Karachi',
			'from' => $currentPeriod['from'],
			'through' => $currentPeriod['to'],
			'business_cutoff' => $businessCutoff,
			'compare_from' => $comparisonPeriod['from'],
			'compare_to' => $comparisonPeriod['to'],
			'data_basis' => 'live_erp_legacy_document_proxies',
			'definition_version' => 'commercial-fact-v1-draft',
			'calendar_version' => $reportCalendarVersion,
			'validation_status' => 'provisional',
			'effective_scope' => array('kind' => $namedPopulation === null ? $scope['kind'] : 'named_population', 'team_ids' => $effectiveTeamIds,
				'salespeople' => $namedPopulation !== null ? 'attributed_at_business_date' : ($scopeNames === null ? 'all_including_unassigned' : $scopeNames),
				'named_population' => $namedPopulation === null ? null : array('scope_key' => $namedPopulation['scope_key'], 'label' => $namedPopulation['label'], 'version' => $namedPopulation['version'], 'include_unassigned' => $namedPopulation['include_unassigned'])),
			'generated_at' => date('c')
		),
		'assumptions' => array(
			'po_label' => 'Customer PO receipts are counted from captured receipt records. ERP order confirmations remain a separate legacy proxy.',
			'enquiry_date' => 'Captured receipt events replace the legacy case proxy for that case; otherwise enquiry date is used, with commencement date as fallback.',
			'enquiry_value' => 'Enquiry amounts are reported in PKR only when the customer currency is PKR; foreign currency is not converted.',
			'week' => 'Weeks start Monday; planned workdays default to Monday through Saturday and follow approved date-specific calendar overrides.',
			'team' => 'Current membership is a provisional fallback; multiply assigned and unknown owners are kept unassigned.',
			'population_coverage' => $namedPopulation === null ? 'Coverage follows the selected reporting scope.' : 'Named-population reporting days require captured coverage for every included team; unassigned activity has no separate coverage submission row.',
			'values' => 'Quotation raw values include ordinary option-0 lines and quick no-item option-0 prices. Net values follow the provisional ERP PDF GST rule; unknown classifications remain unavailable.',
			'frozen_detail' => 'Saved supporting records preserve captured events and legacy ERP proxies at their source grain. Activity-only records have no monetary amount; unavailable report-currency values remain blank.'
		),
		'followup_start_date' => $followupStart,
		'can_manage_adjustments' => $canManageAdjustments && $canViewCompany,
		'can_approve_adjustments' => $canApproveAdjustments && $canViewCompany,
		'adjustment_history' => $adjustmentHistory,
		'current' => $current,
		'comparison' => $comparison,
		'enquiry_status' => array('current' => $currentEnquiryStatus, 'comparison' => $comparisonEnquiryStatus),
		'enquiry_events' => $enquiryEventHistory,
		'po_receipts' => $poReceiptHistory,
		'followups' => $followups,
		'pending_adjustments' => $pendingAdjustments
	);
	$responsePayload['named_reporting_scope_options'] = $canViewCompany && $latestApprovedNamedScope !== null
		? array(array('scope_key' => 'core_sales_teams', 'label' => (string) $latestApprovedNamedScope['label'], 'version' => (int) $latestApprovedNamedScope['scope_version'], 'include_unassigned' => (int) $latestApprovedNamedScope['include_unassigned']))
		: array();
	if ($canAdminSetup && $action === 'report'
		&& $scope['kind'] === 'erp_company' && $scopeNames === null
		&& $namedPopulation === null
		&& ($requestedTeamIds === null || count($requestedTeamIds) === 0)
		&& ($requestedSalespeople === null || count($requestedSalespeople) === 0)) {
		if (!isset($_SESSION['sales_performance_coverage_review_key']) || !is_string($_SESSION['sales_performance_coverage_review_key'])) {
			$_SESSION['sales_performance_coverage_review_key'] = bin2hex(random_bytes(32));
		}
		$reviewKey = $_SESSION['sales_performance_coverage_review_key'];
		$issuedAt = time();
		$teamMetricsByDate = array();
		foreach ($current['teams'] as $currentTeam) {
			$teamIdForSnapshot = (int) $currentTeam['id'];
			if ($teamIdForSnapshot < 1) continue;
			foreach ($currentTeam['weeks'] as $teamWeek) {
				foreach ($teamWeek['days'] as $teamDay) {
					$teamMetricsByDate[$teamIdForSnapshot][$teamDay['date']] = $teamDay['metrics'];
				}
			}
		}
		$coverageReviewOptions = array();
		foreach ($current['daily'] as $currentDay) {
			$date = $currentDay['date'];
			if ($date > $businessCutoff) continue;
			$reviewSets = array(array('team_id' => null, 'metrics' => $currentDay['metrics'], 'unanswered_new_count' => isset($currentDay['unanswered_new_count']) ? (int) $currentDay['unanswered_new_count'] : 0));
			foreach ($reportTeamRows as $reportTeam) {
				$teamIdForSnapshot = (int) $reportTeam['id'];
				$teamMetrics = isset($teamMetricsByDate[$teamIdForSnapshot][$date]) ? $teamMetricsByDate[$teamIdForSnapshot][$date] : spEmptyMetrics();
				$reviewSets[] = array('team_id' => $teamIdForSnapshot, 'metrics' => $teamMetrics, 'unanswered_new_count' => null);
			}
			foreach ($reviewSets as $reviewSet) {
				$reviewMetrics = array('metrics' => $reviewSet['metrics'], 'unanswered_new_count' => $reviewSet['unanswered_new_count']);
				$scopeKeyForSnapshot = 'erp_company';
				$context = spCoverageReviewContext($reviewMetrics, $date, $scopeKeyForSnapshot, $reviewSet['team_id'], $userId,
					$currentPeriod['from'], $currentPeriod['to'], $businessCutoff, $reportCalendarVersion, $issuedAt, $effectiveTeamIds);
				list($contextJson, $signature) = spCoverageReviewSignature($context, $reviewKey);
				$coverageReviewOptions[] = array('date' => $date, 'scope_key' => $scopeKeyForSnapshot, 'team_id' => $reviewSet['team_id'],
					'context_json' => $contextJson, 'signature' => $signature);
			}
		}
		$responsePayload['coverage_review_options'] = $coverageReviewOptions;
	}
	if ($action === 'create_run') {
		$freezeThrough = min($businessCutoff, $currentPeriod['to']);
		$quoteRequest = \SAHamid\BI\Commercial\ReportingRequest::fromArray(array('from' => $currentPeriod['from'], 'through' => $freezeThrough), $scopeNames);
		$frozenQuoteFacts = (new \SAHamid\BI\Commercial\ReportingService())->quotationFacts($db, $quoteRequest);
		$frozenActivityFacts = (new \SAHamid\BI\Commercial\Source\RunFactSource())->fetch($db, $currentPeriod['from'], $freezeThrough, $scopeNames);
		$frozenComparisonThrough = min($comparisonCutoff, $comparisonPeriod['to']);
		$comparisonQuoteRequest = \SAHamid\BI\Commercial\ReportingRequest::fromArray(array('from' => $comparisonPeriod['from'], 'through' => $frozenComparisonThrough), $scopeNames);
		$frozenComparisonQuoteFacts = (new \SAHamid\BI\Commercial\ReportingService())->quotationFacts($db, $comparisonQuoteRequest);
		$frozenComparisonActivityFacts = (new \SAHamid\BI\Commercial\Source\RunFactSource())->fetch($db, $comparisonPeriod['from'], $frozenComparisonThrough, $scopeNames);
		$frozenFacts = array();
		foreach (array_merge($frozenQuoteFacts, $frozenActivityFacts) as $fact) {
			$fact['period_role'] = 'current';
			$frozenFacts[] = $fact;
		}
		foreach (array_merge($frozenComparisonQuoteFacts, $frozenComparisonActivityFacts) as $fact) {
			$fact['period_role'] = 'comparison';
			$frozenFacts[] = $fact;
		}
		spAssignFrozenFactTeams($frozenFacts, $reportTeamBySalesman, $teamMembershipHistory, $namedPopulation === null ? $reportTeamRows : $allTeamRows);
		if ($namedPopulation !== null) {
			$filteredFrozenFacts = array();
			foreach ($frozenFacts as $fact) {
				$date = isset($fact['business_at']) ? substr((string) $fact['business_at'], 0, 10) : '';
				if ($date === '') continue;
				$populationTeamKey = spNamedPopulationTeamKey($fact, $date, $reportTeamBySalesman, $teamMembershipHistory, $allTeamRows, $namedPopulation);
				if ($populationTeamKey === null) continue;
				$fact['credited_team_id'] = preg_match('/^t([0-9]+)$/', $populationTeamKey, $teamMatch) ? (int) $teamMatch[1] : null;
				$fact['credited_team'] = $fact['credited_team_id'];
				$filteredFrozenFacts[] = $fact;
			}
			$frozenFacts = $filteredFrozenFacts;
		}
		$databaseRows = spRows($db, 'SELECT DATABASE() AS database_name', array());
		$databaseName = count($databaseRows) ? $databaseRows[0]['database_name'] : 'unknown';
		$frozen = spCreateFrozenRun($db, $userId, isset($_SESSION['CompanyName']) ? $_SESSION['CompanyName'] : $databaseName, $databaseName,
			$effectiveScope, array('from' => $currentPeriod['from'], 'to' => $currentPeriod['to'], 'business_cutoff' => $businessCutoff,
				'compare_from' => $comparisonPeriod['from'], 'compare_to' => $comparisonPeriod['to'], 'comparison_cutoff' => $comparisonCutoff,
				'team_ids' => $requestedTeamIds, 'salespeople' => $requestedSalespeople, 'population_scope' => $requestedPopulationKey),
			$businessCutoff, $reportCalendarVersion, $responsePayload, $frozenFacts);
		$responsePayload['frozen_run'] = $frozen;
	}
	if ($canAdminSetup) {
		$responsePayload = array_merge($responsePayload, array('can_admin_setup' => true), spAdminSetupPayload($db, $userId, $canApproveNamedScope));
	}
	spRespond(200, $responsePayload);
	} catch (\SAHamid\BI\Exception\BIException $e) {
		spRespond($e->getHttpStatus(), array('status' => 'error', 'code' => $e->getErrorCode(), 'message' => $e->getMessage(), 'details' => $e->getDetails()));
} catch (InvalidArgumentException $e) {
	spRespond(400, array('status' => 'error', 'code' => 'invalid_filter', 'message' => $e->getMessage()));
} catch (Exception $e) {
	error_log('Sales performance report: ' . $e->getMessage());
	spRespond(500, array('status' => 'error', 'code' => 'source_query_failed', 'message' => 'The report could not be loaded. Check reporting setup, then contact support if the issue continues.'));
}
?>
