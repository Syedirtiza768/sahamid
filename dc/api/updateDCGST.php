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

	$salescaseref = $_POST['orderno'];
	$orderno = 'update';

	$SQL = "UPDATE dcs 
			SET GSTadd = '$orderno'
			WHERE orderno = '$salescaseref'";
	if(mysqli_query($db, $SQL)){
	$response = [
		'status' => 'success',
		'message' => 'Missing Parameters.'
	];
}
else {
    $response = [
		'status' => "Error: " . $SQL . "<br>" . mysqli_error($db),
		'message' => 'Missing Parameters.'
	];
  }

	echo json_encode($response);

	return;	