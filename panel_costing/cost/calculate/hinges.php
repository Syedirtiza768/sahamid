<?php
require_once './config.php';
$model = $_POST['model'];
$qty=$_POST['qty']; 
$SQL = 'SELECT * FROM pc_rate';
  $result = mysqli_query($conn, $SQL);
  while ($panelCost = mysqli_fetch_array($result)) {
    $hl_030 = $panelCost['hl_030'];
    $hl_027 = $panelCost['hl_027'];
    $hl_056 = $panelCost['hl_056'];
    $hl_051 = $panelCost['hl_051'];
  }
  if($model === "hl_030"){
    $result = $qty*$hl_030;
  }
  elseif($model === "hl_027"){
    $result = $qty*$hl_027;
  }
  elseif($model === "hl_0556"){
    $result = $qty*$hl_0556;
  }
  elseif($model === "hl_051"){
    $result = $qty*$hl_051;
  }
  
// session_start();
// date_default_timezone_set("Asia/Karachi");
//  $date = date('d-m-y h:i:s');
// $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// if (mysqli_query($conn, $sql)) {
// }
// else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
//   }

$id = $_POST['pc_id'];
$sql = "UPDATE pc_cash_demand SET hinges_budget = '" . $result . "' WHERE pc_id = '" . $id . "'";
mysqli_query($conn, $sql);


session_start();
$_SESSION['hinges_cost'] = $result;

$id = $_POST['pc_id'];
$sql ="UPDATE panel_costing SET hinges_cost = '".$result."' WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo $result;
    }

?>