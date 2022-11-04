<?php
require_once './config.php';
$tt_tt=$_POST['tt_tt'];
$sql ="UPDATE pc_rate SET tt_tt = '".$tt_tt."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('tt_tt ','".$_SESSION['UsersRealName']."','".$tt_tt."','".$date."')";
mysqli_query($conn, $sql);
?>