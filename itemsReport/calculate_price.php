<?php
header('Content-Type: application/json');
session_start();
require_once('includes/config.php');

// Check authentication
if (!isset($_SESSION['UsersRealName'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$stockid = $_POST['stockid'] ?? '';
$requested_qty = $_POST['quantity'] ?? 0;

if (empty($stockid)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Stock ID required',
        'data' => [
            'total_price' => 0.00,
            'unit_price' => 0.00,
            'allocated_quantity' => 0,
            'requested_quantity' => (int)$requested_qty
        ]
    ]);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Same query as your first script
    $query = "
        SELECT stockmoves.stockid,
               systypes.typename,
               stockmoves.type,
               stockmoves.transno,
               stockmoves.trandate,
               stockmoves.debtorno,
               stockmoves.branchcode,
               stockmoves.qty,
               stockmoves.reference,
               stockmoves.price as stockmove_price,
               stockmoves.discountpercent,
               stockmoves.newqoh,
               stockmaster.decimalplaces,
               stockmoves.stkmoveno
        FROM stockmoves
        INNER JOIN systypes ON stockmoves.type = systypes.typeid
        INNER JOIN stockmaster ON stockmoves.stockid = stockmaster.stockid
        WHERE stockmoves.stockid = '" . mysqli_real_escape_string($conn, $stockid) . "'
          AND systypes.typename = 'IGP'
          AND stockmoves.reference LIKE '%From From Vendor:%'
          AND hidemovt = 0
        ORDER BY stkmoveno DESC
    ";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        echo json_encode([
            'success' => true,
            'message' => 'No records found for allocation',
            'data' => [
                'total_price' => 0.00,
                'unit_price' => 0.00,
                'allocated_quantity' => 0,
                'requested_quantity' => (int)$requested_qty
            ]
        ]);
        exit;
    }
    
    // Store all records in array to process them properly
    $records = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
    
    $remaining_qty = (int)$requested_qty;
    $total_weighted_price = 0;
    $total_allocated_qty = 0;
    $bpitems_found = false;
    $allocation_data = []; // Store allocation details for each record
    
    // First pass: Calculate how much to allocate from each record
    foreach ($records as $row) {
        $available_qty = (int)$row['qty'];
        $allocated_qty = 0;
        
        if ($remaining_qty > 0) {
            if ($available_qty >= $remaining_qty) {
                $allocated_qty = $remaining_qty;
                $remaining_qty = 0;
            } else {
                $allocated_qty = $available_qty;
                $remaining_qty -= $available_qty;
            }
        }
        
        $allocation_data[] = [
            'transno' => $row['transno'],
            'available_qty' => $available_qty,
            'allocated_qty' => $allocated_qty,
            'bpitems_price' => 0 // Will be populated in second pass
        ];
        
        if ($remaining_qty <= 0) {
            break;
        }
    }
    
    // Second pass: Get BPItems price for all allocated records
    foreach ($allocation_data as &$allocation) {
        if ($allocation['allocated_qty'] > 0) {
            $transno = $allocation['transno'];
            $bpitems_price = 0;
            
            // Get BPItems price
            $narrative_query = "SELECT igp.narrative FROM `igp` WHERE `dispatchid` = '$transno'";
            $narrative_result = mysqli_query($conn, $narrative_query);
            
            if ($narrative_result && mysqli_num_rows($narrative_result) > 0) {
                $narrative_row = mysqli_fetch_assoc($narrative_result);
                $narrative = $narrative_row['narrative'];
                $clean_narrative = trim(str_replace("Against ParchiNo: ", "", $narrative));
                
                if (!empty($clean_narrative)) {
                    $bpitems_query = "SELECT bpitems.price FROM bpitems 
                                     WHERE deleted_at IS NULL 
                                     AND parchino = '$clean_narrative' 
                                     AND bpitems.stockid = '$stockid'";
                    
                    $bpitems_result = mysqli_query($conn, $bpitems_query);
                    if ($bpitems_result && mysqli_num_rows($bpitems_result) > 0) {
                        $bpitems_row = mysqli_fetch_assoc($bpitems_result);
                        $bpitems_price = (float)$bpitems_row['price'];
                        $bpitems_found = true;
                    }
                }
            }
            
            $allocation['bpitems_price'] = $bpitems_price;
            $allocated_price = $allocation['allocated_qty'] * $bpitems_price;
            $total_weighted_price += $allocated_price;
            $total_allocated_qty += $allocation['allocated_qty'];
        }
    }
    
    // If no BPItems price found in the allocated records, try to find ANY BPItems price
    if (!$bpitems_found) {
        // Try to get the first available BPItems price from any record
        foreach ($records as $row) {
            $transno = $row['transno'];
            $narrative_query = "SELECT igp.narrative FROM `igp` WHERE `dispatchid` = '$transno'";
            $narrative_result = mysqli_query($conn, $narrative_query);
            
            if ($narrative_result && mysqli_num_rows($narrative_result) > 0) {
                $narrative_row = mysqli_fetch_assoc($narrative_result);
                $narrative = $narrative_row['narrative'];
                $clean_narrative = trim(str_replace("Against ParchiNo: ", "", $narrative));
                
                if (!empty($clean_narrative)) {
                    $bpitems_query = "SELECT bpitems.price FROM bpitems 
                                     WHERE deleted_at IS NULL 
                                     AND parchino = '$clean_narrative' 
                                     AND bpitems.stockid = '$stockid'";
                    
                    $bpitems_result = mysqli_query($conn, $bpitems_query);
                    if ($bpitems_result && mysqli_num_rows($bpitems_result) > 0) {
                        $bpitems_row = mysqli_fetch_assoc($bpitems_result);
                        $bpitems_price = (float)$bpitems_row['price'];
                        $bpitems_found = true;
                        break; // Found at least one price
                    }
                }
            }
        }
    }
    
    // If still no BPItems price found
    if (!$bpitems_found || $total_allocated_qty == 0) {
        echo json_encode([
            'success' => true,
            'message' => 'No BPItems price found',
            'data' => [
                'total_price' => 0.00,
                'unit_price' => 0.00,
                'allocated_quantity' => 0,
                'requested_quantity' => (int)$requested_qty
            ]
        ]);
        exit;
    }
    
    // Recalculate with found BPItems price if needed
    if ($total_allocated_qty == 0 && $requested_qty > 0) {
        // Use the found BPItems price for the full requested quantity
        $total_weighted_price = $requested_qty * $bpitems_price;
        $total_allocated_qty = $requested_qty;
    }
    
    $weighted_unit_price = ($total_allocated_qty > 0) ? $total_weighted_price / $total_allocated_qty : 0;
    
    echo json_encode([
        'success' => true,
        'message' => 'Price calculated successfully',
        'data' => [
            'total_price' => round($total_weighted_price, 2),
            'unit_price' => round($weighted_unit_price, 2),
            'allocated_quantity' => $total_allocated_qty,
            'requested_quantity' => (int)$requested_qty
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'data' => [
            'total_price' => 0.00,
            'unit_price' => 0.00,
            'allocated_quantity' => 0,
            'requested_quantity' => (int)$requested_qty
        ]
    ]);
}
?>