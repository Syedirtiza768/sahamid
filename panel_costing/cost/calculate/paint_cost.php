<?php
require_once './config.php';
$pc_h = $_POST['pc_h'];
$pc_w = $_POST['pc_w'];
$pc_d = $_POST['pc_d'];
$id = $_POST['pc_id'];
$paint_cost_model = $_POST['paint_cost_model'];
$SQL = 'SELECT * FROM pc_rate';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    $h_7035 = $panelCost['h_7035'];
    $h_7032 = $panelCost['h_7032'];
    $tf_7035 = $panelCost['tf_7035'];
    $tf_7032 = $panelCost['tf_7032'];
    $thf_7035 = $panelCost['thf_7035'];
    $thf_7032 = $panelCost['thf_7032'];
    $f_7035 = $panelCost['f_7035'];
    $f_7032 = $panelCost['f_7032'];
    $s_7035 = $panelCost['s_7035'];
    $s_7032 = $panelCost['s_7032'];
    $n_7035 = $panelCost['n_7035'];
    $n_7032 = $panelCost['n_7032'];
}
if ($pc_d <= 200 && $paint_cost_model === "7035") {
    $paintcost_total = $h_7035 * $pc_h * $pc_w / 645.16 / 144;
} elseif ($pc_d <= 200 && $paint_cost_model === "7032") {
    $paintcost_total = $h_7032 * $pc_h * $pc_w / 645.16 / 144;
} 
elseif ($pc_d <= 300 && $paint_cost_model === "7035") {
    $paintcost_total = $tf_7035 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 300 && $paint_cost_model === "7032") {
    $paintcost_total = $tf_7032 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 450 && $paint_cost_model === "7035") {
    $paintcost_total = $thf_7035 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 450 && $paint_cost_model === "7032") {
    $paintcost_total = $thf_7032 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 600 && $paint_cost_model === "7035") {
    $paintcost_total = $f_7035 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 600 && $paint_cost_model === "7032") {
    $paintcost_total = $f_7032 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 800 && $paint_cost_model === "7035") {
    $paintcost_total = $s_7035 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 800 && $paint_cost_model === "7032") {
    $paintcost_total = $s_7032 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 1200 && $paint_cost_model === "7035") {
    $paintcost_total = $n_7035 * $pc_h * $pc_w / 645.16 / 144;
}
elseif ($pc_d <= 1200 && $paint_cost_model === "7032") {
    $paintcost_total = $n_7032 * $pc_h * $pc_w / 645.16 / 144;
}

$id = $_POST['pc_id'];
$sql = "UPDATE pc_cash_demand SET paintcost_budget = '" . $paintcost_total . "' WHERE pc_id = '" . $id . "'";
mysqli_query($conn, $sql);

session_start();
$_SESSION['paint_cost'] = $paintcost_total;


// session_start();
// date_default_timezone_set("Asia/Karachi");
//  $date = date('d-m-y h:i:s');
// $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// if (mysqli_query($conn, $sql)) {
// }
// else {
//     echo "Error: " . $sql . "<br>" . $conn->error;
//   }


$sql ="UPDATE panel_costing SET paintcost_total = '".$paintcost_total."' WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo $paintcost_total;
    }
