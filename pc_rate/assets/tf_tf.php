<?php
require_once './config.php';
$tf_tf=$_POST['tf_tf'];
$sql ="UPDATE pc_rate SET tf_tf = '".$tf_tf."'  WHERE id = '1'";
mysqli_query($conn, $sql);
session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('tf_tf ','".$_SESSION['UsersRealName']."','".$tf_tf."','".$date."')";
mysqli_query($conn, $sql);
?>