<?php
require_once './config.php';
$h_7035=$_POST['h_7035'];
$sql ="UPDATE pc_rate SET h_7035 = '".$h_7035."'  WHERE id = '1'";
mysqli_query($conn, $sql);

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('h_7035 ','".$_SESSION['UsersRealName']."','".$h_7035."','".$date."')";
mysqli_query($conn, $sql);


?>