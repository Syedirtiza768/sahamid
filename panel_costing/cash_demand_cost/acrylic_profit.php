<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$acrylic_actual = $_POST['acrylic_actual'];
$acrylic_budget = $_POST['acrylic_budget'];

    $acrylic_profit=  $acrylic_budget - $acrylic_actual;
 
 $sql ="UPDATE pc_cash_demand SET acrylic_actual = '".$acrylic_actual."', acrylic_profit = '".$acrylic_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $acrylic_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>