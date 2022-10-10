<?php
	
	$AllowAnyone=true;
	$PathPrefix='../../';
	
	include('../../quotation/misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	$stockid=$_POST['stockid'];
	$name=$_POST['name'];
	$value=$_POST['value'];
	$SQL = "UPDATE stockmaster SET `".$name."`='".$value."' 
			WHERE stockid ='".$stockid."'";
	//$SQL="UPDATE debtorsmaster SET dueDays = '$dueDays' WHERE debtorno='$debtorno'";
	mysqli_query($db,$SQL);

	echo "$SQL";
	//echo "$debtorno --- $dueDays";






?>