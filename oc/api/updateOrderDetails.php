<?php

	include('../misc.php');

	$response = [];
	
	if(!validHeaders()){

		$response = [
			'status' => 'error',
			'message' => 'Refresh or ReLogin Required.'
		];

		echo json_encode($response);
		return;		

	}

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno'])
		|| !isset($_POST['name']) || !isset($_POST['value'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];
	$name = $_POST['name'];
	$value = $_POST['value'];

	$value = str_replace("'", "\'", $value);

	$db = createDBConnection();

	$SQL = "UPDATE `ocs` SET `".$name."`='".$value."' 
			WHERE orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Update Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$response['status'] = "success";
	$response['data'] = [

		'name'	=>	$name,
		'value'	=>	$value,

	];

	echo json_encode($response);
	return;	

?>