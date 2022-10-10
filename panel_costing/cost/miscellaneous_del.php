<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 
$sql ="UPDATE pannel_costing SET miscellaneous_sf = '0', miscellaneous_ws = '0', miscellaneous_h = '0', miscellaneous_w = '0',miscellaneous_q = '0', miscellaneous_ss = '0'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
    session_start();
    date_default_timezone_set("Asia/Karachi");
     $date = date('d-m-y h:i:s');
    $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Miscellaneous ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
    mysqli_query($conn, $sql);
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>