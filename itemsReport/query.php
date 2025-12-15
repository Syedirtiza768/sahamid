<?php
require_once('includes/config.php');

// Database connection establish karo
try {
    $conn = getDBConnection(); // config.php se connection function
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

$result = null;
$query = '';
$narrative_results = array();
$bpitems_data = array();
$allocated_records = array(); // Quantity allocate kiye gaye records store karega
$total_requested_qty = 0;
$total_allocated_qty = 0;
$total_weighted_price = 0;
$weighted_unit_price = 0;
$submitted_code = '';
$has_bpitems_price = false; // Track if ANY BPItems price was found

// Form submit handle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $submitted_code = mysqli_real_escape_string($conn, $_POST['code']);
    $code = $submitted_code;
    $requested_qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $total_requested_qty = $requested_qty;
    
    // Main Query generate karo (latest se oldest)
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
        WHERE stockmoves.stockid = '$code'
          AND systypes.typename = 'IGP'
          AND stockmoves.reference LIKE '%From From Vendor:%'
          AND hidemovt = 0
        ORDER BY stkmoveno DESC
    ";
    
    $result = mysqli_query($conn, $query);
    
    // Agar query error ho
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    
    // Store all records in array first for better processing
    $all_records = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $all_records[] = $row;
    }
    
    // If no records found
    if (empty($all_records)) {
        echo '<div class="no-result">';
        echo '<p><strong>No records found</strong> for code: <i>' . htmlspecialchars($submitted_code) . '</i></p>';
        echo '<p>Please check the code and try again.</p>';
        echo '</div>';
    } else {
        // First, get ALL BPItems prices for all records to use as fallback
        $all_bpitems_prices = array();
        foreach ($all_records as $record) {
            $transno = $record['transno'];
            $bpitems_price = 0;
            
            // Narrative fetch karo
            $narrative_query = "SELECT igp.narrative FROM `igp` WHERE `dispatchid` = '$transno'";
            $narrative_result = mysqli_query($conn, $narrative_query);
            
            if ($narrative_result && mysqli_num_rows($narrative_result) > 0) {
                $narrative_row = mysqli_fetch_assoc($narrative_result);
                $narrative = $narrative_row['narrative'];
                $clean_narrative = trim(str_replace("Against ParchiNo: ", "", $narrative));
                mysqli_free_result($narrative_result);
                
                // BPItems query
                if (!empty($clean_narrative)) {
                    $bpitems_query = "SELECT bpitems.price FROM bpitems 
                                     WHERE deleted_at IS NULL 
                                     AND parchino = '$clean_narrative' 
                                     AND bpitems.stockid = '$code'";
                    
                    $bpitems_result = mysqli_query($conn, $bpitems_query);
                    
                    if ($bpitems_result && mysqli_num_rows($bpitems_result) > 0) {
                        $bpitems_row = mysqli_fetch_assoc($bpitems_result);
                        $bpitems_price = (float)$bpitems_row['price'];
                        $has_bpitems_price = true;
                        mysqli_free_result($bpitems_result);
                    }
                }
            }
            $all_bpitems_prices[$transno] = $bpitems_price;
        }
        
        // Find the first valid BPItems price to use as fallback
        $fallback_bpitems_price = 0;
        foreach ($all_bpitems_prices as $price) {
            if ($price > 0) {
                $fallback_bpitems_price = $price;
                break;
            }
        }
        
        // Now handle quantity allocation logic
        if ($requested_qty > 0) {
            $remaining_qty = $requested_qty;
            
            // First pass: Determine allocation quantities
            foreach ($all_records as $row) {
                $available_qty = (int)$row['qty'];
                $transno = $row['transno'];
                $stockmove_unit_price = (float)$row['stockmove_price'];
                
                // Kitni quantity allocate karni hai
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
                
                // Get BPItems price for this record or use fallback
                $bpitems_price = $all_bpitems_prices[$transno] ?? $fallback_bpitems_price;
                
                // Get full BPItems info for display
                $bpitems_info = null;
                $clean_narrative = '';
                
                // Narrative fetch karo
                $narrative_query = "SELECT igp.narrative FROM `igp` WHERE `dispatchid` = '$transno'";
                $narrative_result = mysqli_query($conn, $narrative_query);
                
                if ($narrative_result && mysqli_num_rows($narrative_result) > 0) {
                    $narrative_row = mysqli_fetch_assoc($narrative_result);
                    $narrative = $narrative_row['narrative'];
                    $clean_narrative = trim(str_replace("Against ParchiNo: ", "", $narrative));
                    mysqli_free_result($narrative_result);
                    
                    // Full BPItems query for display
                    if (!empty($clean_narrative)) {
                        $bpitems_query = "SELECT bpitems.*, stockmaster.mnfpno, 
                                         manufacturers.manufacturers_name as bname, 
                                         stockmaster.description, stockmaster.mnfCode 
                                  FROM bpitems 
                                  LEFT OUTER JOIN stockmaster ON bpitems.stockid = stockmaster.stockid
                                  LEFT OUTER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
                                  WHERE deleted_at IS NULL 
                                  AND parchino = '$clean_narrative' 
                                  AND bpitems.stockid = '$code'";
                        
                        $bpitems_result = mysqli_query($conn, $bpitems_query);
                        
                        if ($bpitems_result && mysqli_num_rows($bpitems_result) > 0) {
                            $bpitems_row = mysqli_fetch_assoc($bpitems_result);
                            $bpitems_data[$transno] = $bpitems_row;
                            $bpitems_info = $bpitems_row;
                            mysqli_free_result($bpitems_result);
                        } else {
                            $bpitems_data[$transno] = null;
                        }
                    }
                }
                
                // Calculate allocated price
                $allocated_price = $allocated_qty * $bpitems_price;
                $total_weighted_price += $allocated_price;
                
                // Store in allocated records
                $row['allocated_qty'] = $allocated_qty;
                $row['original_qty'] = $available_qty;
                $row['allocated_price'] = $allocated_price;
                $row['unit_price'] = $stockmove_unit_price; // StockMove price for reference
                $row['bpitems_unit_price'] = $bpitems_price; // BPItems price
                $row['is_fallback_price'] = ($bpitems_price == $fallback_bpitems_price && ($all_bpitems_prices[$transno] ?? 0) == 0);
                
                if ($allocated_qty > 0) {
                    $allocated_records[] = $row;
                    $total_allocated_qty += $allocated_qty;
                }
                
                $narrative_results[$transno] = $clean_narrative;
                
                // Agar quantity poori allocate ho gayi to break
                if ($remaining_qty <= 0 && $allocated_qty > 0) {
                    break;
                }
            }
            
            // If no records were allocated but we have quantity requested, show all records
            if (empty($allocated_records) && $requested_qty > 0) {
                foreach ($all_records as $row) {
                    $transno = $row['transno'];
                    $available_qty = (int)$row['qty'];
                    $stockmove_unit_price = (float)$row['stockmove_price'];
                    $bpitems_price = $all_bpitems_prices[$transno] ?? $fallback_bpitems_price;
                    
                    $allocated_price = $available_qty * $bpitems_price;
                    $total_weighted_price += $allocated_price;
                    
                    $row['allocated_qty'] = $available_qty;
                    $row['original_qty'] = $available_qty;
                    $row['allocated_price'] = $allocated_price;
                    $row['unit_price'] = $stockmove_unit_price;
                    $row['bpitems_unit_price'] = $bpitems_price;
                    $row['is_fallback_price'] = ($bpitems_price == $fallback_bpitems_price && ($all_bpitems_prices[$transno] ?? 0) == 0);
                    
                    $allocated_records[] = $row;
                    $total_allocated_qty += $available_qty;
                    
                    // Narrative fetch for display
                    $narrative_query = "SELECT igp.narrative FROM `igp` WHERE `dispatchid` = '$transno'";
                    $narrative_result = mysqli_query($conn, $narrative_query);
                    if ($narrative_result && mysqli_num_rows($narrative_result) > 0) {
                        $narrative_row = mysqli_fetch_assoc($narrative_result);
                        $narrative = $narrative_row['narrative'];
                        $clean_narrative = trim(str_replace("Against ParchiNo: ", "", $narrative));
                        mysqli_free_result($narrative_result);
                        $narrative_results[$transno] = $clean_narrative;
                    }
                }
            }
            
            // ✅ Calculate weighted average unit price
            if ($total_allocated_qty > 0) {
                $weighted_unit_price = $total_weighted_price / $total_allocated_qty;
            }
            
            $result = $allocated_records;
        } else {
            // Agar quantity 0 hai ya nahi di gayi to sab records show karo
            foreach ($all_records as $row) {
                $transno = $row['transno'];
                $stockmove_unit_price = (float)$row['stockmove_price'];
                $available_qty = (int)$row['qty'];
                $bpitems_price = $all_bpitems_prices[$transno] ?? $fallback_bpitems_price;
                
                // Get full BPItems info for display
                $bpitems_info = null;
                $clean_narrative = '';
                
                // Narrative fetch
                $narrative_query = "SELECT igp.narrative FROM `igp` WHERE `dispatchid` = '$transno'";
                $narrative_result = mysqli_query($conn, $narrative_query);
                
                if ($narrative_result && mysqli_num_rows($narrative_result) > 0) {
                    $narrative_row = mysqli_fetch_assoc($narrative_result);
                    $narrative = $narrative_row['narrative'];
                    $clean_narrative = trim(str_replace("Against ParchiNo: ", "", $narrative));
                    mysqli_free_result($narrative_result);
                    
                    // Full BPItems query for display
                    if (!empty($clean_narrative)) {
                        $bpitems_query = "SELECT bpitems.*, stockmaster.mnfpno, 
                                         manufacturers.manufacturers_name as bname, 
                                         stockmaster.description, stockmaster.mnfCode 
                                  FROM bpitems 
                                  LEFT OUTER JOIN stockmaster ON bpitems.stockid = stockmaster.stockid
                                  LEFT OUTER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
                                  WHERE deleted_at IS NULL 
                                  AND parchino = '$clean_narrative' 
                                  AND bpitems.stockid = '$code'";
                        
                        $bpitems_result = mysqli_query($conn, $bpitems_query);
                        
                        if ($bpitems_result && mysqli_num_rows($bpitems_result) > 0) {
                            $bpitems_row = mysqli_fetch_assoc($bpitems_result);
                            $bpitems_data[$transno] = $bpitems_row;
                            $bpitems_info = $bpitems_row;
                            mysqli_free_result($bpitems_result);
                        } else {
                            $bpitems_data[$transno] = null;
                        }
                    }
                }
                
                // Calculate price
                $allocated_price = $available_qty * $bpitems_price;
                $total_weighted_price += $allocated_price;
                $total_allocated_qty += $available_qty;
                
                $narrative_results[$transno] = $clean_narrative;
                $row['allocated_qty'] = $available_qty;
                $row['original_qty'] = $available_qty;
                $row['allocated_price'] = $allocated_price;
                $row['unit_price'] = $stockmove_unit_price;
                $row['bpitems_unit_price'] = $bpitems_price;
                $row['is_fallback_price'] = ($bpitems_price == $fallback_bpitems_price && ($all_bpitems_prices[$transno] ?? 0) == 0);
                $allocated_records[] = $row;
            }
            
            // ✅ Calculate weighted average unit price
            if ($total_allocated_qty > 0) {
                $weighted_unit_price = $total_weighted_price / $total_allocated_qty;
            }
            
            $result = $allocated_records;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ur">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Query Form with Quantity Allocation & Price Calculation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            direction: ltr;
        }
        form {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: inline-block;
            width: 120px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            padding: 8px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 8px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #f4f4f4;
        }
        .query-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
            font-family: monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 13px;
        }
        .no-result {
            color: red;
            padding: 15px;
            border: 1px solid #ffcccc;
            background-color: #fff5f5;
        }
        .error {
            color: #d8000c;
            background-color: #ffbaba;
            padding: 10px;
            border-radius: 5px;
        }
        .info-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f8ff;
            border-left: 4px solid #2196F3;
        }
        .summary-box {
            margin-top: 20px;
            padding: 15px;
            background-color: #e7f3ff;
            border: 2px solid #007bff;
            border-radius: 5px;
        }
        .price-summary-box {
            margin-top: 20px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .price-calculation {
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin-top: 15px;
            border-radius: 5px;
        }
        .calculation-step {
            margin: 8px 0;
            font-family: monospace;
        }
        .result-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }
        .allocated-price {
            background-color: #d4edda;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        .allocated-badge {
            background-color: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
        }
        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
        }
        .loading-note {
            color: #666;
            font-style: italic;
        }
        .bpitems-price {
            background-color: #e7f3ff;
            font-weight: bold;
            color: #0056b3;
        }
        .calculation-note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 10px 0;
        }
        .quantity-highlight {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .fallback-price {
            background-color: #fff3cd;
            color: #856404;
            font-style: italic;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 5px;
        }
        .warning-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>Stock Movement Query with Quantity Allocation & BPItems Price Calculation</h2>

<div class="calculation-note">
    <strong>Note:</strong> Calculation uses <span style="color: #0056b3; font-weight: bold;">BPItems Price</span> (not StockMove Price) for multiplication.
    <?php if (!$has_bpitems_price): ?>
        <span style="color: #dc3545; font-weight: bold;">⚠ No BPItems price found in any record!</span>
    <?php endif; ?>
</div>

<form method="post">
    <div class="form-group">
        <label for="code">Product Code: </label>
        <input type="text" id="code" name="code" placeholder="e.g., 1031MCBA9F44140" required
               value="<?php echo isset($_POST['code']) ? htmlspecialchars($_POST['code']) : ''; ?>">
    </div>
    
    <div class="form-group">
        <label for="quantity">Required Quantity: </label>
        <input type="number" id="quantity" name="quantity" placeholder="e.g., 7" min="1"
               value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>">
        <span style="color: #666; margin-left: 10px;">(Optional - leave empty for all records)</span>
    </div>
    
    <div class="form-group">
        <input type="submit" value="Search & Calculate Price">
    </div>
</form>

<?php
// Show warning if using fallback price
if (!$has_bpitems_price && !empty($result)) {
    echo '<div class="warning-note">';
    echo '<strong>⚠ Warning:</strong> No BPItems price found in any record. Using 0.00 as default price for calculations.';
    echo '</div>';
} elseif ($has_bpitems_price && isset($fallback_bpitems_price) && $fallback_bpitems_price > 0) {
    echo '<div class="info-box">';
    echo '<strong>Info:</strong> Some records use fallback BPItems price: ' . number_format($fallback_bpitems_price, 2);
    echo '</div>';
}
?>

<?php
// ✅ PRICE SUMMARY BOX
if (!empty($submitted_code) && !empty($result)) {
    if ($total_requested_qty > 0 && is_array($result) && count($result) > 0) {
        echo '<div class="price-summary-box">';
        echo '<h3 style="color: white; margin-top: 0;">💰 BPItems Price Calculation</h3>';
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">';
        echo '<div><strong>Requested Quantity:</strong><br><span style="font-size: 24px;">' . $total_requested_qty . '</span> units</div>';
        echo '<div><strong>Allocated Quantity:</strong><br><span style="font-size: 24px;">' . $total_allocated_qty . '</span> units</div>';
        echo '<div><strong>Total BPItems Price:</strong><br><span style="font-size: 24px;">' . number_format($total_weighted_price, 2) . '</span></div>';
        echo '<div><strong>Weighted Unit Price:</strong><br><span style="font-size: 24px; color: #ffeb3b;">' . number_format($weighted_unit_price, 2) . '</span> per unit</div>';
        echo '</div>';
        
        if ($total_allocated_qty < $total_requested_qty) {
            echo '<div style="background-color: rgba(255,0,0,0.2); padding: 10px; border-radius: 5px; margin-top: 10px;">';
            echo '<strong>⚠️ Warning:</strong> Shortage of ' . ($total_requested_qty - $total_allocated_qty) . ' units';
            echo '</div>';
        }
        echo '</div>';
        
        // ✅ PRICE CALCULATION DETAILS - BPItems Price
        echo '<div class="price-calculation">';
        echo '<h4>📊 BPItems Price Calculation Breakdown:</h4>';
        
        $step_count = 1;
        foreach ($result as $record) {
            $bpitems_price = $record['bpitems_unit_price'] ?? 0;
            $is_fallback = $record['is_fallback_price'] ?? false;
            
            echo '<div class="calculation-step">';
            echo 'Step ' . $step_count . ': ';
            echo $record['allocated_qty'] . ' units × ' . number_format($bpitems_price, 2);
            if ($is_fallback) {
                echo ' <span class="fallback-price">(fallback)</span>';
            }
            echo ' = ';
            echo '<strong>' . number_format($record['allocated_price'], 2) . '</strong>';
            echo ' (Trans No: ' . $record['transno'] . ')';
            echo '</div>';
            $step_count++;
        }
        
        echo '<div class="calculation-step" style="font-weight: bold; border-top: 1px solid #ddd; padding-top: 10px; margin-top: 10px;">';
        echo 'Total: ' . $total_allocated_qty . ' units = ' . number_format($total_weighted_price, 2) . ' (using BPItems Price)';
        echo '</div>';
        
        if ($total_allocated_qty > 0) {
            echo '<div class="calculation-step" style="color: #28a745; font-weight: bold;">';
            echo 'Weighted Average Price: ' . number_format($total_weighted_price, 2) . ' ÷ ' . $total_allocated_qty . ' = ';
            echo '<span style="font-size: 18px;">' . number_format($weighted_unit_price, 2) . ' per unit</span>';
            echo '</div>';
        }
        echo '</div>';
    }
}
?>

<?php
// Summary box show karo agar quantity allocate hui hai
if ($total_requested_qty > 0 && is_array($result) && !empty($result)) {
    echo '<div class="summary-box">';
    echo '<h3>Quantity Allocation Summary</h3>';
    echo '<p><strong>Requested Quantity:</strong> ' . $total_requested_qty . '</p>';
    echo '<p><strong>Allocated Quantity:</strong> ' . $total_allocated_qty . '</p>';
    
    if ($total_allocated_qty < $total_requested_qty) {
        echo '<div class="warning-box">';
        echo '<p><strong>⚠️ Warning:</strong> Insufficient stock available!</p>';
        echo '<p>Requested: ' . $total_requested_qty . ' | Allocated: ' . $total_allocated_qty . ' | Shortage: ' . ($total_requested_qty - $total_allocated_qty) . '</p>';
        echo '</div>';
    } else {
        echo '<p style="color: #28a745;"><strong>✓ Full quantity allocated successfully!</strong></p>';
    }
    echo '</div>';
}
?>


<?php if (!empty($submitted_code) && !empty($result)): ?>
    <?php if (is_array($result) && count($result) > 0): ?>
        <?php $num_rows = count($result); ?>
        <h3>Allocated Records: <?php echo $num_rows; ?> IGP(s)</h3>
        <div class="info-box">
            <p><strong>Allocation Logic:</strong> Records are allocated from <strong>latest to oldest</strong> based on stkmoveno.</p>
            <p>Each IGP's quantity is used until the requested quantity is fulfilled.</p>
            <?php if (!$has_bpitems_price): ?>
                <p style="color: #dc3545;"><strong>⚠ No BPItems prices found:</strong> Using 0.00 for calculations</p>
            <?php endif; ?>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Trans No</th>
                    <th>Tran Date</th>
                    <th>Quantity Allocation</th>
                    <th>StockMove Price</th>
                    <th class="bpitems-price">BPItems Price</th>
                    <th>Allocated Price</th>
                    <th>Narrative (ParchiNo)</th>
                    <th>Price Diff</th>
                    <th>Original Qty</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $index => $row): 
                    $transno = $row['transno'];
                    $stockmove_price = $row['unit_price'];
                    $bpitems_price = $row['bpitems_unit_price'] ?? 0;
                    $allocated_price = $row['allocated_price'];
                    $is_fallback = $row['is_fallback_price'] ?? false;
                    
                    // Narrative hasil karo array se
                    $narrative = isset($narrative_results[$transno]) ? $narrative_results[$transno] : '';
                    $display_narrative = htmlspecialchars($narrative);
                    
                    // BPItems data
                    $bpitems_info = isset($bpitems_data[$transno]) ? $bpitems_data[$transno] : null;
                    
                    // Quantity information
                    $original_qty = $row['original_qty'];
                    $allocated_qty = $row['allocated_qty'];
                    
                    // BPItems price display
                    if (empty($narrative)) {
                        $display_narrative = '<span class="loading-note">No narrative</span>';
                        $bpitems_price_display = '<span class="loading-note">N/A</span>';
                        $price_diff = '<span class="loading-note">N/A</span>';
                    } elseif ($bpitems_info === null) {
                        $bpitems_price_display = number_format($bpitems_price, 2);
                        if ($is_fallback) {
                            $bpitems_price_display .= ' <span class="fallback-price">fallback</span>';
                        }
                        $price_diff = number_format($stockmove_price - $bpitems_price, 2);
                    } else {
                        $bpitems_price_display = number_format($bpitems_price, 2);
                        if ($is_fallback) {
                            $bpitems_price_display .= ' <span class="fallback-price">fallback</span>';
                        }
                        $price_diff = number_format($stockmove_price - $bpitems_price, 2);
                    }
                    
                    // Quantity display formatting
                    $quantity_display = $allocated_qty;
                    if ($allocated_qty < $original_qty) {
                        $quantity_display .= ' <span class="allocated-badge">Partially Used</span>';
                    }
                ?>
                    <tr class="<?php echo $total_requested_qty > 0 ? 'quantity-highlight' : ''; ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($transno); ?></td>
                        <td><?php echo htmlspecialchars($row['trandate']); ?></td>
                        <td><strong><?php echo $quantity_display; ?></strong> / <?php echo $original_qty; ?></td>
                        <td><?php echo number_format($stockmove_price, 2); ?></td>
                        <td class="bpitems-price"><?php echo $bpitems_price_display; ?></td>
                        <td class="allocated-price"><?php echo number_format($allocated_price, 2); ?></td>
                        <td><?php echo $display_narrative; ?></td>
                        <td><?php echo $price_diff; ?></td>
                        <td><?php echo $original_qty; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- ✅ FINAL RESULT BOX -->
        <?php if ($total_requested_qty > 0): ?>
        <div class="result-box">
            <h4>🎯 Final Result (using BPItems Price):</h4>
            <p>For <strong><?php echo $total_requested_qty; ?> units</strong> requested:</p>
            <p>• Total Allocated: <strong><?php echo $total_allocated_qty; ?> units</strong></p>
            <p>• Total BPItems Price: <strong><?php echo number_format($total_weighted_price, 2); ?></strong></p>
            <p>• Weighted Average Unit Price: <strong><?php echo number_format($weighted_unit_price, 2); ?> per unit</strong></p>
            <p style="color: #0056b3; font-weight: bold;">✓ Calculation based on BPItems Price, not StockMove Price</p>
            <?php if (!$has_bpitems_price): ?>
                <p style="color: #dc3545;"><strong>⚠ Note:</strong> No BPItems prices found, using 0.00</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Allocation details -->
        <div class="info-box">
            <h4>Allocation Details:</h4>
            <ul>
                <?php foreach ($result as $index => $row): ?>
                    <li>Record <?php echo $index + 1; ?>: 
                        Trans No <?php echo $row['transno']; ?> - 
                        Allocated <?php echo $row['allocated_qty']; ?> out of <?php echo $row['original_qty']; ?> units
                        <?php if ($row['is_fallback_price'] ?? false): ?>
                            <span class="fallback-price">(fallback price)</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
    <?php endif; ?>
<?php endif; ?>

</body>
</html>