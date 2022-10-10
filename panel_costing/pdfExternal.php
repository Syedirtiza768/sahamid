<?php
session_start();
if(!empty($_POST['costNo'])){
    $value = $_POST['costNo'];
    $_SESSION["costNo"] = $_POST['costNo'];
    echo $value;
}
else{
$costNo= $_SESSION["costNo"];
$image = "img/solar.png";
include_once('cost/config.php');
require("fpdf/fpdf.php");
$pdf = new FPDF();
$sql= "SELECT * FROM pannel_costing WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$pdf-> AddPage();
$pdf->Image($image,60,30,90,0,'PNG');
$pdf->SetFont("Arial","B",25);
$pdf->Cell(0,10,"Panel Costing. ($costNo)",0,1, 'C');
$pdf->SetFont("Arial","B",20);
$pdf->Cell(0,8,"(12 SWG Cost Sheet)",0,1, 'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Multiple Gauge's Total Weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['gauges_total_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['total_SF'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet By Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['s_by_sf'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet Consume",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['sheet_consume'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$mult_g_p = number_format($row['mult_gauge_price']);
$pdf->Cell(100,10,"Multiple Gauge Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$mult_g_p,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"12 SWG Sheet Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$t_SWG_price = number_format($row['12_SWG_price']);
$pdf->Cell(90,10,$t_SWG_price,1,1,'C');

$sql= "SELECT * FROM cost_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row1 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Panel Lock Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pl_cost = number_format($row1['pl_cost']);
$pdf->Cell(90,10,$pl_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Hinges Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$h_cost = number_format($row1['h_cost']);
$pdf->Cell(90,10,$h_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"ACRYLIC SHEET Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$as_cost = number_format($row1['as_cost']);
$pdf->Cell(90,10,$as_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"GAS KIT Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$gk_cost = number_format($row1['gk_cost']);
$pdf->Cell(90,10,$gk_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"I-Bolt Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$i_cost = number_format($row1['i_cost']);
$pdf->Cell(90,10,$i_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cable Duct Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$cd_cost = number_format($row1['cd_cost']);
$pdf->Cell(90,10,$cd_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Paint Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pc_cost = number_format($row1['pc_cost']);
$pdf->Cell(90,10,$pc_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Cost In 12 SWG",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_12_SWG = number_format($row1['cost_12_SWG']);
$pdf->Cell(90,10,$cost_12_SWG,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cost Price in Multiple Gauge",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_in_mult_gauge = number_format($row1['cost_in_mult_gauge']);
$pdf->Cell(90,10,$cost_in_mult_gauge,1,1,'C');

$sql= "SELECT * FROM bus_bar_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row2 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar total weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row2['bus_bar_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_cost = number_format($row2['bus_bar_cost']);
$pdf->Cell(90,10,$bus_bar_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Tin Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$tin_cost = number_format($row2['tin_cost']);
$pdf->Cell(90,10,$tin_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Seleeve Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$sleeve_cost = number_format($row2['sleeve_cost']);
$pdf->Cell(90,10,$sleeve_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_price = number_format($row2['bus_bar_price']);
$pdf->Cell(90,10,$bus_bar_price,1,1,'C');

$sql= "SELECT * FROM pannel_costing WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$pdf-> AddPage();
$pdf->Image($image,60,30,90,0,'PNG');
$pdf->SetFont("Arial","B",25);
$pdf->Cell(0,10,"Panel Costing. ($costNo)",0,1, 'C');
$pdf->SetFont("Arial","B",20);
$pdf->Cell(0,8,"(14 SWG Cost Sheet)",0,1, 'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Multiple Gauge's Total Weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['gauges_total_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['total_SF'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet By Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['s_by_sf'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet Consume",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['sheet_consume'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$mult_g_p = number_format($row['mult_gauge_price']);
$pdf->Cell(100,10,"Multiple Gauge Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$mult_g_p,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"14 SWG Sheet Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$f_SWG_price = number_format($row['14_SWG_price']);
$pdf->Cell(90,10,$f_SWG_price,1,1,'C');

$sql= "SELECT * FROM cost_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row1 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Panel Lock Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pl_cost = number_format($row1['pl_cost']);
$pdf->Cell(90,10,$pl_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Hinges Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$h_cost = number_format($row1['h_cost']);
$pdf->Cell(90,10,$h_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"ACRYLIC SHEET Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$as_cost = number_format($row1['as_cost']);
$pdf->Cell(90,10,$as_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"GAS KIT Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$gk_cost = number_format($row1['gk_cost']);
$pdf->Cell(90,10,$gk_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"I-Bolt Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$i_cost = number_format($row1['i_cost']);
$pdf->Cell(90,10,$i_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cable Duct Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$cd_cost = number_format($row1['cd_cost']);
$pdf->Cell(90,10,$cd_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Paint Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pc_cost = number_format($row1['pc_cost']);
$pdf->Cell(90,10,$pc_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Cost In 14 SWG",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_14_SWG = number_format($row1['cost_14_SWG']);
$pdf->Cell(90,10,$cost_14_SWG,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cost Price in Multiple Gauge",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_in_mult_gauge = number_format($row1['cost_in_mult_gauge']);
$pdf->Cell(90,10,$cost_in_mult_gauge,1,1,'C');

$sql= "SELECT * FROM bus_bar_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row2 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar total weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row2['bus_bar_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_cost = number_format($row2['bus_bar_cost']);
$pdf->Cell(90,10,$bus_bar_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Tin Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$tin_cost = number_format($row2['tin_cost']);
$pdf->Cell(90,10,$tin_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Seleeve Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$sleeve_cost = number_format($row2['sleeve_cost']);
$pdf->Cell(90,10,$sleeve_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_price = number_format($row2['bus_bar_price']);
$pdf->Cell(90,10,$bus_bar_price,1,1,'C');

$sql= "SELECT * FROM pannel_costing WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$pdf-> AddPage();
$pdf->Image($image,60,30,90,0,'PNG');
$pdf->SetFont("Arial","B",25);
$pdf->Cell(0,10,"Panel Costing. ($costNo)",0,1, 'C');
$pdf->SetFont("Arial","B",20);
$pdf->Cell(0,8,"(16 SWG Cost Sheet)",0,1, 'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Multiple Gauge's Total Weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['gauges_total_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['total_SF'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet By Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['s_by_sf'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet Consume",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['sheet_consume'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$mult_g_p = number_format($row['mult_gauge_price']);
$pdf->Cell(100,10,"Multiple Gauge Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$mult_g_p,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"16 SWG Sheet Pricet",1,0,'C');
$pdf->SetFont("Arial","",16);
$s_SWG_price = number_format($row['16_SWG_price']);
$pdf->Cell(90,10,$s_SWG_price,1,1,'C');

$sql= "SELECT * FROM cost_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row1 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Panel Lock Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pl_cost = number_format($row1['pl_cost']);
$pdf->Cell(90,10,$pl_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Hinges Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$h_cost = number_format($row1['h_cost']);
$pdf->Cell(90,10,$h_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"ACRYLIC SHEET Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$as_cost = number_format($row1['as_cost']);
$pdf->Cell(90,10,$as_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"GAS KIT Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$gk_cost = number_format($row1['gk_cost']);
$pdf->Cell(90,10,$gk_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"I-Bolt Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$i_cost = number_format($row1['i_cost']);
$pdf->Cell(90,10,$i_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cable Duct Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$cd_cost = number_format($row1['cd_cost']);
$pdf->Cell(90,10,$cd_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Paint Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pc_cost = number_format($row1['pc_cost']);
$pdf->Cell(90,10,$pc_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Cost In 16 SWG",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_16_SWG = number_format($row1['cost_16_SWG']);
$pdf->Cell(90,10,$cost_16_SWG,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cost Price in Multiple Gauge",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_in_mult_gauge = number_format($row1['cost_in_mult_gauge']);
$pdf->Cell(90,10,$cost_in_mult_gauge,1,1,'C');

$sql= "SELECT * FROM bus_bar_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row2 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar total weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row2['bus_bar_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_cost = number_format($row2['bus_bar_cost']);
$pdf->Cell(90,10,$bus_bar_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Tin Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$tin_cost = number_format($row2['tin_cost']);
$pdf->Cell(90,10,$tin_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Seleeve Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$sleeve_cost = number_format($row2['sleeve_cost']);
$pdf->Cell(90,10,$sleeve_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_price = number_format($row2['bus_bar_price']);
$pdf->Cell(90,10,$bus_bar_price,1,1,'C');

$sql= "SELECT * FROM pannel_costing WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$pdf-> AddPage();
$pdf->Image($image,60,30,90,0,'PNG');
$pdf->SetFont("Arial","B",25);
$pdf->Cell(0,10,"Panel Costing. ($costNo)",0,1, 'C');
$pdf->SetFont("Arial","B",20);
$pdf->Cell(0,8,"(18 SWG Cost Sheet)",0,1, 'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Multiple Gauge's Total Weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['gauges_total_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['total_SF'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet By Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['s_by_sf'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet Consume",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['sheet_consume'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$mult_g_p = number_format($row['mult_gauge_price']);
$pdf->Cell(100,10,"Multiple Gauge Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$mult_g_p,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"18 SWG Sheet Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$e_SWG_price = number_format($row['18_SWG_price']);
$pdf->Cell(90,10,$e_SWG_price,1,1,'C');

$sql= "SELECT * FROM cost_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row1 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Panel Lock Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pl_cost = number_format($row1['pl_cost']);
$pdf->Cell(90,10,$pl_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Hinges Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$h_cost = number_format($row1['h_cost']);
$pdf->Cell(90,10,$h_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"ACRYLIC SHEET Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$as_cost = number_format($row1['as_cost']);
$pdf->Cell(90,10,$as_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"GAS KIT Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$gk_cost = number_format($row1['gk_cost']);
$pdf->Cell(90,10,$gk_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"I-Bolt Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$i_cost = number_format($row1['i_cost']);
$pdf->Cell(90,10,$i_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cable Duct Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$cd_cost = number_format($row1['cd_cost']);
$pdf->Cell(90,10,$cd_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Paint Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pc_cost = number_format($row1['pc_cost']);
$pdf->Cell(90,10,$pc_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Cost In 18 SWG",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_18_SWG = number_format($row1['cost_18_SWG']);
$pdf->Cell(90,10,$cost_18_SWG,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cost Price in Multiple Gauge",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_in_mult_gauge = number_format($row1['cost_in_mult_gauge']);
$pdf->Cell(90,10,$cost_in_mult_gauge,1,1,'C');

$sql= "SELECT * FROM bus_bar_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row2 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar total weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row2['bus_bar_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_cost = number_format($row2['bus_bar_cost']);
$pdf->Cell(90,10,$bus_bar_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Tin Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$tin_cost = number_format($row2['tin_cost']);
$pdf->Cell(90,10,$tin_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Seleeve Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$sleeve_cost = number_format($row2['sleeve_cost']);
$pdf->Cell(90,10,$sleeve_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_price = number_format($row2['bus_bar_price']);
$pdf->Cell(90,10,$bus_bar_price,1,1,'C');

$sql= "SELECT * FROM pannel_costing WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$pdf-> AddPage();
$pdf->Image($image,60,30,90,0,'PNG');
$pdf->SetFont("Arial","B",25);
$pdf->Cell(0,10,"Panel Costing. ($costNo)",0,1, 'C');
$pdf->SetFont("Arial","B",20);
$pdf->Cell(0,8,"(20 SWG Cost Sheet)",0,1, 'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Multiple Gauge's Total Weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['gauges_total_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['total_SF'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet By Square Feet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['s_by_sf'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Sheet Consume",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['sheet_consume'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$mult_g_p = number_format($row['mult_gauge_price']);
$pdf->Cell(100,10,"Multiple Gauge Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$mult_g_p,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"20 SWG Sheet Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$ty_SWG_price = number_format($row['20_SWG_price']);
$pdf->Cell(90,10,$ty_SWG_price,1,1,'C');

$sql= "SELECT * FROM cost_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row1 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Panel Lock Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pl_cost = number_format($row1['pl_cost']);
$pdf->Cell(90,10,$pl_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Hinges Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$h_cost = number_format($row1['h_cost']);
$pdf->Cell(90,10,$h_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"ACRYLIC SHEET Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$as_cost = number_format($row1['as_cost']);
$pdf->Cell(90,10,$as_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"GAS KIT Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$gk_cost = number_format($row1['gk_cost']);
$pdf->Cell(90,10,$gk_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"I-Bolt Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$i_cost = number_format($row1['i_cost']);
$pdf->Cell(90,10,$i_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cable Duct Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$cd_cost = number_format($row1['cd_cost']);
$pdf->Cell(90,10,$cd_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Paint Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pc_cost = number_format($row1['pc_cost']);
$pdf->Cell(90,10,$pc_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Total Cost In 20 SWG",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_20_SWG = number_format($row1['cost_20_SWG']);
$pdf->Cell(90,10,$cost_20_SWG,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cost Price in Multiple Gauge",1,0,'C');
$pdf->SetFont("Arial","",16);
$cost_in_mult_gauge = number_format($row1['cost_in_mult_gauge']);
$pdf->Cell(90,10,$cost_in_mult_gauge,1,1,'C');

$sql= "SELECT * FROM bus_bar_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row2 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar total weight",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row2['bus_bar_weight'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_cost = number_format($row2['bus_bar_cost']);
$pdf->Cell(90,10,$bus_bar_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Tin Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$tin_cost = number_format($row2['tin_cost']);
$pdf->Cell(90,10,$tin_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Seleeve Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$sleeve_cost = number_format($row2['sleeve_cost']);
$pdf->Cell(90,10,$sleeve_cost,1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$bus_bar_price = number_format($row2['bus_bar_price']);
$pdf->Cell(90,10,$bus_bar_price,1,1,'C');
$pdf->Output();




}
?>