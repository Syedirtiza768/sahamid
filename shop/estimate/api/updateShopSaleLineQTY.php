<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['orderno']) || !isset($_POST['line']) || !isset($_POST['quantity'])){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Missing Parms...'
			]);
		return;
	}

	$orderno 		= $_POST['orderno'];
	$lineno  		= $_POST['line'];
	$quantity 		= $_POST['quantity'];

	if($quantity == 0 ){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Quantity cannot be 0 please add quantity again'
			]);
		return;
	}


	$SQL = "SELECT complete FROM shopsale WHERE orderno=$orderno";
	$res = mysqli_query($db, $SQL);

	if(mysqli_fetch_assoc($res)['complete'] == 1){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Quantity cannot be updated shop sale has been finalized'
			]);
		return;
	}

	$SQL = "UPDATE shopsalelines 
			SET quantity='".$quantity."'
			WHERE id='".$lineno."'
			AND orderno='".$orderno."'";
	if(DB_query($SQL, $db)){
		echo json_encode(['status' 	=> 'success']);
	}else{
		echo json_encode(['status' 	=> 'error']);
	}
	return;