<?php
/**
 * Read-only check of the result cache against a real target: a saved result equals a fresh one, is
 * private to the user, is bypassed by Refresh, ignores corrupt or expired files, and a period change
 * reuses saved balances. Explicit target: BI_TEST_DB_HOST/PORT/USER/PASSWORD/NAME; BI_TEST_USER_ID and
 * BI_TEST_OTHER_USER_ID (two different ERP user ids).
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Directors\ResultCache;
use SAHamid\BI\Security\AuthorizationContext;

$failures = array();
function cacheCheck($condition, $message, array &$failures) { echo ($condition ? 'PASS: ' : 'FAIL: ') . $message . "\n"; if (!$condition) { $failures[] = $message; } }
function stripCache(array $data) { unset($data['metadata']['cache'], $data['metadata']['server_ms']); return $data; }

try {
    foreach (array('BI_TEST_DB_HOST', 'BI_TEST_DB_USER', 'BI_TEST_DB_NAME', 'BI_TEST_USER_ID', 'BI_TEST_OTHER_USER_ID') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', getenv('BI_TEST_DB_NAME'), (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    $db->query('SET SESSION TRANSACTION READ ONLY');
    $db->query('SET SESSION query_cache_type=OFF');
    $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'directors-cache-test-' . getmypid();
    $make = function ($userId, $refresh = false) use ($db, $directory) {
        $service = new DirectorsService($db, new AuthorizationContext($userId, getenv('BI_TEST_DB_NAME'), 'Test', 8, true, array('*'), array(0), null), array(), 'PKR');
        $service->useCache(new ResultCache($directory), $refresh);
        return $service;
    };
    $request = function ($start, $end, $part = '') { return DirectorsRequest::fromArray(array('start' => $start, 'end' => $end, 'comparison' => 'yoy', 'part' => $part)); };
    $user = getenv('BI_TEST_USER_ID');

    $t = microtime(true); $first = $make($user)->overview($request('2026-04-01', '2026-11-05', 'sales')); $firstMs = (microtime(true) - $t) * 1000;
    $t = microtime(true); $second = $make($user)->overview($request('2026-04-01', '2026-11-05', 'sales')); $secondMs = (microtime(true) - $t) * 1000;
    cacheCheck($first['metadata']['cache']['hit'] === false && $second['metadata']['cache']['hit'] === true, 'first call computes, second call is a saved result', $failures);
    cacheCheck(json_encode(stripCache($first)) === json_encode(stripCache($second)), 'the saved result equals the freshly computed one', $failures);
    cacheCheck($secondMs < 150, sprintf('a saved result is served in %.0f ms (first call %.0f ms)', $secondMs, $firstMs), $failures);
    $refreshed = $make($user, true)->overview($request('2026-04-01', '2026-11-05', 'sales'));
    cacheCheck($refreshed['metadata']['cache']['hit'] === false, 'Refresh bypasses the saved result', $failures);
    $other = $make(getenv('BI_TEST_OTHER_USER_ID'))->overview($request('2026-04-01', '2026-11-05', 'sales'));
    cacheCheck($other['metadata']['cache']['hit'] === false, 'another user never receives this user\'s saved result', $failures);
    $differentPeriod = $make($user)->overview($request('2026-09-01', '2026-09-30', 'sales'));
    cacheCheck($differentPeriod['metadata']['cache']['hit'] === false, 'a different period is a different saved result', $failures);

    // balances do not depend on the period: a new period reuses them
    $t = microtime(true); $make($user)->overview($request('2026-07-01', '2026-10-31', 'finance')); $withoutBalances = (microtime(true) - $t) * 1000;
    $t = microtime(true); $make($user)->overview($request('2026-06-01', '2026-09-30', 'finance')); $withBalances = (microtime(true) - $t) * 1000;
    cacheCheck($withBalances < $withoutBalances, sprintf('a new period reuses saved balances (%.0f ms then %.0f ms)', $withoutBalances, $withBalances), $failures);

    $cache = new ResultCache($directory);
    cacheCheck($cache->get(md5('missing'), 300) === null, 'a missing entry is a miss', $failures);
    file_put_contents($directory . DIRECTORY_SEPARATOR . md5('bad') . '.json', '{not json');
    cacheCheck($cache->get(md5('bad'), 300) === null, 'a corrupt file is a miss, not an error', $failures);
    $cache->put(md5('old'), array('x' => 1));
    cacheCheck($cache->get(md5('old'), -1) === null && $cache->get(md5('old'), 300)['data'] === array('x' => 1), 'an entry past its lifetime is ignored', $failures);
    $unwritable = new ResultCache('/proc/not-writable/directors');
    $unwritable->put(md5('x'), array(1));
    cacheCheck($unwritable->get(md5('x'), 300) === null, 'an unwritable cache location silently means no cache', $failures);
    foreach ((array) glob($directory . DIRECTORY_SEPARATOR . '*') as $file) { @unlink($file); }
    @rmdir($directory);
    echo $failures ? 'FAILED: ' . count($failures) . " check(s)\n" : "The result cache behaves.\n";
    exit($failures ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
