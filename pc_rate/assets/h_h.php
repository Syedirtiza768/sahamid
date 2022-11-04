<?php
require_once './config.php';
$h_h=$_POST['h_h'];
$sql ="UPDATE pc_rate SET h_h = '".$h_h."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('h_h ','".$_SESSION['UsersRealName']."','".$h_h."','".$date."')";
mysqli_query($conn, $sql);
?>