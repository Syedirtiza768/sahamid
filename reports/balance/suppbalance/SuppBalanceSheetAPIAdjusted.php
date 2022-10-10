<?php

	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$SQL = 'SELECT suppliers.supplierid as debtorno,suppliers.suppname as name,
				SUM(ovamount - alloc) as curr  
			FROM supptrans 
			INNER JOIN suppliers ON suppliers.supplierid = supptrans.supplierno 
			WHERE supptrans.type=601
			AND supptrans.settled=0
			GROUP BY suppliers.supplierid'; 
	
	$result = mysqli_query($db, $SQL);
	
	$response = [];

	while($customer = mysqli_fetch_assoc($result)){

		$res = [];

		$res[0] = "<form action='".$RootPath."/../suppstatement/SupplierStatementAdjusted.php' method='post' target='_blank'>
						<input type='hidden' name='FormID' value='".$_SESSION['FormID']."' />
						<input type='hidden' name='cust' value='".$customer['debtorno']."' />
						<input type='submit' class='btn btn-info' style='width:100%' value='".$customer['debtorno']."' />
					</form>";
		$res[1] = $customer['name'];
		$res[2] = locale_number_format($customer['curr'],2). "<sub>PKR</sub>";
		$res[3] = '<a href="../../../Payments.php?SupplierID='.$customer['debtorno'].'" class="btn btn-info" target="_blank" style="font-size:11px; white-space: nowrap;">Enter Payment</a>';

		$response[] = $res;

	}

	echo json_encode($response);
	return;
