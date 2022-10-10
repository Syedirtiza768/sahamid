<?php
require_once './config.php';
$fy_5_foot_size= $_POST['first_foot_size'];
$fy_5_sleeve_cost = $_POST['first_sleeve_cost'];
$fy_5_factor = $_POST['first_factor'];
$fy_5_sleeve_total_cost=$_POST['first_cost'];
$fy_5_bbr_weight=$_POST['first_bbr_weight'];
$id=$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Bus Bar(50*5) ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE bus_bar_sheet SET 50_5_foot_size = '".$fy_5_foot_size."',
50_5_sleeve_cost = '".$fy_5_sleeve_cost."',
50_5_factor = '".$fy_5_factor."',
50_5_sleeve_total_cost = '".$fy_5_sleeve_total_cost."',
50_5_bbr_weight = '".$fy_5_bbr_weight."'
  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>