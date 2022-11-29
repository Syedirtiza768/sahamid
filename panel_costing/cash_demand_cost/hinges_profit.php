<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$hinges_actual = $_POST['hinges_actual'];
$hinges_budget = $_POST['hinges_budget'];

    $hinges_profit=  $hinges_budget - $hinges_actual;
 
 $sql ="UPDATE pc_cash_demand SET hinges_actual = '".$hinges_actual."', hinges_profit = '".$hinges_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $hinges_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>