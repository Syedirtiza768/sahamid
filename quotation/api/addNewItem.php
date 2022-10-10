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

	if(isInValidSalesCase($salescaseref, $orderno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();


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

	$SQL = "SELECT salesorderdetailsindex FROM salesorderdetailsip 
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
	$unitrate = $unitprice;
	$lastcostupdate = $item['lastcostupdate'];
	$lastupdatedby = $item['lastupdatedby'];

	$SQL = "INSERT INTO `salesorderdetailsip`(`orderlineno`, `orderno`, `lineoptionno`,`stkcode`,`unitprice`,`unitrate`,`lastcostupdate`,`lastupdatedby`,
			`discountpercent`,`quantity`)
			VALUES ('".$line."','".$orderno."','".$option."','".$item_id."','".$unitprice."','".$unitrate."','".$lastcostupdate."','".$lastupdatedby."',
			'".$discount."','".$quantity."')";

	$result = mysqli_query($db, $SQL);
    $insertId = mysqli_insert_id($db);
/*    $SQL="DELETE FROM quotationmodifications WHERE orderno=$orderno AND lineno=$line AND type='additem'";
    mysqli_query($db, $SQL);*/
    $SQL = "INSERT INTO quotationmodifications (orderno,lineno,description,type) VALUES ($orderno,$line,'Item Added (".$item_id.") by ".$_SESSION['UsersRealName']."','additem')";

    mysqli_query($db, $SQL);

if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Item could not be added.'
		];

		echo json_encode($response);
		return;	

	}



	$SQL = "SELECT 	locstock.stockid,salesordersip.fromstkloc,
					locstock.quantity
			FROM salesordersip
			INNER JOIN locstock 
			ON locstock.loccode = salesordersip.fromstkloc
			WHERE locstock.stockid = '".$item_id."'";

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

	closeDBConnection($db);

	$response = [];

	$response['status'] = "success";
	$response['data'] = [

		'id'		=>	$insertId,
		'title'		=>	$item_id,
		'line'		=>	$line,
		'option'	=>	$option,
		'model'		=>	$item['mnfCode'],
		'part'		=>	$item['mnfpno'],
		'desc'		=>	$item['description'],
		'qoh'		=>	$qoh,
		'price'		=>	$unitprice,
		'discount'	=>	0,
		'quantity'	=>	0,
		'total'		=>	0,
		'updateby'	=>	$item['lastupdatedby'],
        'updated'	=>  $item['lastcostupdate']

	];

	utf8_encode_deep($response);

	echo json_encode($response);

	return;

?>