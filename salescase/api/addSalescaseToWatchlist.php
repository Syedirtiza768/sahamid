<?php

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	$salescaseref = trim($_GET['salescaseref']);

	$SQL = "SELECT * FROM salescase_watchlist
	 		WHERE salescaseref='".$salescaseref."'
	 		AND userid='".$_SESSION['UserID']."'";

	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) > 0){

		$SQL = "UPDATE salescase_watchlist SET deleted=0
				WHERE salescaseref='".$salescaseref."'
	 			AND userid='".$_SESSION['UserID']."'";

	}else{

		$SQL = "INSERT INTO salescase_watchlist (userid,salescaseref,created_at,priority)
				VALUES ('".$_SESSION['UserID']."','".$salescaseref."','".date('Y-m-d H:i:s')."',2)";

	}

	DB_query($SQL, $db);

	header('Location: ' . $_SERVER['HTTP_REFERER']);

