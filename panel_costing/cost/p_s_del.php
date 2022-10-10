<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 
$sql ="UPDATE pannel_costing SET protection_sheet_sf = '0', protection_sheet_ws = '0', protection_sheet_h = '0', protection_sheet_w = '0',protection_sheet_q = '0', protection_sheet_ss = '0'  WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
    session_start();
    date_default_timezone_set("Asia/Karachi");
     $date = date('d-m-y h:i:s');
    $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Protection Sheet-1 ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
    mysqli_query($conn, $sql);
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>