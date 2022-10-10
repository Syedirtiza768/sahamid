<?php

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$response = [];
	
	if(!validHeaders()){

		$response = [
			'status' => 'error',
			'message' => 'Refresh or ReLogin Required.'
		];

		echo json_encode($response);
		return;		

	}

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];

	$db = createDBConnection();
	
	$pass = true;

	$SQL = "SELECT quantity FROM dcoptions WHERE orderno='".$orderno."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){

		echo json_encode([
				'status' => 'error',
				'message' => 'No Line or Option added'
			]);
		return;
	
	}

	while($row = mysqli_fetch_assoc($res)){

		if($row['quantity'] == 0)
			$pass = false;

	}

	if(!$pass){
		echo json_encode([
				'status' => 'error',
				'message' => 'Option with 0 quantity found'
			]);
		return;
	}

	$SQL = "SELECT quantity FROM dcdetails WHERE orderno='".$orderno."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){

		echo json_encode([
				'status' => 'error',
				'message' => 'Option with 0 items found'
			]);
		return;
	
	}

	while($row = mysqli_fetch_assoc($res)){

		if($row['quantity'] == 0)
			$pass = false;

	}

	if(!$pass){
		echo json_encode([
				'status' => 'error',
				'message' => 'Item with 0 quantity found'
			]);
		return;
	}

	$SQL = "UPDATE dcs SET inprogress='1' 
			WHERE orderno='".$orderno."'
			AND salescaseref='".$salescaseref."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Update Failed.'
		];

		echo json_encode($response);
		return;	

	}
	
	$SQL = "SELECT * FROM salesorders 
			WHERE salescaseref='".$salescaseref."'";
	$res = mysqli_query($db, $SQL);
	$qCount = mysqli_num_rows($res);
	
	if($qCount != 0){
		
		closeDBConnection($db);
	
		echo json_encode([
			'status' => 'success',
			'message' => 'Update Without oc quotation.'
		]);
		
		return;
	
	}

	$SQL = "SELECT * FROM dcs WHERE orderno='".$orderno."'";
	$res = mysqli_query($db, $SQL);
	$dc  = mysqli_fetch_assoc($res);

	utf8_replace_array($dc);

	$quotationNo = GetNextTransNo(30, $db);

	$SQL = "INSERT INTO `salesorders`(orderno,orddate,`salescaseref`,`debtorno`,
			`branchcode`, `customerref`, `buyername`, `comments`, 
			`ordertype`, `shipvia`, `deladd1`, `deladd2`, `deladd3`,
			`deladd4`, `deladd5`, `deladd6`, `contactphone`, 
			`contactemail`, `deliverto`, `deliverblind`, `freightcost`, 
			`advance`, `delivery`, `commisioning`, `after`, `gst`, 
			`afterdays`, `fromstkloc`, `deliverydate`,`confirmeddate`,
			`printedpackingslip`, `datepackingslipprinted`, `quotation`,
			`quotedate`, `poplaced`, `salesperson`,`GSTadd`,`services`,`WHT`,`rate_clause`,`rate_validity`) 
			VALUES ('".$quotationNo."','".$dc['orddate']."','".$dc['salescaseref']."',
				'".$dc['debtorno']."','".$dc['branchcode']."','".$dc['customerref']."',
				'".$dc['buyername']."','".$dc['comments']."','".$dc['ordertype']."',
				'".$dc['shipvia']."','".$dc['deladd1']."','".$dc['deladd2']."',
				'".$dc['deladd3']."','".$dc['deladd4']."','".$dc['deladd5']."',
				'".$dc['deladd6']."','".$dc['contactphone']."','".$dc['contactemail']."',
				'".$dc['deliverto']."','".$dc['deliverblind']."','".$dc['freightcost']."',
				'".$dc['advance']."','".$dc['delivery']."','".$dc['commisioning']."',
				'".$dc['after']."','".$dc['gst']."','".$dc['afterdays']."',
				'".$dc['fromstkloc']."', '".$dc['deliverydate']."','".$dc['confirmeddate']."',
				'".$dc['printedpackingslip']."','".$dc['datepackingslipprinted']."','1',
				'".$dc['quotedate']."','".$dc['poplaced']."','".$dc['salesperson']."','".$dc['GSTAdd']."','".$dc['services']."','".$dc['WHT']."','usd','".date('Y-m-d',strtotime(date('Y-m-d')." +15 days"))."'

					)";

	mysqli_query($db, $SQL);
	$SQL = "SELECT max(id) as id FROM exchange_rate";
	$res = mysqli_query($db, $SQL);
	$id = mysqli_fetch_assoc($res)['id'];

	$SQL = "SELECT * FROM exchange_rate WHERE id=$id";
	$res = mysqli_query($db, $SQL);
	$rates = mysqli_fetch_assoc($res);

	$rates = json_encode($rates);

	$SQL = "UPDATE salesorders 
			SET clause_rates = '$rates'
			WHERE orderno = $quotationNo";
	mysqli_query($db, $SQL);
	

	$ocNo = GetNextTransNo(60, $db);

	$SQL = "INSERT INTO `ocs`(inprogress,orderno,orddate,`salescaseref`,`debtorno`,
			`branchcode`, `customerref`, `buyername`, `comments`, 
			`ordertype`, `shipvia`, `deladd1`, `deladd2`, `deladd3`,
			`deladd4`, `deladd5`, `deladd6`, `contactphone`, 
			`contactemail`, `deliverto`, `deliverblind`, `freightcost`, 
			`advance`, `delivery`, `commisioning`, `after`, `gst`, 
			`afterdays`, `fromstkloc`, `deliverydate`,`confirmeddate`,
			`printedpackingslip`, `datepackingslipprinted`, `quotation`,
			`quotedate`, `poplaced`, `salesperson`,`GSTadd`,`services`,`WHT`) 
			VALUES ('1','".$ocNo."','".$dc['orddate']."','".$dc['salescaseref']."',
				'".$dc['debtorno']."','".$dc['branchcode']."','".$dc['customerref']."',
				'".$dc['buyername']."','".$dc['comments']."','".$dc['ordertype']."',
				'".$dc['shipvia']."','".$dc['deladd1']."','".$dc['deladd2']."',
				'".$dc['deladd3']."','".$dc['deladd4']."','".$dc['deladd5']."',
				'".$dc['deladd6']."','".$dc['contactphone']."','".$dc['contactemail']."',
				'".$dc['deliverto']."','".$dc['deliverblind']."','".$dc['freightcost']."',
				'".$dc['advance']."','".$dc['delivery']."','".$dc['commisioning']."',
				'".$dc['after']."','".$dc['gst']."','".$dc['afterdays']."',
				'".$dc['fromstkloc']."', '".$dc['deliverydate']."','".$dc['confirmeddate']."',
				'".$dc['printedpackingslip']."','".$dc['datepackingslipprinted']."','1',
				'".$dc['quotedate']."','".$dc['poplaced']."','".$dc['salesperson']."','".$dc['GSTAdd']."','".$dc['services']."','".$dc['WHT']."')";

	mysqli_query($db, $SQL);

	//Sales DC Lines
	$SQL = "SELECT `orderno`, `lineno`, `clientrequirements` FROM dclines
			WHERE orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($result)){

		utf8_replace_array($row);

		$SQL = "INSERT INTO `salesorderlines`(`orderno`,`lineno`,`clientrequirements`)
				VALUES ('".$quotationNo."','".$row['lineno']."','".$row['clientrequirements']."')";

		mysqli_query($db, $SQL);

		$SQL = "INSERT INTO `oclines`(`orderno`,`lineno`,`clientrequirements`)
				VALUES ('".$ocNo."','".$row['lineno']."','".$row['clientrequirements']."')";

		mysqli_query($db, $SQL);

	}

	$SQL = "SELECT  `orderno`, `lineno`, `optionno`, `optiontext`, `stockstatus`,`quantity` 
			FROM dcoptions WHERE orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($result)){

		utf8_replace_array($row);

		$SQL = "INSERT INTO `salesorderoptions`
							(`orderno`,`lineno`,`optionno`,`optiontext`,`stockstatus`,`quantity`)
				VALUES 		('".$quotationNo."','".$row['lineno']."','".$row['optionno']."',
							'".$row['optiontext']."','".$row['stockstatus']."',
							'".$row['quantity']."')";

		mysqli_query($db, $SQL);

		$SQL = "INSERT INTO `ocoptions`
							(`orderno`,`lineno`,`optionno`,`optiontext`,`stockstatus`,`quantity`)
				VALUES 		('".$ocNo."','".$row['lineno']."','".$row['optionno']."',
							'".$row['optiontext']."','".$row['stockstatus']."',
							'".$row['quantity']."')";

		mysqli_query($db, $SQL);

	}

	$SQL = "SELECT  `orderlineno`, `orderno`, `lineoptionno`, 
	`optionitemno`, `internalitemno`, `stkcode`, `qtyinvoiced`, `unitprice`, `quantity`,
	`estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`,
	`poline` FROM dcdetails
	where orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	while($sod = mysqli_fetch_assoc($result)){

		utf8_replace_array($sod);

		$SQL = "INSERT INTO `salesorderdetails` (`orderlineno`, `orderno`, `lineoptionno`, 
		`optionitemno`, `internalitemno`, `stkcode`, `qtyinvoiced`, `unitprice`, `quantity`,
		`estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`,
		`poline`) VALUES ('".$sod['orderlineno']."','".$quotationNo."',
		'".$sod['lineoptionno']."','".$sod['optionitemno']."',
		'".$sod['internalitemno']."','".$sod['stkcode']."','".$sod['qtyinvoiced']."',
		'".$sod['unitprice']."','".$sod['quantity']."','".$sod['estimate']."',
		'".$sod['discountpercent']."','".$sod['actualdispatchdate']."','".$sod['completed']."',
		'".$sod['narrative']."','".$sod['itemdue']."','".$sod['poline']."')";

		mysqli_query($db, $SQL);

		$SQL = "INSERT INTO `ocdetails` (`orderlineno`, `orderno`, `lineoptionno`, 
		`optionitemno`, `internalitemno`, `stkcode`, `qtyinvoiced`, `unitprice`, `quantity`,
		`estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`,
		`poline`) VALUES ('".$sod['orderlineno']."','".$ocNo."',
		'".$sod['lineoptionno']."','".$sod['optionitemno']."',
		'".$sod['internalitemno']."','".$sod['stkcode']."','".$sod['qtyinvoiced']."',
		'".$sod['unitprice']."','".$sod['quantity']."','".$sod['estimate']."',
		'".$sod['discountpercent']."','".$sod['actualdispatchdate']."','".$sod['completed']."',
		'".$sod['narrative']."','".$sod['itemdue']."','".$sod['poline']."')";

		mysqli_query($db, $SQL);

	}
    $SQL="UPDATE salesorders INNER JOIN salesorderdetails ON salesorders.orderno=salesorderdetails.orderno
            INNER JOIN stockmaster ON salesorderdetails.stkcode=stockmaster.stockid
            SET salesorderdetails.lastcostupdate=stockmaster.lastcostupdate,
            salesorderdetails.lastupdatedby=stockmaster.lastupdatedby
            WHERE salesorders.orderno=$quotationNo
            ";

    mysqli_query($db, $SQL);

	closeDBConnection($db);
	
	echo json_encode([
		'status' => 'success',
		'message' => 'Yo.'
	]);

?>