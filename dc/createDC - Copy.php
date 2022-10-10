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

	echo $SQL = "SELECT max(orderno) as orderno from salesorders 
			where salescaseref='".$salescaseref."'";

	$result = mysqli_query($db, $SQL);

	$salesOrderNo = mysqli_fetch_assoc($result)['orderno'];

	//Make OC from existing Quotation
	if($salesOrderNo == null){

		returnErrorResult($RootPath,$salescaseref, "Quotation Not Found");
		
	}

	$SQL = "SELECT * FROM salesorders 
			WHERE orderno='".$salesOrderNo."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		returnErrorResult($RootPath,$salescaseref, "Quotation Not Found2");

	}

	$q = mysqli_fetch_assoc($result);

	$newDCNo = GetNextTransNo(512,$db); 

	$SQL = "INSERT INTO `dcs`(`orderno`, `salescaseref`, `debtorno`, `branchcode`, `customerref`, `orddate`, `shipvia`, `deladd1`, `deladd2`, `deladd3`, `deladd4`, `deladd5`, `deladd6`, `deliverto`, `deliverblind`, `gst`, `fromstkloc`, `deliverydate`, `confirmeddate`, `quotation`, `quotedate`, `inprogress`) 
		VALUES ('".str_replace("'","\'",$newDCNo)."','".str_replace("'","\'",$salescaseref)."','".str_replace("'","\'",$debtorNo)."','".str_replace("'","\'",$branchCode)."',
		'".str_replace("'","\'",$q['customerref'])."','".str_replace("'","\'",date('Y-m-d'))."',1,'".str_replace("'","\'",$q['deladd1'])."','".str_replace("'","\'",$q['deladd2'])."','".str_replace("'","\'",$q['deladd3'])."',
		'".str_replace("'","\'",$q['deladd4'])."','".str_replace("'","\'",$q['deladd5'])."','".str_replace("'","\'",$q['deladd6'])."','".str_replace("'","\'",$q['deliverto'])."','".str_replace("'","\'",$q['deliverblind'])."',
		'".str_replace("'","\'",$q['gst'])."','".$q['fromstkloc']."','".date('Y-m-d')."','".date('Y-m-d')."','".$q['quotation']."',
		'".date('Y-m-d')."',0)";


	$result = mysqli_query($db, $SQL);

	if(!$result){

		returnErrorResult($RootPath,$salescaseref, "DC insert failed");

	}

	header('Location: '.$RootPath."/dc/makedc.php?salescaseref=".$salescaseref."&ModifyOrderNumber=".$newDCNo);
	exit;

?>