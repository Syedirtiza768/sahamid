<?php
/**
 * Read-only: the three independent overview slices (finance, sales, operations), merged, must equal
 * the whole overview: every measure identical, the same attention items (largest first, top 8), the same
 * data-health items and budget comparison. Explicit target: BI_TEST_DB_HOST/PORT/USER/PASSWORD/NAME.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;

$failures = array();
function partsCheck($condition, $message, array &$failures) { echo ($condition ? 'PASS: ' : 'FAIL: ') . $message . "\n"; if (!$condition) { $failures[] = $message; } }

try {
    foreach (array('BI_TEST_DB_HOST', 'BI_TEST_DB_USER', 'BI_TEST_DB_NAME') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', getenv('BI_TEST_DB_NAME'), (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    $db->query('SET SESSION TRANSACTION READ ONLY');
    $context = new AuthorizationContext(getenv('BI_TEST_USER_ID') ?: 'parts', getenv('BI_TEST_DB_NAME'), 'Explicit verification target', 8, true, array('*'), array(0), null);
    $service = new DirectorsService($db, $context, array(), 'PKR');
    foreach (array('2026-04-01:2026-11-05:yoy', '2026-09-01:2026-09-30:previous') as $spec) {
        list($start, $end, $comparison) = explode(':', $spec);
        $request = function ($part) use ($start, $end, $comparison) { return DirectorsRequest::fromArray(array('start' => $start, 'end' => $end, 'comparison' => $comparison, 'part' => $part)); };
        $whole = $service->overview($request(''));
        $merged = array('metrics' => array(), 'attention' => array(), 'data_health' => array(), 'budget' => null);
        $counts = array();
        foreach (array('finance', 'sales', 'operations') as $part) {
            $data = $service->overview($request($part));
            $counts[$part] = count($data['metrics']);
            foreach ($data['metrics'] as $metric) { $merged['metrics'][$metric['id']][] = $metric; }
            $merged['attention'] = array_merge($merged['attention'], $data['attention']);
            $merged['data_health'] = array_merge($merged['data_health'], $data['data_health']);
            if ($data['budget_comparison'] !== null) { $merged['budget'] = $data['budget_comparison']; }
        }
        $duplicated = array_filter($merged['metrics'], function ($list) { return count($list) > 1; });
        partsCheck(count($duplicated) === 0, "[$spec] no measure appears in two slices (" . implode('/', $counts) . ' measures per slice)', $failures);
        $wholeById = array_column($whole['metrics'], null, 'id');
        $same = count($wholeById) === count($merged['metrics']);
        foreach ($wholeById as $id => $metric) {
            $other = isset($merged['metrics'][$id]) ? $merged['metrics'][$id][0] : null;
            if ($other === null || json_encode($other) !== json_encode($metric)) { $same = false; echo "  differs: $id\n"; }
        }
        partsCheck($same, "[$spec] every measure equals the whole overview (" . count($wholeById) . ' measures)', $failures);
        usort($merged['attention'], function ($a, $b) { return $b['amount'] <=> $a['amount']; });
        partsCheck(json_encode(array_slice($merged['attention'], 0, 8)) === json_encode($whole['attention']), "[$spec] merged attention list equals the whole overview's", $failures);
        $titles = function (array $items) { $t = array_column($items, 'title'); sort($t); return $t; };
        partsCheck($titles($merged['data_health']) === $titles($whole['data_health']), "[$spec] data-health items are the same", $failures);
        partsCheck(json_encode($merged['budget']) === json_encode($whole['budget_comparison']), "[$spec] budget comparison is the same", $failures);
    }
    echo $failures ? 'FAILED: ' . count($failures) . " check(s)\n" : "The slices add up to the whole overview.\n";
    exit($failures ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
