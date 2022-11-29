<?php
require_once './config.php';
$lock_model = $_POST['lock_model'];
$lock_qty = $_POST['lock_qty'];
$SQL = 'SELECT * FROM pc_rate';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    $ms_408 = $panelCost['ms_408'];
    $ms_480 = $panelCost['ms_480'];
    $bnl_22 = $panelCost['bnl_22'];
    $pl_130 = $panelCost['pl_130'];
    $pl_150 = $panelCost['pl_150'];
}
if ($lock_model == 'ms_408') {
    $lock_cost = $lock_qty * $ms_408;
} elseif ($lock_model == 'ms_480') {
    $lock_cost = $lock_qty * $ms_480;
} elseif ($lock_model == 'bnl_22') {
    $lock_cost = $lock_qty * $bnl_22;
} elseif ($lock_model == 'pl_130') {
    $lock_cost = $lock_qty * $pl_130;
} elseif ($lock_model == 'pl_150') {
    $lock_cost = $lock_qty * $pl_150;
}

$id = $_POST['pc_id'];
$sql = "UPDATE pc_cash_demand SET lock_budget = '" . $lock_cost . "' WHERE pc_id = '" . $id . "'";
mysqli_query($conn, $sql);


session_start();
$_SESSION['lock_cost'] = $lock_cost;


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
$sql = "UPDATE panel_costing SET lock_cost = '" . $lock_cost . "' WHERE id = '" . $id . "'";
if (mysqli_query($conn, $sql)) {
    echo $lock_cost;
}
