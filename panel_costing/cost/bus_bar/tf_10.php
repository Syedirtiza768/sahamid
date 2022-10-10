<?php
require_once './config.php';
$tf_10_foot_size= $_POST['first_foot_size'];
$tf_10_sleeve_cost = $_POST['first_sleeve_cost'];
$tf_10_factor = $_POST['first_factor'];
$tf_10_sleeve_total_cost=$_POST['first_cost'];
$tf_10_bbr_weight=$_POST['first_bbr_weight'];
$id=$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Bus Bar(25*10) ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

$sql ="UPDATE bus_bar_sheet SET 25_10_foot_size = '".$tf_10_foot_size."',
25_10_sleeve_cost = '".$tf_10_sleeve_cost."',
25_10_factor = '".$tf_10_factor."',
25_10_sleeve_total_cost = '".$tf_10_sleeve_total_cost."',
25_10_bbr_weight = '".$tf_10_bbr_weight."'
  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>