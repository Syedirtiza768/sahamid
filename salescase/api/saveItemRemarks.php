<?php

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$salescaseref = $_POST['salescaseref'];
	$itemcode	  = $_POST['itemcode'];
	$remarks 	  = $_POST['remarks'];

	$db  = createDBConnection();
	$res = [];
	
	$SQL = 'SELECT * FROM salescaseremarks 
			WHERE salescaseref="'.$salescaseref.'"
			AND itemcode="'.$itemcode.'"';

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) == 0){

		$SQL = "INSERT INTO salescaseremarks(salescaseref,remarks,itemcode) 
				VALUES ('".$salescaseref."','".$remarks."','".$itemcode."') ";
		
	}else{

		$SQL = "UPDATE salescaseremarks
				SET remarks='".$remarks."' WHERE salescaseref='".$salescaseref."' 
				AND itemcode='".$itemcode."'";

	}

	DB_query($SQL, $db);

	$res['status']  = 'success';
	$res['message'] = '';

	echo json_encode($res);