<?php 

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	$parchi = trim($_POST['parchino']);
	$terms = trim($_POST['terms']);

	if(!isset($parchi) || !isset($terms) || trim($parchi) == "" || trim($terms) == ""){

		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid Parms'

			]);
		return;

	}

	$SQL = "SELECT * FROM bazar_parchi WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Mess',

			]);
		return;

	}

	$SQL = "UPDATE bazar_parchi SET terms='".($terms)."' WHERE parchino='".$parchi."'";
	DB_query($SQL, $db);

 	echo json_encode([

			'status' => 'success',
			'message' => 'Updated Successfully',

		]);
	return;