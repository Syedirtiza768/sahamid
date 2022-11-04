<?php
require_once './config.php';
$pl_130=$_POST['pl_130'];
$sql ="UPDATE pc_rate SET pl_130 = '".$pl_130."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('pl_130 ','".$_SESSION['UsersRealName']."','".$pl_130."','".$date."')";
mysqli_query($conn, $sql);
?>