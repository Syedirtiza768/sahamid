<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$acrylic_qty = $_POST['acrylic_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $acrylic_total= $panelCost['acrylic_cost'];
  }

 $paint_budget = $acrylic_total*$acrylic_qty;
 
 $sql ="UPDATE pc_cash_demand SET acrylic_budget = '".$paint_budget."', acrylic_qty = '".$acrylic_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>