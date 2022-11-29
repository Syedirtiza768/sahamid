<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$paint_cost_qty = $_POST['paint_cost_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $paintcost_total= $panelCost['paintcost_total'];
  }

 $paint_budget = $paintcost_total*$paint_cost_qty;
 
 $sql ="UPDATE pc_cash_demand SET paintcost_budget = '".$paint_budget."', paintcost_qty = '".$paint_cost_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>