<?php

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	//if(isset($_SESSION['salesPersonComparison'])){
	//	echo json_encode($_SESSION['salesPersonComparison']);
	//	return;
	//}

	//TopSalesPersons
	$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
						*invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman  FROM invoice 
			INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
			INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
			INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
				AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
				AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
			WHERE invoice.returned = 0
			AND invoice.inprogress = 0
			AND invoice.invoicesdate <= '".date("Y-m-t")."'
			GROUP BY invoice.invoiceno";
			
	//AND invoice.invoicesdate >= '".date("2001-01-01")."'

	$res = mysqli_query($db, $SQL);

	$TopSalesPersons = [];

	while($row = mysqli_fetch_assoc($res)){

		if(!array_key_exists($row['salesman'], $TopSalesPersons)){
			$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='".$row['salesman']."'";
			$TopSalesPersons[$row['salesman']] = [];
			$TopSalesPersons[$row['salesman']]['code'] = $row['salesman'];
			$TopSalesPersons[$row['salesman']]['name'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['salesmanname'];
			$TopSalesPersons[$row['salesman']]['sales'] = 0;
		}

		$TopSalesPersons[$row['salesman']]['sales'] += $row['price'];

	}

	usort($TopSalesPersons, "cmp");

	function cmp($a, $b){
	    if ($a['sales'] == $b['sales'])
	        return 0;
	    return ($a['sales'] > $b['sales']) ? -1 : 1;
	}

	$salesPersonData = [];

	$count = 1;
	foreach ($TopSalesPersons as $key => $value) {

		//if($count > 5)
			//break;
		
		$data = [];

		for($month = -5; $month <= 0; $month++){

			$startDate = date('Y-m-01',strtotime($month." month",strtotime(date("F") . "1")));
			$endDate = date('Y-m-t',strtotime($month." month",strtotime(date("F") . "1")));

			$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity)as price
							,custbranch.salesman,invoice.gst,invoice.services  
					FROM invoice 
					INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
					INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
					INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
						AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
						AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
					WHERE invoice.returned = 0
					AND invoice.inprogress = 0
					AND custbranch.salesman = '".$value['code']."'
					AND invoice.invoicesdate >= '".$startDate."'
					AND invoice.invoicesdate <= '".$endDate."'
					GROUP BY invoice.invoiceno";

			$res = mysqli_query($db, $SQL);
			
			$price = 0;
			
			while($row = mysqli_fetch_assoc($res)){
				
				$percent = $row['services'] == 1 ? 1.16:1.17;
				
				if($row['gst'] == "inclusive"){
					$price += $row['price']/$percent;
				}else{
					$price += $row['price'];
				}
				
			}
			
			$data[] = ($price ? round($price):0);

		}

		$salesPersonData[] = [

			'name' => $value['name'],
			'data' => $data

		];

		$count++;

	}

	$months = [];
	for($month = -5; $month <= 0; $month++){

		$months[] = date("M-Y",strtotime($month." month",strtotime(date("F") . "1")));

	}
	
	$response = [

		'start' => (int)(date('m',strtotime('-5 month',strtotime(date("F") . "1")))),
		'months'  => $months,
		'data'  => $salesPersonData
	];
	
	//if(!isset($_SESSION['salesPersonComparison'])){
	//	$_SESSION['salesPersonComparison'] = $response;
	//}

	echo json_encode($response);
