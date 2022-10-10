<?php
require_once './config.php';
$id =$_POST['pannel_costing_id'];
$gk_qty = $_POST['gk_qty'];
$gk_uc = $_POST['gk_uc'];
$gk_cost=  $_POST['gk_cost'];
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Gas Kit ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);
$sql ="UPDATE cost_sheet SET gk_qty = '".$gk_qty."',gk_uc = '".$gk_uc."', gk_cost = '".$gk_cost."' WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>