<?php

// Run with mysqli enabled and CROSSSECTION_TEST_DB_* pointing to a test database.
// All fixtures and caches are connection-local TEMPORARY tables; source data is never changed.
mysqli_report(MYSQLI_REPORT_OFF);
$db = mysqli_connect(
    getenv('CROSSSECTION_TEST_DB_HOST') ?: '127.0.0.1',
    getenv('CROSSSECTION_TEST_DB_USER') ?: 'root',
    getenv('CROSSSECTION_TEST_DB_PASSWORD') ?: '',
    getenv('CROSSSECTION_TEST_DB_NAME') ?: 'test',
    (int) (getenv('CROSSSECTION_TEST_DB_PORT') ?: 3319)
);
if (!$db) {
    throw new RuntimeException('Cannot connect to the cross-sectional report test database.');
}
mysqli_set_charset($db, 'utf8mb4');

function run_sql($sql, $conn) {
    $result = mysqli_query($conn, $sql);
    if ($result === false) {
        throw new RuntimeException(mysqli_error($conn));
    }
    return $result;
}

function DB_fetch_array($result) {
    return mysqli_fetch_assoc($result);
}

function cs_assert($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$stockCollation = in_array('--case-sensitive', $argv, true) ? 'utf8_bin' : 'utf8_general_ci';
run_sql("CREATE TEMPORARY TABLE stockmaster (
    stockid VARCHAR(40) CHARACTER SET utf8 COLLATE $stockCollation PRIMARY KEY,
    brand INT, mbflag CHAR(1), mnfCode VARCHAR(50), mnfpno VARCHAR(50), description VARCHAR(255)
) DEFAULT CHARSET=utf8mb4", $db);
run_sql("CREATE TEMPORARY TABLE manufacturers (manufacturers_id INT PRIMARY KEY, manufacturers_name VARCHAR(100))", $db);
run_sql("INSERT INTO manufacturers VALUES (987, 'Katko')", $db);
run_sql("CREATE TEMPORARY TABLE stockmoves (
    stkmoveno INT PRIMARY KEY,
    stockid VARCHAR(40) CHARACTER SET utf8 COLLATE $stockCollation NOT NULL,
    loccode VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
    trandate DATE, type INT, newqoh DECIMAL(20,4)
)", $db);
run_sql("CREATE TEMPORARY TABLE systypes (typeid INT PRIMARY KEY, typename VARCHAR(50))", $db);
run_sql("INSERT INTO systypes VALUES (25, 'Receipt')", $db);

$plainId = '9871LBS6419410203514';
$hiddenId = $stockCollation === 'utf8_bin' ? strtolower($plainId) : $plainId . "\u{200B}";
$plainEsc = mysqli_real_escape_string($db, $plainId);
$hiddenEsc = mysqli_real_escape_string($db, $hiddenId);
run_sql("INSERT INTO stockmaster VALUES
    ('$plainEsc', 987, 'B', 'A', 'A', 'Switch 📦'),
    ('$hiddenEsc', 987, 'M', 'B', 'B', 'Second switch')", $db);

// Prove these source-valid IDs reproduce the original PRIMARY-key failure.
run_sql("CREATE TEMPORARY TABLE old_unicode_keys (
    stockid VARCHAR(50) NOT NULL PRIMARY KEY
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);
$oldInsert = mysqli_query($db, "INSERT INTO old_unicode_keys SELECT stockid FROM stockmaster");
cs_assert($oldInsert === false && mysqli_errno($db) === 1062, 'Fixture must reproduce the duplicate-key error.');
run_sql('DROP TEMPORARY TABLE old_unicode_keys', $db);

run_sql("INSERT INTO stockmoves VALUES
    (1, '$plainEsc', 'HO', '2025-12-31', 25, 3),
    (2, '$plainEsc', 'ho', '2025-12-31', 25, 4),
    (3, '$hiddenEsc', 'HO', '2025-12-31', 25, 13),
    (4, '$plainEsc', 'HO', '2026-09-30', 25, 5),
    (5, '$plainEsc', 'ho', '2026-09-30', 25, 6),
    (6, '$hiddenEsc', 'HO', '2026-09-30', 25, 17)", $db);

// Simulate a stale cache created by the previous comparison rules.
run_sql("CREATE TEMPORARY TABLE crosssection_qoh_snapshot_cache (
    snapshot_date DATE, stockid VARCHAR(50), qoh DECIMAL(20,4), computed_at DATETIME,
    PRIMARY KEY (snapshot_date, stockid)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);
run_sql("INSERT INTO crosssection_qoh_snapshot_cache VALUES ('2026-01-01', '$plainEsc', 9999, NOW())", $db);

// Execute the actual report preparation, snapshot, and cache code against the fixtures.
$from = '2026-01-01';
$to = '2026-11-03';
$forceQohCacheRefresh = false;
$reportDir = realpath(__DIR__ . '/../v2/crosssection2');
$source = file_get_contents($reportDir . '/index.php');
$start = strpos($source, "    require_once __DIR__ . '/key_schema.php';");
$end = strpos($source, '    $parchinoData = [];');
cs_assert($start !== false && $end !== false, 'Report preparation block must exist.');
$preparation = substr($source, $start, $end - $start);
$preparation = str_replace('__DIR__', var_export($reportDir, true), $preparation);
$preparation = str_replace('CREATE TABLE IF NOT EXISTS', 'CREATE TEMPORARY TABLE IF NOT EXISTS', $preparation);
eval($preparation);

function cs_check_report($db, $plainId, $hiddenId) {
    $rows = [];
    $result = run_sql('SELECT stockid, description, qohA, qohB FROM tmp_report', $db);
    while ($row = DB_fetch_array($result)) {
        $rows[$row['stockid']] = $row;
    }
    cs_assert(count($rows) === 2 && isset($rows[$plainId], $rows[$hiddenId]), 'Both exact source IDs must survive.');
    cs_assert((float) $rows[$plainId]['qohA'] === 7.0 && (float) $rows[$plainId]['qohB'] === 11.0, 'Location keys must retain source comparison rules.');
    cs_assert((float) $rows[$hiddenId]['qohA'] === 13.0 && (float) $rows[$hiddenId]['qohB'] === 17.0, 'Stock quantities must stay separate.');
    cs_assert($rows[$plainId]['description'] === 'Switch 📦', 'Descriptions must retain utf8mb4 support.');
}

cs_check_report($db, $plainId, $hiddenId);
cs_assert($qohSnapshotCacheStats === ['opening' => 'computed', 'closing' => 'computed'], 'Old cache must be bypassed.');
$cachedOpen = $load_qoh_snapshot_from_cache($from, $reportStockIds);
$cachedClose = $load_qoh_snapshot_from_cache($to, $reportStockIds);
cs_assert(count($cachedOpen) === 2 && count($cachedClose) === 2, 'Cache must retain both IDs.');
run_sql('UPDATE tmp_report SET qohA = 0, qohB = 0', $db);
$apply_qoh_map_to_tmp_report_column('qohA', $cachedOpen);
$apply_qoh_map_to_tmp_report_column('qohB', $cachedClose);
cs_check_report($db, $plainId, $hiddenId);
$snapshot = $qoh_at_date('2026-06-01', $reportStockIds);
cs_assert($snapshot[$plainId] === 7.0 && $snapshot[$hiddenId] === 13.0, 'Timeline snapshots must preserve both stock and location keys.');
cs_assert($qoh_at_date('2026-06-01', $reportStockIds) === $snapshot, 'Cached timeline snapshots must preserve exact IDs.');
mysqli_close($db);
echo "PASS: duplicate reproduced; report, opening/closing stock, cache reloads, timeline snapshots, and Unicode text preserved.\n";
