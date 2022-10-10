<?php

	include('../misc.php');

	$response = [];

	if(!validHeaders()){

		$response = [
			'status' => 'error',
			'message' => 'Refresh or ReLogin Required.'
		];

		echo json_encode($response);
		return;		

	}

	if(!isset($_POST['type']) || !isset($_POST['orderno']) || 
		!isset($_POST['description']) || !isset($_POST['lineno']) || 
		!isset($_POST['index']) || !isset($_POST['option']) ){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$type = $_POST['type'];
	$orderno = $_POST['orderno'];
	$description = $_POST['description'];
	$lineno = $_POST['lineno'];
	$index = $_POST['index'];
	$option = $_POST['option'];

	$db = createDBConnection();
	mysqli_set_charset($db,"utf8");

	$description = (addslashes($description));

	if($type == "cr"){

		$SQL = "UPDATE `salesorderlinesip` 
				SET `clientrequirements`='".$description."' 
				WHERE lineindex=".$lineno."";

		mysqli_query($db, $SQL);
		$SQL="DELETE FROM quotationmodifications WHERE orderno=$orderno AND lineno=$lineno AND type='cr'";
        mysqli_query($db, $SQL);
       $SQL = "INSERT INTO quotationmodifications (orderno,lineno,description,type) VALUES ($orderno,$lineno,'Client Requirements Updated By ".$_SESSION['UsersRealName']."','cr')";

        mysqli_query($db, $SQL);


	}else if($type == "desc"){

		$SQL = "UPDATE  `salesorderoptionsip` 
				SET `optiontext`='".$description."' 
				WHERE `optionindex`=".$option."";
				
		mysqli_query($db, $SQL);
        $SQL="DELETE FROM quotationmodifications WHERE orderno=$orderno AND lineno=$lineno AND type='desc'";
        mysqli_query($db, $SQL);
        $SQL = "INSERT INTO quotationmodifications (orderno,lineno,description,type) VALUES ($orderno,$lineno,'Description Updated By ".$_SESSION['UsersRealName']."','desc')";

        mysqli_query($db, $SQL);


    }else{

		$response = [
			'status' => 'error',
			'message' => 'Wrong Parms.'
		];

		echo json_encode($response);
		return;	

	}

	$response = [
		'status' => 'success',
		'data' => [
			'index' => $index,
		]
	];

	echo json_encode($response);
	return;	



?>