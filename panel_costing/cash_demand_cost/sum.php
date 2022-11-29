<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];

$SQL = 'SELECT * FROM pc_cash_demand  WHERE pc_id= "' .$pc_id. '"';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
      $paintcost_budget= $panelCost['paintcost_budget'];
      $rent_budget= $panelCost['rent_budget'];
      $misc_exp_budget= $panelCost['misc_exp_budget'];
      $labour_budget= $panelCost['labour_budget'];
      $bbr_sleeve_budget= $panelCost['bbr_sleeve_budget'];
      $ms_sheet_budget= $panelCost['ms_sheet_budget'];
      $cable_budget= $panelCost['cable_budget'];
      $hinges_budget= $panelCost['hinges_budget'];
      $lock_budget= $panelCost['lock_budget'];
      $acrylic_budget= $panelCost['acrylic_budget'];
      $gaskit_budget= $panelCost['gaskit_budget'];
      $cd_budget= $panelCost['cd_budget'];
      $ibolt_budget= $panelCost['ibolt_budget'];
  }

 $sum= $paintcost_budget+$rent_budget+$misc_exp_budget+$labour_budget+$bbr_sleeve_budget+$ms_sheet_budget+$cable_budget+$hinges_budget+
 $lock_budget+$acrylic_budget+$gaskit_budget+$cd_budget+$ibolt_budget;
 
 $sql ="UPDATE pc_cash_demand SET cashdemand_total = '".$sum."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $sum;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }
?>