<?php
require_once './config.php';
$f_7032=$_POST['f_7032'];
$sql ="UPDATE pc_rate SET f_7032 = '".$f_7032."'  WHERE id = '1'";
mysqli_query($conn, $sql);

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('f_7032 ','".$_SESSION['UsersRealName']."','".$f_7032."','".$date."')";
mysqli_query($conn, $sql);


?>