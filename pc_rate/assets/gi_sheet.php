<?php
require_once './config.php';
$gi_sheet=$_POST['gi_sheet'];
$sql ="UPDATE pc_rate SET gi_sheet = '".$gi_sheet."'  WHERE id = '1'";
mysqli_query($conn, $sql);

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO pc_rate_update(value_name,user,updated_value,updated_date)VALUES('gi_sheet ','".$_SESSION['UsersRealName']."','".$gi_sheet."','".$date."')";
mysqli_query($conn, $sql);

?>