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
	
	$db = createDBConnection();

	$SQL = "SELECT max(lineno) AS lineno FROM dclines 
			WHERE orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	$maxline = mysqli_fetch_assoc($result);

	if($maxline['lineno'] == null)
		$lineno = 0;
	else
		$lineno = $maxline['lineno'] + 1;

	$SQL = 'INSERT INTO dclines (orderno,lineno) 
			VALUES ('.$orderno.','.$lineno.')';

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'SQL Query Error.'
		];

		echo json_encode($response);
		return;	

	}

	$lineid = mysqli_insert_id($db);

	$SQL = "INSERT INTO `dcoptions`(`orderno`, `optionno`, `lineno`) 
			VALUES ('".$orderno."','0','".$lineno."')";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'SQL Query Error.'
		];

		echo json_encode($response);
		return;	

	}

	$optionid = mysqli_insert_id($db);

	closeDBConnection($db);

	$response = [

		'status' => 'success',
		'data' => [
			'salescaseref'	=> $salescaseref,
			'orderno'		=> $orderno,
			'line_id' 		=> $lineid,
			'option_id' 	=> $optionid,
		]

	];

	echo json_encode($response);
	return;

?>