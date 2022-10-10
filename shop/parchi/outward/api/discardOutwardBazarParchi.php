<?php

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db,"discard_outward_slip")){ 
		echo json_encode([

				'status' => 'error',
				'message' => 'Permission Denied!'

			]);
		return;
	}

	$parchi = trim($_POST['parchi']);

	if(!isset($parchi) || trim($parchi) == ""){

		echo json_encode([

				'status' => 'error',
				'message' => 'Missing Parms'

			]);
		return;

	}

	$SQL = "SELECT * FROM bazar_parchi WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0 AND igp_created=0";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid Parms',

			]);
		return;

	}

	$SQL = "SELECT SUM(amount) as total FROM bpledger WHERE parchino='".$parchi."' GROUP BY parchino";
	$res = mysqli_query($db, $SQL);

	if(mysqli_fetch_assoc($res)['total'] != 0){

		echo json_encode([

				'status' => 'error',
				'message' => 'Cannot be discarded due to advance payment',

			]);
		return;

	}

	$SQL = "UPDATE bazar_parchi SET inprogress=0, discarded=1, 
					discarded_at='".date('Y-m-d H:i:s')."', 
					discarded_by='".$_SESSION['UserID']."',
					updated_at='".date('Y-m-d H:i:s')."'
					WHERE parchino='".$parchi."'";
	DB_query($SQL, $db);

	echo json_encode([

				'status' => 'success',
				'message' => 'Discarded Successfully!!!',

			]);
		return;