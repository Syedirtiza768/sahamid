<?php
require_once './config.php';
$hl_051=$_POST['hl_051'];
$sql ="UPDATE pc_rate SET hl_051 = '".$hl_051."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('hl_051 ','".$_SESSION['UsersRealName']."','".$hl_051."','".$date."')";
mysqli_query($conn, $sql);
?>