<?php

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['id']) || !isset($_POST['eta'])){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parms.'
			]);
		return;
	}

	$id = $_POST['id'];
	$eta = $_POST['eta'];

	$SQL = "SELECT * FROM procurement_document 
			WHERE canceled_date IS NULL 
			AND received_date IS NULL
			AND id=$id";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		echo json_encode([
				'status' => 'error',
				'message' => 'Shipment received or order canceled.'
			]);
		return;
	}

	$document = mysqli_fetch_assoc($res);

	if($document['eta_date']){
		$old = strtotime(date('Y-m-d'));
		$new = strtotime($eta);

		if($old > $new){
			echo json_encode([
					'status' => 'error',
					'message' => 'Cannot use past date for eta.'
				]);
			return;
		}

	}

	$SQL = "UPDATE procurement_document 
			SET eta_date='$eta'
			WHERE id=$id";
	if(DB_query($SQL, $db)){
		echo json_encode([
				'status' => 'success'
			]);
		return;
	}else{
		echo json_encode([
				'status' => 'error',
				'message' => 'Something went wrong.'
			]);
		return;
	}

