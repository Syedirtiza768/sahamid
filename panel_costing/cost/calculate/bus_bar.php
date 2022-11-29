<?php
require_once './config.php';
$bb_dimension = $_POST['bb_dimension'];
$busbar_qty = $_POST['busbar_qty'];
$busbar_weight = $_POST['busbar_weight'];
$busbar_sleeve = $_POST['busbar_sleeve'];

$bb_dimension_two = $_POST['bb_dimension_two'];
$busbar_qty_two = $_POST['busbar_qty_two'];
$busbar_weight_two = $_POST['busbar_weight_two'];
$busbar_sleeve_two = $_POST['busbar_sleeve_two'];

$bb_dimension_three = $_POST['bb_dimension_three'];
$busbar_qty_three = $_POST['busbar_qty_three'];
$busbar_weight_three = $_POST['busbar_weight_three'];
$busbar_sleeve_three = $_POST['busbar_sleeve_three'];

$bb_dimension_four = $_POST['bb_dimension_four'];
$busbar_qty_four = $_POST['busbar_qty_four'];
$busbar_weight_four = $_POST['busbar_weight_four'];
$busbar_sleeve_four = $_POST['busbar_sleeve_four'];
$id = $_POST['pc_id'];


if($busbar_weight == ""){
    $busbar_weight = 0;
}
if($busbar_weight_two == ""){
    $busbar_weight_two = 0;
}
if($busbar_weight_three == ""){
    $busbar_weight_three = 0;
}
if($busbar_weight_four == ""){
    $busbar_weight_four = 0;
}

if($busbar_sleeve == ""){
    $busbar_sleeve = 0;
}
if($busbar_sleeve_two == ""){
    $busbar_sleeve_two = 0;
}
if($busbar_sleeve_three == ""){
    $busbar_sleeve_three = 0;
}
if($busbar_sleeve_four == ""){
    $busbar_sleeve_four = 0;
}

$SQL = 'SELECT * FROM pc_rate';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    $bus_bar = $panelCost['bus_bar'];
}


$weight_kg = $busbar_weight + $busbar_weight_two + $busbar_weight_three + $busbar_weight_four;
$total_cost = $weight_kg*$bus_bar;
$sleeve_cost = $busbar_sleeve + $busbar_sleeve_two + $busbar_sleeve_three + $busbar_sleeve_four;

$bbr_sleeve = $sleeve_cost + $total_cost;
$sql ="UPDATE pc_cash_demand SET bbr_sleeve_budget = '".$bbr_sleeve."' WHERE pc_id = '".$id."'";
mysqli_query($conn, $sql);

session_start();
$_SESSION['bbr_cost'] = $total_cost;
$_SESSION['bbr_sleeve_cost'] = $sleeve_cost;



// // session_start();
// // date_default_timezone_set("Asia/Kargkhi");
// //  $date = date('d-m-y h:i:s');
// // $sql = "INSERT INTO panelcostingmodifications(panel_id,slidesEdited,updateDate,pc_description)VALUES('$id','Front Door-1 ','$date','Updated by ".$_SESSION['UsersRealName']." at')";
// // if (mysqli_query($conn, $sql)) {
// // }
// // else {
// //     echo "Error: " . $sql . "<br>" . $conn->error;
// //   }

$sql = "UPDATE panel_costing SET busbar_weight = '" . $weight_kg . "' , busbar_cost = '" . $total_cost . "' , busbar_sleeve = '" . $sleeve_cost . "' WHERE id = '" . $id . "'";
if (mysqli_query($conn, $sql)) {
    echo json_encode(array("weight_kg"=>$weight_kg,"total_cost"=>$total_cost,"sleeve_cost"=>$sleeve_cost));die;
}
