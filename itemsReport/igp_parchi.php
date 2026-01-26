<?php
// Connect to database
require_once('includes/config.php');

try {
    $conn = getDBConnection(); // config.php se connection function
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

set_time_limit(300); // 5 minutes
ini_set('memory_limit', '512M');

// Check if table exists, create if not
$check_table = "SHOW TABLES LIKE 'igp_parchi'";
$table_exists = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_exists) == 0) {
    // Create table if it doesn't exist
    $create_table = "CREATE TABLE igp_parchi (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        stockid VARCHAR(50) NOT NULL,
        parchino VARCHAR(100) NOT NULL,
        quantity DECIMAL(10,2) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        total_value DECIMAL(12,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_stock_parchino (stockid, parchino),
        INDEX idx_stockid (stockid),
        INDEX idx_parchino (parchino)
    )";
    
    if (!mysqli_query($conn, $create_table)) {
        die("Error creating table: " . mysqli_error($conn));
    }
    echo "<p>Created igp_parchi table.</p>";
}

// Track progress
$processed_count = 0;
$skipped_count = 0;
$total_stockids = 0;

// Get all stockids from stockmaster
$stockids_query = "SELECT stockid, description FROM stockmaster ORDER BY stockid";
$stockids_result = mysqli_query($conn, $stockids_query);

if (!$stockids_result) {
    die("Error fetching stockids: " . mysqli_error($conn));
}

// Count total stockids
$count_query = "SELECT COUNT(*) as total FROM stockmaster";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total_stockids = $count_row['total'];

// Get already processed stockids to skip
$processed_stockids = array();
$check_processed = "SELECT DISTINCT stockid FROM igp_parchi";
$processed_result = mysqli_query($conn, $check_processed);

if ($processed_result) {
    while ($row = mysqli_fetch_assoc($processed_result)) {
        $processed_stockids[] = $row['stockid'];
    }
}

echo "<h2>Processing Stockids Data</h2>";
echo "<p>Total stockids in database: $total_stockids</p>";
echo "<p>Already processed: " . count($processed_stockids) . "</p>";
echo "<div id='progress'></div>";

$all_stock_data = array();
$current_index = 0;

// Process each stockid
while ($stock_row = mysqli_fetch_assoc($stockids_result)) {
    $current_index++;
    $stockid = mysqli_real_escape_string($conn, $stock_row['stockid']);
    $description = $stock_row['description'];
    
    // Skip if already processed
    if (in_array($stockid, $processed_stockids)) {
        $skipped_count++;
        echo "<script>document.getElementById('progress').innerHTML = 'Skipped already processed: $stockid ($current_index/$total_stockids)';</script>";
        flush();
        continue;
    }
    
    // Query to get BPItems data for this stockid
    $bpitems_query = "
        SELECT 
            bi.parchino,
            bi.quantity,
            bi.price,
            bi.created_at
        FROM bpitems bi
        WHERE bi.deleted_at IS NULL
          AND bi.stockid = '$stockid'
          AND bi.parchino IN (
            SELECT DISTINCT TRIM(REPLACE(i.narrative, 'Against ParchiNo: ', ''))
            FROM stockmoves sm
            INNER JOIN systypes st ON sm.type = st.typeid
            INNER JOIN igp i ON sm.transno = i.dispatchid
            WHERE sm.stockid = '$stockid'
              AND st.typename = 'IGP'
              AND sm.reference LIKE '%From From Vendor:%'
              AND sm.hidemovt = 0
              AND i.narrative IS NOT NULL
              AND i.narrative != ''
          )
        ORDER BY bi.created_at DESC
    ";
    
    $bpitems_result = mysqli_query($conn, $bpitems_query);
    
    if ($bpitems_result) {
        $stock_bpitems = array();
        $total_quantity = 0;
        $total_value = 0;
        
        while ($bp_row = mysqli_fetch_assoc($bpitems_result)) {
            $parchino = mysqli_real_escape_string($conn, $bp_row['parchino']);
            $quantity = (float)$bp_row['quantity'];
            $price = (float)$bp_row['price'];
            $item_value = $quantity * $price;
            
            // Check if this specific stockid+parchino already exists
            $check_exists = "SELECT id FROM igp_parchi WHERE stockid = '$stockid' AND parchino = '$parchino'";
            $exists_result = mysqli_query($conn, $check_exists);
            
            if (mysqli_num_rows($exists_result) == 0) {
                // Insert into igp_parchi table
                $insert_query = "INSERT INTO igp_parchi (stockid, parchino, quantity, price, total_value) 
                                VALUES ('$stockid', '$parchino', $quantity, $price, $item_value)";
                
                if (mysqli_query($conn, $insert_query)) {
                    $processed_count++;
                } else {
                    echo "<p>Error inserting $stockid - $parchino: " . mysqli_error($conn) . "</p>";
                }
            } else {
                $skipped_count++;
            }
            
            mysqli_free_result($exists_result);
            
            // Store for display
            $stock_bpitems[] = array(
                'parchino' => $bp_row['parchino'],
                'quantity' => $quantity,
                'price' => $price,
                'created_at' => $bp_row['created_at'],
                'value' => $item_value
            );
            
            $total_quantity += $quantity;
            $total_value += $item_value;
        }
        
        // Calculate average price
        $avg_price = ($total_quantity > 0) ? $total_value / $total_quantity : 0;
        
        // Store data for this stockid for display
        $all_stock_data[$stockid] = array(
            'description' => $description,
            'total_quantity' => $total_quantity,
            'total_value' => $total_value,
            'avg_price' => round($avg_price, 2),
            'bpitems_count' => count($stock_bpitems),
            'bpitems' => $stock_bpitems
        );
        
        mysqli_free_result($bpitems_result);
        
        // Update progress
        echo "<script>document.getElementById('progress').innerHTML = 'Processed: $stockid ($current_index/$total_stockids) - Items: " . count($stock_bpitems) . "';</script>";
        flush();
        
        // Optional: Add small delay to prevent server overload
        usleep(100000); // 0.1 second delay
    }
}

// Display summary
echo "<h3>Processing Complete</h3>";
echo "<p>Total stockids processed: $processed_count</p>";
echo "<p>Total records skipped (already exist): $skipped_count</p>";
echo "<p>Total unique stockids in igp_parchi: " . count($all_stock_data) . "</p>";

// Button to view all data
echo "<button onclick='toggleDataView()'>View All Data</button>";
echo "<div id='dataView' style='display:none;'>";

// Display results from igp_parchi table
$summary_query = "
    SELECT 
        stockid,
        COUNT(*) as parchino_count,
        SUM(quantity) as total_quantity,
        SUM(total_value) as total_value,
        CASE 
            WHEN SUM(quantity) > 0 
            THEN SUM(total_value) / SUM(quantity)
            ELSE 0 
        END as avg_price
    FROM igp_parchi 
    GROUP BY stockid 
    ORDER BY stockid
";

$summary_result = mysqli_query($conn, $summary_query);

echo "<h2>Summary from igp_parchi Table</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr>
        <th>Stock ID</th>
        <th>Parchino Count</th>
        <th>Total Quantity</th>
        <th>Total Value</th>
        <th>Avg Price</th>
        <th>Actions</th>
      </tr>";

if ($summary_result) {
    while ($summary_row = mysqli_fetch_assoc($summary_result)) {
        $stockid = htmlspecialchars($summary_row['stockid']);
        
        echo "<tr>";
        echo "<td>$stockid</td>";
        echo "<td>" . $summary_row['parchino_count'] . "</td>";
        echo "<td>" . number_format($summary_row['total_quantity'], 2) . "</td>";
        echo "<td>" . number_format($summary_row['total_value'], 2) . "</td>";
        echo "<td>" . number_format($summary_row['avg_price'], 2) . "</td>";
        echo "<td><button onclick='showDetails(\"$stockid\")'>View Details</button></td>";
        echo "</tr>";
        
        // Hidden details section
        echo "<tr id='details-$stockid' style='display:none;'>";
        echo "<td colspan='6'>";
        echo "<h3>Details for $stockid</h3>";
        
        // Get details for this stockid
        $details_query = "SELECT * FROM igp_parchi WHERE stockid = '$stockid' ORDER BY parchino";
        $details_result = mysqli_query($conn, $details_query);
        
        if (mysqli_num_rows($details_result) > 0) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr>
                    <th>Parchino</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total Value</th>
                    <th>Created At</th>
                  </tr>";
            
            while ($detail_row = mysqli_fetch_assoc($details_result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($detail_row['parchino']) . "</td>";
                echo "<td>" . number_format($detail_row['quantity'], 2) . "</td>";
                echo "<td>" . number_format($detail_row['price'], 2) . "</td>";
                echo "<td>" . number_format($detail_row['total_value'], 2) . "</td>";
                echo "<td>" . $detail_row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No details found.</p>";
        }
        echo "</td></tr>";
    }
}
echo "</table>";
echo "</div>";

// Export button
echo "<br><button onclick='exportData()'>Export to CSV</button>";
echo "<div id='exportResult'></div>";

// JavaScript functions
echo "
<script>
function toggleDataView() {
    var element = document.getElementById('dataView');
    if (element.style.display === 'none') {
        element.style.display = 'block';
    } else {
        element.style.display = 'none';
    }
}

function showDetails(stockid) {
    var element = document.getElementById('details-' + stockid);
    if (element.style.display === 'none') {
        element.style.display = 'table-row';
    } else {
        element.style.display = 'none';
    }
}

function exportData() {
    window.location.href = 'export_igp_parchi.php';
}
</script>
";

mysqli_free_result($stockids_result);
mysqli_close($conn);
?>

<?php
// Create export script (export_igp_parchi.php)
$export_script = '<?php
require_once("includes/config.php");
$conn = getDBConnection();

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=igp_parchi_" . date("Y-m-d") . ".csv");

$output = fopen("php://output", "w");
fputcsv($output, array("Stock ID", "Parchino", "Quantity", "Price", "Total Value", "Created At"));

$query = "SELECT stockid, parchino, quantity, price, total_value, created_at FROM igp_parchi ORDER BY stockid, parchino";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
mysqli_close($conn);
?>';

// Save export script if not exists
if (!file_exists('export_igp_parchi.php')) {
    file_put_contents('export_igp_parchi.php', $export_script);
}
?>