<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$lock_actual = $_POST['lock_actual'];
$lock_budget = $_POST['lock_budget'];

    $lock_profit=  $lock_budget - $lock_actual;
 
 $sql ="UPDATE pc_cash_demand SET lock_actual = '".$lock_actual."', lock_profit = '".$lock_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $lock_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>