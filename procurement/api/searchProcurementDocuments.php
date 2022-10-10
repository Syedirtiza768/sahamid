<?php 
	
	$PathPrefix='../../';
	
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$SQL = "SELECT procurement_document.id,
				   suppliers.suppname as supplier,
				   procurement_document.commencement_date as startdate,
				   procurement_document.canceled_date as canceled,
				   procurement_document.received_date as received,
				   procurement_document.stage
			FROM procurement_document 
			INNER JOIN suppliers ON suppliers.supplierid = procurement_document.supplierid";
	$res = mysqli_query($db, $SQL);

	$result = [];
	$count = 1;

	while($row = mysqli_fetch_assoc($res)){
		$row['sr'] 			= $count++;
		$row['startdate'] 	= date("d/m/Y",strtotime($row['startdate']));
		
		$result[] = $row;
	}

	echo json_encode($result);
	return;