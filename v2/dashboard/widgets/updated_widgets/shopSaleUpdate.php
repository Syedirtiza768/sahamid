<?php
if (!isset($db)) {
    session_start();
    $db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
    return;
}
if (isset($_POST['salesman'])) {
    $salesman = $_POST['salesman'];

    $key = "shopSale";

    $months = [];
    //$salescases = [];
    $crvs = [];
    $csvs = [];
    $recoveries = [];

    for ($month = -5; $month <= 0; $month++) {

        $startDate = date('Y-m-01', strtotime($month . " month", strtotime(date("F") . "1")));
        $endDate = date('Y-m-t', strtotime($month . " month", strtotime(date("F") . "1")));

        //Salescases
        /*
			$SQL = "SELECT * FROM salescase 
					WHERE commencementdate >= '".date('Y-m-01 00:00:00',strtotime($month." month",strtotime(date("F") . "1")))."'
					AND commencementdate <= '".date('Y-m-t 23:23:59',strtotime($month." month",strtotime(date("F") . "1")))."'";
			$salescases [] = mysqli_num_rows(mysqli_query($db, $SQL));
			*/
        //CRV Values
        $SQL = "SELECT 
        shopsale.salesman,
				SUM(debtortrans.ovamount) as value
				
			FROM shopsale 
			INNER JOIN custbranch ON shopsale.branchcode = custbranch.branchcode
			INNER JOIN debtorsmaster ON shopsale.debtorno = debtorsmaster.debtorno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			INNER JOIN debtortrans ON (debtortrans.type = 750
											AND debtortrans.transno = shopsale.orderno
											AND debtortrans.reversed = 0)
            WHERE salesman.salesmanname IN(' . $salesman . ')
            AND shopsale.complete = 1
			AND shopsale.payment='crv'
			AND shopsale.orddate >= '" . $startDate . "'
			AND shopsale.orddate <= '" . $endDate . "'";

        $result = mysqli_query($db, $SQL);
        if ($result === FALSE) {
            $crv = 0;
            $crvs[] = ($crv ? round($crv) : 0);
        } else {
            $row = mysqli_fetch_assoc(mysqli_query($db, $SQL));
            $crv = $row['value'];
            //	$crv = mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'];
            $crvs[] = ($crv ? round($crv) : 0);
        }
        //CSV Values
        $SQL = $SQL = "SELECT 
        shopsale.salesman,
				SUM(debtortrans.ovamount) as value
				
			FROM shopsale 
			INNER JOIN custbranch ON shopsale.branchcode = custbranch.branchcode
			INNER JOIN debtorsmaster ON shopsale.debtorno = debtorsmaster.debtorno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			INNER JOIN debtortrans ON (debtortrans.type = 750
											AND debtortrans.transno = shopsale.orderno
											AND debtortrans.reversed = 0)
                                            WHERE salesman.salesmanname IN(' . $salesman . ')
            AND shopsale.complete = 1
			AND shopsale.payment='CSV'
			AND shopsale.orddate >= '" . $startDate . "'
			AND shopsale.orddate <= '" . $endDate . "'";
        $result = mysqli_query($db, $SQL);
        if ($result === FALSE) {
            $csv = 0;
            $csvs[] = ($csv ? round($csv) : 0);
        } else {
            $row = mysqli_fetch_assoc(mysqli_query($db, $SQL));
            $csv = $row['value'];
            //	$csv = mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'];
            $csvs[] = ($csv ? round($csv) : 0);
        }
        /*
	$SQL = "SELECT SUM(custallocns.amt) as amt FROM custallocns
					INNER JOIN debtortrans ON debtortrans.id = custallocns.transid_allocto
					INNER JOIN shopsale ON debtortrans.transno=shopsale.orderno
					WHERE custallocns.datealloc >= '".$startDate." 00:00:00'
					AND debtortrans.type = 750
					AND shopsale.payment='crv'
					AND debtortrans.trandate <= '".$endDate." 23:59:59'";
			$recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];
	*/
        /*  $SQL = "SELECT SUM(custallocns.amt) as amt FROM custallocns
					INNER JOIN debtortrans ON debtortrans.id = custallocns.transid_allocfrom
					INNER JOIN shopsale ON debtortrans.transno=shopsale.orderno
					WHERE debtortrans.trandate >= '".$startDate." 00:00:00'
					AND debtortrans.type = 750
					AND shopsale.payment='crv'
					AND debtortrans.trandate <= '".$endDate." 23:59:59'";
            $recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];

        */
        $SQL = "SELECT transid_allocfrom as f,transid_allocto as t,datealloc as d,SUM(amt) as amt, dt.trandate as cd, 
			invoice.shopinvoiceno,invoiced.settled, invoiced.alloc as totalalloc, salesman.salesmanname,
			invoiced.ovamount as totalamt, debtorsmaster.name,invoice.invoicedate,invoice.invoicesdate
			FROM custallocns,debtortrans as invoiced
			INNER JOIN invoice on invoice.invoiceno = invoiced.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoiced.debtorno
			INNER JOIN custbranch ON custbranch.branchcode = invoiced.branchcode
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			INNER JOIN debtortrans as dt ON dt.debtorno = invoiced.debtorno
			WHERE salesman.salesmanname IN(' . $salesman . ')
            AND dt.trandate >= '.$startDate.'
			AND dt.trandate <= '.$endDate.'
			AND invoiced.id = transid_allocto
			AND invoiced.debtorno != 'WALKIN01'
			AND dt.id = transid_allocfrom
			AND invoiced.type = 750
			AND invoiced.reversed = 0";
        $result = mysqli_query($db, $SQL);
        if ($result === FALSE) {
            $recovery = 0;

            $recoveries[] = ($recovery ? round($recovery) : 0);

            $months[] = date("M-Y", strtotime($month . " month", strtotime(date("F") . "1")));
        } else {
            $recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];

            $recoveries[] = ($recovery ? round($recovery) : 0);

            $months[] = date("M-Y", strtotime($month . " month", strtotime(date("F") . "1")));
        }
    }
    $response = [

        'months'  => $months,
        'data'  => [
            [
                'name' => 'CRV',
                'data' => $crvs
            ],
            [
                'name' => 'CSV',
                'data' => $csvs
            ],
            [
                'name' => 'Recoveries',
                'data' => $recoveries
            ]
        ]
    ];

    echo json_encode($response);
}
