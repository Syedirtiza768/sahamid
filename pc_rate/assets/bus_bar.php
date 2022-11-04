<?php
require_once './config.php';
$bus_bar=$_POST['bus_bar'];
$sql ="UPDATE pc_rate SET bus_bar = '".$bus_bar."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('bus_bar ','".$_SESSION['UsersRealName']."','".$bus_bar."','".$date."')";
mysqli_query($conn, $sql);
?>