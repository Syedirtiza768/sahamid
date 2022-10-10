<?php
require_once './config.php';
$s_5_foot_size= $_POST['first_foot_size'];
$s_5_sleeve_cost = $_POST['first_sleeve_cost'];
$s_5_factor = $_POST['first_factor'];
$s_5_sleeve_total_cost=$_POST['first_cost'];
$s_5_bbr_weight=$_POST['first_bbr_weight'];
$id=$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Bus Bar(60*5) ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE bus_bar_sheet SET 60_5_foot_size = '".$s_5_foot_size."',
60_5_sleeve_cost = '".$s_5_sleeve_cost."',
60_5_factor = '".$s_5_factor."',
60_5_sleeve_total_cost = '".$s_5_sleeve_total_cost."',
60_5_bbr_weight = '".$s_5_bbr_weight."'
  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>