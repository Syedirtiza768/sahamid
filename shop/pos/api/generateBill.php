<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
	include "../../../qrcode/qrlib.php";
	
	if(!userHasPermission($db, 'create_shop_sale')){
		echo json_encode([
				'status' => 'error',
				'message' => 'Permission Denied.'
			]);
		return;
	}

	$client  	= $_POST['client'];
	$items   	= $_POST['items'];
	$payment 	= $_POST['payment'];
	$advance 	= $_POST['advance'];
	$crname  	= $_POST['name'];
	$discount  	= $_POST['discount'];
	$discountPKR= $_POST['discountPKR'];
	$dispatched = $_POST['dispatchvia'];
	$creferance = $_POST['creferance'];
	$paid 		= $_POST['paid'];

	if($payment != "csv" && $payment != "crv"){

		echo json_encode([
				'status' => 'error',
				'message' => 'missing parms'
			]);
		return;

	}

	if(!isset($items) || count($items) <= 0 || !isset($client)){

		echo json_encode([
				'status' => 'error',
				'message' => 'missing parms'
			]);
		return;

	}

	$error = false;

	DB_Txn_Begin($db);

	if($client['type'] == "new"){

		//create client & verify its created
		$newClientID = "SR-7".GetNextTransNo(701,$db);

		//Client
		$SQL = "INSERT INTO debtorsmaster (debtorno,name,address1,address2,address3,address4,address5,address6,currcode,
							clientsince,holdreason,paymentterms,discount,discountcode,pymtdiscount,creditlimit,
							salestype,invaddrbranch,taxref,customerpoline,typeid,dba,language_id)
				VALUES ('$newClientID','".$client['name']."','".$client['address1']."','".$client['address2']."',
						'".$client['address3']."','','','','PKR','".date('Y-m-d H:i:s')."','1','CA','0','','0','1000',
						'11','0','','0','".$client['ctype']."','".$client['dba']."','en_GB.utf8')";
		
		if(!DB_query($SQL,$db)){

			echo json_encode([
				'status' => 'error',
				'message' => 'Client Creation Failed'
			]);
			return;

		}

		//Branch
		$SQL = "INSERT INTO custbranch(branchcode,debtorno,brname,braddress1,braddress2,braddress3,braddress4,
							braddress5,braddress6,specialinstructions,estdeliverydays,salesman,phoneno,faxno,
							contactname,area,email,defaultlocation,brpostaddr1,brpostaddr2,brpostaddr3,
							brpostaddr4,brpostaddr5,disabletrans,defaultshipvia,custbranchcode,deliverblind)
				VALUES ('$newClientID','$newClientID','".$client['name']."','".$client['address1']."',
						'".$client['address2']."','".$client['address3']."','','','','','0','".$client['salesman']."',
						'','','','15','','MT','','','','','','0','1','','1')";

		if(!DB_query($SQL,$db)){

			echo json_encode([
				'status' => 'error',
				'message' => 'Branch Creation Failed'
			]);
			return;

		}

		$customer = [];
		$customer['debtorno'] 	= $newClientID;
		$customer['branchcode'] = $newClientID;
		$customer['salesman'] 	= $client['salesman'];

	}else{

		//verify provided branch code is correct
		$SQL = "SELECT debtorno,branchcode,salesman FROM custbranch WHERE branchcode='".$client['branchcode']."'";
		$res = mysqli_query($db, $SQL);

		if(mysqli_num_rows($res) != 1){
			echo json_encode(['status' => 'error', 'message' => 'Problem with branchcode']);
			return;
		}

		$customer = mysqli_fetch_assoc($res);

	}

	$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='".$customer['salesman']."'";
	$customer['salesman'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['salesmanname'];

	if($payment == "csv"){
		$customer['salesman'] = $_POST['salesman'];
	}

	$selectedDebtorNo = $customer['debtorno'];

	$SQL = "SELECT dueDays, paymentExpected FROM debtorsmaster WHERE debtorno = '$selectedDebtorNo'";
	$debtorsMaster = mysqli_fetch_assoc(mysqli_query($db, $SQL));
	$cDate = date('Y-m-d');
	$dueDays = date('Y-m-d', strtotime(" + ".$debtorsMaster['dueDays']." days"));
	$expectedDays = date('Y-m-d', strtotime(" + ".$debtorsMaster['paymentExpected']." days"));

	//Create Bill Here
	$billId = GetNextTransNo(750,$db);
	$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

	$SQL = "INSERT INTO shopsale(orderno, debtorno, branchcode, orddate, payment, salesman, advance,
						crname,discount,created_by,dispatchedvia,customerref,paid,discountPKR,accounts,due,expected) 
			VALUES ('$billId','".$customer['debtorno']."','".$customer['branchcode']."','".date('Y-m-d')."',
					'$payment','".$customer['salesman']."','".$advance."','".htmlentities($crname,ENT_QUOTES)."',
					'".$discount."','".$_SESSION['UsersRealName']."','".htmlentities($dispatched,ENT_QUOTES)."','".htmlentities($creferance,ENT_QUOTES)."','".$paid."','".$discountPKR."',0,'$dueDays','$expectedDays')";

	if(!DB_query($SQL,$db)){

		echo json_encode([
			'status' => 'error',
			'message' => 'Bill Creation Failed'
		]);
		return;

	}

	$totalValue = 0;
	
	foreach ($items as $item) {

		$item['desc'] = str_replace("&#10;", "<br>", $item['desc']);
		$item['note'] = str_replace("&#10;", "<br>", $item['note']);

		$item['desc'] = htmlentities($item['desc'],ENT_QUOTES);
		$item['note'] = htmlentities($item['note'],ENT_QUOTES);
		
		$totalValue += $item['price'] * $item['quantity'];
		
		$SQL = "INSERT INTO shopsalelines(orderno, description, notes, quantity, price, uom) 
				VALUES ('$billId','".$item['desc']."','".$item['note']."','".$item['quantity']."',
						'".$item['price']."','".$item['uom']."')";

		if(!DB_query($SQL, $db)){

			echo json_encode([
				'status' => 'error',
				'message' => 'Line Creation Failed'
			]);
			return;

		}

	}

	DB_Txn_Commit($db);

	$data = [];
	$data['module'] = "shopsale";
	$data['code'] = $billId;

	QRcode::png(json_encode($data), '../../../qrcodes/shopsale/'.$billId.'-shopSaleQR.png', 'L', 14, 2); 
		
	echo json_encode(['status' => 'success','code' => $billId]);

	return;