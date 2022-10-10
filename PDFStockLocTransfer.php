<?php

/* $Id: PDFStockLocTransfer.php 6310 2013-08-29 10:42:50Z daintree $*/
include('includes/session.inc');
$Title = _('Stock Location Transfer Docket Error');

include('includes/PDFStarter.php');
$pdf->SetFont('helvetica', '', 10, '', true);

if (isset($_POST['TransferNo'])) {
	$_GET['TransferNo']=$_POST['TransferNo'];
}

if (!isset($_GET['TransferNo'])){

	include ('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . ' ' . _('Reprint transfer docket') . '</p><br />';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>
			<tr>
				<td>' . _('Transfer docket to reprint') . '</td>
				<td><input type="text" class="number" size="10" name="TransferNo" /></td>
			</tr>
		</table>';
	echo '<br />
          <div class="centre">
			<input type="submit" name="Print" value="' . _('Print') .'" />
          </div>';
    echo '</div>
          </form>';
	include ('includes/footer.inc');
	exit;
}

$pdf->addInfo('Title', _('Inventory Location Transfer BOL') );
$pdf->addInfo('Subject', _('Inventory Location Transfer BOL') . ' # ' . $_GET['TransferNo']);
$FontSize=10;
$PageNumber=1;
$line_height=30;
$pdf->SetFont('helvetica', '', 10, '', true);
$ErrMsg = _('An error occurred retrieving the items on the transfer'). '.' . '<p>' .  _('This page must be called with a location transfer reference number').'.';
$DbgMsg = _('The SQL that failed while retrieving the items on the transfer was');
$sql = "SELECT stockrequestitems.dispatchid as reference,
			   stockrequestitems.stockid,
			   stockmaster.description,
			   stockmaster.mnfCode,
			   stockmaster.mnfpno,
			   manufacturers.manufacturers_name,
			    stockmaster.brand,
				stockrequest.salesperson,
				stockrequest.authorizer,
				stockrequest.storemanager,
			   stockrequestitems.qtydelivered as qtydelivered,
			   stockrequestitems.qtyreceived asrecqty,
			   stockrequestitems.shipdate,
			   stockrequest.shiploc as shiploc,
			   locations.locationname as shiplocname,
			   stockrequest.recloc as recloc,
			   locationsrec.locationname as reclocname,
			   stockmaster.decimalplaces
		FROM stockmaster inner join manufacturers on stockmaster.brand= manufacturers.manufacturers_id
		INNER JOIN stockrequestitems ON stockrequestitems.stockid=stockmaster.stockid
		
		INNER JOIN stockrequest ON stockrequest.dispatchid=stockrequestitems.dispatchid
		INNER JOIN locations ON stockrequest.shiploc=locations.loccode
		INNER JOIN locations AS locationsrec ON stockrequest.recloc = locationsrec.loccode
		WHERE stockrequestitems.dispatchid='" . $_GET['TransferNo'] . "'";

$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

If (DB_num_rows($result)==0){


	include ('includes/header.inc');
	prnMsg(_('The transfer reference selected does not appear to be set up') . ' - ' . _('enter the items to be transferred first'),'error');
	include ('includes/footer.inc');
	exit;
}

$TransferRow = DB_fetch_array($result);

include ('includes/PDFStockLocTransferHeader.inc');
$line  =0;
$html2.= '<br><br><hr><br><div style="padding:10px;"><table border = "1" width = "100%"><tr align = "center"><td width = "5%">'.'Sr#'.'</td>';
$html2.= '<td width = "10%" align = "center">'.'Model No'.'</td>';
$html2.= '<td width = "15%" align = "center">'.'Part No'.'</td>';
$html2.= '<td width = "20%" align = "center">'.'Brand'.'</td>';
$html2.= '<td width = "40%">'.'Description'.'</td>';
$html2.= '<td width = "5%" align = "center">'.'Ship Qty'.'</td>';
$html2.= '<td width = "5%" align = "center">'.'Recvd. Qty'.'</td></tr>';

do{
	$line++;
$html2.= '<tr><td width = "5%" align = "center">'.$line.'</td>';
$html2.= '<td width = "10%" align = "center"><center>'.$TransferRow['mnfCode'].'</center></td>';
$html2.= '<td width = "15%" align = "center"><center>'.$TransferRow['mnfpno'].'</center></td>';
$html2.= '<td width = "20%" align = "center"><center>'.$TransferRow['manufacturers_name'].'</center></td>';
$html2.= '<td width = "40%">'.$TransferRow['description'].'</td>';
$html2.= '<td width = "5%"align = "center"><center>'.locale_number_format($TransferRow['qtydelivered'],$TransferRow['decimalplaces']).'</center></td>';
$html2.= '<td width = "5%">'.''.'</td></tr>';

/*
$line_height=30;
$FontSize=10;

do {
$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 100, $FontSize,$TransferRow['mnfCode'], 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+80, $YPos, 250, $FontSize, $TransferRow['mnfpno'], 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+160, $YPos, 120, $FontSize, $TransferRow['manufacturers_name'], 'left');
$LeftOvers = $pdf->addTextWrap($Left_Margin+250, $YPos, 150, $FontSize, $TransferRow['description'], 'left');
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-100-70, $YPos, 100, $FontSize,locale_number_format($TransferRow['qtydelivered'],$TransferRow['decimalplaces']), 'right');
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-100, $YPos, 100, $FontSize,'', 'right');
*/
/*
	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos, 100, $FontSize, $TransferRow['stockid'], 'left');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+100, $YPos, 250, $FontSize, $TransferRow['description'], 'left');
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-100-100, $YPos, 100, $FontSize, locale_number_format($TransferRow['qtydelivered'],$TransferRow['decimalplaces']), 'right');
//	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-100, $YPos, 100, $FontSize, locale_number_format($TransferRow['stockrequest.dispatchid'],$TransferRow['decimalplaces']), 'right');

	$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

	$YPos -= $line_height;

	if ($YPos < $Bottom_Margin + $line_height) {
		$PageNumber++;
		include('includes/PDFStockLocTransferHeader.inc');
	}
*/
} while ($TransferRow = DB_fetch_array($result));
$html2.='</table></div><div style = "height:500px;"></div>';
$pdf->writeHTML($html2, true, false, true, false, '');
$html3 = '<div><table width = "100%" height = "auto">
<tr><td><b>Sender:</b></td><td></td><td><b>Receiver:</b></td><td></td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Signatures:</b></td><td>______________________</td><td><b>Signatures:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Name:</b></td><td>______________________</td><td><b>Name:</b></td><td>______________________</td></tr>
<tr><td><br></td><td></td><td></td><td></td></tr>

<tr><td><b>Designation:</b></td><td>______________________</td><td><b>Designation:</b></td><td>______________________</td></tr>

</table></div>';

$pdf->writeHTML($html3, true, false, true, false, '');

$pdf->OutputD($_SESSION['DatabaseName'] . '_StockLocTrfShpmnt_' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
?>