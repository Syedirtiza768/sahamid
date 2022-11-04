<?php
require_once './config.php';
$hl_056=$_POST['hl_056'];
$sql ="UPDATE pc_rate SET hl_056 = '".$hl_056."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('hl_056 ','".$_SESSION['UsersRealName']."','".$hl_056."','".$date."')";
mysqli_query($conn, $sql);
?>