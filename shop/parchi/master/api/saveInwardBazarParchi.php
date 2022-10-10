<?php 

	$PathPrefix = "../../../../";
	include "../../../../qrcode/qrlib.php";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db,"create_inward_parchi")){
		echo json_encode([

			'status' => 'error',
			'message' => 'User Does Not Have Access!!! Contact Admin.',

		]);
		return;
	}

	if(!isset($_POST['items']) || count($_POST['items']) < 1 || !isset($_POST['vendor']) || trim($_POST['vendor']) == "" || trim($_POST['obo']) == ""){

		echo json_encode([

			'status' => 'error',
			'message' => 'Missing Parameters',

		]);
		return;

	} 


	$items = $_POST['items'];

	$amount 	= 0;
	$pass   	= true;
	$message 	= "";
	
	$on_behalf_of = trim($_POST['obo']);

	foreach ($items as $item) {
		
		if(trim($item['name']) == ""){

			$pass = false;
			$message = "Empty Name for item passed...";

		}

		if($item['quantity'] <= 0){

			$pass = false;
			$message = "Item with 0 or less quantity found...";

		}
		
		if($item['model'] == ""){

			$pass = false;
			$message = "Item without modelno found...";

		}

		$amount += $item['price'];

	}

	if(!$pass){

		echo json_encode([

			'status' => 'error',
			'message' => $message,

		]);
		return;

	}

	DB_Txn_Begin($db);

	$NIBPNo = GetNextTransNo(601, $db);

	$SQL = "INSERT INTO bazar_parchi (type,transno,parchino,amount,svid,temp_vendor,user_id,created_at,updated_at,on_behalf_of)
			VALUES ('601','".$NIBPNo."','MPIW-".$NIBPNo."','".$amount."','','".trim($_POST['vendor'])."',
					'".$_SESSION['UserID']."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".htmlspecialchars($on_behalf_of,ENT_QUOTES)."')";
	DB_query($SQL, $db);

	foreach ($items as $item) {
		
		$comment = "";
		if(trim($item['model']) != "")
			$comment = "Model No: ".htmlspecialchars(trim($item['model']),ENT_QUOTES);
		
		$SQL = "INSERT INTO bpitems (parchino,name,quantity,listprice,discount,price,created_at,updated_at,comments)
				VALUES ('MPIW-".$NIBPNo."','".addslashes($item['name'])."','".$item['quantity']."',
						'".$item['listprice']."','".$item['discount']."','".$item['price']."',
						'".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".$comment."')";
		DB_query($SQL, $db);

	}
	
	DB_Txn_Commit($db);


	$data = [];
	$data['module'] = "MPIW";
	$data['code'] = 'MPIW-'.$NIBPNo;

	QRcode::png(json_encode($data), '../../../../qrcodes/bazar/MPIW/'.$NIBPNo.'-mpiwQR.png', 'L', 14, 2); 

	echo json_encode([

			'status' => 'success',
			'message' => "Bazar Parchi created successfully.",
			'parchino' => 'MPIW-'.$NIBPNo

		]);
		return;