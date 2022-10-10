<?php
	
	$AllowAnyone=true;
	$PathPrefix='../../';
	
	include('../../quotation/misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	$revid=$_POST['revid'];
    $user=$_SESSION['UsersRealName'];
	$value=$_POST['value'];
	$date=date('Y-m-d h:i:s');

	$SQL = "INSERT INTO `reversedAllocationHistoryComments`(`reversedAllocationHistoryID`, `comment`, `user`, `time`) VALUES

            ('$revid','$value','$user','$date')";
	//$SQL="UPDATE debtorsmaster SET dueDays = '$dueDays' WHERE debtorno='$debtorno'";
	mysqli_query($db,$SQL);

	echo "$SQL";
	//echo "$debtorno --- $dueDays";






?>