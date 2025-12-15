<?php
// START output buffering at the VERY BEGINNING - no whitespace before this
ob_start();

// Set JSON header
header('Content-Type: application/json');

// Include required files
require_once('includes/session.inc');
require_once('includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['UsersRealName'])) {
    // Clean any output buffer first
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

    // Main product query
    $SQL = 'SELECT stockmaster.stockid, manufacturers_name, lastcost, materialcost,
            lastcostupdate, lastupdatedby, mnfCode, mnfpno, abbreviation, categorydescription
            FROM stockmaster 
            INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id 
            INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid 
            INNER JOIN itemcondition ON stockmaster.conditionID = itemcondition.conditionID
            GROUP BY stockmaster.stockid';

    $res = mysqli_query($db, $SQL);
    if (!$res) {
        throw new Exception('Database query failed: ' . mysqli_error($db));
    }

    // Get quantities from all locations
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
            mysqli_free_result($res_loc);
        }
    }

    // Combine data
    $response = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $stockid = $row['stockid'];
        $item = $row;
        
        // Add quantities for each location
        foreach ($locations as $location) {
            $item['qty' . $location] = $quantities[$location][$stockid] ?? 0;
        }
        
        $response[] = $item;
    }

    // Free result
    mysqli_free_result($res);

    // Get and clean the output buffer
    $buffer = ob_get_clean();
    
    // If there was any output in buffer, something is wrong
    if (!empty($buffer)) {
        // For debugging - you can log this
        error_log("Unexpected output in buffer: " . $buffer);
        
        // Clean response and send error
        echo json_encode([
            'status' => 'error',
            'error' => 'Server output error',
            'debug' => 'Unexpected output detected (check PHP error logs)',
            'data' => []
        ]);
    } else {
        // Send successful response
        echo json_encode([
            'status' => 'success',
            'data' => $response,
            'count' => count($response)
        ]);
    }

} catch (Exception $e) {
    // Clean buffer and send error
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// Ensure no extra output at the end
exit();
?>