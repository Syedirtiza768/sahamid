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

	if(!isset($_POST['line'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$lineIndex = $_POST['line'];

	$db = createDBConnection();

	$SQL = "DELETE FROM `salesorderlinesip` 
			WHERE lineindex='".$lineIndex."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Line Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "DELETE FROM `salesorderoptionsip` 
			WHERE lineno='".$lineIndex."'";

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
			WHERE orderlineno=".$lineIndex."";

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
			]
		];

	echo json_encode($response);
	return;	

?>