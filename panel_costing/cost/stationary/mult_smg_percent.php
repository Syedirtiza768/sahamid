<?php
require_once './config.php';
$polish= $_POST['polish'];
$rent= $_POST['rent'];
$wiring = $_POST['wc_cost'];
$labour= $_POST['lc_cost'];
$misc_exp= $_POST['me_cost'];
$cost_in_mult_gauge = $_POST['cp_mult_gauge'];
$percentage_price= $_POST['pp_mult_gauge'];
$increase_percent= $_POST['ml_smg_percent'];
$id =$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','COST PRICE IN Multiple Gauge ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);


$sql ="UPDATE cost_sheet SET polish = '".$polish."', rent = '".$rent."',wiring = '".$wiring."',
labour = '".$labour."', misc_exp = '".$misc_exp."' ,cost_in_mult_gauge = '".$cost_in_mult_gauge."' ,
percentage_price = '".$percentage_price."', increase_percent = '".$increase_percent."'  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>