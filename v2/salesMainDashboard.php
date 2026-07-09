<?php
/**
 * Sales Main Dashboard
 * -------------------------------------------------------------------------
 * Implements the "SALES MAIN DASHBOARD" screen from the pkgs-final Figma file
 * (node 694:2). Self-contained page that plugs into the existing v2 shell for
 * authentication ($db, $_SESSION) but renders its own chrome to match the design.
 *
 * PERFORMANCE MODEL
 *   The heavy metrics are ~13 multi-table joins over large tables. To keep the
 *   page responsive:
 *     1. The shell/skeleton renders instantly (no heavy query on first paint).
 *     2. Data is fetched asynchronously via `?data=1` which returns JSON.
 *     3. That JSON is cached in the `cache` table per filter combo (TTL below),
 *        so repeat loads are instant. A Refresh button forces a recompute.
 *   The true root-cause fix (indexes on invoice.invoicesdate / branchcode and
 *   invoicedetails.invoiceno, etc.) ships as docker/init-db/07-dashboard-indexes.sql.
 *
 * Every DB call is guarded (scalarQ / rowsQ) so a metric whose source still
 * needs confirmation degrades to 0 / empty instead of breaking the page.
 * Metrics tagged @NEW are introduced by this design and use a best-effort
 * mapping — confirm the business definition before treating the number as final.
 */

$active = "salesdashboard";
$AllowAnyone = true;
include_once("config.php"); // -> ../includes/session.inc : $db, $_SESSION, helpers, $NewRootPath, $salesPersonsToSwitch

const DASH_CACHE_TTL_MINUTES = 15;

/* ------------------------------------------------------------------ helpers */
function esc($db, $v) { return mysqli_real_escape_string($db, (string)$v); }

/** First column of first row as float (0 on failure). */
function scalarQ($db, $sql) {
	$r = @mysqli_query($db, $sql);
	if (!$r) return 0.0;
	$row = mysqli_fetch_row($r);
	return $row ? (float)$row[0] : 0.0;
}

/** All rows as assoc array ([] on failure). */
function rowsQ($db, $sql) {
	$out = [];
	$r = @mysqli_query($db, $sql);
	if (!$r) return $out;
	while ($row = mysqli_fetch_assoc($r)) $out[] = $row;
	return $out;
}

/* Time-bucket helpers — keep SQL grouping and PHP bucket list in lock-step. */
function bucketExpr($col, $res) {
	if ($res === 'yearly')    return "DATE_FORMAT($col,'%Y')";
	if ($res === 'quarterly') return "CONCAT(YEAR($col),'-Q',QUARTER($col))";
	return "DATE_FORMAT($col,'%Y-%m')";
}
function bucketKey($ts, $res) {
	if ($res === 'yearly')    return date('Y', $ts);
	if ($res === 'quarterly') return date('Y', $ts) . '-Q' . (intdiv((int)date('n', $ts) - 1, 3) + 1);
	return date('Y-m', $ts);
}
function bucketLabel($ts, $res) {
	if ($res === 'yearly')    return date('Y', $ts);
	if ($res === 'quarterly') return 'Q' . (intdiv((int)date('n', $ts) - 1, 3) + 1) . " '" . date('y', $ts);
	return date('M', $ts);
}

/* ------------------------------------------------------- access & filters */
$adminLevels = [8, 10, 22];
$isAdmin = in_array((int)($_SESSION['AccessLevel'] ?? 0), $adminLevels);
if (function_exists('userHasPermission')) {
	$isAdmin = $isAdmin || @userHasPermission($db, '*');
}

$selType  = $_GET['type'] ?? 'all';         // 'all' (Overall Sales) or a salesmancode
$smanName = null;
$smanCode = null;
if ($selType !== 'all') {
	$row = rowsQ($db, "SELECT salesmancode, salesmanname FROM salesman WHERE salesmancode = '" . esc($db, $selType) . "' LIMIT 1");
	if ($row) { $smanCode = $row[0]['salesmancode']; $smanName = $row[0]['salesmanname']; }
} elseif (!$isAdmin) {
	$smanName = $_SESSION['UsersRealName'] ?? null;
	$selType  = 'self';
}
$smWhere = $smanName ? " AND salesman.salesmanname = '" . esc($db, $smanName) . "' " : "";

$resolution = $_GET['resolution'] ?? 'monthly';
if (!in_array($resolution, ['monthly', 'quarterly', 'yearly'])) $resolution = 'monthly';

$range = $_GET['range'] ?? 'ytd';
$Y = (int)date('Y');
switch ($range) {
	case 'mtd':      $startDate = date('Y-m-01');                          $endDate = date('Y-m-d'); break;
	case 'qtd':      $qm = (floor((date('n') - 1) / 3) * 3) + 1;
	                 $startDate = date('Y-' . str_pad($qm, 2, '0', STR_PAD_LEFT) . '-01'); $endDate = date('Y-m-d'); break;
	case 'last12':   $startDate = date('Y-m-d', strtotime('-11 months', strtotime(date('Y-m-01')))); $endDate = date('Y-m-d'); break;
	case 'lastyear': $startDate = ($Y - 1) . '-01-01';                     $endDate = ($Y - 1) . '-12-31'; break;
	default:         $range = 'ytd'; $startDate = $Y . '-01-01';           $endDate = date('Y-m-d');
}

/* ============================================================ computation */
/**
 * Compute the full dashboard payload (KPIs + chart series + table). This is the
 * expensive part; it only runs inside the ?data=1 branch, behind the cache.
 */
function computeDashboard($db, $isAdmin, $smanName, $smanCode, $smWhere, $resolution, $range, $startDate, $endDate) {

	// Invoiced-value formula + join (matches dashboard/new_widgets/salesTargetYearly.php).
	// The custbranch/salesman joins are only added when a salesman filter is active —
	// for the common "Overall Sales" view we skip them, cutting 2 joins off every scan.
	$INV_VALUE = "SUM((((invoicedetails.unitprice / 100) * ((1 - invoicedetails.discountpercent) * 100)) * invoicedetails.quantity) * invoiceoptions.quantity)";
	$INV_FROM  = "FROM invoice
		INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
		INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
			AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
			AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)"
		. ($smanName ? "
		INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
		INNER JOIN salesman   ON salesman.salesmancode = custbranch.salesman" : "");
	$INV_WHERE = "WHERE invoice.returned = 0 AND invoice.inprogress = 0";

	/* ---- time buckets ---- */
	$perYearBuckets = ($resolution === 'yearly') ? 1 : (($resolution === 'quarterly') ? 4 : 12);
	$months = [];
	$cur = strtotime(date('Y-m-01', strtotime($startDate)));
	$last = strtotime(date('Y-m-01', strtotime($endDate)));
	while ($cur <= $last) {
		$k = bucketKey($cur, $resolution);
		if (!isset($months[$k])) $months[$k] = bucketLabel($cur, $resolution);
		$cur = strtotime('+1 month', $cur);
	}
	if (!$months) { $months = [bucketKey(time(), $resolution) => bucketLabel(time(), $resolution)]; }

	/* ---- monthly (bucketed) invoiced value ---- */
	$monthInvoice = array_fill_keys(array_keys($months), 0.0);
	foreach (rowsQ($db, "SELECT " . bucketExpr('invoice.invoicesdate', $resolution) . " ym, $INV_VALUE v
			$INV_FROM $INV_WHERE
			AND invoice.invoicesdate BETWEEN '$startDate' AND '$endDate' $smWhere
			GROUP BY ym") as $r) {
		if (isset($monthInvoice[$r['ym']])) $monthInvoice[$r['ym']] = (float)$r['v'];
	}
	// Total Invoice Value derived from buckets (avoids a separate full-join scan).
	$kInvoice = array_sum($monthInvoice);

	/* ---- bucketed pending DC ---- */
	$monthDC = array_fill_keys(array_keys($months), 0.0);
	foreach (rowsQ($db, "SELECT " . bucketExpr('dcs.orddate', $resolution) . " ym,
			SUM(dcdetails.unitprice * (1 - dcdetails.discountpercent) * dcdetails.quantity * dcoptions.quantity) v
			FROM dcdetails
			INNER JOIN dcoptions ON (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno)
			INNER JOIN dcs       ON dcs.orderno = dcdetails.orderno
			LEFT  JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			WHERE dcs.orddate BETWEEN '$startDate' AND '$endDate'"
			. ($smanName ? " AND salescase.salesman = '" . esc($db, $smanName) . "' " : "") . "
			GROUP BY ym") as $r) {
		if (isset($monthDC[$r['ym']])) $monthDC[$r['ym']] = (float)$r['v'];
	}
	$kPendingDC = array_sum($monthDC);

	/* ---- period-over-period: same buckets, previous year ---- */
	$startLY = date('Y-m-d', strtotime('-1 year', strtotime($startDate)));
	$endLY   = date('Y-m-d', strtotime('-1 year', strtotime($endDate)));
	$monthInvoiceLY = array_fill_keys(array_keys($months), 0.0);
	foreach (rowsQ($db, "SELECT " . bucketExpr('DATE_ADD(invoice.invoicesdate, INTERVAL 1 YEAR)', $resolution) . " ym, $INV_VALUE v
			$INV_FROM $INV_WHERE
			AND invoice.invoicesdate BETWEEN '$startLY' AND '$endLY' $smWhere
			GROUP BY ym") as $r) {
		if (isset($monthInvoiceLY[$r['ym']])) $monthInvoiceLY[$r['ym']] = (float)$r['v'];
	}

	/* ---- scalar KPIs ---- */
	// 1) Total Sales Target
	if ($smanName) {
		$kTarget = scalarQ($db, "SELECT target FROM salesman WHERE salesmanname = '" . esc($db, $smanName) . "'");
	} else {
		$kTarget = scalarQ($db, "SELECT SUM(target) FROM salesman WHERE `current` = 1");
	}

	// 4) PO Values + 8) Total OC Value — one scan of salesorders (PO is a subset of OC).  @NEW
	$so = rowsQ($db, "SELECT
			SUM(salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) * salesorderdetails.quantity) oc,
			SUM(CASE WHEN salesorders.poplaced = 1
				THEN salesorderdetails.unitprice * (1 - salesorderdetails.discountpercent) * salesorderdetails.quantity ELSE 0 END) po
		FROM salesorders
		INNER JOIN salesorderdetails ON salesorderdetails.orderno = salesorders.orderno
		WHERE salesorders.quotation = 0
		AND salesorders.orddate BETWEEN '$startDate' AND '$endDate'"
		. ($smanCode ? " AND salesorders.salesperson = '" . esc($db, $smanCode) . "' " : ""));
	$kOC = (float)($so[0]['oc'] ?? 0);
	$kPO = (float)($so[0]['po'] ?? 0);

	// 5) CRV / 6) CSV / 11) Shop DC — one grouped scan of shop sales (debtortrans type 750).
	$kCRV = $kCSV = $kShopDC = 0.0;
	foreach (rowsQ($db, "SELECT shopsale.payment p, SUM(debtortrans.ovamount) v
			FROM shopsale
			INNER JOIN debtortrans ON (debtortrans.type = 750 AND debtortrans.transno = shopsale.orderno AND debtortrans.reversed = 0)"
			. ($smanName ? "
			INNER JOIN custbranch ON shopsale.branchcode = custbranch.branchcode
			INNER JOIN salesman   ON custbranch.salesman = salesman.salesmancode" : "") . "
			WHERE shopsale.complete = 1
			AND shopsale.orddate BETWEEN '$startDate' AND '$endDate' $smWhere
			GROUP BY shopsale.payment") as $r) {
		$v = (float)$r['v'];
		$kShopDC += $v;
		if ($r['p'] === 'crv') $kCRV = $v;
		elseif ($r['p'] === 'csv') $kCSV = $v;
	}

	// 7) Outstanding — open debtor balance (debtortrans type 10).
	$OUT_BAL = "SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 THEN ovamount - alloc
						  WHEN GSTwithhold = 0 AND WHT = 1 THEN ovamount - alloc - WHTamt
						  WHEN GSTwithhold = 1 AND WHT = 0 THEN ovamount - alloc - GSTamt
						  ELSE ovamount - alloc - GSTamt - WHTamt END)";
	if ($smanName) {
		// Attribute each debtor to one branch's salesman via a single grouped derived table
		// (replaces a correlated per-row MAX() subquery).
		$kOutstanding = scalarQ($db, "SELECT $OUT_BAL
			FROM debtortrans
			INNER JOIN invoice       ON invoice.invoiceno = debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN (SELECT debtorno, MAX(branchcode) branchcode FROM custbranch GROUP BY debtorno) pb
				ON pb.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch ON (custbranch.branchcode = pb.branchcode AND custbranch.debtorno = pb.debtorno)
			INNER JOIN salesman   ON salesman.salesmancode = custbranch.salesman
			WHERE debtortrans.type = 10 AND debtortrans.settled = 0 AND debtortrans.reversed = 0 $smWhere");
	} else {
		$kOutstanding = scalarQ($db, "SELECT $OUT_BAL
			FROM debtortrans
			INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
			WHERE debtortrans.type = 10 AND debtortrans.settled = 0 AND debtortrans.reversed = 0");
	}

	// 12) Cart Value — issued stock value, scoped to the user's cart access.
	$kCart = scalarQ($db, "SELECT SUM(stockissuance.issued * stockmaster.materialcost * (1 - stockmaster.discount))
		FROM stockissuance
		INNER JOIN stockmaster ON stockissuance.stockid = stockmaster.stockid
		WHERE stockissuance.issued > 0 AND (stockmaster.mbflag = 'B' OR stockmaster.mbflag = 'M')"
		. ($smanCode
			? " AND stockissuance.salesperson = '" . esc($db, $smanCode) . "' "
			: " AND stockissuance.salesperson IN (SELECT can_access FROM cart_report_access WHERE user = '" . esc($db, $_SESSION['UserID'] ?? '') . "') "));

	//  9) Total Business Volume / 10) Total Proper Sale  @NEW
	$kBusinessVolume = $kInvoice + $kShopDC + $kPendingDC;
	$kProperSale     = $kInvoice;

	/* ---- series aligned to bucket order ---- */
	$bucketTarget = $kTarget / (float)$perYearBuckets;
	$catLabels = array_values($months);
	$sInvoice = $sDC = $sTarget = $sInvoiceLY = $cumInvoice = $cumTarget = [];
	$ci = $ct = 0;
	foreach (array_keys($months) as $ym) {
		$vi = round($monthInvoice[$ym]);
		$sInvoice[]   = $vi;
		$sDC[]        = round($monthDC[$ym]);
		$sTarget[]    = round($bucketTarget);
		$sInvoiceLY[] = round($monthInvoiceLY[$ym]);
		$ci += $vi; $ct += $bucketTarget;
		$cumInvoice[] = round($ci);
		$cumTarget[]  = round($ct);
	}

	/* ---- breakup pies ---- */
	$volumePie = [
		['name' => 'Invoiced',   'y' => round($kInvoice)],
		['name' => 'Shop DC',    'y' => round($kShopDC)],
		['name' => 'Cart',       'y' => round($kCart)],
		['name' => 'Pending DC', 'y' => round($kPendingDC)],
	];

	// Brand breakup, self-vs-other breakup and top-items all derive from ONE
	// stock-level aggregate (grouped by stkcode), aggregated in PHP — replacing
	// three separate full invoice-line scans with a single query.
	$stockAgg = rowsQ($db, "SELECT invoicedetails.stkcode code,
			MAX(stockmaster.description) description,
			MAX(manufacturers.manufacturers_name) brand,
			MAX(stockmaster.mbflag) mbflag,
			SUM(invoicedetails.quantity * invoiceoptions.quantity) qty,
			$INV_VALUE v
		$INV_FROM
		LEFT JOIN stockmaster   ON invoicedetails.stkcode = stockmaster.stockid
		LEFT JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
		$INV_WHERE
		AND invoice.invoicesdate BETWEEN '$startDate' AND '$endDate' $smWhere
		GROUP BY invoicedetails.stkcode");

	$brandAgg = [];
	$selfAgg  = ['Self Brand' => 0.0, 'Other Brand' => 0.0, 'Unspecified' => 0.0];
	foreach ($stockAgg as $r) {
		$v = round((float)$r['v']);
		$bn = $r['brand'] ?: 'Unknown';
		$brandAgg[$bn] = ($brandAgg[$bn] ?? 0) + $v;
		$sk = $r['mbflag'] === 'M' ? 'Self Brand' : ($r['mbflag'] === 'B' ? 'Other Brand' : 'Unspecified');
		$selfAgg[$sk] += $v;
	}
	arsort($brandAgg);
	$brandPie = [];
	foreach (array_slice($brandAgg, 0, 6, true) as $name => $v) $brandPie[] = ['name' => $name, 'y' => $v];
	$selfPie = [];
	foreach ($selfAgg as $name => $v) { if ($v > 0) $selfPie[] = ['name' => $name, 'y' => $v]; }

	// Top items — sort the same aggregate by value.
	usort($stockAgg, function ($a, $b) { return ((float)$b['v']) <=> ((float)$a['v']); });
	$topItems = [];
	foreach (array_slice($stockAgg, 0, 10) as $r) {
		$topItems[] = [
			'code' => $r['code'], 'description' => $r['description'],
			'qty' => (float)$r['qty'], 'v' => round((float)$r['v']),
		];
	}

	return [
		'kpis' => [
			['Total Sales Target',    round($kTarget)],
			['Total Invoice Value',   round($kInvoice)],
			['Total Pending DC Value', round($kPendingDC)],
			['PO Values',             round($kPO)],
			['CRV',                   round($kCRV)],
			['CSV',                   round($kCSV)],
			['Outstanding',           round($kOutstanding)],
			['Total OC Value',        round($kOC)],
			['Total Business Volume', round($kBusinessVolume)],
			['Total Proper Sale',     round($kProperSale)],
			['Shop DC',               round($kShopDC)],
			['Cart Value',            round($kCart)],
		],
		'chart' => [
			'categories' => $catLabels,
			'invoice'    => $sInvoice,
			'dc'         => $sDC,
			'target'     => $sTarget,
			'invoiceLY'  => $sInvoiceLY,
			'cumInvoice' => $cumInvoice,
			'cumTarget'  => $cumTarget,
			'volumePie'  => $volumePie,
			'brandPie'   => $brandPie,
			'selfPie'    => $selfPie,
		],
		'topItems' => $topItems,
	];
}

/* ======================================================= JSON data endpoint */
if (isset($_GET['data'])) {
	header('Content-Type: application/json; charset=utf-8');

	$cacheKey = 'salesMainDash-' . $selType . '-' . $resolution . '-' . $range;
	$noCache  = isset($_GET['nocache']);

	if (!$noCache) {
		$hit = rowsQ($db, "SELECT value FROM cache
			WHERE unique_key = '" . esc($db, $cacheKey) . "'
			AND refreshed_at > DATE_SUB(NOW(), INTERVAL " . DASH_CACHE_TTL_MINUTES . " MINUTE) LIMIT 1");
		if ($hit) { echo $hit[0]['value']; exit; }
	}

	$payload = computeDashboard($db, $isAdmin, $smanName, $smanCode, $smWhere, $resolution, $range, $startDate, $endDate);
	$payload['cachedAt'] = date('Y-m-d H:i:s');
	// JSON_INVALID_UTF8_SUBSTITUTE guards against a stray non-UTF8 byte in a
	// product description blanking the whole response.
	$json = json_encode($payload, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE);

	@mysqli_query($db, "INSERT INTO cache (unique_key, value, refreshed_at)
		VALUES ('" . esc($db, $cacheKey) . "', '" . esc($db, $json) . "', NOW())
		ON DUPLICATE KEY UPDATE value = VALUES(value), refreshed_at = VALUES(refreshed_at)");

	echo $json;
	exit;
}

/* ====================================================== shell (renders fast) */
$salesmen = rowsQ($db, "SELECT salesmancode, salesmanname FROM salesman WHERE `current` = 1 ORDER BY salesmanname");
$userReal = $_SESSION['UsersRealName'] ?? ($_SESSION['UserID'] ?? 'User');
$userInitial = strtoupper(substr($userReal, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Sales Main Dashboard | SAHamid ERP</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/font-awesome/css/font-awesome.min.css">
	<script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/jquery/dist/jquery.min.js"></script>
	<script src="<?php echo $NewRootPath; ?>v2/assets/plugins/highcharts.js"></script>
	<style>
		:root {
			--brand-blue: #2E79F6; --brand-purple: #3C3160; --grey-bar: #D9D9D9;
			--sidebar-top: #2b2b2b; --sidebar-bottom: #000000; --content-bg: #EFEFEF;
			--card-border: #E6E6E6; --label-grey: #6B6B6B; --title-grey: #4A4A4A;
		}
		* { box-sizing: border-box; }
		html, body { margin: 0; padding: 0; height: 100%; }
		body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: var(--content-bg); color: #222; display: flex; min-height: 100vh; }
		a { text-decoration: none; color: inherit; }

		.sd-sidebar { width: 240px; flex: 0 0 240px; background: linear-gradient(180deg, var(--sidebar-top) 0%, var(--sidebar-bottom) 100%); color: #fff; display: flex; flex-direction: column; position: sticky; top: 0; height: 100vh; }
		.sd-brand { display: flex; align-items: center; gap: 12px; padding: 18px; font-size: 20px; font-weight: 600; }
		.sd-brand .sd-cube { width: 34px; height: 34px; border-radius: 8px; background: #fff; color: #111; display: flex; align-items: center; justify-content: center; font-size: 18px; }
		.sd-divider { border-top: 1px solid rgba(255,255,255,.12); margin: 4px 16px; }
		.sd-nav { padding: 10px 0; flex: 1; overflow-y: auto; }
		.sd-item { display: flex; align-items: center; gap: 12px; padding: 11px 20px; color: #cfcfcf; cursor: pointer; font-size: 15px; font-weight: 600; }
		.sd-item:hover { color: #fff; }
		.sd-item .fa-chevron-down, .sd-item .fa-chevron-right { margin-left: auto; font-size: 11px; opacity: .7; }
		.sd-sub .sd-subitem { display: flex; align-items: center; gap: 10px; padding: 9px 20px 9px 52px; color: #b6b6b6; font-size: 14px; }
		.sd-sub .sd-subitem:hover { color: #fff; }
		.sd-subitem.active { color: var(--brand-blue); font-weight: 600; }
		.sd-subitem .dash { width: 10px; height: 2px; background: currentColor; display: inline-block; }
		.sd-profile { display: flex; align-items: center; gap: 12px; padding: 16px 18px; border-top: 1px solid rgba(255,255,255,.12); }
		.sd-avatar { width: 42px; height: 42px; border-radius: 10px; flex: 0 0 42px; background: #4b4b4b; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; }
		.sd-profile .nm { font-size: 14px; font-weight: 600; line-height: 1.2; }
		.sd-profile .rl { font-size: 12px; color: #9a9a9a; }
		.sd-profile .fa-sign-in { margin-left: auto; color: #cfcfcf; }

		.sd-main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
		.sd-topbar { background: #fff; height: 60px; display: flex; align-items: center; padding: 0 24px; gap: 22px; border-bottom: 1px solid #eee; }
		.sd-topbar .sd-top-icons { margin-left: auto; display: flex; align-items: center; gap: 22px; color: #6b6b6b; font-size: 18px; }
		.sd-topbar .sd-top-avatar { width: 34px; height: 34px; border-radius: 50%; background: #4b4b4b; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }
		.sd-content { padding: 22px 26px 40px; }
		.sd-page-head { display: flex; align-items: center; margin: 0 0 16px; }
		.sd-page-title { font-size: 16px; font-weight: 700; letter-spacing: .5px; color: var(--title-grey); margin: 0; }
		.sd-meta { margin-left: auto; display: flex; align-items: center; gap: 12px; font-size: 12px; color: #888; }
		.sd-refresh { border: 1px solid #d5d5d5; background: #fff; border-radius: 6px; padding: 5px 12px; cursor: pointer; font-size: 12px; color: #444; }
		.sd-refresh:hover { background: #f3f3f3; }

		.sd-panel { background: #fff; border-radius: 10px; padding: 22px; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
		.sd-filters { display: flex; align-items: center; justify-content: flex-end; gap: 14px; flex-wrap: wrap; padding-bottom: 6px; border-bottom: 1px dashed #dcdcdc; margin-bottom: 20px; }
		.sd-filter { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #333; }
		.sd-filter label { font-weight: 600; }
		.sd-filter select { border: 1px solid #d5d5d5; border-radius: 6px; padding: 6px 26px 6px 10px; font-size: 13px; color: #555; background: #f7f7f7; cursor: pointer; -webkit-appearance: none; appearance: none; background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6'><path d='M0 0l5 6 5-6z' fill='%23888'/></svg>"); background-repeat: no-repeat; background-position: right 9px center; }

		.sd-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 24px; }
		.sd-kpi { border: 1px solid var(--card-border); border-radius: 10px; padding: 18px 14px; text-align: center; background: #fff; }
		.sd-kpi .k-title { font-size: 14px; color: var(--label-grey); margin-bottom: 10px; }
		.sd-kpi .k-value { font-size: 22px; font-weight: 600; color: var(--brand-blue); }

		.sd-charts { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
		.sd-chart-card { border: 1px solid var(--card-border); border-radius: 10px; padding: 16px; background: #fff; }
		.sd-chart-card h4 { margin: 0 0 6px; font-size: 15px; font-weight: 600; color: #333; }
		.sd-chart { height: 240px; }
		.sd-chart-card.full { grid-column: 1 / -1; }
		table.sd-table { width: 100%; border-collapse: collapse; font-size: 13px; }
		table.sd-table th, table.sd-table td { padding: 9px 10px; border-bottom: 1px solid #eee; text-align: left; }
		table.sd-table th { color: #666; font-weight: 600; background: #fafafa; }
		table.sd-table td.num, table.sd-table th.num { text-align: right; }
		.sd-empty, .sd-loading { color: #aaa; font-size: 13px; padding: 24px 0; text-align: center; }

		/* skeleton shimmer */
		@keyframes sk { 0% { background-position: -200px 0; } 100% { background-position: calc(200px + 100%) 0; } }
		.sk { color: transparent !important; border-radius: 5px; background: #eee; background-image: linear-gradient(90deg, #eee, #f5f5f5, #eee); background-size: 200px 100%; background-repeat: no-repeat; animation: sk 1.2s ease-in-out infinite; display: inline-block; min-height: 1em; min-width: 90px; }
		.sd-chart.sk { display: block; min-height: 240px; }

		@media (max-width: 1100px) { .sd-kpis { grid-template-columns: repeat(2, 1fr); } .sd-charts { grid-template-columns: 1fr; } }
		@media (max-width: 720px) { .sd-sidebar { display: none; } .sd-kpis { grid-template-columns: 1fr; } }
	</style>
</head>
<body>
	<aside class="sd-sidebar">
		<div class="sd-brand"><span class="sd-cube"><i class="fa fa-cube"></i></span><span>Dashboards</span></div>
		<div class="sd-divider"></div>
		<nav class="sd-nav">
			<div class="sd-group">
				<div class="sd-item"><i class="fa fa-th-large"></i> Dashboard <i class="fa fa-chevron-down"></i></div>
				<div class="sd-sub">
					<a class="sd-subitem active" href="<?php echo $NewRootPath; ?>v2/salesMainDashboard.php"><span class="dash"></span> Main Dashboard</a>
					<a class="sd-subitem" href="<?php echo $NewRootPath; ?>v2/salesMainDashboard.php?view=summary"><span class="dash"></span> Summary</a>
				</div>
			</div>
			<div class="sd-item"><i class="fa fa-cog"></i> Settings <i class="fa fa-chevron-right"></i></div>
		</nav>
		<div class="sd-profile">
			<div class="sd-avatar"><?php echo htmlspecialchars($userInitial); ?></div>
			<div><div class="nm"><?php echo htmlspecialchars($userReal); ?></div><div class="rl"><?php echo $isAdmin ? 'Admin' : 'Sales'; ?></div></div>
			<a href="<?php echo $NewRootPath; ?>Logout.php" title="Logout"><i class="fa fa-sign-in"></i></a>
		</div>
	</aside>

	<div class="sd-main">
		<header class="sd-topbar">
			<div class="sd-top-icons">
				<i class="fa fa-sliders" title="Filters"></i>
				<i class="fa fa-bell-o" title="Notifications"></i>
				<span class="sd-top-avatar"><?php echo htmlspecialchars($userInitial); ?></span>
			</div>
		</header>

		<main class="sd-content">
			<div class="sd-page-head">
				<h1 class="sd-page-title">SALES MAIN DASHBOARD</h1>
				<div class="sd-meta">
					<span id="sdUpdated"></span>
					<button type="button" class="sd-refresh" id="sdRefresh"><i class="fa fa-refresh"></i> Refresh</button>
				</div>
			</div>

			<div class="sd-panel">
				<div class="sd-filters">
					<div class="sd-filter">
						<label>Type:</label>
						<select id="fType" <?php echo $isAdmin ? '' : 'disabled'; ?>>
							<option value="all" <?php echo $selType === 'all' ? 'selected' : ''; ?>>Overall Sales</option>
							<?php foreach ($salesmen as $sm) {
								echo '<option value="' . htmlspecialchars($sm['salesmancode']) . '" ' . ($selType === $sm['salesmancode'] ? 'selected' : '') . '>' . htmlspecialchars($sm['salesmanname']) . '</option>';
							} ?>
						</select>
					</div>
					<div class="sd-filter">
						<label>Resolution:</label>
						<select id="fResolution">
							<?php foreach (['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'] as $k => $v) {
								echo '<option value="' . $k . '" ' . ($resolution === $k ? 'selected' : '') . '>' . $v . '</option>';
							} ?>
						</select>
					</div>
					<div class="sd-filter">
						<label>Range:</label>
						<select id="fRange">
							<?php foreach (['ytd' => 'This Year', 'mtd' => 'This Month', 'qtd' => 'This Quarter', 'last12' => 'Last 12 Months', 'lastyear' => 'Last Year'] as $k => $v) {
								echo '<option value="' . $k . '" ' . ($range === $k ? 'selected' : '') . '>' . $v . '</option>';
							} ?>
						</select>
					</div>
				</div>

				<div class="sd-kpis" id="sdKpis">
					<?php
					$kpiTitles = ['Total Sales Target', 'Total Invoice Value', 'Total Pending DC Value', 'PO Values', 'CRV', 'CSV', 'Outstanding', 'Total OC Value', 'Total Business Volume', 'Total Proper Sale', 'Shop DC', 'Cart Value'];
					foreach ($kpiTitles as $i => $t) {
						echo '<div class="sd-kpi"><div class="k-title">' . htmlspecialchars($t) . '</div><div class="k-value" data-k="' . $i . '"><span class="sk">&nbsp;</span></div></div>';
					}
					?>
				</div>

				<div class="sd-charts">
					<div class="sd-chart-card"><h4>Sales vs Target vs Pending DC</h4><div id="cBar" class="sd-chart sk"></div></div>
					<div class="sd-chart-card"><h4>Period over Period Chart</h4><div id="cPoP" class="sd-chart sk"></div></div>
					<div class="sd-chart-card"><h4>Score Curve</h4><div id="cScore" class="sd-chart sk"></div></div>
					<div class="sd-chart-card"><h4>Business Volume Breakup</h4><div id="cVolume" class="sd-chart sk"></div></div>
					<div class="sd-chart-card"><h4>Brand Wise Sales Breakup</h4><div id="cBrand" class="sd-chart sk"></div></div>
					<div class="sd-chart-card"><h4>Self Brand Sales Breakup</h4><div id="cSelf" class="sd-chart sk"></div></div>
					<div class="sd-chart-card full">
						<h4>Top Items</h4>
						<div id="sdTable"><div class="sd-loading">Loading…</div></div>
					</div>
				</div>
			</div>
		</main>
	</div>

	<script>
		var BLUE = '#2E79F6', PURPLE = '#3C3160', GREY = '#D9D9D9', LPURPLE = '#6C5CB0', LBLUE = '#8FB6FB';
		var PIE_COLORS = [BLUE, PURPLE, GREY, LPURPLE, LBLUE, '#A9A9A9'];
		var charts = {};

		Highcharts.setOptions({
			lang: { thousandsSep: ',' },
			credits: { enabled: false },
			chart: { style: { fontFamily: "'Segoe UI', Roboto, Helvetica, Arial, sans-serif" } }
		});

		function rs(n) { return 'Rs ' + Math.round(n).toLocaleString('en-US'); }
		function moneyTip() { return { pointFormat: '<b>Rs {point.y:,.0f}</b>' }; }
		function hasData(arr) { return arr && arr.some(function (p) { return (p && p.y ? p.y : p) > 0; }); }
		function kAxis() { return { title: { text: null }, labels: { formatter: function () { return (this.value / 1000) + 'k'; } } }; }
		function mk(id, cfg) { if (charts[id]) { charts[id].destroy(); } document.getElementById(id).classList.remove('sk'); charts[id] = Highcharts.chart(id, cfg); }

		function pie(id, data, title) {
			document.getElementById(id).classList.remove('sk');
			if (!hasData(data)) { if (charts[id]) charts[id].destroy(); document.getElementById(id).innerHTML = '<div class="sd-empty" style="padding-top:100px;">No data for this period.</div>'; return; }
			mk(id, {
				chart: { type: 'pie' }, title: { text: null }, colors: PIE_COLORS,
				tooltip: { pointFormat: '<b>Rs {point.y:,.0f}</b> ({point.percentage:.1f}%)' },
				plotOptions: { pie: { innerSize: '55%', dataLabels: { enabled: true, format: '{point.name}', style: { fontWeight: 'normal', fontSize: '11px' } } } },
				series: [{ name: title, data: data }]
			});
		}

		function render(res) {
			// KPIs
			res.kpis.forEach(function (k, i) {
				var el = document.querySelector('.k-value[data-k="' + i + '"]');
				if (el) el.textContent = rs(k[1]);
			});
			var D = res.chart;

			mk('cBar', {
				chart: { type: 'column' }, title: { text: null }, xAxis: { categories: D.categories }, yAxis: kAxis(),
				plotOptions: { column: { borderWidth: 0, borderRadius: 2 } }, tooltip: moneyTip(),
				series: [
					{ name: 'Target', data: D.target, color: GREY },
					{ name: 'Sales', data: D.invoice, color: BLUE },
					{ name: 'Pending DC', data: D.dc, color: PURPLE }
				]
			});
			mk('cPoP', {
				chart: { type: 'column' }, title: { text: null }, xAxis: { categories: D.categories }, yAxis: kAxis(),
				plotOptions: { column: { borderWidth: 0, borderRadius: 2 } }, tooltip: moneyTip(),
				series: [
					{ name: 'Previous Period', data: D.invoiceLY, color: GREY },
					{ name: 'Current Period', data: D.invoice, color: BLUE }
				]
			});
			mk('cScore', {
				chart: { type: 'spline' }, title: { text: null }, xAxis: { categories: D.categories }, yAxis: kAxis(), tooltip: moneyTip(),
				series: [
					{ name: 'Cumulative Sales', data: D.cumInvoice, color: BLUE, lineWidth: 3, marker: { enabled: false } },
					{ name: 'Cumulative Target', data: D.cumTarget, color: '#333', dashStyle: 'Dot', lineWidth: 2, marker: { enabled: false } }
				]
			});
			pie('cVolume', D.volumePie, 'Business Volume');
			pie('cBrand', D.brandPie, 'Brand');
			pie('cSelf', D.selfPie, 'Self vs Other');

			// table
			var t = res.topItems || [];
			if (!t.length) { document.getElementById('sdTable').innerHTML = '<div class="sd-empty">No item sales in the selected period.</div>'; }
			else {
				var h = '<table class="sd-table"><thead><tr><th>Code</th><th>Description</th><th class="num">Qty</th><th class="num">Value</th></tr></thead><tbody>';
				t.forEach(function (it) {
					h += '<tr><td>' + esc(it.code) + '</td><td>' + esc(it.description) + '</td><td class="num">' + Number(it.qty).toLocaleString('en-US') + '</td><td class="num">' + rs(it.v) + '</td></tr>';
				});
				document.getElementById('sdTable').innerHTML = h + '</tbody></table>';
			}
			if (res.cachedAt) document.getElementById('sdUpdated').textContent = 'Updated ' + res.cachedAt;
		}

		function esc(s) { return (s == null ? '' : String(s)).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

		function qs(nocache) {
			var p = 'data=1&type=' + encodeURIComponent(document.getElementById('fType').value)
				+ '&resolution=' + encodeURIComponent(document.getElementById('fResolution').value)
				+ '&range=' + encodeURIComponent(document.getElementById('fRange').value);
			return nocache ? p + '&nocache=1' : p;
		}
		function markLoading() {
			document.querySelectorAll('.k-value').forEach(function (e) { e.innerHTML = '<span class="sk">&nbsp;</span>'; });
			['cBar', 'cPoP', 'cScore', 'cVolume', 'cBrand', 'cSelf'].forEach(function (id) { document.getElementById(id).classList.add('sk'); });
			document.getElementById('sdTable').innerHTML = '<div class="sd-loading">Loading…</div>';
		}
		function load(nocache) {
			markLoading();
			fetch('salesMainDashboard.php?' + qs(nocache), { credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(render)
				.catch(function () { document.getElementById('sdTable').innerHTML = '<div class="sd-empty">Failed to load data. Please refresh.</div>'; });
		}

		['fType', 'fResolution', 'fRange'].forEach(function (id) { document.getElementById(id).addEventListener('change', function () { load(false); }); });
		document.getElementById('sdRefresh').addEventListener('click', function () { load(true); });
		load(false);
	</script>
</body>
</html>
