<?php
require_once './config.php';
$ss_sheet=$_POST['ss_sheet'];
$sql ="UPDATE pc_rate SET ss_sheet = '".$ss_sheet."'  WHERE id = '1'";
mysqli_query($conn, $sql);

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('ss_sheet ','".$_SESSION['UsersRealName']."','".$ss_sheet."','".$date."')";
mysqli_query($conn, $sql);

?>