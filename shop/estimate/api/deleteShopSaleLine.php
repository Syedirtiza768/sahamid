<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['orderno']) || !isset($_POST['line'])){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Missing Parms...'
			]);
		return;
	}

	$orderno 		= $_POST['orderno'];
	$lineno  		= $_POST['line'];

	$SQL = "SELECT complete FROM estimateshopsale WHERE orderno=$orderno";
	$res = mysqli_query($db, $SQL);

	if(mysqli_fetch_assoc($res)['complete'] == 1){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Line cannot be deleted shop sale has been finalized'
			]);
		return;
	}

	$SQL = "SELECT * FROM estimateshopsalelines WHERE orderno=$orderno";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'This is the only line remaining it cannot be deleted',
				'SQL'		=> $SQL
			]);
		return;
	}

	$SQL = "DELETE FROM estimateshopsalesitems WHERE orderno=$orderno AND lineno=$lineno";
	DB_query($SQL, $db);

	$SQL = "DELETE FROM estimateshopsalelines WHERE orderno=$orderno AND id=$lineno";
	DB_query($SQL, $db);
	
	echo json_encode(['status' 	=> 'success']);
	return;