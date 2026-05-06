<?php
$active = "reports";
$AllowAnyone = true;

// Change to v2/ directory so config.php's relative includes resolve correctly
chdir(__DIR__ . '/..');
include_once("config.php");

ini_set('memory_limit', '1024M');
set_time_limit(600);

if (!userHasPermission($db, "top_items_quotation_report")) {
    header("Location: /sahamid");
    return;
}

if (isset($_POST['to'])) {
    set_time_limit(600);
    ob_clean();
    header('Content-Type: application/json');

    // Connection defaults to utf8; DB default for TEMPORARY tables is often latin1 — then UTF-8 in
    // stockmaster.description cannot be stored. Use utf8mb4 for this request and for temp tables.
    mysqli_set_charset($db, 'utf8mb4');

    $fromInput = trim($_POST['from'] ?? '');
    $toInput = trim($_POST['to'] ?? '');
    $fromDate = DateTime::createFromFormat('Y-m-d', $fromInput);
    $toDate = DateTime::createFromFormat('Y-m-d', $toInput);
    $fromValid = $fromDate && $fromDate->format('Y-m-d') === $fromInput;
    $toValid = $toDate && $toDate->format('Y-m-d') === $toInput;
    if (!$fromValid || !$toValid) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format. Expected YYYY-MM-DD.']);
        exit;
    }
    if ($fromDate > $toDate) {
        http_response_code(400);
        echo json_encode(['error' => 'From date must be on or before To date.']);
        exit;
    }
    $from = mysqli_real_escape_string($db, $fromInput);
    $to = mysqli_real_escape_string($db, $toInput);

    $forceQohCacheRefresh = !empty($_POST['forceQohCacheRefresh']);

    $action = isset($_POST['action']) ? trim((string) $_POST['action']) : 'search';
    if (!in_array($action, ['search', 'valuationBreakdown', 'valuationDetailTimeline'], true)) {
        $action = 'search';
    }

    // Helper: run query without DB_query (which outputs HTML on error)
    function run_sql($sql, $conn) {
        $r = mysqli_query($conn, $sql);
        if (!$r) {
            ob_clean();
            echo json_encode(['error' => mysqli_error($conn), 'sql' => substr($sql, 0, 200)]);
            exit;
        }
        return $r;
    }

    run_sql("CREATE TABLE IF NOT EXISTS crosssection_qoh_snapshot_cache (
        snapshot_date DATE NOT NULL,
        stockid VARCHAR(50) NOT NULL,
        qoh DECIMAL(20,4) NOT NULL,
        computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (snapshot_date, stockid),
        KEY idx_cs_qoh_snap_date (snapshot_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);

    /** @return array<string,float>|null Full map for all $stockIds, or null if cache incomplete */
    $load_qoh_snapshot_from_cache = function (string $dateSqlEscaped, array $stockIds) use ($db, $run_sql) {
        if ($stockIds === []) {
            return [];
        }
        $map = [];
        foreach (array_chunk($stockIds, 500) as $chunk) {
            $parts = [];
            foreach ($chunk as $sid) {
                $parts[] = "'" . mysqli_real_escape_string($db, $sid) . "'";
            }
            $inSql = implode(',', $parts);
            $res = run_sql(
                "SELECT stockid, qoh FROM crosssection_qoh_snapshot_cache
                  WHERE snapshot_date = '$dateSqlEscaped' AND stockid IN ($inSql)",
                $db
            );
            while ($row = DB_fetch_array($res)) {
                $map[$row['stockid']] = (float) $row['qoh'];
            }
        }
        foreach ($stockIds as $sid) {
            if (!array_key_exists($sid, $map)) {
                return null;
            }
        }

        return $map;
    };

    /** @param array<string,float> $qohByStockid */
    $save_qoh_snapshot_to_cache = function (string $dateSqlEscaped, array $qohByStockid) use ($db, $run_sql) {
        if ($qohByStockid === []) {
            return;
        }
        foreach (array_chunk($qohByStockid, 250, true) as $chunk) {
            $vals = [];
            foreach ($chunk as $sid => $q) {
                $sidE = mysqli_real_escape_string($db, (string) $sid);
                $vals[] = "('$dateSqlEscaped', '$sidE', " . (float) $q . ", NOW())";
            }
            run_sql(
                "INSERT INTO crosssection_qoh_snapshot_cache (snapshot_date, stockid, qoh, computed_at)
                 VALUES " . implode(',', $vals) . "
                 ON DUPLICATE KEY UPDATE qoh = VALUES(qoh), computed_at = NOW()",
                $db
            );
        }
    };

    /** Apply per-stock QOH to tmp_report.qohA or .qohB in one join update. */
    $apply_qoh_map_to_tmp_report_column = function (string $column, array $qohByStockid) use ($db, $run_sql) {
        if (!in_array($column, ['qohA', 'qohB'], true) || $qohByStockid === []) {
            return;
        }
        run_sql("CREATE TEMPORARY TABLE tmp_qoh_apply (
            stockid VARCHAR(50) NOT NULL PRIMARY KEY,
            qoh DECIMAL(20,4) NOT NULL
        ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);
        foreach (array_chunk($qohByStockid, 400, true) as $chunk) {
            $vals = [];
            foreach ($chunk as $sid => $q) {
                $vals[] = "('" . mysqli_real_escape_string($db, (string) $sid) . "'," . (float) $q . ")";
            }
            run_sql('INSERT INTO tmp_qoh_apply (stockid, qoh) VALUES ' . implode(',', $vals), $db);
        }
        run_sql("UPDATE tmp_report r INNER JOIN tmp_qoh_apply t ON r.stockid = t.stockid SET r.$column = t.qoh", $db);
        run_sql('DROP TEMPORARY TABLE tmp_qoh_apply', $db);
    };

    /**
     * QOH per stockid at end of a calendar date (same rules as opening/closing moves).
     * With $stockIdsForFullSnapshot, reads/writes crosssection_qoh_snapshot_cache so any report or timeline
     * can reuse the same date without rescanning stockmoves.
     */
    $qoh_at_date = function (string $dateSqlEscaped, array $stockIdsForFullSnapshot = null) use (
        $db,
        $run_sql,
        $forceQohCacheRefresh,
        $load_qoh_snapshot_from_cache,
        $save_qoh_snapshot_to_cache
    ) {
        if (!$forceQohCacheRefresh
            && $stockIdsForFullSnapshot !== null
            && $stockIdsForFullSnapshot !== []) {
            $cached = $load_qoh_snapshot_from_cache($dateSqlEscaped, $stockIdsForFullSnapshot);
            if ($cached !== null) {
                return $cached;
            }
        }

        run_sql("CREATE TEMPORARY TABLE tmp_snap_moves (
            stockid VARCHAR(50) NOT NULL,
            loccode VARCHAR(10) NOT NULL,
            stkmoveno INT NOT NULL,
            PRIMARY KEY (stockid, loccode)
        ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);

        run_sql("INSERT INTO tmp_snap_moves
              SELECT stockid, loccode, MAX(stkmoveno)
              FROM stockmoves sm
              LEFT JOIN systypes st ON sm.type = st.typeid
              WHERE trandate <= '$dateSqlEscaped'
                AND NOT (
                    LOWER(COALESCE(st.typename, '')) = 'dc'
                    OR LOWER(COALESCE(st.typename, '')) LIKE '%shop sale%'
                )
              GROUP BY stockid, loccode", $db);

        $map = [];
        $res = run_sql("SELECT sm.stockid, SUM(sm.newqoh) AS qoh
                  FROM stockmoves sm
                  INNER JOIN tmp_snap_moves m
                      ON sm.stockid = m.stockid
                     AND sm.loccode = m.loccode
                     AND sm.stkmoveno = m.stkmoveno
                  GROUP BY sm.stockid", $db);
        while ($row = DB_fetch_array($res)) {
            $map[$row['stockid']] = (float) $row['qoh'];
        }
        run_sql("DROP TEMPORARY TABLE tmp_snap_moves", $db);

        if ($stockIdsForFullSnapshot !== null && $stockIdsForFullSnapshot !== []) {
            $out = [];
            foreach ($stockIdsForFullSnapshot as $sid) {
                $out[$sid] = $map[$sid] ?? 0.0;
            }
            if (!$forceQohCacheRefresh) {
                $save_qoh_snapshot_to_cache($dateSqlEscaped, $out);
            }

            return $out;
        }

        return $map;
    };

    // Aligned with itemsReport/index.php pricing method
    function calculatePriceForStock($parchinos, $requested_qty)
    {
        if ($requested_qty <= 0 || empty($parchinos)) {
            return [
                'total_bpitems_price' => 0,
                'weighted_unit_price' => 0,
                'total_quantity' => 0
            ];
        }

        $remaining_qty = $requested_qty;
        $total_allocated_qty = 0;
        $total_weighted_price = 0;

        foreach ($parchinos as $parchino) {
            if ($remaining_qty <= 0) break;

            $available_qty = (float)$parchino['quantity'];

            // Use adjust_unit_price if available and > 0, otherwise use original price
            $unit_price = (float)($parchino['adjust_unit_price'] ?? 0);
            if ($unit_price <= 0) {
                $unit_price = (float)$parchino['price'];
            }

            // Apply landing factor exactly like itemsReport
            $landing_factor = (float)($parchino['landing_factor'] ?? 1);
            if ($landing_factor <= 0) {
                $landing_factor = 1;
            }
            $effective_price = $unit_price * $landing_factor;

            $allocated_qty = min($available_qty, $remaining_qty);
            if ($allocated_qty > 0) {
                $allocated_price = $allocated_qty * $effective_price;
                $total_weighted_price += $allocated_price;
                $total_allocated_qty += $allocated_qty;
                $remaining_qty -= $allocated_qty;
            }
        }

        $weighted_unit_price = $total_allocated_qty > 0
            ? $total_weighted_price / $total_allocated_qty
            : 0;

        return [
            'total_bpitems_price' => round($total_weighted_price, 2),
            'weighted_unit_price' => round($weighted_unit_price, 2),
            'total_quantity' => round($total_allocated_qty, 2)
        ];
    }

    /** Snapshot dates: From, each calendar month-start strictly after From until <= To, and To (unique, sorted). */
    function crosssection_valuation_timeline_dates(DateTime $from, DateTime $to): array
    {
        $keys = [];
        $keys[$from->format('Y-m-d')] = true;
        $keys[$to->format('Y-m-d')] = true;
        $cursor = clone $from;
        $cursor->modify('first day of next month');
        while ($cursor <= $to) {
            $keys[$cursor->format('Y-m-d')] = true;
            $cursor->modify('+1 month');
        }
        $dates = array_keys($keys);
        sort($dates);
        return $dates;
    }

    /** One row per calendar day from $from through $to (inclusive). */
    function crosssection_valuation_timeline_dates_daily(DateTime $from, DateTime $to): array
    {
        $dates = [];
        $cur = clone $from;
        $cur->setTime(0, 0, 0);
        $end = clone $to;
        $end->setTime(0, 0, 0);
        while ($cur <= $end) {
            $dates[] = $cur->format('Y-m-d');
            $cur->modify('+1 day');
        }

        return $dates;
    }

    /** Weekly samples: start date, then +7 days, always including $to. */
    function crosssection_valuation_timeline_dates_weekly(DateTime $from, DateTime $to): array
    {
        $keys = [];
        $keys[$from->format('Y-m-d')] = true;
        $keys[$to->format('Y-m-d')] = true;
        $cursor = clone $from;
        $cursor->modify('+7 days');
        while ($cursor <= $to) {
            $keys[$cursor->format('Y-m-d')] = true;
            $cursor->modify('+7 days');
        }
        $dates = array_keys($keys);
        sort($dates);

        return $dates;
    }

    // ═══════════════════════════════════════════════════════════
    // STEP 1: Create temp table with all stock items in one shot
    // ═══════════════════════════════════════════════════════════
    run_sql("CREATE TEMPORARY TABLE tmp_report (
        stockid VARCHAR(50) NOT NULL PRIMARY KEY,
        mnfCode VARCHAR(50),
        mnfpno VARCHAR(50),
        description VARCHAR(255),
        manufacturers_name VARCHAR(100),
        qohA DECIMAL(20,4) DEFAULT 0,
        qohB DECIMAL(20,4) DEFAULT 0
    ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);

    // Omit service/test SKUs from this report (shared list with v2/crosssection.php).
    $crosssection_excluded_stockids = include __DIR__ . '/../crosssection_excluded_skus.php';
    $crosssection_excluded_sql = implode(',', array_map(function ($id) use ($db) {
        return "'" . mysqli_real_escape_string($db, $id) . "'";
    }, $crosssection_excluded_stockids));
    run_sql("INSERT INTO tmp_report (stockid, mnfCode, mnfpno, description, manufacturers_name)
              SELECT sm.stockid, sm.mnfCode, sm.mnfpno, sm.description, m.manufacturers_name
              FROM stockmaster sm
              LEFT JOIN manufacturers m ON m.manufacturers_id = sm.brand
              WHERE sm.mbflag IN ('B','M')
                AND sm.stockid NOT IN ($crosssection_excluded_sql)", $db);

    $reportStockIds = [];
    $ridRes = run_sql('SELECT stockid FROM tmp_report', $db);
    while ($ridRow = DB_fetch_array($ridRes)) {
        $reportStockIds[] = $ridRow['stockid'];
    }

    $qohSnapshotCacheStats = [
        'opening' => 'computed',
        'closing' => 'computed',
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 2: Opening stock — reuse cross-date QOH snapshot cache when possible
    // ═══════════════════════════════════════════════════════════
    $openingFromCache = false;
    if (!$forceQohCacheRefresh) {
        $cachedOpen = $load_qoh_snapshot_from_cache($from, $reportStockIds);
        if ($cachedOpen !== null) {
            $apply_qoh_map_to_tmp_report_column('qohA', $cachedOpen);
            $qohSnapshotCacheStats['opening'] = 'cache';
            $openingFromCache = true;
        }
    }
    if (!$openingFromCache) {
        run_sql("CREATE TEMPORARY TABLE tmp_open_moves (
            stockid VARCHAR(50) NOT NULL,
            loccode VARCHAR(10) NOT NULL,
            stkmoveno INT NOT NULL,
            PRIMARY KEY (stockid, loccode)
        ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);

        run_sql("INSERT INTO tmp_open_moves
              SELECT stockid, loccode, MAX(stkmoveno)
              FROM stockmoves sm
              LEFT JOIN systypes st ON sm.type = st.typeid
              WHERE trandate <= '$from'
                AND NOT (
                    LOWER(COALESCE(st.typename, '')) = 'dc'
                    OR LOWER(COALESCE(st.typename, '')) LIKE '%shop sale%'
                )
              GROUP BY stockid, loccode", $db);

        run_sql("UPDATE tmp_report r
              INNER JOIN (
                  SELECT sm.stockid, SUM(sm.newqoh) AS qohA
                  FROM stockmoves sm
                  INNER JOIN tmp_open_moves om
                      ON sm.stockid = om.stockid
                     AND sm.loccode = om.loccode
                     AND sm.stkmoveno = om.stkmoveno
                  GROUP BY sm.stockid
              ) q ON r.stockid = q.stockid
              SET r.qohA = q.qohA", $db);

        run_sql('DROP TEMPORARY TABLE tmp_open_moves', $db);

        if (!$forceQohCacheRefresh) {
            $saveOpen = [];
            $soRes = run_sql('SELECT stockid, qohA FROM tmp_report', $db);
            while ($soRow = DB_fetch_array($soRes)) {
                $saveOpen[$soRow['stockid']] = (float) $soRow['qohA'];
            }
            $save_qoh_snapshot_to_cache($from, $saveOpen);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // STEP 3: Closing stock — same snapshot cache (typically different date than opening)
    // ═══════════════════════════════════════════════════════════
    $closingFromCache = false;
    if (!$forceQohCacheRefresh) {
        $cachedClose = $load_qoh_snapshot_from_cache($to, $reportStockIds);
        if ($cachedClose !== null) {
            $apply_qoh_map_to_tmp_report_column('qohB', $cachedClose);
            $qohSnapshotCacheStats['closing'] = 'cache';
            $closingFromCache = true;
        }
    }
    if (!$closingFromCache) {
        run_sql("CREATE TEMPORARY TABLE tmp_close_moves (
            stockid VARCHAR(50) NOT NULL,
            loccode VARCHAR(10) NOT NULL,
            stkmoveno INT NOT NULL,
            PRIMARY KEY (stockid, loccode)
        ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);

        run_sql("INSERT INTO tmp_close_moves
              SELECT stockid, loccode, MAX(stkmoveno)
              FROM stockmoves sm
              LEFT JOIN systypes st ON sm.type = st.typeid
              WHERE trandate <= '$to'
                AND NOT (
                    LOWER(COALESCE(st.typename, '')) = 'dc'
                    OR LOWER(COALESCE(st.typename, '')) LIKE '%shop sale%'
                )
              GROUP BY stockid, loccode", $db);

        run_sql("UPDATE tmp_report r
              INNER JOIN (
                  SELECT sm.stockid, SUM(sm.newqoh) AS qohB
                  FROM stockmoves sm
                  INNER JOIN tmp_close_moves cm
                      ON sm.stockid = cm.stockid
                     AND sm.loccode = cm.loccode
                     AND sm.stkmoveno = cm.stkmoveno
                  GROUP BY sm.stockid
              ) q ON r.stockid = q.stockid
              SET r.qohB = q.qohB", $db);

        run_sql('DROP TEMPORARY TABLE tmp_close_moves', $db);

        if (!$forceQohCacheRefresh) {
            $saveClose = [];
            $scRes = run_sql('SELECT stockid, qohB FROM tmp_report', $db);
            while ($scRow = DB_fetch_array($scRes)) {
                $saveClose[$scRow['stockid']] = (float) $scRow['qohB'];
            }
            $save_qoh_snapshot_to_cache($to, $saveClose);
        }
    }

    // ═══════════════════════════════════════════════════════════
    // STEP 4: Load igp_parchi pricing & calculate weighted prices
    // ═══════════════════════════════════════════════════════════
    $parchinoData = [];
    $sqlParchi = "SELECT p.stockid, p.quantity, p.price, p.adjust_unit_price, p.landing_factor
                  FROM igp_parchi p
                  INNER JOIN tmp_report r ON p.stockid = r.stockid
                  ORDER BY p.stockid, p.pdate DESC, p.id DESC";
    $resParchi = run_sql($sqlParchi, $db);
    while ($r = DB_fetch_array($resParchi)) {
        $sid = $r['stockid'];
        if (!isset($parchinoData[$sid])) {
            $parchinoData[$sid] = [];
        }
        $parchinoData[$sid][] = [
            'quantity'           => (float)$r['quantity'],
            'price'              => (float)$r['price'],
            'adjust_unit_price'  => (float)($r['adjust_unit_price'] ?? 0),
            'landing_factor'     => (float)($r['landing_factor'] ?? 1)
        ];
    }

    // Same quantity basis as itemsReport/index.php: current total locstock quantity across all locations.
    $currentTotalQtyByStockid = [];
    $resCurrentQty = run_sql("SELECT l.stockid, SUM(l.quantity) AS total_qty
                  FROM locstock l
                  INNER JOIN tmp_report r ON l.stockid = r.stockid
                  GROUP BY l.stockid", $db);
    while ($qtyRow = DB_fetch_array($resCurrentQty)) {
        $currentTotalQtyByStockid[$qtyRow['stockid']] = (float)$qtyRow['total_qty'];
    }

    // ═══════════════════════════════════════════════════════════
    // STEP 5: Fetch tmp_report and compute final values in PHP
    // ═══════════════════════════════════════════════════════════
    $response = [];
    $skuMeta = [];
    $result = run_sql("SELECT * FROM tmp_report", $db);
    while ($item = DB_fetch_array($result)) {
        $openQty  = (float)$item['qohA'];
        $closeQty = (float)$item['qohB'];
        $sid      = $item['stockid'];

        // Weighted unit price from igp_parchi using the exact same quantity basis as itemsReport.
        $qtyForPrice = $currentTotalQtyByStockid[$sid] ?? 0;
        $priceData = calculatePriceForStock($parchinoData[$sid] ?? [], $qtyForPrice);
        $unitPrice = (float)$priceData['weighted_unit_price'];

        // Pickup per-SKU landing factor exactly from latest igp_parchi row,
        // same source used by itemsReport frontend payload.
        $latestAdjust = 0;
        $latestLandingFactor = 1;
        if (!empty($parchinoData[$sid])) {
            $latest = $parchinoData[$sid][0]; // ORDER BY pdate DESC, id DESC
            $latestAdjust = (float)($latest['adjust_unit_price'] ?? 0);
            $latestLandingFactor = (float)($latest['landing_factor'] ?? 1);
        }
        if ($latestLandingFactor <= 0) {
            // If latest row has 0/blank factor, pick nearest valid factor for this SKU.
            if (!empty($parchinoData[$sid])) {
                foreach ($parchinoData[$sid] as $pRow) {
                    $candidateFactor = (float)($pRow['landing_factor'] ?? 0);
                    if ($candidateFactor > 0) {
                        $latestLandingFactor = $candidateFactor;
                        break;
                    }
                }
            }
            if ($latestLandingFactor <= 0) {
                $latestLandingFactor = 1;
            }
        }

        // Match itemsReport/frontend.php valuation fallback:
        // weighted_unit_price first, else adjust_unit_price.
        $effectiveUnitForValuation = $unitPrice > 0 ? $unitPrice : $latestAdjust;

        $item['adjust_unit_price'] = $latestAdjust;
        $item['landing_factor']    = $latestLandingFactor;
        // Match itemsReport "Adj. Price": (weighted unit price if present, else adjust price) × landing factor.
        // Example:
        // - weighted 1391.60, factor 1.4  => 1948.24
        // - adjust   4945.50, factor 1.4  => 6923.70
        $unitPriceCost = $effectiveUnitForValuation * $latestLandingFactor;
        $item['unitPriceCost']     = round($unitPriceCost, 2);
        $item['totalAmountFrom']   = round($openQty * $effectiveUnitForValuation * $latestLandingFactor, 2);
        $item['totalAmountTo']     = round($closeQty * $effectiveUnitForValuation * $latestLandingFactor, 2);

        $skuMeta[$sid] = [
            'effective' => (float)$effectiveUnitForValuation,
            'landing'   => (float)$latestLandingFactor,
        ];

        if ($action === 'search') {
            $response[] = $item;
        }
        unset($parchinoData[$sid]);
    }

    // ═══════════════════════════════════════════════════════════
    // Optional: per-date SKU breakdown (uses tmp_report for labels)
    // ═══════════════════════════════════════════════════════════
    if ($action === 'valuationBreakdown') {
        $snapshotInput = trim($_POST['snapshotDate'] ?? '');
        $snapDt = DateTime::createFromFormat('Y-m-d', $snapshotInput);
        if (!$snapDt || $snapDt->format('Y-m-d') !== $snapshotInput) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid snapshotDate. Expected YYYY-MM-DD.']);
            exit;
        }
        if ($snapDt < $fromDate || $snapDt > $toDate) {
            http_response_code(400);
            echo json_encode(['error' => 'snapshotDate must fall within the report From and To dates.']);
            exit;
        }

        $dEsc = mysqli_real_escape_string($db, $snapshotInput);
        $qohMap = $qoh_at_date($dEsc, array_keys($skuMeta));
        $lines = [];
        foreach ($skuMeta as $sid => $meta) {
            $q = $qohMap[$sid] ?? 0.0;
            $lv = round($q * $meta['effective'] * $meta['landing'], 2);
            if ($lv <= 0) {
                continue;
            }
            $lines[] = ['stockid' => $sid, 'lineValue' => $lv];
        }
        usort($lines, function ($a, $b) {
            return $b['lineValue'] <=> $a['lineValue'];
        });
        $topN = 50;
        $top = array_slice($lines, 0, $topN);
        $otherLines = array_slice($lines, $topN);
        $otherValue = 0.0;
        foreach ($otherLines as $ol) {
            $otherValue += $ol['lineValue'];
        }
        $otherValue = round($otherValue, 2);
        $totalValue = 0.0;
        foreach ($lines as $ln) {
            $totalValue += $ln['lineValue'];
        }
        $totalValue = round($totalValue, 2);

        $metaById = [];
        if (!empty($top)) {
            $ids = [];
            foreach ($top as $row) {
                $ids[] = "'" . mysqli_real_escape_string($db, $row['stockid']) . "'";
            }
            $inSql = implode(',', $ids);
            $descRes = run_sql("SELECT stockid, mnfCode, mnfpno, description, manufacturers_name FROM tmp_report WHERE stockid IN ($inSql)", $db);
            while ($row = DB_fetch_array($descRes)) {
                $metaById[$row['stockid']] = $row;
            }
        }
        $breakdownRows = [];
        foreach ($top as $row) {
            $sid = $row['stockid'];
            $m = $metaById[$sid] ?? [];
            $q = $qohMap[$sid] ?? 0.0;
            $breakdownRows[] = [
                'stockid'            => $sid,
                'mnfCode'            => $m['mnfCode'] ?? '',
                'mnfpno'             => $m['mnfpno'] ?? '',
                'description'        => $m['description'] ?? '',
                'manufacturers_name' => $m['manufacturers_name'] ?? '',
                'qoh'                => round((float) $q, 4),
                'lineValue'          => $row['lineValue'],
            ];
        }

        mysqli_query($db, "DROP TEMPORARY TABLE IF EXISTS tmp_report");
        echo json_encode([
            'snapshotDate'      => $snapshotInput,
            'totalValue'        => $totalValue,
            'breakdown'         => $breakdownRows,
            'otherCount'        => count($otherLines),
            'otherValue'        => $otherValue,
            'qohSnapshotCache'  => $qohSnapshotCacheStats,
        ]);
        exit;
    }

    // ═══════════════════════════════════════════════════════════
    // STEP 6: Valuation timeline (cached: historical points reuse DB; subset ranges reuse rows from
    // any superseding report window with same cost_basis_hash; current month + misses recalc)
    // ═══════════════════════════════════════════════════════════
    $valuationTimeline = [];
    $timelineCacheStats = [
        'fromCache'           => 0,
        'fromCacheSuperset'   => 0,
        'computed'            => 0,
        'bypassed'            => false,
    ];
    $forceRefreshTimelineCache = !empty($_POST['forceTimelineCacheRefresh']);

    $detailFromInput = '';
    $detailToInput = '';
    $granularityPost = 'daily';
    if ($action === 'valuationDetailTimeline') {
        $detailFromInput = trim($_POST['detailFrom'] ?? '');
        $detailToInput = trim($_POST['detailTo'] ?? '');
        $granularityPost = isset($_POST['granularity']) ? trim((string) $_POST['granularity']) : 'daily';
        if (!in_array($granularityPost, ['daily', 'weekly'], true)) {
            $granularityPost = 'daily';
        }

        $detailFromDt = DateTime::createFromFormat('Y-m-d', $detailFromInput);
        $detailToDt = DateTime::createFromFormat('Y-m-d', $detailToInput);
        $dfOk = $detailFromDt && $detailFromDt->format('Y-m-d') === $detailFromInput;
        $dtOk = $detailToDt && $detailToDt->format('Y-m-d') === $detailToInput;
        if (!$dfOk || !$dtOk) {
            http_response_code(400);
            echo json_encode(['error' => 'detailFrom and detailTo must be valid YYYY-MM-DD dates.']);
            exit;
        }
        if ($detailFromDt > $detailToDt) {
            http_response_code(400);
            echo json_encode(['error' => 'detailFrom must be on or before detailTo.']);
            exit;
        }
        if ($detailFromDt < $fromDate || $detailToDt > $toDate) {
            http_response_code(400);
            echo json_encode(['error' => 'Detail range must fall within the report From and To dates.']);
            exit;
        }

        $spanDays = (int) $detailFromDt->diff($detailToDt)->format('%a');
        if ($granularityPost === 'daily') {
            $timelineDates = crosssection_valuation_timeline_dates_daily($detailFromDt, $detailToDt);
        } else {
            if ($spanDays > 2800) {
                http_response_code(400);
                echo json_encode(['error' => 'Weekly detail range is too wide (max ~2800 days).']);
                exit;
            }
            $timelineDates = crosssection_valuation_timeline_dates_weekly($detailFromDt, $detailToDt);
        }
        if (count($timelineDates) > 400) {
            http_response_code(400);
            echo json_encode(['error' => 'Too many timeline points (max 400). Zoom in further or use weekly detail.']);
            exit;
        }
    } else {
        $timelineDates = crosssection_valuation_timeline_dates($fromDate, $toDate);
    }

    ksort($skuMeta);
    $hashLines = [];
    foreach ($skuMeta as $sid => $meta) {
        $hashLines[] = $sid . "\t" . sprintf('%.8F', $meta['effective']) . "\t" . sprintf('%.8F', $meta['landing']);
    }
    $costBasisHash = hash('sha256', implode("\n", $hashLines));

    run_sql("CREATE TABLE IF NOT EXISTS crosssection_valuation_timeline_cache (
        report_from DATE NOT NULL,
        report_to DATE NOT NULL,
        snapshot_date DATE NOT NULL,
        total_value DECIMAL(20,2) NOT NULL,
        cost_basis_hash CHAR(64) NOT NULL,
        computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (report_from, report_to, snapshot_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4", $db);

    $cachedByDate = [];
    /** @var array<string, 'exact'|'superset'> */
    $timelineCacheHitKind = [];
    $nowMonth = (new DateTime('today'))->format('Y-m');
    if (!$forceRefreshTimelineCache && !empty($timelineDates)) {
        $inList = [];
        foreach ($timelineDates as $d) {
            $inList[] = "'" . mysqli_real_escape_string($db, $d) . "'";
        }
        $inSql = implode(',', $inList);
        $hashEsc = mysqli_real_escape_string($db, $costBasisHash);
        // Snapshot totals depend only on inventory-at-date + cost basis, not on report_from/report_to.
        // Reuse any cached row whose stored window fully contains this request's [from, to].
        $cacheRes = run_sql(
            "SELECT snapshot_date, total_value, report_from, report_to
               FROM crosssection_valuation_timeline_cache
              WHERE cost_basis_hash = '$hashEsc'
                AND snapshot_date IN ($inSql)
                AND report_from <= '$from'
                AND report_to >= '$to'
              ORDER BY (report_from = '$from' AND report_to = '$to') DESC,
                       report_from ASC,
                       report_to DESC",
            $db
        );
        while ($row = DB_fetch_array($cacheRes)) {
            $sd = $row['snapshot_date'];
            if (isset($cachedByDate[$sd])) {
                continue;
            }
            $cachedByDate[$sd] = (float)$row['total_value'];
            $timelineCacheHitKind[$sd] = ($row['report_from'] === $from && $row['report_to'] === $to)
                ? 'exact'
                : 'superset';
        }
    }
    $timelineCacheStats['bypassed'] = $forceRefreshTimelineCache;

    foreach ($timelineDates as $dStr) {
        $snapMonth = substr($dStr, 0, 7);
        $inCurrentMonth = ($snapMonth === $nowMonth);

        $useCached = !$forceRefreshTimelineCache
            && !$inCurrentMonth
            && isset($cachedByDate[$dStr]);

        if ($useCached) {
            $totalVal = $cachedByDate[$dStr];
            $valuationTimeline[] = [
                'date'       => $dStr,
                'totalValue' => round($totalVal, 2),
            ];
            $timelineCacheStats['fromCache']++;
            if (($timelineCacheHitKind[$dStr] ?? '') === 'superset') {
                $timelineCacheStats['fromCacheSuperset']++;
            }
            continue;
        }

        $dEsc = mysqli_real_escape_string($db, $dStr);
        $qohMap = $qoh_at_date($dEsc, array_keys($skuMeta));
        $totalVal = 0.0;
        foreach ($skuMeta as $sid => $meta) {
            $q = $qohMap[$sid] ?? 0.0;
            $totalVal += round($q * $meta['effective'] * $meta['landing'], 2);
        }
        $totalVal = round($totalVal, 2);

        $valuationTimeline[] = [
            'date'       => $dStr,
            'totalValue' => $totalVal,
        ];
        $timelineCacheStats['computed']++;

        $hashEsc = mysqli_real_escape_string($db, $costBasisHash);
        run_sql(
            "INSERT INTO crosssection_valuation_timeline_cache
                (report_from, report_to, snapshot_date, total_value, cost_basis_hash, computed_at)
             VALUES ('$from', '$to', '" . mysqli_real_escape_string($db, $dStr) . "', $totalVal, '$hashEsc', NOW())
             ON DUPLICATE KEY UPDATE
                total_value = VALUES(total_value),
                cost_basis_hash = VALUES(cost_basis_hash),
                computed_at = NOW()",
            $db
        );
    }

    mysqli_query($db, "DROP TEMPORARY TABLE IF EXISTS tmp_report");

    if ($action === 'valuationDetailTimeline') {
        echo json_encode([
            'valuationTimeline'       => $valuationTimeline,
            'valuationTimelineCache'  => $timelineCacheStats,
            'qohSnapshotCache'        => $qohSnapshotCacheStats,
            'detailRange'             => [
                'from'        => $detailFromInput,
                'to'          => $detailToInput,
                'granularity' => $granularityPost,
            ],
        ]);
        exit;
    }

    echo json_encode([
        'rows'                   => $response,
        'valuationTimeline'      => $valuationTimeline,
        'valuationTimelineCache' => $timelineCacheStats,
        'qohSnapshotCache'       => $qohSnapshotCacheStats,
    ]);
    exit;
}

include_once("includes/header.php");
include_once("includes/sidebar.php");
?>

<style>
    .date {
        padding: 10px;
        border-radius: 7px;
    }
    thead tr, tfoot tr {
        background-color: #424242;
        color: white;
    }
    .dataTables_wrapper {
        overflow-x: auto;
    }
    #loadingMessage {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 20px;
        border-radius: 5px;
        z-index: 1000;
        display: none;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="col-md-12">
            <h1>Cross Sectional Stock Analysis (v2)</h1>
        </div>
        <label>From Date</label>
        <input type="date" class="date fromDate">
        <label>To Date</label>
        <input type="date" class="date toDate">
        <button class="btn btn-success date searchData">Search</button>
    </section>

    <div id="loadingMessage">Loading data... This may take a few minutes for large date ranges.</div>

    <section class="content">
        <div class="row" id="stockValueSummaryRow" style="margin-bottom: 10px;">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="cardTotalStartLabel">Total stock value (start date)</span>
                        <span class="info-box-number" id="cardTotalStartValue">—</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="ion ion-cash"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="cardTotalEndLabel">Total stock value (end date)</span>
                        <span class="info-box-number" id="cardTotalEndValue">—</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" id="valuationChartRow" style="margin-bottom: 15px; display: none;">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Inventory valuation over period</h3>
                    </div>
                    <div class="box-body" style="position: relative;">
                        <p class="text-muted" style="margin-top: 0; font-size: 12px;">Default series: report start, first of each month in range, and end date (same SKUs, exclusions, and costing as the table). Wheel zoom and drag pan on the chart; click a point for top SKUs at that date; use detail buttons to load daily/weekly points for the visible range (server recomputes).</p>
                        <div class="btn-toolbar" style="margin-bottom: 8px; flex-wrap: wrap;">
                            <div class="btn-group btn-group-sm" style="margin-bottom: 4px;">
                                <button type="button" class="btn btn-default" id="valuationChartResetZoom" title="Show full range"><i class="fa fa-search-minus"></i> Reset zoom</button>
                                <button type="button" class="btn btn-default" id="valuationChartDailyDetail" title="Replace chart with daily snapshots for indices currently visible"><i class="fa fa-calendar"></i> Daily detail (visible)</button>
                                <button type="button" class="btn btn-default" id="valuationChartWeeklyDetail" title="Replace chart with weekly snapshots for indices currently visible"><i class="fa fa-calendar-o"></i> Weekly detail (visible)</button>
                            </div>
                            <span class="text-muted small" style="margin-left: 8px; line-height: 28px;">Wheel: zoom · Drag: pan · Click point: breakdown</span>
                        </div>
                        <div id="valuationChartVisibleRange" class="text-muted small" style="margin-bottom: 6px;"></div>
                        <div id="valuationChartDetailNote" class="text-info small" style="margin-bottom: 6px; display: none;"></div>
                        <div style="height: 300px; position: relative;">
                            <canvas id="valuationTimelineChart"></canvas>
                        </div>
                        <p class="text-muted small" style="margin: 6px 0 4px;">Overview (full series)</p>
                        <div style="height: 56px; position: relative; max-width: 100%;">
                            <canvas id="valuationTimelineNavChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="valuationDrilldownModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Valuation breakdown <span id="valuationDrilldownModalDate"></span></h4>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <p class="text-muted" id="valuationDrilldownSummary" style="margin-top: 0;"></p>
                        <div class="table-responsive">
                            <table class="table table-striped table-condensed table-bordered" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th>Stock ID</th>
                                        <th>Mnf code</th>
                                        <th>Part no.</th>
                                        <th>Description</th>
                                        <th>Brand</th>
                                        <th class="text-right">QOH @ date</th>
                                        <th class="text-right">Line value</th>
                                    </tr>
                                </thead>
                                <tbody id="valuationDrilldownTbody"></tbody>
                            </table>
                        </div>
                        <p class="text-muted small" id="valuationDrilldownOther" style="display: none;"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <table class="table table-striped table-responsive" border="1" id="datatable">
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Manufacturers Code</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th id="thQtyFrom">Int. Ref. Quantity</th>
                    <th>Unit Price Cost</th>
                    <th id="thQtyTo">Qty</th>
                    <th id="thTotalFrom">Total Amount @From</th>
                    <th id="thTotalTo">Total Amount @To</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Stock ID</th>
                    <th>Manufacturers Code</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>Int. Ref. Quantity</th>
                    <th>Unit Price Cost</th>
                    <th>Qty</th>
                    <th>Total Amount @From</th>
                    <th>Total Amount @To</th>
                </tr>
            </tfoot>
        </table>
    </section>
</div>

<?php include_once("includes/footer.php"); ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8/hammer.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@2.2.0/dist/chartjs-plugin-zoom.min.js"></script>
<script>
(function () {
    if (typeof Chart !== 'undefined' && typeof ChartZoom !== 'undefined') {
        Chart.register(ChartZoom);
    }
})();
$(document).ready(function() {
    function formatAmount2(n) {
        var x = Math.round((parseFloat(n) + Number.EPSILON) * 100) / 100;
        var parts = x.toFixed(2).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    function updateStockValueCards(response, fromDate, toDate) {
        var totalFrom = 0, totalTo = 0;
        for (var i = 0; i < response.length; i++) {
            var row = response[i] || {};
            // Same basis as Total Amount columns (PHP: qty × effective unit × landing factor).
            // Omits excluded SKUs because they never appear in the payload (tmp_report scope).
            totalFrom += parseFloat(row.totalAmountFrom) || 0;
            totalTo += parseFloat(row.totalAmountTo) || 0;
        }
        $('#cardTotalStartLabel').text('Total stock value on ' + fromDate + ' (start)');
        $('#cardTotalEndLabel').text('Total stock value on ' + toDate + ' (end)');
        $('#cardTotalStartValue').text(formatAmount2(totalFrom));
        $('#cardTotalEndValue').text(formatAmount2(totalTo));
    }

    var valuationChartInstance = null;
    var valuationNavChartInstance = null;
    var valuationTimelineRaw = [];
    var valuationTimelineDetailMeta = null;
    var valuationBreakdownXhr = null;

    function formatTimelineLabel(isoDate) {
        if (!isoDate || typeof isoDate !== 'string') {
            return '';
        }
        var p = isoDate.split('-');
        if (p.length !== 3) {
            return isoDate;
        }
        var m = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var mi = parseInt(p[1], 10) - 1;
        var d = parseInt(p[2], 10);
        if (isNaN(mi) || mi < 0 || mi > 11 || isNaN(d)) {
            return isoDate;
        }
        return d === 1 ? (m[mi] + ' ' + p[0]) : (p[2] + ' ' + m[mi] + ' ' + p[0]);
    }

    function thinTimelineLabels(rawLabels) {
        var labels = rawLabels.slice();
        var n = labels.length;
        if (n <= 24) {
            return labels;
        }
        var maxVisible = n <= 120 ? 24 : 18;
        var step = Math.max(1, Math.ceil(n / maxVisible));
        for (var i = 0; i < n; i++) {
            if (i === 0 || i === n - 1 || (i % step === 0)) {
                continue;
            }
            labels[i] = '';
        }
        return labels;
    }

    function getVisibleIndexRange(chart) {
        if (!chart || !chart.scales || !chart.scales.x) {
            return null;
        }
        var x = chart.scales.x;
        var n = valuationTimelineRaw.length;
        if (!n) {
            return null;
        }
        var min = typeof x.min === 'number' ? x.min : 0;
        var max = typeof x.max === 'number' ? x.max : (n - 1);
        var i0 = Math.max(0, Math.floor(Math.min(min, max)));
        var i1 = Math.min(n - 1, Math.ceil(Math.max(min, max)));
        if (i0 > i1) {
            var t = i0; i0 = i1; i1 = t;
        }
        return { i0: i0, i1: i1 };
    }

    function updateVisibleRangeLabel(chart) {
        var r = getVisibleIndexRange(chart);
        var el = $('#valuationChartVisibleRange');
        if (!r || !valuationTimelineRaw.length) {
            el.text('');
            return;
        }
        var d0 = valuationTimelineRaw[r.i0] && valuationTimelineRaw[r.i0].date;
        var d1 = valuationTimelineRaw[r.i1] && valuationTimelineRaw[r.i1].date;
        var cnt = r.i1 - r.i0 + 1;
        if (d0 && d1) {
            el.text('Showing: ' + d0 + ' — ' + d1 + ' (' + cnt + ' point' + (cnt !== 1 ? 's' : '') + ' visible)');
        }
    }

    function buildChartOptions(denseSeries) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            animation: denseSeries ? false : undefined,
            interaction: { mode: 'nearest', intersect: false, axis: 'x' },
            onClick: function(evt, elements) {
                if (!elements || !elements.length) {
                    return;
                }
                var idx = elements[0].index;
                var pt = valuationTimelineRaw[idx];
                if (pt && pt.date) {
                    openValuationBreakdownModal(pt.date);
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        title: function(items) {
                            var i = items[0].dataIndex;
                            var p = valuationTimelineRaw[i];
                            return p && p.date ? p.date : '';
                        },
                        label: function(ctx) {
                            return formatAmount2(ctx.parsed.y);
                        }
                    }
                },
                zoom: {
                    limits: {
                        x: { min: 'original', max: 'original', minRange: 1 }
                    },
                    pan: {
                        enabled: true,
                        mode: 'x',
                        onPanComplete: function(ctx) {
                            updateVisibleRangeLabel(ctx.chart);
                        }
                    },
                    zoom: {
                        wheel: { enabled: true },
                        pinch: { enabled: true },
                        mode: 'x',
                        onZoomComplete: function(ctx) {
                            updateVisibleRangeLabel(ctx.chart);
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: denseSeries ? 18 : 24
                    }
                },
                y: {
                    ticks: {
                        callback: function(v) {
                            return formatAmount2(v);
                        }
                    }
                }
            }
        };
    }

    function destroyValuationCharts() {
        if (valuationChartInstance) {
            valuationChartInstance.destroy();
            valuationChartInstance = null;
        }
        if (valuationNavChartInstance) {
            valuationNavChartInstance.destroy();
            valuationNavChartInstance = null;
        }
    }

    function updateValuationChart(timeline) {
        var $row = $('#valuationChartRow');
        var $detailNote = $('#valuationChartDetailNote');
        if (!timeline || timeline.length === 0) {
            $row.hide();
            valuationTimelineRaw = [];
            valuationTimelineDetailMeta = null;
            $detailNote.hide().text('');
            destroyValuationCharts();
            return;
        }
        $row.show();
        valuationTimelineRaw = timeline.slice();
        if (!valuationTimelineDetailMeta) {
            $detailNote.hide().text('');
        }

        var rawLabels = timeline.map(function(p) { return formatTimelineLabel(p.date); });
        var labels = thinTimelineLabels(rawLabels);
        var values = timeline.map(function(p) { return parseFloat(p.totalValue) || 0; });
        var denseSeries = values.length > 72;

        var navVals = values.slice();
        var navLabels = rawLabels.slice();

        destroyValuationCharts();

        var canvas = document.getElementById('valuationTimelineChart');
        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        valuationChartInstance = new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total stock value',
                    data: values,
                    borderColor: 'rgb(60, 141, 188)',
                    backgroundColor: 'rgba(60, 141, 188, 0.12)',
                    fill: true,
                    tension: 0,
                    pointRadius: denseSeries ? 0 : 3,
                    pointHoverRadius: denseSeries ? 4 : 5,
                    borderWidth: 2
                }]
            },
            options: buildChartOptions(denseSeries)
        });

        var navCanvas = document.getElementById('valuationTimelineNavChart');
        if (navCanvas) {
            valuationNavChartInstance = new Chart(navCanvas, {
                type: 'line',
                data: {
                    labels: navLabels,
                    datasets: [{
                        label: 'Overview',
                        data: navVals,
                        borderColor: 'rgba(100, 100, 100, 0.55)',
                        backgroundColor: 'rgba(60, 141, 188, 0.08)',
                        fill: true,
                        tension: 0,
                        pointRadius: 0,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    }
                }
            });
        }

        updateVisibleRangeLabel(valuationChartInstance);
    }

    function setDetailNote(meta) {
        var el = $('#valuationChartDetailNote');
        valuationTimelineDetailMeta = meta;
        if (!meta) {
            el.hide().text('');
            return;
        }
        el.show().text('Detail series: ' + meta.granularity + ' points from ' + meta.from + ' to ' + meta.to + ' (report range unchanged). Run Search to restore default series.');
    }

    function fetchDetailTimeline(granularity) {
        var from = $('.fromDate').val();
        var to = $('.toDate').val();
        if (!from || !to || !valuationChartInstance || !valuationTimelineRaw.length) {
            alert('Load a report first, then zoom to the range you want to expand.');
            return;
        }
        var r = getVisibleIndexRange(valuationChartInstance);
        if (!r) {
            return;
        }
        var d0 = valuationTimelineRaw[r.i0].date;
        var d1 = valuationTimelineRaw[r.i1].date;
        if (!d0 || !d1) {
            return;
        }
        $("#loadingMessage").show();
        $.ajax({
            url: 'index.php',
            method: 'POST',
            dataType: 'json',
            timeout: 600000,
            data: {
                from: from,
                to: to,
                action: 'valuationDetailTimeline',
                detailFrom: d0,
                detailTo: d1,
                granularity: granularity
            }
        }).done(function(resp) {
            if (resp && resp.error) {
                alert(resp.error);
                return;
            }
            if (resp && resp.valuationTimeline && resp.valuationTimeline.length) {
                var dr = resp.detailRange || { from: d0, to: d1, granularity: granularity };
                setDetailNote(dr);
                updateValuationChart(resp.valuationTimeline);
            } else {
                alert('No timeline data returned for this range.');
            }
        }).fail(function(xhr) {
            var msg = 'Request failed';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            } else if (xhr.status === 400 && xhr.responseText) {
                try {
                    var j = JSON.parse(xhr.responseText);
                    if (j.error) {
                        msg = j.error;
                    }
                } catch (e) { /* ignore */ }
            }
            alert(msg);
        }).always(function() {
            $("#loadingMessage").hide();
        });
    }

    function openValuationBreakdownModal(isoDate) {
        var from = $('.fromDate').val();
        var to = $('.toDate').val();
        if (!from || !to) {
            return;
        }
        $('#valuationDrilldownModalDate').text('— ' + isoDate);
        $('#valuationDrilldownSummary').text('Loading…');
        $('#valuationDrilldownTbody').empty();
        $('#valuationDrilldownOther').hide().text('');
        $('#valuationDrilldownModal').modal('show');

        if (valuationBreakdownXhr) {
            valuationBreakdownXhr.abort();
        }
        valuationBreakdownXhr = $.ajax({
            url: 'index.php',
            method: 'POST',
            dataType: 'json',
            timeout: 600000,
            data: {
                from: from,
                to: to,
                action: 'valuationBreakdown',
                snapshotDate: isoDate
            }
        }).done(function(resp) {
            if (resp && resp.error) {
                $('#valuationDrilldownSummary').text(resp.error);
                return;
            }
            if (!resp || !resp.breakdown) {
                $('#valuationDrilldownSummary').text('No data.');
                return;
            }
            $('#valuationDrilldownSummary').text('Total value at snapshot: ' + formatAmount2(resp.totalValue));
            var $tb = $('#valuationDrilldownTbody');
            $tb.empty();
            for (var i = 0; i < resp.breakdown.length; i++) {
                var r = resp.breakdown[i];
                var $tr = $('<tr>');
                $tr.append($('<td>').text(r.stockid || ''));
                $tr.append($('<td>').text(r.mnfCode || ''));
                $tr.append($('<td>').text(r.mnfpno || ''));
                $tr.append($('<td>').text(r.description || ''));
                $tr.append($('<td>').text(r.manufacturers_name || ''));
                $tr.append($('<td class="text-right">').text(formatAmount2(r.qoh)));
                $tr.append($('<td class="text-right">').text(formatAmount2(r.lineValue)));
                $tb.append($tr);
            }
            if (resp.otherCount > 0) {
                $('#valuationDrilldownOther').show().text(
                    'Other SKUs: ' + resp.otherCount + ' — combined value ' + formatAmount2(resp.otherValue)
                );
            }
        }).fail(function(xhr) {
            var msg = 'Request failed';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            $('#valuationDrilldownSummary').text(msg);
        });
    }

    $('#valuationChartResetZoom').on('click', function() {
        if (valuationChartInstance && typeof valuationChartInstance.resetZoom === 'function') {
            valuationChartInstance.resetZoom();
            updateVisibleRangeLabel(valuationChartInstance);
        }
    });
    $('#valuationChartDailyDetail').on('click', function() { fetchDetailTimeline('daily'); });
    $('#valuationChartWeeklyDetail').on('click', function() { fetchDetailTimeline('weekly'); });

    let table = $('#datatable').DataTable({
        dom: 'Bflrtip',
        lengthMenu: [10, 25, 50, 75, 100],
        deferRender: true,
        buttons: [
            'copy',
            {
                text: 'Download CSV',
                action: function() {
                    let from = $('.fromDate').val();
                    let to = $('.toDate').val();

                    if (!from || !to) {
                        alert("Please select both From and To dates.");
                        return;
                    }

                    let data = table.rows().data();
                    let headers = ['Stock ID','Manufacturers Code','Part No.','Description','Brand',
                        'Int. Ref. Quantity ' + from, 'Unit Price Cost',
                        'Qty ' + to, 'Total Amount @' + from, 'Total Amount @' + to];
                    let csv = headers.join(',') + '\n';

                    for (let i = 0; i < data.length; i++) {
                        let r = data[i];
                        let row = [
                            '"' + (r.stockid || '') + '"',
                            '"' + (r.mnfCode || '') + '"',
                            '"' + (r.mnfpno || '') + '"',
                            '"' + (r.description || '').replace(/"/g, '""') + '"',
                            '"' + (r.manufacturers_name || '') + '"',
                            parseFloat(r.qohA || 0),
                            parseFloat(r.unitPriceCost || 0).toFixed(2),
                            parseFloat(r.qohB || 0),
                            parseFloat(r.totalAmountFrom || 0).toFixed(2),
                            parseFloat(r.totalAmountTo || 0).toFixed(2)
                        ];
                        csv += row.join(',') + '\n';
                    }

                    let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    let link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'cross_section_v2_' + from + '_' + to + '.csv';
                    link.click();
                    URL.revokeObjectURL(link.href);
                }
            },
            'excel'
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        },
        columns: [
            { data: "stockid" },
            { data: "mnfCode" },
            { data: "mnfpno" },
            { data: "description" },
            { data: "manufacturers_name" },
            { data: "qohA", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "unitPriceCost", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "qohB", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "totalAmountFrom", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "totalAmountTo", render: $.fn.dataTable.render.number(',', '.', 2) }
        ],
        initComplete: function() {
            this.api().columns().every(function() {
                var column = this;
                $('<input type="text" placeholder="Search '+column.header().textContent+'" />')
                    .appendTo($(column.footer()).empty())
                    .on('keyup change', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
            });
        }
    });

    var searchDebounceTimer = null;
    var searchActiveXhr = null;
    var searchRequestSeq = 0;
    var SEARCH_DEBOUNCE_MS = 500;

    function dedupeRowsByStockId(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            return rows;
        }
        var seen = Object.create(null);
        var out = [];
        for (var i = 0; i < rows.length; i++) {
            var raw = rows[i] && rows[i].stockid != null ? String(rows[i].stockid) : '';
            var key = raw !== '' ? raw.toLowerCase() : '';
            if (key !== '' && seen[key]) {
                continue;
            }
            if (key !== '') {
                seen[key] = true;
            }
            out.push(rows[i]);
        }
        return out;
    }

    function executeCrossSectionSearch() {
        var from = $(".fromDate").val();
        var to = $(".toDate").val();

        if (!from || !to) {
            alert("Please select both From and To dates.");
            $(".searchData").prop("disabled", false);
            return;
        }

        var mySeq = ++searchRequestSeq;
        setDetailNote(null);

        if (searchActiveXhr) {
            searchActiveXhr.abort();
            searchActiveXhr = null;
        }

        $(".searchData").prop("disabled", true);

        $('#thQtyFrom').text('Int. Ref. Quantity ' + from);
        $('#thQtyTo').text('Qty ' + to);
        $('#thTotalFrom').text('Total Amount @' + from);
        $('#thTotalTo').text('Total Amount @' + to);

        $("#loadingMessage").show();
        $('#cardTotalStartValue, #cardTotalEndValue').text('…');
        $('#valuationChartRow').hide();
        table.clear().draw(false);

        var thisXhr = $.ajax({
            url: "index.php",
            method: "POST",
            data: { from: from, to: to },
            dataType: "json",
            timeout: 600000,
            success: function(response) {
                if (mySeq !== searchRequestSeq || thisXhr !== searchActiveXhr) {
                    return;
                }
                if (response && response.rows && Array.isArray(response.rows)) {
                    var rows = dedupeRowsByStockId(response.rows);
                    table.clear();
                    table.rows.add(rows).draw();
                    updateStockValueCards(rows, from, to);
                    updateValuationChart(response.valuationTimeline || []);
                } else if (Array.isArray(response)) {
                    var rows = dedupeRowsByStockId(response);
                    table.clear();
                    table.rows.add(rows).draw();
                    updateStockValueCards(rows, from, to);
                    updateValuationChart([]);
                } else if (response && response.error) {
                    alert("Server error: " + (response.error || "unknown"));
                    $('#cardTotalStartValue, #cardTotalEndValue').text('—');
                    updateValuationChart(null);
                } else {
                    $('#cardTotalStartValue, #cardTotalEndValue').text('—');
                    updateValuationChart([]);
                }
            },
            error: function(xhr, status, error) {
                if (mySeq !== searchRequestSeq || thisXhr !== searchActiveXhr) {
                    return;
                }
                if (status === 'abort') {
                    return;
                }
                if (status === 'timeout') {
                    alert("Request timed out. Try a smaller date range.");
                } else {
                    alert("Error loading data: " + error + "\n" + (xhr.responseText || '').substring(0, 200));
                }
                $('#cardTotalStartValue, #cardTotalEndValue').text('—');
                updateValuationChart(null);
            },
            complete: function(xhr, status) {
                if (mySeq !== searchRequestSeq || thisXhr !== searchActiveXhr) {
                    return;
                }
                searchActiveXhr = null;
                $("#loadingMessage").hide();
                $(".searchData").prop("disabled", false);
            }
        });
        searchActiveXhr = thisXhr;
    }

    $(".searchData").on("click", function() {
        var $btn = $(".searchData");
        if ($btn.prop("disabled")) {
            return;
        }
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function() {
            executeCrossSectionSearch();
        }, SEARCH_DEBOUNCE_MS);
    });
});
</script>

<?php include_once("includes/foot.php"); ?>
