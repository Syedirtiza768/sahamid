<?php
	
	include('includes/session.inc');
	$Title = _('Stock Location Transfer Docket Error');
	$document= 'QuotationNo: '.$_GET['QuotationNo'];
	include('includes/PDFStarter2.php');

	$pdf->setFootTitle("Quotation # ".$_GET['QuotationNo']." -- ");

	$FontSize=10;
	$PageNumber=0;
	$line_height=30;

	$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
	$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');

	$sql = "SELECT salesorders.customerref,
				salesorders.contactphone,
				salesorders.contactemail,
				salesorders.salescaseref,
				salesorders.comments,
				salesorders.orddate,
				salesorders.deliverto,
				salesorders.deladd1,
				salesorders.deladd2,
				salesorders.deladd3,
				salesorders.deladd4,
				salesorders.deladd5,
				salesorders.quotedate,
				salesorders.contactperson,
				salesorders.advance,
				salesorders.delivery,
				salesorders.commisioning,
				salesorders.after,
				salesorders.gst,
				salesorders.afterdays,
				salesorders.GSTadd,
				salesorders.services,
				salesorders.rate_clause,
				salesorders.clause_rates,
				salesorders.freightclause,
				salesorders.rate_validity,
				salesorders.WHT,
				salesorders.umqd,
				salesorders.validity,
				salesorders.comments,
				debtorsmaster.name,
				debtorsmaster.currcode,
				debtorsmaster.address1,
				debtorsmaster.address2,
				debtorsmaster.address3,
				debtorsmaster.address4,
				debtorsmaster.address5,
				debtorsmaster.address6,
				shippers.shippername,
				salesorders.printedpackingslip,
				salesorders.datepackingslipprinted,
				salesorders.branchcode,
				locations.taxprovinceid,
				locations.locationname,
				currencies.decimalplaces AS currdecimalplaces
			FROM salesorders INNER JOIN debtorsmaster
			ON salesorders.debtorno=debtorsmaster.debtorno
			INNER JOIN shippers
			ON salesorders.shipvia=shippers.shipper_id
			INNER JOIN locations
			ON salesorders.fromstkloc=locations.loccode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE salesorders.quotation=1
			AND salesorders.orderno='" . $_GET['QuotationNo'] ."'";

	$result=DB_query($sql,$db, $ErrMsg);
	$TransferRow2 = DB_fetch_array($result);

	$sqlS = "SELECT * from salesorders 
			INNER JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch ON debtorsmaster.debtorno = custbranch.debtorno
			INNER JOIN dba ON dba.companyname = debtorsmaster.dba
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			WHERE orderno = '" . $_GET['QuotationNo'] ."'";

	$resultS=DB_query($sqlS,$db, $ErrMsg);
	$TransferRowS = DB_fetch_array($resultS);
    $currency=$TransferRow2['rate_clause'];
    $values=$TransferRow2['clause_rates'];



	$sqlSalesperson = "SELECT * FROM salesman 
		INNER JOIN www_users on salesman.salesmanname = www_users.realname
		WHERE salesman.salesmanname = '" . $TransferRowS['salesmanname'] ."'";

	$resultSalesperson=DB_query($sqlSalesperson,$db, $ErrMsg);
	$TransferRowSalesperson = DB_fetch_array($resultSalesperson);

	if (DB_num_rows($result)==0){
		include ('includes/header.inc');
		prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
		include ('includes/footer.inc');
		exit;
	}

	$TransferRow = DB_fetch_array($result);
 

	include ('includes/PDFQuotationPageHeader.inc');
	
	$orderNo = $_GET['QuotationNo'];



	if ($TransferRowS['printexchange']==0) {


    $SQL = "SELECT * FROM salesorderlines 
			WHERE orderno='$orderNo'";
    $lines = mysqli_query($db, $SQL);
    $totalLines = mysqli_num_rows($lines);

    $lineCount = 1;
    while ($line = mysqli_fetch_assoc($lines)) {

        $lineNo = $line['lineno'];
        $clientRequirements = $line['clientrequirements'];

        $clientRequirements = str_replace("</p>", "", $clientRequirements);
        $clientRequirements = str_replace("<p>", "", $clientRequirements);
        $clientRequirements = str_replace("</div>", "", $clientRequirements);
        $clientRequirements = str_replace("<div>", "", $clientRequirements);

        $SQL = "SELECT * FROM salesorderoptions 
				WHERE orderno='$orderNo'
				AND lineno='$lineNo'";
        $options = mysqli_query($db, $SQL);
        $lineOptions = mysqli_num_rows($options);

        $optionCount = 1;
        while ($option = mysqli_fetch_assoc($options)) {

            $weOffer = $option['optiontext'];

            $weOffer = str_replace("</p>", "", $weOffer);
            $weOffer = str_replace("<p>", "", $weOffer);
            $weOffer = str_replace("</div>", "", $weOffer);
            $weOffer = str_replace("<div>", "", $weOffer);

            $html2 .= '<br><br><br>';
            $html2 .= '<div style="padding:10px;">
						<table nobr="true" border = "1" width = "100%">
							<tr>
								<th width = "10%" align="center">' . '<b>Serial No. </b>.</th>
								<th width = "50%" align="center"><b> Description </b></th>
								<th width = "10%"align="center"> <b>Quantity</b></th>
								<th width = "15%" align="center"> <b>Rate</b></th>
								<th width = "15%" align="center"><b>Total</b></th>
							</tr>
							<tr>';

            if ($lineOptions == 1)
                $html2 .= "<td width='10%' align='center'><h3>$lineCount</h3></td>";
            else
                $html2 .= "<td width='10%' align='center'><h3>$lineCount Option No. $optionCount</h3></td>";

            $html2 .= "<td width = '50%'>
						<table>
							<tr>
								<td><b><u>Client Required</u></b>.<br />" . utf8_decode($clientRequirements) . '</td>
							</tr>
							<tr>
								<td><b><u>We Offer </b></u><br /> ' . utf8_decode($weOffer) . '</td>
							</tr>
						</table>
						</td>
						<td width = "10%" align="center">' . $option['quantity'] . '<br>' . $option['uom'] . '</td>
						<td width = "15%" align="center">PKR' . locale_number_format($option['price'], 2) . '</td>
						<td width = "15%" align="center">PKR' . locale_number_format(round($option['quantity'] * $option['price']), 2) . '</td>
					</tr>
					<tr>
						<td colspan="7">Stock Status: ' . $option['stockstatus'] . '</td>
					</tr>
					</table>
					</div>';

            $optionCount++;

        }

        $lineCount++;

    }

    $SQL = "SELECT SUM(price*quantity) as value				
			FROM salesorderoptions
			WHERE salesorderoptions.optionno=0
			AND salesorderoptions.orderno=$orderNo";
    $res = mysqli_query($db, $SQL);
    $quoteValue = mysqli_fetch_assoc($res)['value'];

    $html2 .= '<div align="right"><table border="1">';

    if ($TransferRow2['GSTadd'] == 'update') {

        $html2 .= '<tr>
					<td><b>SUBTOTAL</b></td>
					<td>PKR' . locale_number_format(round($quoteValue), 2) . '</td>
				</tr>';

        if ($TransferRow2['services'] == 1) {

            $html2 .= '<tr>
						<td><b>16% GST</b></td>
						<td>PKR' . locale_number_format(round($quoteValue * 0.16), 2) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . (locale_number_format(round($quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100), 2)) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.16), 2) . '</td>
						</tr>';

            }

        } else {

            $html2 .= '<tr>
						<td><b>18% GST</b></td>
						<td>PKR' . locale_number_format(round($quoteValue * 0.18), 2) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.18 + $quoteValue * $TransferRow2['WHT'] / 100), 2) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.18), 2) . '</td>
						</tr>';

            }

        }

    }


    if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {

        $html2 .= '<tr>
					<td><b>SUBTOTAL</b></td>
					<td>PKR' . locale_number_format(round($quoteValue), 2) . '</td>
				</tr>';

        if ($TransferRow2['services'] == 1) {

            $html2 .= '<tr>
						<td><b>16% GST</b></td>
						<td>PKR' . locale_number_format(round($quoteValue * 0.16), 2) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . (locale_number_format(round($quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100), 2)) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.16), 2) . '</td>
						</tr>';

            }

        } else {

            $html2 .= '<tr>
						<td><b>17% GST</b></td>
						<td>PKR' . locale_number_format(round($quoteValue * 0.17), 2) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.17 + $quoteValue * $TransferRow2['WHT'] / 100), 2) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.17), 2) . '</td>
						</tr>';

            }

        }

    }

    if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {

        $html2 .= '<tr>
					<td><b>SUBTOTAL</b></td>
					<td>PKR' . locale_number_format(round($quoteValue), 2) . '</td>
				</tr>';

        if ($TransferRow2['services'] == 1) {

            $html2 .= '<tr>
						<td><b>16% GST</b></td>
						<td>PKR' . locale_number_format(round($quoteValue * 0.16), 2) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . (locale_number_format(round($quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100), 2)) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.16), 2) . '</td>
						</tr>';

            }

        } else {

            $html2 .= '<tr>
						<td><b>18% GST</b></td>
						<td>PKR' . locale_number_format(round($quoteValue * 0.18), 2) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.18 + $quoteValue * $TransferRow2['WHT'] / 100), 2) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue * 1.18), 2) . '</td>
						</tr>';

            }

        }

    }

    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {

        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>SUBTOTAL inclusive of 16% GST</b></td>
							<td>PKR' . locale_number_format($quoteValue, 2) . ' </td>
						</tr>';

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total inclusive of 16% GST</b></td>
							<td>PKR' . locale_number_format(round($quoteValue), 2) . ' </td>
						</tr>';

            }

        } else {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>SUBTOTAL inclusive of 16% GST</b></td>
							<td>PKR' . locale_number_format($quoteValue, 2) . ' </td>
						</tr>';

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total inclusive of 17% GST</b></td>
							<td>PKR' . locale_number_format(round($quoteValue), 2) . ' </td>
						</tr>';

            }

        }

    }

    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {

        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>SUBTOTAL inclusive of 16% GST</b></td>
							<td>PKR' . locale_number_format($quoteValue, 2) . ' </td>
						</tr>';

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total inclusive of 16% GST</b></td>
							<td>PKR' . locale_number_format(round($quoteValue), 2) . ' </td>
						</tr>';

            }

        } else {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>SUBTOTAL inclusive of 16% GST</b></td>
							<td>PKR' . locale_number_format($quoteValue, 2) . ' </td>
						</tr>';

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total inclusive of 18% GST</b></td>
							<td>PKR' . locale_number_format(round($quoteValue), 2) . ' </td>
						</tr>';

            }

        }

    }


    if ($TransferRow2['GSTadd'] == '') {

        if ($TransferRow2['WHT'] != 0) {

            $html2 .= '<tr>
						<td><b>SUBTOTAL</b></td>
						<td>PKR' . locale_number_format(round($quoteValue), 2) . '</td>
					</tr>';

            $html2 .= '<tr>
						<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
						<td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . '</td>
					</tr>';

            $html2 .= '<tr>
						<td><b>Grand Total</b></td>
						<td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) . '</td>
					</tr>';

        } else {

            $html2 .= '<tr>
						<td><b>Grand Total</b></td>
						<td>PKR' . locale_number_format(round($quoteValue), 2) . '</td>
					</tr>';

        }

    }

    $html2 .= '</table></div>';
    $html2 .= 'In case of multiple options amount of first option is considered<br/>';

    $pdf->writeHTML(utf8_decode($html2), true, false, true, false, '');

    $htmlpayment .= '<b>Terms and Conditions: </b><br>Stock availability is subject to prior sales.<br>Company reserves the right to apply force majeur clause without any repercussion to the relationship between the customer and itself.<br>';
        $htmlpayment .= getPriceChangeStatement($TransferRow2['rate_clause'], $TransferRow2['clause_rates'], $TransferRow2['rate_validity'], $TransferRow2['GSTadd'],$TransferRow2['freightclause'],$TransferRow2['validity']);
        if ($TransferRow2['validity'] != 0) {

        $htmlpayment .= '<br><b>Quoted Price Valid For ' . $TransferRow2['validity'] . " Days.</b><br><br>";

    }

        $htmlpaymentterms = '<br/><b>Payment Terms:</b><br>';
        if ($TransferRow2['advance'] > 0)
            $htmlpaymentterms .= 'Advance ' . $TransferRow2['advance'] . '%<br>';
        if ($TransferRow2['delivery'] > 0)
            $htmlpaymentterms .= 'On Delivery ' . $TransferRow2['delivery'] . '%<br>';
        if ($TransferRow2['commisioning'] > 0)
            $htmlpaymentterms .= 'On Commisioning ' . $TransferRow2['commisioning'] . '%<br>';
        if ($TransferRow2['after'] > 0)
            $htmlpaymentterms .= $TransferRow2['after'] . '% After ' . $TransferRow2['afterdays'] . 'days';
        $htmlpaymentterms .= '<br>' . $TransferRow2['gst'] . '<br>';


        $html4 .= '</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
        $pdf->SetFontSize(10);
        $pdf->writeHTML(utf8_decode($htmlpaymentterms), true, false, true, false, '');
        $pdf->SetFontSize(6);
        $pdf->writeHTML(utf8_decode($htmlpayment), true, false, true, false, '');
        $pdf->Ln();
        $pdf->writeHTML(utf8_decode($TransferRow2['comments']), true, false, true, false, '');
        $pdf->Ln();
        $pdf->SetFontSize(10);
    ob_end_clean();
    $pdf->OutputD($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf');
    $pdf->__destruct();

    function utf8_encode_deep(&$input)
    {
        if (is_string($input)) {
            $input = utf8_encode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                utf8_encode_deep($value);
            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                utf8_encode_deep($input->$var);
            }
        }
    }
}





    if ($TransferRowS['printexchange']==1) {


        $SQL = "SELECT * FROM salesorderlines 
                WHERE orderno='$orderNo'";
        $lines = mysqli_query($db, $SQL);
        $totalLines = mysqli_num_rows($lines);

        $lineCount = 1;
        while ($line = mysqli_fetch_assoc($lines)) {

            $lineNo = $line['lineno'];
            $clientRequirements = $line['clientrequirements'];

            $clientRequirements = str_replace("</p>", "", $clientRequirements);
            $clientRequirements = str_replace("<p>", "", $clientRequirements);
            $clientRequirements = str_replace("</div>", "", $clientRequirements);
            $clientRequirements = str_replace("<div>", "", $clientRequirements);

            $SQL = "SELECT * FROM salesorderoptions 
                    WHERE orderno='$orderNo'
                    AND lineno='$lineNo'";
            $options = mysqli_query($db, $SQL);
            $lineOptions = mysqli_num_rows($options);

            $optionCount = 1;
            while ($option = mysqli_fetch_assoc($options)) {

                $weOffer = $option['optiontext'];

                $weOffer = str_replace("</p>", "", $weOffer);
                $weOffer = str_replace("<p>", "", $weOffer);
                $weOffer = str_replace("</div>", "", $weOffer);
                $weOffer = str_replace("<div>", "", $weOffer);

                $html2 .= '<br><br><br>';
                $html2 .= '<div style="padding:10px;">
                            <table nobr="true" border = "1" width = "100%">
                                <tr>
                                    <th width = "10%" align="center">' . '<b>Serial No. </b>.</th>
                                    <th width = "50%" align="center"><b> Description </b></th>
                                    <th width = "10%"align="center"> <b>Quantity</b></th>
                                    <th width = "15%" align="center"> <b>Rate</b></th>
                                    <th width = "15%" align="center"><b>Total</b></th>
                                </tr>
                                <tr>';

                if ($lineOptions == 1)
                    $html2 .= "<td width='10%' align='center'><h3>$lineCount</h3></td>";
                else
                    $html2 .= "<td width='10%' align='center'><h3>$lineCount Option No. $optionCount</h3></td>";

                $html2 .= "<td width = '50%'>
                            <table>
                                <tr>
                                    <td><b><u>Client Required</u></b>.<br />" . utf8_decode($clientRequirements) . '</td>
                                </tr>
                                <tr>
                                    <td><b><u>We Offer </b></u><br /> ' . utf8_decode($weOffer) . '</td>
                                </tr>
                            </table>
                            </td>
                            <td width = "10%" align="center">' . $option['quantity'] . '<br>' . $option['uom'] . '</td>
                            <td width = "15%" align="center">PKR' . locale_number_format($option['price'], 2) .getparityrate($currency, $values, $option['price']) . '</td>
                            <td width = "15%" align="center">PKR' . locale_number_format(round($option['quantity'] * $option['price']), 2) .getparityrate($currency, $values,$option['quantity'] * $option['price']) .  '</td>
                        </tr>
                        <tr>
                            <td colspan="7">Stock Status: ' . $option['stockstatus'] . '</td>
                        </tr>
                        </table>
                        </div>';

                $optionCount++;

            }

            $lineCount++;

        }

        $SQL = "SELECT SUM(price*quantity) as value				
                FROM salesorderoptions
                WHERE salesorderoptions.optionno=0
                AND salesorderoptions.orderno=$orderNo";
        $res = mysqli_query($db, $SQL);
        $quoteValue = mysqli_fetch_assoc($res)['value'];

        $html2 .= '<div align="right"><table border="1">';

        if ($TransferRow2['GSTadd'] == 'update') {

            $html2 .= '<tr>
                        <td><b>SUBTOTAL</b></td>
                        <td>PKR' . locale_number_format(round($quoteValue), 2) .getparityrate($currency, $values, $quoteValue) .  '</td>
                    </tr>';

            if ($TransferRow2['services'] == 1) {

                $html2 .= '<tr>
                            <td><b>16% GST</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue * 0.16), 2) . getparityrate($currency, $values,$quoteValue * 0.16) . '</td>
                        </tr>';

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . (locale_number_format(round($quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100), 2)) .getparityrate($currency, $values, $quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.16), 2) . getparityrate($currency, $values, $quoteValue * 1.16) . '</td>
                            </tr>';

                }

            } else {

                $html2 .= '<tr>
                            <td><b>18% GST</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue * 0.18), 2) .getparityrate($currency, $values, $quoteValue * 0.18) .  '</td>
                        </tr>';

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.18 + $quoteValue * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $quoteValue * 1.18 + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.18), 2) .getparityrate($currency, $values,$quoteValue * 1.18) .  '</td>
                            </tr>';

                }

            }

        }


        if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {

            $html2 .= '<tr>
                        <td><b>SUBTOTAL</b></td>
                        <td>PKR' . locale_number_format(round($quoteValue), 2) .getparityrate($currency, $values, $quoteValue) .  '</td>
                    </tr>';

            if ($TransferRow2['services'] == 1) {

                $html2 .= '<tr>
                            <td><b>16% GST</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue * 0.16), 2) . getparityrate($currency, $values,$quoteValue * 0.16) . '</td>
                        </tr>';

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . (locale_number_format(round($quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100), 2)) .getparityrate($currency, $values, $quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.16), 2) . getparityrate($currency, $values, $quoteValue * 1.16) . '</td>
                            </tr>';

                }

            } else {

                $html2 .= '<tr>
                            <td><b>17% GST</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue * 0.17), 2) .getparityrate($currency, $values, $quoteValue * 0.17) .  '</td>
                        </tr>';

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.17 + $quoteValue * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $quoteValue * 1.17 + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.17), 2) .getparityrate($currency, $values,$quoteValue * 1.17) .  '</td>
                            </tr>';

                }

            }

        }

        if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {

            $html2 .= '<tr>
                        <td><b>SUBTOTAL</b></td>
                        <td>PKR' . locale_number_format(round($quoteValue), 2) .getparityrate($currency, $values, $quoteValue) .  '</td>
                    </tr>';

            if ($TransferRow2['services'] == 1) {

                $html2 .= '<tr>
                            <td><b>16% GST</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue * 0.16), 2) . getparityrate($currency, $values,$quoteValue * 0.16) . '</td>
                        </tr>';

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . (locale_number_format(round($quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100), 2)) .getparityrate($currency, $values, $quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.16), 2) . getparityrate($currency, $values, $quoteValue * 1.16) . '</td>
                            </tr>';

                }

            } else {

                $html2 .= '<tr>
                            <td><b>18% GST</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue * 0.18), 2) .getparityrate($currency, $values, $quoteValue * 0.18) .  '</td>
                        </tr>';

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.18 + $quoteValue * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $quoteValue * 1.18 + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue * 1.18), 2) .getparityrate($currency, $values,$quoteValue * 1.17) .  '</td>
                            </tr>';

                }

            }

        }

        if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14') ) {

            if ($TransferRow2['services'] == 1) {

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>SUBTOTAL inclusive of 16% GST</b></td>
                                <td>PKR' . locale_number_format($quoteValue, 2) .getparityrate($currency, $values, $quoteValue) .  ' </td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) .getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total inclusive of 16% GST</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue), 2) . getparityrate($currency, $values, $quoteValue) . ' </td>
                            </tr>';

                }

            } else {

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>SUBTOTAL inclusive of 16% GST</b></td>
                                <td>PKR' . locale_number_format($quoteValue, 2) . getparityrate($currency, $values, $quoteValue) . ' </td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total inclusive of 17% GST</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue), 2) .getparityrate($currency, $values, $quoteValue) .  ' </td>
                            </tr>';

                }

            }

        }

        if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14') ) {

            if ($TransferRow2['services'] == 1) {

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>SUBTOTAL inclusive of 16% GST</b></td>
                                <td>PKR' . locale_number_format($quoteValue, 2) .getparityrate($currency, $values, $quoteValue) .  ' </td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) .getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total inclusive of 16% GST</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue), 2) . getparityrate($currency, $values, $quoteValue) . ' </td>
                            </tr>';

                }

            } else {

                if ($TransferRow2['WHT'] != 0) {

                    $html2 .= '<tr>
                                <td><b>SUBTOTAL inclusive of 16% GST</b></td>
                                <td>PKR' . locale_number_format($quoteValue, 2) . getparityrate($currency, $values, $quoteValue) . ' </td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                                <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                            </tr>';

                    $html2 .= '<tr>
                                <td><b>Grand Total</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
                            </tr>';

                } else {

                    $html2 .= '<tr>
                                <td><b>Grand Total inclusive of 18% GST</b></td>
                                <td>PKR' . locale_number_format(round($quoteValue), 2) .getparityrate($currency, $values, $quoteValue) .  ' </td>
                            </tr>';

                }

            }

        }

        if ($TransferRow2['GSTadd'] == '') {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
                            <td><b>SUBTOTAL</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue), 2) .getparityrate($currency, $values, $quoteValue) .  '</td>
                        </tr>';

                $html2 .= '<tr>
                            <td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
                            <td>PKR' . locale_number_format($quoteValue * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                        </tr>';

                $html2 .= '<tr>
                            <td><b>Grand Total</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue + $quoteValue * $TransferRow2['WHT'] / 100), 2) .getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
                        </tr>';

            } else {

                $html2 .= '<tr>
                            <td><b>Grand Total</b></td>
                            <td>PKR' . locale_number_format(round($quoteValue), 2) .getparityrate($currency, $values, $quoteValue) .  '</td>
                        </tr>';

            }

        }

        $html2 .= '</table></div>';
        $html2 .= 'In case of multiple options amount of first option is considered<br/>';

        $pdf->writeHTML(utf8_decode($html2), true, false, true, false, '');

        $htmlpayment .= '<b>Terms and Conditions: </b><br>Stock availability is subject to prior sales.<br>Company reserves the right to apply force majeur clause without any repercussion to the relationship between the customer and itself.<br>';
        $htmlpayment .= getPriceChangeStatement($TransferRow2['rate_clause'], $TransferRow2['clause_rates'], $TransferRow2['rate_validity'], $TransferRow2['GSTadd'],$TransferRow2['freightclause'],$TransferRow2['validity']);

        if ($TransferRow2['validity'] != 0) {

            $htmlpayment .= '<br><b>Quoted Price Valid For ' . $TransferRow2['validity'] . " Days.</b><br><br>";

        }

        $htmlpaymentterms = '<br/><b>Payment Terms:</b><br>';
        if ($TransferRow2['advance'] > 0)
            $htmlpaymentterms .= 'Advance ' . $TransferRow2['advance'] . '%<br>';
        if ($TransferRow2['delivery'] > 0)
            $htmlpaymentterms .= 'On Delivery ' . $TransferRow2['delivery'] . '%<br>';
        if ($TransferRow2['commisioning'] > 0)
            $htmlpaymentterms .= 'On Commisioning ' . $TransferRow2['commisioning'] . '%<br>';
        if ($TransferRow2['after'] > 0)
            $htmlpaymentterms .= $TransferRow2['after'] . '% After ' . $TransferRow2['afterdays'] . 'days';
        $htmlpaymentterms .= '<br>' . $TransferRow2['gst'] . '<br>';


        $html4 .= '</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
        $pdf->SetFontSize(10);
        $pdf->writeHTML(utf8_decode($htmlpaymentterms), true, false, true, false, '');
        $pdf->SetFontSize(6);
        $pdf->writeHTML(utf8_decode($htmlpayment), true, false, true, false, '');
        $pdf->Ln();
        $pdf->writeHTML(utf8_decode($TransferRow2['comments']), true, false, true, false, '');
        $pdf->Ln();
        $pdf->SetFontSize(10);

        ob_end_clean();
        $pdf->OutputD($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf');
        $pdf->__destruct();

        function utf8_encode_deep(&$input)
        {
            if (is_string($input)) {
                $input = utf8_encode($input);
            } else if (is_array($input)) {
                foreach ($input as &$value) {
                    utf8_encode_deep($value);
                }

                unset($value);
            } else if (is_object($input)) {
                $vars = array_keys(get_object_vars($input));

                foreach ($vars as $var) {
                    utf8_encode_deep($input->$var);
                }
            }
        }
    }






    if ($TransferRowS['printexchange']==2) {


    $SQL = "SELECT * FROM salesorderlines 
			WHERE orderno='$orderNo'";
    $lines = mysqli_query($db, $SQL);
    $totalLines = mysqli_num_rows($lines);

    $lineCount = 1;
    while ($line = mysqli_fetch_assoc($lines)) {

        $lineNo = $line['lineno'];
        $clientRequirements = $line['clientrequirements'];

        $clientRequirements = str_replace("</p>", "", $clientRequirements);
        $clientRequirements = str_replace("<p>", "", $clientRequirements);
        $clientRequirements = str_replace("</div>", "", $clientRequirements);
        $clientRequirements = str_replace("<div>", "", $clientRequirements);

        $SQL = "SELECT * FROM salesorderoptions 
				WHERE orderno='$orderNo'
				AND lineno='$lineNo'";
        $options = mysqli_query($db, $SQL);
        $lineOptions = mysqli_num_rows($options);

        $optionCount = 1;
        while ($option = mysqli_fetch_assoc($options)) {

            $weOffer = $option['optiontext'];

            $weOffer = str_replace("</p>", "", $weOffer);
            $weOffer = str_replace("<p>", "", $weOffer);
            $weOffer = str_replace("</div>", "", $weOffer);
            $weOffer = str_replace("<div>", "", $weOffer);

            $html2 .= '<br><br><br>';
            $html2 .= '<div style="padding:10px;">
						<table nobr="true" border = "1" width = "100%">
							<tr>
								<th width = "10%" align="center">' . '<b>Serial No. </b>.</th>
								<th width = "50%" align="center"><b> Description </b></th>
								<th width = "10%"align="center"> <b>Quantity</b></th>
								<th width = "15%" align="center"> <b>Rate</b></th>
								<th width = "15%" align="center"><b>Total</b></th>
							</tr>
							<tr>';

            if ($lineOptions == 1)
                $html2 .= "<td width='10%' align='center'><h3>$lineCount</h3></td>";
            else
                $html2 .= "<td width='10%' align='center'><h3>$lineCount Option No. $optionCount</h3></td>";

            $html2 .= "<td width = '50%'>
						<table>
							<tr>
								<td><b><u>Client Required</u></b>.<br />" . utf8_decode($clientRequirements) . '</td>
							</tr>
							<tr>
								<td><b><u>We Offer </b></u><br /> ' . utf8_decode($weOffer) . '</td>
							</tr>
						</table>
						</td>
						<td width = "10%" align="center">' . $option['quantity'] . '<br>' . $option['uom'] . '</td>
						<td width = "15%" align="center">' . getparityrate($currency, $values, $option['price']) . '</td>
						<td width = "15%" align="center">' .getparityrate($currency, $values,$option['quantity'] * $option['price']) .  '</td>
					</tr>
					<tr>
						<td colspan="7">Stock Status: ' . $option['stockstatus'] . '</td>
					</tr>
					</table>
					</div>';

            $optionCount++;

        }

        $lineCount++;

    }

    $SQL = "SELECT SUM(price*quantity) as value				
			FROM salesorderoptions
			WHERE salesorderoptions.optionno=0
			AND salesorderoptions.orderno=$orderNo";
    $res = mysqli_query($db, $SQL);
    $quoteValue = mysqli_fetch_assoc($res)['value'];

    $html2 .= '<div align="right"><table border="1">';

    if ($TransferRow2['GSTadd'] == 'update' ) {

        $html2 .= '<tr>
					<td><b>SUBTOTAL</b></td>
					<td>' .getparityrate($currency, $values, $quoteValue) .  '</td>
				</tr>';

        if ($TransferRow2['services'] == 1) {

            $html2 .= '<tr>
						<td><b>16% GST</b></td>
						<td>' .getparityrate($currency, $values,$quoteValue * 0.16) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.16) . '</td>
						</tr>';

            }

        } else {

            $html2 .= '<tr>
						<td><b>18% GST</b></td>
						<td>' .getparityrate($currency, $values, $quoteValue * 0.18) .  '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.18 + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values,$quoteValue * 1.18) .  '</td>
						</tr>';

            }

        }

    }
    if ($TransferRow2['GSTadd'] == 'exclusive'  && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {

        $html2 .= '<tr>
					<td><b>SUBTOTAL</b></td>
					<td>' .getparityrate($currency, $values, $quoteValue) .  '</td>
				</tr>';

        if ($TransferRow2['services'] == 1) {

            $html2 .= '<tr>
						<td><b>16% GST</b></td>
						<td>' .getparityrate($currency, $values,$quoteValue * 0.16) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.16) . '</td>
						</tr>';

            }

        } else {

            $html2 .= '<tr>
						<td><b>17% GST</b></td>
						<td>' .getparityrate($currency, $values, $quoteValue * 0.17) .  '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.17 + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values,$quoteValue * 1.17) .  '</td>
						</tr>';

            }

        }

    }

    if ($TransferRow2['GSTadd'] == 'exclusive'  && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {

        $html2 .= '<tr>
					<td><b>SUBTOTAL</b></td>
					<td>' .getparityrate($currency, $values, $quoteValue) .  '</td>
				</tr>';

        if ($TransferRow2['services'] == 1) {

            $html2 .= '<tr>
						<td><b>16% GST</b></td>
						<td>' .getparityrate($currency, $values,$quoteValue * 0.16) . '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.16 + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.16) . '</td>
						</tr>';

            }

        } else {

            $html2 .= '<tr>
						<td><b>18% GST</b></td>
						<td>' .getparityrate($currency, $values, $quoteValue * 0.18) .  '</td>
					</tr>';

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * 1.18 + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values,$quoteValue * 1.18) .  '</td>
						</tr>';

            }

        }

    }

    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {

        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>SUBTOTAL inclusive of 16% GST</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue) .  ' </td>
						</tr>';

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total inclusive of 16% GST</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue) . ' </td>
						</tr>';

            }

        } else {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>SUBTOTAL inclusive of 16% GST</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue) . ' </td>
						</tr>';

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' . getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total inclusive of 17% GST</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue) .  ' </td>
						</tr>';

            }

        }

    }

    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {

        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>SUBTOTAL inclusive of 16% GST</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue) .  ' </td>
						</tr>';

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total inclusive of 16% GST</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue) . ' </td>
						</tr>';

            }

        } else {

            if ($TransferRow2['WHT'] != 0) {

                $html2 .= '<tr>
							<td><b>SUBTOTAL inclusive of 16% GST</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue) . ' </td>
						</tr>';

                $html2 .= '<tr>
							<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
						</tr>';

                $html2 .= '<tr>
							<td><b>Grand Total</b></td>
							<td>' . getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) . '</td>
						</tr>';

            } else {

                $html2 .= '<tr>
							<td><b>Grand Total inclusive of 18% GST</b></td>
							<td>' .getparityrate($currency, $values, $quoteValue) .  ' </td>
						</tr>';

            }

        }

    }

    if ($TransferRow2['GSTadd'] == '') {

        if ($TransferRow2['WHT'] != 0) {

            $html2 .= '<tr>
						<td><b>SUBTOTAL</b></td>
						<td>' . getparityrate($currency, $values, $quoteValue) .  '</td>
					</tr>';

            $html2 .= '<tr>
						<td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td>
						<td>' .getparityrate($currency, $values, $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
					</tr>';

            $html2 .= '<tr>
						<td><b>Grand Total</b></td>
						<td>' .getparityrate($currency, $values, $quoteValue + $quoteValue * $TransferRow2['WHT'] / 100) .  '</td>
					</tr>';

        } else {

            $html2 .= '<tr>
						<td><b>Grand Total</b></td>
						<td>' .getparityrate($currency, $values, $quoteValue) .  '</td>
					</tr>';

        }

    }

    $html2 .= '</table></div>';
    $html2 .= 'In case of multiple options amount of first option is considered<br/>';

    $pdf->writeHTML(utf8_decode($html2), true, false, true, false, '');

    $htmlpayment .= '<b>Terms and Conditions: </b><br>
					Stock availability is subject to prior sales.<br>Company reserves the right to apply force majeur clause without any repercussion to the relationship between the customer and itself.<br>';
        $htmlpayment .= getPriceChangeStatement($TransferRow2['rate_clause'], $TransferRow2['clause_rates'], $TransferRow2['rate_validity'], $TransferRow2['GSTadd'],$TransferRow2['freightclause'],$TransferRow2['validity']);

        if ($TransferRow2['validity'] != 0) {

        $htmlpayment .= '<br><b>Quoted Price Valid For ' . $TransferRow2['validity'] . " Days.</b><br><br>";

    }

        $htmlpaymentterms = '<br/><b>Payment Terms:</b><br>';
        if ($TransferRow2['advance'] > 0)
            $htmlpaymentterms .= 'Advance ' . $TransferRow2['advance'] . '%<br>';
        if ($TransferRow2['delivery'] > 0)
            $htmlpaymentterms .= 'On Delivery ' . $TransferRow2['delivery'] . '%<br>';
        if ($TransferRow2['commisioning'] > 0)
            $htmlpaymentterms .= 'On Commisioning ' . $TransferRow2['commisioning'] . '%<br>';
        if ($TransferRow2['after'] > 0)
            $htmlpaymentterms .= $TransferRow2['after'] . '% After ' . $TransferRow2['afterdays'] . 'days';
        $htmlpaymentterms .= '<br>' . $TransferRow2['gst'] . '<br>';


        $html4 .= '</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
        $pdf->SetFontSize(10);
        $pdf->writeHTML(utf8_decode($htmlpaymentterms), true, false, true, false, '');
        $pdf->SetFontSize(6);
        $pdf->writeHTML(utf8_decode($htmlpayment), true, false, true, false, '');
        $pdf->Ln();
        $pdf->writeHTML(utf8_decode($TransferRow2['comments']), true, false, true, false, '');
        $pdf->Ln();
        $pdf->SetFontSize(10);

    ob_end_clean();
    $pdf->OutputD($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf');
    $pdf->__destruct();

    function utf8_encode_deep(&$input)
    {
        if (is_string($input)) {
            $input = utf8_encode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                utf8_encode_deep($value);
            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                utf8_encode_deep($input->$var);
            }
        }
    }
}





