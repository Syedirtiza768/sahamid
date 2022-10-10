<?php
	
	include('includes/session.inc');
	include('quotation/misc.php');
	$Title = _('Stock Location Transfer Docket Error');
	$_SESSION['document']= 'QuotationNo: '.$_GET['QuotationNo'];
	include('includes/PDFStarter2.php');

	$pdf->addInfo('Title', _('Quotation') );
	$pdf->addInfo('Subject', _('Quotation') . ' # ' . $_GET['QuotationNo']);

	$pdf->SetAutoPageBreak(TRUE, 15);
	
	$FontSize=10;
	$PageNumber=0;
	$line_height=30;

	$orderno = $_GET['QuotationNo'];

	$db = createDBConnection();

	$SQL = "SELECT salesordersip.debtorno,
 				   debtorsmaster.name,
				   salesordersip.branchcode,
				   salesordersip.existing,
				   salesordersip.eorderno,
				   salesordersip.customerref,
				   salesordersip.comments,
				   salesordersip.orddate,
				   salesordersip.ordertype,
				   salesordersip.shipvia,
				   salesordersip.deliverto,
				   salesordersip.deladd1,
				   salesordersip.deladd2,
				   salesordersip.deladd3,
				   salesordersip.deladd4,
				   salesordersip.deladd5,
				   salesordersip.deladd6,
				   salesordersip.quotedate,
				   salesordersip.confirmeddate,
				   salesordersip.contactphone,
				   salesordersip.contactemail,
				   salesordersip.salesperson,
				   salesordersip.GSTadd,
				   salesordersip.gst,
				   salesordersip.salescaseref,
				   salesordersip.WHT,
				   salesordersip.services,
				   salesordersip.freightcost,
				   salesordersip.advance,
				   salesordersip.delivery,
				   salescase.salesman as salesmann,
				   salesordersip.commisioning,
				   salesordersip.after,
				   salesordersip.afterdays,
				   salesordersip.deliverydate,
				   debtorsmaster.currcode,
				   currencies.decimalplaces,
				   paymentterms.terms,
				   salesordersip.fromstkloc,
				   salesordersip.printedpackingslip,
				   salesordersip.datepackingslipprinted,
				   salesordersip.quotation,
				   salesordersip.deliverblind,
				   debtorsmaster.customerpoline,
				   locations.locationname,
				   custbranch.estdeliverydays,
				   custbranch.salesman
				FROM salesordersip
				INNER JOIN salescase
				ON salesordersip.salescaseref = salescase.salescaseref
				INNER JOIN debtorsmaster
				ON salesordersip.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch
				ON salesordersip.debtorno = custbranch.debtorno
				AND salesordersip.branchcode = custbranch.branchcode
				INNER JOIN paymentterms
				ON debtorsmaster.paymentterms=paymentterms.termsindicator
				INNER JOIN locations
				ON locations.loccode=salesordersip.fromstkloc
				INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
				WHERE salesordersip.orderno = '" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Quotation Not Found!!!'
		];

		echo json_encode($response);
		return;	

	}

	$details = mysqli_fetch_assoc($result);

	//Items
	$SQL = "SELECT  salesorderdetailsip.internalitemno,
					salesorderdetailsip.salesorderdetailsindex,
					salesorderdetailsip.orderlineno,
					salesorderdetailsip.lineoptionno,
					salesorderdetailsip.optionitemno,
					salesorderdetailsip.stkcode,
					stockmaster.description,
					stockmaster.longdescription,
					stockmaster.materialcost,
					stockmaster.volume,
					stockmaster.grossweight,
					stockmaster.units,
					stockmaster.serialised,
					stockmaster.nextserialno,
					stockmaster.eoq,
					salesorderdetailsip.unitprice,
					salesorderdetailsip.quantity,
					salesorderdetailsip.discountpercent,
					salesorderdetailsip.actualdispatchdate,
					salesorderdetailsip.qtyinvoiced,
					salesorderdetailsip.narrative,
					salesorderdetailsip.itemdue,
					salesorderdetailsip.poline,
					locstock.quantity as qohatloc,
					stockmaster.mbflag,
					stockmaster.discountcategory,
					stockmaster.decimalplaces,
					stockmaster.lastupdatedby,
					stockmaster.lastcostupdate,
					stockmaster.mnfcode,
					stockmaster.mnfpno,
					stockmaster.categoryid,
					manufacturers.manufacturers_name as manu_name,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
					salesorderdetailsip.completed
				FROM salesorderdetailsip INNER JOIN stockmaster
				ON salesorderdetailsip.stkcode = stockmaster.stockid
				INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
				INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
				WHERE  locstock.loccode = '" . $details['fromstkloc'] . "'
				AND salesorderdetailsip.orderno ='" . $orderno . "'
				ORDER BY salesorderdetailsip.orderlineno";

	$result = mysqli_query($db, $SQL);

	$items = [];

	while($row = mysqli_fetch_assoc($result)){

		$line 	= $row['orderlineno'];
		$option = $row['lineoptionno'];

		if(!(array_key_exists($line, $items))){

			$items[$line] = [];			

		}

		if(!(array_key_exists($option, $items[$line]))){

			$items[$line][$option] = [];

		}

		$items[$line][$option][] = $row;

	}


	//Options
	$SQL = "SELECT * FROM salesorderoptionsip 
			WHERE  salesorderoptionsip.orderno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	$options = [];

	while($row = mysqli_fetch_assoc($result)){

		$line 		 = $row['lineno'];
		$optionindex = $row['optionindex'];

		$options[$line][$optionindex] = $row;

		$options[$line][$optionindex]['items'] = 
			((isset($items[$line]) && isset($items[$line][$optionindex]))
			? $items[$line][$optionindex] : []);


	}

	//Lines
	$SQL = "SELECT * FROM salesorderlinesip 
			WHERE  salesorderlinesip.orderno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	$lines = [];

	while($row = mysqli_fetch_assoc($result)){

		$lineindex = $row['lineindex'];
		
		$lines[$lineindex] = $row;

		$lines[$lineindex]['options'] = (isset($options[$lineindex])) ? $options[$lineindex] : [];

	}

	$details['lines'] = $lines;

	$SQL = "SELECT * from salesordersip INNER JOIN
			debtorsmaster 
			ON salesordersip.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch 
			ON debtorsmaster.debtorno = custbranch.debtorno
			INNER JOIN dba ON dba.companyname = debtorsmaster.dba
			INNER JOIN salesman
			ON custbranch.salesman = salesman.salesmancode
			WHERE orderno = '".$orderno."'";

	$result = mysqli_query($db,$SQL);

	$row = mysqli_fetch_assoc($result);
	
	$salesman = $row['salesmanname'];

	$SQL = "SELECT * from salesman 
			inner join www_users on salesman.salesmanname = www_users.realname
			WHERE salesman.salesmanname = '" . $row['salesmanname'] ."'";

	$result = mysqli_query($db, $SQL);

	$salesperson = mysqli_fetch_assoc($result);

	If (mysqli_num_rows($result)==0){

		echo "SalesManNotFound";
	}

	$img = glob($_SESSION['part_pics_dir'] . '/companylogos/' .$TransferRowS['dba'].'*');
	
	$pdf->addJpegFromFile($img[0], 20, 750, 0, 60);

	$html = '<div align = "center">';
	$html .= '<h1></h1>';
	$html .= '<h3>Preview Quotation</h3>';
	$html .= '<h1 align="right">QUOTATION</h1>';
	$html .= '</div>';

	$html .= '<div align="center" style="padding-top:100px;"></div>';
	$html .= '<div style="border:1px; height:auto; width:auto;">';
	$html .= '<table border="1">';
	$html .= '<tr><td>Customer Name</td><td>'.$details['name'].'</td>';
	$html .= '<td>Branch Code</td><td>'.$details['branchcode'].'</td></tr>';

	$html .= '<tr><td>Customer Ref.</td><td>'.$details['customerref'].'</td>';
	$html .= '<td>Quotation No#</td><td>Quotaion In Progress</td></tr>';

	$html .= '<tr><td>Sales Case Ref</td><td>'.$details['salescaseref'].'</td>';
	$html .= '<td>Dispatch Date</td><td>'.ConvertSQLDateTime($details['orddate']).'</td></tr>';

	$html .= '<tr><td>Sales Person</td><td>'.$salesman.'</td>';
	$html .= '<td>Printing Date</td><td>'.Date($_SESSION['DefaultDateFormat']).'</td></tr>';

	$html .= '<tr><td>Sales Person Contact no</td><td>'.$salesperson['smantel'].'</td>';
	$html .= '<td>Sales Person Email</td><td>'.$salesperson['email'].'</td></tr>';

	$html .= '<tr><td>Customer Contact no</td><td>'.$details['contactphone'].'</td>';
	$html .= '<td>Customer Email</td><td>'.$details['contactemail'].'</td></tr>';

	$html .='<tr><td>Quotation For</td><td>'.$details['name'].'<br>';
	$html .= utf8_encode($details['deladd1']).'<br>';
	$html .= utf8_encode($details['deladd2']).'<br>';
	$html .= utf8_encode($details['deladd3']).'<br>';
	$html .= utf8_encode($details['deladd4']).'<br>';
	$html .= utf8_encode($details['deladd5']).'<br></td>';

	$html .='<td>Bill To</td><td>'.$details['deliverto'].'<br>';
	$html .= utf8_encode($details['deladd1']).'<br>';
	$html .= utf8_encode($details['deladd2']).'<br>';
	$html .= utf8_encode($details['deladd3']).'<br>';
	$html .= utf8_encode($details['deladd4']).'<br>';
	$html .= utf8_encode($details['deladd4']).'<br></td>';

	$html .='</tr></table></div><div style = "height:500px;"></div>';

	$lineNo = 1;
	$totalQuote = 0;

	foreach($details['lines'] as $line){

		$optionNo = 1;
		$total 	  = 0;

 		$clientRequirements = $line['clientrequirements'];

		$clientRequirements = str_replace("</p>","",$clientRequirements);
		$clientRequirements = str_replace("<p>","",$clientRequirements);
		$clientRequirements = str_replace("</div>","",$clientRequirements);
		$clientRequirements = str_replace("<div>","",$clientRequirements);
		$clientRequirements =  utf8_decode($clientRequirements);

		foreach($line['options'] as $option){

			$html .= '<br><br><br>';
			$html .= '<div style="padding:10px;">';
			$html .= '<table nobr="true" border = "1" width = "100%">';
			$html .= '<tr><td colspan="7">';
			$html .= '<h3>Line No. '.$lineNo.' Option No. '.$optionNo.'</h3>';
			$html .= '</td></tr>';

			$html .= '<tr><td colspan="7">';
			$html .= 'Client Required.<br /> '.$clientRequirements;
			$html .= '</td></tr>';

			$html .= '<tr align = "center">';
			$html .= '<td width = "5%">'.'Sr#'.'</td>';

			$html .= '<td width = "10%" align = "center">'.'Model No'.'</td>';
			$html .= '<td width = "10%" align = "center">'.'Part No'.'</td>';
			$html .= '<td width = "10%" align = "center">'.'Brand'.'</td>';
			$html .= '<td width = "25%">'.'Description'.'</td>';
			$html .= '<td width = "5%" align = "center">'.'qty.'.'</td>';
			$html .= '<td width = "10%" align = "center">'.'List Price'.'</td>';
			$html .= '<td width = "5%" align = "center">'.'Disc.'.'</td>';
			$html .= '<td width = "10%" align = "center">'.'Unit Rate'.'</td>';
			$html .= '<td width = "10%" align = "center">'.'Sub Total'.'</td>';
			$html .= '</tr>';

			$itemNo = 1;

			foreach ($option['items'] as $item) {

				$unitprice 	= $item['standardcost'];
				$quantity 	= $item['quantity'];
				$discount  	= $item['discountpercent'];

				$html .= '<tr><td width = "5%" align = "center">'.$itemNo .'</td>';
				$html .= '<td width="10%" align="center">';
				$html .= '<center>'.$item['mnfcode'].'</center>';
				$html .= '</td>';
				$html .= '<td width="10%" align="center">';
				$html .= '<center>'.$item['mnfpno'].'</center>';
				$html .= '</td>';
				$html .= '<td width="10%" align="center">';
				$html .= '<center>'.$item['manu_name'].'</center>';
				$html .= '</td>';
				$html .= '<td width="25%">'.$item['description'].'</td>';
				$html .= '<td width="5%" align="center">';
				$html .= '<center>'.locale_number_format($quantity,0).'</center>'; //quantity
				$html .= '</td>';
				$html .= '<td width="10%" align="center">';
				$html .= '<center>'.locale_number_format($unitprice,0).'</center>'; //unitprice
				$html .= '</td>';
				$html .= '<td width="5%" align="center">';
				$html .= '<center>'.locale_number_format($discount*100,2).'</center>'; //discount
				$html .= '</td>';
				$html .= '<td width="10%">Rs.';
				$html .= locale_number_format(($unitprice*(1 - $discount)),2);
				$html .= '</td>';

				$total += ($unitprice*$quantity*(1-$discount));

				$html .= '<td width="10%" align="center">';
				$html .= '<center>';
				$html .= locale_number_format(($unitprice*$quantity*(1-$discount)),2);
				$html .= '</center>';
				$html .= '</td></tr>';
			
				$itemNo += 1;
			
			}

			$optionText = $option['optiontext'];
			$optionText = str_replace("</p>","",$optionText);
			$optionText = str_replace("<p>","",$optionText);
			$optionText = str_replace("</div>","",$optionText);
			$optionText = utf8_encode(str_replace("<div>","",$optionText));

			$optionQuantity = $option['quantity'];
			$stockStatus 	= $option['stockstatus'];

			$html .= '<tr><td colspan="9"> We Offer.<br /> ';
			$html .= html_entity_decode($optionText).'</td></tr>';

			$html .= '<tr><td colspan="2" align="center"> Quantity: '.$optionQuantity.'</td>';
			$html .= '<td colspan="5" align="center"> Stock Status: '.$stockStatus.'</td>';
			$html .= '<td colspan="3" align="center"> Total: Rs.';
			$html .= locale_number_format(round($optionQuantity*$total),2).'</td></tr>';
			$html .= '</table></div>';

			$totalQuote += $total;

			$optionNo += 1;

		}



		$lineNo += 1;

	}

	$html .= '<div align="right"><table border="1">';
 	
 	if ($details['GSTadd'] == 'exclusive'){
		
		$html .= '<tr><td><b>SUBTOTAL</b></td><td>'.locale_number_format(round($totalQuote),2).'</td></tr>';
  
  		if ($details['services'] == 1){

			$html .= '<tr><td><b>16% GST</b></td><td>'.($totalQuote*0.16).'</td></tr>';
   
   			if ($details['WHT'] != 0){

				$html .= '<tr><td>';
				$html .= '<b>'.$details['WHT'].'% Witholding Tax</b>';
				$html .= '</td>';
				$html .= '<td>'.locale_number_format($totalQuote*$details['WHT']/100,2).'</td></tr>';	  
				$html .= '<tr><td><b>Grand Total</b></td><td>';
 				$html .= locale_number_format($totalQuote*1.16+$totalQuote*$details['WHT']/100,2);
 				$html .= '</td></tr>';
		 	
		 	}else{
			
				$html .= '<tr><td><b>Grand Total</b></td><td>';
 				$html .= locale_number_format($totalQuote*1.16,2).'</td></tr>';
			 
		 	}
 		
 		}else {

			$html .= '<tr><td><b>17% GST</b></td><td>';
 			$html .= locale_number_format($totalQuote*0.17,2) .'</td></tr>';

   			if ($TransferRow2['WHT'] != 0){

				$html .= '<tr><td><b>'.$details['WHT'].'% Witholding Tax</b></td><td>';
	 			$html .= locale_number_format($totalQuote*$details['WHT']/100,2).'</td></tr>';	  

				$html .= '<tr><td><b>Grand Total</b></td><td>';
	 			$html .= locale_number_format($totalQuote*1.17+$totalQuote*$details['WHT']/100,2);
	 			$html .= '</td></tr>';

		 	}else {
			
				$html .= '<tr><td><b>Grand Total</b></td><td>';
 				$html .= locale_number_format($totalQuote*1.17,2).'</td></tr>';
			 
		 	}
	  
  		}
  
	}else if ($details['GSTadd'] == 'inclusive') {
 
  		if ($details['services'] == 1){

   			if ($details['WHT'] != 0){
			 
			 	$html .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>';
 				$html .= locale_number_format($totalQuote,2) .' </td></tr>';	

				$html .= '<tr><td><b>'.$details['WHT'].'% Witholding Tax</b></td><td>';
				$html .= locale_number_format($totalQuote*$details['WHT']/100,2).'</td></tr>';	  
				$html .= '<tr><td><b>Grand Total</b></td><td>';
				$html .= locale_number_format($totalQuote+$totalQuote*$details['WHT']/100,2);
				$html .= '</td></tr>';
		 	
		 	}else{
			
				$html .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>';
				$html .= locale_number_format($totalQuote,2).' </td></tr>';
			 
		 	}

  		}else {

   			if ($TransferRow2['WHT'] != 0){
			 
				$html .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>';
 				$html .= locale_number_format($totalQuote,2).' </td></tr>';	  
				$html .= '<tr><td><b>'.$details['WHT'].'% Witholding Tax</b></td><td>';
 				$html .= locale_number_format($totalQuote*$details['WHT']/100,2).'</td></tr>';	  
				$html .= '<tr><td><b>Grand Total</b></td><td>';
 				$html .= locale_number_format($totalQuote+$totalQuote*$details['WHT']/100,2);
 				$html .= '</td></tr>';
		 
		 	}else {
			
				$html .= '<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>';
 				$html .= locale_number_format($totalQuote,2).' </td></tr>';
			 
		 	}
 	  
  		}

 	} else if ($TransferRow2['GSTadd'] == '') {
 
   		if ($TransferRow2['WHT'] != 0) {
			 
			$html .= '<tr><td><b>SUBTOTAL</b></td><td>';
 			$html .= locale_number_format($totalQuote,2).'</td></tr>';	  
			$html .= '<tr><td><b>'.$details['WHT'].'% Witholding Tax</b></td><td>';
 			$html .= locale_number_format($totalQuote*$details['WHT']/100,2).'</td></tr>';	  
			$html .= '<tr><td><b>Grand Total</b></td><td>';
 			$html .= locale_number_format($totalQuote+$totalQuote*$details['WHT']/100,2).'</td></tr>';
		 
		} else {
			
			$html .= '<tr><td><b>Grand Total</b></td><td>';
 			$html .= locale_number_format($rowquotevalue['value'],2).'</td></tr>';
			 
		}

 	}
 
 	$html .= '</table></div>';
 	$html .= 'In case of multiple options amount of first option is considered<br/>';
	$html .= 'Payment Terms:<br>';

	if($details['advance']>0)
		$html .= 'Advance '.$details['advance'].'%<br>';

	if($details['delivery']>0)
		$html .= 'On Delivery '.$details['delivery'].'%<br>';

	if($details['commisioning']>0)
		$html .= 'On Commisioning '.$details['commisioning'].'%<br>';

	if($details['after']>0)
		$html .= $details['after'].'% After '.$details['afterdays']. 'days';

	$pdf->writeHTML($html, true, false, true, false, '');

 	$pdf->Output(dirname(__FILE__).'/tempPDF/'.$_GET['QuotationNo'].'ext.pdf', 'F');

 	echo json_encode([]);

 	return;
 	exit;
	

for ($line=0; $line<= $resultrow['maxlineno']; $line++ )
{
	

for ($option=0; $option<= $resultrowO['maxlineoptionno']; $option++ )
	{






$total = 0;
do{
	if(($line== $TransferRow['orderlineno']) AND ($option== $TransferRow['lineoptionno']) ) {
		$sqlC = "SELECT *
	FROM salesorderoptionsip 
	WHERE salesorderoptionsip.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderoptionsip.lineno='" .$line. "'
	AND salesorderoptionsip.optionno='" .$option. "'
	
	";
					$resultC = DB_query($sqlC,$db);
			$TransferRowC = DB_fetch_array($resultC);
$lineindex = $line + 1 ;

	
	}} while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);



	
}}

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
$sqlquotevalue="SELECT salesordersip.salescaseref, salesorderdetailsip.orderno,
 SUM(salesorderdetailsip.unitprice* (1 - salesorderdetailsip.discountpercent)*salesorderdetailsip.quantity*salesorderoptionsip.quantity )
 as value from salesorderdetailsip INNER JOIN salesorderoptionsip on
 (salesorderdetailsip.orderno = salesorderoptionsip.orderno AND 
 salesorderdetailsip.orderlineno = salesorderoptionsip.lineno) 
 INNER JOIN salesordersip on salesordersip.orderno = salesorderdetailsip.orderno 
 WHERE salesorderdetailsip.lineoptionno = 0 and salesorderoptionsip.optionno = 0 and salesordersip.orderno='".$_GET['QuotationNo'] ."'";

 $resultsqlquotevalue=DB_query($sqlquotevalue,$db);
 $rowquotevalue=DB_fetch_array($resultsqlquotevalue);
 





$html4.='</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
 $pdf->writeHTML($html2, true, false, true, false, '');

//$pdf->writeHTML($html3, true, false, true, false, '');
 ob_end_clean();

 //$pdf->OutputD($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf');
 echo json_encode([
 		'status' => 'success'
 	]);
 $pdf->__destruct();

?>