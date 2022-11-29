<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$gaskit_actual = $_POST['gaskit_actual'];
$gaskit_budget = $_POST['gaskit_budget'];

    $gaskit_profit=  $gaskit_budget - $gaskit_actual;
 
 $sql ="UPDATE pc_cash_demand SET gaskit_actual = '".$gaskit_actual."', gaskit_profit = '".$gaskit_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $gaskit_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>