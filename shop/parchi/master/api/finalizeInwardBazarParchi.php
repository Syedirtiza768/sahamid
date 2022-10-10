<?php

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db,"finalize_market_slip")){ 
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

	$SQL = "SELECT * FROM bazar_parchi WHERE parchino='".$parchi."' AND inprogress=1 AND discarded=0 AND settled=0";
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
				'message' => 'Orignal Vendor Not Attached.',

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

	DB_Txn_Begin($db);

		$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

		$SQL = "UPDATE bazar_parchi 
				SET inprogress=0,
					amount='".$totalValue."',
					updated_at='".date("Y-m-d H:i:s")."'  
				WHERE parchino='".$parchi."'";
		DB_query($SQL, $db);

		$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
				VALUES (601,'".$parchiDetails['transno']."','".date('Y-m-d H-i-s')."',
						'".$PeriodNo."',4,'Inward ParchiNo ".$parchiDetails['parchino']."','".($totalValue)."')";
		DB_query($SQL, $db);

		$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
				VALUES (601,'".$parchiDetails['transno']."','".date('Y-m-d H-i-s')."',
						'".$PeriodNo."',2100,'Inward ParchiNo ".$parchiDetails['parchino']."','".(-1*$totalValue)."')";
		DB_query($SQL, $db);

		$SQL = "INSERT INTO `supptrans`(`transno`, `type`, `supplierno`, `trandate`, `inputdate`,`ovamount`)
				VALUES ('".$parchiDetails['transno']."',601,'".$parchiDetails['svid']."','".date('Y-m-d')."',
						'".date('Y-m-d')."','".$totalValue."')";
		DB_query($SQL, $db);

		if($advance != 0){

			$RequestNo 	= GetNextTransNo(22, $db);

			$SQL = "INSERT INTO `banktrans`(`type`, `transno`, `bankact`, `ref`, `exrate`, `functionalexrate`,
			 					`transdate`, `banktranstype`, `amount`, `currcode`) 
			 		VALUES (22,'".$RequestNo."',1034,'adv ".$parchiDetails['parchino']."',1,1,'".date('Y-m-d')."',
			 				'Cash',".(-1*$advance).",'PKR')"; 
			DB_query($SQL, $db);
			
			$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
					VALUES (22,'".$RequestNo."','".date('Y-m-d H-i-s')."',
						'".$PeriodNo."',2100,'Payment to  ".$parchiDetails['svid']."','".($advance)."')";
			DB_query($SQL, $db);

			$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
					VALUES (22,'".$RequestNo."','".date('Y-m-d H-i-s')."',
							'".$PeriodNo."',1034,'Payment to  ".$parchiDetails['svid']."','".(-1*$advance)."')";
			DB_query($SQL, $db);

			$SQL = "INSERT INTO `supptrans`(`transno`, `type`, `supplierno`, `trandate`, `inputdate`,`ovamount`)
					VALUES ('".$RequestNo."',22,'".$parchiDetails['svid']."','".date('Y-m-d')."',
						'".date('Y-m-d')."','".-1*$advance."')";
			DB_query($SQL, $db);	


			$SQL = "UPDATE supptrans 
					SET alloc='".(-1*$advance)."',
						settled=1 
					WHERE type=22
					AND transno=$RequestNo";
			DB_query($SQL, $db);

			$settled = ($totalValue <= $advance+2) ? 1 : 0;

			$SQL = "UPDATE supptrans 
					SET alloc=$advance,
						settled=$settled 
					WHERE type=601
					AND transno='".$parchiDetails['transno']."'";
			DB_query($SQL, $db);

			$SQL = "SELECT id 
				FROM supptrans
				WHERE type=22
				AND transno=$RequestNo";
			$from = mysqli_fetch_assoc(mysqli_query($db,$SQL))['id'];

			$SQL = "SELECT id 
				FROM supptrans
				WHERE type=601
				AND transno='".$parchiDetails['transno']."'";
			$to = mysqli_fetch_assoc(mysqli_query($db,$SQL))['id'];

			$SQL = "INSERT INTO `suppallocs`(`amt`, `datealloc`, `transid_allocfrom`, `transid_allocto`, `date`)
			 		VALUES ('".$advance."','".date('Y-m-d')."','".$from."','".$to."','".date('Y-m-d H:i:s')."')";
			DB_query($SQL,$db);

		}

		if($parchiDetails['igp_created'] == 0){

			//Generate IGP
			$RequestNo 	= GetNextTransNo(38, $db);
			$loc  	 	= $_SESSION['UserStockLocation'];
			$date 	 	= date('Y-m-d');
			$src 		= "From Vendor: ".$parchiDetails['svid'];
			$manager 	= $_SESSION['UsersRealName'];
			$narr 	 	= "Against ParchiNo: ".$parchi;
			
			$SQL = "INSERT INTO igp (dispatchid,loccode,despatchdate,receivedfrom,storemanager,narrative)
					VALUES('".$RequestNo."','".$loc."','".$date."','".$src."','".$manager."','".$narr."')";
			DB_query($SQL, $db);

			foreach ($items as $item) {
				$SQL = "INSERT INTO igpitems (dispatchitemsid,dispatchid,stockid,quantity,comments)
						VALUES('".$item['index']."','".$RequestNo."','".$item['stockid']."','".$item['quantity_received']."','')";
				DB_query($SQL, $db);
				
				$SQL = "SELECT decimalplaces FROM stockmaster WHERE stockid='".$item['stockid']."'";
				$decimal = mysqli_fetch_assoc(mysqli_query($db,$SQL))['decimalplaces'];	

				$SQL = "SELECT locstock.quantity FROM locstock
						WHERE locstock.stockid='".$item['stockid']."' 
						AND loccode='".$loc."'";
				$ResultQ = DB_query($SQL, $db);
				$QtyOnHandPrior = 0;
				if (DB_num_rows($ResultQ)==1){
					$LocQtyRow = DB_fetch_row($ResultQ);
					$QtyOnHandPrior = $LocQtyRow[0];
				}

				$SQL = "INSERT INTO stockmoves (stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
						VALUES ('".$item['stockid']."',510,'".$RequestNo."','".$loc."','".$date."',
								'From ".DB_escape_string($src)."','".round($item['quantity_received'],$decimal)."','".$PeriodNo."',
								'".round($QtyOnHandPrior + $item['quantity_received'],$decimal)."')";

				$ErrMsg =  'CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE : ';
				$ErrMsg .= 'The stock movement record for the incoming stock cannot be added because';
				$DbgMsg =  'The following SQL to insert the stock movement record was used';
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE locstock SET quantity = quantity + '".round($item['quantity_received'], $decimal)."'
						WHERE stockid='".$item['stockid']."'
						AND loccode='" . $loc . "'";

				$ErrMsg = 'CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE : '; 
				$ErrMsg .= 'The location stock record could not be updated because';
				$DbgMsg =  'The following SQL to update the stock record was used';
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

				$substore = 9;

				if($loc == "HO"){
					$substore = 10;
				}else if($loc == "MT"){
					$substore = 5;
				}else if($loc == "SR"){
					$substore = 9;
				}else if($loc == "VSR"){
					$substore = 11;
				}
					
		 		$SQL = "UPDATE substorestock SET quantity = quantity + '".round($item['quantity_received'], $decimal)."'
						WHERE stockid='" . $item['stockid'] . "' AND substoreid='" . $substore . "'";

				$ErrMsg = 'CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE : ';
				$ErrMsg .= 'The location stock record could not be updated because';
				$DbgMsg =  'The following SQL to update the stock record was used';
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
			}

			$SQL = "UPDATE bazar_parchi 
					SET igp_created=1,
						igp_id='".$RequestNo."',
						updated_at='".date("Y-m-d H:i:s")."'  
					WHERE parchino='".$parchi."'";
			DB_query($SQL, $db);


			
		}

	DB_Txn_Commit($db);

	echo json_encode([

			'status' => 'success',
			'message' => 'Saved Successfully.'

		]);