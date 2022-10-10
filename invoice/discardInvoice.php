<?php

	if (!isset($PathPrefix)) {
		$PathPrefix='../';
	}
	
	include('misc.php');
	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');

	$invoiceNo = $_GET['invoice'];

	$db = createDBConnection();

	$SQL = "SELECT * FROM invoice WHERE invoiceno='".$invoiceNo."' AND inprogress=1";
	$res = mysqli_query($db, $SQL);
	$cou = mysqli_num_rows($res);

	if($cou <= 0){
		header("Location: inprogressinvoice.php");
		return;
	}

	$SQL = "DELETE FROM invoice WHERE invoiceno='".$invoiceNo."'";
	mysqli_query($db, $SQL);

	$SQL = "DELETE FROM invoicelines WHERE invoiceno='".$invoiceNo."'";
	mysqli_query($db, $SQL);

	$SQL = "DELETE FROM invoiceoptions WHERE invoiceno='".$invoiceNo."'";
	mysqli_query($db, $SQL);

	$SQL = "DELETE FROM invoicedetails WHERE invoiceno='".$invoiceNo."'";
	mysqli_query($db, $SQL);

	header("Location: inprogressinvoice.php");
	return;