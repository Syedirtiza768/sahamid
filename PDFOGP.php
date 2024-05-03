<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');

include('includes/PDFStarter.php');

if (isset($RequestNo)) {
	$_GET['RequestNo']=$RequestNo;
}

$pdf->addInfo('Title', _('Outward Gate Pass') );
$pdf->addInfo('Subject', _('Outward Gate Pass') . ' # ' . $_GET['RequestNo']);
$pdf->SetAutoPageBreak(TRUE, 15);

$FontSize=10;
$PageNumber=1;
$line_height=30;
$pdf->SetFont('helvetica', '', 10, '', true);

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
 $query = "INNER JOIN ogpsalescaseref ON posdispatch.dispatchid=ogpsalescaseref.dispatchid";
}

$sql = "SELECT * FROM ogpcsvref WHERE dispatchid =" . $_GET['RequestNo'] . " ";
$result = DB_query($sql,$db);
If (DB_num_rows($result)!=0){
	$query1 = "ogpcsvref.csv,";
	$query2 = "ogpcsvref.requestedby,";
 $query = "INNER JOIN ogpcsvref ON posdispatch.dispatchid=ogpcsvref.dispatchid";
}

$sql = "SELECT * FROM ogpcrvref WHERE dispatchid =" . $_GET['RequestNo'] . " ";
$result = DB_query($sql,$db);
If (DB_num_rows($result)!=0){
	$query1 = "ogpcrvref.crv,";
	$query2 = "ogpcrvref.requestedby,";
 $query = "INNER JOIN ogpcrvref ON posdispatch.dispatchid=ogpcrvref.dispatchid";
}

$sql = "SELECT * FROM ogpmporef WHERE dispatchid =" . $_GET['RequestNo'] . " ";
$result = DB_query($sql,$db);
If (DB_num_rows($result)!=0){
	$query1 = "ogpmporef.mpo,";
	$query2 = "ogpmporef.requestedby,";
 $query = "INNER JOIN ogpmporef ON posdispatch.dispatchid=ogpmporef.dispatchid";
}

echo $sql = "SELECT DISTINCT
posdispatch.deliveredto,
posdispatch.loccode,
posdispatch.despatchdate,
posdispatch.storemanager,
posdispatch.narrative,
locations.locationname,
". $query1 ."
". $query2 ."
		posdispatchitems.dispatchid,
		posdispatchitems.stockid,
		posdispatchitems.quantity, 
		stockmaster.description,
			   stockmaster.mnfCode,
			   stockmaster.mnfpno,
		 manufacturers.manufacturers_name,
		 stockmaster.decimalplaces
		FROM stockmaster inner join manufacturers on stockmaster.brand= manufacturers.manufacturers_id
		INNER JOIN posdispatchitems ON posdispatchitems.stockid=stockmaster.stockid
		INNER JOIN posdispatch ON posdispatch.dispatchid=posdispatchitems.dispatchid
		INNER JOIN locations ON posdispatch.loccode=locations.loccode
		". $query ."
		WHERE posdispatch.dispatchid=" . $_GET['RequestNo'] . " ";

		
$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
If (DB_num_rows($result)==0){

	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);
/*$sql2 = "SELECT * FROM posdispatch where posdispatch.dispatchid='" . $_GET['RequestNo'] . "' ";
$result2 = DB_query($sql2,$db, $ErrMsg, $DbgMsg);
$TransferRow2 = DB_fetch_array($result2);
$sql3 = "select count(*) as count from posdispatch where posdispatch.despatchdate= ".$TransferRow2['despatchdate'];
$result3 = DB_query($sql3,$db, $ErrMsg, $DbgMsg);
$TransferRow3 = DB_fetch_array($result3);
$incpart = 0;
$totalRows = $TransferRow3['count'];
*/
$incpart = sprintf("%04d",$totalRows+2);
include ('includes/PDFOGPHeader.inc');

$line  =0;
$html2.= '<br><br><br><div style="padding:10px;"><table border = "1" width = "100%"><tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
$html2.= '<td width = "15%" align = "center">'.'Model No'.'</td>';
$html2.= '<td width = "15%" align = "center">'.'Part No'.'</td>';
$html2.= '<td width = "20%" align = "center">'.'Brand'.'</td>';
$html2.= '<td width = "40%">'.'Description'.'</td>';
$html2.= '<td width = "5%" align = "center">'.'quantity'.'</td></tr>';

do{
	$line++;
$html2.= '<tr><td width = "5%" align = "center">'.$line.'</td>';
$html2.= '<td width = "15%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
$html2.= '<td width = "15%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
$html2.= '<td width = "20%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
$html2.= '<td width = "40%">'.$TransferRow['description'].'</td>';
$html2.= '<td width = "5%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td></tr>';


} while ($TransferRow = DB_fetch_array($result));
$html2.='</table></div><div style = "height:500px;"></div>';
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

$pdf->writeHTML($html3, true, false, true, false, '');
 ob_end_clean();
$pdf->OutputD($_SESSION['DatabaseName'] . '_OGP_' . $_GET['RequestNo'] . '.pdf');
$pdf->__destruct();
?>