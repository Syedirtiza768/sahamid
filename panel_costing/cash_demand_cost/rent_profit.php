<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$rent_actual = $_POST['rent_actual'];
$rent_budget = $_POST['rent_budget'];

    $rent_profit=  $rent_budget - $rent_actual;
 
 $sql ="UPDATE pc_cash_demand SET rent_actual = '".$rent_actual."', rent_profit = '".$rent_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $rent_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>