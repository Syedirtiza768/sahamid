<?php
require_once './config.php';
$ms_408=$_POST['ms_408'];
$sql ="UPDATE pc_rate SET ms_408 = '".$ms_408."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('ms_408 ','".$_SESSION['UsersRealName']."','".$ms_408."','".$date."')";
mysqli_query($conn, $sql);
?>