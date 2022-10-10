<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');
$document= 'OrderConfirmationNo: '.$_GET['OrderConfirmationNo'];
include('includes/PDFStarter2.php');



$pdf->addInfo('Title', _('OrderConfirmation') );
$pdf->addInfo('Subject', _('OrderConfirmation') . ' # ' . $_GET['OrderConfirmationNo']);

$pdf->SetAutoPageBreak(TRUE, 15);
$FontSize=10;
$PageNumber=0;
$line_height=30;

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT ocs.customerref,
				ocs.salescaseref,
				ocs.contactphone,
				ocs.contactemail,
				ocs.comments,
				ocs.orddate,
				ocs.deliverto,
				ocs.deladd1,
				ocs.deladd2,
				ocs.deladd3,
				ocs.deladd4,
				ocs.deladd5,
				
				ocs.advance,
				ocs.delivery,
				ocs.commisioning,
				ocs.after,
				ocs.afterdays,
				
				debtorsmaster.name,
				debtorsmaster.currcode,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtorsmaster.address6,
				shippers.shippername,
				ocs.printedpackingslip,
				ocs.datepackingslipprinted,
				ocs.branchcode,
				locations.taxprovinceid,
				locations.locationname,
				currencies.decimalplaces AS currdecimalplaces
			FROM ocs INNER JOIN debtorsmaster
			ON ocs.debtorno=debtorsmaster.debtorno
			INNER JOIN shippers
			ON ocs.shipvia=shippers.shipper_id
			INNER JOIN locations
			ON ocs.fromstkloc=locations.loccode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE ocs.quotation=1
			AND ocs.orderno='" . $_GET['OrderConfirmationNo'] ."'";

$result=DB_query($sql,$db, $ErrMsg);
$TransferRow2 = DB_fetch_array($result);
$sqlS = "SELECT * from ocs INNER JOIN
		debtorsmaster 
		ON ocs.debtorno = debtorsmaster.debtorno
		INNER JOIN custbranch 
		ON debtorsmaster.debtorno = custbranch.debtorno
		INNER JOIN dba ON dba.companyname = debtorsmaster.dba
		INNER JOIN salesman
		ON custbranch.salesman = salesman.salesmancode
		WHERE orderno = 


'" . $_GET['OrderConfirmationNo'] ."'";

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
 

include ('includes/PDFOCPageHeader.inc');
	$sqlL="select MAX(orderlineno) as maxlineno from ocdetails where  orderno = ".$_GET['OrderConfirmationNo']."
	
	"; 		
$result = DB_query($sqlL,$db);
$resultrow=DB_fetch_array($result);

for ($line=0; $line<= $resultrow['maxlineno']; $line++ )
{
		$sqllinecheck = "SELECT *
	FROM ocdetails 
	WHERE ocdetails.orderno='" .$_GET['OrderConfirmationNo'] . "'
	AND ocdetails.orderlineno='" .$line. "'

	";
					$resultlinecheck = DB_query($sqllinecheck,$db);
		//	$TransferRowB = DB_fetch_array($resultB);
	if(mysqli_num_rows($resultlinecheck)>0)
	{
	
	
	$sqlO="select MAX(lineoptionno) as maxlineoptionno from ocdetails where orderno = ".$_GET['OrderConfirmationNo']."
	
AND orderlineno = ".$line.""; 		
$resultO = DB_query($sqlO,$db);
$resultrowO=DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";
	
for ($option=0; $option<= $resultrowO['maxlineoptionno']; $option++ )
	{
				$sqloptioncheck = "SELECT *
	FROM ocdetails 
	WHERE ocdetails.orderno='" .$_GET['OrderConfirmationNo'] . "'
	AND ocdetails.orderlineno='" .$line. "'
	AND ocdetails.lineoptionno='" .$option. "'

	";
					$resultoptioncheck = DB_query($sqloptioncheck,$db);
		//	$TransferRowB = DB_fetch_array($resultB);
	if(mysqli_num_rows($resultoptioncheck)>0)
	{

		
		$sqlA = "SELECT *
	FROM ocdetails INNER JOIN stockmaster
		ON ocdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE ocdetails.orderno='" . $_GET['OrderConfirmationNo'] . "'
	
	";
					$resultA = DB_query($sqlA,$db);
			$TransferRow = DB_fetch_array($resultA);
			
//$line  =0;
$sqlB = "SELECT *
	FROM oclines 
	WHERE oclines.orderno='" . $_GET['OrderConfirmationNo'] . "'
	AND oclines.lineno='" .$line. "'
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
$clt = html_entity_decode($TransferRowB['clientrequirements']);
$html2.= '<br><br><br><div style="padding:10px;"><table nobr="true" border = "1" width = "100%">
<tr><td colspan="7">' . '<h3>Line No. '.($line+1).'</h3>' . '</td></tr>
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
	FROM ocoptions 
	WHERE ocoptions.orderno='" . $_GET['OrderConfirmationNo'] . "'
	AND ocoptions.lineno='" .$line. "'
	AND ocoptions.optionno='" .$option. "'
	
	";
					$resultC = DB_query($sqlC,$db);
			$TransferRowC = DB_fetch_array($resultC);
$lineindex = $line + 1 ;
$html2.= '<tr><td width = "5%" align = "center">'.$lineindex .'</td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
$html2.= '<td width = "25%">'.$TransferRow['description'].'</td>';
$html2.= '<td width = "5%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],0).'</center></td>
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
	
	}} while ($TransferRow = DB_fetch_array($resultA));}
	}
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
<td colspan="2" align="center">' . 'Stock Status: '.$TransferRowC['stockstatus'].'</td>
<td colspan="3" align="center">' . 'Unit Rate: '.locale_number_format($total,2).'</td>

<td colspan="3" align="center">' . 'Total: Rs.'.locale_number_format($TransferRowC['quantity']*$total,2).'</td></tr>
</table></div>';
}
}
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
//$pdf->OutputD($_SESSION['DatabaseName'] . '_OrderConfirmation_' . $_GET['OrderConfirmationNo'] . '.pdf');
 $pdf->Output(dirname(__FILE__).'/tempPDF/oc/'.$_GET['OrderConfirmationNo'].'int.pdf', 'F');
$pdf->__destruct();
echo json_encode([]);
//$pdf->__destruct();

?>