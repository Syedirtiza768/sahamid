<?php

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db,"finalize_outward_market_slip")){ 
		echo json_encode([
				'status' => 'error',
				'message' => 'Permission Denied!'
			]);
		return;
	}

	if(!isset($_POST['parchino']) || trim($_POST['parchino']) == ""){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parameters'
			]);
		return;
	}

	$parchi = trim($_POST['parchino']);

	$SQL = "SELECT * FROM bazar_parchi WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0 AND type=602";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Parchi Not Found or already saved.',

			]);
		return;

	}

	$parchiDetails = mysqli_fetch_assoc($res);

	if($parchiDetails['svid'] == ''){
		echo json_encode([
				'status' => 'error',
				'message' => 'Orignal Client Not Attached.',
			]);
		return;
	}

	$SQL = "SELECT * FROM bpitems WHERE deleted_at IS NULL AND stockid='' AND parchino='".$parchi."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) > 0){
		echo json_encode([
				'status' => 'error',
				'message' => 'Items without orignal SKU found.',
			]);
		return;
	}

	$salesperson = $parchiDetails['on_behalf_of']; 

	$SQL = "SELECT * FROM bpitems WHERE deleted_at IS NULL AND parchino='".$parchi."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) <= 0){

		echo json_encode([

				'status' => 'error',
				'message' => 'Items Not Found(Should be discarded)',

			]);
		return;

	}

	$totalValue = 0;
	$error = 0;

	$count = 1;
	$items = [];

	while($row = mysqli_fetch_assoc($res)){

		$row['index'] = $count;
		$items[] = $row;

		if($row['quantity_received'] <= 0){
			echo json_encode([
					'status' => 'error',
					'message' => 'Item with 0 Quantity Found.'
				]);
			$error = 1;
			break;
		}

		$stockid = $row['stockid'];
		$SQL = "SELECT issued,dc FROM stockissuance WHERE stockid='$stockid'AND salesperson='$salesperson'";
		$qtyRes = mysqli_query($db, $SQL);
		$qtyRes = mysqli_fetch_assoc($qtyRes);
		$issued = $qtyRes['issued'];

		if($issued < $row['quantity_received']){
			echo json_encode([
					'status' => 'error',
					'message' => "Quantity issued for item $stockid is less then quantity required."
				]);
			$error = 1;
			break;
		}

		if($row['price'] <= 0){

			echo json_encode([

					'status' => 'error',
					'message' => 'Item with Price 0 Found.'

				]);
			$error = 1;
			break;

		}

		$totalValue += ($row['quantity_received'] * $row['price']);
		$count++;

	}

	if($error == 1){
		return;
	}

	$SQL = "SELECT SUM(amount) as advance FROM bpledger WHERE parchino = '".$parchiDetails['parchino']."'";
	$res = mysqli_query($db,$SQL);

	$advance = mysqli_fetch_assoc($res)['advance'] ?:0;

	if(($advance) > ($totalValue+2)){
		echo json_encode([
				'status' => 'error',
				'message' => 'Advance paid is greater than the slip value'
			]);
		return;
	}

	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

	$SQL = "SELECT * FROM bpitems WHERE deleted_at IS NULL AND parchino='".$parchi."'";
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		$quantity_received = $row['quantity_received'];
		$stockid 		   = $row['stockid']; 

		$SQL = "UPDATE `stockissuance` 
				SET issued=(issued-$quantity_received),dc=(dc+$quantity_received) 
				WHERE salesperson='".$salesperson."'
				AND stockid='".$stockid."'";
		DB_query($SQL, $db);
		
		// $SQL = "UPDATE `ogpmporef` 
		// 		SET quantity=(quantity-$quantity_received)
		// 		WHERE salesman='".$salesperson."'
		// 		AND stockid='".$stockid."'
		// 		AND mpo = '". $parchi ."'";
		// DB_query($SQL, $db);

		// 1. First, get the sum of all quantities for this salesman/stockid/mpo combination
	$sumSQL = "SELECT SUM(quantity) as total_quantity 
           FROM ogpmporef 
           WHERE salesman = '" . $salesperson . "'
             AND stockid = '" . $stockid . "'
             AND mpo = '" . $parchi . "'
			 AND quantity IS NOT NULL
			AND quantity > 0";
	$sumResult = DB_query($sumSQL, $db);
	$sumRow = DB_fetch_array($sumResult);
	$totalQuantity = $sumRow['total_quantity'];

	// Handle NULL case (if no records found)
	if ($totalQuantity === null) {
		$totalQuantity = 0;
	}

	// 2. Calculate the new quantity after deduction
	$newQuantity = $totalQuantity - $quantity_received;

	// 3. Set all existing records to NULL first
	$nullifySQL = "UPDATE ogpmporef 
               SET quantity = NULL 
               WHERE salesman = '" . $salesperson . "'
                 AND stockid = '" . $stockid . "'
                 AND mpo = '" . $parchi . "'
				 AND quantity IS NOT NULL
			AND quantity > 0";
	DB_query($nullifySQL, $db);
		
        $SQL = "SELECT locstock.quantity FROM locstock
						WHERE locstock.stockid='".$stockid."' 
						AND loccode='".$_SESSION['UserStockLocation']."'";
        $ResultQ = DB_query($SQL, $db);
        $QtyOnHandPrior = 0;
        if (DB_num_rows($ResultQ)==1){
            $LocQtyRow = DB_fetch_row($ResultQ);
            $QtyOnHandPrior = $LocQtyRow[0];
        }

		$SQL = "INSERT INTO stockmoves(stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
				VALUES ('".$stockid."','602','".$parchiDetails['transno']."','".$_SESSION['UserStockLocation']."','".Date('Y-m-d')."',
					'"._('Delivered To').' '.$parchiDetails['parchino']."','".$quantity_received."',
					'".$PeriodNo."','".$QtyOnHandPrior."')";
		DB_query($SQL, $db);

	}

	$SQL = "SELECT debtorno FROM custbranch WHERE branchcode='".$parchiDetails['svid']."'";
	$res = mysqli_query($db, $SQL);

	$debtorno 	= mysqli_fetch_assoc($res)['debtorno'];
	$branchcode = $parchiDetails['svid'];
	$cDate 	 	= date('Y-m-d H-i-s');
	$URN   	 	= $_SESSION['UsersRealName'];
	$transno 	= $parchiDetails['transno'];
	$obo 		= $parchiDetails['on_behalf_of'];

	DB_Txn_Begin($db);

		$SQL = "UPDATE bazar_parchi 
				SET inprogress=0,
					amount='".$totalValue."',
					updated_at='".date("Y-m-d H:i:s")."'  
				WHERE parchino='".$parchi."'";
		DB_query($SQL, $db);

		$SQL = "INSERT INTO debtortrans (transno, type, debtorno, branchcode, trandate, inputdate, prd, reference,
							tpe, order_, ovamount, WHTamt, GSTamt, GSTtotalamt, ovgst, ovfreight, rate, invtext,
							shipvia, consignment, packages, salesperson, processed )
				VALUES 		('$transno', 602, '$debtorno', '$branchcode', '$cDate', '$cDate', '$PeriodNo', '', '', '', 
							'$totalValue', 0, 0, 0, '', '', '1', 'Default', '', '', '', '$obo','-1')";
		DB_query($SQL, $db);

		$paymentToID = $_SESSION['LastInsertId'];

		$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
				VALUES (602,'".$parchiDetails['transno']."','".date('Y-m-d H-i-s')."',
						'".$PeriodNo."',1,'Outward ParchiNo ".$parchiDetails['parchino']."','".(-1*$totalValue)."')";
		DB_query($SQL, $db);

		$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
				VALUES (602,'".$parchiDetails['transno']."','".date('Y-m-d H-i-s')."',
						'".$PeriodNo."',1100,'Outward ParchiNo ".$parchiDetails['parchino']."','".($totalValue)."')";
		DB_query($SQL, $db);

		if($advance > 0){

			$receiptno 	= GetNextTransNo(12, $db);

			$SQL = "INSERT INTO debtortrans (transno, type, debtorno, branchcode, trandate, inputdate, prd,
								reference, tpe, rate, ovamount, ovdiscount, invtext, salesperson, chequefilepath,
								chequedepositfilepath, cashfilepath, bankaccount )
					VALUES 		('$receiptno',12, '$debtorno','$branchcode','$cDate','$cDate', '$PeriodNo', 
								'Advance Entry for MPO $transno','',1,'" . (-1*$advance) . "',0,
								'Advance Cash','$URN','','','',1034)";
			DB_query($SQL,$db);

			$paymentFromID = $_SESSION['LastInsertId'];

			$SQL = "UPDATE debtorsmaster
					SET lastpaiddate ='".date('Y-m-d H-i-s')."',
					lastpaid=$advance
					WHERE debtorsmaster.debtorno='".$debtorno."'";
			DB_query($SQL,$db);

			$SQL = "INSERT INTO banktrans (type, transno, bankact, ref, exrate, functionalexrate,
								transdate, banktranstype, amount, currcode)
					VALUES (12, '$receiptno', 1034, 'Advance Entry for MPO $transno', 1, 1,
							'".date('Y-m-d H-i-s')."', 'Cash', '" . $advance . "', 'PKR')";
			DB_query($SQL,$db);

			$SQL = "INSERT INTO custallocns ( datealloc, amt, transid_allocfrom, transid_allocto ) 
					VALUES ('".date('Y-m-d')."','$advance', '$paymentFromID', '$paymentToID')";
			DB_query($SQL,$db);

			$toPaymentSettled = (abs($totalValue - $advance) < 3) ? 1:0;

			$SQL = "UPDATE debtortrans 
					SET alloc='$advance',
						settled=$toPaymentSettled,
						processed=$paymentFromID
					WHERE id='$paymentToID'";
			DB_query($SQL,$db);

			$SQL = "UPDATE debtortrans 
					SET settled=1, 
						alloc='".(-1*$advance)."'
					WHERE id='$paymentFromID'";
			DB_query($SQL,$db);

			/*$SQL = "INSERT INTO gltrans (type, typeno, trandate, periodno, account, narrative, amount)
					VALUES (602, '$transno', '$cDate', '$PeriodNo', 1100, 'Advance Entry for MPO $transno','".(-1*$advance)."')";
			DB_query($SQL,$db);*/

			$SQL = "INSERT INTO gltrans (type, typeno, trandate, periodno, account, narrative, amount)
					VALUES (602,'$transno','$cDate','$PeriodNo', 1034,'Advance Entry for MPO $transno','$advance')";
			DB_query($SQL,$db);

		}

	DB_Txn_Commit($db);

	echo json_encode([

			'status' => 'success',
			'message' => 'Saved Successfully.'

		]);