<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$labour_actual = $_POST['labour_actual'];
$labour_budget = $_POST['labour_budget'];

    $labour_profit=  $labour_budget - $labour_actual;
 
 $sql ="UPDATE pc_cash_demand SET labour_actual = '".$labour_actual."', labour_profit = '".$labour_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $labour_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>