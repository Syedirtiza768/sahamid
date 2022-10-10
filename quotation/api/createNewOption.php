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

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno']) || !isset($_POST['lineno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];
	$lineno = $_POST['lineno'];
	
	if(isInValidSalesCase($salescaseref, $orderno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	if(!lineExists($lineno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Line No Provided.'
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();

	$SQL = 'INSERT INTO salesorderoptionsip (orderno,lineno) VALUES ('.$orderno.','.$lineno.')';
	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'SQL Query Error.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "select MAX(optionindex) as maxoptionno from salesorderoptionsip where orderno = ".$orderno." AND lineno=".$lineno.""; 		
	$result = mysqli_query($db, $SQL);
	$row = mysqli_fetch_assoc($result);
	
	$option_id = $row['maxoptionno'];

	closeDBConnection($db);

	$response = [

		'status' => 'success',
		'data' => [
			'salescaseref'	=> $salescaseref,
			'orderno'		=> $orderno,
			'line_id' 		=> $lineno,
			'option_id' 	=> $option_id
		]

	];

	echo json_encode($response);
	return;

?>
