<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 




$sql ="UPDATE pannel_costing SET front_door_sf_four = '0', front_door_ws_four = '0', front_door_h_four = '0', front_door_w_four = '0',front_door_q_four = '0', front_door_ss_four = '0'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
    session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-4 ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>
