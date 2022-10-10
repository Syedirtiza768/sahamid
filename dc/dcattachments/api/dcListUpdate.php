<?php
    $AllowAnyone=true;
	$PathPrefix = "../../../";
	include("../../../includes/session.inc");
	include('../../../includes/SQL_CommonFunctions.inc');
    if(!userHasPermission($db,"book_voucher")){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    $orderno=$_POST['orderno'];
    $name=$_POST['name'];
    $value=$_POST['value'];
    $SQL = "UPDATE `voucher` SET `".$name."`='".$value."' 
                WHERE id='".$orderno."'";
    //$SQL="UPDATE debtorsmaster SET dueDays = '$dueDays' WHERE debtorno='$debtorno'";
    mysqli_query($db,$SQL);

    echo "$SQL";
    //echo "$debtorno --- $dueDays";
