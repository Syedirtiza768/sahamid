<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['orderno']) || !isset($_POST['line']) || !isset($_POST['description'])){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Missing Parms...'
			]);
		return;
	}

	$orderno 		= $_POST['orderno'];
	$lineno  		= $_POST['line'];
	$description 	= $_POST['description'];

	if(trim($description) == ""){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Description Cannot Be Empty'
			]);
		return;
	}

	$SQL = "SELECT complete FROM shopsale WHERE orderno=$orderno";
	$res = mysqli_query($db, $SQL);

	if(mysqli_fetch_assoc($res)['complete'] == 1){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Description cannot be updated shop sale has been finalized'
			]);
		return;
	}

	$SQL = "UPDATE shopsalelines 
			SET description='".htmlentities($description)."'
			WHERE id='".$lineno."'
			AND orderno='".$orderno."'";
	if(DB_query($SQL, $db)){
		echo json_encode(['status' 	=> 'success']);
	}else{
		echo json_encode(['status' 	=> 'error']);
	}
	return;