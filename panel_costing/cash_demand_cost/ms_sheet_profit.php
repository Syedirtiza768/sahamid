<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$ms_sheet_actual = $_POST['ms_sheet_actual'];
$ms_sheet_budget = $_POST['ms_sheet_budget'];

    $ms_sheet_profit=  $ms_sheet_budget - $ms_sheet_actual;
 
 $sql ="UPDATE pc_cash_demand SET ms_sheet_actual = '".$ms_sheet_actual."', ms_sheet_profit = '".$ms_sheet_profit."' WHERE pc_id = '".$pc_id."'";
 if (mysqli_query($conn, $sql)) {
  echo $ms_sheet_profit;
  }
  else {
      echo "Error: " . $sql . "<br>" . $conn->error;
    }


?>