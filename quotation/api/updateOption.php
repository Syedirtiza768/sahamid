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

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno'])
		|| !isset($_POST['name']) || !isset($_POST['value'])
		|| !isset($_POST['option'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];
	$option = $_POST['option'];
	$name = $_POST['name'];
	$value = $_POST['value'];

	$value = str_replace("'", "\'", $value);
	
	if(isInValidSalesCase($salescaseref, $orderno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters1.'
		];

		echo json_encode($response);
		return;	

	}

	if(!($name == "stockstatus" || $name == "quantity" || $name == "uom" || $name == "price")){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.',
			'name'	=> $name
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();
    $SQL="SELECT lineno FROM salesorderoptionsip WHERE optionindex='".$option."'";
    $lineno=mysqli_fetch_assoc(mysqli_query($db,$SQL))['lineno'];

	$SQL = "UPDATE `salesorderoptionsip` SET `".$name."`='".$value."' 
			WHERE orderno='".$orderno."'
			AND optionindex='".$option."'";

	$result = mysqli_query($db, $SQL);
$SQLA="DELETE FROM quotationmodifications WHERE orderno=$orderno AND lineno=$lineno AND type ='$name'";
mysqli_query($db, $SQLA);
$SQLB = "INSERT INTO quotationmodifications (orderno,lineno,description,type) VALUES ($orderno,$lineno,'$name updated (".$value.") by ".$_SESSION['UsersRealName']."','$name')";

mysqli_query($db, $SQLB);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Update Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$response['status'] = "success";
	$response['data'] = [

		'name'	=>	$name,
		'value'	=>	$value,

	];

	echo json_encode($response);
	return;	

?>