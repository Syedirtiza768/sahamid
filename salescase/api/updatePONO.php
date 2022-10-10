<?php 

	include("../misc.php");

	$db = createDBConnection();
	$salescaseref = $_POST['salescaseref'];
	$pono = $_POST['pono'];
	$count = $_POST['count'];

	$SQL = 'UPDATE salescasepo SET pono="'.$pono.'"
			WHERE salescaseref="'.$salescaseref.'"
			AND pocount='.$count;

	mysqli_query($db,$SQL);

	header('Location: ' . $_SERVER['HTTP_REFERER']."#rppbutton");

?>