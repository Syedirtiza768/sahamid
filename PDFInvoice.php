<?php

	/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
	include('includes/session.inc');
	$Title = _('Stock Location Transfer Docket Error');
	$document= 'invoiceNo: '.$_GET['InvoiceNo'];
	include('includes/PDFStarter2.php');

	$pdf->addInfo('Title', _('Internal Invoice') );
	$pdf->addInfo('Subject', _('Internal Invoice') . ' # ' . $_GET['InvoiceNo']);

	$pdf->SetAutoPageBreak(TRUE, 15);
	$FontSize=10;
	$PageNumber=0;
	$line_height=30;

	$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' 
			.  _('This page must be called with a location transfer reference number').'.';
	$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was'); 

	$SQL = "SELECT invoice.customerref,
			invoice.salescaseref,
			invoice.contactphone,
			invoice.contactemail,
			invoice.comments,
			invoice.invoicedate,
			invoice.deliverto,
			invoice.deladd1,
			invoice.deladd2,
			invoice.deladd3,
			invoice.deladd4,
			invoice.deladd5,		
			invoice.gst,
			invoice.services,
			invoice.branchcode,
			invoice.shopinvoiceno,
			invoice.invoicesdate,
			dba.companyaddress,
			debtorsmaster.dba,
			debtorsmaster.name,
			debtorsmaster.currcode,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			shippers.shippername,
			locations.taxprovinceid,
			locations.locationname,
			salesman.smantel,
			salesman.salesmanname,
			www_users.email as salesmanemail,
			currencies.decimalplaces AS currdecimalplaces
		FROM invoice 
		INNER JOIN debtorsmaster
		ON invoice.debtorno=debtorsmaster.debtorno
		INNER JOIN shippers
		ON invoice.shipvia=shippers.shipper_id
		INNER JOIN locations
		ON invoice.fromstkloc=locations.loccode
		INNER JOIN currencies
		ON debtorsmaster.currcode=currencies.currabrev
		INNER JOIN custbranch 
		ON debtorsmaster.debtorno = custbranch.debtorno
		INNER JOIN dba 
		ON dba.companyname = debtorsmaster.dba
		INNER JOIN salesman
		ON custbranch.salesman = salesman.salesmancode
		INNER JOIN www_users 
		ON salesman.salesmanname = www_users.realname
		WHERE invoice.invoiceno='" . $_GET['InvoiceNo'] ."'";

	$result = DB_query($SQL,$db, $ErrMsg);

	$invoiceDetails = mysqli_fetch_assoc($result);

	If (DB_num_rows($result)==0){

		include ('includes/header.inc');
		prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' 
			. _('enter the items to be transferred first'),'error');
		include ('includes/footer.inc');
		exit;
	
	}

	$img = glob($_SESSION['part_pics_dir'] . '/companylogos/' .$invoiceDetails['dba'].'*');
	$pdf->addJpegFromFile($img[0], 20, 750, 0, 60);

	$lineIndex = 0;
	$optionIndex = 0;

	$html = "";
	$html .= '<div align = "center"> 
				<h1>'.$invoiceDetails['dba'].'</h1>
				<h3>'.$invoiceDetails['companyaddress'].'</h3>
				<h1 align="right">Internal Invoice</h1>
			</div>';

	$html .= '<div align = "center" style = "padding-top:100px;">
				</div>
					<div style = "border:1px; height:auto; width:auto; " >';

	$html .= '<table border="1">
				<tr>
					<td>Customer Name</td>
					<td>'.$invoiceDetails['name'].'</td>
					<td>Branch Code</td>invoicesdate
					<td>'.$invoiceDetails['branchcode'].'</td>
				</tr>';

	$html .= '<tr>
				<td>Customer Ref.</td>
				<td>'.$invoiceDetails['customerref'].'</td>
				<td>Invoice No#</td>
				<td>
					Internal: '.$_GET['InvoiceNo'].'<br>
					Shop: '.$invoiceDetails['shopinvoiceno'].'
				</td>
			</tr>';

	$html .= '<tr>
				<td>Sales Case Ref</td>
				<td>'.$invoiceDetails['salescaseref'].'</td>
				<td>Invoice Date</td>
				<td>'.ConvertSQLDateTime($invoiceDetails['invoicesdate']).'</td>
			</tr>';

	$html .= '<tr>
				<td>Sales Person</td>
				<td>'.$invoiceDetails['salesmanname'].'</td>
				<td>Printing Date</td>
				<td>'.Date($_SESSION['DefaultDateFormat']).'</td>
			</tr>';

	$html .= '<tr>
				<td>Sales Person Contact no</td>
				<td>'.$invoiceDetails['smantel'].'</td>
				<td>Sales Person Email</td>
				<td>'.$invoiceDetails['salesmanemail'].'</td>
			</tr>';

	$html .= '<tr>
				<td>Customer Contact No</td>
				<td>'.$invoiceDetails['contactphone'].'</td>
				<td>Customer Email</td>
				<td>'.$invoiceDetails['contactemail'].'</td>
			</tr>';

	$html .= '<tr>
				<td>Invoice For</td>
				<td>'.$invoiceDetails['name'].
					'<br>'.$invoiceDetails['address1'].
					'<br>'.$invoiceDetails['address2'].
					'<br>'.$invoiceDetails['address3'].
					'<br>'.$invoiceDetails['address4'].
					'<br>'.$invoiceDetails['address5'].
				'</td>';

	$html .= '<td>Bill To</td>
				<td>'.$invoiceDetails['deliverto'].
					'<br>'.$invoiceDetails['deladd1'].
					'<br>'.$invoiceDetails['deladd2'].
					'<br>'.$invoiceDetails['deladd3'].
					'<br>'.$invoiceDetails['deladd4'].
					'<br>'.$invoiceDetails['deladd4'].
				'</td>';

	$html .= '</tr></table></div><div style = "height:500px;"></div>';

	$SQL = "SELECT * FROM invoicelines
			WHERE invoiceno='". $_GET['InvoiceNo'] ."'";

	$lineResult = mysqli_query($db, $SQL);

	while($invoiceLine = mysqli_fetch_assoc($lineResult)){

		$clientRequirements = "";
		$clientRequirements = $invoiceLine['clientrequirements'];
		$clientRequirements = str_replace("</p>","",$clientRequirements);
		$clientRequirements = str_replace("<p>","",$clientRequirements);
		$clientRequirements = str_replace("</div>","",$clientRequirements);
		$clientRequirements = str_replace("<div>","",$clientRequirements);
		$clientRequirements = ($clientRequirements);

	 	$html .= '<br><br><br><div style="padding:10px;">
					<table nobr="true" border = "1" width = "100%">
						<tr>
							<td colspan="7">
								<h3>Line No. '.($lineIndex+=1).'</h3>
							</td>
						</tr>
						<tr>
							<td colspan="7">Client Required.<br /> '.$clientRequirements.'</td>
						</tr>
						<tr align = "center">
							<td width = "5%">Sr#</td>
							<td width = "10%" align = "center">Model No</td>
							<td width = "10%" align = "center">Part No</td>
							<td width = "10%" align = "center">Brand</td>
							<td width = "15%">Description</td>
							<td width = "5%" align = "center">qty.</td>
							<td width = "10%" align = "center">List Price</td>
							<td width = "5%" align = "center">Disc.</td>
							<td width = "10%" align = "center">Unit Rate</td>
							<td width = "10%" align = "center">Sub Total</td>
							<td width = "10%" align = "center">+GST -GST</td>
						</tr>';

		$SQL = "SELECT * FROM invoiceoptions 
				WHERE invoiceno='". $invoiceLine['invoiceno'] ."'
				AND invoicelineno='". $invoiceLine['invoicelineno'] ."'";

		$optionResult = mysqli_query($db, $SQL);

		while($lineOption = mysqli_fetch_assoc($optionResult)){

			$total = 0;

			$SQL = "SELECT * FROM invoicedetails
					INNER JOIN stockmaster
					ON invoicedetails.stkcode=stockmaster.stockid
					INNER JOIN manufacturers 
					ON manufacturers.manufacturers_id = stockmaster.brand
					WHERE invoiceno = '". $invoiceLine['invoiceno'] ."'
					AND invoicelineno = '". $lineOption['invoicelineno'] ."'
					AND invoiceoptionno = '". $lineOption['invoiceoptionno'] ."'";

			$itemsResult = mysqli_query($db, $SQL);

			while($optionItem = mysqli_fetch_assoc($itemsResult)){

				if($invoiceDetails['gst'] == '' || $invoiceDetails['gst'] == 'exclusive' && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){

					$val = locale_number_format(round(($optionItem['unitprice'])*$optionItem['quantity']*
						(1 - $optionItem['discountpercent']),$optionItem['decimalplaces']));
					$total += ($optionItem['unitprice']*$optionItem['quantity']*(1 - $optionItem['discountpercent']));

				}else if($invoiceDetails['services'] == 1 && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){

					$val = locale_number_format(round(($optionItem['unitprice']/1.16)*$optionItem['quantity']*
						(1 - $optionItem['discountpercent']),$optionItem['decimalplaces']));
					$total += (($optionItem['unitprice']/1.16)*$optionItem['quantity']*(1 - $optionItem['discountpercent']));

				}else if($invoiceDetails['services'] == 0 && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){

					$val = locale_number_format(round(($optionItem['unitprice']/1.17)*$optionItem['quantity']*
						(1 - $optionItem['discountpercent']),$optionItem['decimalplaces']));
					$total += (($optionItem['unitprice']/1.17)*$optionItem['quantity']*(1 - $optionItem['discountpercent']));

				}

				if($invoiceDetails['gst'] == '' || $invoiceDetails['gst'] == 'exclusive' && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){

					$val = locale_number_format(round(($optionItem['unitprice'])*$optionItem['quantity']*
						(1 - $optionItem['discountpercent']),$optionItem['decimalplaces']));
					$total += ($optionItem['unitprice']*$optionItem['quantity']*(1 - $optionItem['discountpercent']));

				}else if($invoiceDetails['services'] == 1 && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){

					$val = locale_number_format(round(($optionItem['unitprice']/1.16)*$optionItem['quantity']*
						(1 - $optionItem['discountpercent']),$optionItem['decimalplaces']));
					$total += (($optionItem['unitprice']/1.16)*$optionItem['quantity']*(1 - $optionItem['discountpercent']));

				}else if($invoiceDetails['services'] == 0 && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){

					$val = locale_number_format(round(($optionItem['unitprice']/1.17)*$optionItem['quantity']*
						(1 - $optionItem['discountpercent']),$optionItem['decimalplaces']));
					$total += (($optionItem['unitprice']/1.18)*$optionItem['quantity']*(1 - $optionItem['discountpercent']));

				}

				$html .= '<tr>
							<td width = "5%" align = "center">'.$lineIndex .'</td>
							<td width = "10%" align = "center">
								<center>'.$optionItem['mnfCode'].'</center>
							</td>
							<td width = "10%" align = "center">
								<center>'.$optionItem['mnfpno'].'</center>
							</td>
							<td width = "10%" align = "center">
								<center>'.$optionItem['manufacturers_name'].'</center>
							</td>
							<td width = "15%">'.$optionItem['description'].'</td>
							<td width = "5%" align = "center">
								<center>'.locale_number_format($optionItem['quantity'],0).'</center>
							</td>
							<td width = "10%" align = "center">
								<center>'.locale_number_format($optionItem['unitprice'],$optionItem['decimalplaces']).'</center>
							</td>
							<td width = "5%"align = "center">
								<center>'.locale_number_format($optionItem['discountpercent']*100,2).'</center>
							</td>
							<td width = "10%">
								Rs.'.locale_number_format(round($optionItem['unitprice']*(1 - $optionItem['discountpercent'])),2).'
							</td>
							<td width = "10%"align = "center">
								<center>'
									.locale_number_format(round(
										$optionItem['unitprice']*
										$optionItem['quantity']*
										(1 - $optionItem['discountpercent']),
										$optionItem['decimalplaces']
									)).'
								</center>
							</td>
							<td width = "10%"align = "center">
								<center>'
									.$val.'
								</center>
							</td>
						</tr>';

			}

			$optionText = "";
			$optionText = $lineOption['optiontext'];
			$optionText = str_replace("</p>","",$optionText);
			$optionText = str_replace("<p>","",$optionText);
			$optionText = str_replace("</div>","",$optionText);
			$optionText = str_replace("<div>","",$optionText);
			$optionText = html_entity_decode($optionText);

			$html .= '<tr>
						<td colspan="11">We Offer.<br /> '.$optionText.'</td>
					</tr>
					<tr>
						<td colspan="3" align="center">Qty: '.$lineOption['quantity'].'</td>
						<td colspan="4" align="center">Unit Rate: '.locale_number_format(round($total),2).'</td>
						<td colspan="4" align="center">Total: Rs.'.locale_number_format(round($lineOption['quantity']*$total),2).'</td>
					</tr></table></div>';

		}

	}

	$sqlquotevalue="SELECT invoice.salescaseref, invoicedetails.invoiceno,
 		SUM(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity )
 		AS value FROM invoicedetails 
 		INNER JOIN invoiceoptions 
 		ON (invoicedetails.invoiceno = invoiceoptions.invoiceno 
 			AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
 		INNER JOIN invoice ON invoice.invoiceno = invoicedetails.invoiceno 
 		WHERE invoicedetails.invoiceoptionno = 0 
 		AND invoiceoptions.invoiceoptionno = 0 
 		AND invoice.invoiceno='".$_GET['InvoiceNo'] ."'";

 	$resultsqlquotevalue=DB_query($sqlquotevalue,$db);
 	$rowquotevalue=DB_fetch_array($resultsqlquotevalue);

 	if($invoiceDetails['gst'] == '' && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){
 		if($invoiceDetails['services']==1){
 			$rowquotevalue['tax']=$rowquotevalue['value']*0.16;
		}else{
	 		$rowquotevalue['tax']=$rowquotevalue['value']*0.17;
		}	
 	}else{
 		$rowquotevalue['tax'] = 0;
 	}

	 if($invoiceDetails['gst'] == 'exclusive' && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){
		if($invoiceDetails['services']==1){
			$rowquotevalue['tax']=$rowquotevalue['value']*0.16;
	   }else{
			$rowquotevalue['tax']=$rowquotevalue['value']*0.18;
	   }	
	}else{
		$rowquotevalue['tax'] = 0;
	}

 	if ($invoiceDetails['gst'] == 'inclusive' && ($invoiceDetails['services']==1 ) && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){

		$rowquotevalue['value']= ($rowquotevalue['value']/1.16);
		$rowquotevalue['tax'] = ($rowquotevalue['value']*0.16);

	}else if ($invoiceDetails['gst'] == 'inclusive' && ($invoiceDetails['services']==0) && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){
 
		$rowquotevalue['value']= ($rowquotevalue['value']/1.17);
		$rowquotevalue['tax'] = ($rowquotevalue['value']*0.17);

 	}
 	
	
	if ($invoiceDetails['gst'] == 'inclusive' && ($invoiceDetails['services']==1 ) && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){

		$rowquotevalue['value']= ($rowquotevalue['value']/1.16);
		$rowquotevalue['tax'] = ($rowquotevalue['value']*0.16);

	}else if ($invoiceDetails['gst'] == 'inclusive' && ($invoiceDetails['services']==0) && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){
 
		$rowquotevalue['value']= ($rowquotevalue['value']/1.18);
		$rowquotevalue['tax'] = ($rowquotevalue['value']*0.18);

 	}

	$html .= '<div align="right">
 				<table border="1">
 					<tr>
 						<td><b>SUBTOTAL</b></td>
 						<td>'.locale_number_format(round($rowquotevalue['value']),2).'</td>
 					</tr>';
					 
					 if($invoiceDetails['gst'] == 'update' ){
				 
						 $html .= '<tr>
									 <td><b>GST '.(($TransferRow2['services'] == 1) ? '16%':'18%').'</b></td>
									 <td>'.locale_number_format(round($rowquotevalue['tax']),2).'</td>
								 </tr>';
					 
					 }

 	if($invoiceDetails['gst'] == 'exclusive' && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){

		$html .= '<tr>
					<td><b>GST '.(($TransferRow2['services'] == 1) ? '16%':'17%').'</b></td>
					<td>'.locale_number_format(round($rowquotevalue['tax']),2).'</td>
				</tr>';
	
	}
 	
	if($invoiceDetails['gst'] == 'inclusive' && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){

		$html .= '<tr>
					<td><b>GST '.(($TransferRow2['services'] == 1) ? '16%':'17%').'</b></td>
					<td>'.locale_number_format(round($rowquotevalue['tax']),2).'</td>
				</tr>';
	
	}

	if($invoiceDetails['gst'] == '' && strtotime($invoiceDetails['invoicesdate']) < strtotime('2023-02-14')){

		$html .= '<tr>
					<td><b>GST '.(($TransferRow2['services'] == 1) ? '16%':'17%').'</b></td>
					<td>'.locale_number_format(round($rowquotevalue['tax']),2).'</td>
				</tr>';
	
	}


 	if($invoiceDetails['gst'] == 'exclusive' && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){

		$html .= '<tr>
					<td><b>GST '.(($TransferRow2['services'] == 1) ? '16%':'18%').'</b></td>
					<td>'.locale_number_format(round($rowquotevalue['tax']),2).'</td>
				</tr>';
	
	}
 	if($invoiceDetails['gst'] == 'inclusive' && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){

		$html .= '<tr>
					<td><b>GST '.(($TransferRow2['services'] == 1) ? '16%':'18%').'</b></td>
					<td>'.locale_number_format(round($rowquotevalue['tax']),2).'</td>
				</tr>';
	
	}

 	if($invoiceDetails['gst'] == '' && strtotime($invoiceDetails['invoicesdate']) >= strtotime('2023-02-14')){

		$html .= '<tr>
					<td><b>GST '.(($TransferRow2['services'] == 1) ? '16%':'18%').'</b></td>
					<td>'.locale_number_format(round($rowquotevalue['tax']),2).'</td>
				</tr>';
	
	}
  
 	$html .= '<tr>
 				<td><b>Grand Total</b></td>
 				<td>'.locale_number_format(round($rowquotevalue['value']+$rowquotevalue['tax']),2).'</td>
 			</tr></table></div>';

	$pdf->writeHTML($html, true, false, true, false, '');
	ob_end_clean();
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_Invoice_' . $_GET['InvoiceNo'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_Invoice_' . $_GET['InvoiceNo'] . '.pdf','I');

    $pdf->__destruct();
	return;

?>