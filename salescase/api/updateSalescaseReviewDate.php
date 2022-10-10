<?php 

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	$salescaseref = trim($_POST['salescaseref']);
	$reviewDate	  = trim($_POST['review']);

	$SQL = "SELECT * FROM salescase 
			WHERE closed=0
			AND salescaseref='".$salescaseref."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) <= 0){

		echo json_encode([

				'status' => 'error',
				'message' => 'closed or invalid'

			]);
		return;

	}

	if(strtotime($reviewDate) < strtotime('yesterday')){
		
		echo json_encode([

				'status' => 'error',
				'message' => 'Select a future date or current date'

			]);
		return;
	
	}

	$SQL = "SELECT * FROM salescase_watchlist
			WHERE salescaseref='".$salescaseref."'
			AND userid='".$_SESSION['UserID']."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){

		$SQL = "INSERT INTO salescase_watchlist (userid,salescaseref,review_on,created_at,priority)
				VALUES ('".$_SESSION['UserID']."','".$salescaseref."','".$reviewDate."','".date('Y-m-d H:i:s')."',2)";

	}else{

		$SQL = "UPDATE salescase_watchlist 
				SET review_on='".$reviewDate."', deleted=0 
				WHERE salescaseref='".$salescaseref."'
				AND userid='".$_SESSION['UserID']."'";

	}

	DB_Query($SQL,$db);

	echo json_encode([

			'status' => 'success',
			'message' => 'updated'

		]);
	return;