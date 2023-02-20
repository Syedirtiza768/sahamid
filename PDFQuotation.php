<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');
$_SESSION['document']= 'QuotationNo: '.$_GET['QuotationNo'];

include('includes/PDFStarter2.php');



$pdf->addInfo('Title', _('Quotation') );
$pdf->addInfo('Subject', _('Quotation') . ' # ' . $_GET['RequestNo']);

$pdf->SetAutoPageBreak(TRUE, 15);
$FontSize=10;
$PageNumber=0;
$line_height=30;

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT salesorders.customerref,
				salesorders.salescaseref,
				salesorders.contactphone,
				salesorders.contactemail,
				salesorders.comments,
				salesorders.orddate,
				salesorders.deliverto,
				salesorders.deladd1,
				salesorders.deladd2,
				salesorders.deladd3,
				salesorders.deladd4,
				salesorders.deladd5,
				salesorders.advance,
				salesorders.delivery,
				salesorders.commisioning,
				salesorders.after,
				salesorders.afterdays,
				salesorders.GSTadd,
				salesorders.quotedate,
				salesorders.services,
				salesorders.rate_clause,
				salesorders.clause_rates,
				salesorders.WHT,
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

$currency=$TransferRow2['rate_clause'];
$values=$TransferRow2['clause_rates'];


if(!($_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 8 || $_SESSION['AccessLevel'] == 10)) {

	
	
	$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
	$res = mysqli_query($db, $SQL);
	$canAccess = [];
	while($row = mysqli_fetch_assoc($res))
		$canAccess[] = $row['can_access'];

	$SQL = 'SELECT * FROM salescase 
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE salescase.salescaseref = "'.$TransferRow2['salescaseref'].'"
			AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
			OR www_users.userid IN ("'.implode('","', $canAccess).'") )';
	$res = mysqli_query($db, $SQL);

	if(!$res || mysqli_num_rows($res) == 0){
      
      echo "Access Denied!";
      return;

    }

}

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

    $sqlL = "select MAX(orderlineno) as maxlineno from salesorderdetails where orderno = " . $_GET['QuotationNo'] . "";
    $result = DB_query($sqlL, $db);
    $resultrow = DB_fetch_array($result);

    for ($line = 0; $line <= $resultrow['maxlineno']; $line++) {

        $sqlO = "select MAX(lineoptionno) as maxlineoptionno from salesorderdetails where orderno = " . $_GET['QuotationNo'] . "
AND orderlineno = " . $line . "";
        $resultO = DB_query($sqlO, $db);
        $resultrowO = DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";

        for ($option = 0; $option <= $resultrowO['maxlineoptionno']; $option++) {
            $sqlA = "SELECT *,salesorderdetails.lastcostupdate as lastupdated, salesorderdetails.lastupdatedby as updatedby
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE salesorderdetails.orderno='" . $_GET['QuotationNo'] . "'";
            $resultA = DB_query($sqlA, $db);
            $TransferRow = DB_fetch_array($resultA);

//$line  =0;
            $sqlB = "SELECT *
	FROM salesorderlines 
	WHERE salesorderlines.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderlines.lineno='" . $line . "'
	";
            $resultB = DB_query($sqlB, $db);
            $TransferRowB = DB_fetch_array($resultB);
            //$clientrequirements = $TransferRowB['clientrequirements'];
            //		$TransferRowB['clientrequirements'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowB['clientrequirements']);
//$TransferRowB['clientrequirements'] = stripslashes($TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</p>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<p>", "", $TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</div>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<div>", "", $TransferRowB['clientrequirements']);


            //$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowB['clientrequirements']);
            $clt = utf8_decode($TransferRowB['clientrequirements']);
            $html2 .= '<br><br><br><div style="padding:10px;"><table nobr="true" border = "1" width = "100%">
<tr><td colspan="7">' . '<h3>Line No. ' . ($line + 1) . ' Option No. ' . ($option + 1) . '</h3>' . '</td></tr>
<tr><td colspan="7">' . 'Client Required.<br /> ' . $clt . '</td></tr>

<tr align = "center"><td width = "5%">' . 'Sr#' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Model No' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Part No' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Brand' . '</td>';
            $html2 .= '<td width = "25%">' . 'Description' . '</td>';
            $html2 .= '<td width = "5%" align = "center">' . 'qty.' . '</td>
<td width = "10%" align = "center">' . 'List Price' . '</td>
<td width = "5%" align = "center">' . 'Disc.' . '</td>
<td width = "10%" align = "center">' . 'Unit Rate' . '</td>
<td width = "10%" align = "center">' . 'Sub Total' . '</td>
</tr>';
            $total = 0;
            do {
                if (($line == $TransferRow['orderlineno']) AND ($option == $TransferRow['lineoptionno'])) {
                    $sqlC = "SELECT *
	FROM salesorderoptions 
	WHERE salesorderoptions.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderoptions.lineno='" . $line . "'
	AND salesorderoptions.optionno='" . $option . "'
	
	";
                    $resultC = DB_query($sqlC, $db);
                    $TransferRowC = DB_fetch_array($resultC);
                    $lineindex = $line + 1;
                    $html2 .= '<tr><td width = "5%" align = "center">' . $lineindex . '</td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['mnfCode'] . '</center></td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['mnfpno'] . '</center></td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['manufacturers_name'] . '</center></td>';
                    $html2 .= '<td width = "25%">' . $TransferRow['description'] . '</td>';
                    $html2 .= '<td width = "5%"align = "center"><center>' .  locale_number_format($TransferRow['quantity'], 0) . '</center></td>
';
                    if ($TransferRow['lastupdated']=='0000-00-00')
                        $lastupdated="";
                    else
                        $lastupdated=ConvertSQLDateTime($TransferRow['lastupdated']);

                    $html2 .= '<td width = "10%"align = "center"><center>' . locale_number_format($TransferRow['unitprice'], 2) ."<br/>".  $lastupdated.'<br/>'. '</center></td>
';
                    $html2 .= '<td width = "5%"align = "center"><center>' . locale_number_format($TransferRow['discountpercent'] * 100, 2) . '</center></td>
';

                    $html2 .= '<td width = "10%">' . 'Rs.' . locale_number_format(($TransferRow['unitprice'] * (1 - $TransferRow['discountpercent'])), 2) .'</td>';

                    $total = $total + ($TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent']));
                    $html2 .= '<td width = "10%"align = "center"><center>' . locale_number_format(($TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent'])), $TransferRow['decimalplaces']) .'</center></td></tr>';

                }
            } while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</p>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<p>", "", $TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</div>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<div>", "", $TransferRowC['optiontext']);


            $html2 .= '
<tr><td colspan="9">' . 'We Offer.<br /> ' . utf8_decode($TransferRowC['optiontext']) . '</td></tr>

<tr><td colspan="3" align="center">' . 'Quantity: ' . $TransferRowC['quantity'] . ' ' . $TransferRowC['uom'] . ' </td>
<td colspan="4" align="center">' . 'Stock Status: ' . $TransferRowC['stockstatus'] . '</td>
<td colspan="3" align="center">' . 'Total: Rs.' . locale_number_format(round($TransferRowC['quantity'] * $total), 2) .'</td></tr>
</table></div><br/>';
            $SQL="SELECT * FROM quotationmodifications WHERE eorderno=".$_GET['QuotationNo']." AND lineno=$line";
            $result=mysqli_query($db,$SQL);
            while($row=mysqli_fetch_assoc($result)){

                $html2 .= $row['description']." at ".date("F j, Y, g:i a",strtotime($row['updatedat']))."<br/>";
            }
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


    if ($TransferRowS['printexchange'] == 0) {

        if ($TransferRow2['GSTadd'] == 'update' ) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
     ' . round($rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>18% GST</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 0.18), 2) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.18), 2) . '</td></tr>';


                }


            }

        }


        if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
     ' . round($rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>17% GST</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 0.17), 2) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.17 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.17), 2) . '</td></tr>';


                }


            }

        }

        elseif ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
     ' . round($rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>18% GST</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 0.18), 2) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] * 1.18), 2) . '</td></tr>';


                }


            }

        }


        if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {


            if ($TransferRow2['services'] == 1) {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
     ' . locale_number_format($rowquotevalue['value'], 2) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value']), 2) . ' </td></tr>';


                }

            } else {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
     ' . locale_number_format($rowquotevalue['value'], 2) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value']), 2) . ' </td></tr>';


                }


            }


        }
        elseif ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {


            if ($TransferRow2['services'] == 1) {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
     ' . locale_number_format($rowquotevalue['value'], 2) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value']), 2) . ' </td></tr>';


                }

            } else {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
     ' . locale_number_format($rowquotevalue['value'], 2) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
     ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 18% GST</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value']), 2) . ' </td></tr>';


                }


            }


        }

        if ($TransferRow2['GSTadd'] == '') {


            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
                        ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
                        ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>
     ' . locale_number_format(round($rowquotevalue['value']), 2) . '</td></tr>';


            }

        }
    }

    $html2 .= '</table>
 </div>';
    $html2 .= 'In case of multiple options amount of first option is considered<br/>';
    $html2 .= 'Payment Terms:<br>';
    if ($TransferRow2['advance'] > 0)
        $html2 .= 'Advance ' . $TransferRow2['advance'] . '%<br>';
    if ($TransferRow2['delivery'] > 0)
        $html2 .= 'On Delivery ' . $TransferRow2['delivery'] . '%<br>';
    if ($TransferRow2['commisioning'] > 0)
        $html2 .= 'On Commisioning ' . $TransferRow2['commisioning'] . '%<br>';
    if ($TransferRow2['after'] > 0)
        $html2 .= $TransferRow2['after'] . '% After ' . $TransferRow2['afterdays'] . 'days';

    $html2 .= $TransferRow2['comments'];

    if (isset($_GET['abcd'])) {
        echo $html2;
        return;
    }


    $html4 .= '</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
    $pdf->writeHTML($html2, true, false, true, false, '');

//$pdf->writeHTML($html3, true, false, true, false, '');
    ob_end_clean();
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf','I');

    $pdf->__destruct();
}




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

    $sqlL = "select MAX(orderlineno) as maxlineno from salesorderdetails where orderno = " . $_GET['QuotationNo'] . "";
    $result = DB_query($sqlL, $db);
    $resultrow = DB_fetch_array($result);

    for ($line = 0; $line <= $resultrow['maxlineno']; $line++) {

        $sqlO = "select MAX(lineoptionno) as maxlineoptionno from salesorderdetails where orderno = " . $_GET['QuotationNo'] . "
AND orderlineno = " . $line . "";
        $resultO = DB_query($sqlO, $db);
        $resultrowO = DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";

        for ($option = 0; $option <= $resultrowO['maxlineoptionno']; $option++) {
            $sqlA = "SELECT *,salesorderdetails.lastcostupdate as lastupdated, salesorderdetails.lastupdatedby as updatedby
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE salesorderdetails.orderno='" . $_GET['QuotationNo'] . "'";
            $resultA = DB_query($sqlA, $db);
            $TransferRow = DB_fetch_array($resultA);

//$line  =0;
            $sqlB = "SELECT *
	FROM salesorderlines 
	WHERE salesorderlines.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderlines.lineno='" . $line . "'
	";
            $resultB = DB_query($sqlB, $db);
            $TransferRowB = DB_fetch_array($resultB);
            //$clientrequirements = $TransferRowB['clientrequirements'];
            //		$TransferRowB['clientrequirements'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowB['clientrequirements']);
//$TransferRowB['clientrequirements'] = stripslashes($TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</p>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<p>", "", $TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</div>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<div>", "", $TransferRowB['clientrequirements']);


            //$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowB['clientrequirements']);
            $clt = utf8_decode($TransferRowB['clientrequirements']);
            $html2 .= '<br><br><br><div style="padding:10px;"><table nobr="true" border = "1" width = "100%">
<tr><td colspan="7">' . '<h3>Line No. ' . ($line + 1) . ' Option No. ' . ($option + 1) . '</h3>' . '</td></tr>
<tr><td colspan="7">' . 'Client Required.<br /> ' . $clt . '</td></tr>

<tr align = "center"><td width = "5%">' . 'Sr#' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Model No' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Part No' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Brand' . '</td>';
            $html2 .= '<td width = "25%">' . 'Description' . '</td>';
            $html2 .= '<td width = "5%" align = "center">' . 'qty.' . '</td>
<td width = "10%" align = "center">' . 'List Price' . '</td>
<td width = "5%" align = "center">' . 'Disc.' . '</td>
<td width = "10%" align = "center">' . 'Unit Rate' . '</td>
<td width = "10%" align = "center">' . 'Sub Total' . '</td>
</tr>';
            $total = 0;
            do {
                if (($line == $TransferRow['orderlineno']) AND ($option == $TransferRow['lineoptionno'])) {
                    $sqlC = "SELECT *
	FROM salesorderoptions 
	WHERE salesorderoptions.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderoptions.lineno='" . $line . "'
	AND salesorderoptions.optionno='" . $option . "'
	
	";
                    $resultC = DB_query($sqlC, $db);
                    $TransferRowC = DB_fetch_array($resultC);
                    $lineindex = $line + 1;
                    $html2 .= '<tr><td width = "5%" align = "center">' . $lineindex . '</td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['mnfCode'] . '</center></td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['mnfpno'] . '</center></td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['manufacturers_name'] . '</center></td>';
                    $html2 .= '<td width = "25%">' . $TransferRow['description'] . '</td>';
                    $html2 .= '<td width = "5%"align = "center"><center>' .  locale_number_format($TransferRow['quantity'], 0) . '</center></td>
';
                    if ($TransferRow['lastupdated']=='0000-00-00')
                        $lastupdated="";
                    else
                        $lastupdated=ConvertSQLDateTime($TransferRow['lastupdated']);

                    $html2 .= '<td width = "10%"align = "center"><center>' .locale_number_format($TransferRow['unitprice'], $TransferRow['decimalplaces']) . getparityrate($currency, $values, $TransferRow['unitprice']) ."<br/>".  $lastupdated.'<br/>'. '</center></td>
';
                    $html2 .= '<td width = "5%"align = "center"><center>' . locale_number_format($TransferRow['discountpercent'] * 100, 2) . '</center></td>
';

                    $html2 .= '<td width = "10%">' . 'Rs.' . locale_number_format(($TransferRow['unitprice'] * (1 - $TransferRow['discountpercent'])), 2) . getparityrate($currency, $values, $TransferRow['unitprice'] * (1 - $TransferRow['discountpercent'])) . '</td>';

                    $total = $total + ($TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent']));
                    $html2 .= '<td width = "10%"align = "center"><center>' . locale_number_format(($TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent'])), $TransferRow['decimalplaces']) . getparityrate($currency, $values, $TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent'])) . '</center></td></tr>';

                }
            } while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</p>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<p>", "", $TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</div>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<div>", "", $TransferRowC['optiontext']);


            $html2 .= '
<tr><td colspan="9">' . 'We Offer.<br /> ' . utf8_decode($TransferRowC['optiontext']) . '</td></tr>

<tr><td colspan="3" align="center">' . 'Quantity: ' . $TransferRowC['quantity'] . ' ' . $TransferRowC['uom'] . ' </td>
<td colspan="4" align="center">' . 'Stock Status: ' . $TransferRowC['stockstatus'] . '</td>
<td colspan="3" align="center">' . 'Total: Rs.' . locale_number_format(round($TransferRowC['quantity'] * $total), 2) . getparityrate($currency, $values, $TransferRowC['quantity'] * $total) . '</td></tr>
</table></div><br/>';
            $SQL="SELECT * FROM quotationmodifications WHERE eorderno=".$_GET['QuotationNo']." AND lineno=$line";
            $result=mysqli_query($db,$SQL);
            while($row=mysqli_fetch_assoc($result)){
                $html2 .= $row['description']." at ".date("F j, Y, g:i a",strtotime($row['updatedat']))."<br/>";
                }
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


    if ($TransferRowS['printexchange'] == 1) {

        if ($TransferRow2['GSTadd'] == 'update' ) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>18% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 0.18), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 0.18) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.18), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18) . '</td></tr>';


                }


            }

        }

        if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>17% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 0.17), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 0.17) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.17 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.17 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.17), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.17) . '</td></tr>';


                }


            }

        }
        elseif ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 0.16), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . (locale_number_format(round($rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2)) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.16), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>18% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 0.18), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 0.18) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] * 1.17), 2) . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18) . '</td></tr>';


                }


            }

        }


        if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {


            if ($TransferRow2['services'] == 1) {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' . locale_number_format($rowquotevalue['value'], 2) . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';


                }

            } else {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' . locale_number_format($rowquotevalue['value'], 2) . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';


                }


            }


        }
        elseif ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {


            if ($TransferRow2['services'] == 1) {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' . locale_number_format($rowquotevalue['value'], 2) . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';


                }

            } else {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' . locale_number_format($rowquotevalue['value'], 2) . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 18% GST</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';


                }


            }


        }


        if ($TransferRow2['GSTadd'] == '') {


            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . locale_number_format($rowquotevalue['value'] * $TransferRow2['WHT'] / 100, 2) . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100), 2) . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . locale_number_format(round($rowquotevalue['value']), 2) . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';


            }

        }

    }

    $html2 .= '</table>
 </div>';
    $html2 .= 'In case of multiple options amount of first option is considered<br/>';
    $html2 .= 'Payment Terms:<br>';
    if ($TransferRow2['advance'] > 0)
        $html2 .= 'Advance ' . $TransferRow2['advance'] . '%<br>';
    if ($TransferRow2['delivery'] > 0)
        $html2 .= 'On Delivery ' . $TransferRow2['delivery'] . '%<br>';
    if ($TransferRow2['commisioning'] > 0)
        $html2 .= 'On Commisioning ' . $TransferRow2['commisioning'] . '%<br>';
    if ($TransferRow2['after'] > 0)
        $html2 .= $TransferRow2['after'] . '% After ' . $TransferRow2['afterdays'] . 'days';

    $html2 .= $TransferRow2['comments'];

    if (isset($_GET['abcd'])) {
        echo $html2;
        return;
    }


    $html4 .= '</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
    $pdf->writeHTML($html2, true, false, true, false, '');

//$pdf->writeHTML($html3, true, false, true, false, '');
    ob_end_clean();
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf','I');

    $pdf->__destruct();
}




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

    $sqlL = "select MAX(orderlineno) as maxlineno from salesorderdetails where orderno = " . $_GET['QuotationNo'] . "";
    $result = DB_query($sqlL, $db);
    $resultrow = DB_fetch_array($result);

    for ($line = 0; $line <= $resultrow['maxlineno']; $line++) {

        $sqlO = "select MAX(lineoptionno) as maxlineoptionno from salesorderdetails where orderno = " . $_GET['QuotationNo'] . "
AND orderlineno = " . $line . "";
        $resultO = DB_query($sqlO, $db);
        $resultrowO = DB_fetch_array($resultO);
//print_r($resultrowO);
//echo "<b>hello".$resultrowO['maxlineoptionno']."world</b>";

        for ($option = 0; $option <= $resultrowO['maxlineoptionno']; $option++) {
            $sqlA = "SELECT *,salesorderdetails.lastcostupdate as lastupdated, salesorderdetails.lastupdatedby as updatedby
	FROM salesorderdetails INNER JOIN stockmaster
		ON salesorderdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE salesorderdetails.orderno='" . $_GET['QuotationNo'] . "'";
            $resultA = DB_query($sqlA, $db);
            $TransferRow = DB_fetch_array($resultA);

//$line  =0;
            $sqlB = "SELECT *
	FROM salesorderlines 
	WHERE salesorderlines.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderlines.lineno='" . $line . "'
	";
            $resultB = DB_query($sqlB, $db);
            $TransferRowB = DB_fetch_array($resultB);
            //$clientrequirements = $TransferRowB['clientrequirements'];
            //		$TransferRowB['clientrequirements'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowB['clientrequirements']);
//$TransferRowB['clientrequirements'] = stripslashes($TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</p>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<p>", "", $TransferRowB['clientrequirements']);
            $TransferRowB['clientrequirements'] = str_replace("</div>", "", $TransferRowB['clientrequirements']);

            $TransferRowB['clientrequirements'] = str_replace("<div>", "", $TransferRowB['clientrequirements']);


            //$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowB['clientrequirements']);
            $clt = utf8_decode($TransferRowB['clientrequirements']);
            $html2 .= '<br><br><br><div style="padding:10px;"><table nobr="true" border = "1" width = "100%">
<tr><td colspan="7">' . '<h3>Line No. ' . ($line + 1) . ' Option No. ' . ($option + 1) . '</h3>' . '</td></tr>
<tr><td colspan="7">' . 'Client Required.<br /> ' . $clt . '</td></tr>

<tr align = "center"><td width = "5%">' . 'Sr#' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Model No' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Part No' . '</td>';
            $html2 .= '<td width = "10%" align = "center">' . 'Brand' . '</td>';
            $html2 .= '<td width = "25%">' . 'Description' . '</td>';
            $html2 .= '<td width = "5%" align = "center">' . 'qty.' . '</td>
<td width = "10%" align = "center">' . 'List Price' . '</td>
<td width = "5%" align = "center">' . 'Disc.' . '</td>
<td width = "10%" align = "center">' . 'Unit Rate' . '</td>
<td width = "10%" align = "center">' . 'Sub Total' . '</td>
</tr>';
            $total = 0;

            do {
                if (($line == $TransferRow['orderlineno']) AND ($option == $TransferRow['lineoptionno'])) {
                    $sqlC = "SELECT *
	FROM salesorderoptions 
	WHERE salesorderoptions.orderno='" . $_GET['QuotationNo'] . "'
	AND salesorderoptions.lineno='" . $line . "'
	AND salesorderoptions.optionno='" . $option . "'
	
	";
                    $resultC = DB_query($sqlC, $db);
                    $TransferRowC = DB_fetch_array($resultC);
                    $lineindex = $line + 1;
                    $html2 .= '<tr><td width = "5%" align = "center">' . $lineindex . '</td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['mnfCode'] . '</center></td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['mnfpno'] . '</center></td>';
                    $html2 .= '<td width = "10%" align = "center"><center>' . $TransferRow['manufacturers_name'] . '</center></td>';
                    $html2 .= '<td width = "25%">' . $TransferRow['description'] . '</td>';
                    $html2 .= '<td width = "5%"align = "center"><center>' .  locale_number_format($TransferRow['quantity'], 0) . '</center></td>
';
                    if ($TransferRow['lastupdated']=='0000-00-00')
                        $lastupdated="";
                    else
                        $lastupdated=ConvertSQLDateTime($TransferRow['lastupdated']);

                    $html2 .= '<td width = "10%"align = "center"><center>' . getparityrate($currency, $values, $TransferRow['unitprice']) ."<br/>".  $lastupdated.'<br/>'. '</center></td>
';
                    $html2 .= '<td width = "5%"align = "center"><center>' . locale_number_format($TransferRow['discountpercent'] * 100, 2) . '</center></td>
';

                    $html2 .= '<td width = "10%">' . getparityrate($currency, $values, $TransferRow['unitprice'] * (1 - $TransferRow['discountpercent'])) . '</td>';

                    $total = $total + ($TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent']));
                    $html2 .= '<td width = "10%"align = "center"><center>' . getparityrate($currency, $values, $TransferRow['unitprice'] * $TransferRow['quantity'] * (1 - $TransferRow['discountpercent'])) . '</center></td></tr>';

                }
            } while ($TransferRow = DB_fetch_array($resultA));

//$TransferRowC['optiontext'] = preg_replace('#(\\\r|\\\r\\\n|\\\n|\\r|\\r\\n|\\n)#','<br/>',$TransferRowC['optiontext']);
//$TransferRowC['optiontext'] = stripslashes($TransferRowC['optiontext']);

//	$TransferRowC['optiontext'] = preg_replace('#(\)#','',$TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</p>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<p>", "", $TransferRowC['optiontext']);
            $TransferRowC['optiontext'] = str_replace("</div>", "", $TransferRowC['optiontext']);

            $TransferRowC['optiontext'] = str_replace("<div>", "", $TransferRowC['optiontext']);


            $html2 .= '
<tr><td colspan="9">' . 'We Offer.<br /> ' . utf8_decode($TransferRowC['optiontext']) . '</td></tr>

<tr><td colspan="3" align="center">' . 'Quantity: ' . $TransferRowC['quantity'] . ' ' . $TransferRowC['uom'] . ' </td>
<td colspan="4" align="center">' . 'Stock Status: ' . $TransferRowC['stockstatus'] . '</td>
<td colspan="3" align="center">' . 'Total:'. getparityrate($currency, $values, $TransferRowC['quantity'] * $total) . '</td></tr>
</table></div><br/>';
            $SQL="SELECT * FROM quotationmodifications WHERE eorderno=".$_GET['QuotationNo']." AND lineno=$line";
            $result=mysqli_query($db,$SQL);
            while($row=mysqli_fetch_assoc($result)){
                $html2 .= $row['description']." at ".date("F j, Y, g:i a",strtotime($row['updatedat']))."<br/>";
            }
        }
    }
/*    $SQL="SELECT * FROM quotationmodifications WHERE eorderno=".$_GET['QuotationNo']." AND lineno>
    (SELECT MAX";
    $result=mysqli_query($db,$SQL);
    while($row=mysqli_fetch_assoc($result)){
        $html2 .= $row['description']." at ".date("F j, Y, g:i a",strtotime($row['updatedat']))."<br/>";
    }*/
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


    if ($TransferRowS['printexchange'] == 2) {
        if ($TransferRow2['GSTadd'] == 'update') {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>18% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 0.18) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18) . '</td></tr>';


                }


            }

        }

        if ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>17% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 0.17) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.17 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.17) . '</td></tr>';


                }


            }

        }
        elseif ($TransferRow2['GSTadd'] == 'exclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {
            $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';
            if ($TransferRow2['services'] == 1) {
                $html2 .= '<tr><td><b>16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 0.16) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.16) . '</td></tr>';


                }

            } else {
                $html2 .= '<tr><td><b>18% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 0.18) . '</td></tr>';
                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18 + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * 1.18) . '</td></tr>';


                }


            }

        }
        if ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) < strtotime('2023-02-14')) {


            if ($TransferRow2['services'] == 1) {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';


                }

            } else {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 17% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';


                }


            }


        }
        elseif ($TransferRow2['GSTadd'] == 'inclusive' && strtotime($TransferRow2['quotedate']) >= strtotime('2023-02-14')) {


            if ($TransferRow2['services'] == 1) {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';


                }

            } else {

                if ($TransferRow2['WHT'] != 0) {
                    $html2 .= '<tr><td><b>SUBTOTAL inclusive of 16% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';
                    $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                    $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                } else {

                    $html2 .= '<tr><td><b>Grand Total inclusive of 18% GST</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . ' </td></tr>';


                }


            }


        }

        if ($TransferRow2['GSTadd'] == '') {


            if ($TransferRow2['WHT'] != 0) {
                $html2 .= '<tr><td><b>SUBTOTAL</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';
                $html2 .= '<tr><td><b>' . $TransferRow2['WHT'] . '% Witholding Tax</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value'] + $rowquotevalue['value'] * $TransferRow2['WHT'] / 100) . '</td></tr>';
            } else {

                $html2 .= '<tr><td><b>Grand Total</b></td><td>
 ' . getparityrate($currency, $values, $rowquotevalue['value']) . '</td></tr>';


            }

        }

    }


    $html2 .= '</table>
 </div>';
    $html2 .= 'In case of multiple options amount of first option is considered<br/>';
    $html2 .= 'Payment Terms:<br>';
    if ($TransferRow2['advance'] > 0)
        $html2 .= 'Advance ' . $TransferRow2['advance'] . '%<br>';
    if ($TransferRow2['delivery'] > 0)
        $html2 .= 'On Delivery ' . $TransferRow2['delivery'] . '%<br>';
    if ($TransferRow2['commisioning'] > 0)
        $html2 .= 'On Commisioning ' . $TransferRow2['commisioning'] . '%<br>';
    if ($TransferRow2['after'] > 0)
        $html2 .= $TransferRow2['after'] . '% After ' . $TransferRow2['afterdays'] . 'days';

    $html2 .= $TransferRow2['comments'];

    if (isset($_GET['abcd'])) {
        echo $html2;
        return;
    }


    $html4 .= '</table>';
//$pdf->writeHTML($html4, true, false, true, false, '');
    $pdf->writeHTML($html2, true, false, true, false, '');

//$pdf->writeHTML($html3, true, false, true, false, '');
    ob_end_clean();
    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_Quotation_' . $_GET['QuotationNo'] . '.pdf','I');
    $pdf->__destruct();
}


?>