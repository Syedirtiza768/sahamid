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

	if(!isset($_POST['itemIndex'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$index = $_POST['itemIndex'];

	$SQL = "DELETE FROM `invoicedetails` WHERE invoicedetailsindex=".$index."";

	$db = createDBConnection();

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$response = [
			'status' => 'success',
			'data' => [
				'index' => $index
			]
		];

	echo json_encode($response);
	return;	

?>