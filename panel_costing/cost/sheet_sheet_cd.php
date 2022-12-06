<?php
require_once './config.php';
$w = $_POST['sheet_sheet_cd'];
$id=$_POST['pc_id'];

  session_start();
  date_default_timezone_set("Asia/Karachi");
  $date = date('d-m-y h:i:s');
  $sql = "INSERT INTO panelcostingmodifications(panel_id,updateDate,pc_description)VALUES('$id','$date','Updated Sheet for cash demand by ".$_SESSION['UsersRealName']." at')";
  mysqli_query($conn, $sql);

$sql ="UPDATE panel_costing SET sheet_sheet_cd = '".$w."'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>