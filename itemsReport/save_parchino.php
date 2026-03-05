<?php
header('Content-Type: application/json');
require_once('includes/session.inc');
require_once('includes/config.php');

if (!isset($_SESSION['UsersRealName'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $db = getDBConnection();
    if (!$db) {
        throw new Exception('Database connection failed');
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    if ($action === 'save_all' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || !isset($data['prices']) || !isset($data['factors'])) {
            throw new Exception('Invalid data format');
        }

        $prices = $data['prices'];
        $factors = $data['factors'];
        
        mysqli_begin_transaction($db);
        
        try {
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            // First, get all stock IDs that have existing parchino records
            $allStockIds = array_unique(array_merge(array_keys($prices), array_keys($factors)));
            
            foreach ($allStockIds as $stockId) {
                $stockId = mysqli_real_escape_string($db, $stockId);
                
                // Check if there's an existing parchino record for this stock ID
                $checkSql = "SELECT id FROM igp_parchi WHERE stockid = '$stockId' ORDER BY pdate DESC LIMIT 1";
                $checkResult = mysqli_query($db, $checkSql);
                
                $price = isset($prices[$stockId]) ? floatval($prices[$stockId]) : null;
                $factor = isset($factors[$stockId]) ? floatval($factors[$stockId]) : null;
                
                if (mysqli_num_rows($checkResult) > 0) {
                    // Update the most recent parchino record
                    $row = mysqli_fetch_assoc($checkResult);
                    $parchinoId = $row['id'];
                    
                    $updateFields = [];
                    if ($price !== null) {
                        $updateFields[] = "adjust_unit_price = $price";
                    }
                    if ($factor !== null) {
                        $updateFields[] = "landing_factor = $factor";
                    }
                    
                    if (!empty($updateFields)) {
                        $updateSql = "UPDATE igp_parchi SET " . implode(', ', $updateFields) . " WHERE id = $parchinoId";
                        if (mysqli_query($db, $updateSql)) {
                            $successCount++;
                        } else {
                            $errorCount++;
                            $errors[] = "Failed to update for $stockId: " . mysqli_error($db);
                        }
                    }
                } else {
                    // No parchino record exists, create a new one with default values
                    $insertFields = ["stockid = '$stockId'", "quantity = 0", "price = 0", "parchino = 'SYSTEM'", "pdate = CURDATE()"];
                    
                    if ($price !== null) {
                        $insertFields[] = "adjust_unit_price = $price";
                    }
                    if ($factor !== null) {
                        $insertFields[] = "landing_factor = $factor";
                    }
                    
                    $insertSql = "INSERT INTO igp_parchi SET " . implode(', ', $insertFields);
                    if (mysqli_query($db, $insertSql)) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $errors[] = "Failed to insert for $stockId: " . mysqli_error($db);
                    }
                }
            }
            
            mysqli_commit($db);
            
            echo json_encode([
                'status' => 'success',
                'message' => "Saved $successCount customizations successfully" . ($errorCount > 0 ? " with $errorCount errors" : ""),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);
            
        } catch (Exception $e) {
            mysqli_rollback($db);
            throw $e;
        }
        
    } elseif ($action === 'save_single' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $stockId = mysqli_real_escape_string($db, $_POST['stockid'] ?? '');
        $field = mysqli_real_escape_string($db, $_POST['field'] ?? '');
        $value = floatval($_POST['value'] ?? 0);
        
        if (!$stockId || !$field || !in_array($field, ['adjust_unit_price', 'landing_factor'])) {
            throw new Exception('Invalid parameters');
        }
        
        // Check if there's an existing parchino record for this stock ID
        $checkSql = "SELECT id FROM igp_parchi WHERE stockid = '$stockId' ORDER BY pdate DESC LIMIT 1";
        $checkResult = mysqli_query($db, $checkSql);
        
        if (mysqli_num_rows($checkResult) > 0) {
            // Update the most recent parchino record
            $row = mysqli_fetch_assoc($checkResult);
            $parchinoId = $row['id'];
            
            $sql = "UPDATE igp_parchi SET $field = $value WHERE id = $parchinoId";
        } else {
            // No parchino record exists, create a new one with this value
            if ($field === 'adjust_unit_price') {
                $sql = "INSERT INTO igp_parchi (stockid, quantity, price, parchino, pdate, adjust_unit_price) 
                        VALUES ('$stockId', 0, 0, 'SYSTEM', CURDATE(), $value)";
            } else {
                $sql = "INSERT INTO igp_parchi (stockid, quantity, price, parchino, pdate, landing_factor) 
                        VALUES ('$stockId', 0, 0, 'SYSTEM', CURDATE(), $value)";
            }
        }
        
        if (mysqli_query($db, $sql)) {
            echo json_encode([
                'status' => 'success',
                'message' => "Saved successfully",
                'affected_rows' => mysqli_affected_rows($db)
            ]);
        } else {
            throw new Exception('Failed to save: ' . mysqli_error($db));
        }
        
    } elseif ($action === 'get' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $stockId = mysqli_real_escape_string($db, $_GET['stockid'] ?? '');
        
        if ($stockId) {
            // Get single record
            $sql = "SELECT adjust_unit_price, landing_factor 
                    FROM igp_parchi 
                    WHERE stockid = '$stockId' 
                    ORDER BY pdate DESC 
                    LIMIT 1";
            $result = mysqli_query($db, $sql);
            
            if ($row = mysqli_fetch_assoc($result)) {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'adjust_unit_price' => floatval($row['adjust_unit_price']),
                        'landing_factor' => floatval($row['landing_factor'])
                    ]
                ]);
            } else {
                echo json_encode([
                    'status' => 'success',
                    'data' => [
                        'adjust_unit_price' => 0,
                        'landing_factor' => 1
                    ]
                ]);
            }
        } else {
            // Get all distinct stock IDs with custom values
            $sql = "SELECT DISTINCT stockid, adjust_unit_price, landing_factor 
                    FROM igp_parchi 
                    WHERE adjust_unit_price != 0 OR landing_factor != 1
                    ORDER BY stockid";
            $result = mysqli_query($db, $sql);
            
            $data = [];
            while ($row = mysqli_fetch_assoc($result)) {
                if (!isset($data[$row['stockid']])) {
                    $data[$row['stockid']] = [
                        'adjust_unit_price' => floatval($row['adjust_unit_price']),
                        'landing_factor' => floatval($row['landing_factor'])
                    ];
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'data' => $data,
                'count' => count($data)
            ]);
        }
        
    } else {
        throw new Exception('Invalid action or request method');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>