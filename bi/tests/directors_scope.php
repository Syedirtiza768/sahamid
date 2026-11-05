<?php
/**
 * Read-only proof that pushing the reporting window into the source queries never changes a result:
 * for every dataset, explore() with window pushdown must equal explore() without it
 * (summary, monthly trend, comparison trend, breakdown, record rows and total).
 * Explicit target: BI_TEST_DB_HOST/PORT/USER/PASSWORD/NAME; optional BI_TEST_USER_ID.
 * BI_SCOPE_PERIODS="2026-04-01:2026-11-05:yoy,2026-09-01:2026-09-30:previous"
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\DatasetCatalog;
use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;


/**
 * Deep comparison. Whole numbers, strings and nulls must be identical. A floating-point amount may differ in
 * its last digits only, because the order rows are summed in can differ (relative 1e-12 and 0.001 absolute).
 */
function scopeCompare($left, $right, $path, array &$differences)
{
    if (is_array($left) && is_array($right)) {
        if (array_keys($left) !== array_keys($right)) { $differences[] = $path . ': different keys/rows'; return; }
        foreach ($left as $key => $value) { scopeCompare($value, $right[$key], $path . '.' . $key, $differences); }
        return;
    }
    if (is_float($left) || is_float($right)) {
        if ($left === null || $right === null) { if ($left !== $right) { $differences[] = $path . ': ' . var_export($left, true) . ' vs ' . var_export($right, true); } return; }
        if (abs($left - $right) > max(0.001, abs($left) * 1e-12)) { $differences[] = $path . ': ' . var_export($left, true) . ' vs ' . var_export($right, true); }
        return;
    }
    if ($left !== $right) { $differences[] = $path . ': ' . var_export($left, true) . ' vs ' . var_export($right, true); }
}

try {
    foreach (array('BI_TEST_DB_HOST', 'BI_TEST_DB_USER', 'BI_TEST_DB_NAME') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', getenv('BI_TEST_DB_NAME'), (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    // BI_TEST_ALLOW_TEMP=1 skips the read-only session so the working-table path (session-local temporary
    // tables, dropped after each call) is exercised; nothing else is written.
    if (getenv('BI_TEST_ALLOW_TEMP') !== '1') { $db->query('SET SESSION TRANSACTION READ ONLY'); }
    $db->query('SET SESSION query_cache_type=OFF');
    $context = new AuthorizationContext(getenv('BI_TEST_USER_ID') ?: 'scope', getenv('BI_TEST_DB_NAME'), 'Explicit verification target', 8, true, array('*'), array(0), null);
    $scoped = new DirectorsService($db, $context, array(), 'PKR');
    $full = new DirectorsService($db, $context, array(), 'PKR');
    $full->setScoping(false);
    $catalog = new DatasetCatalog($context);
    $periods = getenv('BI_SCOPE_PERIODS') ?: '2026-04-01:2026-11-05:yoy,2026-09-01:2026-09-30:previous';
    $failures = 0;
    foreach (explode(',', $periods) as $spec) {
        list($start, $end, $comparison) = explode(':', $spec);
        foreach ($catalog->all() as $id => $definition) {
            $input = array('action' => 'explore', 'dataset' => $id, 'start' => $start, 'end' => $end, 'comparison' => $comparison, 'page_size' => 100);
            $a = $scoped->explore(DirectorsRequest::fromArray($input));
            $b = $full->explore(DirectorsRequest::fromArray($input));
            $problems = array();
            foreach (array('summary', 'trend', 'comparison_trend', 'breakdown', 'details') as $part) {
                $differences = array();
                scopeCompare($a[$part], $b[$part], $part, $differences);
                if ($differences) { $problems[] = $part . ' [' . implode('; ', array_slice($differences, 0, 3)) . ']'; }
            }
            echo (count($problems) ? 'FAIL: ' : 'PASS: ') . "$id [$spec]" . ($problems ? ' differs in ' . implode(', ', $problems) : '') . "\n";
            if ($problems) { $failures++; }
        }
    }
    echo $failures ? "FAILED: $failures dataset/period combinations differ\n" : "Window pushdown returns identical results for every dataset.\n";
    exit($failures ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
