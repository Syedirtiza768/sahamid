<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db, 'internal_items_shopsale')){

		echo json_encode([

				'status' => 'error',
				'message' => 'Permission Denied.'

			]);
		return;
	
	}
	
	if(!isset($_POST['line']) || !isset($_POST['stockid'])){

		echo json_encode([

				'status' => 'error',
				'message' => 'Missing Parms...'

			]);
		return;

	}

	$lineID = $_POST['line'];
	$stockid = $_POST['stockid'];

	$SQL = "SELECT  manufacturers.manufacturers_name as mnfname,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.materialcost as price,
					stockmaster.mnfcode,
					stockmaster.mnfpno
			FROM stockmaster 
			INNER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
			WHERE stockmaster.stockid = '$stockid'";
	$res = mysqli_query($db, $SQL);
	$item = mysqli_fetch_assoc($res);
	if($item['price'] == 0){

    echo json_encode([

        'status' => 'error',
        'message' => 'Item Without Price',
        'SQL' => $SQL

    ]);
    return;

}
	$SQL = "SELECT * FROM estimateshopsalelines WHERE id=$lineID";
	$res = mysqli_query($db, $SQL);
	$line = mysqli_fetch_assoc($res);

	$orderno = $line['orderno'];
	$rate = $item['price'];

	$SQL = "SELECT * FROM estimateshopsalesitems WHERE orderno='$orderno' AND stockid='$stockid' AND lineno='$lineID'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) > 0){

		echo json_encode([

				'status' => 'error',
				'message' => 'Item Already Attached.',
				'SQL' => $SQL

			]);
		return;

	}

	$SQL = "INSERT INTO estimateshopsalesitems(orderno, lineno, stockid, quantity, rate) 
			VALUES ('$orderno','$lineID','$stockid',0,$rate)";
	DB_query($SQL, $db);

	$item['id'] = $_SESSION['LastInsertId'];
	$item['line'] = $lineID;

	echo json_encode([

			'status' => 'success',
			'item' => $item

		]);
	return;




