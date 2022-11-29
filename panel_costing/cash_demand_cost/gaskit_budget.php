<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$gaskit_qty = $_POST['gaskit_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $gaskit_total= $panelCost['gk_cost'];
  }

 $paint_budget = $gaskit_total*$gaskit_qty;
 
 $sql ="UPDATE pc_cash_demand SET gaskit_budget = '".$paint_budget."', gaskit_qty = '".$gaskit_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>