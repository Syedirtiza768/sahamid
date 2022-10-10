<?php

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	include('stages.php');

	if(!isset($_POST['id']) || !isset($_POST['stockid'])){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parms.'
			]);
		return;
	}

	$id 		= $_POST['id'];
	$stockid 	= $_POST['stockid'];

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

	$SQL = "DELETE FROM procurement_document_details WHERE pdid=$id AND stockid='$stockid'";
	DB_query($SQL, $db);

	echo json_encode([
			'status' 	=> 'success',
			'message' 	=> 'Successfully Deleted.'
		]);

