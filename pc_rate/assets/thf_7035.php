<?php
require_once './config.php';
$thf_7035=$_POST['thf_7035'];
$sql ="UPDATE pc_rate SET thf_7035 = '".$thf_7035."'  WHERE id = '1'";
mysqli_query($conn, $sql);

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('thf_7035 ','".$_SESSION['UsersRealName']."','".$thf_7035."','".$date."')";
mysqli_query($conn, $sql);



?>