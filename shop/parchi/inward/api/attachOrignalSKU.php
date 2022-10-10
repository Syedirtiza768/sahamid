<?php

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	$itemIndex 	= $_POST['itemIndex'];
	$stockid 	= $_POST['stockid'];

	if(!isset($itemIndex) || !isset($stockid) || $itemIndex == "" || $itemIndex == ""){

		echo json_encode([
				'status' => 'error',
				'message' => 'missing parms'
			]);
		return;

	}

	$SQL = "SELECT * FROM bpitems WHERE id='".$itemIndex."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([
				'status' => 'error',
				'message' => 'But How?'
			]);
		return;

	}

	$parchiNo = mysqli_fetch_assoc($res)['parchino'];

	$SQL = "SELECT * FROM bpitems WHERE parchino = '$parchino' AND stockid = '$stockid'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) > 0){

		echo json_encode([
				'status' => 'error',
				'message' => 'Item Already Exists In this document.'
			]);
		return;

	}

	$SQL = "UPDATE bpitems SET stockid='".$stockid."'
			WHERE id='".$itemIndex."'";
	DB_query($SQL, $db);

	echo json_encode([
			'status' => 'success',
			'message' => 'Item Attached Successfully'
		]);
	return;
