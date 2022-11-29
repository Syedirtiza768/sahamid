<?php
require_once './config.php';
$ac_qty = $_POST['ac_qty'];
$SQL = 'SELECT * FROM pc_rate';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $acrylic_sheet= $panelCost['acrylic_sheet'];
  }
  if(!empty($ac_qty)){
 $acrylic_cost = $ac_qty*$acrylic_sheet;
  }

  session_start();
$_SESSION['acrylic_cost'] = $acrylic_cost;


// session_start();
// date_default_timezone_set("Asia/Karachi");
//  $date = date('d-m-y h:i:s');
// $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// if (mysqli_query($conn, $sql)) {
// }
// else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
//   }

$id = $_POST['pc_id'];
$sql ="UPDATE pc_cash_demand SET acrylic_budget = '".$acrylic_cost."' WHERE pc_id = '".$id."'";
mysqli_query($conn, $sql);


$sql ="UPDATE panel_costing SET acrylic_cost = '".$acrylic_cost."' WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo $acrylic_cost;
    }
?>