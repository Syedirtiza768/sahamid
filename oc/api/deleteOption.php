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

	if(!isset($_POST['option']) || !isset($_POST['line']) || !isset($_POST['orderno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$optionIndex = $_POST['option'];
	$lineIndex 	 = $_POST['line'];
	$orderno 	 = $_POST['orderno'];

	$db = createDBConnection();

	//Selecting Option
	$SQL = "SELECT * FROM `ocoptions` 
			WHERE optionindex='".$optionIndex."' 
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Option Fetch Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$optionDetails = mysqli_fetch_assoc($result);

	//Deleting Option
	$SQL = "DELETE FROM `ocoptions` 
			WHERE optionindex='".$optionIndex."' 
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

	//Deleting items under option
	$SQL = "DELETE FROM `ocdetails` 
			WHERE orderlineno=".$optionDetails['lineno']." 
			AND lineoptionno=".$optionDetails['optionno']."
			AND orderno=".$orderno."";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Option Items Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$lineDeleted = 0;

	//Time to reorder the remaining options if any, else delete the line 
	$SQL = "SELECT * FROM ocoptions
			WHERE orderno='".$orderno."'
			AND lineno='".$optionDetails['lineno']."'";

	$result = mysqli_query($db, $SQL);

	//No Options remaining under given line
	if(mysqli_num_rows($result) == 0){

		//Lets delete the line
		$SQL = "DELETE FROM oclines 
				WHERE orderno='".$orderno."'
				AND lineindex='".$lineIndex."'";

		$result = mysqli_query($db, $SQL);

		if(!$result){

			$response = [
				'status' => 'error',
				'message' => 'Apparently line delete failed.'
			];

			echo json_encode($response);
			return;	

		}

		reorderlines($orderno, $db);

		$lineDeleted = 1;

	}else{	

		// options exist
		reorderoptions($orderno, $optionDetails['lineno'], $db);

	}

	$response = [
			'status' => 'success',
			'data' => [
				'line' => $lineIndex,
				'option' => $optionIndex,
				'linedeleted' => $lineDeleted,
			]
		];

	echo json_encode($response);
	return;	

?>