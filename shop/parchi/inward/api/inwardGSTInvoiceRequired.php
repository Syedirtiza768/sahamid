<?php 

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	$parchi = trim($_POST['parchi']);
	$value 	= trim($_POST['value']);
	$type 	= trim($_POST['type']);

	if(!isset($parchi) || trim($parchi) == "" || !isset($value) || trim($value) == ""){

		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid Parms'

			]);
		return;

	}

	$SQL = "SELECT * FROM bazar_parchi WHERE parchino='".$parchi."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Mess',

			]);
		return;

	}

	if($type == "terms"){
		$SQL = "UPDATE bazar_parchi SET payment_terms='".$value."' WHERE parchino='".$parchi."'";
		DB_query($SQL, $db);
	}else{
		$SQL = "UPDATE bazar_parchi SET gstinvoice='".$value."' WHERE parchino='".$parchi."'";
		DB_query($SQL, $db);
	}

	echo json_encode([

			'status' => 'success',
			'message' => 'Updated',

		]);
	return;