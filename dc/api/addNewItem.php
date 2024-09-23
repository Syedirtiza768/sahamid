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
		|| !isset($_POST['line']) || !isset($_POST['option'])
		|| !isset($_POST['item_id'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];
	$line = $_POST['line'];
	$option = $_POST['option'];
	$item_id = $_POST['item_id'];

	$db = createDBConnection();

	$SQL = "SELECT * FROM dcs WHERE orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'OC not found.'
		];

		echo json_encode($response);
		return;	

	}

	$ocs = mysqli_fetch_assoc($result);

	$SQL = "SELECT lineno FROM dclines WHERE lineindex='".$line."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Line index not found.'
		];

		echo json_encode($response);
		return;	

	}

	$line = mysqli_fetch_assoc($result)['lineno'];

	$SQL = "SELECT optionno FROM dcoptions 
			WHERE optionindex='".$option."'
			AND lineno='".$line."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Line index not found.'
		];

		echo json_encode($response);
		return;	

	}

	$option = mysqli_fetch_assoc($result)['optionno'];

	$SQL = "SELECT * FROM stockmaster 
			WHERE stockid='".$item_id."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Item Not Found.'
		];

		echo json_encode($response);
		return;	

	}

	$item = mysqli_fetch_assoc($result);

	if($item['materialcost'] < 1){

		$response = [
			'status' => 'error',
			'message' => 'Material cost not set up.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "SELECT dcdetailsindex FROM dcdetails 
			WHERE orderlineno='".$line."' 
			AND orderno='".$orderno."'
			AND lineoptionno='".$option."'
			AND stkcode='".$item_id."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) > 0){

		$response = [
			'status' => 'error',
			'message' => 'Item Already Added.'
		];

		echo json_encode($response);
		return;	

	}

	$unitprice = $item['materialcost']+$item['labourcost']+$item['overheadcost'];
	$discount  = 0;
	$quantity  = 0;

	$SQL = "INSERT INTO `dcdetails`(`orderlineno`, `orderno`, `lineoptionno`,`stkcode`,`unitprice`,
			`discountpercent`,`quantity`)
			VALUES ('".$line."','".$orderno."','".$option."','".$item_id."','".$unitprice."',
			'".$discount."','".$quantity."')";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Item could not be added.'
		];

		echo json_encode($response);
		return;	

	}

	$insertId = mysqli_insert_id($db);

	$SQL = "SELECT salesman FROM salescase WHERE salescaseref='".$salescaseref."'";
	$result = mysqli_query($db, $SQL);
	$salesman = mysqli_fetch_assoc($result)['salesman'];

	$SQL = "SELECT issued as quantity,dc FROM stockissuance WHERE stockid='".$item_id."'
			AND salesperson='".$salesman."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Error fetching QOH please refresh.'
		];

		echo json_encode($response);
		return;	

	}

	$qohResult = mysqli_fetch_assoc($result);

	$qoh = $qohResult['quantity'];
	
	
	$SQL = "SELECT quantity as quantity FROM ogpsalescaseref WHERE stockid='".$item_id."'
			AND salesman='".$salesman."' AND salescaseref = '".$salescaseref."'";

	$result = mysqli_query($db, $SQL);

	$qohResult = mysqli_fetch_assoc($result);

	$salescase_quant = $qohResult['quantity'];

	closeDBConnection($db);

	$response = [];

	$response['status'] = "success";
	$response['data'] = [

		'id'		=>	$insertId,
		'title'		=>	$item_id,
		'line'		=>	$_POST['line'],
		'option'	=>	$_POST['option'],
		'model'		=>	$item['mnfCode'],
		'part'		=>	$item['mnfpno'],
		'desc'		=>	$item['description'],
		'qoh'		=>	$qoh,
		'salescase_quant'		=>	$salescase_quant ,
		'price'		=>	$unitprice,
		'discount'	=>	0,
		'quantity'	=>	0,
		'total'		=>	0,
		'update'	=>	$item['lastupdatedby']."(".$item['lastcostupdate'].")",

	];

	utf8_encode_deep($response);

	echo json_encode($response);

	return;

?>