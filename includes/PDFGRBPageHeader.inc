<?php
/* $Id: PDFStockLocTransferHeader.inc 6167 2013-07-31 21:38:51Z rchacon $*/

/*PDF page header for inventory check report */



$img = glob($_SESSION['part_pics_dir'] . '/companylogos/' .$TransferRowS['dba'].'*');
$pdf->addJpegFromFile($img[0], 20, 750, 0, 60);



$html2= '<div align = "center"> <h1>'.$TransferRowS['dba'].'</h1><h3>'.$TransferRowS['companyaddress'].'</h3><h1 align="right">GRB</h1></div>';

//$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
$html2.= '<div align = "center" style = "padding-top:100px;"></div><div style = "border:1px; height:auto; width:auto; " >';
$html2.= '<table border="1"><tr><td>Customer Name</td><td>'.$TransferRow2['name'].'</td><td>Branch Code</td><td>'.$TransferRow2['branchcode'].'</td></tr>';

$html2.= '<tr><td>Customer Ref.</td><td>'.$TransferRow2['customerref'].'</td><td>GRB No#</td><td>'.$_GET['grbno'].' 
DC#'.$TransferRow2['dcno'].' </td></tr>';
$html2.= '<tr><td>PO Date.</td><td>'.(($TransferRow2['podate'] != "0000-00-00 00:00:00") ? date('d/m/Y',strtotime($TransferRow2['podate'])):"").'</td><td>Dispatch Through</td><td>'.$TransferRow2['dispatchthrough'].'</td></tr>';
$html2.= '<tr><td>Sales Case Ref</td><td>'.$TransferRow2['salescaseref'].'</td><td>Dispatch Date</td><td>'.ConvertSQLDateTime($TransferRow2['orddate']).'</td></tr>';
$html2.= '<tr><td>Sales Person</td><td>'.$TransferRowS['salesmanname'].'</td><td>Contact Person</td><td>'.$TransferRow2['contactperson'].'</td></tr>';
$html2.= '<tr><td>Sales Person Contact no</td><td>'.$TransferRowS['smantel'].'</td><td>Customer Contact no</td><td>'.$TransferRow2['contactphone'].'</td></tr>';
$html2.= '<tr><td>Sales Person Email</td><td>'.$TransferRowSalesperson['email'].'</td><td>Customer Email</td><td>'.$TransferRow2['contactemail'].'</td></tr>';

$html2.='<tr><td>GRB For</td><td>'.$TransferRow2['name'].'<br>'.$TransferRow2['address1'].'<br>'.$TransferRow2['address2'].'<br>'.$TransferRow2['address3'].'<br>'.$TransferRow2['address4'].'<br>'.$TransferRow2['address5'].'</td>';
//$html2.='<td>Deliver To</td><td>'.$TransferRow2['deliverto'].'<br>'.$TransferRow2['deladd1'].'<br>'.$TransferRow2['deladd2'].'<br>'.$TransferRow2['deladd3']'<br>'.$TransferRow2['deladd4'].'<br>'.$TransferRow2['deladd5'].'</td></tr>';
$html2.='<td>Billed To</td><td>'.$TransferRow2['deliverto'].'<br>'.$TransferRow2['deladd1'].'<br>'.$TransferRow2['deladd2'].'<br>'.$TransferRow2['deladd3'].'<br>'.$TransferRow2['deladd4'].'<br>'.$TransferRow2['deladd4'].'</td>';

$html2.='</tr></table></div><div style = "height:500px;"></div>';



$terms = $TransferRow['narrative'];
$gstclause = $TransferRow['gstclause'];
$salesperson = $TransferRow['salesperson'];
?>
