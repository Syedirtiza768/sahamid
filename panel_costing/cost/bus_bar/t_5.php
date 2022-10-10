<?php
require_once './config.php';
$t_5_foot_size= $_POST['first_foot_size'];
$t_5_sleeve_cost = $_POST['first_sleeve_cost'];
$t_5_factor = $_POST['first_factor'];
$t_5_sleeve_total_cost=$_POST['first_cost'];
$t_5_bbr_weight=$_POST['first_bbr_weight'];
$id=$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Bus Bar(20*5) ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE bus_bar_sheet SET 20_5_foot_size = '".$t_5_foot_size."',
20_5_sleeve_cost = '".$t_5_sleeve_cost."',
20_5_factor = '".$t_5_factor."',
20_5_sleeve_total_cost = '".$t_5_sleeve_total_cost."',
20_5_bbr_weight = '".$t_5_bbr_weight."'
  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>