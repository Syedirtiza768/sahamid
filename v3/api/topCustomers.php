<?php 

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	if(isset($_GET['count'])){
		if(isset($_SESSION['topCustomersList'.$_GET['count']])){
			echo json_encode($_SESSION['topCustomersList'.$_GET['count']]);
			return;
		}
	}

	$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
						*invoicedetails.quantity)*invoiceoptions.quantity) as value, debtorsmaster.name,
						debtorsmaster.debtorno FROM invoice 
			INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
			INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
						AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
						AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoice.debtorno
			WHERE invoice.returned = 0
			AND invoice.inprogress = 0
			GROUP BY invoice.debtorno";

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
		
		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
						*invoicedetails.quantity)*invoiceoptions.quantity) as value FROM invoice 
			INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
			INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
						AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
						AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoice.debtorno
			WHERE invoice.returned = 0
			AND invoice.inprogress = 0
			AND invoice.debtorno = '".$customer['debtorno']."'
			AND invoice.invoicesdate >= '".date("Y-m-01")."'
			AND invoice.invoicesdate <= '".date("Y-m-t")."'
			GROUP BY invoice.debtorno";

		$customer['count'] = $count;
		$customer['value'] = locale_number_format($customer['value'],2);
		$customer['thisMonth'] = locale_number_format(mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'] ?:0,2);

		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
						*invoicedetails.quantity)*invoiceoptions.quantity) as value FROM invoice 
			INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
			INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
						AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
						AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoice.debtorno
			WHERE invoice.returned = 0
			AND invoice.inprogress = 0
			AND invoice.debtorno = '".$customer['debtorno']."'
			AND invoice.invoicesdate >= '".date("Y-m-01", strtotime('-3 month'))."'
			AND invoice.invoicesdate <= '".date("Y-m-t")."'
			GROUP BY invoice.debtorno";
			
		$customer['tMonths'] = locale_number_format(mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'] ?:0,2);
		
		$response[] = $customer;
		
		$count++;

	}
	
	if(isset($_GET['count'])){
		if(!isset($_SESSION['topCustomersList'.$_GET['count']])){
			$_SESSION['topCustomersList'.$_GET['count']] = $response;
		}
	}

	echo json_encode($response);

	return;

	function cmp($a, $b){
	    if ($a['value'] == $b['value'])
	        return 0;
	    return ($a['value'] > $b['value']) ? -1 : 1;
	}
	
	