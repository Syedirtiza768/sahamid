<?php
require_once './config.php';
$id =$_POST['pannel_costing_id'];
$as_uc=$_POST['as_uc'];
$as_qty = $_POST['as_qty'];
$as_cost=  $_POST['as_cost'];
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','ACRYLIC SHEET ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE cost_sheet SET as_qty = '".$as_qty."', as_uc = '".$as_uc."' , as_cost = '".$as_cost."' WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>
?>