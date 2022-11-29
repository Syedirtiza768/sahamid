<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$bbr_sleeve_qty = $_POST['bbr_sleeve_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
    $bbr_sleeve_total = $panelCost['busbar_cost'] + $panelCost['busbar_sleeve'];
  }

 $paint_budget = $bbr_sleeve_total*$bbr_sleeve_qty;
 
 $sql ="UPDATE pc_cash_demand SET bbr_sleeve_budget = '".$paint_budget."', bbr_sleeve_qty = '".$bbr_sleeve_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>