<?php
require_once './config.php';
$i_bolt=$_POST['i_bolt'];
$sql ="UPDATE pc_rate SET i_bolt = '".$i_bolt."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('i_bolt ','".$_SESSION['UsersRealName']."','".$i_bolt."','".$date."')";
mysqli_query($conn, $sql);
?>