<?php
	$AllowAnyone = true;
	$PathPrefix='../../';
	include_once('../../includes/session.inc');
	include_once('../../includes/SQL_CommonFunctions.inc');

	$client	= $_POST['key'];
	$SQL="SELECT name,dba FROM debtorsmaster WHERE debtorno='$client'";



	$response=mysqli_fetch_assoc(mysqli_query($db,$SQL));

	$fres = [];
	$fres['status'] = "success";
	$fres['data'] = $response;

	$eresponse = json_encode($fres);

	echo $eresponse;

	return;













?>