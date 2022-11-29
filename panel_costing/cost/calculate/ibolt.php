<?php
require_once './config.php';
$ibolt_qty = $_POST['ibolt_qty'];
$id = $_POST['pc_id'];
$SQL = 'SELECT * FROM pc_rate';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $i_bolt= $panelCost['i_bolt'];
  }
  if(!empty($ibolt_qty)){
 $ibolt_cost = $ibolt_qty*$i_bolt;
  }

  $id = $_POST['pc_id'];
$sql = "UPDATE pc_cash_demand SET ibolt_budget = '" . $ibolt_cost . "' WHERE pc_id = '" . $id . "'";
mysqli_query($conn, $sql);

  session_start();
  $_SESSION['ibolt_cost'] = $ibolt_cost;
  
// session_start();
// date_default_timezone_set("Asia/Kargkhi");
//  $date = date('d-m-y h:i:s');
// $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// if (mysqli_query($conn, $sql)) {
// }
// else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
//   }

$sql ="UPDATE panel_costing SET ibolt_cost = '".$ibolt_cost."' WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo $ibolt_cost;
    }
?>