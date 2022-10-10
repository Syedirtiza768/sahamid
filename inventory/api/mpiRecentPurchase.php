<?php 

	$AllowAnyone = true;

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	if(!userHasPermission($db, 'mpi_recent_purchases')){
		echo json_encode([]);
		return;
	}

	$stockid  = $_GET['stockid'];
	$parchi  = $_GET['parchi'];

	$SQL = "SELECT  suppliers.suppname as name,
					bazar_parchi.parchino,
					bpitems.price
			FROM bpitems
			INNER JOIN bazar_parchi ON bazar_parchi.parchino = bpitems.parchino
			INNER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
			WHERE bazar_parchi.type = 601
			AND bpitems.stockid = '$stockid'
			AND bpitems.parchino != '$parchi'";
	$res = mysqli_query($db, $SQL);

	$response = [];
	while($row = mysqli_fetch_assoc($res)){
		$response[] = [
			$row['parchino'],
			$row['name'],
			$row['price'],
		];
	}
	echo json_encode($response);