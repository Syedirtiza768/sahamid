<?php
require_once './config.php';
$pl_150=$_POST['pl_150'];
$sql ="UPDATE pc_rate SET pl_150 = '".$pl_150."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('pl_150 ','".$_SESSION['UsersRealName']."','".$pl_150."','".$date."')";
mysqli_query($conn, $sql);
?>