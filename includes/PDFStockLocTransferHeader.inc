<?php
/* $Id: PDFStockLocTransferHeader.inc 6167 2013-07-31 21:38:51Z rchacon $*/

/*PDF page header for inventory check report */


//$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$html2= '<div align = "center" style = "padding-top:100px;"><h1>Stock Transfer Document</h1></div><div style = "border:1px; border-style:dotted;" ><table><tr><td>Source Location</td><td>'.$TransferRow['shiplocname'].'</td><td>Transfer No.</td><td>'.$_GET['TransferNo'].'</td></tr>';
$html2.= '<tr><td>Source User</td><td>'.$TransferRow['storemanager'].'</td><td>Shipping Date</td><td>'.ConvertSQLDateTime($TransferRow['shipdate']).'</td></tr>';
$html2.= '<tr><td>Destination Location</td><td>'.$TransferRow['reclocname'].'</td><td>Authorizer</td><td>'.$TransferRow['authorizer'].'</td></tr>';
$html2.= '<tr><td>Destination User</td><td>'.$TransferRow['salesperson'].'</td><td>Printing Date</td><td>'.Date($_SESSION['DefaultDateFormat']).'</td></tr></table></div><div style = "height:500px;"></div>';



?>
