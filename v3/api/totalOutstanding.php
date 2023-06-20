<?php 

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$SQL = 'SELECT debtorsmaster.debtorno,debtorsmaster.name,
				SUM(
					CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount - alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS totalBalance  
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno 
			WHERE debtortrans.type=10 
			AND debtortrans.settled=0
			AND debtortrans.debtorno LIKE "%MT%"
			AND debtortrans.reversed=0'; 
	
	$res = mysqli_query($db, $SQL);

	$response['status'] = 'success';
	$response['totalBalance'] = (round(mysqli_fetch_assoc($res)['totalBalance'],2));

	$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount-alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS 30daysdue 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE debtortrans.type=10 
			AND debtortrans.settled=0 
			AND debtortrans.reversed=0 
			AND debtortrans.debtorno LIKE "%MT%"
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 30';
		
	$res = mysqli_query($db, $SQL);
	$response['add'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
	
	$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount-alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS 30daysdue 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE debtortrans.type=10 
			AND debtortrans.settled=0 
			AND debtortrans.reversed=0 
			AND debtortrans.debtorno LIKE "%MT%"
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 30 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 60';
		
	$res = mysqli_query($db, $SQL);
	$response['tdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
	
	$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount-alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS 60daysdue 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE debtortrans.type=10 
			AND debtortrans.settled=0 
			AND debtortrans.reversed=0 
			AND debtortrans.debtorno LIKE "%MT%"
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 60 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 90';
		
	$res = mysqli_query($db, $SQL);
	$response['sdd'] = (round(mysqli_fetch_assoc($res)['60daysdue'],2));
	
	$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount-alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS 30daysdue 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE debtortrans.type=10 
			AND debtortrans.settled=0 
			AND debtortrans.reversed=0 
			AND debtortrans.debtorno LIKE "%MT%"
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 90 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 120';
		
	$res = mysqli_query($db, $SQL);
	$response['ndd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
	
	$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount-alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS 30daysdue 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE debtortrans.type=10 
			AND debtortrans.settled=0 
			AND debtortrans.reversed=0 
			AND debtortrans.debtorno LIKE "%MT%"
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 120';
		
	$res = mysqli_query($db, $SQL);
	$response['otdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));

	$res = [];

	$res[] = [
		'0-30 Days',
		$response['add']
	];

	$res[] = [
		'30+ Days',
		$response['tdd']
	];

	$res[] = [
		'60+ Days',
		$response['sdd']
	];

	$res[] = [
		'90+ Days',
		$response['ndd']
	];

	$res[] = [
		'120+ Days',
		$response['otdd']
	];

	$res[] = [
		'Total',
		$response['totalBalance']
	];
	
	echo json_encode($res);
	return;