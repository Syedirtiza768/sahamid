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
		|| !isset($_POST['name']) || !isset($_POST['value'])
		|| !isset($_POST['option'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];
	$option = $_POST['option'];
	$name = $_POST['name'];
	$value = $_POST['value'];

	$value = str_replace("'", "\'", $value);

	if(!($name == "stockstatus" || $name == "quantity")){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.',
			'name'	=> $name
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();

	$SQL = "UPDATE `ocoptions` SET `".$name."`='".$value."' 
			WHERE orderno='".$orderno."'
			AND optionindex='".$option."'";

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