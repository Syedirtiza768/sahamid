<?php
require_once './config.php';
$id =$_POST['pannel_costing_id'];
$pl_model= $_POST['pl_model'];
$pl_mf= $_POST['pl_mf'];
$pl_qty = $_POST['pl_qty'];
$pl_cost=  $pl_mf*$pl_qty;

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Panel Lock ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);


$sql ="UPDATE cost_sheet SET pl_model = '".$pl_model."', pl_mf = '".$pl_mf."', pl_qty = '".$pl_qty."', pl_cost = '".$pl_cost."' WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>