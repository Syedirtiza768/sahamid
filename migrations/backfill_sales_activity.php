<?php
/*
 * Backfill Sales Performance events from existing ERP history.
 *
 * CLI only. Every backfilled row is marked entered_by_user_id / actor_user_id = 'backfill' and
 * attribution_basis = 'backfill@...'. Closing reasons are used as recorded in the database.
 *
 * Re-running is safe: every row has a fixed idempotency key, and existing keys are skipped.
 *
 *   php migrations/backfill_sales_activity.php --dry-run
 *   php migrations/backfill_sales_activity.php --phase=enquiry|po|quotes|all
 *
 * Connection: SA_DB_HOST, SA_DB_USER, SA_DB_PASS, SA_DB_NAME (default database: sahamid).
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(403);
	exit("CLI only\n");
}

require_once __DIR__ . '/../includes/salesActivityEvents.php';

$options = getopt('', array('dry-run', 'phase::'));
$dryRun = array_key_exists('dry-run', $options);
$phase = isset($options['phase']) ? $options['phase'] : 'all';
if (!in_array($phase, array('enquiry', 'po', 'quotes', 'all'), true)) {
	fwrite(STDERR, "Unknown --phase. Use enquiry, po, quotes or all.\n");
	exit(2);
}

$db = mysqli_connect(getenv('SA_DB_HOST') ?: 'localhost', getenv('SA_DB_USER') ?: 'root', getenv('SA_DB_PASS') ?: '', getenv('SA_DB_NAME') ?: 'sahamid');
if (!$db) {
	fwrite(STDERR, 'Connection failed: ' . mysqli_connect_error() . "\n");
	exit(1);
}
mysqli_set_charset($db, 'utf8');

$backfillActor = 'backfill';
$nowText = date('Y-m-d H:i:s');

function bfLog($message) {
	fwrite(STDERR, '[' . date('H:i:s') . '] ' . $message . "\n");
}

function bfSql($db, $value) {
	return $value === null ? 'NULL' : "'" . mysqli_real_escape_string($db, (string) $value) . "'";
}

// Returns a usable 'Y-m-d H:i:s' value, or null for empty and zero dates.
function bfTimestamp($value) {
	if ($value === null) return null;
	$value = trim((string) $value);
	if ($value === '' || strpos($value, '0000') === 0) return null;
	return strlen($value) === 10 ? $value . ' 00:00:00' : $value;
}

function bfInsertIgnore($db, $table, array $columns, array $rows) {
	$inserted = 0;
	foreach (array_chunk($rows, 1000) as $chunk) {
		$tuples = array();
		foreach ($chunk as $row) {
			$tuples[] = '(' . implode(', ', array_map(function ($value) use ($db) { return bfSql($db, $value); }, $row)) . ')';
		}
		$sql = 'INSERT IGNORE INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES ' . implode(', ', $tuples);
		if (!mysqli_query($db, $sql)) throw new Exception('Insert into ' . $table . ' failed: ' . mysqli_error($db));
		$inserted += mysqli_affected_rows($db);
	}
	return $inserted;
}

// Team attribution as the live API does it: membership in effect on the event date, otherwise the single team the name belongs to.
function bfAttribution(array $memberships, array $teamMembers, array $codeByName, $salesman, $date) {
	$code = isset($codeByName[$salesman]) ? $codeByName[$salesman] : null;
	if ($code !== null && isset($memberships[$code])) {
		$history = $memberships[$code];
		$effective = array();
		foreach ($history as $row) {
			if ($row['valid_from'] <= $date && ($row['valid_to'] === null || $row['valid_to'] > $date)) $effective[] = $row['team_id'];
		}
		return array('code' => $code, 'team' => count($effective) === 1 ? (string) $effective[0] : null,
			'basis' => count($effective) === 1 ? 'history' : 'history_gap_or_overlap');
	}
	$teams = array();
	foreach ($teamMembers as $teamId => $members) {
		if (in_array($salesman, $members, true)) $teams[] = (string) $teamId;
	}
	return array('code' => $code, 'team' => count($teams) === 1 ? $teams[0] : null,
		'basis' => count($teams) === 1 ? 'current_fallback' : 'current_overlap_or_unmapped');
}

$summary = array();

// Shared reference data for enquiry and PO phases.
$codeByName = array();
$result = mysqli_query($db, 'SELECT salesmanname, salesmancode FROM salesman');
while ($row = mysqli_fetch_assoc($result)) $codeByName[$row['salesmanname']] = $row['salesmancode'];

$memberships = array();
$result = mysqli_query($db, 'SELECT salesmancode, team_id, valid_from, valid_to FROM sales_performance_team_membership ORDER BY valid_from, id');
while ($row = mysqli_fetch_assoc($result)) $memberships[$row['salesmancode']][] = $row;

$teamMembers = array();
$result = mysqli_query($db, 'SELECT id, members FROM salesteam');
while ($row = mysqli_fetch_assoc($result)) {
	$teamMembers[(int) $row['id']] = array_map('trim', explode(',', (string) $row['members']));
}

// ---------------------------------------------------------------------------
// Enquiry events: received, response and outcome for every case.
// ---------------------------------------------------------------------------
if ($phase === 'enquiry' || $phase === 'all') {
	bfLog('Enquiry phase: reading cases');
	$firstQuote = array();
	$result = mysqli_query($db, "SELECT salescaseref, MIN(orddate) AS first_quote FROM salesorders WHERE quotation=1 AND salescaseref<>'' AND orddate IS NOT NULL AND orddate<>'0000-00-00' GROUP BY salescaseref");
	while ($row = mysqli_fetch_assoc($result)) $firstQuote[$row['salescaseref']] = bfTimestamp($row['first_quote']);

	$columns = array('salescaseref', 'episode_no', 'event_type', 'business_at', 'recorded_at', 'actor_user_id', 'entered_by_user_id',
		'credited_salesman_code', 'event_team_id', 'attribution_basis', 'response_code', 'reason_code', 'idempotency_key');
	$pending = array();
	$counts = array('received' => 0, 'response' => 0, 'outcome' => 0, 'skipped_no_date' => 0);
	$outcomeCounts = array();
	$inserted = 0;

	$cases = mysqli_query($db, 'SELECT salescaseref, salesman, commencementdate, closed, closingdate, closingreason FROM salescase ORDER BY commencementdate, salescaseref');
	while ($case = mysqli_fetch_assoc($cases)) {
		$received = bfTimestamp($case['commencementdate']);
		if ($received === null) { $counts['skipped_no_date']++; continue; }
		$caseRef = $case['salescaseref'];
		$episode = '1';
		$events = array(array('received', $received, null, null));
		$last = $received;

		if (isset($firstQuote[$caseRef])) {
			$response = max($firstQuote[$caseRef], $received);
			$events[] = array('response', $response, 'quotation_issued', null);
			$last = $response;
		}
		if ((int) $case['closed'] === 1) {
			$closed = bfTimestamp($case['closingdate']);
			if ($closed === null || $closed < $last) $closed = $last;
			list($outcomeType, $reasonCode) = saActClosureOutcome($case['closingreason']);
			$events[] = array($outcomeType, $closed, null, $reasonCode);
			$outcomeCounts[$outcomeType] = isset($outcomeCounts[$outcomeType]) ? $outcomeCounts[$outcomeType] + 1 : 1;
		}

		foreach ($events as $event) {
			list($type, $when, $responseCode, $reasonCode) = $event;
			$attribution = bfAttribution($memberships, $teamMembers, $codeByName, $case['salesman'], substr($when, 0, 10));
			$key = 'auto-' . substr(sha1($caseRef . '|' . $episode . '|' . $type), 0, 40);
			$pending[] = array($caseRef, $episode, $type, $when, $nowText, $backfillActor, $backfillActor,
				$attribution['code'], $attribution['team'], 'backfill@' . $attribution['basis'], $responseCode, $reasonCode, $key);
			if ($type === 'received') $counts['received']++;
			elseif ($type === 'response') $counts['response']++;
			else $counts['outcome']++;
		}

		if (count($pending) >= 5000) {
			if (!$dryRun) $inserted += bfInsertIgnore($db, 'sales_performance_enquiry_events', $columns, $pending);
			$pending = array();
		}
	}
	if (!$dryRun && count($pending)) $inserted += bfInsertIgnore($db, 'sales_performance_enquiry_events', $columns, $pending);

	$summary['enquiry'] = $counts + array('outcomes_by_type' => $outcomeCounts, 'inserted' => $dryRun ? 'dry run' : $inserted);
	bfLog('Enquiry phase done');
}

// ---------------------------------------------------------------------------
// Customer POs: every PO number recorded on a case, dated at the case's PO upload date.
// The PO upload date is only kept for the latest upload, so older POs on the same case share it.
// ---------------------------------------------------------------------------
if ($phase === 'po' || $phase === 'all') {
	bfLog('PO phase: reading PO records');
	$existing = array();
	$result = mysqli_query($db, "SELECT debtorno, branchcode, customer_reference_normalized FROM sales_performance_po_receipts WHERE receipt_status='active'");
	while ($row = mysqli_fetch_assoc($result)) $existing[$row['debtorno'] . '|' . $row['branchcode'] . '|' . $row['customer_reference_normalized']] = true;

	$poColumns = array('debtorno', 'branchcode', 'customer_reference_original', 'customer_reference_normalized', 'received_at', 'recorded_at',
		'salescaseref', 'entered_by_user_id', 'credited_salesman_code', 'event_team_id', 'attribution_basis', 'tax_basis', 'receipt_status', 'idempotency_key');
	$lockColumns = array('source_type', 'source_key', 'policy_version', 'revision_no', 'updated_at');
	$poRows = array();
	$lockRows = array();
	$counts = array('po_rows' => 0, 'skipped_duplicate' => 0, 'skipped_no_date' => 0, 'skipped_blank' => 0);
	$inserted = 0;
	$lockInserted = 0;

	$result = mysqli_query($db,
		"SELECT p.salescaseref, p.pono, c.debtorno, c.branchcode, c.salesman, c.podate, c.commencementdate
		 FROM salescasepo p INNER JOIN salescase c ON c.salescaseref = p.salescaseref
		 WHERE p.pono IS NOT NULL AND TRIM(p.pono) <> '' AND p.pono <> 'Not Set Yet'
		 ORDER BY p.salescaseref, p.pocount, p.salescasepoindex");
	while ($row = mysqli_fetch_assoc($result)) {
		$normalized = strtoupper(preg_replace('/\s+/', ' ', trim($row['pono'])));
		if ($normalized === '') { $counts['skipped_blank']++; continue; }
		$when = bfTimestamp($row['podate']);
		if ($when === null) $when = bfTimestamp($row['commencementdate']);
		if ($when === null) { $counts['skipped_no_date']++; continue; }
		$duplicateKey = $row['debtorno'] . '|' . $row['branchcode'] . '|' . $normalized;
		if (isset($existing[$duplicateKey])) { $counts['skipped_duplicate']++; continue; }
		$existing[$duplicateKey] = true;

		$attribution = bfAttribution($memberships, $teamMembers, $codeByName, $row['salesman'], substr($when, 0, 10));
		$key = 'auto-po-' . substr(sha1($row['salescaseref'] . '|' . $normalized), 0, 40);
		$poRows[] = array($row['debtorno'], $row['branchcode'], substr(trim($row['pono']), 0, 150), substr($normalized, 0, 150), $when, $nowText,
			$row['salescaseref'], $backfillActor, $attribution['code'], $attribution['team'], 'backfill@' . $attribution['basis'], 'unknown', 'active', $key);
		$lockRows[] = array('po_receipt', hash('sha256', $duplicateKey), 'normalized-value-v1', 0, $nowText);
		$counts['po_rows']++;

		if (count($poRows) >= 5000) {
			if (!$dryRun) {
				$inserted += bfInsertIgnore($db, 'sales_performance_po_receipts', $poColumns, $poRows);
				$lockInserted += bfInsertIgnore($db, 'sales_performance_normalization_locks', $lockColumns, $lockRows);
			}
			$poRows = array();
			$lockRows = array();
		}
	}
	if (!$dryRun && count($poRows)) {
		$inserted += bfInsertIgnore($db, 'sales_performance_po_receipts', $poColumns, $poRows);
		$lockInserted += bfInsertIgnore($db, 'sales_performance_normalization_locks', $lockColumns, $lockRows);
	}
	$summary['po'] = $counts + array('inserted' => $dryRun ? 'dry run' : $inserted);
	bfLog('PO phase done');
}

// ---------------------------------------------------------------------------
// Quotation issues: one snapshot per quotation, dated at the quotation date, in date order so sequences follow time.
// Source facts are read once per calendar year, and line items are loaded in batches of quotations, because a per-quotation
// read scans the whole options table. Same rules and hashes as the live hook. Each quotation is its own transaction.
if ($phase === 'quotes' || $phase === 'all') {
	bfLog('Quotation phase: reading quotations');
	require_once __DIR__ . '/../bi/bootstrap.php';
	$source = new \SAHamid\BI\Commercial\Source\QuotationSource();
	$result = mysqli_query($db, "SELECT orderno, orddate FROM salesorders WHERE quotation=1 AND salescaseref<>'' AND orddate IS NOT NULL AND orddate<>'0000-00-00' ORDER BY orddate, orderno");
	$rows = array();
	while ($row = mysqli_fetch_assoc($result)) {
		if (ctype_digit((string) $row['orderno'])) $rows[] = $row;
	}
	$total = count($rows);
	$attempted = 0;
	$errors = 0;
	$skippedNoFact = 0;
	$started = microtime(true);
	$lastReported = 0;

	$yearStart = 0;
	while ($yearStart < $total) {
		$year = substr($rows[$yearStart]['orddate'], 0, 4);
		$yearEnd = $yearStart;
		while ($yearEnd < $total && substr($rows[$yearEnd]['orddate'], 0, 4) === $year) $yearEnd++;

		if ($dryRun) {
			$attempted += $yearEnd - $yearStart;
		} else {
			$factsByKey = array();
			foreach ($source->fetch($db, $year . '-01-01', $year . '-12-31') as $fact) $factsByKey[(string) $fact['source_key']] = $fact;

			for ($batchStart = $yearStart; $batchStart < $yearEnd; $batchStart += 300) {
				$batchEnd = min($batchStart + 300, $yearEnd);
				$normalOrders = array();
				for ($i = $batchStart; $i < $batchEnd; $i++) {
					$orderNo = (string) $rows[$i]['orderno'];
					if (isset($factsByKey[$orderNo]) && $factsByKey[$orderNo]['source_type'] !== 'quick_quotation_option_zero') $normalOrders[] = $orderNo;
				}
				// Line items for the batch, in one query. Same columns and order as the per-document query in snapshotFromFact().
				$linesByOrder = array();
					if (count($normalOrders)) {
						// Same rows as the per-document join in snapshotFromFact(): detail lines (ordered by line) with the option
						// quantity of their option 0. The two reads avoid joining the unindexed options table, which is slow for large batches.
						$placeholders = implode(',', array_fill(0, count($normalOrders), '?'));
						$typesAndBind = function ($sql) use ($db, $normalOrders, $placeholders) {
							$stmt = mysqli_prepare($db, str_replace('{IN}', $placeholders, $sql));
							if (!$stmt) throw new Exception('Line query could not be prepared: ' . mysqli_error($db));
							$params = $normalOrders;
							$bind = array(str_repeat('s', count($params)));
							foreach ($params as $index => $value) $bind[] = &$params[$index];
							call_user_func_array(array($stmt, 'bind_param'), $bind);
							if (!mysqli_stmt_execute($stmt)) throw new Exception('Line query failed: ' . mysqli_stmt_error($stmt));
							$result = mysqli_stmt_get_result($stmt);
							$rows = array();
							while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
							mysqli_stmt_close($stmt);
							return $rows;
						};
						$quantities = array();
						foreach ($typesAndBind('SELECT orderno, lineno, quantity FROM salesorderoptions WHERE orderno IN ({IN}) AND optionno=0') as $option) {
							$quantities[(string) $option['orderno'] . '|' . (string) $option['lineno']][] = $option['quantity'];
						}
						foreach ($typesAndBind('SELECT d.* FROM salesorderdetails d WHERE d.orderno IN ({IN}) AND d.lineoptionno=0 ORDER BY d.orderno, d.orderlineno, d.salesorderdetailsindex') as $detail) {
							$key = (string) $detail['orderno'] . '|' . (string) $detail['orderlineno'];
							if (!isset($quantities[$key])) continue;
							foreach ($quantities[$key] as $quantity) {
								$line = $detail;
								$line['selected_option_quantity'] = $quantity;
								$linesByOrder[(string) $detail['orderno']][] = $line;
							}
						}
					}

			$attempted += $batchEnd - $batchStart;
			// One transaction per batch of quotations (one commit per 300 instead of one per quotation).
			// If the batch fails, it is undone and redone one quotation at a time, so a single bad quotation cannot undo the rest.
			try {
				if (!mysqli_begin_transaction($db)) throw new Exception('Could not start the batch transaction.');
				for ($i = $batchStart; $i < $batchEnd; $i++) {
					$orderNo = (string) $rows[$i]['orderno'];
					if (!isset($factsByKey[$orderNo])) { $skippedNoFact++; continue; }
					$lines = in_array($orderNo, $normalOrders, true) ? (isset($linesByOrder[$orderNo]) ? $linesByOrder[$orderNo] : array()) : null;
					$snapshot = $source->snapshotFromFact($db, $orderNo, $factsByKey[$orderNo], $lines);
					saActRecordQuotationIssue($db, $orderNo, $backfillActor, bfTimestamp($rows[$i]['orddate']), $snapshot, false);
				}
				if (!mysqli_commit($db)) throw new Exception('Batch commit failed.');
			} catch (Throwable $batchError) {
				mysqli_rollback($db);
				bfLog('batch from quotation ' . $rows[$batchStart]['orderno'] . ' redone one by one: ' . $batchError->getMessage());
				for ($i = $batchStart; $i < $batchEnd; $i++) {
					$orderNo = (string) $rows[$i]['orderno'];
					if (!isset($factsByKey[$orderNo])) continue;
					try {
						$lines = in_array($orderNo, $normalOrders, true) ? (isset($linesByOrder[$orderNo]) ? $linesByOrder[$orderNo] : array()) : null;
						$snapshot = $source->snapshotFromFact($db, $orderNo, $factsByKey[$orderNo], $lines);
						// Deadlocks with other writers are retried; the record is keyed, so a retry cannot duplicate it.
						for ($attempt = 1; ; $attempt++) {
							try {
								saActRecordQuotationIssue($db, $orderNo, $backfillActor, bfTimestamp($rows[$i]['orddate']), $snapshot);
								break;
							} catch (Throwable $e) {
								if ($attempt >= 3 || stripos($e->getMessage(), 'deadlock') === false) throw $e;
								usleep(200000 * $attempt);
							}
						}
					} catch (Throwable $e) {
						$errors++;
						bfLog('quotation ' . $orderNo . ': ' . $e->getMessage());
					}
				}
			}
			}
		}
		if ($attempted - $lastReported >= 1000 || $yearEnd === $total) {
			bfLog(sprintf('quotations %d of %d (%.0fs, %d errors, %d without source fact)', $attempted, $total, microtime(true) - $started, $errors, $skippedNoFact));
			$lastReported = $attempted;
		}
		$yearStart = $yearEnd;
	}
	$summary['quotes'] = array('quotations_total' => $total, 'attempted' => $attempted, 'errors' => $errors,
		'skipped_no_source_fact' => $skippedNoFact, 'mode' => $dryRun ? 'dry run (counted only)' : 'written (existing snapshots are skipped)');
	bfLog('Quotation phase done');
}

echo json_encode($summary, JSON_PRETTY_PRINT) . "\n";
