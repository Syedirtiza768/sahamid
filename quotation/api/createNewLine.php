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

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];
	
	if(isInValidSalesCase($salescaseref, $orderno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();

	$SQL = 'INSERT INTO salesorderlinesip (orderno) VALUES ('.$orderno.')';
	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'SQL Query Error.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "select MAX(lineindex) as maxlineno from salesorderlinesip where orderno = ".$orderno.""; 		
	$result = mysqli_query($db, $SQL);
	$row = mysqli_fetch_assoc($result);
	
	$line_id = $row['maxlineno'];

	closeDBConnection($db);

	$response = [

		'status' => 'success',
		'data' => [
			'salescaseref'	=> $salescaseref,
			'orderno'		=> $orderno,
			'line_id' 		=> $line_id
		]

	];

	echo json_encode($response);
	return;

?>