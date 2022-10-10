<?php

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	include('stages.php');

	if(!isset($_POST['id'])){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parms.'
			]);
		return;
	}

	$id = $_POST['id'];

	$SQL = "SELECT stage,timeline FROM procurement_document 
			WHERE id=$id 
			AND procurement_document.canceled_date IS NULL 
			AND procurement_document.received_date IS NULL";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		echo json_encode([
				'status' => 'error',
				'message' => 'Cannot Be Updated'
			]);
		return;
	}

	$res = mysqli_fetch_assoc($res);
	$stage = $res['stage'];
	$timeline = json_decode($res['timeline']);

	$stage = $stages[$stage];

	if($stage['isfinal']){
		echo json_encode([
				'status' => 'error',
				'message' => 'Cannot Be Updated'
			]);
		return;
	}

	$date  = date("Y-m-d H:i:s");
	$next  = $stage['next'];

	$timeline[] = [
		'stage' => $next,
		'date'  => $date 
	];

	$timeline = json_encode($timeline);

	if($stages[$next]['isfinal']){
		$SQL = "UPDATE procurement_document
				SET timeline='$timeline',
					received_date='$date',
					stage='$next'
				WHERE id=$id";
	}else{
		$SQL = "UPDATE procurement_document
				SET timeline='$timeline',
					stage='$next'
				WHERE id=$id";
	}

	DB_query($SQL, $db);

	if($stages[$next]['isfinal']){
		$SQL = "SELECT stockid 
				FROM procurement_document_details
				WHERE id=$id
				GROUP BY stockid";
		$res = mysqli_query($db,$SQL);
		while($row = mysqli_fetch_assoc($res)){
			$stkid = $row['stockid'];
			$SQL = "UPDATE reorderitems 
					SET fulfilled_date='$date'
					WHERE approved_date IS NOT NULL
					AND rejected_date IS NULL 
					AND fulfilled_date IS NULL
					AND reorderitems.stockid='".$stkid."'";
			DB_query($SQL, $db);
		}
	}

	echo json_encode([
			'status' => 'success',
			'data'	 => [
				'current' => $next,
				'isfinal' => $stages[$next]['isfinal'],
				'next'	  => $stages[$next]['next'],
				'date'	  => date('d/m/Y'),
				'permissions' => $stages[$next]
			]
		]);