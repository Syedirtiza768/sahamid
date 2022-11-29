<?php
require_once './config.php';
$gk_qty = $_POST['gk_qty'];
$SQL = 'SELECT * FROM pc_rate';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    $gas_kit = $panelCost['gas_kit'];
}
if (!empty($gk_qty)) {
    $gk_cost = $gk_qty * $gas_kit;
}
$id = $_POST['pc_id'];
$sql = "UPDATE pc_cash_demand SET gaskit_budget = '" . $gk_cost . "' WHERE pc_id = '" . $id . "'";
mysqli_query($conn, $sql);

session_start();
$_SESSION['gk_cost'] = $gk_cost;

// session_start();
// date_default_timezone_set("Asia/Kargkhi");
//  $date = date('d-m-y h:i:s');
// $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// if (mysqli_query($conn, $sql)) {
// }
// else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
//   }


$sql = "UPDATE panel_costing SET gk_cost = '" . $gk_cost . "' WHERE id = '" . $id . "'";
if (mysqli_query($conn, $sql)) {
    echo $gk_cost;
}
