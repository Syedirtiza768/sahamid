<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db, 'update_quantity_shopsale')){
		
		echo json_encode([

				'status' 	=> 'error',
				'message' 	=> 'Permission Denied.'

			]);
		return;
		
	}

	if(!isset($_POST['itemid']) || !isset($_POST['quantity'])){

		echo json_encode([

				'status' 	=> 'error',
				'message' 	=> 'Missing Parms...'

			]);
		return;

	}

	$itemID 	= $_POST['itemid'];
	$quantity 	= $_POST['quantity'];
    $discount 	= $_POST['discount'];

	$SQL = "SELECT * 
			FROM estimateshopsalesitems
			INNER JOIN estimateshopsale ON estimateshopsale.orderno = estimateshopsalesitems.orderno 
			WHERE estimateshopsalesitems.id=$itemID
			AND estimateshopsale.complete=0";
	$res = mysqli_query($db, $SQL);
	
	if(mysqli_num_rows($res) <= 0){
		
		echo json_encode([

				'status' => 'error',
				'message' => 'Already Saved.'

			]);
		return;
		
	}	

	$SQL = "UPDATE estimateshopsalesitems 
			SET quantity = $quantity
			WHERE id=$itemID";
	DB_query($SQL, $db);
	$SQL="SELECT lineno FROM estimateshopsalesitems WHERE id=$itemID";

	$lineno=mysqli_fetch_assoc(mysqli_query($db,$SQL))['lineno'];
    $SQL = "UPDATE estimateshopsalesitems 
			SET discountpercent = $discount
			WHERE lineno=$lineno";
    DB_query($SQL, $db);


echo json_encode([

			'status' 	=> 'success'

		]);
	return;

