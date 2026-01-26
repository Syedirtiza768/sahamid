<?php
// Turn off all error reporting to prevent output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering immediately
ob_start();

$PathPrefix = "../../../../";

// Include files with error handling
try {
    if (!@include("../../../../includes/session.inc")) {
        throw new Exception("Failed to include session.inc");
    }

    if (!@include('../../../../includes/SQL_CommonFunctions.inc')) {
        throw new Exception("Failed to include SQL_CommonFunctions.inc");
    }
} catch (Exception $e) {
    // Send clean error as JSON
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['error' => 'File inclusion failed', 'message' => $e->getMessage()]);
    exit();
}

// Function to capture any output and return clean JSON
function sendCleanJson($data)
{
    // Get any output that was buffered
    $ob_contents = ob_get_contents();

    // If there's any unexpected output, log it
    if (!empty($ob_contents) && trim($ob_contents) !== '') {
        error_log("Unexpected output detected: " . substr($ob_contents, 0, 500));
    }

    // Clean all output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    // Send JSON
    header('Content-Type: application/json; charset=utf-8');

    // Encode with error checking
    $json = json_encode($data);
    if ($json === false) {
        // JSON encoding failed
        $json = json_encode([
            'error' => 'JSON encoding failed',
            'json_error' => json_last_error_msg(),
            'data_sample' => substr(print_r($data, true), 0, 500)
        ]);
    }

    echo $json;
    exit();
}

// Check permission
if (!function_exists('userHasPermission') || !userHasPermission($db, "list_inward_parchi")) {
    sendCleanJson([]);
}

try {
    // Prepare parameters
    $useFilters = isset($_GET['filters']) && $_GET['filters'] === 'yes';
    $hasSessionFilter = isset($_SESSION['filter']);

    // Build query efficiently
    $queryParts = [
        'select' => "SELECT DISTINCT 
            bazar_parchi.*,
            bazar_parchi.settled as settled2,
            supptrans.id as traid,
            www_users.realname,
            suppliers.suppname as name",
        'from' => "FROM bazar_parchi
            INNER JOIN www_users ON bazar_parchi.user_id = www_users.userid
            LEFT JOIN supptrans ON (bazar_parchi.transno = supptrans.transno AND supptrans.type = 601)
            LEFT JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid",
        'where' => "WHERE bazar_parchi.discarded = 0
            AND (supptrans.type = 601 OR supptrans.type IS NULL)
            AND bazar_parchi.type = 601",
    ];

    // Add conditions based on filters
    $conditions = [];
    $bindParams = [];
    $bindTypes = "";

    if ($useFilters) {
        // Date filters
        if (!empty($_GET['from'])) {
            $conditions[] = "bazar_parchi.created_at >= ?";
            $bindParams[] = $_GET['from'];
            $bindTypes .= "s";
        }

        if (!empty($_GET['to'])) {
            $conditions[] = "bazar_parchi.created_at <= ?";
            $bindParams[] = $_GET['to'] . " 23:59:59";
            $bindTypes .= "s";
        }

        // State filters
        if (!empty($_GET['state'])) {
            switch ($_GET['state']) {
                case 'saved':
                    $conditions[] = "bazar_parchi.inprogress = 0";
                    $conditions[] = "(supptrans.settled = 0 OR supptrans.settled IS NULL)";
                    break;
                case 'settled':
                    $conditions[] = "supptrans.settled = 1";
                    break;
                case 'inprogress':
                    $conditions[] = "bazar_parchi.inprogress = 1";
                    break;
            }
        }

        // Item filter
        if (!empty($_GET['item'])) {
            $queryParts['from'] .= " INNER JOIN bpitems bi ON bazar_parchi.parchino = bi.parchino";
            $conditions[] = "bi.stockid LIKE CONCAT('%', ?, '%')";
            $bindParams[] = $_GET['item'];
            $bindTypes .= "s";
        }

        // Brand filter
        if (!empty($_GET['brand']) && $_GET['brand'] !== 'All') {
            $conditions[] = "EXISTS (
                SELECT 1 FROM bpitems bi2 
                INNER JOIN stockmaster sm ON bi2.stockid = sm.stockid 
                WHERE bi2.parchino = bazar_parchi.parchino 
                AND sm.brand = ?
            )";
            $bindParams[] = $_GET['brand'];
            $bindTypes .= "s";
        }
    } elseif ($hasSessionFilter) {
        // Session-based filters
        switch ($_SESSION['filter']) {
            case 'none':
                $conditions[] = "bazar_parchi.svid = ?";
                $bindParams[] = $_SESSION['svid'] ?? '';
                $bindTypes .= "s";
                break;
            case 'saved':
                $conditions[] = "bazar_parchi.inprogress = 0";
                $conditions[] = "bazar_parchi.svid = ?";
                $bindParams[] = $_SESSION['svid'] ?? '';
                $bindTypes .= "s";
                break;
            case 'inprogress':
                $conditions[] = "bazar_parchi.inprogress = 1";
                $conditions[] = "bazar_parchi.svid = ?";
                $bindParams[] = $_SESSION['svid'] ?? '';
                $bindTypes .= "s";
                break;
            case 'settled':
                $conditions[] = "supptrans.settled = 1";
                $conditions[] = "bazar_parchi.svid = ?";
                $bindParams[] = $_SESSION['svid'] ?? '';
                $bindTypes .= "s";
                break;
        }
    }

    // Add all conditions to WHERE clause
    if (!empty($conditions)) {
        $queryParts['where'] .= " AND " . implode(" AND ", $conditions);
    }

    // Build final query
    $SQL = $queryParts['select'] . " " . $queryParts['from'] . " " . $queryParts['where'];

    // Log query for debugging (remove in production)
    error_log("Generated SQL: " . $SQL);

    // Use prepared statement
    if (!$stmt = mysqli_prepare($db, $SQL)) {
        throw new Exception("Query preparation failed: " . mysqli_error($db));
    }

    // Bind parameters if any
    if (!empty($bindParams)) {
        mysqli_stmt_bind_param($stmt, $bindTypes, ...$bindParams);
    }

    // Execute query
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Query execution failed: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception("Failed to get result set: " . mysqli_error($db));
    }

    // Pre-fetch user permissions
    $canEdit = userHasPermission($db, "edit_inward_parchi");
    $canPrintInternal = userHasPermission($db, "inward_parchi_internal");

    $data = [];
    $batchParchinos = [];
    $rows = [];

    // Collect all parchinos for batch queries
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
        $batchParchinos[] = $row['parchino'];
    }

    mysqli_stmt_close($stmt);

    // Batch query for advance amounts
    $advanceAmounts = [];
    if (!empty($batchParchinos)) {
        $placeholders = str_repeat('?,', count($batchParchinos) - 1) . '?';
        $advanceSQL = "SELECT parchino, SUM(amount) as advance FROM bpledger WHERE parchino IN ($placeholders) GROUP BY parchino";

        $stmtAdv = mysqli_prepare($db, $advanceSQL);
        if ($stmtAdv) {
            mysqli_stmt_bind_param($stmtAdv, str_repeat('s', count($batchParchinos)), ...$batchParchinos);
            mysqli_stmt_execute($stmtAdv);
            $advResult = mysqli_stmt_get_result($stmtAdv);

            while ($advRow = mysqli_fetch_assoc($advResult)) {
                $advanceAmounts[$advRow['parchino']] = $advRow['advance'] ?: 0;
            }
            mysqli_stmt_close($stmtAdv);
        }
    }

    // Batch query for item amounts
    $itemAmounts = [];
    if (!empty($batchParchinos)) {
        $itemSQL = "SELECT parchino, SUM(quantity_received * price) as amount 
                    FROM bpitems 
                    WHERE parchino IN ($placeholders) 
                    AND stockid != '' 
                    AND deleted_by = '' 
                    GROUP BY parchino";

        $stmtItem = mysqli_prepare($db, $itemSQL);
        if ($stmtItem) {
            mysqli_stmt_bind_param($stmtItem, str_repeat('s', count($batchParchinos)), ...$batchParchinos);
            mysqli_stmt_execute($stmtItem);
            $itemResult = mysqli_stmt_get_result($stmtItem);

            while ($itemRow = mysqli_fetch_assoc($itemResult)) {
                $itemAmounts[$itemRow['parchino']] = $itemRow['amount'] ?: 0;
            }
            mysqli_stmt_close($stmtItem);
        }
    }

    // Batch query for allocations
    $allocations = [];
    if (!empty($batchParchinos)) {
        // First get all traids
        $traidMap = [];
        foreach ($rows as $row) {
            if (!empty($row['traid'])) {
                $traidMap[$row['traid']] = $row['parchino'];
            }
        }

        if (!empty($traidMap)) {
            $traidPlaceholders = str_repeat('?,', count($traidMap) - 1) . '?';
            $allocSQL = "SELECT transid_allocto, MAX(datealloc) as last_date, SUM(amt) as total_alloc 
                         FROM suppallocs 
                         WHERE transid_allocto IN ($traidPlaceholders) 
                         GROUP BY transid_allocto";

            $stmtAlloc = mysqli_prepare($db, $allocSQL);
            if ($stmtAlloc) {
                mysqli_stmt_bind_param($stmtAlloc, str_repeat('s', count($traidMap)), ...array_keys($traidMap));
                mysqli_stmt_execute($stmtAlloc);
                $allocResult = mysqli_stmt_get_result($stmtAlloc);

                while ($allocRow = mysqli_fetch_assoc($allocResult)) {
                    $allocations[$traidMap[$allocRow['transid_allocto']]] = [
                        'last_date' => $allocRow['last_date'],
                        'total_alloc' => $allocRow['total_alloc'] ?: 0
                    ];
                }
                mysqli_stmt_close($stmtAlloc);
            }
        }
    }

    // Process rows with cached data
    foreach ($rows as $row) {
        $parchino = $row['parchino'];

        // Get cached values
        $advance = $advanceAmounts[$parchino] ?? 0;
        $amount = $itemAmounts[$parchino] ?? 0;
        $allocation = $allocations[$parchino] ?? ['last_date' => '', 'total_alloc' => 0];

        // Process row
        if (empty($row['name'])) {
            $row['name'] = $row['temp_vendor'] ?? '';
        }

        $r = [];
        $parchinoParts = explode("-", $parchino);
        $r[] = $parchinoParts[1] ?? '';
        $r[] = $parchino;
        $r[] = $row['svid'];
        $r[] = $row['name'];
        $r[] = date("d/m/Y", strtotime($row['created_at']));

        // Amount with conditional formatting
        if (function_exists('userHasVendorPermission') && userHasVendorPermission($db, $row['svid'])) {
            if (function_exists('locale_number_format')) {
                $amountFormatted = locale_number_format($amount, 2);
            } else {
                $amountFormatted = number_format($amount, 2);
            }

            if ($amount <= 10000) {
                $r[] = '<span style="background-color:yellow;">' . $amountFormatted . '</span>';
            } elseif ($amount <= 50000) {
                $r[] = '<span style="background-color:blue;color: white;">' . $amountFormatted . '</span>';
            } elseif ($amount <= 100000) {
                $r[] = '<span style="background-color:darkblue;color: white;">' . $amountFormatted . '</span>';
            } elseif ($amount <= 200000) {
                $r[] = '<span style="background-color:deeppink;color: white;">' . $amountFormatted . '</span>';
            } else {
                $r[] = '<span style="background-color:red;color: white;">' . $amountFormatted . '</span>';
            }
        } else {
            $r[] = "";
        }

        // GST invoice
        switch ($row['gstinvoice'] ?? 'none') {
            case 'e':
                $r[] = "Exclusive";
                break;
            case 'i':
                $r[] = "Inclusive";
                break;
            default:
                $r[] = "";
        }

        // Advance amount
        if (function_exists('userHasVendorPermission') && userHasVendorPermission($db, $row['svid'])) {
            $r[] = $advance;
        } else {
            $r[] = "";
        }

        // Allocation date
        $r[] = $allocation['last_date'];

        // Status
        $status = "Saved";
        if ($row['discarded'] == 1) {
            $status = "Discarded";
        } elseif ($row['settled2'] == 1) {
            $status = "Settled";
        } elseif ($row['inprogress'] == 1) {
            $status = "InProgress";
        } elseif ($allocation['total_alloc'] > 0) {
            $status = "Saved " . $allocation['total_alloc'];
        }
        $r[] = $status;

        $r[] = $row['realname'];

        // Edit button
        if ($canEdit) {
            if ($row['inprogress'] == 1 && $row['settled2'] == 0) {
                $r[] = "<a class='btn btn-warning' target='_blank' href='editInwardParchi.php?parchi=" . urlencode($parchino) . "'>Edit</a>";
            } else {
                $r[] = "<a class='btn btn-success' target='_blank' href='addfilesInwardParchi.php?parchi=" . urlencode($parchino) . "'>Edit Aux.</a>";
            }
        } else {
            $r[] = "";
        }

        // IGP button
        $r[] = ($row['igp_created'] == 0) ? "" :
            "<a class='btn btn-info' target='_blank' href='../../../PDFIGP.php?RequestNo=" . urlencode($row['igp_id']) . "'>IGP</a>";

        // External print button
        $r[] = ($row['discarded'] == 1) ? "" :
            "<a class='btn btn-info' target='_blank' href='inwardParchiPrint.php?parchi=" . urlencode($parchino) . "'>External</a>";

        // Internal print button
        if ($canPrintInternal) {
            $r[] = ($row['discarded'] == 1) ? "" :
                "<a class='btn btn-info' target='_blank' href='inwardParchiPrint.php?parchi=" . urlencode($parchino) . "&internal'>Internal</a>";
        } else {
            $r[] = "";
        }

        $data[] = $r;
    }

    // Send clean JSON response
    sendCleanJson($data);
} catch (Exception $e) {
    // Send error as JSON
    sendCleanJson([
        'error' => true,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
