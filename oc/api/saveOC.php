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

	$SQL = "SELECT quantity FROM ocoptions WHERE orderno='".$orderno."'";
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

	$SQL = "SELECT quantity FROM ocdetails WHERE orderno='".$orderno."'";
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

	//Sales Order
	$SQL = "SELECT * FROM ocs WHERE orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'OC not found.'
		];

		echo json_encode($response);
		return;	

	}

	$so = mysqli_fetch_assoc($result);
	
	utf8_replace_array($so);

	$ocOrderNo = GetNextTransNo(30,$db);

	$SQL = "INSERT INTO `salesorders`(orderno,orddate,`salescaseref`,`debtorno`,
			`branchcode`, `customerref`, `buyername`, `comments`, 
			`ordertype`, `shipvia`, `deladd1`, `deladd2`, `deladd3`,
			`deladd4`, `deladd5`, `deladd6`, `contactphone`, 
			`contactemail`, `deliverto`, `deliverblind`, `freightcost`, 
			`advance`, `delivery`, `commisioning`, `after`, `gst`, 
			`afterdays`, `fromstkloc`, `deliverydate`,`confirmeddate`,
			`printedpackingslip`, `datepackingslipprinted`, `quotation`,
			`quotedate`, `poplaced`, `salesperson`,`GSTadd`,`services`,`WHT`) 
			VALUES ('".$ocOrderNo."','".$so['orddate']."','".$so['salescaseref']."',
				'".$so['debtorno']."','".$so['branchcode']."','".$so['customerref']."',
				'".$so['buyername']."','".$so['comments']."','".$so['ordertype']."',
				'".$so['shipvia']."','".$so['deladd1']."','".$so['deladd2']."',
				'".$so['deladd3']."','".$so['deladd4']."','".$so['deladd5']."',
				'".$so['deladd6']."','".$so['contactphone']."','".$so['contactemail']."',
				'".$so['deliverto']."','".$so['deliverblind']."','".$so['freightcost']."',
				'".$so['advance']."','".$so['delivery']."','".$so['commisioning']."',
				'".$so['after']."','".$so['gst']."','".$so['afterdays']."',
				'".$so['fromstkloc']."', '".$so['deliverydate']."','".$so['confirmeddate']."',
				'".$so['printedpackingslip']."','".$so['datepackingslipprinted']."','1',
				'".$so['quotedate']."','".$so['poplaced']."','".$so['salesperson']."','".$so['GSTadd']."','".$so['services']."','".$so['WHT']."')";

	$result = mysqli_query($db, $SQL);

	//Sales Order Lines
	$SQL = "SELECT `orderno`, `lineno`, `clientrequirements` FROM oclines
			WHERE orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($result)){
		
		utf8_replace_array($row);

		$SQL = "INSERT INTO `salesorderlines`(`orderno`,`lineno`,`clientrequirements`)
				VALUES ('".$ocOrderNo."','".$row['lineno']."','".$row['clientrequirements']."')";

		mysqli_query($db, $SQL);

	}

	//Sales Order Options
	$SQL = "SELECT  `orderno`, `lineno`, `optionno`, `optiontext`, `stockstatus`,`quantity` 
			FROM ocoptions WHERE orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($result)){
		
		utf8_replace_array($row);

		$SQL = "INSERT INTO `salesorderoptions`
							(`orderno`,`lineno`,`optionno`,`optiontext`,`stockstatus`,`quantity`)
				VALUES 		('".$ocOrderNo."','".$row['lineno']."','".$row['optionno']."',
							'".$row['optiontext']."','".$row['stockstatus']."',
							'".$row['quantity']."')";

		mysqli_query($db, $SQL);

	}

	//Sales Order Details
	$SQL = "SELECT  `orderlineno`, `orderno`, `lineoptionno`, 
	`optionitemno`, `internalitemno`, `stkcode`, `qtyinvoiced`, `unitprice`, `quantity`,
	`estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`,
	`poline` FROM ocdetails
	where orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	while($sod = mysqli_fetch_assoc($result)){
		
		utf8_replace_array($sod);

		$SQL = "INSERT INTO `salesorderdetails` (`orderlineno`, `orderno`, `lineoptionno`, 
		`optionitemno`, `internalitemno`, `stkcode`, `qtyinvoiced`, `unitprice`, `quantity`,
		`estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`,
		`poline`) VALUES ('".$sod['orderlineno']."','".$ocOrderNo."',
		'".$sod['lineoptionno']."','".$sod['optionitemno']."',
		'".$sod['internalitemno']."','".$sod['stkcode']."','".$sod['qtyinvoiced']."',
		'".$sod['unitprice']."','".$sod['quantity']."','".$sod['estimate']."',
		'".$sod['discountpercent']."','".$sod['actualdispatchdate']."','".$sod['completed']."',
		'".$sod['narrative']."','".$sod['itemdue']."','".$sod['poline']."')";

		mysqli_query($db, $SQL);

	}

	$SQL = "UPDATE ocs SET inprogress='1' 
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

	closeDBConnection($db);
	
	echo json_encode([
			'status' => 'success',
			'message' => 'Update Failed.'
		]);

?>