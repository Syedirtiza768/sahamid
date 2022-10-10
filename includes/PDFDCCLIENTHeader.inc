<?php
/* $Id: PDFStockLocTransferHeader.inc 6167 2013-07-31 21:38:51Z rchacon $*/

/*PDF page header for inventory check report */


//$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$html2= '<br><br><div align = "center" style = "padding-top:100px;"><h1>'.$TransferRow['dba'].'</h1><h3>'.$TransferRow['companyaddress'].'</h3><h2>'.$TransferRow['dctype'].'</h2><h2>M/s:	'.$TransferRow['deliverto'].'</h2></div><div style = "border:1px; height:auto; width:auto; " ><table><tr><td>TR/RR/Courier/Pickup No#</td><td>'.$TransferRow['ref'].'</td><td>Document No#</td><td>'.$TransferRow['loccode'].'11/'.$TransferRow['despatchdate'].'/'.  $_GET['RequestNo'].'</td></tr>';
$html2.= '<tr><td>P.O. #</td><td>'.$TransferRow['po'].'</td><td>Dispatch Date</td><td>'.ConvertSQLDateTime($TransferRow['despatchdate']).'</td></tr>';
$html2.= '<tr><td>Sales Person</td><td>'.$TransferRow['salesperson'].'</td><td>Printing Date</td><td>'.Date($_SESSION['DefaultDateFormat']).'</td></tr></table></div><div style = "height:500px;"></div>';


$terms = $TransferRow['narrative'];
$gstclause = $TransferRow['gstclause'];
$salesperson = $TransferRow['salesperson'];
?>
