<?php 
if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}
$key = "outstandingTotal";
if (isset($_POST['salesman'])) {
	$salesman = $_POST['salesman'];

		$response = [];




		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
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
                    INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
					INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
                    WHERE salesman.salesmanname IN('.$salesman.')
					AND debtortrans.type=10 
					AND debtortrans.settled=0
					AND debtortrans.reversed=0';

		$res = mysqli_query($db, $SQL);

		$response['status'] = 'success';
		$response['totalBalance'] = (round(mysqli_fetch_assoc($res)['totalBalance'], 2));

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
					WHERE salesman.salesmanname IN('.$salesman.')
					AND debtortrans.type=10 
					AND debtortrans.settled=0 
					AND debtortrans.reversed=0 
					AND DATEDIFF( "' . date('Y/m/d') . '",invoice.invoicesdate) < 30';

		$res = mysqli_query($db, $SQL);
		$response['add'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

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
    WHERE salesman.salesmanname IN('.$salesman.')
    AND debtortrans.type=10 
    AND debtortrans.settled=0 
    AND debtortrans.reversed=0 
    AND DATEDIFF( "' . date('Y/m/d') . '",invoice.invoicesdate) >= 30 
    AND DATEDIFF( "' . date('Y/m/d') . '",invoice.invoicesdate) < 60';

		$res = mysqli_query($db, $SQL);
        if(mysqli_num_rows($res) == 0) {
            $response['tdd'] = 0;
       } else {
           // do other stuff...
           $response['tdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));
       }

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
					WHERE salesman.salesmanname IN('.$salesman.')
					AND debtortrans.type=10 
					AND debtortrans.settled=0 
					AND debtortrans.reversed=0 
					AND DATEDIFF( "' . date('Y/m/d') . '",invoice.invoicesdate) >= 60 
					AND DATEDIFF( "' . date('Y/m/d') . '",invoice.invoicesdate) < 90';

		$res = mysqli_query($db, $SQL);
		$response['sdd'] = (round(mysqli_fetch_assoc($res)['60daysdue'], 2));

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
					WHERE salesman.salesmanname IN('.$salesman.')
					AND debtortrans.type=10 
					AND debtortrans.settled=0 
					AND debtortrans.reversed=0 
					AND DATEDIFF( "' . date('Y/m/d') . '",invoice.invoicesdate) >= 90 
					AND DATEDIFF( "' . date('Y/m/d') . '",invoice.invoicesdate) < 120';

		$res = mysqli_query($db, $SQL);
		$response['ndd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

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
					WHERE salesman.salesmanname IN('.$salesman.')
					AND debtortrans.type=10 
					AND debtortrans.settled=0 
					AND debtortrans.reversed=0 
					AND DATEDIFF( "' . date('Y/m/d') . '",invoice.invoicesdate) >= 120';

		$res = mysqli_query($db, $SQL);
		$response['otdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

		$resp = [];

		$resp[] = [
			'0-30 Days',
			$response['add']
		];

		$resp[] = [
			'30+ Days',
			$response['tdd']
		];

		$resp[] = [
			'60+ Days',
			$response['sdd']
		];

		$resp[] = [
			'90+ Days',
			$response['ndd']
		];

		$resp[] = [
			'120+ Days',
			$response['otdd']
		];

		$resp[] = [
			'Total',
			$response['totalBalance']
		];
        echo json_encode($resp);
	}
?>