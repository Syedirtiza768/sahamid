<?php
require_once './config.php';
$s_7032=$_POST['s_7032'];
$sql ="UPDATE pc_rate SET s_7032 = '".$s_7032."'  WHERE id = '1'";
mysqli_query($conn, $sql);

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('s_7032 ','".$_SESSION['UsersRealName']."','".$s_7032."','".$date."')";
mysqli_query($conn, $sql);



?>