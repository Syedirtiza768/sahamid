<?php
/**
 * Read-only reconciliation of the Directors sales measures with the approved
 * Sales Executive Report rules (v2/includes/salesExecutiveData.php).
 *
 * Needs an explicit target: BI_TEST_DB_HOST/PORT/USER/PASSWORD/NAME. The session is
 * set to READ ONLY. It does not read config.php, change data or apply migrations.
 * Periods: BI_RECON_PERIODS="2026-09-01:2026-09-30,2026-04-01:2026-11-05" (default: last complete month).
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';
require_once dirname(__DIR__, 2) . '/v2/includes/salesExecutiveData.php';

use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;

$failures = array();
function reconcileCheck($label, $actual, $expected, $tolerance, array &$failures)
{
    $ok = $actual !== null && abs((float) $actual - (float) $expected) <= $tolerance;
    echo ($ok ? 'PASS: ' : 'FAIL: ') . $label . ' dashboard=' . var_export($actual, true) . ' source=' . var_export($expected, true) . "\n";
    if (!$ok) { $failures[] = $label; }
}

try {
    foreach (array('BI_TEST_DB_HOST', 'BI_TEST_DB_USER', 'BI_TEST_DB_NAME') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_OFF);
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', getenv('BI_TEST_DB_NAME'), (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    if ($db->connect_errno) { throw new RuntimeException('Cannot connect to the explicit target.'); }
    $db->set_charset('utf8mb4');
    $db->query('SET SESSION TRANSACTION READ ONLY');
    $context = new AuthorizationContext(getenv('BI_TEST_USER_ID') ?: 'reconcile', getenv('BI_TEST_DB_NAME'), 'Explicit verification target', 8, true, array('*'), array(0), null);
    $service = new DirectorsService($db, $context, array(), 'PKR');
    $periods = getenv('BI_RECON_PERIODS') ?: date('Y-m-01', strtotime('first day of previous month')) . ':' . date('Y-m-t', strtotime('first day of previous month'));
    foreach (explode(',', $periods) as $range) {
        list($start, $end) = explode(':', $range);
        echo "== $start to $end\n";
        $source = sxCompute($db, sxPeriod('custom', $start, $end), null);
        if (!empty($GLOBALS['sxErrors'])) { throw new RuntimeException('Source report failed: ' . json_encode($GLOBALS['sxErrors'])); }
        $kpi = $source['kpi'];
        $get = function ($dataset) use ($service, $start, $end) {
            return $service->explore(DirectorsRequest::fromArray(array('action' => 'explore', 'dataset' => $dataset, 'start' => $start, 'end' => $end, 'comparison' => 'none', 'page_size' => 1)))['summary'];
        };
        $s = $get('posted_invoices');
        reconcileCheck('Posted invoices value', $s['value'], $kpi['invoiced']['v'], 0.01, $failures);
        reconcileCheck('Posted invoices count', $s['rows'], $kpi['invoiced']['n'], 0, $failures);
        $s = $get('shop_sales');
        reconcileCheck('Shop sales value', $s['value'], $kpi['shop']['v'], 0.01, $failures);
        $s = $get('invoice_value');
        reconcileCheck('Invoice line value', $s['value'], $kpi['invoiceLine'], 0.01, $failures);
        $s = $get('po_received');
        reconcileCheck('PO received value', $s['value'], $kpi['poReceived']['v'], 0.01, $failures);
        reconcileCheck('PO received count', $s['rows'], $kpi['poReceived']['n'], 0, $failures);
    }
    // Snapshots do not depend on the period.
    $s = $get('pending_deliveries');
    reconcileCheck('Pending DC List value', $s['value'], $kpi['pending']['v'], 0.01, $failures);
    reconcileCheck('Pending DC List count', $s['rows'], $kpi['pending']['n'], 0, $failures);
    $s = $get('outstanding_invoices');
    reconcileCheck('Outstanding value', $s['value'], $kpi['outstanding']['v'], 0.01, $failures);
    reconcileCheck('Outstanding count', $s['rows'], $kpi['outstanding']['n'], 0, $failures);
    reconcileCheck('Outstanding overdue value', $s['overdue'], $kpi['outstanding']['overdueV'], 0.01, $failures);
    // ---- derived measures, each against independently written SQL on the last period above ---------------------
    $overview = $service->overview(DirectorsRequest::fromArray(array('start' => $start, 'end' => $end, 'comparison' => 'yoy')));
    $byId = array_column($overview['metrics'], null, 'id');
    $days = $overview['metadata']['period']['days'];
    reconcileCheck('DSO = outstanding / invoiced x days', $byId['dso_days']['value'], $kpi['outstanding']['v'] / $kpi['invoiced']['v'] * $days, 0.0001, $failures);
    $posted = "(SELECT inv.invoiceno, MIN(inv.debtorno) debtorno, MIN(inv.invoicesdate) invoicesdate, MIN(inv.salescaseref) ref FROM invoice inv
        WHERE inv.returned = 0 AND inv.inprogress = 0 AND EXISTS (SELECT 1 FROM debtortrans pt WHERE pt.type = 10 AND pt.transno = inv.invoiceno AND pt.reversed = 0) GROUP BY inv.invoiceno)";
    $sums = array();
    $result = $db->query("SELECT SUM(dt.ovamount) v FROM $posted i JOIN debtortrans dt ON dt.type = 10 AND dt.transno = i.invoiceno AND dt.reversed = 0
        WHERE i.invoicesdate BETWEEN '$start' AND '$end' GROUP BY i.debtorno ORDER BY v DESC");
    while ($row = $result->fetch_assoc()) { $sums[] = (float) $row['v']; }
    $positive = array_filter($sums, function ($v) { return $v > 0; });
    reconcileCheck('Top-10 customer share', $byId['customer_concentration_pct']['value'], array_sum(array_slice($positive, 0, 10)) / array_sum($positive) * 100, 0.0001, $failures);
    $result = $db->query("SELECT DATEDIFF(i.invoicesdate, o.first_oc) d FROM $posted i
        JOIN (SELECT salescaseref, MIN(orddate) first_oc FROM ocs WHERE orddate > '1900-01-01' GROUP BY salescaseref) o ON o.salescaseref = i.ref
        WHERE i.invoicesdate BETWEEN '$start' AND '$end' AND DATEDIFF(i.invoicesdate, o.first_oc) >= 0 ORDER BY d");
    $d = array();
    while ($row = $result->fetch_assoc()) { $d[] = (int) $row['d']; }
    $n = count($d);
    reconcileCheck('Order-to-invoice median days', $byId['order_to_invoice_days']['value'], $n % 2 ? $d[intdiv($n, 2)] : ($d[$n / 2 - 1] + $d[$n / 2]) / 2, 0.0001, $failures);
    $userId = $context->getUserId();
    $bank = $db->prepare('SELECT SUM(t.amount / t.exrate / t.functionalexrate) v, COUNT(*) n FROM banktrans t WHERE t.exrate > 0 AND t.functionalexrate > 0 AND t.transdate <= CURDATE() AND t.bankact IN (SELECT accountcode FROM bankaccountusers WHERE userid = ?)');
    $bank->bind_param('s', $userId);
    $bank->execute();
    $bankRow = $bank->get_result()->fetch_assoc();
    if ((int) $bankRow['n'] > 0) { reconcileCheck('Bank book balance for the user accounts', $byId['bank_balance']['value'], $bankRow['v'], 0.01, $failures); }
    if ((int) $byId['payables']['unknown_due'] > 0 && $byId['payables_due_soon']['rows'] === 0) {
        $unavailable = $byId['payables_due_soon']['available'] === false && strpos((string) $byId['payables_due_soon']['error'], 'no recorded due date') !== false;
        reconcileCheck('Payments due is unavailable, not zero, when no balance has a due date', $unavailable ? 1 : 0, 1, 0, $failures);
    }
    echo $failures ? 'FAILED: ' . implode('; ', $failures) . "\n" : "All sales measures reconcile with the approved report rules.\n";
    exit($failures ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
