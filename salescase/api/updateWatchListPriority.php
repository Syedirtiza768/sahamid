<?php

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$priority = $_POST["priority"];
	$salescaseref = $_POST['salescaseref'];
	$UserID = $_SESSION['UserID'];

	$SQL = "UPDATE salescase_watchlist 
			SET priority='".$priority."'
			WHERE salescaseref='".$salescaseref."'
			AND userid='".$UserID."'";

	DB_query($SQL, $db);

	echo json_encode([

			'status' => 'success'

		]);
