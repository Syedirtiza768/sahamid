<?php
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

// CSV headers - Updated to match your current columns
fputcsv($output, [
    'Stock ID',
    'Brand', 
    'Category',
    'Condition',
    'Qty',
    'Total Price',
    'Unit Price',
    'List Price'
]);

try {
    // Main query
    $SQL = 'SELECT stockmaster.stockid, manufacturers_name, lastcost, materialcost,
            lastcostupdate, lastupdatedby, mnfCode, mnfpno, abbreviation, categorydescription
            FROM stockmaster 
            INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id 
            INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid 
            INNER JOIN itemcondition ON stockmaster.conditionID = itemcondition.conditionID
            GROUP BY stockmaster.stockid';

    $res = mysqli_query($db, $SQL);

    if (!$res) {
        throw new Exception('Query failed: ' . mysqli_error($db));
    }

    // Get quantities
    $locations = ['HO', 'MT', 'SR', 'OS', 'VSR', 'WS'];
    $quantities = [];

    foreach ($locations as $location) {
        $quantities[$location] = [];
        $SQL_loc = "SELECT stockid, quantity FROM locstock WHERE loccode='$location'";
        $res_loc = mysqli_query($db, $SQL_loc);
        
        if ($res_loc) {
            while ($row = mysqli_fetch_assoc($res_loc)) {
                $quantities[$location][$row['stockid']] = $row['quantity'];
            }
        }
    }

    // Collect all data first for filtering
    $allData = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $stockid = $row['stockid'];
        
        // Calculate total quantity
        $totalQty = (isset($quantities['HO'][$stockid]) ? $quantities['HO'][$stockid] : 0) +
                   (isset($quantities['MT'][$stockid]) ? $quantities['MT'][$stockid] : 0) +
                   (isset($quantities['SR'][$stockid]) ? $quantities['SR'][$stockid] : 0) +
                   (isset($quantities['OS'][$stockid]) ? $quantities['OS'][$stockid] : 0) +
                   (isset($quantities['VSR'][$stockid]) ? $quantities['VSR'][$stockid] : 0) +
                   (isset($quantities['WS'][$stockid]) ? $quantities['WS'][$stockid] : 0);

        $allData[] = [
            'data' => $row,
            'totalQty' => $totalQty
        ];
    }

    // Apply filter
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

    // Output filtered data
    foreach ($filteredData as $item) {
        $row = $item['data'];
        $totalQty = $item['totalQty'];
        
        fputcsv($output, [
            $row['stockid'],
            $row['manufacturers_name'],
            $row['categorydescription'],
            $row['abbreviation'],
            $totalQty,
            $row['materialcost'] ? 'PKR ' . number_format($row['materialcost'], 2) : 'PKR 0.00',
            '-', // Unit Price (placeholder)
            '-'  // List Price (placeholder)
        ]);
    }

    // Free result
    mysqli_free_result($res);

} catch (Exception $e) {
    // Handle error - output to CSV
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}

fclose($output);
?>