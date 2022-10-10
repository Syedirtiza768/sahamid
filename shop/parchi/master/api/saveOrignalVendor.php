<?php
	
	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	$parchi = trim($_POST['parchi']);
	$svid = trim($_POST['svid']);

	$SQL = "SELECT * FROM bazar_parchi 
			WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0 AND svid=''";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Parchi Not Found or already saved.',

			]);
		return;

	}

	$SQL = "UPDATE bazar_parchi SET svid='".$svid."'  
			WHERE parchino='".$parchi."'";
	
	if(DB_query($SQL, $db)){

		echo json_encode([
				'status' => 'success'
			]);
		return;

	}


