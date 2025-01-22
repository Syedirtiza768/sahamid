<?php
session_start();
include_once("../config1.php");

$userId = $_SESSION['UserID']; // Get logged-in user ID
// $userId = $_SESSION['defaultlocation']; // Get logged-in user ID
          $query = "SELECT * FROM www_users WHERE userid = '$userId'";
          $result = mysqli_query($conn, $query);

          if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $defaultlocation = $user['defaultlocation'];
          } else {
            echo "User not found.";
          }

// Step 1: Get notifications before checking the status
$query = "SELECT * FROM ogp_notifications WHERE defaultlocation = '". $defaultlocation ."' AND is_read = 0 ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);


$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Step 2: Check the quantity of all items related to this notification in the 'submitted_ogp_items' table
    $notification_id = $row['id']; // Get the notification_id

    // Query to check if all items have quantity 0
    $checkItemsSQL = "SELECT quantity FROM submitted_ogp_items WHERE notification_id = ?";
    $checkItemsStmt = $conn->prepare($checkItemsSQL);
    $checkItemsStmt->bind_param("i", $notification_id);
    $checkItemsStmt->execute();
    $checkItemsResult = $checkItemsStmt->get_result();

    // Step 3: Check if all items have quantity 0
    $allZero = true; // Assume all quantities are zero initially
    while ($item = $checkItemsResult->fetch_assoc()) {
        if ($item['quantity'] > 0) {
            $allZero = false; // If any quantity is greater than zero, set to false
            break;
        }
    }

    // Step 4: If all items' quantities are zero, update the status to 'completed'
    if ($allZero) {
        $updateStatusSQL = "UPDATE ogp_notifications SET status = 'completed' WHERE id = ?";
        $updateStatusStmt = $conn->prepare($updateStatusSQL);
        $updateStatusStmt->bind_param("i", $notification_id);
        $updateStatusStmt->execute();
    }

    // Step 5: Fetch updated notifications and format the timestamp
    $row['timestamp'] = timeAgo($row['created_at']); // Format the timestamp
    unset($row['created_at']); // Remove original field if you want to send only the formatted timestamp

    $notifications[] = $row;
}

// Function to format time in relative terms (like "Just now", "2 hours ago")
function timeAgo($timestamp)
{
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;

    // Calculate time difference in different units
    $minutes      = round($seconds / 60);           // value 60 is seconds
    $hours        = round($seconds / 3600);         // value 3600 is 60 minutes * 60 sec
    $days         = round($seconds / 86400);        // value 86400 is 24 hours * 60 minutes * 60 sec
    $weeks        = round($seconds / 604800);       // value 604800 is 7 days * 24 hours * 60 minutes * 60 sec
    $months       = round($seconds / 2629440);      // value 2629440 is ((365+365+365+365)/4/12) days * 24 hours * 60 minutes * 60 sec
    $years        = round($seconds / 31553280);     // value 31553280 is ((365+365+365+365)/4) days * 24 hours * 60 minutes * 60 sec

    // Now we start to format the time difference based on its value
    if ($seconds <= 60) {
        return "Just now";
    } else if ($minutes <= 60) {
        if ($minutes == 1) {
            return "one minute ago";
        } else {
            return "$minutes minutes ago";
        }
    } else if ($hours <= 24) {
        if ($hours == 1) {
            return "an hour ago";
        } else {
            return "$hours hours ago";
        }
    } else if ($days <= 7) {
        if ($days == 1) {
            return "yesterday";
        } else {
            return "$days days ago";
        }
    } else if ($weeks <= 4.3) { // 4.3 == 30/7
        if ($weeks == 1) {
            return "a week ago";
        } else {
            return "$weeks weeks ago";
        }
    } else if ($months <= 12) {
        if ($months == 1) {
            return "one month ago";
        } else {
            return "$months months ago";
        }
    } else {
        if ($years == 1) {
            return "one year ago";
        } else {
            return "$years years ago";
        }
    }
}

// Step 6: Return the updated notifications
echo json_encode([
    'count' => count($notifications),
    'notifications' => $notifications
]);

