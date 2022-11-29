<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$misc_exp_qty = $_POST['misc_exp_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $misc_exp_total= $panelCost['misc_exp'];
  }

 $paint_budget = $misc_exp_total*$misc_exp_qty;
 
 $sql ="UPDATE pc_cash_demand SET misc_exp_budget = '".$paint_budget."', misc_exp_qty = '".$misc_exp_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>