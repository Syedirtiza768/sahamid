<?php
require_once('includes/config.php');

// Database connection establish karo
try {
    $conn = getDBConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

$submitted_code = '';
$requested_qty = 0;
$result = null;
$allocated_records = array();
$total_requested_qty = 0;
$total_allocated_qty = 0;
$total_weighted_price = 0;
$weighted_unit_price = 0;

// Form submit handle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $submitted_code = mysqli_real_escape_string($conn, $_POST['code']);
    $requested_qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $total_requested_qty = $requested_qty;

    // ✅ SIMPLIFIED QUERY - Now from igp_parchi table only
    $query = "
        SELECT 
            stockid,
            parchino,
            quantity as available_qty,
            price as bpitems_price,
            total_value,
            pdate
        FROM igp_parchi 
        WHERE stockid = '$submitted_code'
        ORDER BY pdate DESC, parchino DESC
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }

    // Store all records in array
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
        // Now handle quantity allocation logic
        if ($requested_qty > 0) {
            $remaining_qty = $requested_qty;

            // Quantity allocation from latest to oldest
            foreach ($all_records as $row) {
                $available_qty = (int)$row['available_qty'];
                $bpitems_price = (float)$row['bpitems_price'];

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

                // Calculate allocated price
                $allocated_price = $allocated_qty * $bpitems_price;
                $total_weighted_price += $allocated_price;

                // Store in allocated records
                $row['allocated_qty'] = $allocated_qty;
                $row['allocated_price'] = $allocated_price;

                if ($allocated_qty > 0) {
                    $allocated_records[] = $row;
                    $total_allocated_qty += $allocated_qty;
                }

                // Agar quantity poori allocate ho gayi to break
                if ($remaining_qty <= 0 && $allocated_qty > 0) {
                    break;
                }
            }

            // If no records were allocated but we have quantity requested, show all records
            if (empty($allocated_records) && $requested_qty > 0) {
                foreach ($all_records as $row) {
                    $available_qty = (int)$row['available_qty'];
                    $bpitems_price = (float)$row['bpitems_price'];

                    $allocated_price = $available_qty * $bpitems_price;
                    $total_weighted_price += $allocated_price;

                    $row['allocated_qty'] = $available_qty;
                    $row['allocated_price'] = $allocated_price;

                    $allocated_records[] = $row;
                    $total_allocated_qty += $available_qty;
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
                $available_qty = (int)$row['available_qty'];
                $bpitems_price = (float)$row['bpitems_price'];

                // Calculate price
                $allocated_price = $available_qty * $bpitems_price;
                $total_weighted_price += $allocated_price;
                $total_allocated_qty += $available_qty;

                $row['allocated_qty'] = $available_qty;
                $row['allocated_price'] = $allocated_price;
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Query - Optimized with igp_parchi Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        form {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="number"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="submit"] {
            padding: 10px 25px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .no-result {
            color: #721c24;
            background-color: #f8d7da;
            padding: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            margin: 20px 0;
        }

        .price-summary-box {
            margin: 20px 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .summary-box {
            margin: 20px 0;
            padding: 20px;
            background-color: #e7f3ff;
            border: 2px solid #007bff;
            border-radius: 8px;
        }

        .info-box {
            margin: 15px 0;
            padding: 15px;
            background-color: #f0f8ff;
            border-left: 4px solid #2196F3;
            border-radius: 5px;
        }

        .result-box {
            background-color: #fff3cd;
            border: 2px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: bold;
        }

        .allocated-badge {
            background-color: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 12px;
            margin-left: 5px;
        }

        .warning-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }

        .efficiency-note {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 14px;
        }

        .stat-box {
            display: inline-block;
            padding: 15px;
            margin: 10px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            min-width: 200px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }

        .query-speed {
            font-size: 12px;
            color: #28a745;
            margin-top: 5px;
        }
    </style>
</head>

<body>

    <h2>📊 Stock Query - Optimized with Pre-calculated Data</h2>

    <div class="efficiency-note">
        ✅ <strong>Optimized Query:</strong> Now fetching from pre-calculated <code>igp_parchi</code> table for instant results!
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

    <?php if (!empty($submitted_code) && !empty($result)): ?>
        <?php
        // Calculate query speed
        $start_time = microtime(true);
        $num_records = count($result);
        $end_time = microtime(true);
        $query_time = round(($end_time - $start_time) * 1000, 2); // in milliseconds
        ?>

        <div class="info-box">
            <h3>⚡ Query Performance</h3>
            <div class="stat-box">
                <div>Records Found</div>
                <div class="stat-value"><?php echo $num_records; ?></div>
                <div class="query-speed">Query time: <?php echo $query_time; ?>ms</div>
            </div>
            <div class="stat-box">
                <div>Source Table</div>
                <div class="stat-value">igp_parchi</div>
                <div class="query-speed">Pre-calculated data</div>
            </div>
        </div>

        <?php if ($total_requested_qty > 0): ?>
            <!-- ✅ PRICE SUMMARY BOX -->
            <div class="price-summary-box">
                <h3 style="color: white; margin-top: 0;">💰 Price Calculation Summary</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div><strong>Requested Quantity:</strong><br><span style="font-size: 24px;"><?php echo $total_requested_qty; ?></span> units</div>
                    <div><strong>Allocated Quantity:</strong><br><span style="font-size: 24px;"><?php echo $total_allocated_qty; ?></span> units</div>
                    <div><strong>Total Value:</strong><br><span style="font-size: 24px;"><?php echo number_format($total_weighted_price, 2); ?></span></div>
                    <div><strong>Unit Price:</strong><br><span style="font-size: 24px; color: #ffeb3b;"><?php echo number_format($weighted_unit_price, 2); ?></span></div>
                </div>

                <?php if ($total_allocated_qty < $total_requested_qty): ?>
                    <div style="background-color: rgba(255,0,0,0.2); padding: 10px; border-radius: 5px; margin-top: 10px;">
                        <strong>⚠️ Shortage:</strong> <?php echo ($total_requested_qty - $total_allocated_qty); ?> units
                    </div>
                <?php endif; ?>
            </div>

            <!-- ✅ ALLOCATION SUMMARY -->
            <div class="summary-box">
                <h3>📋 Allocation Summary</h3>
                <p><strong>Requested:</strong> <?php echo $total_requested_qty; ?> units</p>
                <p><strong>Allocated:</strong> <?php echo $total_allocated_qty; ?> units</p>
                <p><strong>Coverage:</strong> <?php echo round(($total_allocated_qty / $total_requested_qty) * 100, 2); ?>%</p>

                <?php if ($total_allocated_qty < $total_requested_qty): ?>
                    <div class="warning-box">
                        <p><strong>⚠️ Insufficient Stock</strong></p>
                        <p>Shortage: <?php echo ($total_requested_qty - $total_allocated_qty); ?> units</p>
                    </div>
                <?php else: ?>
                    <p style="color: #28a745;"><strong>✓ Full allocation successful!</strong></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- ✅ RESULTS TABLE -->
        <h3>📝 Available Records (<?php echo $num_records; ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Parchino</th>
                    <th>Purchase Date</th>
                    <th>Available Qty</th>
                    <?php if ($total_requested_qty > 0): ?>
                        <th>Allocated Qty</th>
                    <?php endif; ?>
                    <th>Unit Price</th>
                    <?php if ($total_requested_qty > 0): ?>
                        <th>Allocated Value</th>
                    <?php else: ?>
                        <th>Total Value</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $index => $row):
                    $allocated_qty = $row['allocated_qty'] ?? $row['available_qty'];
                    $display_qty = ($total_requested_qty > 0) ? $allocated_qty : $row['available_qty'];
                    $is_partial = ($total_requested_qty > 0 && $allocated_qty < $row['available_qty']);
                ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['parchino']); ?></strong></td>
                        <td><?php
                            // Just extract the date part (first 10 characters)
                            echo substr($row['pdate'], 0, 10);
                            ?></td>
                        <td>
                            <?php echo $row['available_qty']; ?>
                            <?php if ($is_partial): ?>
                                <span class="allocated-badge">Partial</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($total_requested_qty > 0): ?>
                            <td><strong><?php echo $allocated_qty; ?></strong></td>
                        <?php endif; ?>
                        <td><?php echo number_format($row['bpitems_price'], 2); ?></td>
                        <td>
                            <?php if ($total_requested_qty > 0): ?>
                                <?php echo number_format($row['allocated_price'], 2); ?>
                            <?php else: ?>
                                <?php echo number_format($row['total_value'], 2); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ✅ FINAL RESULT -->
        <?php if ($total_requested_qty > 0): ?>
            <div class="result-box">
                <h3>🎯 Final Calculation</h3>
                <p>For <strong><?php echo $total_requested_qty; ?> units</strong> of <strong><?php echo htmlspecialchars($submitted_code); ?></strong>:</p>
                <p>• Total Value: <strong>₹<?php echo number_format($total_weighted_price, 2); ?></strong></p>
                <p>• Weighted Average Price: <strong>₹<?php echo number_format($weighted_unit_price, 2); ?> per unit</strong></p>
                <p>• Allocation Efficiency: <strong><?php echo round(($total_allocated_qty / $total_requested_qty) * 100, 2); ?>%</strong></p>
                <p style="color: #28a745; margin-top: 10px;">✅ Calculated from pre-processed igp_parchi data</p>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</body>

</html>