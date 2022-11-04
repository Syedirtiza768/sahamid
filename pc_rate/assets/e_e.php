<?php
require_once './config.php';
$e_e=$_POST['e_e'];
$sql ="UPDATE pc_rate SET e_e = '".$e_e."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('e_e ','".$_SESSION['UsersRealName']."','".$e_e."','".$date."')";
mysqli_query($conn, $sql);
?>