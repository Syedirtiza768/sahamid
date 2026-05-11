<?php
/**
 * IB form sheet — persist and load monthly payment totals (matches Excel header row).
 * Other scripts can: require_once $PathPrefix.'includes/IBFormSheet.inc';
 */

if (!function_exists('ib_form_sheet_normalize_month')) {
	function ib_form_sheet_normalize_month($yearMonth) {
		// Accept HTML5 month "YYYY-MM" or "YYYY-MM-DD" (first day stored)
		$yearMonth = trim((string)$yearMonth);
		if ($yearMonth === '') {
			return null;
		}
		if (preg_match('/^(\d{4})-(\d{2})/', $yearMonth, $m)) {
			$y = (int)$m[1];
			$mon = (int)$m[2];
			if ($y >= 1970 && $y <= 2100 && $mon >= 1 && $mon <= 12) {
				return sprintf('%04d-%02d-01', $y, $mon);
			}
		}
		return null;
	}

	function ib_form_sheet_parse_amount($v) {
		if ($v === null || $v === '') {
			return 0.0;
		}
		if (is_numeric($v)) {
			return (float)$v;
		}
		$s = preg_replace('/[^0-9.\-]/', '', (string)$v);
		return $s === '' ? 0.0 : (float)$s;
	}

	/**
	 * Insert or replace values for a calendar month (unique on period_month).
	 * @param mysqli $db
	 * @param array $row keys: period_month, total_payment_gst, total_payment_nongst_cash,
	 *   total_payment_international, total_payment_freightward, total_advance_payment
	 * @param string $userId
	 * @return array{ok:bool,error?:string,id?:int}
	 */
	function ib_form_sheet_upsert($db, array $row, $userId = '') {
		$period = ib_form_sheet_normalize_month($row['period_month'] ?? '');
		if (!$period) {
			return ['ok' => false, 'error' => 'Invalid month'];
		}
		$gst = ib_form_sheet_parse_amount($row['total_payment_gst'] ?? 0);
		$non = ib_form_sheet_parse_amount($row['total_payment_nongst_cash'] ?? 0);
		$intl = ib_form_sheet_parse_amount($row['total_payment_international'] ?? 0);
		$fr = ib_form_sheet_parse_amount($row['total_payment_freightward'] ?? 0);
		$adv = ib_form_sheet_parse_amount($row['total_advance_payment'] ?? 0);
		$uid = mb_substr((string)$userId, 0, 20);

		$sql = "INSERT INTO ib_form_sheet_entries
			(period_month, total_payment_gst, total_payment_nongst_cash, total_payment_international,
			 total_payment_freightward, total_advance_payment, entered_by)
			VALUES (?,?,?,?,?,?,?)
			ON DUPLICATE KEY UPDATE
				total_payment_gst = VALUES(total_payment_gst),
				total_payment_nongst_cash = VALUES(total_payment_nongst_cash),
				total_payment_international = VALUES(total_payment_international),
				total_payment_freightward = VALUES(total_payment_freightward),
				total_advance_payment = VALUES(total_advance_payment),
				entered_by = VALUES(entered_by)";

		$st = mysqli_prepare($db, $sql);
		if (!$st) {
			return ['ok' => false, 'error' => mysqli_error($db)];
		}
		mysqli_stmt_bind_param(
			$st,
			'sddddds',
			$period,
			$gst,
			$non,
			$intl,
			$fr,
			$adv,
			$uid
		);
		$ok = mysqli_stmt_execute($st);
		$err = $ok ? '' : mysqli_stmt_error($st);
		$id = (int)mysqli_insert_id($db);
		mysqli_stmt_close($st);
		if (!$ok) {
			return ['ok' => false, 'error' => $err ?: 'Save failed'];
		}
		if ($id === 0) {
			$q = mysqli_prepare($db, 'SELECT id FROM ib_form_sheet_entries WHERE period_month = ? LIMIT 1');
			mysqli_stmt_bind_param($q, 's', $period);
			mysqli_stmt_execute($q);
			$res = mysqli_stmt_get_result($q);
			$r = $res ? mysqli_fetch_assoc($res) : null;
			mysqli_stmt_close($q);
			$id = $r ? (int)$r['id'] : 0;
		}
		return ['ok' => true, 'id' => $id];
	}

	/**
	 * @return list<array<string,mixed>>
	 */
	function ib_form_sheet_list($db, $limit = 200) {
		$limit = max(1, min(2000, (int)$limit));
		$sql = "SELECT id, period_month, total_payment_gst, total_payment_nongst_cash,
			total_payment_international, total_payment_freightward, total_advance_payment,
			entered_by, created_at, updated_at
			FROM ib_form_sheet_entries
			ORDER BY period_month DESC
			LIMIT " . (int)$limit;
		$res = mysqli_query($db, $sql);
		if (!$res) {
			return [];
		}
		$out = [];
		while ($row = mysqli_fetch_assoc($res)) {
			$out[] = $row;
		}
		return $out;
	}

	/**
	 * @return array<string,mixed>|null
	 */
	function ib_form_sheet_get_by_month($db, $yearMonth) {
		$period = ib_form_sheet_normalize_month($yearMonth);
		if (!$period) {
			return null;
		}
		$st = mysqli_prepare($db, 'SELECT * FROM ib_form_sheet_entries WHERE period_month = ? LIMIT 1');
		if (!$st) {
			return null;
		}
		mysqli_stmt_bind_param($st, 's', $period);
		mysqli_stmt_execute($st);
		$result = mysqli_stmt_get_result($st);
		$row = $result ? mysqli_fetch_assoc($result) : null;
		mysqli_stmt_close($st);
		return $row ?: null;
	}

	/**
	 * @return list<array<string,mixed>>
	 */
	function ib_form_sheet_list_json_ready($db, $limit = 500) {
		$rows = ib_form_sheet_list($db, $limit);
		foreach ($rows as &$r) {
			$r['labels'] = [
				'Month' => $r['period_month'],
				'Total Payment (GST)at 1st of everymonth' => (float)$r['total_payment_gst'],
				'Total Payment at 1st NonGST/ CASH) of everymonth' => (float)$r['total_payment_nongst_cash'],
				'Total Payment at 1st (international)  everymonth' => (float)$r['total_payment_international'],
				'Total Payment at 1st (Frightward ) month' => (float)$r['total_payment_freightward'],
				'Total Advance Payment  1st o f every month' => (float)$r['total_advance_payment'],
			];
		}
		return $rows;
	}
}
