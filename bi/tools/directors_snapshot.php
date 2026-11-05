<?php
/**
 * Daily capture of the Directors' dashboard balance measures (receivables, payables,
 * outstanding invoices, stock value, pending DC value). CLI only, idempotent per day.
 *
 * Needs an explicit target, like the tests: BI_DB_HOST / BI_DB_USER / BI_DB_PASSWORD / BI_DB_NAME
 * (optional BI_DB_PORT). It does not read config.php. Apply
 * migrations/directors_balance_snapshots.sql first. Schedule once a day after midnight, e.g.
 *   5 0 * * *  BI_DB_HOST=... BI_DB_USER=... BI_DB_PASSWORD=... BI_DB_NAME=... php bi/tools/directors_snapshot.php
 *
 * The capture is company-wide. Anything that reads the history must still check the reader's
 * access to the source measure (DirectorsAuthorization::canRead) before showing it.
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/bootstrap.php';

use SAHamid\BI\Directors\DirectorsService;
use SAHamid\BI\Security\AuthorizationContext;

try {
    foreach (array('BI_DB_HOST', 'BI_DB_USER', 'BI_DB_NAME') as $variable) {
        if (!getenv($variable)) { throw new RuntimeException('Explicit ' . $variable . ' is required.'); }
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(getenv('BI_DB_HOST'), getenv('BI_DB_USER'), getenv('BI_DB_PASSWORD') ?: '', getenv('BI_DB_NAME'), (int) (getenv('BI_DB_PORT') ?: 3306));
    $db->set_charset('utf8mb4');
    date_default_timezone_set('Asia/Karachi');
    $context = new AuthorizationContext('snapshot-job', getenv('BI_DB_NAME'), 'Snapshot job', 8, true, array('*'), array(0), null);
    $service = new DirectorsService($db, $context, array(), 'PKR');
    $date = date('Y-m-d');
    $statement = $db->prepare('INSERT INTO directors_balance_snapshots (snapshot_date, measure, value, overdue, row_count, invalid_amounts, captured_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE value = VALUES(value), overdue = VALUES(overdue), row_count = VALUES(row_count),
        invalid_amounts = VALUES(invalid_amounts), captured_at = VALUES(captured_at)');
    $written = 0;
    foreach ($service->snapshotSummaries(array('receivables', 'payables', 'outstanding_invoices', 'inventory', 'pending_deliveries')) as $measure => $summary) {
        $value = $summary['value'];
        $overdue = $summary['overdue'] > 0 ? $summary['overdue'] : null;
        $rows = $summary['rows'];
        $invalid = $summary['invalid_amounts'];
        $statement->bind_param('ssddii', $date, $measure, $value, $overdue, $rows, $invalid);
        $statement->execute();
        $written++;
        echo "Captured $measure for $date: " . ($value === null ? 'unavailable' : number_format($value, 2)) . " ($rows rows, $invalid excluded)\n";
    }
    echo "Done: $written measures for $date.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    exit(1);
}
