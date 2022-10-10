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
		|| !isset($_POST['item']) || !isset($_POST['name']) 
		|| !isset($_POST['value'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref 	= $_POST['salescaseref'];
	$orderno 		= $_POST['orderno'];
	$item 			= explode("item", $_POST['item'])[1];
	$name 			= $_POST['name'];
	$value 			= $_POST['value'];

	$db = createDBConnection();

	if($name == "quantity"){

		$SQL = "UPDATE `ocdetails` 
				SET `quantity`='".$value."'
				WHERE `ocdetailsindex`=".$item."";

	}else{

		if($name == "uprice"){

			if(!isset($_POST['discount'])){

				$response = [
					'status' => 'error',
					'message' => 'Invalid Parameters.'
				];

				echo json_encode($response);
				return;	

			}

			$uprice = $value;
			$discount = $_POST['discount']/100;

		}else{

			$response = [
				'status' => 'error',
				'message' => 'Invalid Parameters.'
			];

			echo json_encode($response);
			return;	

		}

		$SQL = "UPDATE `ocdetails` 
				SET `discountpercent`='".$discount."'
				WHERE `ocdetailsindex`=".$item."";

	}

	$result = mysqli_query($db, $SQL);

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