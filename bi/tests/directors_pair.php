<?php
/**
 * Read-only equivalence check: the overview's single-pass summaryPair() must equal two
 * summary() calls plus the undated-row scan, for every dataset.
 * Explicit target: BI_TEST_DB_HOST/PORT/USER/PASSWORD/NAME. Session is READ ONLY.
 * BI_PAIR_PERIODS="2026-04-01:2026-11-05:yoy,2026-09-01:2026-09-30:previous"
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\DatasetCatalog;
use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;

try {
    foreach (array('BI_TEST_DB_HOST', 'BI_TEST_DB_USER', 'BI_TEST_DB_NAME') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', getenv('BI_TEST_DB_NAME'), (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    $db->query('SET SESSION TRANSACTION READ ONLY');
    $context = new AuthorizationContext(getenv('BI_TEST_USER_ID') ?: 'pair', getenv('BI_TEST_DB_NAME'), 'Explicit verification target', 8, true, array('*'), array(0), null);
    $service = new DirectorsService($db, $context, array(), 'PKR');
    $class = new ReflectionClass($service);
    $summary = $class->getMethod('summary'); $summary->setAccessible(true);
    $pair = $class->getMethod('summaryPair'); $pair->setAccessible(true);
    $resolve = $class->getMethod('resolveDefinition'); $resolve->setAccessible(true);
    $catalog = new DatasetCatalog($context);
    $failures = 0;
    $periods = getenv('BI_PAIR_PERIODS') ?: date('Y-m-01', strtotime('first day of previous month')) . ':' . date('Y-m-t', strtotime('first day of previous month')) . ':previous';
    foreach (explode(',', $periods) as $spec) {
        list($start, $end, $comparison) = explode(':', $spec);
        $request = DirectorsRequest::fromArray(array('start' => $start, 'end' => $end, 'comparison' => $comparison));
        foreach ($catalog->all() as $id => $definition) {
            $definition = $resolve->invoke($service, $definition);
            $withPrevious = $definition['kind'] !== 'snapshot' && $request->period['comparison_start'] !== null;
            $old = array('current' => $summary->invoke($service, $definition, $request, false), 'previous' => $withPrevious ? $summary->invoke($service, $definition, $request, true) : null);
            $new = $pair->invoke($service, $definition, $request, $withPrevious);
            $undated = $definition['kind'] === 'flow'
                ? (int) $db->query("SELECT COUNT(*) AS n FROM (" . str_replace('?', '0', $definition['sql']) . ") q WHERE q.date IS NULL OR q.date < '1900-01-01'")->fetch_assoc()['n'] : $new['undated'];
            $same = true;
            foreach (array('current', 'previous') as $side) {
                if ($old[$side] === null || $new[$side] === null) { $same = $same && $old[$side] === $new[$side]; continue; }
                foreach ($old[$side] as $key => $value) {
                    $other = $new[$side][$key];
                    $equal = is_float($value) || is_float($other) ? (abs((float) $value - (float) $other) <= max(0.000001, abs((float) $value) * 1e-9)) && ($value === null) === ($other === null) : $value === $other;
                    if (!$equal) { $same = false; echo "  $id $side.$key old=" . var_export($value, true) . ' new=' . var_export($other, true) . "\n"; }
                }
            }
            if ($definition['kind'] === 'flow' && $undated !== $new['undated']) { $same = false; echo "  $id undated old=$undated new=" . $new['undated'] . "\n"; }
            echo ($same ? 'PASS: ' : 'FAIL: ') . "$id [$spec]\n";
            if (!$same) { $failures++; }
        }
    }
    echo $failures ? "FAILED: $failures dataset/period combinations differ\n" : "summaryPair() equals summary() on every dataset.\n";
    exit($failures ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
