<?php
session_start();
include_once("../config1.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['error' => 'Invalid or missing ID parameter']);
    exit;
}

$id = intval($_GET['id']);

// Query to fetch related data along with substore description
$query = "
    SELECT 
        submitted_ogp_items.stockcode, 
        submitted_ogp_items.notification_id, 
        submitted_ogp_items.quantity, 
        submitted_ogp_items.status, 
        submitted_ogp_items.storeid, 
        submitted_ogp_items.substoreid, 
        substores.description AS substore_description
    FROM 
        submitted_ogp_items
    LEFT JOIN 
        substores ON submitted_ogp_items.substoreid = substores.substoreid
    WHERE 
        submitted_ogp_items.notification_id = ?
";


$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$relatedData = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $relatedData[] = $row; // Return all data including substore description
    }
    echo json_encode(['related_info' => $relatedData]);
} else {
    echo json_encode(['related_info' => $relatedData]);
}
?>

