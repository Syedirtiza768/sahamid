<?php
require_once './config.php';
$cd_mf= $_POST['cd_mf'];
$cd_model= $_POST['cd_model'];
$cd_qty = $_POST['cd_qty'];
$cd_cost= $_POST['cd_cost'];
$id =$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Cable Duct ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);


$sql ="UPDATE cost_sheet SET cd_model = '".$cd_model."', cd_mf = '".$cd_mf."',cd_qty = '".$cd_qty."', cd_cost = '".$cd_cost."' WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>