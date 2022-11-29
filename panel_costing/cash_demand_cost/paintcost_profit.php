<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$paintcost_actual = $_POST['paintcost_actual'];
$paintcost_budget = $_POST['paintcost_budget'];

    $paintcost_profit=  $paintcost_budget - $paintcost_actual;
 
 $sql ="UPDATE pc_cash_demand SET paintcost_actual = '".$paintcost_actual."', paintcost_profit = '".$paintcost_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $paintcost_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>