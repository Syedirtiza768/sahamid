<?php
require_once './config.php';
$sheet_type= $_POST['sheet_type'];
$id=$_POST['pannel_costing_id'];
$matal_s_price = $_POST['matal_s_price'];
$stainless_s_price = $_POST['stainless_s_price'];
$galvanized_s_price=$_POST['galvanized_s_price'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Sheet Selection ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE pannel_costing SET sheet_type = '".$sheet_type."', matal_s_price = '".$matal_s_price."', stainless_s_price = '".$stainless_s_price."', galvanized_s_price = '".$galvanized_s_price."'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>