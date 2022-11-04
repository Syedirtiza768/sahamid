<?php
require_once './config.php';
$ms_480=$_POST['ms_480'];
$sql ="UPDATE pc_rate SET ms_480 = '".$ms_480."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('ms_480 ','".$_SESSION['UsersRealName']."','".$ms_480."','".$date."')";
mysqli_query($conn, $sql);
?>