<?php 

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db,"negotiate_outward_slip_price")){ 
		echo json_encode([
				
				'status' => 'error',
				'message' => 'Permission Denied!'

			]);
		return;
	}

	$parchi = trim($_POST['parchino']);
	$item 	= trim($_POST['item']);
	$listprice 	= trim($_POST['listprice']);
	$discount 	= trim($_POST['discount']);
	$price 	= trim($_POST['price']);
	$name 	= htmlspecialchars(trim($_POST['name']));

	if(!isset($parchi) || !isset($item) || trim($parchi) == "" || trim($item) == "" ||
		!isset($listprice) || trim($listprice) == "" || !isset($discount) || trim($discount) == "" || 
		!isset($price) || trim($price) == "" || !isset($name) || trim($name) == ""){

		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid Parms'

			]);
		return;

	}

	if($price < 0){

		echo json_encode([

				'status' => 'error',
				'message' => 'Entered Value cannot be less then 0'

			]);
		return;

	}

	$SQL = "SELECT * FROM bazar_parchi WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Mess',

			]);
		return;

	}

	$SQL = "SELECT * FROM bpitems WHERE id='".$item."' AND deleted_at IS NULL AND parchino='".$parchi."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Mess2',

			]);
		return;

	}

	$oldValue = mysqli_fetch_assoc($res)['price'];

	if($oldValue == $price){

		echo json_encode([

				'status' => 'error',
				'message' => 'Price Being Updated Was same as before',

			]);
		return;

	}

	$SQL = "UPDATE bpitems SET listprice='".$listprice."',discount='".$discount."',
					price='".$price."',updated_at='".date('Y-m-d H:i:s')."' 
			WHERE id='".$item."'";
	DB_query($SQL, $db);

	$SQL = "INSERT INTO bpitemupdates(bpitemid,type,old_value,new_value,user_id,obo)
			VALUES('".$item."','price','".$oldValue."','".$price."','".$_SESSION['UserID']."','".$name."')";
	DB_query($SQL, $db);


	echo json_encode([

				'status' => 'success',
				'message' => 'Updated Successfully',
				'val' => $price,
				'time' => date("d/m/Y h:i A")

			]);
		return;