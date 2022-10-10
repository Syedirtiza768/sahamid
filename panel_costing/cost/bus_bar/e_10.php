<?php
require_once './config.php';
$e_10_foot_size= $_POST['first_foot_size'];
$e_10_sleeve_cost = $_POST['first_sleeve_cost'];
$e_10_factor = $_POST['first_factor'];
$e_10_sleeve_total_cost=$_POST['first_cost'];
$e_10_bbr_weight=$_POST['first_bbr_weight'];
$id=$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Bus Bar(80*10) ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE bus_bar_sheet SET 80_10_foot_size = '".$e_10_foot_size."',
80_10_sleeve_cost = '".$e_10_sleeve_cost."',
80_10_factor = '".$e_10_factor."',
80_10_sleeve_total_cost = '".$e_10_sleeve_total_cost."',
80_10_bbr_weight = '".$e_10_bbr_weight."'
  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>