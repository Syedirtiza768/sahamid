<?php
	$AllowAnyone = true;
	$PathPrefix='../../';
	include_once('../../includes/session.inc');
	include_once('../../includes/SQL_CommonFunctions.inc');

	$brand 	= $_POST['brand'];
	$SQL="SELECT manufacturers_name FROM manufacturers WHERE manufaturers_id='$brand'";



	$response=mysqli_fetch_assoc(mysqli_query($db,$SQL))['manufacturers_name'];

	$fres = [];
	$fres['status'] = "success";
	$fres['data'] = $response;

	$eresponse = json_encode($fres);

	echo $eresponse;

	return;













?>