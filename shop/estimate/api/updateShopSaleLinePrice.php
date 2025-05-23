<?php 
	
	$AllowAnyone = true;

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['orderno']) || !isset($_POST['line']) || !isset($_POST['price'])){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Missing Parms...'
			]);
		return;
	}

	$orderno 	= $_POST['orderno'];
	$lineno  	= $_POST['line'];
	$price 		= $_POST['price'];
	if (isset($_POST['discount']))
    $discount   = $_POST['discount'];
	$SQL = "SELECT complete FROM shopsale WHERE orderno=$orderno";
	$res = mysqli_query($db, $SQL);

	if(mysqli_fetch_assoc($res)['complete'] == 1){
		echo json_encode([
				'status' 	=> 'error',
				'message' 	=> 'Price cannot be updated shop sale has been finalized'
			]);
		return;
	}
if (isset($_POST['discount'])){
    $SQL = "UPDATE shopsalesitems 
			SET discountpercent = $discount
			WHERE lineno=$lineno";
DB_query($SQL, $db);}
	$SQL = "UPDATE shopsalelines 
			SET price='".$price."'
			WHERE id='".$lineno."'
			AND orderno='".$orderno."'";
	if(DB_query($SQL, $db)){
		echo json_encode(['status' 	=> 'success']);
	}else{
		echo json_encode(['status' 	=> 'error']);
	}
	return;