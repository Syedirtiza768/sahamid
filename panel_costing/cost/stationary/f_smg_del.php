<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 
$polish= $_POST['polish'];
$rent= $_POST['rent'];
$wiring = $_POST['wc_cost'];
$labour= $_POST['lc_cost'];
$misc_exp= $_POST['me_cost'];
$sql ="UPDATE cost_sheet SET polish = '".$polish."', rent = '".$rent."',wiring = '".$wiring."',
labour = '".$labour."', misc_exp = '".$misc_exp."' ,cost_14_SWG='0',
percent_price_14_SWG = '0', percent_14_SWG = '0'  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
    session_start();
    date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','COST PRICE IN MS 14 SWG ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>