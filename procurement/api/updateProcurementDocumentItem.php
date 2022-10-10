<?php

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	include('stages.php');

	if(!isset($_POST['id']) || !isset($_POST['stockid']) || !isset($_POST['name']) || !isset($_POST['value'])){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parms.'
			]);
		return;
	}

	$id 		= $_POST['id'];
	$stockid 	= $_POST['stockid'];
	$name 		= $_POST['name'];
	$value 		= $_POST['value'];

	if($name != "client_required" && $name != "safety_inventory" && $name != "stock" && $name != "quantity" && $name != "price"){
		echo json_encode([
				'status' => 'error',
				'message' => 'Invalid Parms.'
			]);
		return;
	}

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

	$SQL = "SELECT * FROM procurement_document_details 
			WHERE stockid='$stockid'
			AND pdid=$id";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){
		echo json_encode([
				'status' => 'error',
				'message' => 'Item Not Found.'
			]);
		return;
	}

	$SQL = "UPDATE procurement_document_details
			SET $name='$value'
			WHERE pdid=$id
			AND stockid='$stockid'";
	DB_query($SQL, $db);

	if($name == "client_required" || $name == "safety_inventory" || $name == "stock"){
		$SQL = "UPDATE procurement_document_details
			SET quantity=(client_required+safety_inventory+stock)
			WHERE pdid=$id
			AND stockid='$stockid'";
		DB_query($SQL, $db);
	}

	echo json_encode([
			'status' 	=> 'success',
			'message' 	=> 'Successfully Deleted.'
		]);