<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 


$sql ="UPDATE pannel_costing SET front_door_sf = '0', front_door_ws = '0', front_door_h = '0', front_door_w = '0',front_door_q = '0', front_door_ss = '0'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
    session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);

}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>