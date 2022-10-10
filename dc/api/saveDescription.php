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

	$description = str_replace("'", "\'", $description);

	$db = createDBConnection();

	if($type == "cr"){

		$SQL = "UPDATE `dclines` 
				SET `clientrequirements`='".$description."' 
				WHERE lineindex=".$lineno."";

		mysqli_query($db, $SQL);


	}else if($type == "desc"){

		$SQL = "UPDATE  `dcoptions` 
				SET `optiontext`='".$description."' 
				WHERE `optionindex`=".$option."";

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
			'line' 	=> $lineno,
			'option'=> $option,

		]
	];

	echo json_encode($response);
	return;	



?>