<?php 

	if(!isset($_GET['start']) || !isset($_GET['end'])){
	    echo json_encode([]);
	    return;
	}

	$db=mysqli_connect('localhost','irtiza','netetech321','sahamid');

	$start = $_GET['start'];
	$end = $_GET['end'];
	$salesPerson = isset($_GET['slps']) ? $_GET['slps']:"";
	$customer = isset($_GET['cus']) ? $_GET['cus']: "";

	$salespersonwisesales = [];

	//quotationvaluesalesperson (salesperson,value)
	$SQL = 'SELECT salescase.salesman,
	SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
	 ) as value from salesorderdetails INNER JOIN salesorderoptions on
	 (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
	 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=salesorders.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=salesorderdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE salesorderdetails.lineoptionno = 0 
	 
	 and salesorderoptions.optionno = 0 
	 AND salesorderdetails.orderno in 
	 (
		SELECT MAX(orderno) from salesorders group by salescaseref
	 
	 
	 )
	 AND salesorders.orddate BETWEEN "'.$start.'" AND "'.$end.'"
	 AND salescase.salesman LIKE "%'.$salesPerson.'%"
	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
	 GROUP BY salescase.salesman
	 
	 HAVING value>0';

	$result = mysqli_query($db,$SQL);

	$quotationvaluesalesperson = [];

	while($row = mysqli_fetch_assoc($result)){
		
		if(array_key_exists($row['salesman'], $quotationvaluesalesperson))
			$quotationvaluesalesperson[$row['salesman']] += $row['value'];
		else
			$quotationvaluesalesperson[$row['salesman']] = $row['value'];

		if(!isset($salespersonwisesales[$row['salesman']])){
			$salespersonwisesales[$row['salesman']]['name'] = $row['salesman'];
		}

	}

	//ocvaluesalesperson  (salesperson,value)
	$SQL = 'SELECT salescase.salesman,
	SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity
	 ) as value from ocdetails INNER JOIN ocoptions on
	 (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
	 INNER JOIN ocs on ocs.orderno = ocdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=ocs.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=ocdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE ocdetails.lineoptionno = 0 
	 
	 and ocoptions.optionno = 0 
	 
	 AND ocs.orddate BETWEEN "'.$start.'" AND "'.$end.'"
	 AND salescase.salesman LIKE "%'.$salesPerson.'%"
	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
	 GROUP BY salescase.salesman';

	$result = mysqli_query($db,$SQL);

	$ocvaluesalesperson = [];

	while($row = mysqli_fetch_assoc($result)){
		
		if(array_key_exists($row['salesman'], $ocvaluesalesperson))
			$ocvaluesalesperson[$row['salesman']] += $row['value'];
		else
			$ocvaluesalesperson[$row['salesman']] = $row['value'];

		if(!isset($salespersonwisesales[$row['salesman']])){
			$salespersonwisesales[$row['salesman']]['name'] = $row['salesman'];
		}

	}

	//dcvaluesalesperson (salesperson,value)
	$SQL = 'SELECT salescase.salesman,
	SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
	 ) as value from dcdetails INNER JOIN dcoptions on
	 (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
	 INNER JOIN dcs on dcs.orderno = dcdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=dcdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE dcdetails.lineoptionno = 0 
	 
	 and dcoptions.optionno = 0 
	 AND dcs.orddate BETWEEN "'.$start.'" AND "'.$end.'"
	 AND salescase.salesman LIKE "%'.$salesPerson.'%"
	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
	 GROUP BY salescase.salesman';

	$result = mysqli_query($db,$SQL);

	$dcvaluesalesperson = [];

	while($row = mysqli_fetch_assoc($result)){
		
		if(array_key_exists($row['salesman'], $dcvaluesalesperson))
			$dcvaluesalesperson[$row['salesman']] += $row['value'];
		else
			$dcvaluesalesperson[$row['salesman']] = $row['value'];

		if(!isset($salespersonwisesales[$row['salesman']])){
			$salespersonwisesales[$row['salesman']]['name'] = $row['salesman'];
		}

	}
	
	//invoicebrandvalue
	$SQL = 'SELECT salesman.salesmanname as salesman,
			SUM(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity
	 ) as value from invoicedetails 
	 INNER JOIN invoiceoptions on
	 (invoicedetails.invoiceno = invoiceoptions.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
	 INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno
	 INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=invoice.debtorno
	 INNER JOIN custbranch ON (custbranch.debtorno=invoice.debtorno AND 
		custbranch.branchcode=invoice.branchcode)
	 INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
	 WHERE invoice.returned = 0
	 AND invoice.inprogress = 0
	 AND invoice.invoicesdate BETWEEN "'.$start.'" AND "'.$end.'"
	 AND salesman.salesmanname LIKE "%'.$salesPerson.'%"
	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 GROUP BY salesman.salesmancode';

	$result = mysqli_query($db,$SQL);

	$invoicevaluesalesperson = [];

	while($row = mysqli_fetch_assoc($result)){

		if(array_key_exists($row['salesman'], $invoicevaluesalesperson))
			$invoicevaluesalesperson[$row['salesman']] += $row['value'];
		else
			$invoicevaluesalesperson[$row['salesman']] = $row['value'];

		if(!isset($salespersonwisesales[$row['salesman']])){
			$salespersonwisesales[$row['salesman']]['name'] = $row['salesman'];
		}

	}

	$data = [];

	foreach ($salespersonwisesales as $key => $value) {
		$data[] = [
			'0' => $value['name'],
			'1' => isset($quotationvaluesalesperson[$key]) ? number_format($quotationvaluesalesperson[$key]):0,
			'2' => isset($ocvaluesalesperson[$key]) ? number_format($ocvaluesalesperson[$key]):0,
			'3' => isset($dcvaluesalesperson[$key]) ? number_format($dcvaluesalesperson[$key]):0,
			'4' => isset($invoicevaluesalesperson[$key]) ? number_format($invoicevaluesalesperson[$key]):0,
		];
	}
	
	echo json_encode($data);
	return;

?>