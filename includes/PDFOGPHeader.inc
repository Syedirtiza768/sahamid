<?php
/* $Id: PDFStockLocTransferHeader.inc 6167 2013-07-31 21:38:51Z rchacon $*/

/*PDF page header for inventory check report */


//$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$html2= '<div align = "center" style = "padding-top:100px;">
<h1>Outwards Gate Pass</h1>
</div>
<div style = "border:1px; border-style:dotted;" >
<table>
<tr>
<td>Source Location</td>
<td>'.$TransferRow['locationname'].'</td>
<td>Document No.</td>
<td>'.$TransferRow['loccode'].'12/'.$TransferRow['despatchdate'].'/'.  $_GET['RequestNo'].'</td>
</tr>';
$html2.= '<tr>
<td>Store Manager</td>
<td>'.$TransferRow['storemanager'].'</td>
<td>Dispatch Date</td>
<td>'.ConvertSQLDateTime($TransferRow['despatchdate']).'</td>
</tr>';
$html2.= '<tr><td>Sales Person</td>
<td>'.$TransferRow['deliveredto'].'</td>
<td>Printing Date</td><td>'.Date($_SESSION['DefaultDateFormat']).'</td>
</tr>';
if($TransferRow['salescaseref'] != ""){
$html2.= '<tr><td>Salescaseref</td>
<td>'.$TransferRow['salescaseref'].'</td>
<td>Requested By</td>
<td>'.$TransferRow['requestedby'].'</td>
</tr>';
}
if($TransferRow['csv'] != ""){
$html2.= '<tr><td>CSV NO.</td>
<td style="width:180px">'.$TransferRow['csv'].'</td>
<td>Requested By</td>
<td>'.$TransferRow['requestedby'].'</td></tr>';
}
if($TransferRow['crv'] != ""){
$html2.= '<tr><td>CRV NO.</td>
<td style="width:180px">'.$TransferRow['crv'].'</td>
<td>Requested By</td>
<td>'.$TransferRow['requestedby'].'</td></tr>';
}
if($TransferRow['mpo'] != ""){
$html2.= '<tr><td>MPO NO.</td>
<td style="width:180px">'.$TransferRow['mpo'].'</td>
<td>Requested By</td>
<td>'.$TransferRow['requestedby'].'</td></tr>';
}
$html2.= '</table></div>
<div style = "height:520px;"></div>';



?>
