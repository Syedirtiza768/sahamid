<?php
/**
 * Explicit-target, read-only reporting smoke/reconciliation tool.
 * Does not read config.php, change data, grant permissions or apply migrations.
 * Set BI_TEST_DB_HOST/PORT/USER/PASSWORD/NAME and BI_TEST_USER_ID explicitly.
 * Optional BI_TEST_START/END (default last complete calendar month).
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\DatasetCatalog;
use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;

function directorsIntegrationCheck($condition, $message)
{
    if (!$condition) { throw new RuntimeException($message); }
}
function directorsIntegrationRows($db, $sql, array $params, $types)
{
    $statement = $db->prepare($sql);
    if ($types !== '') {
        $bind = array($types);
        foreach ($params as $index => $value) { $bind[] = &$params[$index]; }
        call_user_func_array(array($statement, 'bind_param'), $bind);
    }
    $statement->execute();
    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

try {
    foreach (array('BI_TEST_DB_HOST', 'BI_TEST_DB_USER', 'BI_TEST_DB_NAME', 'BI_TEST_USER_ID') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $database = getenv('BI_TEST_DB_NAME');
    $userId = getenv('BI_TEST_USER_ID');
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', $database,
        (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    $db->query('SET SESSION TRANSACTION READ ONLY');
    $context = new AuthorizationContext($userId, $database, 'Explicit verification target', 8, true, array('*'), array(0), null);
    $catalog = new DatasetCatalog($context);
    $service = new DirectorsService($db, $context, array(), getenv('BI_TEST_CURRENCY') ?: 'PKR');
    $start = getenv('BI_TEST_START') ?: date('Y-m-01', strtotime('first day of previous month'));
    $end = getenv('BI_TEST_END') ?: date('Y-m-t', strtotime('first day of previous month'));
    $base = array('start' => $start, 'end' => $end, 'comparison' => 'previous', 'page_size' => 100);
    $errors = array();
    $started = microtime(true);
    foreach ($catalog->all() as $id => $definition) {
        try {
            $request = DirectorsRequest::fromArray($base + array('action' => 'explore', 'dataset' => $id));
            $result = $service->explore($request);
            $plan = directorsIntegrationRows($db, 'EXPLAIN SELECT * FROM (' . $definition['sql'] . ') source_facts LIMIT 1', $definition['params'], $definition['types']);
            directorsIntegrationCheck(count($plan) > 0, 'No query plan');
            $summary = $result['summary'];
            directorsIntegrationCheck($summary['rows'] === $result['details']['total'], 'Summary/detail count differs');
            if ($summary['rows'] <= 100 && $summary['value'] !== null) {
                $total = array_sum(array_column($result['details']['rows'], 'amount'));
                directorsIntegrationCheck(abs($total - $summary['value']) < max(0.0001, abs($total) * 0.00000001), 'Detail values do not reconcile');
            }
            if ($summary['value'] !== null && $result['breakdown']) {
                $total = array_sum(array_column($result['breakdown'], 'value'));
                directorsIntegrationCheck(abs($total - $summary['value']) < max(0.0001, abs($total) * 0.00000001), 'Breakdown values do not reconcile');
            }
            if ($definition['kind'] === 'snapshot') {
                directorsIntegrationCheck($result['summary']['previous'] === null && !$result['trend'], 'Snapshot claimed historical comparison');
            }
            // Exercise bounded export and assert it uses the same authorized facts.
            if ($summary['rows'] <= 5000) {
                $export = $service->explore($request, true);
                directorsIntegrationCheck(count($export['details']['rows']) === $summary['rows'], 'Export count differs');
            }
            echo 'PASS: ' . $id . ' (' . $summary['rows'] . ' filtered facts, ' . $summary['invalid_amounts'] . " excluded amounts)\n";
        } catch (Throwable $exception) {
            $errors[] = $id . ': ' . $exception->getMessage();
            fwrite(STDERR, 'FAIL: ' . end($errors) . "\n");
        }
    }
    $overview = $service->overview(DirectorsRequest::fromArray($base));
    $composites = count(array_filter($overview['metrics'], function ($metric) { return isset($metric['components']); }));
    directorsIntegrationCheck(count($overview['metrics']) === count($catalog->all()) + $composites, 'Overview lost source coverage');
    echo 'Checked ' . count($catalog->all()) . ' datasets in ' . (int) round((microtime(true) - $started) * 1000) . " ms.\n";
    echo "This verifies the explicit target only; it does not establish historical completeness or production-volume performance.\n";
    exit($errors ? 1 : 0);
} catch (Throwable $exception) {
    fwrite(STDERR, 'FAIL: ' . $exception->getMessage() . "\n");
    exit(1);
}
