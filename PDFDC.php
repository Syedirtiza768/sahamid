<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');

include('includes/PDFStarter.php');

if (isset($RequestNo)) {
	$_GET['RequestNo']=$RequestNo;
}

$pdf->addInfo('Title', _('Delivery Challan') );
$pdf->addInfo('Subject', _('Delivery Challan') . ' # ' . $_GET['RequestNo']);
$FontSize=10;
$PageNumber=1;
$line_height=30;

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT
dc.salesperson, 

dc.loccode,
dc.despatchdate,
 dc.po,
 dc.ref,
 dc.dba,
 dc.deliverto,
 
 dba.companyaddress,

locations.locationname,
		dcitems.dispatchid,
		dcitems.stockid,
		dcitems.quantity, 
		dc.salescaseref,
		stockmaster.description,
			   stockmaster.mnfCode,
			   stockmaster.mnfpno,
		manufacturers.manufacturers_name,
		 stockmaster.decimalplaces
		FROM stockmaster inner join manufacturers on stockmaster.brand= manufacturers.manufacturers_id
		INNER JOIN dcitems ON dcitems.stockid=stockmaster.stockid
		
		INNER JOIN dc ON dc.dispatchid=dcitems.dispatchid
		INNER JOIN locations ON dc.loccode=locations.loccode
		INNER JOIN dba ON dc.dba=dba.companyname
		
		WHERE dc.dispatchid=" . $_GET['RequestNo'] . "";

		
$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
If (DB_num_rows($result)==0){

	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);
 $sqlA = "select  * from 
								custcontacts inner join dc on
								custcontacts.contid = dc.contid 
								where dc.salescaseref 
								= '".$_GET['salescaseref']."'";
					$resultA = DB_query($sql,$db);
			$rowresultA = DB_fetch_array($resultA);
$sql2 = "SELECT * FROM posdispatch where posdispatch.dispatchid='" . $_GET['RequestNo'] . "' ";
$result2 = DB_query($sql2,$db, $ErrMsg, $DbgMsg);
$TransferRow2 = DB_fetch_array($result2);
$sql3 = "select * from posdispatch where posdispatch.despatchdate= ".$TransferRow2['despatchdate'];
$result2 = DB_query($sql2,$db, $ErrMsg, $DbgMsg);
$TransferRow2 = DB_fetch_array($result2);
$sql4 = "select * from dc inner join dcclient on dc.dispatchid = dcclient.dispatchid where dc.dispatchid= ". $_GET['RequestNo'];
$result4 = DB_query($sql4,$db, $ErrMsg, $DbgMsg);
$TransferRow4 = DB_fetch_array($result4);
$incpart = 0;
$totalRows = count($result2);

$incpart = sprintf("%04d",$totalRows+2);
include ('includes/PDFDCHeader.inc');

$line  =0;
$html2.= '<br><br><br><div style="padding:10px;"><table border = "1" width = "100%"><tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Model No'.'</td>';
$html2.= '<td width = "15%" align = "center">'.'Part No'.'</td>';
$html2.= '<td width = "20%" align = "center">'.'Brand'.'</td>';
$html2.= '<td width = "40%">'.'Description'.'</td>';
$html2.= '<td width = "5%" align = "center">'.'quantity'.'</td>

<td width = "5%" align = "center">'.'Rate'.'</td>
</tr>';

do{
	$line++;
$html2.= '<tr><td width = "5%" align = "center">'.$line.'</td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
$html2.= '<td width = "15%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
$html2.= '<td width = "20%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
$html2.= '<td width = "40%">'.$TransferRow['description'].'</td>';
$html2.= '<td width = "5%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td>
';

$html2.= '<td width = "5%">'.$TransferRow['comments'].'</td></tr>';


} while ($TransferRow = DB_fetch_array($result));
$html2.='</table></div>';
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
$pdf->OutputD($_SESSION['DatabaseName'] . '_DC_' . $_GET['RequestNo'] . '.pdf');
$pdf->__destruct();
?>