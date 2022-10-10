<?php
	
	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$SQL = 'SELECT suppliers.supplierid as debtorno,suppliers.suppname as name,
				SUM(ovamount - alloc) as totalBalance  
			FROM supptrans 
			INNER JOIN suppliers ON suppliers.supplierid = supptrans.supplierno 
			WHERE supptrans.type=601
			AND supptrans.settled=0'; 
	
	$res = mysqli_query($db, $SQL);

	$response['status'] = 'success';
	$response['totalBalance'] = locale_number_format(round(mysqli_fetch_assoc($res)['totalBalance'],2),2);
	
	echo json_encode($response);
	return;