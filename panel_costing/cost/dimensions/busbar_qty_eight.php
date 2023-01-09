<?php
require_once './config.php';
$w = $_POST['door8_d'];
$id=$_POST['pc_id'];
$weight= $_POST['weight'];
$sleeve= $_POST['sleeve'];

session_start();
date_default_timezone_set("Asia/Karachi");
  $date = date('d-m-y h:i:s');
  $sql = "INSERT INTO panelcostingmodifications(panel_id,updateDate,pc_description)VALUES('$id','$date','Updated Bus Bar 8 Quantity by ".$_SESSION['UsersRealName']." at')";
  mysqli_query($conn, $sql);

$sql ="UPDATE panel_costing SET bbr8_qty = '".$w."', bbr8_weight = '".$weight."', bbr8_sleeve = '".$sleeve."'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>