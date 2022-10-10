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

	if(!isset($_POST['line']) || !isset($_POST['orderno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$lineIndex = $_POST['line'];
	$orderno   = $_POST['orderno'];

	$db = createDBConnection();

	$SQL = "SELECT * FROM `oclines` 
			WHERE lineindex='".$lineIndex."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Line Fetch failed.'
		];

		echo json_encode($response);
		return;	

	}

	$lineDetails = mysqli_fetch_assoc($result);

	$SQL = "DELETE FROM `oclines` 
			WHERE lineindex='".$lineIndex."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Line Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "DELETE FROM `ocoptions` 
			WHERE lineno='".$lineDetails['lineno']."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Option Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "DELETE FROM `ocdetails` 
			WHERE orderlineno='".$lineDetails['lineno']."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Option Items Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	reorderlines($orderno, $db);

	$response = [
			'status' => 'success',
			'data' => [
				'line' => $lineIndex,
			]
		];

	echo json_encode($response);
	return;	

?>