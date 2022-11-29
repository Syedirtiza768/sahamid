<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$ibolt_qty = $_POST['ibolt_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $ibolt_total= $panelCost['ibolt_cost'];
  }

 $paint_budget = $ibolt_total*$ibolt_qty;
 
 $sql ="UPDATE pc_cash_demand SET ibolt_budget = '".$paint_budget."', ibolt_qty = '".$ibolt_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>