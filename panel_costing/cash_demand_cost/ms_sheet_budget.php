<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$ms_sheet_qty = $_POST['ms_sheet_qty'];
$SQL = 'SELECT * FROM panel_costing  WHERE id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
    if($panelCost['guage_value_cd'] == "14_swg"){
      $ms_sheet_total= $panelCost['14swg_sc'];
    }
    elseif($panelCost['guage_value_cd'] == "16_swg"){
      $ms_sheet_total= $panelCost['16swg_sc'];
    }
    elseif($panelCost['guage_value_cd'] == "18_swg"){
      $ms_sheet_total= $panelCost['18swg_sc'];
    }
  }

 $paint_budget = $ms_sheet_total*$ms_sheet_qty;
 
 $sql ="UPDATE pc_cash_demand SET ms_sheet_budget = '".$paint_budget."', ms_sheet_qty = '".$ms_sheet_qty."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paint_budget;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>