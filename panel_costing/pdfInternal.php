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
$sql= "SELECT * FROM pannel_costing WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$pdf = new FPDF();
$pdf-> AddPage();
$pdf->Image($image,60,30,90,0,'PNG');
$pdf->SetFont("Arial","B",25);
$pdf->Cell(0,10,"Panel Costing ($costNo)",1,1, 'C');

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
$pdf->Cell(100,10,"Multiple Gauge Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['mult_gauge_price'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"12 SWG Sheet Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['12_SWG_price'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"14 SWG Sheet Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['14_SWG_price'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"16 SWG Sheet Pricet",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['16_SWG_price'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"18 SWG Sheet Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['18_SWG_price'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"20 SWG Sheet Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row['20_SWG_price'],1,1,'C');

$sql= "SELECT * FROM cost_sheet WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row1 = mysqli_fetch_array($result);

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Panel Lock Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row1['pl_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Hinges Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row1['h_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"ACRYLIC SHEET Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row1['as_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"GAS KIT Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row1['gk_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"I-Bolt Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row1['i_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cable Duct Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row1['cd_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Paint Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row1['pc_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Cost Price in Multiple Gauge",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row1['cost_in_mult_gauge'],1,1,'C');

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
$pdf->Cell(90,10,$row2['bus_bar_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Tin Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row2['tin_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Seleeve Cost",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row2['sleeve_cost'],1,1,'C');

$pdf->SetFont("Arial","B",16);
$pdf->Cell(100,10,"Bus Bar Price",1,0,'C');
$pdf->SetFont("Arial","",16);
$pdf->Cell(90,10,$row2['bus_bar_price'],1,1,'C');

$sql= "SELECT panel_id FROM panelcostingmodifications WHERE costNo = $costNo";
$result = mysqli_query($conn, $sql);
$row112 = mysqli_fetch_array($result);
$id= $row112['panel_id'];
$sql= "SELECT * FROM panelcostingmodifications WHERE panel_id = $id";
$result = mysqli_query($conn, $sql);
$pdf->SetXY(10, 440);
$pdf->SetFont("Arial","B",25);
$pdf->Cell(0,10,"Changes History",1,1);
while($row3 = mysqli_fetch_array($result)){
$pdf->SetFont("Arial","",10);
$pdf->Cell(100,10,"* ".$row3['slidesEdited'].$row3['pc_description']." (". $row3['updateDate'].")",0,1);
}
$pdf->Output();
}
?>