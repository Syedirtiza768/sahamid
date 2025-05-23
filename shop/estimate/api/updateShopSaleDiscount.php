<?php 
	
	$AllowAnyone = true;

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['orderno']) || !isset($_POST['discount']) || !isset($_POST['type'])){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Missing Parms...'
			]);
		return;
	}

	$orderno 		= $_POST['orderno'];
	$type  			= trim($_POST['type']);
	$discount 		= $_POST['discount'];

	$SQL = "SELECT complete FROM shopsale WHERE orderno=$orderno";
	$res = mysqli_query($db, $SQL);

	if(mysqli_fetch_assoc($res)['complete'] == 1){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Discount cannot be updated shop sale has been finalized'
			]);
		return;
	}


	$SQL = "UPDATE shopsale 
			SET ".$type."='".$discount."'
			WHERE orderno='".$orderno."'";
	if(DB_query($SQL, $db)){
		echo json_encode(['status' 	=> 'success']);
	}else{
		echo json_encode(['status' 	=> 'error']);
	}
	return;