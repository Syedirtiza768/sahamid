<?php
// ✅ INCREASE MEMORY LIMIT AT THE VERY TOP
ini_set('memory_limit', '512M');  // Increase to 512MB
set_time_limit(300);  // 5 minutes timeout

ob_start();
header('Content-Type: application/json');
require_once('includes/session.inc');
require_once('includes/config.php');

if (!isset($_SESSION['UsersRealName'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // Create database connection
    $db = getDBConnection();
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // ✅ OPTIMIZED: Process in batches to save memory
    $batchSize = 1000;
    $allData = [];
    $processedCount = 0;
    
    // Get total count first
    $countResult = mysqli_query($db, "SELECT COUNT(*) as total FROM stockmaster");
    $totalCount = mysqli_fetch_assoc($countResult)['total'];
    
    // ✅ Process in batches
    for ($offset = 0; $offset < $totalCount; $offset += $batchSize) {
        error_log("Processing batch: $offset to " . ($offset + $batchSize));
        
        // Main product query for this batch
        $SQL = "SELECT stockmaster.stockid, manufacturers_name, lastcost, materialcost,
                lastcostupdate, lastupdatedby, mnfCode, mnfpno, abbreviation, categorydescription,
                stockmaster.description
                FROM stockmaster 
                INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id 
                INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid 
                INNER JOIN itemcondition ON stockmaster.conditionID = itemcondition.conditionID
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
        $stockIdsStr = "'" . implode("','", array_map(function($id) use ($db) {
            return mysqli_real_escape_string($db, $id);
        }, $batchStockIds)) . "'";
        
        $SQL_qty = "SELECT stockid, loccode, SUM(quantity) as quantity 
                   FROM locstock 
                   WHERE stockid IN ($stockIdsStr)
                   GROUP BY stockid, loccode";
        
        $res_qty = mysqli_query($db, $SQL_qty);
        if ($res_qty) {
            while ($row_qty = mysqli_fetch_assoc($res_qty)) {
                $quantities[$row_qty['stockid']][$row_qty['loccode']] = $row_qty['quantity'];
            }
            mysqli_free_result($res_qty);
        }
        
        // ✅ Get parchino data for this batch
        $parchinoData = [];
        $SQL_parchinos = "SELECT stockid, quantity, price 
                         FROM igp_parchi 
                         WHERE stockid IN ($stockIdsStr)
                         ORDER BY stockid, pdate DESC";
        
        $res_parchinos = mysqli_query($db, $SQL_parchinos);
        if ($res_parchinos) {
            while ($row = mysqli_fetch_assoc($res_parchinos)) {
                if (!isset($parchinoData[$row['stockid']])) {
                    $parchinoData[$row['stockid']] = [];
                }
                $parchinoData[$row['stockid']][] = [
                    'quantity' => $row['quantity'],
                    'price' => $row['price']
                ];
            }
            mysqli_free_result($res_parchinos);
        }
        
        // ✅ Process each item in this batch
        $locations = ['HO', 'MT', 'SR', 'OS', 'VSR', 'WS'];
        
        foreach ($batchStockIds as $stockid) {
            if (!isset($batchRows[$stockid])) continue;
            
            $item = $batchRows[$stockid];
            
            // Add quantities
            $totalQty = 0;
            foreach ($locations as $location) {
                $qty = $quantities[$stockid][$location] ?? 0;
                $item['qty' . $location] = $qty;
                $totalQty += $qty;
            }
            $item['total_qty'] = $totalQty;
            
            // Calculate price
            $priceData = calculatePriceForStock($parchinoData[$stockid] ?? [], $totalQty);
            $item['total_bpitems_price'] = $priceData['total_bpitems_price'];
            $item['weighted_unit_price'] = $priceData['weighted_unit_price'];
            $item['total_quantity'] = $priceData['total_quantity'];
            
            $allData[] = $item;
            $processedCount++;
            
            // Free memory for processed item
            unset($batchRows[$stockid], $quantities[$stockid], $parchinoData[$stockid]);
        }
        
        // Free batch memory
        mysqli_free_result($res);
        unset($batchStockIds, $batchRows, $quantities, $parchinoData);
        
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

// ✅ Optimized price calculation
function calculatePriceForStock($parchinos, $requested_qty) {
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
        $bpitems_price = (float)$parchino['price'];
        
        $allocated_qty = min($available_qty, $remaining_qty);
        if ($allocated_qty > 0) {
            $allocated_price = $allocated_qty * $bpitems_price;
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

exit();
?>