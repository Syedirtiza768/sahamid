<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db, 'delete_internal_item_shopsale')){
		
		echo json_encode([

				'status' => 'success',
				'message' => 'Permission Denied.'

			]);
		return;
		
	}

	if(!isset($_POST['itemid'])){
		echo json_encode([

				'status' => 'success',
				'message' => 'Missing Parms'

			]);
		return;
	}

	$itemID = $_POST['itemid'];
    $discount   = $_POST['discount'];
    $externalLineID=$_POST['externalLineID'];
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
			SET discountpercent = $discount
			WHERE lineno=$externalLineID";
    DB_query($SQL, $db);
	$SQL = "DELETE FROM estimateshopsalesitems WHERE id=$itemID";
	DB_query($SQL, $db);

	echo json_encode([

			'status' => 'success',

		]);
	return;