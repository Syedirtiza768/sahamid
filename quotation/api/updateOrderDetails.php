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

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno'])
		|| !isset($_POST['name']) || !isset($_POST['value'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];
	$name = $_POST['name'];
	$value = $_POST['value'];

	$value = str_replace("'", "\'", $value);
	
	if(isInValidSalesCase($salescaseref, $orderno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();

	if($name == "rate_clause"){
		$SQL = "SELECT * FROM salesordersip WHERE orderno=$orderno";
		$res = mysqli_query($db, $SQL);
		$res = mysqli_fetch_assoc($res);

		if($res['rate_clause'] == "usd"){
			$SQL = "SELECT max(id) as id FROM exchange_rate";
			$res = mysqli_query($db, $SQL);
			$id = mysqli_fetch_assoc($res)['id'];

			$SQL = "SELECT * FROM exchange_rate WHERE id=$id";
			$res = mysqli_query($db, $SQL);
			$rates = mysqli_fetch_assoc($res);
			$rates = json_encode($rates);

			$SQL = "UPDATE `salesordersip` SET clause_rates='$rates' 
					WHERE orderno='$orderno'";
			mysqli_query($db, $SQL);
		}
	}

 	$SQL = "UPDATE `salesordersip` SET `".$name."`='".$value."' 
			WHERE orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Update Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$response['status'] = "success";
	$response['data'] = [

		'name'	=>	$name,
		'value'	=>	$value,

	];

	echo json_encode($response);
	return;	

?>