<?php
$db_host = 'localhost';
$db_username = 'irtiza';
$db_password = 'netetech321';
$db_name = 'sahamid';

$id = $_POST['id'];
//Create connection and select DB
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$sql = "SELECT * from salesteam WHERE id='$id' ";
$result = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($result)) {
        echo json_encode(array(
            "name" => $row['name'],
            "lead" => $row['lead'],
            "id" => $row['id'],
            "members" => $row['members']
        ));
    }
?>