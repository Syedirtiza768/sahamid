<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$misc_exp_actual = $_POST['misc_exp_actual'];
$misc_exp_budget = $_POST['misc_exp_budget'];

    $misc_exp_profit=  $misc_exp_budget - $misc_exp_actual;
 
 $sql ="UPDATE pc_cash_demand SET misc_exp_actual = '".$misc_exp_actual."', misc_exp_profit = '".$misc_exp_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $misc_exp_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>