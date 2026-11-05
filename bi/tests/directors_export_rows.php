<?php
/**
 * Read-only check of the export row builders against real service output:
 * the records export carries a correct total row, and the overview export carries every
 * measure plus the attention and data-health items. Explicit target: BI_TEST_DB_HOST/PORT/USER/PASSWORD/NAME.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\DirectorsRequest;
use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Directors\ExportRows;
use SAHamid\BI\Security\AuthorizationContext;

$failures = array();
function exportCheck($condition, $message, array &$failures) { echo ($condition ? 'PASS: ' : 'FAIL: ') . $message . "\n"; if (!$condition) { $failures[] = $message; } }

try {
    foreach (array('BI_TEST_DB_HOST', 'BI_TEST_DB_USER', 'BI_TEST_DB_NAME') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(getenv('BI_TEST_DB_HOST'), getenv('BI_TEST_DB_USER'), getenv('BI_TEST_DB_PASSWORD') ?: '', getenv('BI_TEST_DB_NAME'), (int) (getenv('BI_TEST_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    $db->query('SET SESSION TRANSACTION READ ONLY');
    $context = new AuthorizationContext(getenv('BI_TEST_USER_ID') ?: 'export', getenv('BI_TEST_DB_NAME'), 'Explicit verification target', 8, true, array('*'), array(0), null);
    $service = new DirectorsService($db, $context, array(), 'PKR');
    $start = getenv('BI_TEST_START') ?: '2026-09-01';
    $end = getenv('BI_TEST_END') ?: '2026-09-30';

    foreach (array('posted_invoices', 'shop_sales', 'petty_cash') as $dataset) {
        $data = $service->explore(DirectorsRequest::fromArray(array('action' => 'export', 'dataset' => $dataset, 'start' => $start, 'end' => $end, 'comparison' => 'none')), true);
        $rows = ExportRows::records($data);
        $total = array_pop($rows);
        $sum = array_sum(array_column($rows, 'amount'));
        exportCheck(strpos($total['reference'], 'TOTAL (' . count($rows) . ' records)') === 0, "$dataset: total row states the record count (" . count($rows) . ')', $failures);
        exportCheck(is_numeric($total['amount']) && abs($total['amount'] - $sum) < 0.01, "$dataset: total row equals the sum of the exported rows ($sum)", $failures);
        exportCheck(is_numeric($total['amount']) && abs($total['amount'] - $data['summary']['value']) < 0.01 && count($rows) === $data['summary']['rows'], "$dataset: total row and count equal the on-screen summary", $failures);
    }
    $overview = $service->overview(DirectorsRequest::fromArray(array('start' => '2026-04-01', 'end' => '2026-11-05', 'comparison' => 'yoy')));
    $rows = ExportRows::summary($overview);
    exportCheck(count($rows) === count($overview['metrics']) + count($overview['attention']) + count($overview['data_health']), 'overview export has every measure, attention item and data-health item (' . count($rows) . ' rows)', $failures);
    $sections = array_count_values(array_column($rows, 'section'));
    exportCheck(!empty($sections['Headline']) && count($overview['attention']) === (isset($sections['Needs attention']) ? $sections['Needs attention'] : 0), 'overview export labels the headline measures and the attention items', $failures);
    echo $failures ? 'FAILED: ' . count($failures) . " check(s)\n" : "Export rows are consistent with the screen.\n";
    exit($failures ? 1 : 0);
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
