<?php 

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	$parchi = trim($_POST['parchi']);
	$item 	= trim($_POST['item']);
	$value 	= trim($_POST['value']);

	if(!isset($parchi) || !isset($item) || trim($parchi) == "" || trim($item) == "" ||
		!isset($value) || trim($value) == ""){

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

	$SQL = "SELECT * FROM bpitems WHERE id='".$item."' AND deleted_at IS NULL AND parchino='".$parchi."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Mess2',

			]);
		return;

	}

	$SQL = "UPDATE bpitems SET comments='".$value."' WHERE id='".$item."'";
	DB_query($SQL, $db);

	echo json_encode([

				'status' => 'success',
				'message' => 'Updated Successfully',

			]);
		return;
