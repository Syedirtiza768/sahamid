<?php
require_once './config.php';
$acrylic_sheet=$_POST['acrylic_sheet'];
$sql ="UPDATE pc_rate SET acrylic_sheet = '".$acrylic_sheet."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('acrylic_sheet ','".$_SESSION['UsersRealName']."','".$acrylic_sheet."','".$date."')";
mysqli_query($conn, $sql);
?>