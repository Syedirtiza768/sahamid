<?php 

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$salescaseref = $_POST['salescaseref'];
	$db = createDBConnection();

	if(isset($_POST['closesalescase'])){

		$SQL = 'UPDATE salescase SET closingremarks="",
				closingreason="Case Closed",
				stage="DC",
				closingdate="'.date('Y-m-d H:i:s').'",
				closed=1						
				WHERE salescaseref="'.$salescaseref.'"';

	}else{

		$SQL = 'UPDATE salescase SET closingremarks = "'.$_POST['closingremarks'].'",
				closingreason = "'.$_POST['caseclosereason'].'",
				stage = "'.$_POST['stage'].'",
				closingdate = "'.date('Y-m-d H:i:s').'",
				closed = 1						
				WHERE salescaseref = "'.$salescaseref.'"';

	}

	mysqli_query($db, $SQL);

	header("Location: ".$_SERVER['HTTP_REFERER']);