<?php
	
	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['name']) || trim($_POST['name']) == "" || strlen(trim($_POST['name'])) < 3){
		
		echo json_encode([
				
				'status' => 'error',
				'message' => 'Missing Parameters'

			]);
		return;
	
	}

	$_POST['name'] = trim($_POST['name']);

	$SQL = "SELECT * FROM shop_vendors WHERE name='".$_POST['name']."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) >= 1){

		echo json_encode([
				
				'status' => 'error',
				'message' => 'Vendor Already Exists'

			]);
		return;

	}

	$SQL = "INSERT INTO shop_vendors (name) VALUES ('".$_POST['name']."')";
	DB_query($SQL, $db);

	$SQL = "SELECT * FROM shop_vendors WHERE name='".$_POST['name']."'";
	$res = mysqli_query($db, $SQL);
	$res = mysqli_fetch_assoc($res);

	$SQL = "UPDATE shop_vendors SET vid='MV-".$res['id']."' WHERE id='".$res['id']."'";
	DB_query($SQL, $db);

	$SQL = "INSERT INTO suppliers(`supplierid`,`suppname`,`supptype`,`currcode`,`paymentterms`,`suppliersince`)
			VALUES ('MV-".$res['id']."','".$_POST['name']."',2,'PKR','DF','".date('Y-m-d')."')";
	DB_query($SQL, $db);

	echo json_encode([
				
				'status' => 'success',
				'vid' => 'MV-'.$res['id'],
				'name' => $_POST['name']

			]);