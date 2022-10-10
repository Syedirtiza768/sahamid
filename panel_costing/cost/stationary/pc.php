<?php
require_once './config.php';
$pc_mf= $_POST['pc_mf'];
$pc_model= $_POST['pc_model'];
$pc_cost= $_POST['pc_cost'];
$id =$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Paint Cost ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);


$sql ="UPDATE cost_sheet SET pc_model = '".$pc_model."', pc_mf = '".$pc_mf."', pc_cost = '".$pc_cost."' WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>