<?php
require_once './config.php';
$tf_f=$_POST['tf_f'];
$sql ="UPDATE pc_rate SET tf_f = '".$tf_f."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('tf_f ','".$_SESSION['UsersRealName']."','".$tf_f."','".$date."')";
mysqli_query($conn, $sql);
?>