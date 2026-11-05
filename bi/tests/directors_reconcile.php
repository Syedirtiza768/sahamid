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
    $context = new AuthorizationContext('reconcile', getenv('BI_TEST_DB_NAME'), 'Explicit verification target', 8, true, array('*'), array(0), null);
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
    echo $failures ? 'FAILED: ' . implode('; ', $failures) . "\n" : "All sales measures reconcile with the approved report rules.\n";
    exit($failures ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
