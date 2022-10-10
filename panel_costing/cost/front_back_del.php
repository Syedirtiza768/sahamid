<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 
$sql ="UPDATE pannel_costing SET FB_base_sf = '0', FB_base_ws = '0', FB_base_L = '0', FB_base_w = '0',FB_base_q = '0', FB_base_ss = '0'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
    session_start();
    date_default_timezone_set("Asia/Karachi");
     $date = date('d-m-y h:i:s');
    $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front & Back Base ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
    mysqli_query($conn, $sql);
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>