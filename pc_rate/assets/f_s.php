<?php
require_once './config.php';
$f_s=$_POST['f_s'];
$sql ="UPDATE pc_rate SET f_s = '".$f_s."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('f_s ','".$_SESSION['UsersRealName']."','".$f_s."','".$date."')";
mysqli_query($conn, $sql);
?>