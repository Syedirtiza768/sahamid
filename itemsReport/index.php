<?php
// ✅ INCREASE MEMORY LIMIT AT THE VERY TOP
ini_set('memory_limit', '512M');  // Increase to 512MB
set_time_limit(300);  // 5 minutes timeout

ob_start();
header('Content-Type: application/json');
require_once('includes/session.inc');
require_once('includes/config.php');
require_once(__DIR__ . '/report_helpers.php');

if (!isset($_SESSION['UsersRealName'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Inventory list: same B/M scope and igp_parchi-based unit costs as v2/crosssection2 (valuation alignment).
    // Create database connection
    $db = getDBConnection();
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // ✅ OPTIMIZED: Process in batches to save memory
    $batchSize = 1000;
    $allData = [];
    $processedCount = 0;

    // Same product scope as v2/crosssection2: bought + manufactured parts only
    $countResult = mysqli_query($db, "SELECT COUNT(*) as total
        FROM stockmaster
        WHERE stockmaster.mbflag IN ('B','M')");
    $totalCount = mysqli_fetch_assoc($countResult)['total'];

    // Load these audit/price lookups once. Repeating full stockmoves and
    // bpitems scans inside every 1,000-row batch can time out the API.
    $latestOutwardDates = getReportLatestOutwardDates($db);
    $fallbackPrices = getReportFallbackPrices($db);

    // ✅ Process in batches
    for ($offset = 0; $offset < $totalCount; $offset += $batchSize) {
        error_log("Processing batch: $offset to " . ($offset + $batchSize));

        // Main product query for this batch
        $SQL = "SELECT stockmaster.stockid,
                COALESCE(manufacturers_name, '') AS manufacturers_name,
                lastcost, materialcost,
                lastcostupdate, lastupdatedby, mnfCode, mnfpno,
                COALESCE(abbreviation, '') AS abbreviation,
                COALESCE(categorydescription, '') AS categorydescription,
                stockmaster.description
                FROM stockmaster 
                LEFT JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id 
                LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid 
                LEFT JOIN itemcondition ON stockmaster.conditionID = itemcondition.conditionID
                WHERE stockmaster.mbflag IN ('B','M')
                ORDER BY stockmaster.stockid
                LIMIT $offset, $batchSize";

        $res = mysqli_query($db, $SQL);
        if (!$res) {
            throw new Exception('Database query failed: ' . mysqli_error($db));
        }

        // ✅ Collect stock IDs for this batch
        $batchStockIds = [];
        $batchRows = [];

        while ($row = mysqli_fetch_assoc($res)) {
            $batchStockIds[] = $row['stockid'];
            $batchRows[$row['stockid']] = $row;
        }

        if (empty($batchStockIds)) {
            mysqli_free_result($res);
            continue;
        }

        // ✅ Get quantities for this batch
        $quantities = [];
        $totalQuantities = [];
        $stockIdsStr = "'" . implode("','", array_map(function ($id) use ($db) {
            return mysqli_real_escape_string($db, $id);
        }, $batchStockIds)) . "'";

        $SQL_qty = "SELECT stockid, loccode, SUM(quantity) as quantity 
                   FROM locstock 
                   WHERE stockid IN ($stockIdsStr)
                   GROUP BY stockid, loccode";

        $res_qty = mysqli_query($db, $SQL_qty);
        if ($res_qty) {
            while ($row_qty = mysqli_fetch_assoc($res_qty)) {
                $stockIdQty = $row_qty['stockid'];
                $qty = (float)$row_qty['quantity'];
                $quantities[$stockIdQty][$row_qty['loccode']] = $qty;
                if (!isset($totalQuantities[$stockIdQty])) {
                    $totalQuantities[$stockIdQty] = 0;
                }
                $totalQuantities[$stockIdQty] += $qty;
            }
            mysqli_free_result($res_qty);
        }

        // ✅ Get parchino data for this batch - INCLUDING adjust_unit_price and landing_factor
        $parchinoData = [];
        $SQL_parchinos = "SELECT id, stockid, quantity, price, adjust_unit_price, landing_factor, pdate
                            FROM igp_parchi 
                            WHERE stockid IN ($stockIdsStr)
                            ORDER BY stockid, pdate DESC, id DESC";

        $res_parchinos = mysqli_query($db, $SQL_parchinos);
        if ($res_parchinos) {
            while ($row = mysqli_fetch_assoc($res_parchinos)) {
                if (!isset($parchinoData[$row['stockid']])) {
                    $parchinoData[$row['stockid']] = [];
                }
                $parchinoData[$row['stockid']][] = [
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'adjust_unit_price' => $row['adjust_unit_price'] ?? 0,
                    'landing_factor' => $row['landing_factor'] ?? 1
                ];
            }
            mysqli_free_result($res_parchinos);
        }

        // ✅ Process each item in this batch
        $locations = ['HO', 'MT', 'SR', 'OS', 'VSR', 'WS'];

        foreach ($batchStockIds as $stockid) {
            if (!isset($batchRows[$stockid])) continue;

            $item = $batchRows[$stockid];

            // Keep legacy per-location columns for UI, but value/stat calculations use all location codes.
            foreach ($locations as $location) {
                $item['qty' . $location] = $quantities[$stockid][$location] ?? 0;
            }
            $totalQty = $totalQuantities[$stockid] ?? 0;
            $item['total_qty'] = $totalQty;

            $fallbackPrice = isset($fallbackPrices[$stockid])
                ? $fallbackPrices[$stockid]['price']
                : 0;
            $priceData = calculatePriceForStock(
                $parchinoData[$stockid] ?? [],
                $totalQty,
                $fallbackPrice
            );
            $item['total_bpitems_price'] = $priceData['total_bpitems_price'];
            $item['weighted_unit_price'] = $priceData['weighted_unit_price'];
            $item['total_quantity'] = $priceData['total_quantity'];
            $item['unpriced_quantity'] = $priceData['unpriced_quantity'];
            $item['price_status'] = $priceData['price_status'];
            $item['price_source'] = $priceData['price_source'];
            $item['price_coverage_percent'] = $priceData['price_coverage_percent'];

            // Get the latest adjust_unit_price and landing_factor from the most recent parchino record
            if (!empty($parchinoData[$stockid])) {
                // Use the most recent record's values (assuming first in array due to ORDER BY pdate DESC)
                $latestParchino = $parchinoData[$stockid][0];
                $item['adjust_unit_price'] = $latestParchino['adjust_unit_price'] ?? 0;
                $item['landing_factor'] = $latestParchino['landing_factor'] ?? 1;

                // Check if ANY parchino record has a manual price adjustment
                $item['has_manual_price'] = false;
                foreach ($parchinoData[$stockid] as $p) {
                    if (floatval($p['adjust_unit_price'] ?? 0) > 0) {
                        $item['has_manual_price'] = true;
                        break;
                    }
                }
            } else {
                $item['adjust_unit_price'] = 0;
                $item['landing_factor'] = 1;
                $item['has_manual_price'] = false;
            }

            $item['latest_outward_date'] = $latestOutwardDates[$stockid] ?? null;
            // Keep the old field for existing clients and saved CSV workflows.
            $item['latest_trandate'] = $item['latest_outward_date'];

            $allData[] = $item;
            $processedCount++;

            // Free memory for processed item
            unset($batchRows[$stockid], $quantities[$stockid], $totalQuantities[$stockid], $parchinoData[$stockid]);
        }

        // Free batch memory
        mysqli_free_result($res);
        unset($batchStockIds, $batchRows, $quantities, $totalQuantities, $parchinoData);

        // Force garbage collection
        if ($offset % 5000 == 0) {
            gc_collect_cycles();
        }

        // Send progress to client if needed
        if (isset($_GET['progress']) && $offset % 5000 == 0) {
            $percent = round(($offset / $totalCount) * 100, 2);
            error_log("Progress: $percent% ($processedCount/$totalCount)");
        }
    }

    // ✅ Send response
    $buffer = ob_get_clean();

    if (!empty($buffer)) {
        echo json_encode([
            'status' => 'error',
            'error' => 'Server output error',
            'data' => []
        ]);
    } else {
        echo json_encode([
            'status' => 'success',
            'data' => $allData,
            'count' => count($allData),
            'message' => "Processed $processedCount products"
        ]);
    }
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit();
