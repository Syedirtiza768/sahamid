<?php
require_once './config.php';
$hl_030=$_POST['hl_030'];
$sql ="UPDATE pc_rate SET hl_030 = '".$hl_030."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('hl_030 ','".$_SESSION['UsersRealName']."','".$hl_030."','".$date."')";
mysqli_query($conn, $sql);

?>