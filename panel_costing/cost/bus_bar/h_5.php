<?php
require_once './config.php';
$h_5_foot_size= $_POST['first_foot_size'];
$h_5_sleeve_cost = $_POST['first_sleeve_cost'];
$h_5_factor = $_POST['first_factor'];
$h_5_sleeve_total_cost=$_POST['first_cost'];
$h_5_bbr_weight=$_POST['first_bbr_weight'];
$id=$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Bus Bar(100*5) ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE bus_bar_sheet SET 100_5_foot_size = '".$h_5_foot_size."',
100_5_sleeve_cost = '".$h_5_sleeve_cost."',
100_5_factor = '".$h_5_factor."',
100_5_sleeve_total_cost = '".$h_5_sleeve_total_cost."',
100_5_bbr_weight = '".$h_5_bbr_weight."'
  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>