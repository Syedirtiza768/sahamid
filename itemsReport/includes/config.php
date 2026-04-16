<?php
//Database details
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_username = 'irtiza';
$db_password = 'netetech321';
$db_name = 'sahamid';

// Function to create database connection
function getDBConnection() {
    global $db_host, $db_username, $db_password, $db_name;
    
    $conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
    if (mysqli_connect_errno()) {
        throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());
    }
    
    // Set UTF-8 encoding
    mysqli_set_charset($conn, "utf8");
    
    return $conn;
}
?>