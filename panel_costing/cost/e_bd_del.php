<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 
$sql ="UPDATE pannel_costing SET back_door_sf_eight = '0', back_door_ws_eight = '0', back_door_h_eight = '0', back_door_w_eight = '0',back_door_q_eight = '0', back_door_ss_eight = '0'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
    session_start();
    date_default_timezone_set("Asia/Karachi");
     $date = date('d-m-y h:i:s');
    $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Back Door-8 ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
    mysqli_query($conn, $sql);
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>
