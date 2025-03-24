<?php

$dcNo = $_GET['DCNO'];

$PathPrefix = '../';

include('../includes/session.inc');
include('../includes/PDFStarter2.php');

$pdf->addInfo('Title', _('Delivery Chalan'));
$pdf->addInfo('Subject', _('Delivery Chalan') . ' # s-' . $dcNo);
$pdf->SetAutoPageBreak(TRUE, 15);

$SQL = "SELECT dcs.customerref,
				dcs.contactphone,
				dcs.contactemail,
				dcs.salescaseref,
				dcs.comments,
				dcs.orddate,
				dcs.deliverto,
				dcs.services,
				dcs.deladd1,
				dcs.deladd2,
				dcs.deladd3,
				dcs.deladd4,
				dcs.deladd5,
				dcs.advance,
				dcs.delivery,
				dcs.commisioning,
				dcs.after,
				dcs.gst,
				dcs.afterdays,
				dcs.services,
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
				dcs.debtorno,
				locations.taxprovinceid,
				locations.locationname,
				currencies.decimalplaces AS currdecimalplaces
			FROM dcs INNER JOIN debtorsmaster
			ON dcs.debtorno=debtorsmaster.debtorno
			INNER JOIN shippers
			ON dcs.shipvia=shippers.shipper_id
			INNER JOIN locations
			ON dcs.fromstkloc=locations.loccode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE dcs.quotation=1
			AND dcs.orderno='" . $dcNo . "'";

$dc = DB_query($SQL, $db);

if (DB_num_rows($dc) == 0) {

	echo json_encode([

		'status' => 'error',
		'state' => 'DCH!DAYRMAUK8765'

	]);
	return;
}

$dc = mysqli_fetch_assoc($dc);

$SQL = "SELECT * from dcs 
			INNER JOIN debtorsmaster ON dcs.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch ON debtorsmaster.debtorno = custbranch.debtorno
			INNER JOIN dba ON dba.companyname = debtorsmaster.dba
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			WHERE orderno = '" . $dcNo . "'";

$info = DB_query($SQL, $db);

if (DB_num_rows($info) == 0) {

	echo json_encode([

		'status' => 'error',
		'state' => 'DCH!DZYAHWE2423'

	]);
	return;
}

$info = mysqli_fetch_assoc($info);

$SQL = "SELECT * from salesman 
			INNER JOIN www_users ON salesman.salesmanname = www_users.realname
			WHERE salesman.salesmanname = '" . $info['salesmanname'] . "'";

$salesman = mysqli_fetch_assoc(mysqli_query($db, $SQL));

$img = glob('../' . $_SESSION['part_pics_dir'] . '/companylogos/' . $info['dba'] . '*');
$pageWidth = 612; // Page width in points (standard A4 width)
$imageWidth = 280; // Set your preferred fixed image width
$imageHeight = 120;

// Calculate X position for horizontal centering
$xPosition = ($pageWidth - $imageWidth) / 2;
$yPosition = 750-40;

$pdf->addJpegFromFile($img[0], $xPosition, $yPosition, $imageWidth, $imageHeight);

$SQL = "SELECT * FROM dcoptions WHERE orderno='" . $dcNo . "'";
$options = mysqli_query($db, $SQL);

$html = '<br><br><br><br><br><br><br><div align = "center"><h3><br><br>' . $info['companyaddress'] . '</h3><h1 align="right">Delivery Challan</h1></div>';
/*
if(isset($_GET['orignal'])){
    $html .= '<h1 align="right" style="margin-bottom:0px">Orignal</h1>';
}else{
    $html .= '<h1 align="right" style="margin-bottom:0px">Duplicate</h1>';
}
*/
$html .= '</div>
			<div style = "border:1px; height:auto; width:auto; margin-top:0px">
				<table border="1">
					<tr>
						<td>Customer Name</td>
						<td>' . $dc['name'] . '</td>
						<td>Branch Code</td>
						<td>' . $dc['branchcode'] . '</td>
					</tr>
					<tr>
						<td>Customer Ref.</td>
						<td>' . $dc['customerref'] . '</td>
						<td>Delivery Challan No#</td>
						<td>' . $dcNo . '</td>
					</tr>
					<tr>
						<td>Sales Case Ref</td>
						<td>' . $dc['salescaseref'] . '</td>
						<td>Dispatch Date</td>
						<td>' . ConvertSQLDateTime($dc['orddate']) . '</td>
					</tr>
					<tr>
						<td>Sales Person</td>
						<td>' . $info['salesmanname'] . '</td>
						<td>Printing Date</td>
						<td>' . Date($_SESSION['DefaultDateFormat']) . '</td>
					</tr>
					<tr>
						<td>Sales Person Contact no</td>
						<td>' . $info['smantel'] . '</td>
						<td>Sales Person Email</td>
						<td>' . $salesman['email'] . '</td>
					</tr>
					<tr>
						<td>Customer Contact no</td>
						<td>' . $dc['contactphone'] . '</td>
						<td>Customer Email</td>
						<td>' . $dc['contactemail'] . '</td>
					</tr>
					<tr>
						<td>Delivery Challan For</td>
						<td>' . $dc['name'] . '<br>'
	. $dc['address1'] . '<br>'
	. $dc['address2'] . '<br>'
	. $dc['address3'] . '<br>'
	. $dc['address4'] . '<br>'
	. $dc['address5'] .
	'</td>
						<td>Bill To</td>
						<td>'
	. $dc['deliverto'] . '<br>'
	. $dc['deladd1'] . '<br>'
	. $dc['deladd2'] . '<br>'
	. $dc['deladd3'] . '<br>'
	. $dc['deladd4'] . '<br>'
	. $dc['deladd4'] .
	'</td>
					</tr>
				</table>
			</div>
			<div style = "height:20px;"></div>';


if (mysqli_num_rows($options) <= 0) {

	$html .= '<div style = "text-align:center">No Option Found!!!</div>';
} else {

	if (!isset($_GET['withoutPrice'])) {

		$html .= '
					<div style="padding:10px;">
						<table nobr="true" border = "1" width = "100%">
							<tr>
								<th width = "10%" align="center"><b>Serial No. </b>.</th>
								<th width = "50%" align="center"><b> Description </b></th>
								<th width = "10%"align="center"> <b>Quantity</b></th>
								<th width = "15%" align="center"> <b>Unit Rate</b></th>
								<th width = "15%" align="center"><b>Total</b></th>
							</tr>';
	} else {

		$html .= '
					<div style="padding:10px;">
						<table nobr="true" border = "1" width = "100%">
							<tr>
								<th width = "10%" align="center"><b>Serial No. </b>.</th>
								<th width = "80%" align="center"><b> Description </b></th>
								<th width = "10%"align="center"> <b>Quantity</b></th>
							</tr>';
	}
}

$index = 1;
$total = 0;
while ($option = mysqli_fetch_assoc($options)) {

	$option['optiontext'] = str_replace("</p>", "", $option['optiontext']);
	$option['optiontext'] = str_replace("<p>", "", $option['optiontext']);
	$option['optiontext'] = str_replace("</div>", "", $option['optiontext']);
	$option['optiontext'] = str_replace("<div>", "", $option['optiontext']);

	$subTotal = round($option['quantity'] * $option['optprice']);

	if (!isset($_GET['withoutPrice'])) {

		$html .= '<tr>
						<td width = "10%" align="center"><h3>' . ($index) . '</h3></td>
						<td>' . html_entity_decode($option['optiontext']) . '</td>
						<td width = "10%" align="center">' . $option['quantity'] . '</td>
						<td width = "15%" align="center">' . locale_number_format($option['optprice'], 2) . '</td>
						<td width = "15%" align="center">' . locale_number_format($subTotal, 2) . '</td>
					</tr>';
	} else {

		$html .= '<tr>
						<td width = "10%" align="center"><h3>' . ($index) . '</h3></td>
						<td>' . html_entity_decode($option['optiontext']) . '</td>
						<td width = "10%" align="center">' . $option['quantity'] . '</td>
					</tr>';
	}

	$index++;

	$total += $subTotal;
}

if (isOverCreditLimit($db, $dc['debtorno'], round($total))) {
	echo "This Client Has Exceded Credit Limit!!!";
	return;
}

$exclusive = ($dc['GSTAdd'] == 'exclusive');
$services = ($dc['services'] == 1);

$html .= '</table>
			</div>';

if (!isset($_GET['withoutPrice'])) {

	$html .= '<div align="right">
					<table border="1">';

	if ($dc['GSTAdd'] == "") {

		$html .= 	'<tr>
							<td><b>Grand Total </b></td>
							<td>' . locale_number_format(round($total), 2) . '</td>
						</tr>';
	} else {

		if ($dc['GSTAdd'] == 'exclusive' && strtotime($dc['quotedate']) < strtotime('2023-02-14')) {
			$html .= 	'<tr>
							<td><b>' . (($exclusive) ? 'SUBTOTAL' : 'Grand Total inclusive of 17% GST') . '</b></td>
							<td>' . locale_number_format(round($total), 2) . '</td>
						</tr>';
			if ($services) {
				$html .= '<tr>
								<td><b>16% GST</b></td>
								<td>' . locale_number_format($total * 0.16, 2) . '</td>
							</tr>
							<tr>
								<td><b>Grand Total</b></td>
								<td>' . (locale_number_format(round(($total * 1.16)), 2)) . '</td>
							</tr>';
			} else {
				$html .= '<tr>
								<td><b>17% GST</b></td>
								<td>' . locale_number_format($total * 0.17, 2) . '</td>
							</tr>
							<tr>
								<td><b>Grand Total</b></td>
								<td>' . (locale_number_format(round(($total * 1.17)), 2)) . '</td>
							</tr>';
			}
		} elseif ($dc['GSTAdd'] == 'exclusive' && strtotime($dc['quotedate']) >= strtotime('2023-02-14')) {
			$html .= 	'<tr>
							<td><b>' . (($exclusive) ? 'SUBTOTAL' : 'Grand Total inclusive of 18% GST') . '</b></td>
							<td>' . locale_number_format(round($total), 2) . '</td>
						</tr>';
			if ($services) {
				$html .= '<tr>
								<td><b>16% GST</b></td>
								<td>' . locale_number_format($total * 0.16, 2) . '</td>
							</tr>
							<tr>
								<td><b>Grand Total</b></td>
								<td>' . (locale_number_format(round(($total * 1.16)), 2)) . '</td>
							</tr>';
			} else {
				$html .= '<tr>
								<td><b>18% GST</b></td>
								<td>' . locale_number_format($total * 0.18, 2) . '</td>
							</tr>
							<tr>
								<td><b>Grand Total</b></td>
								<td>' . (locale_number_format(round(($total * 1.18)), 2)) . '</td>
							</tr>';
			}
		} elseif ($dc['GSTAdd'] == 'inclusive') {
			$html .= 	'<tr>
							<td><b>' . (($exclusive) ? 'SUBTOTAL' : 'Grand Total inclusive of 18% GST') . '</b></td>
							<td>' . locale_number_format(round($total), 2) . '</td>
						</tr>';
		} elseif ($dc['GSTAdd'] == 'update' && strtotime($dc['quotedate']) < strtotime('2023-02-14')) {
			$html .= 	'<tr>
							<td><b>' . (($exclusive) ? 'SUBTOTAL' : 'Grand Total inclusive of 18% GST') . '</b></td>
							<td>' . locale_number_format(round($total), 2) . '</td>
						</tr>';
			if ($services) {
				$html .= '<tr>
								<td><b>16% GST</b></td>
								<td>' . locale_number_format($total * 0.16, 2) . '</td>
							</tr>
							<tr>
								<td><b>Grand Total</b></td>
								<td>' . (locale_number_format(round(($total * 1.16)), 2)) . '</td>
							</tr>';
			} else {
				$html .= '<tr>
								<td><b>18% GST</b></td>
								<td>' . locale_number_format($total * 0.18, 2) . '</td>
							</tr>
							<tr>
								<td><b>Grand Total</b></td>
								<td>' . (locale_number_format(round(($total * 1.18)), 2)) . '</td>
							</tr>';
			}
		} elseif ($dc['GSTAdd'] == 'update' && strtotime($dc['quotedate']) >= strtotime('2023-02-14')) {
			$html .= 	'<tr>
							<td><b>Grand Total </b></td>
							<td>' . locale_number_format(round($total), 2) . '</td>
						</tr>';
		}
	}

	$html .= '</table></div>';
}

$html .= '<b>Terms and Conditions: </b><br>
				We do not undertake any risk of breakage or loss of goods<br>
				in transit when once the delivery has been effected<br><br>
				<b>Payment Terms:</b><br>';

if ($dc['advance'] > 0)
	$html .= 'Advance ' . $dc['advance'] . '%<br>';
if ($dc['delivery'] > 0)
	$html .= 'On Delivery ' . $dc['delivery'] . '%<br>';
if ($dc['commisioning'] > 0)
	$html .= 'On Commisioning ' . $dc['commisioning'] . '%<br>';
if ($dc['after'] > 0)
	$html .= $dc['after'] . '% After ' . $dc['afterdays'] . 'days';

$html .= '<br>' . $dc['gst'] . '<br>';
$html .= 'This is a system generated document and does not require any signatures or stamp';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->AddPage();
$pdf->Image('../duplicate.jpg', 450, 50, 100, 96, 'jpg', '', 'right', false, 300, '', false, false, 0);
$pdf->writeHTML($html, true, false, true, false, '');
ob_end_clean();
$pdf->OutputD($_SESSION['DatabaseName'] . '_DC_' . $dcNo . '.pdf');
$pdf->__destruct();
