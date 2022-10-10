<?php
	
	$AllowAnyone=true;
	$PathPrefix='../../';
	
	include('../../quotation/misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	$debtorno=$_POST['debtorno'];
	$name=$_POST['name'];
	$value=$_POST['value'];
	$SQL = "UPDATE `debtorsmaster` SET `".$name."`='".$value."' 
			WHERE debtorno='".$debtorno."'";
	//$SQL="UPDATE debtorsmaster SET dueDays = '$dueDays' WHERE debtorno='$debtorno'";
	mysqli_query($db,$SQL);

	echo "$SQL";
	//echo "$debtorno --- $dueDays";






?>