<?php
require_once './config.php';

$sheet_selection = $_POST['sheet_selection'];
$wiring_cost = $_POST['wiring_cost'];
$labour = $_POST['labour'];
$misc_exp = $_POST['misc_exp'];
$rent = $_POST['rent'];
$Increase_percent_14 = $_POST['Increase_percent_14'];
$Increase_percent_16 = $_POST['Increase_percent_16'];
$Increase_percent_18 = $_POST['Increase_percent_18'];

session_start();
  $paint_cost = $_SESSION['paint_cost'];
  $hinges_cost = $_SESSION['hinges_cost'];
  $lock_cost = $_SESSION['lock_cost'];
  $acrylic_cost = $_SESSION['acrylic_cost'];
  $gk_cost = $_SESSION['gk_cost'];
  $ibolt_cost = $_SESSION['ibolt_cost'];
  $cd_cost = $_SESSION['cd_cost'];
  $bbr_cost = $_SESSION['bbr_cost'];
  $bbr_sleeve_cost = $_SESSION['bbr_sleeve_cost'];

$SQL = 'SELECT * FROM pc_rate';
$result = mysqli_query($conn, $SQL);
while ($panelCost = mysqli_fetch_array($result)) {
    $ms_sheet = $panelCost['ms_sheet'];
    $ss_sheet = $panelCost['ss_sheet'];
    $gi_sheet = $panelCost['gi_sheet'];
}

$total_sheet = $_SESSION['total_sheet'];

$swg14_sw = $total_sheet * 48.64;
$swg16_sw = $total_sheet * 38.4;
$swg18_sw = $total_sheet * 28;

if ($sheet_selection == 'ms_sheet') {
    $swg14_cost = $swg14_sw * $ms_sheet;
} elseif ($sheet_selection == 'ss_sheet') {
    $swg14_cost = $swg14_sw * $ss_sheet;
} elseif ($sheet_selection == 'gi_sheet') {
    $swg14_cost = $swg14_sw * $gi_sheet;
}

if ($sheet_selection == 'ms_sheet') {
    $swg16_cost = $swg16_sw * $ms_sheet;
} elseif ($sheet_selection == 'ss_sheet') {
    $swg16_cost = $swg16_sw * $ss_sheet;
} elseif ($sheet_selection == 'gi_sheet') {
    $swg16_cost = $swg16_sw * $gi_sheet;
}

if ($sheet_selection == 'ms_sheet') {
    $swg18_cost = $swg18_sw * $ms_sheet;
} elseif ($sheet_selection == 'ss_sheet') {
    $swg18_cost = $swg18_sw * $ss_sheet;
} elseif ($sheet_selection == 'gi_sheet') {
    $swg18_cost = $swg18_sw * $gi_sheet;
}


$swg14_total_cost = $swg14_cost+$paint_cost+$rent+$hinges_cost+$lock_cost+$acrylic_cost+$wiring_cost+$labour+$misc_exp+$gk_cost+$ibolt_cost+$cd_cost+$bbr_cost+$bbr_sleeve_cost;
$swg16_total_cost = $swg16_cost+$paint_cost+$rent+$hinges_cost+$lock_cost+$acrylic_cost+$wiring_cost+$labour+$misc_exp+$gk_cost+$ibolt_cost+$cd_cost+$bbr_cost+$bbr_sleeve_cost;
$swg18_total_cost = $swg18_cost+$paint_cost+$rent+$hinges_cost+$lock_cost+$acrylic_cost+$wiring_cost+$labour+$misc_exp+$gk_cost+$ibolt_cost+$cd_cost+$bbr_cost+$bbr_sleeve_cost;

$Increase_percent_14 = $Increase_percent_14/100 + 1;
$Increase_percent_16 = $Increase_percent_16/100 + 1;
$Increase_percent_18 = $Increase_percent_18/100 + 1;

$swg14_final_cost = $swg14_total_cost * $Increase_percent_14;
$swg16_final_cost = $swg16_total_cost * $Increase_percent_16;
$swg18_final_cost = $swg18_total_cost * $Increase_percent_18;



$id = $_POST['pc_id'];
$sql = "UPDATE pc_cash_demand SET ms_sheet_budget = '" . $swg14_cost . "' WHERE pc_id = '" . $id . "'";
mysqli_query($conn, $sql);

$id = $_POST['pc_id'];
$sql ="UPDATE panel_costing SET 14swg_total = '".$swg14_total_cost."', 14swg_final = '".$swg14_final_cost."',
16swg_total = '".$swg16_total_cost."',16swg_final = '".$swg16_final_cost."',
18swg_total = '".$swg18_total_cost."', 18swg_final = '".$swg18_final_cost."', 14swg_sw = '".$swg14_sw."', 16swg_sw = '".$swg16_sw."', 18swg_sw = '".$swg18_sw."',
14swg_sc = '".$swg14_cost."', 16swg_sc = '".$swg16_cost."', 18swg_sc = '".$swg18_cost."' WHERE id = '".$id."'";
if (mysqli_query($conn, $sql)) {
    echo json_encode(array("swg14_total_cost"=>$swg14_total_cost,"swg14_final_cost"=>$swg14_final_cost,
    "swg16_total_cost"=>$swg16_total_cost,"swg16_final_cost"=>$swg16_final_cost,
    "swg18_total_cost"=>$swg18_total_cost,"swg18_final_cost"=>$swg18_final_cost, "swg14_sw"=>$swg14_sw,"swg16_sw"=>$swg16_sw,"swg18_sw"=>$swg18_sw,"swg14_cost"=>$swg14_cost,
    "swg16_cost"=>$swg16_cost,"swg18_cost"=>$swg18_cost));die;

    }


   
