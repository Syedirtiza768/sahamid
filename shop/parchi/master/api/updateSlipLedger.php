<?php 

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	$parchi = trim($_POST['parchi']);
	$amount = trim($_POST['amount']);

	$SQL = "SELECT * FROM bazar_parchi 
			WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Parchi Not Found or already saved.',

			]);
		return;

	}

	$SQL = "INSERT INTO bpledger (parchino,amount,userid,username)
			VALUES ('".$parchi."','".$amount."','".$_SESSION['UserID']."','".$_SESSION['UsersRealName']."')";

	if(DB_query($SQL, $db)){

		echo json_encode([
				'status' => 'success',
				'time'   => date("d/m/Y")
			]);
		return;

	}
