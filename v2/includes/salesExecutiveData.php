<?php
/**
 * Sales Executive Report: data layer.
 *
 * Every figure is read straight from ERP documents for the chosen dates. Where the
 * ERP already has a report for a figure, the same rule is used so the numbers agree:
 *
 *   Invoiced ............. SalesPerson Monthly Invoices  (debtortrans type 10 amount)
 *   CRV / CSV ............ SalesPerson Monthly CRV       (debtortrans type 750 amount)
 *   Pending DC ........... Pending DC List               (DC not invoiced, grouped, couriered or GRB'd)
 *   Outstanding .......... Outstanding Invoices          (open type-10 balance less GST/WHT withheld)
 *   Quotation, OC, DC .... Sales Data Analysis           (latest quotation, first option only)
 *   PO received .......... My Sales Activity             (PO file on the case, else a recorded PO receipt)
 *   Invoice line value ... Sales Main Dashboard          (unitprice x (1 - discount) x qty x option qty)
 *
 * Join safety: customer branches are keyed by (branchcode, debtorno), so both are matched.
 * ocs, dcs and invoice can repeat an order or invoice number, so each is reduced to one
 * row per number before its lines are summed.
 *
 * A failed query is recorded in $GLOBALS['sxErrors'] and its figure is returned as null,
 * so the page can say a number is missing instead of showing a misleading zero.
 */

const SX_ZERO_DT = '0000-00-00 00:00:00';
const SX_CASE_COLS = ['case', 'client', 'salesperson', 'opened', 'quoteNo', 'quote', 'status', 'po', 'poSource', 'oc', 'dc', 'invoiced'];

function sxEsc($db, $v) { return mysqli_real_escape_string($db, (string)$v); }

/** Rows as assoc arrays, or null (and an error entry) when the query fails. */
function sxRows($db, $sql, $figure) {
	$r = @mysqli_query($db, $sql);
	if (!$r) {
		$GLOBALS['sxErrors'][] = ['figure' => $figure, 'error' => mysqli_error($db)];
		return null;
	}
	$out = [];
	while ($row = mysqli_fetch_assoc($r)) $out[] = $row;
	return $out;
}

function sxValidDate($d) {
	if (!is_string($d) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) return false;
	list($y, $m, $day) = array_map('intval', explode('-', $d));
	return checkdate($m, $day, $y);
}

/** Resolve the date range from a preset or custom dates, plus the calendar months it touches. */
function sxPeriod($preset, $from, $to) {
	$today = date('Y-m-d');
	$Y = (int)date('Y');
	switch ($preset) {
		case 'mtd':      $s = date('Y-m-01'); $e = $today; break;
		case 'qtd':      $q = intdiv((int)date('n') - 1, 3) * 3 + 1; $s = sprintf('%04d-%02d-01', $Y, $q); $e = $today; break;
		case 'last12':   $s = date('Y-m-01', strtotime('-11 months', strtotime(date('Y-m-01')))); $e = $today; break;
		case 'lastyear': $s = ($Y - 1) . '-01-01'; $e = ($Y - 1) . '-12-31'; break;
		case 'custom':
			if (sxValidDate($from) && sxValidDate($to)) {
				$s = min($from, $to); $e = max($from, $to);
				break;
			}
			// invalid custom dates fall back to this year
		default:         $preset = 'ytd'; $s = $Y . '-01-01'; $e = $today;
	}
	$months = [];
	$cur = strtotime(date('Y-m-01', strtotime($s)));
	$last = strtotime(date('Y-m-01', strtotime($e)));
	while ($cur <= $last) {
		$months[date('Y-m', $cur)] = date('M Y', $cur);
		$cur = strtotime('+1 month', $cur);
	}
	return ['preset' => $preset, 'start' => $s, 'end' => $e, 'months' => $months];
}

/** Salesperson scope: null for everyone, else code, name, annual target and ERP user id. */
function sxSalesperson($db, $code) {
	if ($code === '' || $code === 'all' || $code === null) return null;
	$rows = sxRows($db, "SELECT s.salesmancode, s.salesmanname, s.target,
			(SELECT u.userid FROM www_users u WHERE u.realname = s.salesmanname LIMIT 1) userid
		FROM salesman s WHERE s.salesmancode = '" . sxEsc($db, $code) . "' LIMIT 1", 'Salesperson');
	if (!$rows) return null;
	return ['code' => $rows[0]['salesmancode'], 'name' => $rows[0]['salesmanname'],
		'target' => (float)$rows[0]['target'], 'userid' => (string)$rows[0]['userid']];
}

function sxHasTable($db, $table) {
	$r = @mysqli_query($db, "SHOW TABLES LIKE '" . sxEsc($db, $table) . "'");
	return $r && mysqli_num_rows($r) > 0;
}

/**
 * DC numbers already listed in a DC group (dcgroups.dcnos, comma-separated), as a lookup set
 * keyed by DC number. Such DCs are on their way to an invoice, so they are not pending.
 * Shared by the Pending DC List, this report and the Sales Main Dashboard. Null on failure.
 */
function sxGroupedDcNumbers($db) {
	$r = @mysqli_query($db, "SELECT dcnos FROM dcgroups WHERE dcnos IS NOT NULL AND dcnos <> ''");
	if (!$r) return null;
	$set = [];
	while ($row = mysqli_fetch_row($r)) {
		foreach (explode(',', (string)$row[0]) as $no) {
			$no = trim($no);
			if ($no !== '') $set[$no] = true;
		}
	}
	return $set;
}

/** Bucket rows into a status, using the Sales Data Analysis closing reasons. */
function sxCaseStatus($poDate, $hasQuote, $quote, $reason) {
	$r = strtolower((string)$reason);
	$has = function ($needle) use ($r) { return strpos($r, strtolower($needle)) !== false; };
	if ($poDate !== null) return 'PO received';
	if ($has('high prices') || $has('want in equal') || $has('Delivery time offered is not acceptable')) return 'Lost';
	if ($has('internal cross') || $has('external cross') || $has('Customer has cancelled the enquiry') || $has('any other reason')) return 'Cancelled';
	if ($has('Out of scope') || ($r !== '' && $quote <= 0)) return 'Out of scope';
	if ($r === '' && $quote > 0) return 'In pipeline';
	if ($r === '' && !$hasQuote) return 'No quotation';
	if ($r === '') return 'Quoted at zero';
	return 'Closed, other reason';
}

/**
 * Compute the whole report payload. $P is from sxPeriod(), $S from sxSalesperson().
 */
function sxCompute($db, $P, $S) {
	$GLOBALS['sxErrors'] = [];
	$s = $P['start']; $e = $P['end'];
	$today = date('Y-m-d');
	$codeF   = $S ? " AND cb.salesman = '" . sxEsc($db, $S['code']) . "'" : '';
	$nameF   = $S ? " AND sc.salesman = '" . sxEsc($db, $S['name']) . "'" : '';
	$branch  = function ($alias) { return " LEFT JOIN custbranch cb ON cb.branchcode = $alias.branchcode AND cb.debtorno = $alias.debtorno"; };
	$monthOf = function ($col) { return "DATE_FORMAT($col, '%Y-%m')"; };

	// One row per invoice number, dated in the period, not returned or in progress, and with a
	// posting (debtortrans type 10) that has not been reversed. Both invoice measures use this set,
	// so posted amount and line value cover the same invoices and differ only by tax.
	$INV = "(SELECT inv.invoiceno, MIN(inv.debtorno) debtorno, MIN(inv.branchcode) branchcode, MIN(inv.invoicesdate) invoicesdate
		FROM invoice inv WHERE inv.returned = 0 AND inv.inprogress = 0 AND inv.invoicesdate BETWEEN '$s' AND '$e'
		AND EXISTS (SELECT 1 FROM debtortrans pt WHERE pt.type = 10 AND pt.transno = inv.invoiceno AND pt.reversed = 0)
		GROUP BY inv.invoiceno)";
	$OCS = "(SELECT orderno, MIN(salescaseref) salescaseref, MIN(orddate) orddate FROM ocs GROUP BY orderno)";
	$DCS = "(SELECT orderno, MIN(salescaseref) salescaseref, MIN(orddate) orddate FROM dcs GROUP BY orderno)";
	$DC_LINE  = "dd.unitprice * (1 - dd.discountpercent) * dd.quantity * dop.quantity";
	$OC_LINE  = "od.unitprice * (1 - od.discountpercent) * od.quantity * oo.quantity";
	$INV_LINE = "idt.unitprice * (1 - idt.discountpercent) * idt.quantity * io.quantity";

	$hasRc  = sxHasTable($db, 'sales_performance_po_receipts');
	$poJoin = $hasRc ? " LEFT JOIN (SELECT salescaseref, MIN(received_at) first_received FROM sales_performance_po_receipts
			WHERE receipt_status = 'active' GROUP BY salescaseref) rc ON rc.salescaseref = sc.salescaseref" : '';
	$poExpr = $hasRc ? "COALESCE(NULLIF(sc.podate, '" . SX_ZERO_DT . "'), rc.first_received)" : "NULLIF(sc.podate, '" . SX_ZERO_DT . "')";

	/* ---------------------------------------------------------------- salespeople & target */
	$salesmen = sxRows($db, "SELECT s.salesmancode, s.salesmanname, s.target, s.`current`,
			(SELECT u.userid FROM www_users u WHERE u.realname = s.salesmanname LIMIT 1) userid
		FROM salesman s", 'Salespeople') ?: [];
	$byCode = [];
	foreach ($salesmen as $m) $byCode[$m['salesmancode']] = $m;
	$monthCount = count($P['months']);
	if ($S) {
		$annualTarget = $S['target'];
	} else {
		$annualTarget = 0.0;
		foreach ($salesmen as $m) if ((int)$m['current'] === 1) $annualTarget += (float)$m['target'];
	}
	$monthlyTarget = $annualTarget / 12;
	$periodTarget  = $monthlyTarget * $monthCount;

	/* ---------------------------------------------------------------- invoiced (posted amount) */
	$invRows = sxRows($db, "SELECT cb.salesman code, " . $monthOf('i.invoicesdate') . " ym, COUNT(*) n, SUM(dt.ovamount) v
		FROM $INV i
		INNER JOIN debtortrans dt ON dt.type = 10 AND dt.transno = i.invoiceno AND dt.reversed = 0"
		. $branch('i') . " WHERE 1 = 1 $codeF GROUP BY cb.salesman, ym", 'Invoiced');
	$invoiced = $invRows === null ? null : ['n' => 0, 'v' => 0.0];
	$invMonth = array_fill_keys(array_keys($P['months']), 0.0);
	$invByCode = [];
	foreach ((array)$invRows as $r) {
		$invoiced['n'] += (int)$r['n']; $invoiced['v'] += (float)$r['v'];
		if (isset($invMonth[$r['ym']])) $invMonth[$r['ym']] += (float)$r['v'];
		$k = $r['code'] === null ? '' : $r['code'];
		$invByCode[$k] = ($invByCode[$k] ?? 0) + (float)$r['v'];
	}

	/* ---------------------------------------------------------------- invoice line value (dashboard measure) */
	$lineRows = sxRows($db, "SELECT SUM($INV_LINE) v
		FROM $INV i
		INNER JOIN invoiceoptions io ON io.invoiceno = i.invoiceno
		INNER JOIN invoicedetails idt ON idt.invoiceno = i.invoiceno AND idt.invoicelineno = io.invoicelineno AND idt.invoiceoptionno = io.invoiceoptionno"
		. $branch('i') . " WHERE 1 = 1 $codeF", 'Invoice line value');
	$invoiceLine = $lineRows === null ? null : (float)$lineRows[0]['v'];

	/* ---------------------------------------------------------------- shop sales: CRV, CSV */
	$shopRows = sxRows($db, "SELECT LOWER(ss.payment) p, COUNT(*) n, SUM(dt.ovamount) v
		FROM shopsale ss
		INNER JOIN debtortrans dt ON dt.type = 750 AND dt.transno = ss.orderno AND dt.reversed = 0"
		. $branch('ss') . " WHERE ss.complete = 1 AND ss.orddate BETWEEN '$s' AND '$e' $codeF GROUP BY LOWER(ss.payment)", 'Shop sales');
	$crv = $csv = $shop = $shopRows === null ? null : ['n' => 0, 'v' => 0.0];
	foreach ((array)$shopRows as $r) {
		$shop['n'] += (int)$r['n']; $shop['v'] += (float)$r['v'];
		if ($r['p'] === 'crv') $crv = ['n' => (int)$r['n'], 'v' => (float)$r['v']];
		if ($r['p'] === 'csv') $csv = ['n' => (int)$r['n'], 'v' => (float)$r['v']];
	}

	/* ---------------------------------------------------------------- OC value per case (all dates) */
	$ocCaseSql = "SELECT o.salescaseref, SUM($OC_LINE) v
		FROM $OCS o
		INNER JOIN ocdetails od ON od.orderno = o.orderno
		INNER JOIN ocoptions oo ON oo.orderno = od.orderno AND oo.lineno = od.orderlineno
		WHERE od.lineoptionno = 0 AND oo.optionno = 0";

	/* ---------------------------------------------------------------- PO received in period */
	$poRows = sxRows($db, "SELECT sc.salescaseref, sc.salesman, DATE($poExpr) pd, COALESCE(ov.v, 0) oc
		FROM salescase sc $poJoin
		LEFT JOIN ($ocCaseSql GROUP BY o.salescaseref) ov ON ov.salescaseref = sc.salescaseref
		WHERE $poExpr IS NOT NULL AND DATE($poExpr) BETWEEN '$s' AND '$e' $nameF", 'PO received');
	$poReceived = $poRows === null ? null : ['n' => 0, 'v' => 0.0];
	$poMonth = array_fill_keys(array_keys($P['months']), 0.0);
	$poByName = [];
	foreach ((array)$poRows as $r) {
		$poReceived['n']++; $poReceived['v'] += (float)$r['oc'];
		$ym = substr($r['pd'], 0, 7);
		if (isset($poMonth[$ym])) $poMonth[$ym] += (float)$r['oc'];
		$poByName[$r['salesman']] = ($poByName[$r['salesman']] ?? 0) + (float)$r['oc'];
	}

	/* ---------------------------------------------------------------- OC dated in period */
	$ocRows = sxRows($db, "SELECT COUNT(DISTINCT o.orderno) n, SUM($OC_LINE) v
		FROM $OCS o
		INNER JOIN ocdetails od ON od.orderno = o.orderno
		INNER JOIN ocoptions oo ON oo.orderno = od.orderno AND oo.lineno = od.orderlineno
		LEFT JOIN salescase sc ON sc.salescaseref = o.salescaseref
		WHERE od.lineoptionno = 0 AND oo.optionno = 0 AND DATE(o.orddate) BETWEEN '$s' AND '$e' $nameF", 'OC value');
	$oc = $ocRows === null ? null : ['n' => (int)$ocRows[0]['n'], 'v' => (float)$ocRows[0]['v']];

	/* ---------------------------------------------------------------- sales orders (dashboard "Total OC Value") */
	$soRows = sxRows($db, "SELECT SUM(sd.unitprice * (1 - sd.discountpercent) * sd.quantity) v
		FROM salesorders so INNER JOIN salesorderdetails sd ON sd.orderno = so.orderno
		WHERE so.quotation = 0 AND so.orddate BETWEEN '$s' AND '$e'"
		. ($S ? " AND so.salesperson = '" . sxEsc($db, $S['code']) . "'" : ''), 'Sales orders');
	$salesOrders = $soRows === null ? null : (float)$soRows[0]['v'];

	/* ---------------------------------------------------------------- DC raised in period */
	$dcRows = sxRows($db, "SELECT " . $monthOf('d.orddate') . " ym, COUNT(DISTINCT d.orderno) n, SUM($DC_LINE) v
		FROM $DCS d
		INNER JOIN dcdetails dd ON dd.orderno = d.orderno
		INNER JOIN dcoptions dop ON dop.orderno = dd.orderno AND dop.lineno = dd.orderlineno
		LEFT JOIN salescase sc ON sc.salescaseref = d.salescaseref
		WHERE dd.lineoptionno = 0 AND dop.optionno = 0 AND d.orddate BETWEEN '$s' AND '$e' $nameF
		GROUP BY ym", 'DC raised');
	$dcRaised = $dcRows === null ? null : ['n' => 0, 'v' => 0.0];
	$dcMonth = array_fill_keys(array_keys($P['months']), 0.0);
	foreach ((array)$dcRows as $r) {
		$dcRaised['n'] += (int)$r['n']; $dcRaised['v'] += (float)$r['v'];
		if (isset($dcMonth[$r['ym']])) $dcMonth[$r['ym']] = (float)$r['v'];
	}

	/* ---------------------------------------------------------------- pending DC today (Pending DC List rule) */
	// DCs listed in a DC group are excluded too: without that list the pending figure is not reliable.
	$grouped = sxGroupedDcNumbers($db);
	if ($grouped === null) $GLOBALS['sxErrors'][] = ['figure' => 'DC groups', 'error' => mysqli_error($db)];
	$pendRows = sxRows($db, "SELECT d.orderno dcno, MIN(d.orddate) orddate, MIN(d.salescaseref) salescaseref,
			MIN(sc.salesman) salesman, MIN(dm.name) client, SUM($DC_LINE) v,
			CASE WHEN MIN(d.gst) LIKE '%inclusive%' THEN SUM($DC_LINE) * 0.83 ELSE SUM($DC_LINE) END vx
		FROM (SELECT orderno, MIN(salescaseref) salescaseref, MIN(orddate) orddate, MIN(gst) gst FROM dcs
			WHERE courierslipdate = '" . SX_ZERO_DT . "' AND invoicedate = '" . SX_ZERO_DT . "'
			AND grbdate = '" . SX_ZERO_DT . "' AND invoicegroupid IS NULL GROUP BY orderno) d
		INNER JOIN dcdetails dd ON dd.orderno = d.orderno
		INNER JOIN dcoptions dop ON dop.orderno = dd.orderno AND dop.lineno = dd.orderlineno
		INNER JOIN salescase sc ON sc.salescaseref = d.salescaseref
		INNER JOIN debtorsmaster dm ON dm.debtorno = sc.debtorno
		WHERE dd.lineoptionno = 0 AND dop.optionno = 0 $nameF
		GROUP BY d.orderno", 'Pending DC');
	if ($grouped === null) $pendRows = null;
	$pending = $pendRows === null ? null : ['n' => 0, 'v' => 0.0, 'vExcl' => 0.0, 'inPeriodN' => 0, 'inPeriodV' => 0.0];
	$ageing = ['0–30 days' => ['n' => 0, 'v' => 0.0], '31–60 days' => ['n' => 0, 'v' => 0.0],
		'61–90 days' => ['n' => 0, 'v' => 0.0], 'Over 90 days' => ['n' => 0, 'v' => 0.0]];
	$oldPending = [];
	$pendByName = [];
	foreach ((array)$pendRows as $r) {
		if (isset($grouped[(string)$r['dcno']])) continue;
		$v = (float)$r['v'];
		$pending['n']++; $pending['v'] += $v; $pending['vExcl'] += (float)$r['vx'];
		if ($r['orddate'] >= $s && $r['orddate'] <= $e) { $pending['inPeriodN']++; $pending['inPeriodV'] += $v; }
		$age = (int)floor((strtotime($today) - strtotime($r['orddate'])) / 86400);
		$bucket = $age <= 30 ? '0–30 days' : ($age <= 60 ? '31–60 days' : ($age <= 90 ? '61–90 days' : 'Over 90 days'));
		$ageing[$bucket]['n']++; $ageing[$bucket]['v'] += $v;
		if ($age > 30) $oldPending[] = ['dc' => $r['dcno'], 'date' => $r['orddate'], 'age' => $age, 'case' => $r['salescaseref'],
			'client' => $r['client'], 'salesperson' => $r['salesman'], 'v' => $v];
		$pendByName[$r['salesman']] = ($pendByName[$r['salesman']] ?? 0) + $v;
	}
	usort($oldPending, function ($a, $b) { return $b['v'] <=> $a['v']; });
	$oldPending = array_slice($oldPending, 0, 15);

	/* ---------------------------------------------------------------- outstanding today (Outstanding Invoices rule) */
	$REM = "CASE WHEN GSTwithhold = 0 AND WHT = 0 THEN ovamount - alloc
		WHEN GSTwithhold = 0 AND WHT = 1 THEN ovamount - alloc - WHTamt
		WHEN GSTwithhold = 1 AND WHT = 0 THEN ovamount - alloc - GSTamt
		WHEN GSTwithhold = 1 AND WHT = 1 THEN ovamount - alloc - GSTamt - WHTamt END";
	$outRows = sxRows($db, "SELECT sc.salesman, COUNT(*) n, SUM($REM) v,
			SUM(CASE WHEN invoice.due > '0000-00-00' AND invoice.due < CURDATE() THEN 1 ELSE 0 END) on_,
			SUM(CASE WHEN invoice.due > '0000-00-00' AND invoice.due < CURDATE() THEN $REM ELSE 0 END) ov
		FROM debtorsmaster
		INNER JOIN invoice ON invoice.debtorno = debtorsmaster.debtorno AND invoice.returned = 0
		INNER JOIN salescase sc ON invoice.salescaseref = sc.salescaseref
		INNER JOIN dcgroups ON dcgroups.id = invoice.groupid
		INNER JOIN debtortrans ON debtortrans.transno = invoice.invoiceno AND debtortrans.type = 10
			AND debtortrans.reversed = 0 AND debtortrans.settled = 0
		WHERE 1 = 1 $nameF GROUP BY sc.salesman", 'Outstanding');
	$outstanding = $outRows === null ? null : ['n' => 0, 'v' => 0.0, 'overdueN' => 0, 'overdueV' => 0.0];
	$outByName = [];
	foreach ((array)$outRows as $r) {
		$outstanding['n'] += (int)$r['n']; $outstanding['v'] += (float)$r['v'];
		$outstanding['overdueN'] += (int)$r['on_']; $outstanding['overdueV'] += (float)$r['ov'];
		$outByName[$r['salesman']] = (float)$r['v'];
	}

	/* ---------------------------------------------------------------- cart today */
	$cartF = '';
	if ($S) {
		$keys = array_filter([$S['code'], $S['name'], $S['userid']], function ($k) { return $k !== ''; });
		$cartF = " AND si.salesperson IN ('" . implode("','", array_map(function ($k) use ($db) { return sxEsc($db, $k); }, $keys)) . "')";
	}
	$cartRows = sxRows($db, "SELECT si.salesperson sp, SUM(si.issued * sm.materialcost) v
		FROM stockissuance si INNER JOIN stockmaster sm ON sm.stockid = si.stockid
		WHERE si.issued > 0 AND (sm.mbflag = 'B' OR sm.mbflag = 'M') $cartF GROUP BY si.salesperson", 'Cart value');
	$cart = $cartRows === null ? null : 0.0;
	$cartBy = [];
	foreach ((array)$cartRows as $r) { $cart += (float)$r['v']; $cartBy[$r['sp']] = (float)$r['v']; }

	/* ---------------------------------------------------------------- items: brand, self vs other, top items */
	$itemRows = sxRows($db, "SELECT idt.stkcode code, MAX(sm.description) description, MAX(mf.manufacturers_name) brand,
			MAX(sm.mbflag) mbflag, SUM(idt.quantity * io.quantity) qty, SUM($INV_LINE) v
		FROM $INV i
		INNER JOIN invoiceoptions io ON io.invoiceno = i.invoiceno
		INNER JOIN invoicedetails idt ON idt.invoiceno = i.invoiceno AND idt.invoicelineno = io.invoicelineno AND idt.invoiceoptionno = io.invoiceoptionno
		LEFT JOIN stockmaster sm ON sm.stockid = idt.stkcode
		LEFT JOIN manufacturers mf ON mf.manufacturers_id = sm.brand"
		. $branch('i') . " WHERE 1 = 1 $codeF GROUP BY idt.stkcode", 'Items');
	$brands = []; $self = ['Self brand' => 0.0, 'Other brands' => 0.0, 'Not classified' => 0.0];
	foreach ((array)$itemRows as $r) {
		$v = (float)$r['v'];
		$b = $r['brand'] ?: 'Not set';
		$brands[$b] = ($brands[$b] ?? 0) + $v;
		$self[$r['mbflag'] === 'M' ? 'Self brand' : ($r['mbflag'] === 'B' ? 'Other brands' : 'Not classified')] += $v;
	}
	arsort($brands);
	$brandList = []; $other = 0.0; $i = 0;
	foreach ($brands as $b => $v) { if ($i++ < 8) $brandList[] = ['name' => $b, 'v' => $v]; else $other += $v; }
	if ($other > 0) $brandList[] = ['name' => 'All other brands', 'v' => $other];
	$items = (array)$itemRows;
	usort($items, function ($a, $b) { return (float)$b['v'] <=> (float)$a['v']; });
	$topItems = array_map(function ($r) { return ['code' => $r['code'], 'description' => $r['description'], 'brand' => $r['brand'],
		'qty' => (float)$r['qty'], 'v' => (float)$r['v']]; }, array_slice($items, 0, 15));

	/* ---------------------------------------------------------------- sales cases opened in period */
	$caseSet = "(SELECT salescaseref FROM salescase sc WHERE DATE(sc.commencementdate) BETWEEN '$s' AND '$e' $nameF)";
	$caseRows = sxRows($db, "SELECT sc.salescaseref, sc.salesman, DATE(sc.commencementdate) opened, sc.closingreason,
			dm.name client, DATE($poExpr) pd, (sc.podate <> '" . SX_ZERO_DT . "') pofile
		FROM salescase sc $poJoin LEFT JOIN debtorsmaster dm ON dm.debtorno = sc.debtorno
		WHERE DATE(sc.commencementdate) BETWEEN '$s' AND '$e' $nameF", 'Sales cases');
	// Latest quotation only: salesorders also holds confirmed orders (quotation = 0).
	$lastQuote = sxRows($db, "SELECT so.salescaseref, MAX(so.orderno) orderno FROM salesorders so
		INNER JOIN $caseSet c ON c.salescaseref = so.salescaseref WHERE so.quotation = 1 GROUP BY so.salescaseref", 'Quotations');
	$quoteVal = sxRows($db, "SELECT so.salescaseref, SUM(sd.unitprice * (1 - sd.discountpercent) * sd.quantity * sop.quantity) v
		FROM salesorders so
		INNER JOIN (SELECT salescaseref, MAX(orderno) mx FROM salesorders WHERE quotation = 1 GROUP BY salescaseref) lq ON lq.mx = so.orderno
		INNER JOIN $caseSet c ON c.salescaseref = so.salescaseref
		INNER JOIN salesorderdetails sd ON sd.orderno = so.orderno
		INNER JOIN salesorderoptions sop ON sop.orderno = sd.orderno AND sop.lineno = sd.orderlineno
		WHERE sd.lineoptionno = 0 AND sop.optionno = 0 GROUP BY so.salescaseref", 'Quotation value');
	$ocCase = sxRows($db, "SELECT t.salescaseref, t.v FROM ($ocCaseSql GROUP BY o.salescaseref) t
		INNER JOIN $caseSet c ON c.salescaseref = t.salescaseref", 'OC by case');
	$dcCase = sxRows($db, "SELECT d.salescaseref, SUM($DC_LINE) v
		FROM $DCS d
		INNER JOIN $caseSet c ON c.salescaseref = d.salescaseref
		INNER JOIN dcdetails dd ON dd.orderno = d.orderno
		INNER JOIN dcoptions dop ON dop.orderno = dd.orderno AND dop.lineno = dd.orderlineno
		WHERE dd.lineoptionno = 0 AND dop.optionno = 0 GROUP BY d.salescaseref", 'DC by case');
	$invCase = sxRows($db, "SELECT x.salescaseref, SUM(dt.ovamount) v
		FROM (SELECT invoiceno, MIN(salescaseref) salescaseref FROM invoice WHERE returned = 0 AND inprogress = 0 GROUP BY invoiceno) x
		INNER JOIN $caseSet c ON c.salescaseref = x.salescaseref
		INNER JOIN debtortrans dt ON dt.type = 10 AND dt.transno = x.invoiceno AND dt.reversed = 0
		GROUP BY x.salescaseref", 'Invoiced by case');

	$map = function ($rows, $col) { $m = []; foreach ((array)$rows as $r) $m[$r['salescaseref']] = $r[$col]; return $m; };
	$mQuoteNo = $map($lastQuote, 'orderno'); $mQuote = $map($quoteVal, 'v'); $mOc = $map($ocCase, 'v');
	$mDc = $map($dcCase, 'v'); $mInv = $map($invCase, 'v');

	$statusOrder = ['PO received', 'In pipeline', 'Lost', 'Cancelled', 'Out of scope', 'Quoted at zero', 'No quotation', 'Closed, other reason'];
	$status = array_fill_keys($statusOrder, ['n' => 0, 'v' => 0.0]);
	$cases = ['total' => 0, 'quotedN' => 0, 'quotedV' => 0.0, 'poN' => 0, 'poQuotedN' => 0, 'poQuoteV' => 0.0, 'poOcV' => 0.0,
		'dcV' => 0.0, 'invV' => 0.0, 'anomalyN' => 0];
	$caseList = []; $anomalies = []; $stale = [];
	$quoteByName = []; $winByName = []; $pipeByName = [];
	foreach ((array)$caseRows as $r) {
		$ref = $r['salescaseref'];
		$hasQuote = isset($mQuoteNo[$ref]);
		$q = (float)($mQuote[$ref] ?? 0);
		$ocv = (float)($mOc[$ref] ?? 0);
		$dcv = (float)($mDc[$ref] ?? 0);
		$inv = (float)($mInv[$ref] ?? 0);
		$st = sxCaseStatus($r['pd'], $hasQuote, $q, $r['closingreason']);
		$cases['total']++;
		$status[$st]['n']++; $status[$st]['v'] += $q;
		if ($q > 0) { $cases['quotedN']++; $cases['quotedV'] += $q; }
		if ($st === 'PO received') { $cases['poN']++; $cases['poQuoteV'] += $q; $cases['poOcV'] += $ocv; if ($q > 0) $cases['poQuotedN']++; }
		$cases['dcV'] += $dcv; $cases['invV'] += $inv;
		$sp = $r['salesman'];
		if ($q > 0) $quoteByName[$sp] = ($quoteByName[$sp] ?? 0) + $q;
		if ($st === 'PO received' && $q > 0) $winByName[$sp] = ($winByName[$sp] ?? 0) + $q;
		if ($st === 'In pipeline') $pipeByName[$sp] = ($pipeByName[$sp] ?? 0) + $q;
		if ($r['pd'] !== null && !$hasQuote) {
			$cases['anomalyN']++;
			$anomalies[] = ['case' => $ref, 'client' => $r['client'], 'salesperson' => $sp, 'po' => $r['pd'], 'oc' => $ocv];
		}
		if ($st === 'In pipeline' && $r['opened'] < date('Y-m-d', strtotime('-90 days'))) {
			$stale[] = ['case' => $ref, 'client' => $r['client'], 'salesperson' => $sp, 'opened' => $r['opened'], 'quote' => $q];
		}
		$caseList[] = ['case' => $ref, 'client' => $r['client'], 'salesperson' => $sp, 'opened' => $r['opened'],
			'quoteNo' => $mQuoteNo[$ref] ?? null, 'quote' => $q, 'status' => $st, 'po' => $r['pd'],
			'poSource' => $r['pd'] === null ? null : ((int)$r['pofile'] === 1 ? 'PO file' : 'Recorded PO'),
			'oc' => $ocv, 'dc' => $dcv, 'invoiced' => $inv];
	}
	usort($stale, function ($a, $b) { return $b['quote'] <=> $a['quote']; });
	$cases['successValue'] = $cases['quotedV'] > 0 ? $cases['poOcV'] / $cases['quotedV'] : null;
	$cases['successCount'] = $cases['quotedN'] > 0 ? $cases['poQuotedN'] / $cases['quotedN'] : null;
	$cases['status'] = [];
	foreach ($statusOrder as $k) $cases['status'][] = ['name' => $k, 'n' => $status[$k]['n'], 'v' => $status[$k]['v']];
	if ($caseRows === null) $cases = null;

	/* ---------------------------------------------------------------- team */
	$team = [];
	$people = $S ? [$byCode[$S['code']] ?? ['salesmancode' => $S['code'], 'salesmanname' => $S['name'], 'target' => $S['target'], 'current' => 1, 'userid' => $S['userid']]]
		: array_values(array_filter($salesmen, function ($m) { return (int)$m['current'] === 1; }));
	foreach ($people as $m) {
		$code = $m['salesmancode']; $name = $m['salesmanname'];
		$tgt = (float)$m['target'] / 12 * $monthCount;
		$inv = (float)($invByCode[$code] ?? 0);
		$q = (float)($quoteByName[$name] ?? 0);
		// Cart issuance is keyed by whatever the DC request stored: code, name or user id.
		$cartV = 0.0;
		foreach (array_unique(array_filter([$code, $name, (string)($m['userid'] ?? '')], 'strlen')) as $k) $cartV += (float)($cartBy[$k] ?? 0);
		$team[] = ['code' => $code, 'name' => $name, 'target' => $tgt, 'invoiced' => $inv,
			'achievement' => $tgt > 0 ? $inv / $tgt : null, 'po' => (float)($poByName[$name] ?? 0),
			'quoted' => $q, 'win' => $q > 0 ? (float)($winByName[$name] ?? 0) / $q : null,
			'pipeline' => (float)($pipeByName[$name] ?? 0), 'pendingDc' => (float)($pendByName[$name] ?? 0),
			'outstanding' => (float)($outByName[$name] ?? 0),
			'cart' => $cartV];
	}
	if (!$S && isset($invByCode['']) && $invByCode[''] > 0) {
		$team[] = ['code' => '', 'name' => 'Branch without salesperson', 'target' => 0, 'invoiced' => $invByCode[''],
			'achievement' => null, 'po' => 0, 'quoted' => 0, 'win' => null, 'pipeline' => 0, 'pendingDc' => 0, 'outstanding' => 0, 'cart' => 0];
	}
	usort($team, function ($a, $b) { return ($b['achievement'] ?? -1) <=> ($a['achievement'] ?? -1); });

	$num = function ($v) { return $v === null ? null : round((float)$v, 2); };
	return [
		'period' => ['start' => $s, 'end' => $e, 'preset' => $P['preset'], 'months' => array_keys($P['months']),
			'monthLabels' => array_values($P['months']), 'monthCount' => $monthCount],
		'scope' => $S ? $S['name'] : 'All salespeople',
		'kpi' => [
			'target' => $num($periodTarget), 'annualTarget' => $num($annualTarget),
			'invoiced' => $invoiced, 'achievement' => ($invoiced !== null && $periodTarget > 0) ? $invoiced['v'] / $periodTarget : null,
			'invoiceLine' => $num($invoiceLine), 'crv' => $crv, 'csv' => $csv, 'shop' => $shop,
			'poReceived' => $poReceived, 'oc' => $oc, 'salesOrders' => $num($salesOrders), 'dcRaised' => $dcRaised,
			'pending' => $pending, 'outstanding' => $outstanding, 'cart' => $num($cart),
			'businessVolume' => ($invoiceLine === null || $shop === null || $pending === null) ? null
				: round($invoiceLine + $shop['v'] + $pending['inPeriodV'], 2),
		],
		'cases' => $cases,
		// One row of values per case, with the field names once: the list can run to thousands of cases.
		'caseList' => ['cols' => SX_CASE_COLS, 'rows' => array_map(function ($c) {
			return [$c['case'], $c['client'], $c['salesperson'], $c['opened'], $c['quoteNo'], round($c['quote'], 2),
				$c['status'], $c['po'], $c['poSource'], round($c['oc'], 2), round($c['dc'], 2), round($c['invoiced'], 2)];
		}, $caseList)],
		'anomalies' => $anomalies,
		'stale' => array_slice($stale, 0, 15),
		'staleCount' => count($stale),
		'ageing' => array_map(function ($k) use ($ageing) { return ['name' => $k, 'n' => $ageing[$k]['n'], 'v' => $ageing[$k]['v']]; }, array_keys($ageing)),
		'oldPending' => $oldPending,
		'trend' => ['invoiced' => array_values($invMonth), 'target' => array_fill(0, $monthCount, round($monthlyTarget, 2)),
			'po' => array_values($poMonth), 'dc' => array_values($dcMonth)],
		'team' => $team,
		'brands' => $brandList,
		'self' => array_map(function ($k) use ($self) { return ['name' => $k, 'v' => $self[$k]]; }, array_keys($self)),
		'topItems' => $topItems,
		'poReceiptsInstalled' => $hasRc,
		'errors' => $GLOBALS['sxErrors'],
	];
}

/* ---------------------------------------------------------------- cache storage
 * The shared `cache` table stores `value` as TEXT (64 KB) in latin1. A large or non-latin1
 * payload written as-is is silently cut off or altered, so payloads are gzip-compressed and
 * base64-encoded (plain ASCII), written only when they fit the column, and checked on read.
 */

/**
 * Cache table to use: the report's own table (MEDIUMTEXT, created by
 * migrations/sales_executive_report.sql) when it exists, else the shared `cache` table.
 */
function sxCacheTable($db) {
	static $table = null;
	if ($table === null) $table = sxHasTable($db, 'sales_executive_cache') ? 'sales_executive_cache' : 'cache';
	return $table;
}

/** Largest value the cache column can hold, read from the table itself; 0 when unknown. */
function sxCacheCapacity($db) {
	$r = @mysqli_query($db, "SHOW COLUMNS FROM `" . sxCacheTable($db) . "` LIKE 'value'");
	$row = $r ? mysqli_fetch_assoc($r) : null;
	$type = strtolower((string)($row['Type'] ?? ''));
	if (strpos($type, 'longtext') === 0) return 4294967295;
	if (strpos($type, 'mediumtext') === 0) return 16777215;
	if (strpos($type, 'text') === 0) return 65535;
	return 0;
}

/** Compressed, ASCII-only form of a JSON payload, or null when compression is unavailable. */
function sxCachePack($json) {
	return function_exists('gzcompress') ? 'gz1:' . base64_encode(gzcompress($json, 6)) : null;
}

/** The JSON inside a stored value, or null when it is missing, foreign or damaged. */
function sxCacheUnpack($stored) {
	if (!is_string($stored) || strncmp($stored, 'gz1:', 4) !== 0 || !function_exists('gzuncompress')) return null;
	$raw = base64_decode(substr($stored, 4), true);
	$json = $raw === false ? false : @gzuncompress($raw);
	if (!is_string($json) || json_decode($json) === null) return null;
	return $json;
}
