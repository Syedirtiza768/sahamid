<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$cd_qty = $_POST['cd_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $cd_total= $panelCost['cd_cost'];
  }

 $paint_budget = $cd_total*$cd_qty;
 
 $sql ="UPDATE pc_cash_demand SET cd_budget = '".$paint_budget."', cd_qty = '".$cd_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>