<?php
require_once './config.php';
$id=$_POST['pannel_costing_id']; 
$polish= $_POST['polish'];
$rent= $_POST['rent'];
$wiring = $_POST['wc_cost'];
$labour= $_POST['lc_cost'];
$misc_exp= $_POST['me_cost'];

session_start();
    date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','COST PRICE IN Multiple Gauge ','$date','deleted by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);


$sql ="UPDATE cost_sheet SET polish = '".$polish."', rent = '".$rent."',wiring = '".$wiring."',
labour = '".$labour."', misc_exp = '".$misc_exp."' ,cost_in_mult_gauge='0',
percentage_price = '0', increase_percent = '0'  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo "Deleted";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>