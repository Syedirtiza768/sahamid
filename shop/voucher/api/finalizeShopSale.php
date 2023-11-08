<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db, 'finalize_shopsale')){
		
		echo json_encode([

				'status' => 'error',
				'message' => 'Permission Denied.'

			]);
		return;
		
	}

	if(!isset($_POST['orderno'])){

		echo json_encode([

				'status' => 'error',
				'message' => 'missing parms'

			]);
		return;

	}

	$orderno = trim($_POST['orderno']);

	$SQL = "SELECT * FROM shopsale WHERE orderno=$orderno AND complete=0";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){

		echo json_encode([

				'status' => 'error',
				'message' => 'Not Found or Already Saved.'

			]);
		return;

	}

	$shopSale = mysqli_fetch_assoc($res);
	
	$SQL = "SELECT * 
			FROM shopsalelines 
			LEFT OUTER JOIN shopsalesitems ON (shopsalesitems.orderno = shopsalelines.orderno
				AND shopsalesitems.lineno = shopsalelines.id)
			WHERE shopsalesitems.id IS NULL
			AND shopsalesitems.orderno=$orderno";
	$res = mysqli_query($db, $SQL);
	
	if(mysqli_num_rows($res) > 0){

		echo json_encode([

				'status' => 'error',
				'message' => 'Lines Without items found.'

			]);
		return;

	}

	$SQL = "SELECT * FROM shopsalesitems WHERE orderno=$orderno";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) <= 0){

		echo json_encode([

				'status' => 'error',
				'message' => 'Items Not Found.'

			]);
		return;

	}

	$salesman = $shopSale['salesman'];

	while($row = mysqli_fetch_assoc($res)){

		if($row['quantity'] <= 0){

			echo json_encode([

					'status' => 'error',
					'message' => 'Items with 0 or less quantity found.'

				]);
			return;

		}

		$stockid = $row['stockid'];

		$SQL = "SELECT SUM(shopsalesitems.quantity * shopsalelines.quantity) as required
				FROM shopsalesitems 
				INNER JOIN shopsalelines ON (shopsalelines.orderno = shopsalesitems.orderno
					AND shopsalelines.id = shopsalesitems.lineno)
				WHERE shopsalesitems.orderno=$orderno
				AND shopsalesitems.stockid='$stockid'";
		$requiredQuantity = mysqli_fetch_assoc(mysqli_query($db, $SQL))['required'];
		$requiredQuantity = ($requiredQuantity != "") ? $requiredQuantity:0;

		$SQL = "SELECT issued
				FROM stockissuance 
				WHERE stockid='$stockid'
				AND salesperson='$salesman'";
		$issuedQuantity = mysqli_fetch_assoc(mysqli_query($db, $SQL))['issued'];
		$issuedQuantity = ($issuedQuantity != "") ? $issuedQuantity:0;

		

		if($requiredQuantity < 5426565) {

			echo json_encode([

					'status' => 'error',
					'message' => 'Cart quantity for item ('.$stockid.') is '.$issuedQuantity.', required is '.$requiredQuantity

				]);
			return;

		}

	}

	$SQL = "SELECT defaultlocation FROM www_users WHERE realname='$salesman'";
	$location = mysqli_fetch_assoc(mysqli_query($db, $SQL))['defaultlocation'];

	$SQL = "SELECT SUM(shopsalesitems.quantity * shopsalelines.quantity) as required, shopsalesitems.stockid
			FROM shopsalesitems 
			INNER JOIN shopsalelines ON (shopsalelines.orderno = shopsalesitems.orderno
				AND shopsalelines.id = shopsalesitems.lineno)
			WHERE shopsalesitems.orderno=$orderno
			GROUP BY shopsalesitems.stockid";
	$res = mysqli_query($db, $SQL);

	$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);

	while($item = mysqli_fetch_assoc($res)){

		$SQL = "SELECT issued,dc FROM stockissuance 
				WHERE stockid='".$item['stockid']."'
				AND salesperson='".$salesman."'";
		$quantityRes = mysqli_fetch_assoc(mysqli_query($db, $SQL));

		$issued = $quantityRes['issued'];
		$dc 	= $quantityRes['dc'];

		$newIssued 	= $issued - $item['required'];
		$newDC 		= $dc + $item['required'];

		$SQL = "UPDATE `stockissuance` SET issued='".$newIssued."',dc='".$newDC."' 
				WHERE salesperson='".$salesman."'
				AND stockid='".$item['stockid']."'";
		DB_query($SQL, $db);

		$SQL = "INSERT INTO stockmoves(stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
				VALUES ('".$item['stockid']."','750','".$orderno."','".$location."','".Date('Y-m-d')."',
						'Delivered To ".$shopSale['payment']." (Cart: $salesman)','".$item['required']."',
						'".$PeriodNo."','".$newIssued."')";
		DB_query($SQL, $db);

	}	

	$failedSQL = [];

	if($shopSale['accounts'] == 0) {

		$SQL = "SELECT SUM(quantity*price) as total 
				FROM shopsalelines
				WHERE orderno=$orderno";
		$res = mysqli_query($db, $SQL);		

		$totalValue = mysqli_fetch_assoc($res)['total'];

		$totalValue = $totalValue * (1 - ($shopSale['discount'] / 100));
		$totalValue -= $shopSale['discountPKR'];

		$billId  = $orderno;
		$advance = $shopSale['advance'];
		$debNo 	 = $shopSale['debtorno'];
		$bCode 	 = $shopSale['branchcode'];
		$payment = $shopSale['payment'];
		$cDate 	 = date('Y-m-d H-i-s');
		$URN   	 = $_SESSION['UsersRealName'];

		$SQL = "INSERT INTO debtortrans (transno, type, debtorno, branchcode, trandate, inputdate, prd, reference,
							tpe, order_, ovamount, WHTamt, GSTamt, GSTtotalamt, ovgst, ovfreight, rate, invtext,
							shipvia, consignment, packages, salesperson, processed )
				VALUES 		('$billId', 750, '$debNo', '$bCode', '$cDate', '$cDate', '$PeriodNo', '', '', '', 
							'$totalValue', 0, 0, 0, '', '', '1', 'Default', '', '', '', '$URN','-1')";
		if(!DB_query($SQL,$db)){
			$failedSQL[] = $SQL;
		}

	 	$paymentToID = $_SESSION['LastInsertId'];

	 	if($payment == "csv"){

			$receiptno 	= GetNextTransNo(12,$db);
			$advance 	= $totalValue;
			
			$SQL = "INSERT INTO debtortrans (transno, type, debtorno, branchcode, trandate, inputdate, prd, reference,
							tpe, order_, ovamount, WHTamt, GSTamt, GSTtotalamt, ovgst, ovfreight, rate, invtext,
							shipvia, consignment, packages, salesperson, processed )
					VALUES 		('$receiptno', 12, '$debNo', '$bCode', '$cDate', '$cDate', '$PeriodNo', '', '', '', 
							'".(-1*$totalValue)."', 0, 0, 0, '', '', '1', 'Cash', '', '', '', '$URN','-1')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			$paymentFromID = $_SESSION['LastInsertId'];

			$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
					VALUES (750,'".$billId."','".date('Y-m-d H-i-s')."',
							'".$PeriodNo."',1,'(1) CSV Payment ".$billId."','".(-1 * $totalValue)."')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
					VALUES (750,'".$billId."','".date('Y-m-d H-i-s')."',
							'".$PeriodNo."',1100,'(1100) CSV Payment ".$billId."','".($totalValue)."')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			$SQL = "UPDATE debtorsmaster
					SET lastpaiddate ='".date('Y-m-d H-i-s')."',
					lastpaid=$advance
					WHERE debtorsmaster.debtorno='".$shopSale['debtorno']."'";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			$SQL = "INSERT INTO banktrans (type, transno, bankact, ref, exrate, functionalexrate,
								transdate, banktranstype, amount, currcode)
					VALUES (12, '$receiptno', 1034, 'Advance Entry for CSV $billId', 1, 1,
							'".date('Y-m-d H-i-s')."', 'Cash', '" . $advance . "', 'PKR')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			$SQL = "INSERT INTO custallocns ( datealloc, amt, transid_allocfrom, transid_allocto ) 
					VALUES ('".date('Y-m-d')."','$advance', '$paymentFromID', '$paymentToID')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}
			
			$SQL = "UPDATE debtortrans 
					SET alloc='$advance',
						settled=1,
						processed=$paymentFromID
					WHERE id='$paymentToID'";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}
		
			$SQL = "UPDATE debtortrans 
					SET settled=1, 
						alloc='".(-1*$advance)."'
					WHERE id='$paymentFromID'";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			$SQL = "INSERT INTO gltrans (type, typeno, trandate, periodno, account, narrative, amount)
					VALUES (750, '$billId', '$cDate', '$PeriodNo', 1100, 'Advance Entry for CSV $billId','".(-1*$advance)."')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			$SQL = "INSERT INTO gltrans (type, typeno, trandate, periodno, account, narrative, amount)
					VALUES (750,'$billId','$cDate','$PeriodNo', 1034,'Advance Entry for CSV $billId','$advance')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

		}else if($payment == "crv"){

			$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
					VALUES (750,'".$billId."','".date('Y-m-d H-i-s')."',
							'".$PeriodNo."',1,'(1) CRV Payment ".$billId."','".(-1 * $totalValue)."')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
					VALUES (750,'".$billId."','".date('Y-m-d H-i-s')."',
							'".$PeriodNo."',1100,'(1100) CRV Payment ".$billId."','".($totalValue)."')";
			if(!DB_query($SQL,$db)){
				$failedSQL[] = $SQL;
			}

			if ($advance>0 AND $advance != ''){

				$receiptno = GetNextTransNo(12,$db);

				$SQL = "INSERT INTO debtortrans (transno, type, debtorno, branchcode, trandate, inputdate, prd,
									reference, tpe, rate, ovamount, ovdiscount, invtext, salesperson, chequefilepath,
									chequedepositfilepath, cashfilepath, bankaccount )
						VALUES 		('$receiptno',12, '$debNo','$bCode','$cDate','$cDate', '$PeriodNo', 
									'Advance Entry for CRV $billId','',1,'" . (-1*$advance) . "',0,
									'Advance Cash','$URN','','','',1034)";
				if(!DB_query($SQL,$db)){
					$failedSQL[] = $SQL;
				}

				$paymentFromID = $_SESSION['LastInsertId'];

				$SQL = "UPDATE debtorsmaster
						SET lastpaiddate ='".date('Y-m-d H-i-s')."',
						lastpaid=$advance
						WHERE debtorsmaster.debtorno='".$shopSale['debtorno']."'";
				if(!DB_query($SQL,$db)){
					$failedSQL[] = $SQL;
				}

				$SQL = "INSERT INTO banktrans (type, transno, bankact, ref, exrate, functionalexrate,
									transdate, banktranstype, amount, currcode)
						VALUES (12, '$receiptno', 1034, 'Advance Entry for CRV $billId', 1, 1,
								'".date('Y-m-d H-i-s')."', 'Cash', '" . $advance . "', 'PKR')";
				if(!DB_query($SQL,$db)){
					$failedSQL[] = $SQL;
				}
		
				$SQL = "INSERT INTO custallocns ( datealloc, amt, transid_allocfrom, transid_allocto ) 
						VALUES ('".date('Y-m-d')."','$advance', '$paymentFromID', '$paymentToID')";
				if(!DB_query($SQL,$db)){
					$failedSQL[] = $SQL;
				}

				$toPaymentSettled = (abs($totalValue - $advance) < 3) ? 1:0;		
				
				$SQL = "UPDATE debtortrans 
						SET alloc='$advance',
							settled=$toPaymentSettled,
							processed=$paymentFromID
						WHERE id='$paymentToID'";
				if(!DB_query($SQL,$db)){
					$failedSQL[] = $SQL;
				}

				$SQL = "UPDATE debtortrans 
						SET settled=1, 
							alloc='".(-1*$advance)."'
						WHERE id='$paymentFromID'";
				if(!DB_query($SQL,$db)){
					$failedSQL[] = $SQL;
				}

				/*$SQL = "INSERT INTO gltrans (type, typeno, trandate, periodno, account, narrative, amount)
						VALUES (750, '$billId', '$cDate', '$PeriodNo', 1100, 'Advance Entry for CRV $billId','".(-1*$advance)."')";
				if(!DB_query($SQL,$db)){
					$failedSQL[] = $SQL;
				}*/

				$SQL = "INSERT INTO gltrans (type, typeno, trandate, periodno, account, narrative, amount)
						VALUES (750,'$billId','$cDate','$PeriodNo', 1034,'Advance Entry for CRV $billId','$advance')";
				if(!DB_query($SQL,$db)){
					$failedSQL[] = $SQL;
				}

			}	

		}

	}

	if(count($failedSQL) > 0){
		echo json_encode($failedSQL);
		return;
	}else{
		
		$SQL = "UPDATE shopsale SET complete=1, accounts=1 WHERE orderno=$orderno";
		DB_query($SQL, $db);

		echo json_encode(['status' => 'success']);

	}
