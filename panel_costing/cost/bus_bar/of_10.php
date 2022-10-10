<?php
require_once './config.php';
$of_10_foot_size= $_POST['first_foot_size'];
$of_10_sleeve_cost = $_POST['first_sleeve_cost'];
$of_10_factor = $_POST['first_factor'];
$of_10_sleeve_total_cost=$_POST['first_cost'];
$of_10_bbr_weight=$_POST['first_bbr_weight'];
$id=$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Bus Bar(150*10) ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE bus_bar_sheet SET 150_10_foot_size = '".$of_10_foot_size."',
150_10_sleeve_cost = '".$of_10_sleeve_cost."',
150_10_factor = '".$of_10_factor."',
150_10_sleeve_total_cost = '".$of_10_sleeve_total_cost."',
150_10_bbr_weight = '".$of_10_bbr_weight."'
  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>