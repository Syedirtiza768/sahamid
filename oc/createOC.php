<?php

	$PathPrefix='../';

	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');
	include('misc.php');

	$RootPath = explode("/oc",$RootPath)[0];

	if(!(isset($_GET['salescaseref']) && isset($_GET['DebtorNo']) 
		&& isset($_GET['BranchCode']) && isset($_GET['selectedcustomer']))){

		returnErrorResult($RootPath,$salescaseref, "Missing Parameters");
		exit;

	}

	$salescaseref = $_GET['salescaseref'];
	$debtorNo 	  = $_GET['DebtorNo'];
	$branchCode   = $_GET['BranchCode'];
	$selectedcustomer = $_GET['selectedcustomer'];

	if(isIncorrectSalesCase($salescaseref)){

		returnErrorResult($RootPath,$salescaseref, "Invalid Salescase");
		exit;

	}

	$db = createDBConnection();

	if(isset($_GET['NewOrder']) && isset($_GET['pono'])){

		$SQL = "SELECT orderno FROM ocs 
				WHERE salescaseref='".$salescaseref."'
				AND pono='".$_GET['pono']."'";

		$result = mysqli_query($db, $SQL);

		if(mysqli_num_rows($result) > 0){

			returnErrorResult($RootPath,$salescaseref, "OC already created.");
			exit;

		}

	}

	$SQL = "SELECT max(orderno) as orderno from salesorders 
			where salescaseref='".$salescaseref."'";

	$result = mysqli_query($db, $SQL);

	$salesOrderNo = mysqli_fetch_assoc($result)['orderno'];

	//Make OC from existing Quotation
	if(isset($_GET['NewOrder']) && $salesOrderNo != null){

		if(!(isset($_GET['pono']) && isset($_GET['ocref']))){

			returnErrorResult($RootPath,$salescaseref, "OC cannot be created.");
			exit;

		}

		//Sales Order
		$SQL = "SELECT * FROM salesorders WHERE orderno='".$salesOrderNo."'";

		$result = mysqli_query($db, $SQL);

		if(mysqli_num_rows($result) != 1){

			returnErrorResult($RootPath,$salescaseref, "Quotation not found");
			exit;

		}

		$so = mysqli_fetch_assoc($result);
		
		utf8_replace_array($so);

		$ocOrderNo = GetNextTransNo(60,$db);

		$SQL = "INSERT INTO `ocs`(inprogress,quotationno,orderno,orddate,`salescaseref`,`debtorno`,
				`branchcode`, `customerref`, `buyername`, `comments`, 
				`ordertype`, `shipvia`, `deladd1`, `deladd2`, `deladd3`,
				`deladd4`, `deladd5`, `deladd6`, `contactphone`, 
				`contactemail`, `deliverto`, `deliverblind`, `freightcost`, 
				`advance`, `delivery`, `commisioning`, `after`, `gst`, 
				`afterdays`, `fromstkloc`, `deliverydate`,`confirmeddate`,
				`printedpackingslip`, `datepackingslipprinted`, `quotation`,
				`quotedate`, `poplaced`, `salesperson`,`GSTadd`,`services`,`WHT`) 
				VALUES ('1','".$salesOrderNo."','".$ocOrderNo."','".date('Y-m-d H:m:s')."','".$so['salescaseref']."','".$so['debtorno']."',
				'".$so['branchcode']."','".$so['customerref']."','".$so['buyername']."','".$so['comments']."',
				'".$so['ordertype']."','".$so['shipvia']."','".$so['deladd1']."','".$so['deladd2']."','".$so['deladd3']."',
				'".$so['deladd4']."','".$so['deladd5']."','".$so['deladd6']."','".$so['contactphone']."',
				'".$so['contactemail']."','".$so['deliverto']."','".$so['deliverblind']."','".$so['freightcost']."',
				'".$so['advance']."','".$so['delivery']."','".$so['commisioning']."','".$so['after']."','".$so['gst']."',
				'".$so['afterdays']."','".$so['fromstkloc']."', '".$so['deliverydate']."','".$so['confirmeddate']."',
				'".$so['printedpackingslip']."','".$so['datepackingslipprinted']."','1',
				'".$so['quotedate']."','".$so['poplaced']."','".$so['salesperson']."','".$so['GSTadd']."','".$so['services']."','".$so['WHT']."')";

		$result = mysqli_query($db, $SQL);

		//Sales Order Lines
		$SQL = "SELECT `orderno`, `lineno`, `clientrequirements` FROM salesorderlines
				WHERE orderno = ".$salesOrderNo."";

		$result = mysqli_query($db, $SQL);

		while($row = mysqli_fetch_assoc($result)){

			utf8_replace_array($row);

			$SQL = "INSERT INTO `oclines`(`orderno`,`lineno`,`clientrequirements`)
					VALUES ('".$ocOrderNo."','".$row['lineno']."','".$row['clientrequirements']."')";

			mysqli_query($db, $SQL);

		}

		//Sales Order Options
		$SQL = "SELECT  `orderno`, `lineno`, `optionno`, `optiontext`, `stockstatus`,`quantity` 
				FROM salesorderoptions WHERE orderno = ".$salesOrderNo."";

		$result = mysqli_query($db, $SQL);

		while($row = mysqli_fetch_assoc($result)){
			
			utf8_replace_array($row);

			$SQL = "INSERT INTO `ocoptions`
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
		`poline` FROM salesorderdetails
		where orderno = ".$salesOrderNo."";

		$result = mysqli_query($db, $SQL);

		while($sod = mysqli_fetch_assoc($result)){
			
			utf8_replace_array($sod);

			$SQL = "INSERT INTO `ocdetails` (`orderlineno`, `orderno`, `lineoptionno`, 
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


	} else if(isset($_GET['NewOrder']) && $salesOrderNo == null){

		$SQL = "SELECT custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
				custbranch.specialinstructions,
				custbranch.estdeliverydays,
				custbranch.salesman
			FROM custbranch
			WHERE custbranch.branchcode='".$branchCode."'
			AND custbranch.debtorno = '".$debtorNo."'";

		$customerInfoResult = mysqli_query($db, $SQL);

		if(mysqli_num_rows($customerInfoResult) == 0){

			returnErrorResult($RootPath,$salescaseref, "Customer Info not found");
			exit;

		}

		$customerInfo = mysqli_fetch_assoc($customerInfoResult);

		$ocOrderNo = GetNextTransNo(60,$db);

		$SQL = "INSERT INTO `ocs`(`inprogress`,`quotation`,`orderno`,`salescaseref`, `debtorno`, `branchcode`, `buyername`,`fromstkloc`,`deladd1`,`deladd2`,`deladd3`,`deladd4`,`deladd5`,`deladd6`,`deliverto`,`contactphone`,`contactemail`,`shipvia`,`deliverblind`,`afterdays`,`salesperson`,`quotedate`,`confirmeddate`,`deliverydate`) 
				VALUES ('0','1','".$ocOrderNo."','".$salescaseref."','".$debtorNo."', '".$branchCode."','".$selectedcustomer."','MT','".$customerInfo['braddress1']."','".$customerInfo['braddress2']."','".$customerInfo['braddress3']."','".$customerInfo['braddress4']."','".$customerInfo['braddress5']."','".$customerInfo['braddress6']."','".$customerInfo['brname']."','".$customerInfo['phoneno']."','".$customerInfo['email']."','".$customerInfo['defaultshipvia']."','".$customerInfo['deliverblind']."','".$customerInfo['estdeliverydays']."','".$customerInfo['salesman']."','".date('Y-m-d')."','".date('Y-m-d')."','".date('Y-m-d')."')";

		$result = mysqli_query($db, $SQL);

		if(!$result){

			returnErrorResult($RootPath,$salescaseref, "OC create failed");
			exit;

		}

		$SQL = "INSERT INTO `oclines`(`orderno`, `lineno`) 
				VALUES ('".$ocOrderNo."','0')";

		mysqli_query($db, $SQL);

		$SQL = "INSERT INTO `ocoptions`(`orderno`, `optionno`, `lineno`) 
				VALUES ('".$ocOrderNo."','0','0')";

		mysqli_query($db, $SQL);

		returnSuccessResult($RootPath, $salescaseref, "New OC created successfully");
		exit;

	}

	if(isset($_GET['pono']) && isset($_GET['ocref'])){

		$SQL = "UPDATE ocs SET pono = '".$_GET['pono']."' 
				WHERE orderno = ".$ocOrderNo." AND pono = ''";
		
		mysqli_query($db, $SQL);

		$SQL = "UPDATE ocdetails SET pono = '".$_GET['pono']."'  
		WHERE orderno = ".$ocOrderNo." AND pono = ''";

		mysqli_query($db, $SQL);

		$SQL = "UPDATE oclines SET pono = '".$_GET['pono']."' 
		WHERE orderno = ".$ocOrderNo." AND pono = ''";

		mysqli_query($db, $SQL);

		$SQL = "UPDATE ocoptions SET pono = '".$_GET['pono']."'  
		WHERE orderno = ".$ocOrderNo." AND pono = ''";
		
		mysqli_query($db, $SQL);

	}

	returnSuccessResult($RootPath, $salescaseref, "New OC created successfully from EQ");
	exit;

?>