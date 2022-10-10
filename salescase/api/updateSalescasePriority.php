<?php 

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	$salescaseref = trim($_POST['salescaseref']);
	$priority 	  = trim($_POST['priority']);

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

	$SQL = "UPDATE salescase SET priority='".$priority."', priority_added=1, priority_updated_by='".$_SESSION['UsersRealName']."'
			WHERE salescaseref='".$salescaseref."'";
	DB_Query($SQL, $db);

	if($priority == "high"){

		$SQL = "SELECT * FROM salescase_watchlist
				WHERE salescaseref='".$salescaseref."'
				AND userid='".$_SESSION['UserID']."'";
		$res = mysqli_query($db, $SQL);

		if(mysqli_num_rows($res) == 0){

			$SQL = "INSERT INTO salescase_watchlist (userid,salescaseref,created_at,priority)
					VALUES ('".$_SESSION['UserID']."','".$salescaseref."','".date('Y-m-d H:i:s')."',2)";
			DB_Query($SQL,$db);

		}

	}
	
	echo json_encode([

			'status' => 'success',
			'message' => 'updated'

		]);
	return;