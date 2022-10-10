<?php
$AllowAnyone = true;
$PathPrefix='../../';
include('../../includes/session.inc');
include('../../includes/SQL_CommonFunctions.inc');
$info=pathinfo($_POST['basepath'])['dirname'];
$filename =  basename($_POST['basepath'],'.'."pdf");

if (strpos($filename, "ourierSlip")!=False) {
    $attribute1 = "courierslipdate";
    $attribute2 = "courierslipdeleted";
}
if (strpos($filename, "urchaseOrder")!=False) {
    $attribute1 = "podate";
    $attribute2 = "podeleted";
}
if (strpos($filename, "nvoice")!=False) {
    $attribute1 = "invoicedate";
    $attribute2 = "invoicedeleted";
}
if (strpos($filename, "RB")!=False) {
    $attribute1 = "grbdate";
    $attribute2 = "grbdeleted";
}
$oldname='../'.$info.'/'.$filename.'.pdf';
$newname='../'.$info.'/'.$filename.'deleted.pdf';

rename($oldname,$newname);




    $SQL = "UPDATE dcs SET $attribute1='0000-00-00 00:00:00' WHERE orderno=".$_POST['orderno'];
    DB_Query($SQL,$db);
    $SQL = "UPDATE dcs SET $attribute2=1 WHERE orderno=".$_POST['orderno'];
    mysqli_query($db,$SQL);

$arr = array('status' => "success");
echo json_encode($arr);




?>