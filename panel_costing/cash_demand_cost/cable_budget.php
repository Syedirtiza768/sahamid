<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$cable_qty = $_POST['cable_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $cable_total= $panelCost['wiring'];
  }

 $paint_budget = $cable_total*$cable_qty;
 
 $sql ="UPDATE pc_cash_demand SET cable_budget = '".$paint_budget."', cable_qty = '".$cable_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>