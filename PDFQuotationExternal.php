<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');
$document= 'QuotationNo: '.$_GET['QuotationNo'];
include('includes/PDFStarter2.php');

$pdf->setFootTitle("Quotation # ".$_GET['QuotationNo']." -- ");


//	$html2.='<span align="right">Page '.$pdf->getAliasNumPage() . " of " . $pdf->getAliasNbPages().'</span>';

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
				salesorders.rate_clause,
				(CASE salesorders.rate_validity
				WHEN '0000-00-00'
				THEN DATE_ADD( salesorders.orddate, INTERVAL 15 DAY)
				ELSE salesorders.rate_validity
				END) as rate_validity,
				
				salesorders.clause_rates,
				salesorders.afterdays,
				salesorders.GSTadd,
				salesorders.services,
				salesorders.printexchange,
				salesorders.WHT,
				salesorders.umqd,
				salesorders.validity,
				salesorders.comments,
				salesorders.revision,
				salesorders.freightclause,
				salesorders.revision_for,
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
//utf8_decode($TransferRow2);
$sqlS = "SELECT * from salesorders INNER JOIN
		debtorsmaster 
		ON salesorders.debtorno = debtorsmaster.debtorno
		INNER JOIN custbranch 
		ON debtorsmaster.debtorno = custbranch.debtorno
		INNER JOIN dba ON dba.companyname = debtorsmaster.dba
		INNER JOIN salesman
		ON custbranch.salesman = salesman.salesmancode
		WHERE orderno = 


'" . $_GET['QuotationNo'] ."'";

$resultS=DB_query($sqlS,$db, $ErrMsg);
$TransferRowS = DB_fetch_array($resultS);

$currency=$TransferRow2['rate_clause'];
$values=$TransferRow2['clause_rates'];

//Without Exchange Prices



if ($TransferRowS['printexchange']==0) {
    $sqlSalesperson = "SELECT * from salesman inner join 
www_users on salesman.salesmanname = www_users.realname
WHERE salesman.salesmanname = 
'" . $TransferRowS['salesmanname'] . "'";

    $resultSalesperson = DB_query($sqlSalesperson, $db, $ErrMsg);
    $TransferRowSalesperson = DB_fetch_array($resultSalesperson);

    If (DB_num_rows($result) == 0) {

        include('includes/header.inc');
        prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'), 'error');
        include('includes/footer.inc');
        exit;
    }

    $TransferRow = DB_fetch_array($result);


    include('includes/PDFQuotationPageHeader.inc');
//$html2.='<span align="right">Page '.$pdf->getAliasNumPage() . " of " . $pdf->getAliasNbPages().'</span>';

    $sqlL = "select MAX(orderlineno) as maxlineno from salesorderdetails where orderno = '" . $_GET['QuotationNo'] . "'";
    $result = DB_query($sqlL, $db);
    $resultrow = DB_fetch_array($result);

    for ($i = 0; $i <= $resultrow['maxlineno']; $i++) {

        $sqlO = "select MAX(lineoptionno) as maxlineoptionno from salesorderdetails where orderno = '" . $_GET['QuotationNo'] . "'
AND orderlineno = " . $i . "";
        $resultO = DB_query($sqlO, $db);
        $resultrowO = DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";

        for ($j = 0; $j <= $resultrowO['maxlineoptionno']; $j++) {
            $sqlA = "SELECT *
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE salesorderdetails.orderno='" . $_GET['QuotationNo'] . "'";
            $resultA = DB_query($sqlA, $db);
            $TransferRow = DB_fetch_array($resultA);

            $line = 0;
            $sqlB = "SELECT *
	FROM salesorderlines 
	WHERE salesorderlines.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderlines.lineno='" . $i . "'
	";
            $resultB = DB_query($sqlB, $db);
            $TransferRowB = DB_fetch_array($resultB);
            //$clientrequirements = $TransferRowB['clientrequirements'];
            //	$TransferRowB['clientrequirements'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowB['clientrequirements']);
//$TransferRowB['clientrequirements'] = stripslashes($TransferRowB['clientrequirements']);

            //$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</p>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<p>", "", $TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</div>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<div>", "", $TransferRowB['clientrequirements']);

            $html2 .= '<div style="padding:5px;"><table nobr="true" border = "1" width = "100%">

<tr><th width = "10%" align="center">' . '<b>Serial No. </b>.</th>
<th width = "50%" align="center"><b> Description </b></th>
<th width = "10%"align="center"> <b>Quantity</b></th>
<th width = "15%" align="center"> <b>Rate</b></th>

<th width = "15%" align="center"><b>Total</b></th>
</tr>

<tr>';
            if ($resultrowO['maxlineoptionno'] == 0)
                $html2 .= '<td width = "10%" align="center">' . '<h3>' . ($i + 1) . '</h3></td>';

            else
                $html2 .= '<td width = "10%" align="center">' . '<h3>' . ($i + 1) . ' Option No. ' . ($j + 1) . '</h3>' . '</td>';

            $html2 .= '<td width = "50%" ><table><tr><td>' . '<b><u>Client Required</u></b>.<br /> ' . utf8_decode($TransferRowB['clientrequirements']) . '</td></tr>';

//<tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
//$html2.= '<td width = "10%" align = "center">'.'Model No'.'</td>';
//$html2.= '<td width = "15%" align = "center">'.'Part No'.'</td>';
//$html2.= '<td width = "20%" align = "center">'.'Brand'.'</td>';
//$html2.= '<td width = "30%">'.'Description'.'</td>';
//$html2.= '<td width = "10%" align = "center">'.'quantity'.'</td>

//<td width = "10%" align = "center">'.'Rate'.'</td>
//</tr>';
            $total = 0;

            $optionUOM = "";
            do {
                if (($i == $TransferRow['orderlineno']) AND ($j == $TransferRow['lineoptionno'])) {
                    $sqlC = "SELECT *
	FROM salesorderoptions 
	WHERE salesorderoptions.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderoptions.lineno='" . $i . "'
	AND salesorderoptions.optionno='" . $j . "'
	
	";
                    $resultC = DB_query($sqlC, $db);
                    $TransferRowC = DB_fetch_array($resultC);
                    $line++;

                    $optionUOM = $TransferRowC['uom'];

//$html2.= '<tr><td width = "5%" align = "center">'.$line.'</td>';
//$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
//$html2.= '<td width = "15%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
//$html2.= '<td width = "20%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
//$html2.= '<td width = "30%">'.$TransferRow['description'].'</td>';
//$html2.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td>
//';

//$html2.= '<td width = "10%">'.'Rs.'.locale_number_format(($TransferRow['unitprice']*(1 - $TransferRow['discountpercent'])),0).'</td></tr>';

                    $total = $total + ($TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent']));

                }
            } while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</p>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<p>", "", $TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</div>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<div>", "", $TransferRowC['optiontext']);
            $html2 .= '<tr><td>' . '<b><u>We Offer </b></u><br /> ' . utf8_decode($TransferRowC['optiontext']) . '</td></tr></table></td>

<td width = "10%" align="center">' . '' . $TransferRowC['quantity'] . '<br>' . $optionUOM . '</td>
<td width = "15%" align="center">PKR' . '' . locale_number_format($total, 2) . '</td>

<td width = "15%" align="center"PKR>' . '' . locale_number_format(round($TransferRowC['quantity'] * $total), 2) . '</td>
</tr><tr><td colspan="7">' . 'Stock Status: ' . $TransferRowC['stockstatus'] . '</td>
</tr>
</table></div>';
        }
    }

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

    $sqlquotevalue = "SELECT salesorders.salescaseref, salesorderdetails.orderno,
 SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity )
 as value from salesorderdetails INNER JOIN salesorderoptions on
 (salesorderdetails.orderno = salesorderoptions.orderno AND 
 salesorderdetails.orderlineno = salesorderoptions.lineno) 
 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno 
 WHERE salesorderdetails.lineoptionno = 0 and salesorderoptions.optionno = 0 and salesorders.orderno='" . $_GET['QuotationNo'] . "'";

    $resultsqlquotevalue = DB_query($sqlquotevalue, $db);
    $rowquotevalue = DB_fetch_array($resultsqlquotevalue);
    $html2 .= '<div align="right">
 <table border="1">';

 if ($TransferRow2['GSTadd'] == 'update') {
    $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';
    if ($TransferRow2['services'] == 1) {
        $html2 .= '<tr><td><b>16% GST</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) . '</td></tr>';
        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) . '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . '</td></tr>';


        }

    } else {
        $html2 .= '<tr><td><b>18% GST</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 0.18), 2) . '</td></tr>';
        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 1.18), 2) . '</td></tr>';


        }


    }

}

    if ($TransferRow2['GSTadd'] == 'exclusive'  && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {
        $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';
        if ($TransferRow2['services'] == 1) {
            $html2 .= '<tr><td><b>16% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) . '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . '</td></tr>';


            }

        } else {
            $html2 .= '<tr><td><b>17% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 0.17), 2) . '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.17 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.17), 2) . '</td></tr>';


            }


        }

    }
    elseif ($TransferRow2['GSTadd'] == 'exclusive'  && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {
        $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';
        if ($TransferRow2['services'] == 1) {
            $html2 .= '<tr><td><b>16% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) . '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . '</td></tr>';


            }

        } else {
            $html2 .= '<tr><td><b>18% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 0.18), 2) . '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.18), 2) . '</td></tr>';


            }


        }

    }
    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14'))  {


        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'], 2) . ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . ' </td></tr>';


            }

        } else {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'], 2) . ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . ' </td></tr>';


            }


        }


    }
    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14'))  {


        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'], 2) . ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total inclusive of 1% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . ' </td></tr>';


            }

        } else {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'], 2) . ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total inclusive of 18% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . ' </td></tr>';


            }


        }


    }

    if ($TransferRow2['GSTadd'] == '') {


        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';


        }

    }

    $html2 .= '</table>
 </div>';
    $html2 .= 'In case of multiple options amount of first option is considered<br/>';


    $pdf->writeHTML(utf8_decode($html2), true, false, true, false, '');



    $htmlpayment .= '<b>Terms and Conditions: </b><br>Stock availability is subject to prior sales.<br>Company reserves the right to apply force majeur clause without any repercussion to the relationship between the customer and itself.<br>';
    if($TransferRow2['GSTadd'] == 'update'){
        $GSTadd = 'exclusive';
    }
    $htmlpayment .= getPriceChangeStatement($TransferRow2['rate_clause'], $TransferRow2['clause_rates'], $TransferRow2['rate_validity'], $GSTadd,$TransferRow2['freightclause'],$TransferRow2['validity']);



    $htmlpaymentterms= '<br/><b>Payment Terms:</b><br>';
    if ($TransferRow2['advance'] > 0)
        $htmlpaymentterms .= 'Advance ' . $TransferRow2['advance'] . '%<br>';
    if ($TransferRow2['delivery'] > 0)
        $htmlpaymentterms .= 'On Delivery ' . $TransferRow2['delivery'] . '%<br>';
    if ($TransferRow2['commisioning'] > 0)
        $htmlpaymentterms .= 'On Commisioning ' . $TransferRow2['commisioning'] . '%<br>';
    if ($TransferRow2['after'] > 0)
        $htmlpaymentterms .= $TransferRow2['after'] . '% After ' . $TransferRow2['afterdays'] . 'days';
    $htmlpaymentterms .= '<br>' . $TransferRow2['gst'] . '<br>';
    if($TransferRow2['validity'] != 0){
        $htmlpaymentterms.= '<br>Quoted Price Valid For '.$TransferRow2['validity']." Days.<br>";
    }


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
//$pdf->writeHTML($html3, true, false, true, false, '');
    ob_end_clean();
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf','I');
    $pdf->__destruct();

    function utf8_decode(&$input)
    {
        if (is_string($input)) {
            $input = utf8_decode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                utf8_decode($value);
            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                utf8_decode($input->$var);
            }
        }
    }
}



//With Exchange Prices



if ($TransferRowS['printexchange']==1) {
    $sqlSalesperson = "SELECT * from salesman inner join 
www_users on salesman.salesmanname = www_users.realname
WHERE salesman.salesmanname = 
'" . $TransferRowS['salesmanname'] . "'";

    $resultSalesperson = DB_query($sqlSalesperson, $db, $ErrMsg);
    $TransferRowSalesperson = DB_fetch_array($resultSalesperson);

    If (DB_num_rows($result) == 0) {

        include('includes/header.inc');
        prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'), 'error');
        include('includes/footer.inc');
        exit;
    }

    $TransferRow = DB_fetch_array($result);


    include('includes/PDFQuotationPageHeader.inc');
//$html2.='<span align="right">Page '.$pdf->getAliasNumPage() . " of " . $pdf->getAliasNbPages().'</span>';

    $sqlL = "select MAX(orderlineno) as maxlineno from salesorderdetails where orderno = '" . $_GET['QuotationNo'] . "'";
    $result = DB_query($sqlL, $db);
    $resultrow = DB_fetch_array($result);

    for ($i = 0; $i <= $resultrow['maxlineno']; $i++) {

        $sqlO = "select MAX(lineoptionno) as maxlineoptionno from salesorderdetails where orderno = '" . $_GET['QuotationNo'] . "'
AND orderlineno = " . $i . "";
        $resultO = DB_query($sqlO, $db);
        $resultrowO = DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";

        for ($j = 0; $j <= $resultrowO['maxlineoptionno']; $j++) {
            $sqlA = "SELECT *
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE salesorderdetails.orderno='" . $_GET['QuotationNo'] . "'";
            $resultA = DB_query($sqlA, $db);
            $TransferRow = DB_fetch_array($resultA);

            $line = 0;
            $sqlB = "SELECT *
	FROM salesorderlines 
	WHERE salesorderlines.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderlines.lineno='" . $i . "'
	";
            $resultB = DB_query($sqlB, $db);
            $TransferRowB = DB_fetch_array($resultB);
            //$clientrequirements = $TransferRowB['clientrequirements'];
            //	$TransferRowB['clientrequirements'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowB['clientrequirements']);
//$TransferRowB['clientrequirements'] = stripslashes($TransferRowB['clientrequirements']);

            //$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</p>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<p>", "", $TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</div>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<div>", "", $TransferRowB['clientrequirements']);

            $html2 .= '<div style="padding:5px;"><table nobr="true" border = "1" width = "100%">

<tr><th width = "10%" align="center">' . '<b>Serial No. </b>.</th>
<th width = "50%" align="center"><b> Description </b></th>
<th width = "10%"align="center"> <b>Quantity</b></th>
<th width = "15%" align="center"> <b>Rate</b></th>

<th width = "15%" align="center"><b>Total</b></th>
</tr>

<tr>';
            if ($resultrowO['maxlineoptionno'] == 0)
                $html2 .= '<td width = "10%" align="center">' . '<h3>' . ($i + 1) . '</h3></td>';

            else
                $html2 .= '<td width = "10%" align="center">' . '<h3>' . ($i + 1) . ' Option No. ' . ($j + 1) . '</h3>' . '</td>';

            $html2 .= '<td width = "50%" ><table><tr><td>' . '<b><u>Client Required</u></b>.<br /> ' . utf8_decode($TransferRowB['clientrequirements']) . '</td></tr>';

//<tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
//$html2.= '<td width = "10%" align = "center">'.'Model No'.'</td>';
//$html2.= '<td width = "15%" align = "center">'.'Part No'.'</td>';
//$html2.= '<td width = "20%" align = "center">'.'Brand'.'</td>';
//$html2.= '<td width = "30%">'.'Description'.'</td>';
//$html2.= '<td width = "10%" align = "center">'.'quantity'.'</td>

//<td width = "10%" align = "center">'.'Rate'.'</td>
//</tr>';
            $total = 0;

            $optionUOM = "";
            do {
                if (($i == $TransferRow['orderlineno']) AND ($j == $TransferRow['lineoptionno'])) {
                    $sqlC = "SELECT *
	FROM salesorderoptions 
	WHERE salesorderoptions.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderoptions.lineno='" . $i . "'
	AND salesorderoptions.optionno='" . $j . "'
	
	";
                    $resultC = DB_query($sqlC, $db);
                    $TransferRowC = DB_fetch_array($resultC);
                    $line++;

                    $optionUOM = $TransferRowC['uom'];

//$html2.= '<tr><td width = "5%" align = "center">'.$line.'</td>';
//$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
//$html2.= '<td width = "15%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
//$html2.= '<td width = "20%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
//$html2.= '<td width = "30%">'.$TransferRow['description'].'</td>';
//$html2.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td>
//';

//$html2.= '<td width = "10%">'.'Rs.'.locale_number_format(($TransferRow['unitprice']*(1 - $TransferRow['discountpercent'])),0).'</td></tr>';

                    $total = $total + ($TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent']));

                }
            } while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</p>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<p>", "", $TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</div>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<div>", "", $TransferRowC['optiontext']);
            $html2 .= '<tr><td>' . '<b><u>We Offer </b></u><br /> ' . utf8_decode($TransferRowC['optiontext']) . '</td></tr></table></td>

<td width = "10%" align="center">' . '' . $TransferRowC['quantity'] . '<br>' . $optionUOM . '</td>
<td width = "15%" align="center">PKR' . '' . locale_number_format($total, 2) . getparityrate($currency, $values, $total).'

</td>

<td width = "15%" align="center">PKR' . '' . locale_number_format(round($TransferRowC['quantity'] * $total), 2) . getparityrate($currency, $values, $TransferRowC['quantity'] * $total).'</td>
</tr><tr><td colspan="7">' . 'Stock Status: ' . $TransferRowC['stockstatus'] . '</td>
</tr>
</table></div>';
        }
    }

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

    $sqlquotevalue = "SELECT salesorders.salescaseref, salesorderdetails.orderno,
 SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity )
 as value from salesorderdetails INNER JOIN salesorderoptions on
 (salesorderdetails.orderno = salesorderoptions.orderno AND 
 salesorderdetails.orderlineno = salesorderoptions.lineno) 
 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno 
 WHERE salesorderdetails.lineoptionno = 0 and salesorderoptions.optionno = 0 and salesorders.orderno='" . $_GET['QuotationNo'] . "'";

    $resultsqlquotevalue = DB_query($sqlquotevalue, $db);
    $rowquotevalue = DB_fetch_array($resultsqlquotevalue);
    $html2 .= '<div align="right">
 <table border="1">';
 if ($TransferRow2['GSTadd'] == 'update' ) {
    $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value']), 2) .getparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';
    if ($TransferRow2['services'] == 1) {
        $html2 .= '<tr><td><b>16% GST</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 0.16). '</td></tr>';
        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.16).   '</td></tr>';


        }

    } else {
        $html2 .= '<tr><td><b>18% GST</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 0.18), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 0.18).   '</td></tr>';
        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
' . locale_number_format(round($rowquotevalue['value'] * 1.18), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.18).    '</td></tr>';


        }


    }

}


    if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14') ) {
        $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) .getparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';
        if ($TransferRow2['services'] == 1) {
            $html2 .= '<tr><td><b>16% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 0.16). '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.16).   '</td></tr>';


            }

        } else {
            $html2 .= '<tr><td><b>17% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 0.17), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 0.17).   '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.17 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.17 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.17), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.17).    '</td></tr>';


            }


        }

    }

    elseif ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14') ) {
        $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) .getparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';
        if ($TransferRow2['services'] == 1) {
            $html2 .= '<tr><td><b>16% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 0.16). '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.16).   '</td></tr>';


            }

        } else {
            $html2 .= '<tr><td><b>18% GST</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 0.18), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 0.18).   '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] * 1.18), 2) .getparityrate($currency, $values, $rowquotevalue['value'] * 1.18).    '</td></tr>';


            }


        }

    }
    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {


        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'], 2) .getparityrate($currency, $values, $rowquotevalue['value']).     ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) .getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).      '</td></tr>';
            } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>PKR
    ' . locale_number_format(round($rowquotevalue['value']), 2) .getparityrate($currency, $values, $rowquotevalue['value']).      '</td></tr>';


                }

            } else {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>PKR
    ' . locale_number_format($rowquotevalue['value'], 2) .getparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
    ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
    ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) .getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>PKR
    ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']).'</td></tr>';


                }


            }


        }

        elseif ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {


            if ($TransferRow2['services'] == 1) {
    
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>PKR
     ' . locale_number_format($rowquotevalue['value'], 2) .getparityrate($currency, $values, $rowquotevalue['value']).     ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
     ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) .getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).      '</td></tr>';
                } else {
    
                        $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>PKR
        ' . locale_number_format(round($rowquotevalue['value']), 2) .getparityrate($currency, $values, $rowquotevalue['value']).      '</td></tr>';
    
    
                    }
    
                } else {
    
                    if ($TransferRow2['WHT'] != 0) {
                        $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>PKR
        ' . locale_number_format($rowquotevalue['value'], 2) .getparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';
                        $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
        ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) .getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                        $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
        ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) .getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                    } else {
    
                        $html2 .= '<tr><td><b>Grand Total inclusive of 18% GST</b></td><td>PKR
        ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']).'</td></tr>';
    
    
                    }
    
    
                }
    
    
            }

    if ($TransferRow2['GSTadd'] == '') {


        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']).'</td></tr>';
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>PKR
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).'</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) .getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>PKR
 ' . locale_number_format(round($rowquotevalue['value']), 2) .getparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';


        }

    }

    $html2 .= '</table>
 </div>';
    $html2 .= 'In case of multiple options amount of first option is considered<br/>';

    $pdf->writeHTML(utf8_decode(html_entity_decode($html2)), true, false, true, false, '');


    $htmlpayment .= '<b>Terms and Conditions: </b><br>
Stock availability is subject to prior sales.<br>Company reserves the right to apply force majeur clause without any repercussion to the relationship between the customer and itself.<br>';

if($TransferRow2['GSTadd'] == 'update'){
    $GSTadd = 'exclusive';
}
    $htmlpayment .= getPriceChangeStatement($TransferRow2['rate_clause'], $TransferRow2['clause_rates'], $TransferRow2['rate_validity'], $GSTadd,$TransferRow2['freightclause'],$TransferRow2['validity']);



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
    if($TransferRow2['validity'] != 0){
        $htmlpaymentterms.= '<br>Quoted Price Valid For '.$TransferRow2['validity']." Days.<br>";
    }




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
//$pdf->writeHTML($html3, true, false, true, false, '');
    ob_end_clean();  header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf','I');
    $pdf->__destruct();

    function utf8_decode(&$input)
    {
        if (is_string($input)) {
            $input = utf8_decode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                utf8_decode($value);
            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                utf8_decode($input->$var);
            }
        }
    }
}



//Only Exchange Prices


if ($TransferRowS['printexchange']==2) {
    $sqlSalesperson = "SELECT * from salesman inner join 
www_users on salesman.salesmanname = www_users.realname
WHERE salesman.salesmanname = 
'" . $TransferRowS['salesmanname'] . "'";

    $resultSalesperson = DB_query($sqlSalesperson, $db, $ErrMsg);
    $TransferRowSalesperson = DB_fetch_array($resultSalesperson);

    If (DB_num_rows($result) == 0) {

        include('includes/header.inc');
        prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'), 'error');
        include('includes/footer.inc');
        exit;
    }

    $TransferRow = DB_fetch_array($result);


    include('includes/PDFQuotationPageHeader.inc');
//$html2.='<span align="right">Page '.$pdf->getAliasNumPage() . " of " . $pdf->getAliasNbPages().'</span>';

    $sqlL = "select MAX(orderlineno) as maxlineno from salesorderdetails where orderno = '" . $_GET['QuotationNo'] . "'";
    $result = DB_query($sqlL, $db);
    $resultrow = DB_fetch_array($result);

    for ($i = 0; $i <= $resultrow['maxlineno']; $i++) {

        $sqlO = "select MAX(lineoptionno) as maxlineoptionno from salesorderdetails where orderno = '" . $_GET['QuotationNo'] . "'
AND orderlineno = " . $i . "";
        $resultO = DB_query($sqlO, $db);
        $resultrowO = DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";

        for ($j = 0; $j <= $resultrowO['maxlineoptionno']; $j++) {
            $sqlA = "SELECT *
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE salesorderdetails.orderno='" . $_GET['QuotationNo'] . "'";
            $resultA = DB_query($sqlA, $db);
            $TransferRow = DB_fetch_array($resultA);

            $line = 0;
            $sqlB = "SELECT *
	FROM salesorderlines 
	WHERE salesorderlines.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderlines.lineno='" . $i . "'
	";
            $resultB = DB_query($sqlB, $db);
            $TransferRowB = DB_fetch_array($resultB);
            //$clientrequirements = $TransferRowB['clientrequirements'];
            //	$TransferRowB['clientrequirements'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowB['clientrequirements']);
//$TransferRowB['clientrequirements'] = stripslashes($TransferRowB['clientrequirements']);

            //$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</p>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<p>", "", $TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</div>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<div>", "", $TransferRowB['clientrequirements']);

            $html2 .= '<div style="padding:5px;"><table nobr="true" border = "1" width = "100%">

<tr><th width = "10%" align="center">' . '<b>Serial No. </b>.</th>
<th width = "50%" align="center"><b> Description </b></th>
<th width = "10%"align="center"> <b>Quantity</b></th>
<th width = "15%" align="center"> <b>Rate</b></th>

<th width = "15%" align="center"><b>Total</b></th>
</tr>

<tr>';
            if ($resultrowO['maxlineoptionno'] == 0)
                $html2 .= '<td width = "10%" align="center">' . '<h3>' . ($i + 1) . '</h3></td>';

            else
                $html2 .= '<td width = "10%" align="center">' . '<h3>' . ($i + 1) . ' Option No. ' . ($j + 1) . '</h3>' . '</td>';

            $html2 .= '<td width = "50%" ><table><tr><td>' . '<b><u>Client Required</u></b>.<br /> ' . utf8_decode($TransferRowB['clientrequirements']) . '</td></tr>';

//<tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
//$html2.= '<td width = "10%" align = "center">'.'Model No'.'</td>';
//$html2.= '<td width = "15%" align = "center">'.'Part No'.'</td>';
//$html2.= '<td width = "20%" align = "center">'.'Brand'.'</td>';
//$html2.= '<td width = "30%">'.'Description'.'</td>';
//$html2.= '<td width = "10%" align = "center">'.'quantity'.'</td>

//<td width = "10%" align = "center">'.'Rate'.'</td>
//</tr>';
            $total = 0;

            $optionUOM = "";
            do {
                if (($i == $TransferRow['orderlineno']) AND ($j == $TransferRow['lineoptionno'])) {
                    $sqlC = "SELECT *
	FROM salesorderoptions 
	WHERE salesorderoptions.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderoptions.lineno='" . $i . "'
	AND salesorderoptions.optionno='" . $j . "'
	
	";
                    $resultC = DB_query($sqlC, $db);
                    $TransferRowC = DB_fetch_array($resultC);
                    $line++;

                    $optionUOM = $TransferRowC['uom'];

//$html2.= '<tr><td width = "5%" align = "center">'.$line.'</td>';
//$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
//$html2.= '<td width = "15%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
//$html2.= '<td width = "20%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
//$html2.= '<td width = "30%">'.$TransferRow['description'].'</td>';
//$html2.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['quantity'],$TransferRow['decimalplaces']).'</center></td>
//';

//$html2.= '<td width = "10%">'.'Rs.'.locale_number_format(($TransferRow['unitprice']*(1 - $TransferRow['discountpercent'])),0).'</td></tr>';

                    $total = $total + ($TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent']));

                }
            } while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</p>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<p>", "", $TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</div>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<div>", "", $TransferRowC['optiontext']);
            $html2 .= '<tr><td>' . '<b><u>We Offer </b></u><br /> ' . utf8_decode($TransferRowC['optiontext']) . '</td></tr></table></td>

<td width = "10%" align="center">' . '' . $TransferRowC['quantity'] . '<br>' . $optionUOM . '</td>
<td width = "15%" align="center">' . '' . getonlyparityrate($currency, $values, $total).'

</td>

<td width = "15%" align="center">' . '' . getonlyparityrate($currency, $values, $TransferRowC['quantity'] * $total).'</td>
</tr><tr><td colspan="7">' . 'Stock Status: ' . $TransferRowC['stockstatus'] . '</td>
</tr>
</table></div>';
        }
    }

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

    $sqlquotevalue = "SELECT salesorders.salescaseref, salesorderdetails.orderno,
 SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity )
 as value from salesorderdetails INNER JOIN salesorderoptions on
 (salesorderdetails.orderno = salesorderoptions.orderno AND 
 salesorderdetails.orderlineno = salesorderoptions.lineno) 
 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno 
 WHERE salesorderdetails.lineoptionno = 0 and salesorderoptions.optionno = 0 and salesorders.orderno='" . $_GET['QuotationNo'] . "'";

    $resultsqlquotevalue = DB_query($sqlquotevalue, $db);
    $rowquotevalue = DB_fetch_array($resultsqlquotevalue);
    $html2 .= '<div align="right">
 <table border="1">';
 if ($TransferRow2['GSTadd'] == 'update') {
    $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
' . getonlyparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';
    if ($TransferRow2['services'] == 1) {
        $html2 .= '<tr><td><b>16% GST</b></td><td>
' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 0.16). '</td></tr>';
        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>
' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>
' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.16).   '</td></tr>';


        }

    } else {
        $html2 .= '<tr><td><b>18% GST</b></td><td>
' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 0.1).   '</td></tr>';
        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
' . getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>
' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.1 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>
' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.18).    '</td></tr>';


        }


    }

}

    if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {
        $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . getonlyparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';
        if ($TransferRow2['services'] == 1) {
            $html2 .= '<tr><td><b>16% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 0.16). '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.16).   '</td></tr>';


            }

        } else {
            $html2 .= '<tr><td><b>17% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 0.17).   '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.17 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.17).    '</td></tr>';


            }


        }

    }
    elseif ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {
        $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . getonlyparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';
        if ($TransferRow2['services'] == 1) {
            $html2 .= '<tr><td><b>16% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 0.16). '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.16).   '</td></tr>';


            }

        } else {
            $html2 .= '<tr><td><b>18% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 0.1).   '</td></tr>';
            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.1 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).   '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * 1.18).    '</td></tr>';


            }


        }

    }
    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {


        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']).     ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).      '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']).      ' </td></tr>';


            }

        } else {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']). ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']).' </td></tr>';


            }


        }


    }
    if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {


        if ($TransferRow2['services'] == 1) {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']).     ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).      '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']).      ' </td></tr>';


            }

        } else {

            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']). ' </td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).  '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total inclusive of 18% GST</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']).' </td></tr>';


            }


        }


    }

    if ($TransferRow2['GSTadd'] == '') {


        if ($TransferRow2['WHT'] != 0) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . getonlyparityrate($currency, $values, $rowquotevalue['value']).'</td></tr>';
            $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getonlyparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100).'</td></tr>';
            $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100). '</td></tr>';
        } else {

            $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' .getonlyparityrate($currency, $values, $rowquotevalue['value']). '</td></tr>';


        }

    }

    $html2 .= '</table>
 </div>';
    $html2 .= 'In case of multiple options amount of first option is considered<br/>';

    $pdf->writeHTML(utf8_decode($html2), true, false, true, false, '');


    $htmlpayment .= '<b>Terms and Conditions: </b><br>
Stock availability is subject to prior sales<br>Company reserves the right to apply force majeur clause without any repercussion to the relationship between the customer and itself.<br>';

if($TransferRow2['GSTadd'] == 'update'){
    $GSTadd = 'exclusive';
}
    $htmlpayment .= getPriceChangeStatement($TransferRow2['rate_clause'], $TransferRow2['clause_rates'], $TransferRow2['rate_validity'], $GSTadd,$TransferRow2['freightclause'],$TransferRow2['validity']);



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
    if($TransferRow2['validity'] != 0){
        $htmlpaymentterms.= '<br>Quoted Price Valid For '.$TransferRow2['validity']." Days.<br>";
    }



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
//$pdf->writeHTML($html3, true, false, true, false, '');
    ob_end_clean();

    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf','I');
    $pdf->__destruct();


}

?>