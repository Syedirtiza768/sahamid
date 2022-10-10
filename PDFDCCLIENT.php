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
 dcclient.advance,
 dba.companyaddress,

locations.locationname,
		dcclientitems.dispatchid,
		dcclientitems.stockid,
		dcclientitems.quantity, 
		dcclientitems.description,
		dcclientitems.rate,
		dcclientitems.linetotal
		
		 
		FROM dcclientitems
		
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
$dctype = $TransferRow['dctype'];
$advance = $TransferRow['advance'];
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
$html2.= '<td width = "60%">'.'Description'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Quantity'.'</td>
<td width = "10%" align = "center">'.'Rate'.'</td>';
if($dctype == 'Bill')
$html2.= '<td width = "10%" align = "center">'.'Sub Total'.'</td>';
$html2.= '</tr>';
echo "<br>";
echo $sql = "SELECT SUM(linetotal) as total from dcclientitems where dispatchid = ".$_GET['RequestNo'];
$sqlresult = DB_query($sql,$db);
$rowresult = DB_fetch_array($sqlresult);

do{
	$TransferRow['description'] = preg_replace("/\r\n|\r|\n/",'<br/>',$TransferRow['description']);
	echo $TransferRow['description'];
	$line++;
$html2.= '<tr><td width = "10%" align = "center">'.$line.'</td>';
$html2.= '<td width = "60%">'.$TransferRow['description'].'</td>';
$html2.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td>
';
$ratenumformat = preg_replace("/[^0-9]|,/", "", $TransferRow['rate']);
$subtotal = $ratenumformat * $TransferRow['quantity'];
$total = $total +$subtotal;
$balance = $total - $advance;
if($TransferRow['rate'] != 0)
$html2.= '<td width = "10%" align = "center">'.locale_number_format($ratenumformat,0).'/-Ea'.'</td>';
else
$html2.= '<td width = "10%" align = "center"></td>';
	if($dctype == 'Bill')
$html2.= '<td width = "10%" align = "center">'.locale_number_format($subtotal,0).'</td>';
$html2.= '</tr>';


} while ($TransferRow = DB_fetch_array($result) and $line<=50);
if($dctype == 'Bill')
{
$html2.= '<tr><td width = "90%" align = "right">TOTAL: </td>
<td width = "10%" align = "center">'.locale_number_format($total,0).'</td>
</tr>';
$html2.= '<tr><td width = "90%" align = "right">ADVANCE: </td>
<td width = "10%" align = "center">'.locale_number_format($advance,0).'</td>
</tr>';
$html2.= '<tr><td width = "90%" align = "right">BALANCE: </td>
<td width = "10%" align = "center">'.locale_number_format($balance,0).'</td>
</tr>';
}
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
if ($line>25)
{
	$pdf->AddPage();

$htmlA.= '<br><br><br><div style="padding:10px;"><table border = "1" width = "100%"><tr align = "center"><td width = "10%">'.'Sr#'.'</td>';
$htmlA.= '<td width = "60%">'.'Description'.'</td>';
$htmlA.= '<td width = "10%" align = "center">'.'Quantity'.'</td>

<td width = "10%" align = "center">'.'Rate'.'</td>';
if($dctype == 'Bill')
$html2.= '<td width = "10%" align = "center">'.'Sub Total'.'</td>';
$html2.= '</tr>';
echo "<br>";
echo $sql = "SELECT SUM(linetotal) as total from dcclientitems where dispatchid = ".$_GET['RequestNo'];
$sqlresult = DB_query($sql,$db);
$rowresult = DB_fetch_array($sqlresult);

do{
	$TransferRow['description'] = preg_replace("/\r\n|\r|\n/",'<br/>',$TransferRow['description']);
	echo $TransferRow['description'];
	$line++;
$htmlA.= '<tr><td width = "10%" align = "center">'.$line.'</td>';
$htmlA.= '<td width = "60%">'.$TransferRow['description'].'</td>';
$htmlA.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td>
';
echo "<br>";
echo $TransferRow['rate'];
echo $ratenumformat = preg_replace("/[^0-9]|,/", "", $TransferRow['rate']);
$subtotal = $ratenumformat * $TransferRow['quantity'];
$total = $total +$subtotal;
$balance = $total - $advance;
if($TransferRow['rate'] != 0)
$htmlA.= '<td width = "10%" align = "center">'.locale_number_format($ratenumformat,0).'/-Ea'.'</td>';
else
$htmlA.= '<td width = "10%" align = "center"></td>';
	
if($dctype == 'Bill')
$htmlA.= '<td width = "10%" align = "center">'.locale_number_format($subtotal,0).'</td>';

$htmlA.='</tr>';


} while ($TransferRow = DB_fetch_array($result) and $line<=50);
if($dctype == 'Bill')
{
$htmlA.= '<tr><td width = "90%" align = "right">TOTAL: </td>
<td width = "10%" align = "center">'.locale_number_format($total,0).'</td>
</tr>';
$htmlA.= '<tr><td width = "90%" align = "right">ADVANCE: </td>
<td width = "10%" align = "center">'.locale_number_format($advance,0).'</td>
</tr>';
$htmlA.= '<tr><td width = "90%" align = "right">BALANCE: </td>
<td width = "10%" align = "center">'.locale_number_format($balance,0).'</td>
</tr>';
}
$htmlA.='</table>
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
$pdf->writeHTML($htmlA, true, false, true, false, '');

}
$html3 = '<div><table width = "100%" height = "auto">
<tr><td><b>Sender:</b></td><td></td><td><b>Receiver:</b></td><td></td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Signatures:</b></td><td>______________________</td><td><b>Signatures:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Name:</b></td><td>'.$salesperson.'</td><td><b>Name:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Designation:</b></td><td>______________________</td><td><b>Designation:</b></td><td>______________________</td></tr>

</table></div>';
$pdf->writeHTML($html3, true, false, true, false, '');
 ob_end_clean();
$pdf->OutputD($_SESSION['DatabaseName'] . '_DC_' . $_GET['RequestNo'] . '.pdf');
$pdf->__destruct();
?>