<?php
/* $Id: PDFStockLocTransferHeader.inc 6167 2013-07-31 21:38:51Z rchacon $*/

/*PDF page header for inventory check report */


//$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$html2= '<div align = "center" style = "padding-top:100px;"><h1>Inwards Gate Pass</h1></div><div style = "border:1px; border-style:dotted;" ><table><tr><td>Source Location</td><td>'.$TransferRow['locationname'].'</td><td>Document No.</td><td>'.$TransferRow['loccode'].'10/'.$TransferRow['despatchdate'].'/'.  $_GET['RequestNo'].'</td></tr>';
$html2.= '<tr><td>Store Manager</td><td>'.$TransferRow['storemanager'].'</td><td>Received Date</td><td>'.ConvertSQLDateTime($TransferRow['despatchdate']).'</td></tr>';
$html2.= '<tr><td>Received From</td><td>'.$TransferRow['receivedfrom'].(($parchino != "" ? "(".$parchino.")":"")).'</td><td>Printing Date</td><td>'.Date($_SESSION['DefaultDateFormat']).'</td></tr></table></div><div style = "height:500px;"></div>';



?>
