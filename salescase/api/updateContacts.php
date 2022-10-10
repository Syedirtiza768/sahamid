<?php
	
	include("../misc.php");

	$salescaseref = $_POST['salescaseref'];
	$contacts = $_POST['contacts'];

	$db = createDBConnection();

	$SQL = "DELETE FROM salescasecontacts WHERE salescaseref = '".$salescaseref."'";
	mysqli_query($db, $SQL);

	foreach ($contacts as $contact) {

		$SQL = "INSERT INTO salescasecontacts(salescaseref,contid) 
				values ('".$salescaseref."','".$contact."')";

		mysqli_query($db, $SQL);

	}

	echo json_encode($contacts);

?>