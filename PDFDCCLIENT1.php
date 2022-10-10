<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');

include('includes/PDFStarter2.php');

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
echo $sql = "SELECT
dcclient.salesperson, 

dcclient.loccode,
dcclient.despatchdate,
 dcclient.po,
 dcclient.ref,
  dcclient.gstclause,
   dcclient.dctype,
  
 dcclient.dba,
 dcclient.deliverto,
 dcclient.narrative,
 dba.companyaddress,

locations.locationname,
		dcclientitems.dispatchid,
		dcclientitems.stockid,
		dcclientitems.quantity, 
		dcclientitems.description,
		dcclientitems.rate,
			  
		
		 stockmaster.decimalplaces
		FROM stockmaster 
		INNER JOIN dcclientitems ON dcclientitems.stockid=stockmaster.stockid
		
		INNER JOIN dcclient ON dcclient.dispatchid=dcclientitems.dispatchid
		INNER JOIN locations ON dcclient.loccode=locations.loccode
		INNER JOIN dba ON dcclient.dba=dba.companyname
		
		WHERE dcclient.dispatchid=" . $_GET['RequestNo'] . "";

		
$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
If (DB_num_rows($result)==0){

	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);

$sql2 = "SELECT * FROM posdispatch where posdispatch.dispatchid='" . $_GET['RequestNo'] . "' ";
$result2 = DB_query($sql2,$db, $ErrMsg, $DbgMsg);
$TransferRow2 = DB_fetch_array($result2);
$sql3 = "select * from posdispatch where posdispatch.despatchdate= ".$TransferRow2['despatchdate'];
$result2 = DB_query($sql2,$db, $ErrMsg, $DbgMsg);
$TransferRow2 = DB_fetch_array($result2);
$incpart = 0;
$totalRows = count($result2);

$incpart = sprintf("%04d",$totalRows+2);
include ('includes/PDFDCCLIENTHeader.inc');
$gstclause = $TransferRow['gstclause'];
$line  =0;
$html2.= '<br><br><br><div style="padding:10px;"><table border = "1" width = "100%"><tr align = "center"><td width = "10%">'.'Sr#'.'</td>';
$html2.= '<td width = "70%">'.'Description'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Quantity'.'</td>

<td width = "10%" align = "center">'.'Rate'.'</td>
</tr>';

do{
	$line++;
$html2.= '<tr><td width = "10%" align = "center">'.$line.'</td>';
$html2.= '<td width = "70%">'.$TransferRow['description'].'</td>';
$html2.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td>
';

$html2.= '<td width = "10%" align = "center">'.$TransferRow['rate'].'</td></tr>';


} while ($TransferRow = DB_fetch_array($result));
$html2.='</table>
<div style = "width:100%; height:100px;">
</div>
<div style = "padding-top:100px;">

<br><br><br><br>Terms and Conditions: <br><span style = "font-size:10px">
<br>
<br>
'.$gstclause.'<br>
<br>
We do not undertake any risk of breakage or loss of goods<br>
in transit when once the delivery has been effected<br>
<br>

'.$terms.'


</span>

</div>
</div>';
$pdf->writeHTML($html2, true, false, true, false, '');
$html3 = '<div><table width = "100%" height = "auto">
<tr><td><b>Sender:</b></td><td></td><td><b>Receiver:</b></td><td></td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Signatures:</b></td><td>______________________</td><td><b>Signatures:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Name:</b></td><td>'.$salesperson.'</td><td><b>Name:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Designation:</b></td><td>Business Development Officer</td><td><b>Designation:</b></td><td>______________________</td></tr>

</table></div>';
$pdf->writeHTML($html3, true, false, true, false, '');
 ob_end_clean();
$pdf->OutputD($_SESSION['DatabaseName'] . '_DC_' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
?>