<?php
require_once './config.php';
$pc_id = $_POST['pc_id'];
$paintcost_budget = $_POST['paintcost_budget'];
$rent_budget = $_POST['rent_budget'];
$misc_exp_budget = $_POST['misc_exp_budget'];
$labour_budget = $_POST['labour_budget'];
$bbr_sleeve_budget = $_POST['bbr_sleeve_budget'];
$ms_sheet_budget = $_POST['ms_sheet_budget'];
$cable_budget = $_POST['cable_budget'];
$hinges_budget = $_POST['hinges_budget'];
$lock_budget = $_POST['lock_budget'];
$acrylic_budget = $_POST['acrylic_budget'];
$gaskit_budget = $_POST['gaskit_budget'];
$cd_budget = $_POST['cd_budget'];
$ibolt_budget = $_POST['ibolt_budget'];

$sum = $paintcost_budget + $rent_budget + $misc_exp_budget + $labour_budget + $bbr_sleeve_budget + $ms_sheet_budget + $cable_budget + $hinges_budget +
    $lock_budget + $acrylic_budget + $gaskit_budget + $cd_budget + $ibolt_budget;

$sql = "UPDATE pc_cash_demand SET cashdemand_total = '" . $sum . "' WHERE pc_id = '" . $pc_id . "'";
if (mysqli_query($conn, $sql)) {
    echo $sum;
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
