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

	$salescaseref = $_POST['salesref'];
if(!isset($_POST['order'])){

    $response = [
        'status' => 'error',
        'message' => 'Missing Parameters.'
    ];

    echo json_encode($response);
    return;

}

$orderno  	  = $_POST['order'];
	$data 		  = $_POST['data'];

	$db = createDBConnection();

	$SQL = "SELECT * FROM salesordersip 
			WHERE salescaseref = '".$salescaseref."'
			AND orderno='".$orderno."'";

	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Orderno not found.'
		];

		echo json_encode($response);
		return;	

	}

	foreach($data as $brand => $discount){

	$SQL = "UPDATE salesorderdetailsip INNER JOIN stockmaster ON salesorderdetailsip.stkcode=stockmaster.stockid
				SET unitrate=materialcost*'".(1-$discount/100)."',
				discountpercent=$discount,
				salesorderdetailsip.lastcostupdate=stockmaster.lastcostupdate,
				salesorderdetailsip.lastupdatedby=stockmaster.lastupdatedby
				WHERE orderno='".$orderno."'
				AND stkcode IN (SELECT abcd.stkcode FROM (SELECT * FROM salesorderdetailsip) as abcd
				INNER JOIN stockmaster ON stockmaster.stockid = abcd.stkcode
				INNER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
				WHERE manufacturers.manufacturers_name = '".$brand."'
				AND abcd.orderno = '".$orderno."')";

		$res = mysqli_query($db, $SQL);
        /*$SQL = "UPDATE `salesorderdetails`
				SET unitrate=unitprice*'".(1-$discount/100)."',
				discountpercent=$discount
				WHERE orderno='".$orderno."'
				AND stkcode IN (SELECT abcd.stkcode FROM (SELECT * FROM salesorderdetails) as abcd
				INNER JOIN stockmaster ON stockmaster.stockid = abcd.stkcode
				INNER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
				WHERE manufacturers.manufacturers_name = '".$brand."'
				AND abcd.orderno = '".$orderno."')";

        $res = mysqli_query($db, $SQL);*/

	}

	$response = [
		'status' => 'success'
	];

	echo json_encode($response);
	return;	