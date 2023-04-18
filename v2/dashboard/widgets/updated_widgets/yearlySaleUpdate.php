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

    $allowed = [8, 10, 22];

	
		$SQL = "SELECT target FROM salesman WHERE salesmanname IN(' . $salesman . ')";
	
        $result = mysqli_query($db, $SQL);
        if ($result === FALSE) {
            $yearlySalesTarget = 0;
        }
        else{
            $yearlySalesTarget = mysqli_fetch_assoc(mysqli_query($db, $SQL))['target'];
        }

	$months = [];
	$sales = [];

	for ($i = 1; $i <= 12; $i++) {

		$month = 0;
		if ($i <= 9)
			$month .= $i;
		else
			$month = $i;

		$startDate = date('Y-' . $month . '-01');
		$endDate = date('Y-' . $month . '-31');

		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman  FROM invoice 
					INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
					INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
					INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
					INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
						AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
						AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
					WHERE salesman.salesmanname IN(' . $salesman . ')
                    AND invoice.returned = 0
					AND invoice.inprogress = 0";

		$SQL .=	" AND invoice.invoicesdate >= '" . $startDate . "'
					AND invoice.invoicesdate <= '" . $endDate . "'";

        $result = mysqli_query($db, $SQL);

        if ($result === FALSE) {
            $sale = 0;
        }
        else{
            $sale = mysqli_fetch_assoc(mysqli_query($db, $SQL))['price'];
        }

		

		$sales[]  = ($i > (int)(date('m'))) ? null : ((int)($sale ?: 0));
		$months[] = date("M", strtotime($startDate));
	}

	$targets = [];

	$monthsRemaining = 12;
	$salesTotal 	 = 0;

	$totalVal = 0;
	$i = 1;
	foreach ($sales as $sale) {

		$target = ($yearlySalesTarget - $salesTotal) / $monthsRemaining;

		$targets[] = (int)$target;

		$salesTotal += $sale;

		if ($i <= (int)(date('m')))
			$monthsRemaining--;

		$i++;
	}

	$response = [
		'months' => $months,
		'data' => [
			[
				'name' => "Sales",
				'data' => $sales
			],
			[
				'name' => "Target",
				'data' => $targets
			]
		],
		'total' => $salesTotal
	];

	echo json_encode($response);

}
?>