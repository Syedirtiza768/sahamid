<?php
require_once './config.php';
$cd_model = $_POST['cd_model'];
$cd_qty = $_POST['cd_qty'];
$id = $_POST['pc_id'];
$SQL = 'SELECT * FROM pc_rate';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    $tf_tf = $panelCost['tf_tf'];
    $tf_f = $panelCost['tf_f'];
    $tt_tt = $panelCost['tt_tt'];
    $f_f = $panelCost['f_f'];
    $f_s = $panelCost['f_s'];
    $s_f = $panelCost['s_f'];
    $s_s = $panelCost['s_s'];
    $e_e = $panelCost['e_e'];
    $h_h = $panelCost['h_h'];
}
if ($cd_model == "25*25") {
    $cd_cost = $cd_qty * $tf_tf;
} elseif ($cd_model == "25*40") {
    $cd_cost = $cd_qty * $tf_f;
} elseif ($cd_model == "33*33") {
    $cd_cost = $cd_qty * $tt_tt;
} elseif ($cd_model == "40*40") {
    $cd_cost = $cd_qty * $f_f;
} elseif ($cd_model == "40*60") {
    $cd_cost = $cd_qty * $f_s;
} elseif ($cd_model == "60*40") {
    $cd_cost = $cd_qty * $s_f;
} elseif ($cd_model == "60*60") {
    $cd_cost = $cd_qty * $s_s;
} elseif ($cd_model == "80*80") {
    $cd_cost = $cd_qty * $e_e;
} elseif ($cd_model == "100*100") {
    $cd_cost = $cd_qty * $h_h;
}
$sql ="UPDATE pc_cash_demand SET cd_budget = '".$cd_cost."' WHERE pc_id = '".$id."'";
mysqli_query($conn, $sql);

session_start();
$_SESSION['cd_cost'] = $cd_cost;



// session_start();
// date_default_timezone_set("Asia/Kargkhi");
//  $date = date('d-m-y h:i:s');
// $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// if (mysqli_query($conn, $sql)) {
// }
// else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
//   }

$sql = "UPDATE panel_costing SET cd_cost = '" . $cd_cost . "' WHERE id = '" . $id . "'";
if (mysqli_query($conn, $sql)) {
    echo $cd_cost;
}
