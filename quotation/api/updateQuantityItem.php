<?php

	include('../misc.php');

	$response = [];

    $db = createDBConnection();
	$salescaseref 	= $_POST['salescaseref'];
	$orderno 		= $_POST['orderno'];
	$item 			= explode("item", $_POST['item'])[1];
	$name 			= $_POST['name'];
	$value 			= $_POST['value'];





		$SQL = "UPDATE `salesorderdetailsip` 
				SET `quantity`='".$value."'
				WHERE `salesorderdetailsindex`=".$item."";
    $result = mysqli_query($db, $SQL);
    //   $SQLA="DELETE FROM quotationmodifications WHERE orderno=$orderno AND lineno=$lineno AND type ='$name'";
    // mysqli_query($db, $SQLA);
    //  $SQLB = "INSERT INTO quotationmodifications (orderno,lineno,description,type) VALUES ($orderno,$lineno,'$name updated (".$value.") by ".$_SESSION['UsersRealName']."','$name')";

    // mysqli_query($db, $SQLB);



	closeDBConnection($db);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Save Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$response = [

		'status' => 'success',
		'data' => [
			'salescaseref'	=> $salescaseref,
			'orderno'		=> $orderno,
			'name' 			=> $name,
			'value' 		=> $value,
			'discount' 		=> isset($discount) ? $discount : "nan",
		]

	];

	echo json_encode($response);
	return;

?>