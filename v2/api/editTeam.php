<?php
$db_host = 'localhost';
$db_username = 'irtiza';
$db_password = 'netetech321';
$db_name = 'sahamid';

//Create connection and select DB
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$id= $_POST['id'];
$name = $_POST['name'];
$lead = $_POST['lead'];
$members = $_POST['members'];
 
$sql = "UPDATE salesteam SET name = '" . $name . "',members = '" . $members . "', lead = '" . $lead . "' WHERE id = '" . $id . "'";
  if (mysqli_query($conn, $sql)) {
  echo "Done";
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>