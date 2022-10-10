<?php 

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$SQL = "SELECT target FROM salesman WHERE salesmanname='".$_SESSION['UsersRealName']."'";
	$yearlySalesTarget = mysqli_fetch_assoc(mysqli_query($db, $SQL))['target'];
	
	$months = [];
	$sales = [];
	
	for($i=1; $i<=12; $i++){
		
		$month = 0;
		if($i <= 9)
			$month .= $i;
		else
			$month = $i;
		
		$startDate = date('Y-'.$month.'-01');
		$endDate = date('Y-'.$month.'-31');

		/*$SQL = "SELECT SUM(
					CASE WHEN (invoice.gst = 'inclusive' AND invoice.services = 1) 
						THEN (ovamount/1.16)
					WHEN (invoice.gst = 'inclusive' AND invoice.services = 0)
						THEN (ovamount/1.17)
					ELSE
						ovamount
					END) as price, debtortrans.branchcode FROM debtortrans
				INNER JOIN custbranch ON debtortrans.branchcode = custbranch.branchcode
				INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
				INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
				WHERE debtortrans.reversed = 0
				AND debtortrans.type = 10
				AND invoice.inprogress = 0
				AND salesman.salesmanname = '".$_SESSION['UsersRealName']."'
				AND invoice.invoicesdate >= '$startDate'
				AND invoice.invoicesdate <= '$endDate'";*/
		
		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
						*invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman  FROM invoice 
				INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
				INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
				INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
				INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
					AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
					AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
				WHERE invoice.returned = 0
				AND invoice.inprogress = 0
				AND salesman.salesmanname = '".$_SESSION['UsersRealName']."'
				AND invoice.invoicesdate >= '".$startDate."'
				AND invoice.invoicesdate <= '".$endDate."'";
				
		$sale = mysqli_fetch_assoc(mysqli_query($db, $SQL))['price'];
				
		$sales[]  = ($i > (int)(date('m'))) ? null:((int)($sale?:0));
		$months[] = date("M",strtotime($startDate));
		
	}

	$targets = [];

	$monthsRemaining = 12;
	$salesTotal 	 = 0;
	
	$totalVal = 0;
	$i = 1;
	foreach($sales as $sale){
		
		$target = ($yearlySalesTarget - $salesTotal) / $monthsRemaining;
		
		$targets[] = (int)$target;
		
		$salesTotal += $sale;
		
		if($i <= (int)(date('m')))
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
	return;