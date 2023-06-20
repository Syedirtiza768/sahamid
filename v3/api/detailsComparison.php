<?php

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	if(isset($_SESSION['companyAnalysisReport'])){
		echo json_encode($_SESSION['companyAnalysisReport']);
		return;
	}

	$months = [];
	$salescases = [];
	$quotations = [];
	$sales = [];
	$recoveries = [];

	for($month = -5; $month <= 0; $month++){

		$startDate = date('Y-m-01',strtotime($month." month",strtotime(date("F") . "1")));
		$endDate = date('Y-m-t',strtotime($month." month",strtotime(date("F") . "1")));
		
		//Salescases
		$SQL = "SELECT * FROM salescase 
				WHERE commencementdate >= '".date('Y-m-01 00:00:00',strtotime($month." month",strtotime(date("F") . "1")))."'
				AND commencementdate <= '".date('Y-m-t 23:23:59',strtotime($month." month",strtotime(date("F") . "1")))."'";
		$salescases [] = mysqli_num_rows(mysqli_query($db, $SQL));
		
		//Quotation Values
		$SQL = 'SELECT salesorders.salescaseref, salesorderdetails.orderno, SUM(salesorderdetails.unitprice*
					(1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity) as value
				FROM salesorderdetails 
				INNER JOIN salesorderoptions ON (salesorderdetails.orderno = salesorderoptions.orderno 
					AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
				INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno
				WHERE salesorderdetails.lineoptionno = 0 
				AND salesorderoptions.optionno = 0
				AND salesorders.debtorno LIKE "%MT%"
				AND salesorders.orddate >= "'.$startDate.'"
				AND salesorders.orddate <= "'.$endDate.'"';	
		$quotation = mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'];
		$quotations[] = ($quotation ? round($quotation):0);
		
		//Sales
		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
						*invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman,
						invoice.services,invoice.gst
				FROM invoice 
				INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
				INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
				INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
					AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
					AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
				WHERE invoice.returned = 0
				AND invoice.inprogress = 0
				AND invoiceoptions.invoiceoptionno = 0
				AND custbranch.debtorno LIKE '%MT%'
				AND invoice.invoicesdate >= '".$startDate."'
				AND invoice.invoicesdate <= '".$endDate."'
				GROUP BY invoice.invoiceno";
		$res = mysqli_query($db, $SQL);
		
		$saleValue = 0;
		
		while($row = mysqli_fetch_assoc($res)){
				
			$percent = $row['services'] == 1 ? 1.16:1.17;
			
			if($row['gst'] == "inclusive"){
				$saleValue += $row['price']/$percent;
			}else{
				$saleValue += $row['price'];
			}
			
		}

		$sales[] = ($saleValue ? round($saleValue):0);
		
		$SQL = "SELECT SUM(custallocns.amt) as amt FROM custallocns
				INNER JOIN debtortrans ON debtortrans.id = custallocns.transid_allocfrom
				WHERE debtortrans.trandate >= '".$startDate." 00:00:00'
				AND debtortrans.trandate <= '".$endDate." 23:59:59'
				AND debtortrans.debtorno LIKE '%MT%'";
		$recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];
		$recoveries[] = ($recovery ? round($recovery):0);
		
		$months[] = date("M-Y",strtotime($month." month", strtotime(date("F") . "1")));

	}

	$response = [

		'months'  => $months,
		'data'  => [
			[
				'name' => 'Quotation',
				'data' => $quotations
			],
			[
				'name' => 'Sales',
				'data' => $sales
			],
			[
				'name' => 'Recoveries',
				'data' => $recoveries
			]
		]
	];
	
	if(!isset($_SESSION['companyAnalysisReport'])){
		$_SESSION['companyAnalysisReport'] = $response;
	}

	echo json_encode($response);
