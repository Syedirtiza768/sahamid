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

	if(!isset($_POST['option']) || !isset($_POST['line'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$optionIndex = $_POST['option'];
	$lineIndex = $_POST['line'];

	$SQL = "DELETE FROM `salesorderoptionsip` 
			WHERE optionindex='".$optionIndex."' 
			AND lineno='".$lineIndex."'";

	$db = createDBConnection();

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Option Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "DELETE FROM `salesorderdetailsip` 
			WHERE orderlineno=".$lineIndex." 
			AND lineoptionno=".$optionIndex."";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Option Items Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$response = [
			'status' => 'success',
			'data' => [
				'line' => $lineIndex,
				'option' => $optionIndex,
			]
		];

	echo json_encode($response);
	return;	

?>