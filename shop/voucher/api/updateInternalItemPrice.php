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
			FROM shopsalesitems
			INNER JOIN shopsale ON shopsale.orderno = shopsalesitems.orderno 
			WHERE shopsalesitems.id=$itemID
			AND shopsale.complete=0";
	$res = mysqli_query($db, $SQL);
	
	if(mysqli_num_rows($res) <= 0){
		
		echo json_encode([

				'status' => 'error',
				'message' => 'Already Saved.'

			]);
		return;
		
	}	

	$SQL = "UPDATE shopsalesitems 
			SET quantity = $quantity
			WHERE id=$itemID";
	DB_query($SQL, $db);
	$SQL="SELECT lineno FROM shopsalesitems WHERE id=$itemID";

	$lineno=mysqli_fetch_assoc(mysqli_query($db,$SQL))['lineno'];
    $SQL = "UPDATE shopsalesitems 
			SET discountpercent = $discount
			WHERE lineno=$lineno";
    DB_query($SQL, $db);


echo json_encode([

			'status' 	=> 'success'

		]);
	return;

