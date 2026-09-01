<?php
// ✅ INCREASE MEMORY LIMIT - Match index.php
ini_set('memory_limit', '512M');
set_time_limit(300);

require_once('includes/session.inc');
require_once('includes/config.php');
require_once(__DIR__ . '/report_helpers.php');

if (!isset($_SESSION['UsersRealName'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$db = getDBConnection();

// Get the filter parameter with default value
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'non-zero';

// Get custom data from URL parameter (prices and factors)
$customPrices = [];
$landingFactors = [];
if (isset($_GET['custom_data']) && !empty($_GET['custom_data'])) {
    try {
        $customDataJson = urldecode($_GET['custom_data']);
        $customDataJson = base64_decode($customDataJson);
        $customData = json_decode($customDataJson, true) ?: [];
        $customPrices = $customData['prices'] ?? [];
        $landingFactors = $customData['factors'] ?? [];
    } catch (Exception $e) {
        error_log('Error decoding custom data: ' . $e->getMessage());
    }
}

// Set filename based on filter
switch($filter) {
    case 'non-zero':
        $filename = "In_Stock_Price_List_" . date('Y-m-d') . ".csv";
        break;
    case 'zero':
        $filename = "Out_of_Stock_Price_List_" . date('Y-m-d') . ".csv";
        break;
    case 'both':
    default:
        $filename = "Complete_Price_List_" . date('Y-m-d') . ".csv";
        break;
}

// Add custom indicator to filename if custom data exists
if (!empty($customPrices) || !empty($landingFactors)) {
    $filename = str_replace('.csv', '_with_customizations.csv', $filename);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// CSV headers - Updated with ALL columns matching the table (including Stock Status, excluding Last Transaction Date)
fputcsv($output, [
    'Stock ID',
    'Brand', 
    'Category',
    'Model #',
    'Part #',
    'Total Quantity',
    'Total Price',
    'Unit Price (Weighted Average)',
    'Adjust Unit Price',
    'Landing Factor',
    'Adjusted Price After Multiplication',
    'Qty × Adjusted Price',
    'List Price',
    'Stock Status',
    'Cost Coverage',
    'Price Status',
    'Price Source',
    'Latest Outward Date'
]);

try {
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    // Process in batches to match index.php
    $batchSize = 1000;
    $allData = [];
    $processedCount = 0;
    
    // Get total count first
    $countResult = mysqli_query($db, "SELECT COUNT(*) as total FROM stockmaster");
    $totalCount = mysqli_fetch_assoc($countResult)['total'];
    
    // Process in batches
    for ($offset = 0; $offset < $totalCount; $offset += $batchSize) {
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

        // Collect stock IDs for this batch
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
        
        // Get quantities for this batch
        $quantities = [];
        $totalQuantities = [];
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
                if (!isset($totalQuantities[$row_qty['stockid']])) {
                    $totalQuantities[$row_qty['stockid']] = 0;
                }
                $totalQuantities[$row_qty['stockid']] += (float)$row_qty['quantity'];
            }
            mysqli_free_result($res_qty);
        }
        
        // Get cost-lot data from igp_parchi. Activity dates come from stockmoves.
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
                    'quantity' => floatval($row['quantity']),
                    'price' => floatval($row['price']),
                    'adjust_unit_price' => floatval($row['adjust_unit_price'] ?? 0),
                    'landing_factor' => floatval($row['landing_factor'] ?? 1)
                ];
            }
            mysqli_free_result($res_parchinos);
        }

        $latestOutwardDates = getReportLatestOutwardDates($db, $stockIdsStr);
        $fallbackPrices = getReportFallbackPrices($db, $stockIdsStr);
        
        // Process each item in this batch
        $locations = ['HO', 'MT', 'SR', 'OS', 'VSR', 'WS'];
        
        foreach ($batchStockIds as $stockid) {
            if (!isset($batchRows[$stockid])) continue;
            
            $item = $batchRows[$stockid];
            
            // Add quantities
            $totalQty = isset($totalQuantities[$stockid]) ? $totalQuantities[$stockid] : 0;
            foreach ($locations as $location) {
                $qty = isset($quantities[$stockid][$location]) ? (float)$quantities[$stockid][$location] : 0;
                $item['qty' . $location] = $qty;
            }
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
            
            // Get the latest adjust_unit_price and landing_factor from the
            // most recent cost lot for the editable columns.
            if (!empty($parchinoData[$stockid])) {
                $latestParchino = $parchinoData[$stockid][0];
                $item['db_adjust_unit_price'] = $latestParchino['adjust_unit_price'] ?? 0;
                $item['db_landing_factor'] = $latestParchino['landing_factor'] ?? 1;
            } else {
                $item['db_adjust_unit_price'] = 0;
                $item['db_landing_factor'] = 1;
            }
            $item['latest_outward_date'] = $latestOutwardDates[$stockid] ?? null;
            $item['latest_trandate'] = $item['latest_outward_date'];
            
            $allData[] = [
                'data' => $item,
                'totalQty' => $totalQty
            ];
            $processedCount++;
        }
        
        mysqli_free_result($res);
    }
    
    // ✅ Apply filter
    $filteredData = [];
    switch($filter) {
        case 'non-zero':
            $filteredData = array_filter($allData, function($item) {
                return $item['totalQty'] > 0;
            });
            break;
        case 'zero':
            $filteredData = array_filter($allData, function($item) {
                return $item['totalQty'] == 0;
            });
            break;
        case 'both':
        default:
            $filteredData = $allData;
            break;
    }

    // Helper function for raw numbers (no formatting)
    function rawNumber($number) {
        if ($number === null || $number === '' || $number === 0) {
            return '0';
        }
        return rtrim(rtrim(number_format(floatval($number), 2, '.', ''), '0'), '.');
    }

    // Helper function to get stock status (same as in JavaScript)
    function getStockStatusForExport($dateString) {
        if (!$dateString) {
            return 'EXTREMELY DEAD';
        }
        
        $transactionTimestamp = strtotime($dateString);
        $diffDays = $transactionTimestamp === false
            ? 99999
            : max(0, (int)floor((time() - $transactionTimestamp) / 86400));
        
        if ($diffDays <= 180) {
            return 'FAST MOVING';
        } else if ($diffDays <= 360) {
            return 'SLOW MOVING';
        } else if ($diffDays > 360 && $diffDays <= 1000) {
            return 'DEAD STOCK';
        } else {
            return 'EXTREMELY DEAD';
        }
    }

    // ✅ Output data with ALL columns matching the table
    foreach ($filteredData as $itemData) {
        $row = $itemData['data'];
        $totalQty = $itemData['totalQty'];
        $stockId = $row['stockid'];
        
        // Get values from database or custom overrides
        $unitPrice = $totalQty > 0 ? floatval($row['weighted_unit_price']) : 0;
        
        // Use custom price if available, otherwise use database adjust_unit_price
        $hasCustomPrice = isset($customPrices[$stockId]);
        $adjustUnitPrice = $hasCustomPrice ? floatval($customPrices[$stockId]) :
                          (isset($row['db_adjust_unit_price']) ? floatval($row['db_adjust_unit_price']) : 0);
        
        // Use custom factor if available, otherwise use database landing_factor
        $hasCustomFactor = isset($landingFactors[$stockId]);
        $landingFactor = $hasCustomFactor ? floatval($landingFactors[$stockId]) :
                        (isset($row['db_landing_factor']) ? floatval($row['db_landing_factor']) : 1);
        
        // Calculate effective price (unit price if >0, otherwise adjust price)
        $effectivePrice = $unitPrice > 0 ? $unitPrice : $adjustUnitPrice;
        
        // Total Price from priced cost lots (or the explicit BP item fallback).
        $totalPrice = $totalQty > 0 ? floatval($row['total_bpitems_price']) : 0;

        // Calculate adjusted price after multiplication. The server weighted
        // price already includes each lot's landing factor, so use the
        // authoritative total unless a user supplied an override.
        $adjustedPrice = ($hasCustomPrice || $hasCustomFactor)
            ? $effectivePrice * $landingFactor
            : $unitPrice;
        
        // Calculate Qty × Adjusted Price
        $qtyTimesAdjustedPrice = ($hasCustomPrice || $hasCustomFactor)
            ? $totalQty * $adjustedPrice
            : $totalPrice;
        
        // List Price
        $listPrice = floatval($row['materialcost'] ?? 0);
        
        // Activity status is based on the latest outward movement.
        $stockStatus = getStockStatusForExport($row['latest_trandate'] ?? null);

        fputcsv($output, [
            $row['stockid'],
            $row['manufacturers_name'],
            $row['categorydescription'],
            !empty($row['mnfCode']) ? $row['mnfCode'] : '',
            !empty($row['mnfpno']) ? $row['mnfpno'] : '',
            $totalQty,
            rawNumber($totalPrice),
            rawNumber($unitPrice),
            rawNumber($adjustUnitPrice),
            rawNumber($landingFactor),
            rawNumber($adjustedPrice),
            rawNumber($qtyTimesAdjustedPrice),
            rawNumber($listPrice),
            $stockStatus,
            rawNumber($row['price_coverage_percent'] ?? 0) . '%',
            $row['price_status'] ?? 'MISSING_COST',
            $row['price_source'] ?? 'NONE',
            $row['latest_outward_date'] ?? ''
        ]);
    }

} catch (Exception $e) {
    // Handle error - output to CSV
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}

fclose($output);

exit();
?>
