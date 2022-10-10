<?php

	$PathPrefix='../../';
	
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	if(!isset($_GET['salescaseref'])){
		echo json_encode([

				'status' => 'error',
				'message' => 'missing parms'

			]);
		return;
	}

	$salescaseref = trim($_GET['salescaseref']);

	$SQL = "SELECT * FROM salescase_watchlist 
			WHERE salescaseref='".$salescaseref."' 
			AND userid='".$_SESSION['UserID']."'";
	$res = mysqli_query($db, $SQL);

	$action = "";

	if(mysqli_num_rows($res) != 1){
		
		$SQL = "INSERT INTO salescase_watchlist (userid,salescaseref,created_at,priority)
				VALUES ('".$_SESSION['UserID']."','".$salescaseref."','".date('Y-m-d H:i:s')."',2)";

		$action = "INSERT";
	
	}else{

		$row = mysqli_fetch_assoc($res);

		if($row['deleted'] == 1){

			$SQL = "UPDATE salescase_watchlist SET deleted=0
				WHERE salescaseref='".$salescaseref."'
	 			AND userid='".$_SESSION['UserID']."'";

	 		$action = "INSERT";
		
		}else{

			$SQL = "UPDATE salescase_watchlist SET deleted=1
				WHERE salescaseref='".$salescaseref."'
	 			AND userid='".$_SESSION['UserID']."'";

	 		$action = "DELETE";

		}

	}

	DB_query($SQL, $db);

	echo json_encode([

			'status' => 'success',
			'action' => $action

		]);
