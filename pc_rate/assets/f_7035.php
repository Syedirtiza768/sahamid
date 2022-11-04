<?php
require_once './config.php';
$f_7035=$_POST['f_7035'];
$sql ="UPDATE pc_rate SET f_7035 = '".$f_7035."'  WHERE id = '1'";
mysqli_query($conn, $sql);

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('f_7035 ','".$_SESSION['UsersRealName']."','".$f_7035."','".$date."')";
mysqli_query($conn, $sql);


?>