<?php
require_once './config.php';
$id =$_POST['pannel_costing_id'];
$h_model= $_POST['h_model'];
$h_mf= $_POST['h_mf'];
$h_qty = $_POST['h_qty'];
$h_cost=  $_POST['h_cost'];
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Hinges ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE cost_sheet SET h_model = '".$h_model."', h_mf = '".$h_mf."',h_qty = '".$h_qty."', h_cost = '".$h_cost."' WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>
?>