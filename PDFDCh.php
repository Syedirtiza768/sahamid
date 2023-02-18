<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');
$document= 'DCNo: '.$_GET['DCNo'];
include('includes/PDFStarter2.php');



$pdf->addInfo('Title', _('Delivery Challan') );
$pdf->addInfo('Subject', _('Delivery Challan') . ' # ' . $_GET['DCNo']);

$pdf->SetAutoPageBreak(TRUE, 15);
$FontSize=10;
$PageNumber=0;
$line_height=30;

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT dcs.customerref,
				dcs.salescaseref,
				dcs.contactphone,
				dcs.contactemail,
				dcs.comments,
				dcs.orddate,
				dcs.deliverto,
				dcs.deladd1,
				dcs.deladd2,
				dcs.deladd3,
				dcs.deladd4,
				dcs.deladd5,
				dcs.services,
				dcs.advance,
				dcs.delivery,
				dcs.commisioning,
				dcs.after,
				dcs.afterdays,
				dcs.GSTAdd,
				dcs.quotedate,
				debtorsmaster.name,
				debtorsmaster.currcode,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtorsmaster.address6,
				shippers.shippername,
				dcs.printedpackingslip,
				dcs.datepackingslipprinted,
				dcs.branchcode,
				locations.taxprovinceid,
				locations.locationname,
				salescase.podate,
				dcs.dispatchthrough,
				currencies.decimalplaces AS currdecimalplaces
			FROM dcs INNER JOIN debtorsmaster
			ON dcs.debtorno=debtorsmaster.debtorno
			INNER JOIN salescase ON dcs.salescaseref = salescase.salescaseref
			INNER JOIN shippers
			ON dcs.shipvia=shippers.shipper_id
			INNER JOIN locations
			ON dcs.fromstkloc=locations.loccode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE dcs.quotation=1
			AND dcs.orderno='" . $_GET['DCNo'] ."'";

$result=DB_query($sql,$db, $ErrMsg);
$TransferRow2 = DB_fetch_array($result);
$sqlS = "SELECT * from dcs INNER JOIN
		debtorsmaster 
		ON dcs.debtorno = debtorsmaster.debtorno
		INNER JOIN custbranch 
		ON debtorsmaster.debtorno = custbranch.debtorno
		INNER JOIN dba ON dba.companyname = debtorsmaster.dba
		INNER JOIN salesman
		ON custbranch.salesman = salesman.salesmancode
		WHERE orderno = 


'" . $_GET['DCNo'] ."'";

$resultS=DB_query($sqlS,$db, $ErrMsg);
$TransferRowS = DB_fetch_array($resultS);

$sqlSalesperson = "SELECT * from salesman inner join 
www_users on salesman.salesmanname = www_users.realname
WHERE salesman.salesmanname = 
'" . $TransferRowS['salesmanname'] ."'";

$resultSalesperson=DB_query($sqlSalesperson,$db, $ErrMsg);
$TransferRowSalesperson = DB_fetch_array($resultSalesperson);

If (DB_num_rows($result)==0){

	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);
 

include ('includes/PDFDChPageHeader.inc');
	$sqlL="select MAX(orderlineno) as maxlineno from dcdetails where orderno = ".$_GET['DCNo'].""; 		
$result = DB_query($sqlL,$db);
$resultrow=DB_fetch_array($result);

for ($line=0; $line<= $resultrow['maxlineno']; $line++ )
{
	
	$sqlO="select MAX(lineoptionno) as maxlineoptionno from dcdetails where orderno = ".$_GET['DCNo']."
AND orderlineno = ".$line.""; 		
$resultO = DB_query($sqlO,$db);
$resultrowO=DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";
	
for ($option=0; $option<= $resultrowO['maxlineoptionno']; $option++ )
	{$sqlA = "SELECT *
	FROM dcdetails INNER JOIN stockmaster
		ON dcdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE dcdetails.orderno='" . $_GET['DCNo'] . "'";
					$resultA = DB_query($sqlA,$db);
			$TransferRow = DB_fetch_array($resultA);
			
//$line  =0;
$sqlB = "SELECT *
	FROM dclines 
	WHERE dclines.orderno='" . $_GET['DCNo'] . "'
	AND dclines.lineno='" .$line. "'
	";
					$resultB = DB_query($sqlB,$db);
			$TransferRowB = DB_fetch_array($resultB);
			//$clientrequirements = $TransferRowB['clientrequirements'];
	//		$TransferRowB['clientrequirements'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowB['clientrequirements']);
//$TransferRowB['clientrequirements'] = stripslashes($TransferRowB['clientrequirements']);
$TransferRowB['clientrequirements'] = str_replace("</p>","",$TransferRowB['clientrequirements'] );

	$TransferRowB['clientrequirements'] = str_replace("<p>","",$TransferRowB['clientrequirements'] );
$TransferRowB['clientrequirements'] = str_replace("</div>","",$TransferRowB['clientrequirements'] );

$TransferRowB['clientrequirements'] = str_replace("<div>","",$TransferRowB['clientrequirements'] );


			//$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowB['clientrequirements']);
$clt =  utf8_decode($TransferRowB['clientrequirements']);
$html2.= '<br><br><br><div style="padding:10px;"><table nobr="true" border = "1" width = "100%">
<tr><td colspan="7">' . '<h3>Line No. '.($line+1).' Option No. '.($option+1).'</h3>' . '</td></tr>
<tr><td colspan="7">' . 'Client Required.<br /> '.$clt.'</td></tr>

<tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Model No'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Part No'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Brand'.'</td>';
$html2.= '<td width = "25%">'.'Description'.'</td>';
$html2.= '<td width = "5%" align = "center">'.'qty.'.'</td>
<td width = "10%" align = "center">'.'List Price'.'</td>
<td width = "5%" align = "center">'.'Disc.'.'</td>
<td width = "10%" align = "center">'.'Unit Rate'.'</td>
<td width = "10%" align = "center">'.'Sub Total'.'</td>
</tr>';
$total = 0;
do{
	if(($line== $TransferRow['orderlineno']) AND ($option== $TransferRow['lineoptionno']) ) {
		$sqlC = "SELECT *
	FROM dcoptions 
	WHERE dcoptions.orderno='" . $_GET['DCNo'] . "'
	AND dcoptions.lineno='" .$line. "'
	AND dcoptions.optionno='" .$option. "'
	
	";
					$resultC = DB_query($sqlC,$db);
			$TransferRowC = DB_fetch_array($resultC);
$lineindex = $line + 1 ;
$html2.= '<tr><td width = "5%" align = "center">'.$lineindex .'</td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
$html2.= '<td width = "25%">'.$TransferRow['description'].'</td>';
$html2.= '<td width = "5%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],2).'</center></td>
';
$html2.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['unitprice'],$TransferRow['decimalplaces']).'</center></td>
';
$html2.= '<td width = "5%"align = "center"><center>'.locale_number_format($TransferRow['discountpercent']*100,2).'</center></td>
';

$html2.= '<td width = "10%">'.'Rs.'.locale_number_format(($TransferRow['unitprice']*(1 - $TransferRow['discountpercent'])),2).'</td>';

	$total = $total + ($TransferRow['unitprice']*$TransferRow['quantity']*(1 - $TransferRow['discountpercent']));
$html2.= '<td width = "10%"align = "center">
<center>'.locale_number_format($TransferRow['unitprice']*$TransferRow['quantity']*(1 - $TransferRow['discountpercent'])
,$TransferRow['decimalplaces']).'</center></td></tr>';
	
	}} while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);
$TransferRowC['optiontext'] = str_replace("</p>","",$TransferRowC['optiontext']);

$TransferRowC['optiontext'] = str_replace("<p>","",$TransferRowC['optiontext']);
$TransferRowC['optiontext'] = str_replace("</div>","",$TransferRowC['optiontext']);

$TransferRowC['optiontext'] = str_replace("<div>","",$TransferRowC['optiontext']);


	$html2.='
<tr><td colspan="9">' . 'We Offer.<br /> '. html_entity_decode($TransferRowC['optiontext']).'</td></tr>

<tr><td colspan="2" align="center">' . 'Qty: '.$TransferRowC['quantity'].'</td>
<td colspan="4" align="center">' . 'Unit Rate: '.locale_number_format($total,2).'</td>

<td colspan="4" align="center">' . 'Total: Rs.'.locale_number_format(round($TransferRowC['quantity']*$total),2).'</td></tr>
</table></div>';
}}

$sqlquotevalue="SELECT dcs.salescaseref, dcdetails.orderno,
 SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity )
 as value from dcdetails INNER JOIN dcoptions on
 (dcdetails.orderno = dcoptions.orderno AND 
 dcdetails.orderlineno = dcoptions.lineno) 
 INNER JOIN dcs on dcs.orderno = dcdetails.orderno 
 WHERE dcdetails.lineoptionno = 0 and dcoptions.optionno = 0 and dcs.orderno='".$_GET['DCNo'] ."'";

 $resultsqlquotevalue=DB_query($sqlquotevalue,$db);
 $rowquotevalue=DB_fetch_array($resultsqlquotevalue);
 $html2.='<div align="right">
 <table border="1">';

 if ($TransferRow2['GSTAdd'] == 'update')
 {
 $html2.='<tr><td><b>SUBTOTAL</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).'</td></tr>';
  if ($TransferRow2['services'] == 1)
  {
$html2.='<tr><td><b>16% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value']*0.16,2) .'</td></tr>';
   if ($TransferRow2['WHT'] != 0)
		 {
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.(locale_number_format(round($rowquotevalue['value']*1.16+$rowquotevalue['value']*$TransferRow2['WHT']/100),2)) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']*1.16),2).'</td></tr>';
		
			 
		 }

  }
  else
  {
$html2.='<tr><td><b>18% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value']*0.18,2) .'</td></tr>';
   if ($TransferRow2['WHT'] != 0)
		 {
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']*1.18+$rowquotevalue['value']*$TransferRow2['WHT']/100),2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']*1.18),2).'</td></tr>';
		
			 
		 }

	  
  }
  
 }

 if ($TransferRow2['GSTAdd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14'))
 {
 $html2.='<tr><td><b>SUBTOTAL</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).'</td></tr>';
  if ($TransferRow2['services'] == 1)
  {
$html2.='<tr><td><b>16% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value']*0.16,2) .'</td></tr>';
   if ($TransferRow2['WHT'] != 0)
		 {
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.(locale_number_format(round($rowquotevalue['value']*1.16+$rowquotevalue['value']*$TransferRow2['WHT']/100),2)) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']*1.16),2).'</td></tr>';
		
			 
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
 '.locale_number_format(round($rowquotevalue['value']*1.17+$rowquotevalue['value']*$TransferRow2['WHT']/100),2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']*1.17),2).'</td></tr>';
		
			 
		 }

	  
  }
  
 }
 elseif ($TransferRow2['GSTAdd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14'))
 {
 $html2.='<tr><td><b>SUBTOTAL</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).'</td></tr>';
  if ($TransferRow2['services'] == 1)
  {
$html2.='<tr><td><b>16% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value']*0.16,2) .'</td></tr>';
   if ($TransferRow2['WHT'] != 0)
		 {
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.(locale_number_format(round($rowquotevalue['value']*1.16+$rowquotevalue['value']*$TransferRow2['WHT']/100),2)) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']*1.16),2).'</td></tr>';
		
			 
		 }

  }
  else
  {
$html2.='<tr><td><b>18% GST</b></td><td>
 '.locale_number_format($rowquotevalue['value']*0.18,2) .'</td></tr>';
   if ($TransferRow2['WHT'] != 0)
		 {
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']*1.18+$rowquotevalue['value']*$TransferRow2['WHT']/100),2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']*1.18),2).'</td></tr>';
		
			 
		 }

	  
  }
  
 }
  if ($TransferRow2['GSTAdd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14'))
 {
 

 
  if ($TransferRow2['services'] == 1)
  {

   if ($TransferRow2['WHT'] != 0)
		 {
			 $html2.='<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2) .' </td></tr>';	  
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']+$rowquotevalue['value']*$TransferRow2['WHT']/100),2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).' </td></tr>';
		
			 
		 }

  }
  else
  {

   if ($TransferRow2['WHT'] != 0)
		 {
			 $html2.='<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).' </td></tr>';	  
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']+$rowquotevalue['value']*$TransferRow2['WHT']/100),2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).' </td></tr>';
		
			 
		 }

 	  
  }
  
  
 }
 elseif ($TransferRow2['GSTAdd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14'))
 {
 

 
  if ($TransferRow2['services'] == 1)
  {

   if ($TransferRow2['WHT'] != 0)
		 {
			 $html2.='<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2) .' </td></tr>';	  
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']+$rowquotevalue['value']*$TransferRow2['WHT']/100),2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).' </td></tr>';
		
			 
		 }

  }
  else
  {

   if ($TransferRow2['WHT'] != 0)
		 {
			 $html2.='<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).' </td></tr>';	  
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']+$rowquotevalue['value']*$TransferRow2['WHT']/100),2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total inclusive of 18% GST</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).' </td></tr>';
		
			 
		 }

 	  
  }
  
  
 }

 if ($TransferRow2['GSTAdd'] == '')
 {
 

 
  
   if ($TransferRow2['WHT'] != 0)
		 {
			 $html2.='<tr><td><b>SUBTOTAL</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).'</td></tr>';	  
$html2.='<tr><td><b>'.$TransferRow2['WHT'].'% Witholding Tax</b></td><td>
 '.locale_number_format($rowquotevalue['value']*$TransferRow2['WHT']/100,2) .'</td></tr>';	  
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']+$rowquotevalue['value']*$TransferRow2['WHT']/100),2) .'</td></tr>';
		 }
		 else{
			
$html2.='<tr><td><b>Grand Total</b></td><td>
 '.locale_number_format(round($rowquotevalue['value']),2).'</td></tr>';
		
			 
		 }

 }
 
 $html2.='</table>
 </div>';

//$html2 = utf8_encode($html2);
//$pdf->writeHTML($html2, true, false, true, false, '');

//ob_end_clean();
$html3 = '<div><table width = "100%" height = "auto">
<tr><td><b>Sender:</b></td><td></td><td><b>Receiver:</b></td><td></td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Signatures:</b></td><td>______________________</td><td><b>Signatures:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Name:</b></td><td>______________________</td><td><b>Name:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Designation:</b></td><td>______________________</td><td><b>Designation:</b></td><td>______________________</td></tr>

</table></div>';
$html2.= 'Payment Terms:<br>';
if($TransferRow2['advance']>0)
$html2.= 'Advance '.$TransferRow2['advance'].'%<br>';
if($TransferRow2['delivery']>0)
$html2.= 'On Delivery '.$TransferRow2['delivery'].'%<br>';
if($TransferRow2['commisioning']>0)
$html2.= 'On Commisioning '.$TransferRow2['commisioning'].'%<br>';
if($TransferRow2['after']>0)
$html2.= $TransferRow2['after'].'% After '.$TransferRow2['afterdays']. 'days';



$html2.='<br>This is a system generated document and does not require any signatures or stamp</br>';


$html4.='</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
 $pdf->writeHTML($html2, true, false, true, false, '');

//$pdf->writeHTML($html3, true, false, true, false, '');
 ob_end_clean();
header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_DC_' . $_GET['DCNo'] . '.pdf'."\";");

$pdf->Output($_SESSION['DatabaseName'] . '_DC_' . $_GET['DCNo'] . '.pdf','I');


$pdf->__destruct();
?>