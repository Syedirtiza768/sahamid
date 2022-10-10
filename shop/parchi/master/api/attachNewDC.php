<?php 

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['parchino']) || trim($_POST['parchino']) == ""
		|| !isset($_POST['dcno']) || trim($_POST['dcno']) == ""){
		
		echo json_encode([
				
				'status' => 'error',
				'message' => 'Missing Parameters'

			]);
		return;
	
	}

	$parchi = trim($_POST['parchino']);
	$dcno   = addslashes(trim($_POST['dcno']));

	$SQL = "SELECT * FROM dcs WHERE orderno='".$dcno."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid DC NO'

			]);
		return;
	}

	$SQL = "SELECT * FROM parchi_dc WHERE parchino='".$parchi."' AND dcno='".$dcno."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		echo json_encode([

				'status' => 'error',
				'message' => 'DC Already Attached'

			]);
		return;
	}

	$SQL = "INSERT INTO parchi_dc (parchino,dcno)
	 		VALUES('".$parchi."','".$dcno."')";
	$res = DB_query($SQL, $db);

	if($res){
		echo json_encode([

			'status' => 'success',

		]);
		return;	
	}
	
	echo json_encode([

			'status' => 'error',
			'message' => 'Something went wrong'

		]);
	return;