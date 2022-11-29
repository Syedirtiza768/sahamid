<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$lock_qty = $_POST['lock_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $lock_total= $panelCost['lock_cost'];
  }

 $paint_budget = $lock_total*$lock_qty;
 
 $sql ="UPDATE pc_cash_demand SET lock_budget = '".$paint_budget."', lock_qty = '".$lock_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>