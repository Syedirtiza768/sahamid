<?php 

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db,"inward_slip_item_quantity")){ 
		echo json_encode([
				
				'status' => 'error',
				'message' => 'Permission Denied!'

			]);
		return;
	}

	$parchi = trim($_POST['parchi']);
	$item 	= trim($_POST['item']);
	$type 	= trim($_POST['type']);
	$value 	= trim($_POST['value']);

	if(!isset($parchi) || !isset($item) || trim($parchi) == "" || trim($item) == "" ||
		!isset($type) || trim($type) == "" || !isset($value) || trim($value) == ""){

		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid Parms'

			]);
		return;

	}

	if($value < 0){

		echo json_encode([

				'status' => 'error',
				'message' => 'Entered Value cannot be less then 0'

			]);
		return;

	}

	$SQL = "SELECT * FROM bazar_parchi WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0 AND igp_created=0";
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

	if($type != "quantity"){

		echo json_encode([

				'status' => 'error',
				'message' => 'Invalid Parms 2',

			]);
		return;

	}

	$oldValue = 0;

	if($type == "quantity"){

		$SQL = "SELECT quantity_received FROM bpitems WHERE id='".$item."' AND deleted_at IS NULL AND parchino='".$parchi."'";
		$res = mysqli_query($db, $SQL);
		$oldValue = mysqli_fetch_assoc($res)['quantity_received'];

		$SQL = "UPDATE bpitems SET quantity_received='".$value."' WHERE id='".$item."'";

	}else if($type == "price"){

		$SQL = "SELECT price FROM bpitems WHERE id='".$item."' AND deleted_at IS NULL AND parchino='".$parchi."'";
		$res = mysqli_query($db, $SQL);
		$oldValue = mysqli_fetch_assoc($res)['price'];

		$SQL = "UPDATE bpitems SET price='".$value."' WHERE id='".$item."'";

	}

	DB_query($SQL, $db);

	$SQL = "INSERT INTO bpitemupdates(bpitemid,type,old_value,new_value,user_id)
			VALUES('".$item."','".$type."','".$oldValue."','".$value."','".$_SESSION['UserID']."')";
	DB_query($SQL, $db);


	echo json_encode([

				'status' => 'success',
				'message' => 'Updated Successfully',

			]);
		return;