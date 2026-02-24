<?php
// ✅ INCREASE MEMORY LIMIT - Match index.php
ini_set('memory_limit', '512M');
set_time_limit(300);

require_once('includes/session.inc');
require_once('includes/config.php');

if (!isset($_SESSION['UsersRealName'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$db = getDBConnection();

// Get the filter parameter with default value
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'non-zero';

// Get custom prices from URL parameter
$customPrices = [];
if (isset($_GET['custom_prices']) && !empty($_GET['custom_prices'])) {
    try {
        $customPricesJson = urldecode($_GET['custom_prices']);
        $customPricesJson = base64_decode($customPricesJson);
        $customPrices = json_decode($customPricesJson, true) ?: [];
    } catch (Exception $e) {
        // If error, just use empty array
        error_log('Error decoding custom prices: ' . $e->getMessage());
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

// Add custom indicator to filename if custom prices exist
if (!empty($customPrices)) {
    $filename = str_replace('.csv', '_with_custom_prices.csv', $filename);
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// CSV headers - Only raw values, no formatting
fputcsv($output, [
    'Stock ID',
    'Brand', 
    'Category',
    'Condition',
    'Total Quantity',
    'Total Price',
    'Unit Price (Weighted Average)',
    'Adjust Unit Price',      // This is the column users will edit
    'Qty × Unit Price',
    'List Price'
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
        
        // Get parchino data from igp_parchi table
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
                    'quantity' => floatval($row['quantity']),
                    'price' => floatval($row['price'])
                ];
            }
            mysqli_free_result($res_parchinos);
        }
        
        // Process each item in this batch
        $locations = ['HO', 'MT', 'SR', 'OS', 'VSR', 'WS'];
        
        foreach ($batchStockIds as $stockid) {
            if (!isset($batchRows[$stockid])) continue;
            
            $item = $batchRows[$stockid];
            
            // Add quantities
            $totalQty = 0;
            foreach ($locations as $location) {
                $qty = isset($quantities[$stockid][$location]) ? intval($quantities[$stockid][$location]) : 0;
                $item['qty' . $location] = $qty;
                $totalQty += $qty;
            }
            $item['total_qty'] = $totalQty;
            
            // Calculate price
            $priceData = calculatePriceForStock($parchinoData[$stockid] ?? [], $totalQty);
            $item['total_bpitems_price'] = $priceData['total_bpitems_price'];
            $item['weighted_unit_price'] = $priceData['weighted_unit_price'];
            $item['total_quantity'] = $priceData['total_quantity'];
            
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
        // Return as plain number without thousands separator
        return rtrim(rtrim(number_format(floatval($number), 2, '.', ''), '0'), '.');
    }

    // ✅ Output data - RAW VALUES ONLY, no PKR, no notes
    foreach ($filteredData as $itemData) {
        $row = $itemData['data'];
        $totalQty = $itemData['totalQty'];
        $stockId = $row['stockid'];
        
        // TOTAL PRICE - raw number only
        $totalPrice = 0;
        if ($totalQty > 0 && $row['total_bpitems_price'] > 0) {
            $totalPrice = $row['total_bpitems_price'];
        }
        
        // UNIT PRICE - raw number only
        $unitPrice = 0;
        if ($totalQty > 0 && $row['weighted_unit_price'] > 0) {
            $unitPrice = $row['weighted_unit_price'];
        }
        
        // ADJUST UNIT PRICE - use custom price if available, otherwise unit price
        $adjustUnitPrice = isset($customPrices[$stockId]) ? floatval($customPrices[$stockId]) : $unitPrice;
        
        // QTY × UNIT PRICE - raw calculation
        $qtyTimesPrice = $totalQty * $adjustUnitPrice;
        
        // LIST PRICE - raw number
        $listPrice = $row['materialcost'] > 0 ? $row['materialcost'] : 0;

        fputcsv($output, [
            $row['stockid'],
            $row['manufacturers_name'],
            $row['categorydescription'],
            $row['abbreviation'],
            $totalQty,
            rawNumber($totalPrice),
            rawNumber($unitPrice),
            rawNumber($adjustUnitPrice),      // This is the column users will edit
            rawNumber($qtyTimesPrice),
            rawNumber($listPrice)
        ]);
    }

} catch (Exception $e) {
    // Handle error - output to CSV
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}

fclose($output);

// ✅ EXACT SAME FUNCTION AS index.php
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