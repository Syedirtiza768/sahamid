<?php

	include('../misc.php');

	$dcno = $_POST['dcno'];
	$salescaseref = $_POST['salescaseref'];
	$sendinvoice = $_POST['sendinvoice'];

	$db = createDBConnection();

	$SQL = "UPDATE dcs SET dcstatus = '".$sendinvoice."' WHERE orderno=".$dcno;
	mysqli_query($db, $SQL);

	header('Location: ' . $_SERVER['HTTP_REFERER']."#dcbutton");
	
?>