<?php

	if (!isset($PathPrefix)) {
		$PathPrefix='../';
	}
	
	include('misc.php');
	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');

	$groupID = $_GET['groupID'];

	if(!isset($groupID)){
		return;
	}

	$db = createDBConnection();

	$SQL = "SELECT * FROM invoice 
			WHERE groupid='".$groupID."'
			AND inprogress = 1";
	$res = mysqli_query($db, $SQL);
	$count = mysqli_num_rows($res);

	if($count > 0){
		$row = mysqli_fetch_assoc($res);
		$invoiceNo = $row['invoiceno'];
		header("Location: invoice.php?ModifyOrderNumber=".$invoiceNo);
		return;
	}

	$SQL = "SELECT * FROM dcgroups WHERE id='".$groupID."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		return;
	}

	$row = mysqli_fetch_assoc($res);
	$dcnos = $row['dcnos'];
	
	$dcNumbers 	= [];
	$response 	= [];

	foreach (explode(",",$dcnos) as $dcno) {
		
		if(trim($dcno) == '')
			continue;

		$dcNumbers[] = $dcno;

	}

	if(count($dcNumbers) == 0){

		$response['status']  = 'error';
		$response['message'] = 'Group does not contain any DC numbers but how?';

		echo json_encode($response);
		return;

	}

	$SQL = "SELECT * FROM dcs WHERE orderno='".$dcNumbers[0]."'";
	$res = mysqli_query($db, $SQL);
	$inv = mysqli_fetch_assoc($res); 
	$inv['lines'] = []; 

	foreach ($dcNumbers as $dcno) {

		$invLine = [];

		$SQL = "SELECT * FROM dclines WHERE orderno='".$dcno."'";
		$res = mysqli_query($db, $SQL);
		while($lr = mysqli_fetch_assoc($res)){
			$invLine[$lr['lineno']] = $lr;
		}

		$SQL = "SELECT * FROM dcoptions WHERE orderno='".$dcno."'";
		$res = mysqli_query($db, $SQL);

		while($row = mysqli_fetch_assoc($res)){

			if($row['quantity']-$row['qtyinvoiced'] <= 0)
				continue;

			$opt = [];

			$opt[$row['optionno']] = $row;
			$opt[$row['optionno']]['items'] = [];

			$lineNo = $row['lineno'];
			$option = $row['optionno'];

			$SQL = "SELECT * FROM dcdetails 
					WHERE orderno='".$dcno."'
					AND orderlineno='".$lineNo."'
					AND lineoptionno='".$option."'";
			$ires = mysqli_query($db,$SQL);

			while($irow = mysqli_fetch_assoc($ires)){

				$opt[$row['optionno']]['items'][] = $irow;

			}

			$invLine[$row['lineno']]['options'][] = $opt[$row['optionno']];

		}

		$inv['lines'] = array_merge($inv['lines'], $invLine);
		
	}

	$index = -1;
	foreach($inv['lines'] as $line){

		++$index;
		$lineCreated 	= false;
		$optionCreated 	= false;
		if(!isset($line['options'])){
			$line = null;
			unset($inv['lines'][$index]);
			continue;
		}

		foreach ($line['options'] as $option) {
			
			if(!isset($option['items'])){
				$line = null;
				unset($inv['lines'][$index]);
				continue;
			}

		}

	}

	if(count($inv['lines']) == 0){
		
		$response['status']  = 'error';
		$response['message'] = 'Given DC\'s do not contain any lines with options that can be added to invoice.';

		echo json_encode($response);
		return;

	}

	utf8_replace_array($inv);

	$invoiceID = GetNextTransNo(10, $db);

	$SQL = "INSERT INTO `invoice`(`invoiceno`, `groupid`, `salescaseref`, 
	`pono`, `debtorno`, `branchcode`, `customerref`, `buyername`, `comments`,
	`invoicedate`, `ordertype`, `shipvia`, `deladd1`, `deladd2`, `deladd3`, 
	`deladd4`, `deladd5`, `deladd6`, `contactphone`, `contactemail`,
	`deliverto`, `deliverblind`, `freightcost`, `gst`, `fromstkloc`,
	`services`, `salesperson`, `inprogress`, `invoicesdate`, `due`) 
	VALUES ('".$invoiceID."','".$groupID."','".$inv['salescaseref']."',
	'".$inv['pono']."','".$inv['debtorno']."','".$inv['branchcode']."','".$inv['customerref']."',
	'".$inv['buyername']."','".$inv['comments']."','".$inv['invoicedate']."','".$inv['ordertype']."',
	'".$inv['shipvia']."','".$inv['deladd1']."','".$inv['deladd2']."','".$inv['deladd3']."',
	'".$inv['deladd4']."','".$inv['deladd5']."','".$inv['deladd6']."','".$inv['contactphone']."',
	'".$inv['contactemail']."','".$inv['deliverto']."','".$inv['deliverblind']."',
	'".$inv['freightcost']."','','".$inv['fromstkloc']."','".$inv['services']."',
	'".$inv['salesperson']."',1,'".date('Y-m-d')."','".date('Y-m-d')."')";
	
	DB_query($SQL,$db);

	$invoiceLineIndex = 0;
	foreach($inv['lines'] as $line){

		$lineCreated 	= false;
		$optionCreated 	= false;

		if(!$lineCreated){
			
			$SQL = "INSERT INTO `invoicelines`(`orderno`, `invoiceno`, `lineno`, 
					`invoicelineno`, `clientrequirements`)
					VALUES ('".$line['orderno']."','".$invoiceID."','".$line['lineno']."',
					'".$invoiceLineIndex."','".$line['clientrequirements']."')";
			
			DB_query($SQL, $db);

			$lineCreated = true;
		
		}

		$lineOptionIndex = 0;
		foreach ($line['options'] as $option) {

			$sysReq = "";
			
			if(!$optionCreated){
				
				$SQL = "INSERT INTO `invoiceoptions`(`orderno`, `invoiceno`, `lineno`,
				`invoicelineno`, `optionno`,`invoiceoptionno`, `optiontext`, `quantity`) 
				VALUES ('".$option['orderno']."','".$invoiceID."','".$option['lineno']."',
				'".$invoiceLineIndex."','".$option['optionno']."','".$lineOptionIndex."',
				'".$option['optiontext']."','".($option['quantity']-$option['qtyinvoiced'])."')";
				DB_query($SQL, $db);

				$optionCreated = true;

			}

			foreach ($option['items'] as $item) {

				$SQL = "SELECT stockcategory.categorydescription FROM stockmaster 
						INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
						WHERE stockmaster.stockid='".$item['stkcode']."'";
				
				$sysReq .= "<br>".htmlspecialchars(mysqli_fetch_assoc(mysqli_query($db, $SQL))['categorydescription']);

				$SQL="INSERT INTO `invoicedetails`(`orderlineno`, `invoicelineno`, `orderno`, 
				`invoiceno`, `lineoptionno`, `invoiceoptionno`, `stkcode`, 
				`unitprice`, `quantity`, `discountpercent`) 
				VALUES ('".$item['orderlineno']."','".$invoiceLineIndex."','".$item['orderno']."',
				'".$invoiceID."','".$item['lineoptionno']."','".$lineOptionIndex."','".$item['stkcode']."',
				'".$item['unitprice']."','".$item['quantity']."',
				'".$item['discountpercent']."')";

				DB_query($SQL, $db);

			}

			$SQL = "UPDATE `invoicelines` SET `clientrequirements`='".$sysReq."'
					WHERE invoiceno='".$invoiceID."'
					AND invoicelineno='".$invoiceLineIndex."'";
			
			DB_query($SQL, $db);

			$lineOptionIndex++;

		}
		
		$invoiceLineIndex++;

	}

	header("Location: invoice.php?ModifyOrderNumber=".$invoiceID);
