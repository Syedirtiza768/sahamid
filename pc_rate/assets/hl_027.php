<?php
require_once './config.php';
$hl_027=$_POST['hl_027'];
$sql ="UPDATE pc_rate SET hl_027 = '".$hl_027."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('hl_027 ','".$_SESSION['UsersRealName']."','".$hl_027."','".$date."')";
mysqli_query($conn, $sql);
?>