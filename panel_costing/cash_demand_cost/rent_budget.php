<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$rent_qty = $_POST['rent_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $rent_total= $panelCost['rent'];
  }

 $paint_budget = $rent_total*$rent_qty;
 
 $sql ="UPDATE pc_cash_demand SET rent_budget = '".$paint_budget."', rent_qty = '".$rent_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>