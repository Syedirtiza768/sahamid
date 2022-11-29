<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$cd_actual = $_POST['cd_actual'];
$cd_budget = $_POST['cd_budget'];

    $cd_profit=  $cd_budget - $cd_actual;
 
 $sql ="UPDATE pc_cash_demand SET cd_actual = '".$cd_actual."', cd_profit = '".$cd_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $cd_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>