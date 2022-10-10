<?php
require_once './config.php';
$id =$_POST['pannel_costing_id'];
$i_qty = $_POST['i_qty'];
$i_uc = $_POST['i_uc'];
$i_cost=  $_POST['i_cost'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','I-Bolt ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);


$sql ="UPDATE cost_sheet SET i_qty = '".$i_uc."',i_uc = '".$i_uc."', i_cost = '".$i_cost."' WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>