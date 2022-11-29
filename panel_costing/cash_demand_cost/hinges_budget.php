<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$hinges_qty = $_POST['hinges_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $hinges_total= $panelCost['hinges_cost'];
  }

 $paint_budget = $hinges_total*$hinges_qty;
 
 $sql ="UPDATE pc_cash_demand SET hinges_budget = '".$paint_budget."', hinges_qty = '".$hinges_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>