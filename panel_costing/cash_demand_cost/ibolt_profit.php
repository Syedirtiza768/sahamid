<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$ibolt_actual = $_POST['ibolt_actual'];
$ibolt_budget = $_POST['ibolt_budget'];

    $ibolt_profit=  $ibolt_budget - $ibolt_actual;
 
 $sql ="UPDATE pc_cash_demand SET ibolt_actual = '".$ibolt_actual."', ibolt_profit = '".$ibolt_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $ibolt_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>