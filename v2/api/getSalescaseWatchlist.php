<?php 

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	$SQL = "SELECT salescase_watchlist.salescaseref,
				salescase_watchlist.review_on,
				salescase_watchlist.notes,
				salescase_watchlist.priority,
				debtorsmaster.name as client_name,
				debtorsmaster.dba,
				salescase.priority as salescase_priority,
				salescase.closed as salescase_closed,
				salescase.closingreason as salescase_closingreason,
				salescase.closingremarks as salescase_closingremarks,
				salesman.salesmanname,
				salescase.salescasedescription,
				salescase.commencementdate as salescase_created_at,
				salescase_watchlist.created_at
			FROM salescase_watchlist 
			INNER JOIN salescase ON salescase_watchlist.salescaseref = salescase.salescaseref
			INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno
				AND custbranch.branchcode = salescase.branchcode)
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE salescase_watchlist.userid='".$_SESSION['UserID']."'
			AND salescase_watchlist.deleted=0
			AND salescase_watchlist.review_on = '".date('Y-m-d')."' 
			ORDER BY salescase_watchlist.priority";
			
	$res = mysqli_query($db, $SQL);
	
	$response = [];
	
	$count = 1;
	while($row = mysqli_fetch_assoc($res)){
		
		if($count > 5)	break;
		
		$response[] = [
			'count' 	   => $count,
			'salescaseref' => $row['salescaseref'],
			'salesman'	   => $row['salesmanname'],
			'client'       => $row['client_name']
		];
		
		$count++;
	}
	
	echo json_encode($response);