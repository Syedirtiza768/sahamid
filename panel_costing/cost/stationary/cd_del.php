<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 
$sql ="UPDATE cost_sheet SET cd_model = '0', cd_mf = '0', cd_qty='0', cd_cost='0' WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";

    session_start();
    date_default_timezone_set("Asia/Karachi");
     $date = date('d-m-y h:i:s');
    $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Cable Duct ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
    mysqli_query($conn, $sql);


}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>