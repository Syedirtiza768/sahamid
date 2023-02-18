<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');
$document= 'DCNo: '.$_GET['DCNo'];
include('includes/PDFStarter2.php');



$pdf->addInfo('Title', _('Delivery Chalan') );
$pdf->addInfo('Subject', _('Delivery Chalan') . ' # ' . $_GET['RequestNo']);

$pdf->SetAutoPageBreak(TRUE, 15);
$FontSize=10;
$PageNumber=0;
$line_height=30;

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT dcs.customerref,
				dcs.contactphone,
				dcs.contactemail,
				dcs.salescaseref,
				dcs.comments,
				dcs.orddate,
				dcs.deliverto,
				dcs.deladd1,
				dcs.deladd2,
				dcs.deladd3,
				dcs.deladd4,
				dcs.deladd5,
				dcs.contactperson,
				dcs.advance,
				dcs.delivery,
				dcs.commisioning,
				dcs.after,
				dcs.gst,
				dcs.services,
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
			AND dcs.orderno='" .  $_GET['DCNo'] ."'";

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


'" .  $_GET['DCNo'] ."'";

$resultS=DB_query($sqlS,$db, $ErrMsg);
$TransferRowS = DB_fetch_array($resultS);

If (DB_num_rows($result)==0){

	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);
 

include ('includes/PDFDChPageHeader.inc');
	$sqlL="select MAX(orderlineno) as maxlineno from dcdetails where orderno = '". $_GET['DCNo']."'"; 		
$result = DB_query($sqlL,$db);
$resultrow=DB_fetch_array($result);
for ($i=0; $i<= $resultrow['maxlineno']; $i++ )
{
	
	$sqlO="select MAX(lineoptionno) as maxlineoptionno from dcdetails where orderno = '". $_GET['DCNo']."'
AND orderlineno = ".$i.""; 		
$resultO = DB_query($sqlO,$db);
$resultrowO=DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";
	
for ($j=0; $j<= $resultrowO['maxlineoptionno']; $j++ )
	{$sqlA = "SELECT *
	FROM dcdetails INNER JOIN stockmaster
		ON dcdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE dcdetails.orderno='" .  $_GET['DCNo'] . "'";
					$resultA = DB_query($sqlA,$db);
			$TransferRow = DB_fetch_array($resultA);
			
$line  =0;
$sqlB = "SELECT *
	FROM dclines 
	WHERE dclines.orderno='" .  $_GET['DCNo'] . "'
	AND dclines.lineno='" .$i. "'
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
<th width = "15%" align="center"> <b>Unit Rate</b></th>

<th width = "15%" align="center"><b>Total</b></th>
</tr>

<tr>';
if($resultrowO['maxlineoptionno']==0)
$html2.= '<td width = "10%" align="center">' . '<h3>'.($i+1).'</h3></td>';

else
$html2.= '<td width = "10%" align="center">' . '<h3>'.($i+1).' Option No. '.($j+1).'</h3>' . '</td>';
	
$html2.= '<td width = "50%" ><table>';

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
	FROM dcoptions 
	WHERE dcoptions.orderno='" .  $_GET['DCNo'] . "'
	AND dcoptions.lineno='" .$i. "'
	AND dcoptions.optionno='" .$j. "'
	
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
	$html2.='<tr><td>' . utf8_decode($TransferRowC['optiontext']).'</td></tr></table></td>

<td width = "10%" align="center">' . ''.$TransferRowC['quantity'].'</td>
<td width = "15%" align="center">' . ''.locale_number_format($total,2).'</td>

<td width = "15%" align="center">' . ''.locale_number_format(round($TransferRowC['quantity']*$total),2).'</td>
</tr>
</table></div>';
}}

$sqlquotevalue="SELECT dcs.debtorno, dcs.salescaseref, dcdetails.orderno,
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
if(isOverCreditLimit($db, $rowquotevalue['debtorno'],round($rowquotevalue['value']))){
	include ('includes/header.inc');
	echo "This Client Has Exceded Credit Limit!!!";
	include ('includes/footer.inc');
	return;
}
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

//$html = utf8_($html2);
$pdf->writeHTML($html2, true, false, true, false, '');



$htmlpayment = '<b> Terms and Conditions: </b><br>
We do not undertake any risk of breakage or loss of goods<br>
in transit when once the delivery has been effected<br>
<br>

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
$htmlpayment.='This is a system generated document and does not require any signatures or stamp';



$html4.='</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
 $pdf->writeHTML($htmlpayment, true, false, true, false, '');

//$pdf->writeHTML($html3, true, false, true, false, '');
$pdf->AddPage();

$pdf->Image('duplicate.jpg', 450, 70, 100, 96, 'jpg', '', 'right', false, 300, '', false, false, 0);
$pdf->writeHTML($html2, true, false, true, false, '');
$pdf->writeHTML($htmlpayment, true, false, true, false, '');


    ob_end_clean();
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_DC_' . $_GET['DCNo'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_DC_' . $_GET['DCNo'] . '.pdf','I');
    $pdf->__destruct();
?>