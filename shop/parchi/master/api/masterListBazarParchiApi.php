<?php

/**
 * Return the supplier-level Market Master report.
 *
 * The previous implementation executed a separate query for every metric and
 * every vendor, then queried vendor permissions once per row. Apart from being
 * slow, that made an empty response look like a successful report when the
 * permission check failed. Keep the legacy array response expected by
 * DataTables, but build it from one aggregate query and one scope check.
 */

$PathPrefix = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR;
$AllowAnyone = true;

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

include_once($PathPrefix . 'includes/session.inc');
include_once($PathPrefix . 'includes/SQL_CommonFunctions.inc');

function sendMarketMasterJson($payload, $statusCode = 200)
{
	while (ob_get_level() > 0) {
		ob_end_clean();
	}

	http_response_code((int) $statusCode);
	header('Content-Type: application/json; charset=utf-8');
	header('Cache-Control: no-store');

	$json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
	if ($json === false) {
		$json = json_encode(array(
			'error' => true,
			'message' => 'The Market Master response could not be encoded.',
		));
	}

	echo $json;
	exit;
}

function bindMarketMasterParams($statement, $types, &$params)
{
	if ($types === '') {
		return true;
	}

	$arguments = array($statement, $types);
	foreach ($params as $key => &$value) {
		$arguments[] = &$value;
	}

	return call_user_func_array('mysqli_stmt_bind_param', $arguments);
}

function marketMasterEscape($value)
{
	return htmlspecialchars(html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
}

function marketMasterNumber($value)
{
	$value = (float) $value;
	return function_exists('locale_number_format')
		? locale_number_format($value, 2)
		: number_format($value, 2);
}

try {
	if (!isset($_SESSION['UserID']) || trim((string) $_SESSION['UserID']) === '') {
		sendMarketMasterJson(array(
			'error' => true,
			'message' => 'An authenticated ERP session is required.',
		), 401);
	}

	$userId = trim((string) $_SESSION['UserID']);
	$canViewAllVendors = function_exists('userHasPermission') && userHasPermission($db, '*');

	/*
	 * Aggregate one-to-many tables before joining them to bazar_parchi. This
	 * prevents duplicate vendor rows and reduces the old N+1 query pattern to a
	 * single indexed read across the source tables.
	 */
	$sql = "SELECT
				bp.svid,
				COALESCE(
					NULLIF(MAX(s.suppname), ''),
					NULLIF(MAX(bp.temp_vendor), ''),
					CASE WHEN bp.svid = '' THEN 'Unassigned vendor' ELSE CONCAT('Vendor ', bp.svid) END
				) AS vendor_name,
				SUM(CASE WHEN bp.type = 602 THEN 1 ELSE 0 END) AS mpocount,
				SUM(CASE WHEN bp.type = 601 THEN 1 ELSE 0 END) AS mpicount,
				SUM(CASE WHEN bp.type = 602 AND bp.settled = 0 AND bp.inprogress = 1 THEN 1 ELSE 0 END) AS mpoip,
				SUM(CASE WHEN bp.type = 601 AND bp.settled = 0 AND bp.inprogress = 1 THEN 1 ELSE 0 END) AS mpiip,
				SUM(CASE WHEN bp.type = 602 AND bp.settled = 0 AND bp.inprogress = 0 THEN 1 ELSE 0 END) AS mposaved,
				SUM(CASE WHEN bp.type = 601 AND bp.settled = 0 AND bp.inprogress = 0 THEN 1 ELSE 0 END) AS mpisaved,
				SUM(CASE WHEN COALESCE(ts.mpo_settled, 0) = 1 THEN 1 ELSE 0 END) AS mposettled,
				SUM(CASE WHEN COALESCE(ts.mpi_settled, 0) = 1 THEN 1 ELSE 0 END) AS mpisettled,
				COALESCE(MAX(dbalance.receivable), 0) AS receivable,
				COALESCE(MAX(sbalance.payable), 0) AS payable
			FROM bazar_parchi bp
			LEFT JOIN suppliers s
				ON s.supplierid = bp.svid
			LEFT JOIN (
				SELECT transno,
					MAX(CASE WHEN type = 602 AND settled = 1 THEN 1 ELSE 0 END) AS mpo_settled,
					MAX(CASE WHEN type = 601 AND settled = 1 THEN 1 ELSE 0 END) AS mpi_settled
				FROM supptrans
				WHERE type IN (601, 602)
				GROUP BY transno
			) ts ON ts.transno = bp.transno
			LEFT JOIN (
				SELECT supplierno, SUM(ovamount) AS payable
				FROM supptrans
				GROUP BY supplierno
			) sbalance ON sbalance.supplierno = bp.svid
			LEFT JOIN (
				SELECT debtorno, SUM(ovamount) AS receivable
				FROM debtortrans
				GROUP BY debtorno
			) dbalance ON dbalance.debtorno = bp.svid
			WHERE 1 = 1";

	$params = array();
	$types = '';
	if (!$canViewAllVendors) {
		$sql .= "
				AND EXISTS (
					SELECT 1
					FROM vendor_permission vp
					WHERE vp.userid = ?
					  AND (vp.permission = '*' OR vp.permission = bp.svid)
				)";
		$params[] = $userId;
		$types .= 's';
	}

	$sql .= "
			GROUP BY bp.svid
			HAVING SUM(CASE WHEN bp.discarded = 0 THEN 1 ELSE 0 END) > 0
            ORDER BY CASE WHEN bp.svid = '' THEN 1 ELSE 0 END, vendor_name, bp.svid";

	$statement = mysqli_prepare($db, $sql);
	if (!$statement) {
		throw new Exception('The Market Master query could not be prepared.');
	}
	if (!bindMarketMasterParams($statement, $types, $params)) {
		mysqli_stmt_close($statement);
		throw new Exception('The Market Master query parameters could not be bound.');
	}
	if (!mysqli_stmt_execute($statement)) {
		$error = mysqli_stmt_error($statement);
		mysqli_stmt_close($statement);
		throw new Exception('The Market Master query failed: ' . $error);
	}

	$result = mysqli_stmt_get_result($statement);
	if (!$result) {
		mysqli_stmt_close($statement);
		throw new Exception('The Market Master result could not be read.');
	}

	$data = array();
	while ($row = mysqli_fetch_assoc($result)) {
		$svid = (string) $row['svid'];
		$encodedSvid = rawurlencode($svid);
		$escapedSvid = marketMasterEscape($svid);
		$escapedName = marketMasterEscape($row['vendor_name']);

		$statementHtml = '<form target="_blank" action="../../../reports/balance/suppstatement/SupplierStatement.php" method="post" class="market-master-statement-form">'
			. '<input type="hidden" name="FormID" value="' . marketMasterEscape(isset($_SESSION['FormID']) ? $_SESSION['FormID'] : '') . '">'
			. '<input type="hidden" name="cust" value="' . $escapedSvid . '">'
			. '<input type="date" name="fromdate" aria-label="Statement start date">'
			. '<input type="date" name="todate" aria-label="Statement end date">'
			. '<button type="submit" class="btn-info">Supplier Statement</button>'
			. '</form>';

		$r = array();
		$r[] = $escapedSvid;
		$r[] = $escapedName;
		$r[] = '<a target="_blank" rel="noopener" href="../outward/listOutwardBazarParchiSimple.php?svid=' . $encodedSvid . '&filter=none">' . (int) $row['mpocount'] . '</a>';
		$r[] = '<a target="_blank" rel="noopener" href="../inward/listInwardBazarParchiSimple.php?svid=' . $encodedSvid . '&filter=none">' . (int) $row['mpicount'] . '</a>';
		$r[] = '<a target="_blank" rel="noopener" href="../outward/listOutwardBazarParchiSimple.php?svid=' . $encodedSvid . '&filter=inprogress">' . (int) $row['mpoip'] . '</a>';
		$r[] = '<a target="_blank" rel="noopener" href="../inward/listInwardBazarParchiSimple.php?svid=' . $encodedSvid . '&filter=inprogress">' . (int) $row['mpiip'] . '</a>';
		$r[] = '<a target="_blank" rel="noopener" href="../outward/listOutwardBazarParchiSimple.php?svid=' . $encodedSvid . '&filter=saved">' . (int) $row['mposaved'] . '</a>';
		$r[] = '<a target="_blank" rel="noopener" href="../inward/listInwardBazarParchiSimple.php?svid=' . $encodedSvid . '&filter=saved">' . (int) $row['mpisaved'] . '</a>';
		$r[] = '<a target="_blank" rel="noopener" href="../outward/listOutwardBazarParchiSimple.php?svid=' . $encodedSvid . '&filter=settled">' . (int) $row['mposettled'] . '</a>';
		$r[] = '<a target="_blank" rel="noopener" href="../inward/listInwardBazarParchiSimple.php?svid=' . $encodedSvid . '&filter=settled">' . (int) $row['mpisettled'] . '</a>';
		$r[] = marketMasterNumber($row['receivable']);
		$r[] = marketMasterNumber($row['payable']);
		$r[] = $statementHtml;
		$data[] = $r;
	}

	mysqli_free_result($result);
	mysqli_stmt_close($statement);
	sendMarketMasterJson($data);
} catch (Throwable $exception) {
	error_log('Market Master report failed: ' . $exception->getMessage());
	sendMarketMasterJson(array(
		'error' => true,
		'message' => 'Unable to load Market Master data. Please refresh the report or contact support.',
	), 500);
}
