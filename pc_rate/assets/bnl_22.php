<?php
require_once './config.php';
$bnl_22=$_POST['bnl_22'];
$sql ="UPDATE pc_rate SET bnl_22 = '".$bnl_22."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('bnl_22 ','".$_SESSION['UsersRealName']."','".$bnl_22."','".$date."')";
mysqli_query($conn, $sql);
?>