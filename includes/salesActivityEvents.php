<?php
// Sales Performance events recorded automatically as ERP documents change:
//   case created            -> enquiry received
//   quotation saved          -> response (quotation issued), first one per open episode
//   case closed              -> outcome, from the closing reason
//   case reopened            -> reopen (starts the next episode)
//   customer PO uploaded     -> customer PO receipt
//
// Every function is safe to call from an ERP page. A failure is logged and never stops the page.
// Keys are deterministic, so a retry or a second save cannot record the same event twice.

function saActLog($message) {
	error_log('[sales-activity-events] ' . $message);
}

function saActTableExists($db) {
	static $exists = null;
	if ($exists === null) {
		$result = mysqli_query($db, "SHOW TABLES LIKE 'sales_performance_enquiry_events'");
		$exists = $result && mysqli_num_rows($result) > 0;
	}
	return $exists;
}

function saActQuery($db, $sql, array $params = array()) {
	$stmt = mysqli_prepare($db, $sql);
	if (!$stmt) throw new Exception('prepare failed: ' . mysqli_error($db));
	if (count($params) > 0) {
		$bind = array(str_repeat('s', count($params)));
		foreach ($params as $index => $value) $bind[] = &$params[$index];
		call_user_func_array(array($stmt, 'bind_param'), $bind);
	}
	if (!mysqli_stmt_execute($stmt)) throw new Exception('execute failed: ' . mysqli_stmt_error($stmt));
	$rows = array();
	$result = mysqli_stmt_get_result($stmt);
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
		mysqli_free_result($result);
	}
	mysqli_stmt_close($stmt);
	return $rows;
}

// Same attribution rule as the Sales Performance API: team membership in effect on the date, otherwise the single current team.
function saActAttribution($db, $salesman, $date) {
	$codeRows = saActQuery($db, 'SELECT salesmancode FROM salesman WHERE salesmanname = ? LIMIT 1', array($salesman));
	$code = count($codeRows) ? $codeRows[0]['salesmancode'] : null;
	if ($code !== null) {
		$history = saActQuery($db, 'SELECT team_id FROM sales_performance_team_membership WHERE salesmancode=? ORDER BY valid_from, id', array($code));
		if (count($history) > 0) {
			$effective = saActQuery($db, 'SELECT team_id FROM sales_performance_team_membership WHERE salesmancode=? AND valid_from<=? AND (valid_to IS NULL OR valid_to>?) ORDER BY valid_from, id', array($code, $date, $date));
			return array('salesman_code' => $code, 'team_id' => count($effective) === 1 ? $effective[0]['team_id'] : null, 'basis' => count($effective) === 1 ? 'history' : 'history_gap_or_overlap');
		}
	}
	$teamRows = saActQuery($db, 'SELECT id FROM salesteam WHERE FIND_IN_SET(?, REPLACE(members, ", ", ",")) > 0 ORDER BY id', array($salesman));
	return array('salesman_code' => $code, 'team_id' => count($teamRows) === 1 ? $teamRows[0]['id'] : null, 'basis' => count($teamRows) === 1 ? 'current_fallback' : 'current_overlap_or_unmapped');
}

// Maps a closing reason from the ERP lookup lists to a Sales Performance outcome.
// Defaults agreed for rollout; change the table here to re-map.
function saActClosureOutcome($reason) {
	$clean = trim(str_replace(array('\\n', "\n", "\r"), '', (string) $reason));
	$map = array(
		'Customer has cancelled the enquiry' => array('cancellation', false),
		'Customer cancelled PO' => array('cancellation', false),
		'Duplicate Case' => array('duplicate', false),
		"Customer doesn't want in equal" => array('customer_rejection', false),
		'high prices' => array('customer_rejection', false),
		'Delivery time offered is not acceptable by customer' => array('customer_rejection', false),
		'PO not as per quotation' => array('customer_rejection', false),
		'Rate different in PO' => array('customer_rejection', false),
		'Out of scope' => array('company_decline', true),
		'internal cross' => array('company_decline', true),
		'external cross' => array('company_decline', true),
		'Wrong DBA' => array('cancellation', true),
		'Wrong Customer' => array('cancellation', true),
		'Other Reason' => array('cancellation', true),
		'any other reason' => array('cancellation', true),
		'Case Closed' => array('success', false),
	);
	if ($clean === '') return array('cancellation', 'Not recorded');
	if (isset($map[$clean])) return array($map[$clean][0], $map[$clean][1] ? substr($clean, 0, 40) : null);
	return array('cancellation', substr($clean, 0, 40));
}

// Records one enquiry event if the episode rules allow it. Returns true when a new event was written.
function saActRecordEnquiry($db, $caseRef, $eventType, $actorId, $responseCode = null, $reasonCode = null) {
	$cases = saActQuery($db, 'SELECT salescaseref, salesman FROM salescase WHERE salescaseref=? LIMIT 1', array($caseRef));
	if (!count($cases)) return false;
	$events = saActQuery($db,
		"SELECT e.id, e.episode_no, e.event_type FROM sales_performance_enquiry_events e
		 WHERE e.salescaseref=? AND e.event_type<>'void'
		   AND NOT EXISTS (SELECT 1 FROM sales_performance_enquiry_events v WHERE v.voids_event_id=e.id AND v.event_type='void')
		 ORDER BY e.episode_no, e.business_at, e.id",
		array($caseRef));
	$terminal = array('company_decline', 'customer_rejection', 'cancellation', 'duplicate', 'success');
	$last = count($events) ? $events[count($events) - 1] : null;
	$lastType = $last ? $last['event_type'] : null;
	$episode = $last ? (int) $last['episode_no'] : 0;
	$open = $last !== null && !in_array($lastType, $terminal, true);
	$episodeTypes = array();
	foreach ($events as $event) if ((int) $event['episode_no'] === $episode) $episodeTypes[] = $event['event_type'];
	$targetEpisode = $episode;
	if ($eventType === 'received') {
		if ($last !== null) return false;
		$targetEpisode = 1;
	} elseif ($eventType === 'reopen') {
		if ($last === null || $open) return false;
		$targetEpisode = $episode + 1;
	} elseif ($eventType === 'response') {
		if (!$open || !in_array($lastType, array('received', 'reopen'), true) || in_array('response', $episodeTypes, true)) return false;
	} elseif (in_array($eventType, $terminal, true)) {
		if (!$open) return false;
	} else {
		return false;
	}
	$key = 'auto-' . substr(sha1($caseRef . '|' . $targetEpisode . '|' . $eventType), 0, 40);
	if (count(saActQuery($db, 'SELECT id FROM sales_performance_enquiry_events WHERE idempotency_key=? LIMIT 1', array($key)))) return false;
	$attribution = saActAttribution($db, $cases[0]['salesman'], date('Y-m-d'));
	$teamId = $attribution['team_id'] === null ? null : (string) $attribution['team_id'];
	saActQuery($db,
		"INSERT INTO sales_performance_enquiry_events (salescaseref, episode_no, event_type, business_at, recorded_at, actor_user_id, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, response_code, reason_code, idempotency_key)
		 VALUES (?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?)",
		array($caseRef, (string) $targetEpisode, $eventType, (string) $actorId, (string) $actorId, $attribution['salesman_code'], $teamId,
			'auto_erp@' . $attribution['basis'], $responseCode, $reasonCode, $key));
	return true;
}

function saActCaseOpened($db, $caseRef, $actorId) {
	try {
		if (saActTableExists($db)) saActRecordEnquiry($db, $caseRef, 'received', $actorId);
	} catch (Throwable $e) {
		saActLog('case opened ' . $caseRef . ': ' . $e->getMessage());
	}
}

function saActQuotationSaved($db, $caseRef, $actorId) {
	try {
		if (saActTableExists($db) && $caseRef !== '') saActRecordEnquiry($db, $caseRef, 'response', $actorId, 'quotation_issued', null);
	} catch (Throwable $e) {
		saActLog('quotation saved ' . $caseRef . ': ' . $e->getMessage());
	}
}

function saActCaseClosed($db, $caseRef, $closingReason, $actorId) {
	try {
		if (!saActTableExists($db)) return;
		// Only record a closure the ERP actually saved.
		$state = saActQuery($db, 'SELECT closed FROM salescase WHERE salescaseref=? LIMIT 1', array($caseRef));
		if (!count($state) || (int) $state[0]['closed'] !== 1) return;
		list($eventType, $reasonCode) = saActClosureOutcome($closingReason);
		saActRecordEnquiry($db, $caseRef, $eventType, $actorId, null, $reasonCode);
	} catch (Throwable $e) {
		saActLog('case closed ' . $caseRef . ': ' . $e->getMessage());
	}
}

function saActCaseReopened($db, $caseRef, $actorId) {
	try {
		if (!saActTableExists($db)) return;
		$state = saActQuery($db, 'SELECT closed FROM salescase WHERE salescaseref=? LIMIT 1', array($caseRef));
		if (!count($state) || (int) $state[0]['closed'] !== 0) return;
		saActRecordEnquiry($db, $caseRef, 'reopen', $actorId);
	} catch (Throwable $e) {
		saActLog('case reopened ' . $caseRef . ': ' . $e->getMessage());
	}
}

// Records the customer's PO when the PO document is uploaded to a case. Repeats of the same customer reference are skipped.
function saActPoReceived($db, $caseRef, $reference, $actorId) {
	try {
		if (!saActTableExists($db)) return;
		$normalized = strtoupper(preg_replace('/\s+/', ' ', trim((string) $reference)));
		if ($normalized === '') return;
		$cases = saActQuery($db, 'SELECT salescaseref, salesman, debtorno, branchcode FROM salescase WHERE salescaseref=? LIMIT 1', array($caseRef));
		if (!count($cases)) return;
		$case = $cases[0];
		$duplicate = saActQuery($db,
			"SELECT id FROM sales_performance_po_receipts WHERE debtorno=? AND branchcode=? AND customer_reference_normalized=? AND receipt_status='active' LIMIT 1",
			array($case['debtorno'], $case['branchcode'], $normalized));
		if (count($duplicate)) return;
		$attribution = saActAttribution($db, $case['salesman'], date('Y-m-d'));
		$teamId = $attribution['team_id'] === null ? null : (string) $attribution['team_id'];
		$key = 'auto-po-' . substr(sha1($caseRef . '|' . $normalized . '|' . microtime(true)), 0, 40);
		saActQuery($db,
			"INSERT INTO sales_performance_po_receipts (debtorno, branchcode, customer_reference_original, customer_reference_normalized, received_at, recorded_at, salescaseref, entered_by_user_id, credited_salesman_code, event_team_id, attribution_basis, tax_basis, receipt_status, idempotency_key)
			 VALUES (?, ?, ?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, 'unknown', 'active', ?)",
			array($case['debtorno'], $case['branchcode'], substr(trim((string) $reference), 0, 150), substr($normalized, 0, 150), $caseRef, (string) $actorId,
				$attribution['salesman_code'], $teamId, 'auto_erp@' . $attribution['basis'], $key));
		$lockKey = hash('sha256', $case['debtorno'] . '|' . $case['branchcode'] . '|' . $normalized);
		saActQuery($db, "INSERT IGNORE INTO sales_performance_normalization_locks (source_type, source_key, policy_version, revision_no, updated_at) VALUES ('po_receipt', ?, 'normalized-value-v1', 0, NOW())", array($lockKey));
	} catch (Throwable $e) {
		saActLog('po received ' . $caseRef . ': ' . $e->getMessage());
	}
}
