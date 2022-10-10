<?php

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	include('stages.php');

	if(!isset($_POST['id']) || !isset($_POST['status'])){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parms.'
			]);
		return;
	}

	$id 	= $_POST['id'];
	$status = $_POST['status'];
	$reason = $_POST['reason'];

	$SQL = "SELECT * FROM procurement_document
			WHERE id=$id";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		echo json_encode([
				'status' => 'error',
				'message' => 'Document Not Found.'
			]);
		return;
	}

	$document = mysqli_fetch_assoc($res);

	if($document['canceled_date'] && ($status == "Cancel" || $status == "Publish")) {
		echo json_encode([
				'status' => 'error',
				'message' => 'Order has been canceled already.'
			]);
		return;
	}else if($document['received_date'] && ($status == "Discard" || $status == "Cancel")){
		echo json_encode([
				'status' => 'error',
				'message' => 'Order Has been received aready.'
			]);
		return;
	}

	$date  = date('Y-m-d H:i:s');

	$stage = $document['stage'];
	$stage = $stages[$stage];

	$timeline = json_decode($document['timeline']);

	if($status == "Cancel" && $stage['cancelable']){

		$timeline[] = [
			'stage' => "Canceled for $reason",
			'date'  => $date
		];
		$timeline = json_encode($timeline);

		$SQL = "UPDATE procurement_document 
				SET canceled_date='$date',
					timeline='$timeline'
				WHERE id=$id";
		DB_query($SQL, $db);

		echo json_encode([
			'status' => 'success',
			'permissions' => $stages['Canceled']
		]);
		return;

	}else if($status == "Discard" && $stage['discardable']){
		
		$SQL = "DELETE FROM procurement_document WHERE id=$id";
		DB_query($SQL,$db);
		$SQL = "DELETE FROM procurement_document_details WHERE pdid=$id";
		DB_query($SQL,$db);

		echo json_encode([
			'status' => 'success'
		]);
		return;

	}else if($status == "Publish"){
		
		$timeline[] = [
			'stage' => 'Published',
			'date'  => $date
		];
		$timeline = json_encode($timeline);

		$SQL = "UPDATE procurement_document 
				SET publish_date='$date',
					timeline='$timeline'
				WHERE id=$id";
		DB_query($SQL, $db);

		echo json_encode([
			'status' => 'success',
			'message' => ''
		]);
		return;

	}else{

		echo json_encode([
				'status' => 'error',
				'message' => 'Action could not be completed'
			]);
		return;

	}