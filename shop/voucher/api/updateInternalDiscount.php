<?php

$AllowAnyone = true;
$PathPrefix='../../../';
include('../../../includes/session.inc');
include('../../../includes/SQL_CommonFunctions.inc');

if(!userHasPermission($db, 'update_quantity_shopsale')){

    echo json_encode([

        'status' 	=> 'error',
        'message' 	=> 'Permission Denied.'

    ]);
    return;

}

if(!isset($_POST['discount'])){

    echo json_encode([

        'status' 	=> 'error',
        'message' 	=> 'Missing Parms...'

    ]);
    return;

}

$itemID 	= $_POST['itemID'];
$quantity 	= $_POST['quantity'];
$discount 	= $_POST['discount'];




$SQL="SELECT lineno FROM shopsalesitems WHERE id=$itemID";

$lineno=mysqli_fetch_assoc(mysqli_query($db,$SQL))['lineno'];
$SQL = "UPDATE shopsalesitems 
			SET discountpercent = $discount
			WHERE id=$itemID";
DB_query($SQL, $db);


echo json_encode([

    'status' 	=> 'success'

]);
return;









?>