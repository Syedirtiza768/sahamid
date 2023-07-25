<?php
$AllowAnyone=true;

	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
	print_r($_POST);
echo	$SQL = 'UPDATE suppliers SET ledgerstatus="'.$_POST['newValue'].'" WHERE supplierid = "'.$_POST['supplierId'].'"'; 
	
	$result = mysqli_query($db, $SQL);
	
	
	return;
?>
