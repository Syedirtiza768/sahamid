<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');

include('includes/PDFStarter.php');

if (isset($RequestNo)) {
	$_GET['RequestNo']=$RequestNo;
}

$pdf->addInfo('Title', _('Inwards Gate Pass') );
$pdf->addInfo('Subject', _('Inwards Gate Pass') . ' # ' . $_GET['RequestNo']);
$pdf->SetFont('helvetica', '', 10, '', true);
$pdf->SetAutoPageBreak(TRUE, 15);
$FontSize=10;
$PageNumber=1;
$line_height=30;

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$query = NULL;
$query1 = NULL;
$query2 = NULL;
$sql = "SELECT * FROM ogpsalescaseref WHERE dispatchid =" . $_GET['RequestNo'] . " ";
$result = DB_query($sql,$db);
If (DB_num_rows($result)!=0){
	$query1 = "ogpsalescaseref.salescaseref,";
	$query2 = "ogpsalescaseref.requestedby,";
 $query = "INNER JOIN ogpsalescaseref ON igp.dispatchid=ogpsalescaseref.dispatchid";
}

$sql = "SELECT * FROM ogpcsvref WHERE dispatchid =" . $_GET['RequestNo'] . " ";
$result = DB_query($sql,$db);
If (DB_num_rows($result)!=0){
	$query1 = "ogpcsvref.csv,";
	$query2 = "ogpcsvref.requestedby,";
 $query = "INNER JOIN ogpcsvref ON igp.dispatchid=ogpcsvref.dispatchid";
}

$sql = "SELECT * FROM ogpcrvref WHERE dispatchid =" . $_GET['RequestNo'] . " ";
$result = DB_query($sql,$db);
If (DB_num_rows($result)!=0){
	$query1 = "ogpcrvref.crv,";
	$query2 = "ogpcrvref.requestedby,";
 $query = "INNER JOIN ogpcrvref ON igp.dispatchid=ogpcrvref.dispatchid";
}

$sql = "SELECT * FROM ogpmporef WHERE dispatchid =" . $_GET['RequestNo'] . " ";
$result = DB_query($sql,$db);
If (DB_num_rows($result)!=0){
	$query1 = "ogpmporef.mpo,";
	$query2 = "ogpmporef.requestedby,";
 $query = "INNER JOIN ogpmporef ON igp.dispatchid=ogpmporef.dispatchid";
}


echo $sql = "SELECT DISTINCT
igp.receivedfrom, 
igp.storemanager,
igp.loccode,
igp.despatchdate,
igp.narrative,
". $query1 ."
". $query2 ."
locations.locationname,
		igpitems.dispatchid,
		igpitems.stockid,
		igpitems.quantity, 
		stockmaster.description,
			   stockmaster.mnfCode,
			   stockmaster.mnfpno,
			   stockmaster.stockid,
		manufacturers.manufacturers_name,
		 stockmaster.decimalplaces
		FROM stockmaster inner join manufacturers on stockmaster.brand= manufacturers.manufacturers_id
		INNER JOIN igpitems ON igpitems.stockid=stockmaster.stockid
		
		INNER JOIN igp ON igp.dispatchid=igpitems.dispatchid
		INNER JOIN locations ON igp.loccode=locations.loccode
		". $query ."
		WHERE igp.dispatchid=" . $_GET['RequestNo'] . "";

		
$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
If (DB_num_rows($result)==0){

	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);
$sql2 = "SELECT * FROM igp where igp.dispatchid='" . $_GET['RequestNo'] . "' ";
$result2 = DB_query($sql2,$db, $ErrMsg, $DbgMsg);
$TransferRow2 = DB_fetch_array($result2);
$sql3 = "select * from igp where igp.despatchdate= ".$TransferRow2['despatchdate'];
$result2 = DB_query($sql2,$db, $ErrMsg, $DbgMsg);
$TransferRow2 = DB_fetch_array($result2);
$incpart = 0;
$totalRows = count($result2);

$SQL = "SELECT parchino FROM bazar_parchi WHERE igp_id=".$TransferRow['dispatchid'];
$parchino = mysqli_fetch_assoc(mysqli_query($db, $SQL))['parchino'];

$incpart = sprintf("%04d",$totalRows+2);
include ('includes/PDFIGPHeader.inc');

$line  =0;
$html2.= '<br><br><br><div style="padding:10px;"><table border = "1" width = "100%"><tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Item Code'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Model No'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Part No'.'</td>';
$html2.= '<td width = "20%" align = "center">'.'Brand'.'</td>';
$html2.= '<td width = "40%">'.'Description'.'</td>';
$html2.= '<td width = "5%" align = "center">'.'Quantity'.'</td></tr>';

do{
	$line++;
$html2.= '<tr><td width = "5%" align = "center">'.$line.'</td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['stockid'].'</center></td>';

$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
$html2.= '<td width = "20%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
$html2.= '<td width = "40%">'.$TransferRow['description'].'</td>';
$html2.= '<td width = "5%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td></tr>';


} while ($TransferRow = DB_fetch_array($result));
$html2.='</table></div><div style = "height:500px;"></div>';
$html2=utf8_decode($html2);
$pdf->SetFont('helvetica', '', 10, '', true);
$pdf->writeHTML($html2, true, false, true, false, '');
$html3 = '<div><table width = "100%" height = "auto">
<tr><td><b>Sender:</b></td><td></td><td><b>Receiver:</b></td><td></td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Signatures:</b></td><td>______________________</td><td><b>Signatures:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Name:</b></td><td>______________________</td><td><b>Name:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Designation:</b></td><td>______________________</td><td><b>Designation:</b></td><td>______________________</td></tr>

</table></div>';
$html3=utf8_decode($html3);
$pdf->writeHTML($html3, true, false, true, false, '');

 ob_end_clean();
$pdf->OutputD($_SESSION['DatabaseName'] . '_IGP_' . $_GET['RequestNo'] . '.pdf');
$pdf->__destruct();
?>