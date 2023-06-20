<?php

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
	$res = mysqli_query($db, $SQL);

	$canAccess = [];

	while($row = mysqli_fetch_assoc($res))
		$canAccess[] = $row['can_access'];
	
	$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,custbranch.branchcode,
				SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount-alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS value 
			FROM debtortrans 
			INNER JOIN invoice ON (invoice.invoiceno=debtortrans.transno)
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			INNER JOIN www_users ON www_users.realname = salesman.salesmanname
			WHERE debtortrans.type=10 
			AND debtortrans.settled=0
			AND debtortrans.reversed=0
			AND ( salesman.salesmanname ="'.$_SESSION['UsersRealName'].'"
				OR www_users.userid IN ("'.implode('","', $canAccess).'") )
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 120
			GROUP BY invoice.debtorno';
		
	$res = mysqli_query($db, $SQL);

	$customers = [];
	
	while($row = mysqli_fetch_assoc($res)){
		$customers[] = $row;
	}
	
	usort($customers, "cmp");

	$response = [];

	$count = 1;
	foreach ($customers as $customer) {
		
		if(isset($_GET['count']) && $count > $_GET['count'])	break;

		$customer['count'] = $count;
		
		$customer['value'] = locale_number_format($customer['value']);
		
		$response[] = $customer;
		
		$count++;

	}

	echo json_encode($response);

	return;

	function cmp($a, $b){
	    if ($a['value'] == $b['value'])
	        return 0;
	    return ($a['value'] > $b['value']) ? -1 : 1;
	}
	
	
	
	