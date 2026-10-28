<?php
include_once __DIR__ . '/../../db_config.php';
//Database details
$db_host = 'localhost';
$db_username = 'irtiza';
$db_password = SAHAMID_DB_PASSWORD;
$db_name = 'sahamid';

//Create connection and select DB
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
