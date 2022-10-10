<?php

	$PathPrefix='../';

	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');
	include('misc.php');

	$RootPath = explode("/dc",$RootPath)[0];

	if(!(isset($_GET['salescaseref']) && isset($_GET['DebtorNo']) 
		&& isset($_GET['BranchCode']) && isset($_GET['selectedcustomer'])
		&& isset($_GET['NewOrder']))){

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
			WHERE custbranch.branchcode='".$_GET['BranchCode']."'
			AND custbranch.debtorno = '".$_GET['DebtorNo']."'";

	$customerInfoResult = mysqli_query($db, $SQL);

	if(mysqli_num_rows($customerInfoResult) == 0){

		header('Location: '.$RootPath."/salescase.php");
		exit;
		return;

	}

	$customerInfo = mysqli_fetch_assoc($customerInfoResult);
	
	$newDCNo = GetNextTransNo(512,$db); 

	$SQL = "INSERT INTO `dcs`(`orderno`, `salescaseref`, `debtorno`, `branchcode`, `orddate`, `shipvia`, `deladd1`, `deladd2`, `deladd3`, `deladd4`, `deladd5`, `deladd6`, `deliverto`, `fromstkloc`, `deliverydate`, `confirmeddate`, `quotedate`, `inprogress`,`quotation`) 
		VALUES (".$newDCNo.",'".$salescaseref."','".$debtorNo."','".$branchCode."','".date('Y-m-d')."',1,'".addslashes($customerInfo['braddress1'])."','".addslashes($customerInfo['braddress2'])."','".addslashes($customerInfo['braddress3'])."','".addslashes($customerInfo['braddress4'])."','".addslashes($customerInfo['braddress5'])."','".addslashes($customerInfo['braddress6'])."','".addslashes($customerInfo['brname'])."','".addslashes($customerInfo['defaultlocation'])."','".date('Y-m-d')."','".date('Y-m-d')."','".date('Y-m-d')."',0,1)";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		returnErrorResult($RootPath,$salescaseref, "DC insert failed");

	}

	header('Location: '.$RootPath."/dc/makedc.php?salescaseref=".$salescaseref."&ModifyOrderNumber=".$newDCNo);
	exit;

?>