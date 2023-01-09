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

$bb_dimension_five = $_POST['bb_dimension_five'];
$busbar_qty_five = $_POST['busbar_qty_five'];
$busbar_weight_five = $_POST['busbar_weight_five'];
$busbar_sleeve_five = $_POST['busbar_sleeve_five'];

$bb_dimension_six = $_POST['bb_dimension_six'];
$busbar_qty_six = $_POST['busbar_qty_six'];
$busbar_weight_six = $_POST['busbar_weight_six'];
$busbar_sleeve_six = $_POST['busbar_sleeve_six'];

$bb_dimension_seven = $_POST['bb_dimension_seven'];
$busbar_qty_seven = $_POST['busbar_qty_seven'];
$busbar_weight_seven = $_POST['busbar_weight_seven'];
$busbar_sleeve_seven = $_POST['busbar_sleeve_seven'];

$bb_dimension_eight = $_POST['bb_dimension_eight'];
$busbar_qty_eight = $_POST['busbar_qty_eight'];
$busbar_weight_eight = $_POST['busbar_weight_eight'];
$busbar_sleeve_eight = $_POST['busbar_sleeve_eight'];

$bb_dimension_nine = $_POST['bb_dimension_nine'];
$busbar_qty_nine = $_POST['busbar_qty_nine'];
$busbar_weight_nine = $_POST['busbar_weight_nine'];
$busbar_sleeve_nine = $_POST['busbar_sleeve_nine'];

$bb_dimension_ten = $_POST['bb_dimension_ten'];
$busbar_qty_ten = $_POST['busbar_qty_ten'];
$busbar_weight_ten = $_POST['busbar_weight_ten'];
$busbar_sleeve_ten = $_POST['busbar_sleeve_ten'];

$bb_dimension_eleven = $_POST['bb_dimension_eleven'];
$busbar_qty_eleven = $_POST['busbar_qty_eleven'];
$busbar_weight_eleven = $_POST['busbar_weight_eleven'];
$busbar_sleeve_eleven = $_POST['busbar_sleeve_eleven'];

$bb_dimension_twelve = $_POST['bb_dimension_twelve'];
$busbar_qty_twelve = $_POST['busbar_qty_twelve'];
$busbar_weight_twelve = $_POST['busbar_weight_twelve'];
$busbar_sleeve_twelve = $_POST['busbar_sleeve_twelve'];

$bb_dimension_thirteen = $_POST['bb_dimension_thirteen'];
$busbar_qty_thirteen = $_POST['busbar_qty_thirteen'];
$busbar_weight_thirteen = $_POST['busbar_weight_thirteen'];
$busbar_sleeve_thirteen = $_POST['busbar_sleeve_thirteen'];

$bb_dimension_fourteen = $_POST['bb_dimension_fourteen'];
$busbar_qty_fourteen = $_POST['busbar_qty_fourteen'];
$busbar_weight_fourteen = $_POST['busbar_weight_fourteen'];
$busbar_sleeve_fourteen = $_POST['busbar_sleeve_fourteen'];

$bb_dimension_fifteen = $_POST['bb_dimension_fifteen'];
$busbar_qty_fifteen = $_POST['busbar_qty_fifteen'];
$busbar_weight_fifteen = $_POST['busbar_weight_fifteen'];
$busbar_sleeve_fifteen = $_POST['busbar_sleeve_fifteen'];
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

if($busbar_weight_five == ""){
    $busbar_weight_five = 0;
}

if($busbar_weight_six == ""){
    $busbar_weight_six = 0;
}

if($busbar_weight_seven == ""){
    $busbar_weight_seven = 0;
}

if($busbar_weight_eight == ""){
    $busbar_weight_eight = 0;
}

if($busbar_weight_nine == ""){
    $busbar_weight_nine = 0;
}

if($busbar_weight_ten == ""){
    $busbar_weight_ten = 0;
}

if($busbar_weight_eleven == ""){
    $busbar_weight_eleven = 0;
}

if($busbar_weight_twelve == ""){
    $busbar_weight_twelve = 0;
}

if($busbar_weight_thirteen == ""){
    $busbar_weight_thirteen = 0;
}

if($busbar_weight_fourteen == ""){
    $busbar_weight_fourteen = 0;
}

if($busbar_weight_fifteen == ""){
    $busbar_weight_fifteen = 0;
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
if($busbar_sleeve_five == ""){
    $busbar_sleeve_five = 0;
}
if($busbar_sleeve_six == ""){
    $busbar_sleeve_six = 0;
}
if($busbar_sleeve_seven == ""){
    $busbar_sleeve_seven = 0;
}
if($busbar_sleeve_eight == ""){
    $busbar_sleeve_eight = 0;
}
if($busbar_sleeve_nine == ""){
    $busbar_sleeve_nine = 0;
}
if($busbar_sleeve_ten == ""){
    $busbar_sleeve_ten = 0;
}
if($busbar_sleeve_eleven == ""){
    $busbar_sleeve_eleven = 0;
}
if($busbar_sleeve_twelve == ""){
    $busbar_sleeve_twelve = 0;
}
if($busbar_sleeve_thirteen == ""){
    $busbar_sleeve_thirteen = 0;
}
if($busbar_sleeve_fourteen == ""){
    $busbar_sleeve_fourteen = 0;
}
if($busbar_sleeve_fifteen == ""){
    $busbar_sleeve_fifteen = 0;
}

$SQL = 'SELECT * FROM pc_rate';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    $bus_bar = $panelCost['bus_bar'];
}


$weight_kg = $busbar_weight + $busbar_weight_two + $busbar_weight_three + $busbar_weight_four + $busbar_weight_five + $busbar_weight_six +
$busbar_weight_fifteen + $busbar_weight_seven + $busbar_weight_eight + $busbar_weight_nine + $busbar_weight_ten
+ $busbar_weight_eleven + $busbar_weight_twelve + $busbar_weight_thirteen + $busbar_weight_fourteen;
$total_cost = $weight_kg*$bus_bar;
$sleeve_cost = $busbar_sleeve + $busbar_sleeve_two + $busbar_sleeve_three +  $busbar_sleeve_four + $busbar_sleeve_five
+ $busbar_sleeve_six + $busbar_sleeve_seven + $busbar_sleeve_eight + $busbar_sleeve_nine + $busbar_sleeve_ten
+ $busbar_sleeve_eleven + $busbar_sleeve_twelve + $busbar_sleeve_thirteen + $busbar_sleeve_fourteen + $busbar_sleeve_fifteen;

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
