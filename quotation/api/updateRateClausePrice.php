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

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno'])) {

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];

	$SQL = "SELECT max(id) as id FROM exchange_rate";
	$res = mysqli_query($db, $SQL);
	$id = mysqli_fetch_assoc($res)['id'];

	$SQL = "SELECT * FROM exchange_rate WHERE id=$id";
	$res = mysqli_query($db, $SQL);
	$rates = mysqli_fetch_assoc($res);

	$rates = json_encode($rates);

	$SQL = "UPDATE salesordersip 
			SET clause_rates = '$rates'
			WHERE orderno = $orderno";
	mysqli_query($db, $SQL);

	$response = [
		'status' => 'success',
		'message' => 'Missing Parameters.'
	];

	echo json_encode($response);
	return;	