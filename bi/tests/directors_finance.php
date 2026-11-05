<?php
/**
 * Native MariaDB finance fixture reconciliation. Never uses config.php or
 * production credentials. Creates only a database named codex_directors_fixture_*.
 *
 * BI_TEST_DB_HOST/PORT/USER/PASSWORD specify an isolated development server.
 * BI_FIXTURE_DB_NAME must explicitly name the disposable database to create.
 * Optional BI_TEST_SCHEMA points to the application's structural SQL dump.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\FinanceDatasetCatalog;
use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;

function directorsFinanceAssert($condition, $message)
{
    if (!$condition) { throw new RuntimeException($message); }
}
function directorsFinanceNear($actual, $expected, $message)
{
    directorsFinanceAssert($actual !== null && abs((float) $actual - (float) $expected) < 0.000001,
        $message . ': expected ' . $expected . ', received ' . var_export($actual, true));
}
function directorsFinanceRows($db, array $definition)
{
    $statement = $db->prepare($definition['sql']);
    if ($definition['types'] !== '') {
        $params = $definition['params'];
        $bindings = array($definition['types']);
        foreach ($params as $index => $value) { $bindings[] = &$params[$index]; }
        call_user_func_array(array($statement, 'bind_param'), $bindings);
    }
    $statement->execute();
    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

try {
    $database = getenv('BI_FIXTURE_DB_NAME');
    if (!$database || !preg_match('/^codex_directors_fixture_[a-z0-9_]+$/', $database)) {
        throw new RuntimeException('Set BI_FIXTURE_DB_NAME=codex_directors_fixture_<name> on an isolated development server.');
    }
    $host = getenv('BI_TEST_DB_HOST');
    $user = getenv('BI_TEST_DB_USER');
    if (!$host || !$user) { throw new RuntimeException('Explicit BI_TEST_DB_HOST and BI_TEST_DB_USER are required.'); }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli($host, $user, getenv('BI_TEST_DB_PASSWORD') ?: '', '', (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    // The guarded name is code-validated, never an arbitrary identifier.
    $reuse = getenv('BI_FIXTURE_REUSE') === '1';
    if (!$reuse) { $db->query('CREATE DATABASE `' . $database . '`'); }
    $db->select_db($database);
    $db->query("SET SESSION sql_mode = ''");
    $db->query('SET SESSION FOREIGN_KEY_CHECKS = 0');
    if (!$reuse) {
        $schema = getenv('BI_TEST_SCHEMA') ?: dirname(__DIR__, 2) . '/sahamid_structure.sql';
        $source = file_get_contents($schema);
        if ($source === false) { throw new RuntimeException('A structural SQL dump is required; no data dump is loaded.'); }
        preg_match_all('/^CREATE TABLE `[^`]+`\s*\([\s\S]*?^\) ENGINE=[^;]+;/m', $source, $matches);
        directorsFinanceAssert(count($matches[0]) > 30, 'Structural table definitions were not found.');
        foreach ($matches[0] as $tableSql) { $db->query($tableSql); }
        $fixture = file_get_contents(__DIR__ . '/fixtures/directors_finance.sql');
        $db->multi_query($fixture);
        do { if ($result = $db->store_result()) { $result->free(); } } while ($db->more_results() && $db->next_result());
    }

    $definitions = FinanceDatasetCatalog::definitions('fixture-director');
    $facts = array();
    foreach ($definitions as $id => $definition) {
        $facts[$id] = directorsFinanceRows($db, $definition);
        $keys = array_column($facts[$id], 'id');
        directorsFinanceAssert(count($keys) === count(array_unique($keys)), $id . ': fact grain duplicated');
    }
    $ar = array_column($facts['receivables'], null, 'id');
    directorsFinanceAssert(count($ar) === 4, 'AR reversal exclusion or duplicate-document join failed');
    directorsFinanceNear($ar[1]['amount'], 45, 'AR tax/freight/discount/allocation/FX components');
    directorsFinanceAssert($ar[2]['due_status'] === 'unknown_due', 'Ambiguous operational due date must be unknown');
    directorsFinanceAssert($ar[4]['amount'] === null, 'Invalid AR rate must not default to 1');
    directorsFinanceNear(array_sum(array_column($ar, 'amount')), 65, 'Signed current net AR');
    $ap = array_column($facts['payables'], null, 'id');
    directorsFinanceNear(array_sum(array_column($ap, 'amount')), 40, 'Signed current net AP');
    directorsFinanceAssert($ap[1]['due_date'] === '2024-02-15', 'Jan-31 next-month day-15 must be Feb-15');
    directorsFinanceAssert($ap[3]['due_date'] === '2024-02-29', 'Next-month day-31 must clamp to leap-Feb month-end');
    directorsFinanceAssert($ap[4]['amount'] === null, 'Invalid AP rate must not default to 1');
    directorsFinanceAssert(count($facts['cash_movement']) === 3, 'Bank account restriction / duplicate permission rows failed');
    directorsFinanceNear(array_sum(array_column($facts['cash_movement'], 'amount')), -20, 'Bank uses both FX factors');
    directorsFinanceAssert(count(directorsFinanceRows($db, FinanceDatasetCatalog::definitions('unauthorized-user')['cash_movement'])) === 0,
        'Unauthorized bank user can see facts');

    $context = new AuthorizationContext('fixture-director', $database, 'Fixture company', 8, true, array('*'), array(0), null);
    $service = new DirectorsService($db, $context, array(), 'PKR');
    $explore = function($dataset, array $extra = array()) use ($service) {
        return $service->explore(DirectorsRequest::fromArray(array_merge(array(
            'action' => 'explore', 'dataset' => $dataset, 'start' => '2024-01-01', 'end' => '2024-01-31', 'comparison' => 'none',
        ), $extra), 12, '2026-11-05'));
    };
    $profit = $explore('gl_result');
    $revenue = $explore('gl_revenue');
    $cogs = $explore('gl_cost_of_sales');
    $expenses = $explore('gl_expenses');
    directorsFinanceNear($profit['summary']['value'], 450, 'Posted GL net result');
    directorsFinanceNear($profit['summary']['value'], $revenue['summary']['value'] - $cogs['summary']['value'] - $expenses['summary']['value'], 'P&L identity');
    directorsFinanceNear($explore('gl_budget')['summary']['value'], 550, 'Recorded whole-period budget');
    directorsFinanceNear($explore('gl_budget_actual')['summary']['value'], 450, 'Recorded whole-period actual');
    directorsFinanceAssert($explore('gl_budget', array('start' => '2024-01-02'))['summary']['rows'] === 0, 'Partial accounting period must not get full budget');
    $arReport = $explore('receivables');
    directorsFinanceNear($arReport['summary']['value'], 65, 'Current AR ignores selected flow date window');
    directorsFinanceAssert($arReport['summary']['invalid_amounts'] === 1, 'Invalid AR rate not surfaced');
    $bank = $explore('cash_movement');
    directorsFinanceNear($bank['summary']['value'], -20, 'Service bank summary');
    directorsFinanceNear(array_sum(array_column($bank['details']['rows'], 'amount')), -20, 'Bank drill-through reconciles');
    $bankExport = $service->explore(DirectorsRequest::fromArray(array('dataset' => 'cash_movement', 'start' => '2024-01-01', 'end' => '2024-01-31', 'comparison' => 'none'), 12, '2026-11-05'), true);
    directorsFinanceAssert(count($bankExport['details']['rows']) === 3, 'Export authorization differs from summary');
    $account = $revenue['details']['rows'][0]['account'];
    $filtered = $explore('gl_result', array('filters' => array('account' => $account)));
    directorsFinanceNear($filtered['summary']['value'], 1000, 'Drill-down filter scope');
    $denied = new AuthorizationContext('fixture-director', $database, 'Fixture company', 1, false, array('directors_dashboard'), array(2), null);
    $restricted = new DirectorsService($db, $denied, array('PageSecurityArray' => array('AgedDebtors.php' => 2)), 'PKR');
    $forbidden = false;
    try { $restricted->explore(DirectorsRequest::fromArray(array('dataset' => 'gl_result'), 12, '2026-11-05')); }
    catch (\SAHamid\BI\Exception\BIException $exception) { $forbidden = $exception->getErrorCode() === 'forbidden'; }
    directorsFinanceAssert($forbidden, 'Delegated director bypassed financial page token');
    echo "PASS: native MariaDB schema, GL/AR/AP/FX/budget/aging/authorization/drill-through/export fixture checks\n";
    echo 'Fixture database retained for further verification: ' . $database . "\n";
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . "\n");
    exit(1);
}
