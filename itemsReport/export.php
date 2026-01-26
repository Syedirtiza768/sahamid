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

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// CSV headers - Updated to match your DataTable columns
fputcsv($output, [
    'Stock ID',
    'Brand', 
    'Category',
    'Condition',
    'Total Quantity',
    'Total Price',
    'Unit Price (Weighted Average)',
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
        // Main product query for this batch - EXACTLY like index.php
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
        
        // Get quantities for this batch - EXACTLY like index.php
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
        
        // ✅ CORRECTED: Get parchino data from igp_parchi table (not bpitems)
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
            
            // Add quantities - EXACTLY like index.php
            $totalQty = 0;
            foreach ($locations as $location) {
                $qty = isset($quantities[$stockid][$location]) ? intval($quantities[$stockid][$location]) : 0;
                $item['qty' . $location] = $qty;
                $totalQty += $qty;
            }
            $item['total_qty'] = $totalQty;
            
            // Calculate price using EXACT same function as index.php
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
    
    // ✅ Apply filter - EXACTLY like frontend
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

    // Helper function to match frontend's numberFormat()
    function numberFormatPHP($number) {
        if ($number === null || $number === '' || $number === 0) {
            return '0.00';
        }
        return number_format(floatval($number), 2, '.', ',');
    }

    // ✅ Output data - EXACTLY matching frontend display logic
    foreach ($filteredData as $itemData) {
        $row = $itemData['data'];
        $totalQty = $itemData['totalQty'];
        
        // FOR TOTAL PRICE COLUMN - exactly like frontend render function
        $totalPriceValue = 'PKR 0.00';
        $totalPriceNote = '';
        
        if ($totalQty > 0) {
            if ($row['total_bpitems_price'] > 0) {
                $totalPriceValue = 'PKR ' . numberFormatPHP($row['total_bpitems_price']);
                $totalPriceNote = 'for ' . ($row['total_quantity'] ?: $totalQty) . ' units';
            } else {
                $totalPriceValue = 'PKR 0.00';
                $totalPriceNote = 'No parchino data';
            }
        } else {
            $totalPriceValue = 'PKR 0.00';
            $totalPriceNote = 'Out of stock';
        }
        
        // FOR UNIT PRICE COLUMN - exactly like frontend render function
        $unitPriceValue = 'PKR 0.00';
        $unitPriceNote = '';
        
        if ($totalQty > 0) {
            if ($row['weighted_unit_price'] > 0) {
                $unitPriceValue = 'PKR ' . numberFormatPHP($row['weighted_unit_price']);
                $unitPriceNote = 'weighted average';
            } else {
                $unitPriceValue = 'PKR 0.00';
                $unitPriceNote = 'No parchino data';
            }
        } else {
            $unitPriceValue = 'PKR 0.00';
            $unitPriceNote = 'Out of stock';
        }
        
        // Combine values and notes (use | separator for CSV)
        $totalPriceDisplay = $totalPriceValue . ' | ' . $totalPriceNote;
        $unitPriceDisplay = $unitPriceValue . ' | ' . $unitPriceNote;

        fputcsv($output, [
            $row['stockid'],
            $row['manufacturers_name'],
            $row['categorydescription'],
            $row['abbreviation'],
            $totalQty,
            $totalPriceDisplay,
            $unitPriceDisplay,
            $row['materialcost'] > 0 ? 'PKR ' . numberFormatPHP($row['materialcost']) : 'PKR 0.00'
        ]);
    }

} catch (Exception $e) {
    // Handle error - output to CSV
    fputcsv($output, ['Error: ' . $e->getMessage()]);
    fputcsv($output, ['Debug: Check igp_parchi table for price data']);
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