<?php
session_start();
include_once("../config1.php");

// Check if the user is logged in
if (!isset($_SESSION['UserID'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['UserID']; // Get logged-in user ID

// Decode JSON payload
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['notificationId'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$notificationId = $data['notificationId'];

try {
    // Update the notification as read
    $query = "UPDATE ogp_notifications SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind the parameter (single integer parameter)
    $stmt->bind_param("i", $notificationId);
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    // Send a success response
    echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
} catch (Exception $e) {
    // Handle any errors and send an error response
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
 finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
