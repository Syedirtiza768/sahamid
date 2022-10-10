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
			SET discountpercent = $discount
			WHERE lineno=$externalLineID";
    DB_query($SQL, $db);
	$SQL = "DELETE FROM shopsalesitems WHERE id=$itemID";
	DB_query($SQL, $db);

	echo json_encode([

			'status' => 'success',

		]);
	return;