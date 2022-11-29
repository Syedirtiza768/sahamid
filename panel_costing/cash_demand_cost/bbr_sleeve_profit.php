<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$bbr_sleeve_actual = $_POST['bbr_sleeve_actual'];
$bbr_sleeve_budget = $_POST['bbr_sleeve_budget'];

    $bbr_sleeve_profit=  $bbr_sleeve_budget - $bbr_sleeve_actual;
 
 $sql ="UPDATE pc_cash_demand SET bbr_sleeve_actual = '".$bbr_sleeve_actual."', bbr_sleeve_profit = '".$bbr_sleeve_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $bbr_sleeve_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>