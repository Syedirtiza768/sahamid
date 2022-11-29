<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$labour_qty = $_POST['labour_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $labour_total= $panelCost['labour'];
  }

 $paint_budget = $labour_total*$labour_qty;
 
 $sql ="UPDATE pc_cash_demand SET labour_budget = '".$paint_budget."', labour_qty = '".$labour_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>