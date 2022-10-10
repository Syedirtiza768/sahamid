<?php
	
	$AllowAnyone=true;
	$PathPrefix='../../';
	
	include('../../quotation/misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	$user=$_POST['user'];
	$can_access=$_POST['can_access'];
	$value=$_POST['value'];
	if ($value==0){
	    $SQL="DELETE FROM recovery_access WHERE user='$user' AND can_access = '$can_access'";
    }
	else{
	    $SQL="INSERT INTO recovery_access(user,can_access) VALUES ('$user','$can_access')";
    }
	mysqli_query($db,$SQL);

	echo "$SQL";
	//echo "$debtorno --- $dueDays";






?>