<?php
/** Adversarial operational fixture. Requires the disposable finance fixture DB. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';
use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;

function operationCheck($condition, $message) { if (!$condition) { throw new RuntimeException($message); } echo 'PASS: ' . $message . "\n"; }
function operationNear($actual, $expected, $message) { operationCheck($actual !== null && abs($actual - $expected) < 0.000001, $message . ' (expected ' . $expected . ')'); }
try {
    $database = getenv('BI_FIXTURE_DB_NAME');
    if (!$database || !preg_match('/^codex_directors_fixture_[a-z0-9_]+$/', $database) || !getenv('BI_TEST_DB_HOST') || !getenv('BI_TEST_DB_USER')) {
        throw new RuntimeException('An explicit disposable codex_directors_fixture_* database and server are required.');
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', $database, (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    $db->query("SET SESSION sql_mode=''");
    $db->query('SET SESSION FOREIGN_KEY_CHECKS=0');
    if (getenv('BI_OPERATIONS_FIXTURE_LOAD') === '1') {
        operationCheck((int) $db->query('SELECT COUNT(*) n FROM stockmaster')->fetch_assoc()['n'] === 0, 'Operations fixture starts empty');
        $db->multi_query(file_get_contents(__DIR__ . '/fixtures/directors_operations.sql'));
        do { if ($result = $db->store_result()) { $result->free(); } } while ($db->more_results() && $db->next_result());
    }
    $context = new AuthorizationContext('fixture-director', $database, 'Fixture', 8, true, array('*'), array(0));
    $service = new DirectorsService($db, $context, array(), 'PKR', null, '/erp/');
    $base = array('start' => '2024-01-01', 'end' => '2024-01-31', 'comparison' => 'previous', 'action' => 'explore');
    $get = function ($dataset, array $extra = array()) use ($service, $base) {
        return $service->explore(DirectorsRequest::fromArray(array_merge($base, array('dataset' => $dataset), $extra), 12, '2026-11-05'));
    };
    $invoice = $get('invoice_value');
    operationNear($invoice['summary']['value'], 740, 'Invoice published multiplicative formula');
    operationCheck($invoice['summary']['rows'] === 5 && $invoice['summary']['invalid_amounts'] === 3, 'Missing option, duplicate header and unsupported currency stay unknown');
    operationCheck($invoice['summary']['delta'] === null && $invoice['metric']['comparison_complete'] === false, 'Partial totals do not claim reliable movement');
    operationCheck(count(array_filter($invoice['breakdown'], function ($row) { return $row['rows'] > 0; })) === 3, 'Same-name customers retain distinct code groups');
    operationCheck(strpos($invoice['details']['rows'][0]['source_url'], '/erp/') === 0, 'Source links use deployment prefix');
    operationNear($get('invoice_value', array('filters' => array('status' => 'Completed')))['summary']['value'], 740, 'Exact status drill-down reconciles');
    $unknown = $get('invoice_value', array('filters' => array('customer' => 'C3 · Foreign customer')));
    operationCheck($unknown['summary']['value'] === null && $unknown['breakdown'][0]['value'] === null, 'All-invalid group never turns into a zero');
    operationNear($get('shop_sales')['summary']['value'], 60, 'Shop sale unique posted value and FX');
    operationCheck($get('shop_sales')['summary']['invalid_amounts'] === 2, 'Duplicate and missing shop postings stay visible');
    operationNear($get('purchasing')['summary']['value'], 100, 'MPIW received value by slip date excludes discarded and returned slips');
    operationNear($get('purchase_commitments')['summary']['value'], 170, 'Unreceived market-slip estimate includes draft separately');
    operationNear($get('inventory')['summary']['value'], 45, 'Inventory uses material plus labour plus overhead and signed negatives');
    operationNear($get('stock_exceptions')['summary']['value'], 4, 'Stock exceptions count positions once');
    operationNear($get('open_cases')['summary']['value'], 3, 'Duplicate case references remain unknown counts');
    operationNear($get('pipeline')['summary']['value'], 15000, 'Pipeline remains entered unweighted estimate');
    operationNear($get('work_orders')['summary']['value'], 1, 'Duplicate work orders cannot inflate backlog');
    operationNear($get('pending_deliveries')['summary']['value'], 1000, 'Pending DC List value: first-option lines only, excludes grouped and invoiced documents');
    operationCheck($get('pending_deliveries')['summary']['rows'] === 1, 'Pending DC List counts one DC; a DC with no first-option lines is not on the list');
    operationNear($get('petty_cash')['summary']['value'], 170, 'Petty cash separates funding, retains signed credits and PKR policy');
    operationNear($get('legacy_po_commitments')['summary']['value'], 30, 'Legacy PO lifecycle, residual quantity and recorded FX');
    operationNear($get('procurement_pipeline')['summary']['value'], 90, 'Procurement plan lifecycle and unknown prices');
    $delegated = new AuthorizationContext('delegated-director', $database, 'Fixture', 1, false, array('directorreports'), array(2));
    $restricted = new DirectorsService($db, $delegated, array('PageSecurityArray' => array('PcReportTab.php' => 2)), 'PKR');
    $restrictedReport = $restricted->explore(DirectorsRequest::fromArray($base + array('dataset' => 'petty_cash'), 12, '2026-11-05'));
    operationNear($restrictedReport['summary']['value'], 80, 'Expense-tab authorization applies without fanout from repeated grants');
    operationCheck(count($restrictedReport['details']['rows']) === 2, 'Restricted detail shares summary scope');
    operationNear($restricted->record('petty_cash', '1')['row']['amount'], 100, 'Exact source-row evidence uses authorized fact');
    $denied = false;
    try { $restricted->record('petty_cash', '4'); } catch (\SAHamid\BI\Exception\BIException $e) { $denied = $e->getHttpStatus() === 404; }
    operationCheck($denied, 'Direct record URL cannot cross expense-tab boundary');
    echo "PASS: operational source reconciliation complete; no operational company data was accessed.\n";
} catch (Throwable $e) { fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n"); exit(1); }
