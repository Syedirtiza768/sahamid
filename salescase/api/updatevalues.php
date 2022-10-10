<?php

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$salescaseref = $_POST['salescaseref'];
	$name = $_POST['name'];
	$value = $_POST['value'];

	$db = createDBConnection();

	$SQL = "SELECT * FROM salescase WHERE salescaseref='".$salescaseref."'";
	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) == 0 
		|| mysqli_num_rows($result) > 1 
		|| mysqli_fetch_assoc($result)['closed'] == 1
		|| !($name == "enquiryvalue" || $name == "salescasedescription")){

		$response = [];
		$response['status'] = "error";
		$response['message'] = "STOP TRYING TO FUCK WITH THE SYSTEM";
		echo json_encode($response);
		return;
	
	}

	$SQL = "UPDATE salescase SET ".$name." = '".$value."' WHERE salescaseref='".$salescaseref."'";
	$result = mysqli_query($db, $SQL);

	$response = [];
	$response['status'] = "success";
	echo json_encode($response);
	return;