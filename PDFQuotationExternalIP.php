<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');
$document= 'QuotationNo: '.$_GET['QuotationNo'];
include('includes/PDFStarter2.php');


//	$html2.='<span align="right">Page '.$pdf->getAliasNumPage() . " of " . $pdf->getAliasNbPages().'</span>';

$FontSize=10;
$PageNumber=0;
$line_height=30;

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT salesordersip.customerref,
				salesordersip.contactphone,
				salesordersip.contactemail,
				salesordersip.salescaseref,
				salesordersip.comments,
				salesordersip.orddate,
				salesordersip.deliverto,
				salesordersip.deladd1,
				salesordersip.deladd2,
				salesordersip.deladd3,
				salesordersip.deladd4,
				salesordersip.deladd5,
				
				salesordersip.advance,
				salesordersip.delivery,
				salesordersip.commisioning,
				salesordersip.after,
				salesordersip.gst,
				salesordersip.afterdays,
				salesordersip.GSTadd,
				salesordersip.services,
				salesordersip.WHT,
				
				debtorsmaster.name,
				debtorsmaster.currcode,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtorsmaster.address6,
				shippers.shippername,
				salesordersip.printedpackingslip,
				salesordersip.datepackingslipprinted,
				salesordersip.branchcode,
				locations.taxprovinceid,
				locations.locationname,
				currencies.decimalplaces AS currdecimalplaces
			FROM salesordersip INNER JOIN debtorsmaster
			ON salesordersip.debtorno=debtorsmaster.debtorno
			INNER JOIN shippers
			ON salesordersip.shipvia=shippers.shipper_id
			INNER JOIN locations
			ON salesordersip.fromstkloc=locations.loccode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE salesordersip.quotation=1
			AND salesordersip.orderno='" . $_GET['QuotationNo'] ."'";

$result=DB_query($sql,$db, $ErrMsg);
$TransferRow2 = DB_fetch_array($result);
$sqlS = "SELECT * from salesordersip INNER JOIN
		debtorsmaster 
		ON salesordersip.debtorno = debtorsmaster.debtorno
		INNER JOIN custbranch 
		ON debtorsmaster.debtorno = custbranch.debtorno
		INNER JOIN dba ON dba.companyname = debtorsmaster.dba
		INNER JOIN salesman
		ON custbranch.salesman = salesman.salesmancode
		WHERE orderno = 


'" . $_GET['QuotationNo'] ."'";

$resultS=DB_query($sqlS,$db, $ErrMsg);
$TransferRowS = DB_fetch_array($resultS);

If (DB_num_rows($result)==0){

	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);
 

include ('includes/PDFQuotationPageHeader.inc');
//$html2.='<span align="right">Page '.$pdf->getAliasNumPage() . " of " . $pdf->getAliasNbPages().'</span>';

	$sqlL="select MAX(orderlineno) as maxlineno from salesorderdetailsip where orderno = '".$_GET['QuotationNo']."'"; 		
$result = DB_query($sqlL,$db);
$resultrow=DB_fetch_array($result);
for ($i=0; $i<= $resultrow['maxlineno']; $i++ )
{
	
	$sqlO="select MAX(lineoptionno) as maxlineoptionno from salesorderdetailsip where orderno = '".$_GET['QuotationNo']."'
AND orderlineno = ".$i.""; 		
$resultO = DB_query($sqlO,$db);
$resultrowO=DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";
	
for ($j=0; $j<= $resultrowO['maxlineoptionno']; $j++ )
	{$sqlA = "SELECT *
	FROM salesorderdetailsip INNER JOIN stockmaster
		ON salesorderdetailsip.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE salesorderdetailsip.orderno='" . $_GET['QuotationNo'] . "'";
					$resultA = DB_query($sqlA,$db);
			$TransferRow = DB_fetch_array($resultA);
			
$line  =0;
$sqlB = "SELECT *
	FROM salesorderlinesip 
	WHERE salesorderlinesip.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderlinesip.lineno='" .$i. "'
	";
					$resultB = DB_query($sqlB,$db);
			$TransferRowB = DB_fetch_array($resultB);
			//$clientrequirements = $TransferRowB['clientrequirements'];
		//	$TransferRowB['clientrequirements'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowB['clientrequirements']);
//$TransferRowB['clientrequirements'] = stripslashes($TransferRowB['clientrequirements']);

			//$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowB['clientrequirements']);
$TransferRowB['clientrequirements'] = str_replace("</p>","",$TransferRowB['clientrequirements'] );

	$TransferRowB['clientrequirements'] = str_replace("<p>","",$TransferRowB['clientrequirements'] );
$TransferRowB['clientrequirements'] = str_replace("</div>","",$TransferRowB['clientrequirements'] );

$TransferRowB['clientrequirements'] = str_replace("<div>","",$TransferRowB['clientrequirements'] );

$html2.= '<br><br><br><div style="padding:10px;"><table nobr="true" border = "1" width = "100%">

<tr><th width = "10%" align="center">' . '<b>Serial No. </b>.</th>
<th width = "50%" align="center"><b> Description </b></th>
<th width = "10%"align="center"> <b>Quantity</b></th>
<th width = "15%" align="center"> <b>Rate</b></th>

<th width = "15%" align="center"><b>Total</b></th>
</tr>

<tr>';
if($resultrowO['maxlineoptionno']==0)
$html2.= '<td width = "10%" align="center">' . '<h3>'.($i+1).'</h3></td>';

else
$html2.= '<td width = "10%" align="center">' . '<h3>'.($i+1).' Option No. '.($j+1).'</h3>' . '</td>';
	
$html2.= '<td width = "50%" ><table><tr><td>' . '<b><u>Client Required</u></b>.<br /> '. utf8_decode($TransferRowB['clientrequirements']).'</td></tr>';

//<tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
//$html2.= '<td width = "10%" align = "center">'.'Model No'.'</td>';
//$html2.= '<td width = "15%" align = "center">'.'Part No'.'</td>';
//$html2.= '<td width = "20%" align = "center">'.'Brand'.'</td>';
//$html2.= '<td width = "30%">'.'Description'.'</td>';
//$html2.= '<td width = "10%" align = "center">'.'quantity'.'</td>

//<td width = "10%" align = "center">'.'Rate'.'</td>
//</tr>';
$total = 0;
do{
	if(($i== $TransferRow['orderlineno']) AND ($j== $TransferRow['lineoptionno']) ) {
		$sqlC = "SELECT *
	FROM salesorderoptionsipip 
	WHERE salesorderoptionsipip.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderoptionsipip.lineno='" .$i. "'
	AND salesorderoptionsipip.optionno='" .$j. "'
	
	";
					$resultC = DB_query($sqlC,$db);
			$TransferRowC = DB_fetch_array($resultC);
	$line++;
//$html2.= '<tr><td width = "5%" align = "center">'.$line.'</td>';
//$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
//$html2.= '<td width = "15%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
//$html2.= '<td width = "20%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
//$html2.= '<td width = "30%">'.$TransferRow['description'].'</td>';
//$html2.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td>
//';

//$html2.= '<td width = "10%">'.'Rs.'.locale_number_format(($TransferRow['unitprice']*(1 - $TransferRow['discountpercent'])),0).'</td></tr>';

	$total = $total + ($TransferRow['unitprice']*$TransferRow['quantity']*(1 - $TransferRow['discountpercent']));
	
	}} while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);
$TransferRowC['optiontext'] = str_replace("</p>","",$TransferRowC['optiontext']);

$TransferRowC['optiontext'] = str_replace("<p>","",$TransferRowC['optiontext']);
$TransferRowC['optiontext'] = str_replace("</div>","",$TransferRowC['optiontext']);

$TransferRowC['optiontext'] = str_replace("<div>","",$TransferRowC['optiontext']);
	$html2.='<tr><td>' . '<b><u>We Offer </b></u><br /> '. utf8_decode($TransferRowC['optiontext']).'</td></tr></table></td>

<td width = "10%" align="center">' . ''.$TransferRowC['quantity'].'</td>
<td width = "15%" align="center">' . ''.locale_number_format($total,2).'</td>

<td width = "15%" align="center">' . ''.locale_number_format($TransferRowC['quantity']*$total,2).'</td>
</tr><tr><td colspan="7">' . 'Stock Status: '.$TransferRowC['stockstatus'].'</td>
</tr>
</table></div>';
}}

//$html = utf8_($html2);



$html3 = '<div><table width = "100%" height = "auto">
<tr><td><b>Sender:</b></td><td></td><td><b>Receiver:</b></td><td></td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Signatures:</b></td><td>______________________</td><td><b>Signatures:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Name:</b></td><td>______________________</td><td><b>Name:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Designation:</b></td><td>______________________</td><td><b>Designation:</b></td><td>______________________</td></tr>

</table></div>';

$sqlquotevalue="SELECT salesordersip.salescaseref, salesorderdetailsip.orderno,
 SUM(salesorderdetailsip.unitprice* (1 - salesorderdetailsip.discountpercent)*salesorderdetailsip.quantity*salesorderoptionsipip.quantity )
 as value from salesorderdetailsip INNER JOIN salesorderoptionsipip on
 (salesorderdetailsip.orderno = salesorderoptionsipip.orderno AND 
 salesorderdetailsip.orderlineno = salesorderoptionsipip.lineno) 
 INNER JOIN salesordersip on salesordersip.orderno = salesorderdetailsip.orderno 
 WHERE salesorderdetailsip.lineoptionno = 0 and salesorderoptionsipip.optionno = 0 and salesordersip.orderno='".$_GET['QuotationNo'] ."'";

 $resultsqlquotevalue=DB_query($sqlquotevalue,$db);
 $rowquotevalue=DB_fetch_array($resultsqlquotevalue);
 $html2.='<div align="right">
 <table border="1">';
 if ($TransferRow2['GSTadd'] == 'exclusive')
 {
 $html2.='<tr><td><b>SUBTOTAL</b></td><td>
 '.locale_number_format($rowquotevalue['value'],2).'</td></tr>';
  if ($TransferRow2['services'] == 1)
  {
$html2.='<tr><td><b>16% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value']*0.16,2) .'</td></tr>';
   if ($TransferRow2['WHT'] != 0)
		 {
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.(locale_number_format($rowquotevalue['value']*1.16+$rowquotevalue['value']*$TransferRow2['WHT']/100,2)) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format($rowquotevalue['value']*1.16,2).'</td></tr>';
		
			 
		 }

  }
  else
  {
$html2.='<tr><td><b>17% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value']*0.17,2) .'</td></tr>';
   if ($TransferRow2['WHT'] != 0)
		 {
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format($rowquotevalue['value']*1.17+$rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format($rowquotevalue['value']*1.17,2).'</td></tr>';
		
			 
		 }

	  
  }
  
 }
  if ($TransferRow2['GSTadd'] == 'inclusive')
 {
 

 
  if ($TransferRow2['services'] == 1)
  {

   if ($TransferRow2['WHT'] != 0)
		 {
			 $html2.='<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value'],2) .' </td></tr>';	  
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format($rowquotevalue['value']+$rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value'],2).' </td></tr>';
		
			 
		 }

  }
  else
  {

   if ($TransferRow2['WHT'] != 0)
		 {
			 $html2.='<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value'],2).' </td></tr>';	  
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format($rowquotevalue['value']+$rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value'],2).' </td></tr>';
		
			 
		 }

 	  
  }
  
  
 }
 
 if ($TransferRow2['GSTadd'] == '')
 {
 

 
  
   if ($TransferRow2['WHT'] != 0)
		 {
			 $html2.='<tr><td><b>SUBTOTAL</b></td><td>
 '.locale_number_format($rowquotevalue['value'],2).'</td></tr>';	  
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format($rowquotevalue['value']+$rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format($rowquotevalue['value'],2).'</td></tr>';
		
			 
		 }

 }
 
 $html2.='</table>
 </div>';
  $html2.= 'In case of multiple options amount of first option is considered<br/>';
 $pdf->writeHTML($html2, true, false, true, false, '');
 
$htmlpayment.= '<b> Terms and Conditions: </b><br>
Stock availability is subject to prior stock<br>
Company reserves the right to apply force majeur clause<br>
<b>Payment Terms:</b><br>
';
if($TransferRow2['advance']>0)
$htmlpayment.= 'Advance '.$TransferRow2['advance'].'%<br>';
if($TransferRow2['delivery']>0)
$htmlpayment.= 'On Delivery '.$TransferRow2['delivery'].'%<br>';
if($TransferRow2['commisioning']>0)
$htmlpayment.= 'On Commisioning '.$TransferRow2['commisioning'].'%<br>';
if($TransferRow2['after']>0)
$htmlpayment.= $TransferRow2['after'].'% After '.$TransferRow2['afterdays']. 'days';
$htmlpayment.= '<br>'.$TransferRow2['gst'].'<br>';




$html4.='</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
 $pdf->writeHTML($htmlpayment, true, false, true, false, '');

//$pdf->writeHTML($html3, true, false, true, false, '');
 ob_end_clean();
$pdf->OutputD($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf');
$pdf->__destruct();
?>