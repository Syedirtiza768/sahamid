<?php
require_once './config.php';
$ot_5_foot_size= $_POST['first_foot_size'];
$ot_5_sleeve_cost = $_POST['first_sleeve_cost'];
$ot_5_factor = $_POST['first_factor'];
$ot_5_sleeve_total_cost=$_POST['first_cost'];
$ot_5_bbr_weight=$_POST['first_bbr_weight'];
$id=$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Bus Bar(120*5) ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE bus_bar_sheet SET 120_5_foot_size = '".$ot_5_foot_size."',
120_5_sleeve_cost = '".$ot_5_sleeve_cost."',
120_5_factor = '".$ot_5_factor."',
120_5_sleeve_total_cost = '".$ot_5_sleeve_total_cost."',
120_5_bbr_weight = '".$ot_5_bbr_weight."'
  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>