<?php

	include('../misc.php');

	if(!validHeaders()){

		$response = [
			'status' => 'error',
			'message' => 'Refresh or ReLogin Required.'
		];

		echo json_encode($response);
		return;		

	}

	$db = createDBConnection();
	
	$SQL = "TRUNCATE `salesordersip`";

	mysqli_query($db, $SQL);

	$SQL = "TRUNCATE `salesorderlinesip`";
	
	mysqli_query($db, $SQL);

	$SQL = "TRUNCATE `salesorderoptionsip`";
	
	mysqli_query($db, $SQL);

	$SQL = "TRUNCATE `salesorderdetailsip`";
	
	mysqli_query($db, $SQL);

	echo "Truncated IP tables";
	return;

?>