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
    $db = createDBConnection();
	$salescaseref 	= $_POST['salescaseref'];
	$orderno 		= $_POST['orderno'];
	$item 			= explode("item", $_POST['item'])[1];
	$name 			= $_POST['name'];
	$value 			= $_POST['value'];

	$SQL="SELECT orderlineno FROM salesorderdetailsip WHERE `salesorderdetailsindex`=".$item."";
	$lineno=mysqli_fetch_assoc(mysqli_query($db,$SQL))['orderlineno'];
	if(isInValidSalesCase($salescaseref, $orderno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.'
		];

		echo json_encode($response);
		return;	

	}



	if($name == "quantity"){

		$SQL = "UPDATE `salesorderdetailsip` 
				SET `quantity`='".$value."'
				WHERE `salesorderdetailsindex`=".$item."";


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
		$SQL = "UPDATE `salesorderdetailsip` 
				SET `discountpercent`=".$discount.",`unitrate`=".$value."
				WHERE `salesorderdetailsindex`=".$item."";

	}
    $result = mysqli_query($db, $SQL);
      $SQLA="DELETE FROM quotationmodifications WHERE orderno=$orderno AND lineno=$lineno AND type ='$name'";
    mysqli_query($db, $SQLA);
     $SQLB = "INSERT INTO quotationmodifications (orderno,lineno,description,type) VALUES ($orderno,$lineno,'$name updated (".$value.") by ".$_SESSION['UsersRealName']."','$name')";

    mysqli_query($db, $SQLB);



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