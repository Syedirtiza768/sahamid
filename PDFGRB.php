<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('GRB');
$document= 'GRBNo: '.$_GET['grbno'];
include('includes/PDFStarter2.php');



$pdf->addInfo('Title', _('GRB') );
$pdf->addInfo('Subject', _('GRB') . ' # ' . $_GET['grbno']);

$pdf->SetAutoPageBreak(TRUE, 15);
$FontSize=10;
$PageNumber=0;
$line_height=30;

$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
echo $sql = "SELECT dcs.customerref,
				grb.salescaseref,
				grb.dcno,
				dcs.contactphone,
				dcs.contactemail,
				dcs.comments,
				grb.orddate,
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
			FROM grb INNER JOIN dcs ON grb.dcno=dcs.orderno
			INNER JOIN debtorsmaster
			ON dcs.debtorno=debtorsmaster.debtorno
			INNER JOIN salescase ON dcs.salescaseref = salescase.salescaseref
			INNER JOIN shippers
			ON dcs.shipvia=shippers.shipper_id
			INNER JOIN locations
			ON dcs.fromstkloc=locations.loccode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE dcs.quotation=1
			AND grb.orderno=" . $_GET['grbno'] ."";

$result=DB_query($sql,$db, $ErrMsg);
$TransferRow2 = DB_fetch_array($result);
$sqlS = "SELECT * from grb INNER JOIN
		debtorsmaster 
		ON grb.debtorno = debtorsmaster.debtorno
		INNER JOIN custbranch 
		ON debtorsmaster.debtorno = custbranch.debtorno
		INNER JOIN dba ON dba.companyname = debtorsmaster.dba
		INNER JOIN salesman
		ON custbranch.salesman = salesman.salesmancode
		WHERE orderno = 


'" . $_GET['grbno'] ."'";

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


include ('includes/PDFGRBPageHeader.inc');
$html2.= '<br><h2>List Of Items Returned</h2><br><br><div style="padding:10px;"><table nobr="true" border = "1" width = "100%">


<tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
$html2.= '<td width = "15%" align = "center">'.'Model No'.'</td>';
$html2.= '<td width = "15%" align = "center">'.'Part No'.'</td>';
$html2.= '<td width = "25%" align = "center">'.'Brand'.'</td>';
$html2.= '<td width = "30%">'.'Description'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'qty.'.'</td>

</tr>';
$sqlA = "SELECT *,SUM(quantity) as qty
	FROM grbdetails INNER JOIN stockmaster
		ON grbdetails.stkcode=stockmaster.stockid
		INNER JOIN manufacturers 
		ON manufacturers.manufacturers_id = stockmaster.brand
	WHERE grbdetails.orderno='" . $_GET['grbno'] . "'
	GROUP BY grbdetails.stkcode";

$resultA = DB_query($sqlA,$db);

$lineindex=0;
while ($TransferRow = DB_fetch_array($resultA)){

    $lineindex++ ;
    $html2.= '<tr><td width = "5%" align = "center">'.$lineindex .'</td>';
    $html2.= '<td width = "15%">'.$TransferRow['mnfCode'].'</td>';
    $html2.= '<td width = "15%" >'.$TransferRow['mnfpno'].'</td>';
    $html2.= '<td width = "25%" >'.$TransferRow['manufacturers_name'].'</td>';
    $html2.= '<td width = "30%">'.$TransferRow['description'].'</td>';
    $html2.= '<td width = "10%"align = "center"><center>'.locale_number_format($TransferRow['qty'],0).'</center></td></tr>
';
}
    $html2.= '</table>';

     $pdf->writeHTML($html2, true, false, true, false, '');

    //$pdf->writeHTML($html3, true, false, true, false, '');
     ob_end_clean();

    header("Content-type: application/pdf");
    header("Content-Disposition: inline; filename=\"".$_SESSION['DatabaseName'] . '_GRB_' . $_GET['grbno'] . '.pdf'."\";");

    $pdf->Output($_SESSION['DatabaseName'] . '_GRB_' . $_GET['grbno'] . '.pdf','I');

    $pdf->__destruct();
?>