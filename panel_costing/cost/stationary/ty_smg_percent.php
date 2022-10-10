<?php
require_once './config.php';
$polish= $_POST['polish'];
$rent= $_POST['rent'];
$wiring = $_POST['wc_cost'];
$labour= $_POST['lc_cost'];
$misc_exp= $_POST['me_cost'];
$cost_20_SWG = $_POST['cp_tysmg'];
$percent_price_20_SWG= $_POST['pp_tysmg'];
$percent_20_SWG= $_POST['ty_smg_percent'];
$id =$_POST['pannel_costing_id'];

session_start();
date_default_timezone_set("Asia/Karachi");
 $date = date('d-m-y h:i:s');
$sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','COST PRICE IN MS 20 SWG ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
mysqli_query($conn, $sql);


$sql ="UPDATE cost_sheet SET polish = '".$polish."', rent = '".$rent."',wiring = '".$wiring."',
labour = '".$labour."', misc_exp = '".$misc_exp."' ,cost_20_SWG = '".$cost_20_SWG."' ,
percent_price_20_SWG = '".$percent_price_20_SWG."', percent_20_SWG = '".$percent_20_SWG."'  WHERE pannel_id = '".$id."'";
if (mysqli_query($conn, $sql)) {
  echo "Done";
}
else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
?>