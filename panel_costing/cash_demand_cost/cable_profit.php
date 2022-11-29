<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$cable_actual = $_POST['cable_actual'];
$cable_budget = $_POST['cable_budget'];

    $cable_profit=  $cable_budget - $cable_actual;
 
 $sql ="UPDATE pc_cash_demand SET cable_actual = '".$cable_actual."', cable_profit = '".$cable_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $cable_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>