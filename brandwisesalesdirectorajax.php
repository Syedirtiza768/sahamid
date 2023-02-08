<?php
	

	if(!isset($_GET['start']) || !isset($_GET['end'])){
		echo json_encode([]);
		return;
	}

	$db=mysqli_connect('localhost','irtiza','netetech321','sahamid');
	ini_set('max_execution_time', 580); //120 seconds
	$start = $_GET['start'];
	$end = $_GET['end'];
	$salesPerson = isset($_GET['slps']) ? $_GET['slps']:"";
	$customer = isset($_GET['cus']) ? $_GET['cus']: "";
	$customertype = isset($_GET['customertype']) ? $_GET['customertype']: "";


	$salesPerson = explode(',',$salesPerson);
	foreach ($salesPerson as $values) {
	$salesPersons[] = 'salescase.salesman = "'.$values.'" OR';
	$salesman[] = 'salesman.salesmanname = "'.$values.'" OR';
	}
	$salesPerson = implode(' ', $salesPersons);
	$salesman = implode(' ', $salesman);
	$salesPerson = substr($salesPerson, 0, -2);
	$salesman = substr($salesman, 0, -2);

	$customertype = explode(',',$customertype);
	foreach ($customertype as $values) {
		$customertypes[] = 'debtortype.typename = "'.$values.'" OR';
		}
		$customertype = implode(' ', $customertypes);
		$customertype = substr($customertype, 0, -2);

	//Manufacturer Names
	$SQL = 'SELECT * FROM manufacturers';
	$result = mysqli_query($db,$SQL);

	$manufacturers = [];

	while($row = mysqli_fetch_assoc($result)){
		$manufacturers[$row['manufacturers_id']] = $row['manufacturers_name'];
	}

	$brandwisesales = [];
	//quotationvaluebrand (manufacturers_id,value)
	$SQL = 'SELECT manufacturers.manufacturers_id,
	SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
	 ) as value from salesorderdetails INNER JOIN salesorderoptions on
	 (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
	 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=salesorders.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=salesorderdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 INNER JOIN debtortype on debtortype.typeid=debtorsmaster.typeid
	 WHERE salesorderdetails.lineoptionno = 0 
	 
	 and salesorderoptions.optionno = 0 
	 AND salesorderdetails.orderno in 
	 (
		SELECT MAX(orderno) from salesorders group by salescaseref
	 
	 
	 )
	 AND salesorders.orddate BETWEEN "'.$start.'" AND "'.$end.'"
	 AND ('.$salesPerson.')
	 AND ('.$customertype.')
	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
	 GROUP BY manufacturers.manufacturers_id';

	$result = mysqli_query($db,$SQL);

	$quotationValueBrand = [];

	while($row = mysqli_fetch_assoc($result)){
		
		if(array_key_exists($row['manufacturers_id'], $quotationValueBrand))
			$quotationValueBrand[$row['manufacturers_id']] += $row['value'];
		else
			$quotationValueBrand[$row['manufacturers_id']] = $row['value'];

		if(!isset($brandwisesales[$row['manufacturers_id']])){
			$brandwisesales[$row['manufacturers_id']]['id'] = $row['manufacturers_id'];
			$brandwisesales[$row['manufacturers_id']]['name'] = $manufacturers[$row['manufacturers_id']];
		}

	}

	//ocbrandvalue
	$SQL = 'SELECT manufacturers.manufacturers_id,
	SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity
	 ) as value from ocdetails INNER JOIN ocoptions on
	 (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
	 INNER JOIN ocs on ocs.orderno = ocdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=ocs.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=ocdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 INNER JOIN debtortype on debtortype.typeid=debtorsmaster.typeid
	 WHERE ocdetails.lineoptionno = 0 
	 
	 and ocoptions.optionno = 0 
	 
	 AND ocs.orddate BETWEEN "'.$start.'" AND "'.$end.'"
	 AND ('.$salesPerson.')
	 AND ('.$customertype.')
	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
	 GROUP BY manufacturers.manufacturers_id';

	$result = mysqli_query($db,$SQL);

	$ocbrandvalue = [];

	while($row = mysqli_fetch_assoc($result)){
		
		if(array_key_exists($row['manufacturers_id'], $ocbrandvalue))
			$ocbrandvalue[$row['manufacturers_id']] += $row['value'];
		else
			$ocbrandvalue[$row['manufacturers_id']] = $row['value'];

		if(!isset($brandwisesales[$row['manufacturers_id']])){
			$brandwisesales[$row['manufacturers_id']]['id'] = $row['manufacturers_id'];
			$brandwisesales[$row['manufacturers_id']]['name'] = $manufacturers[$row['manufacturers_id']];
		}

	}

	//dcbrandvalue
	$SQL = 'SELECT manufacturers.manufacturers_id,
	SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
	 ) as value from dcdetails INNER JOIN dcoptions on
	 (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
	 INNER JOIN dcs on dcs.orderno = dcdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=dcdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 INNER JOIN debtortype on debtortype.typeid=debtorsmaster.typeid
	 WHERE dcdetails.lineoptionno = 0 
	 
	 and dcoptions.optionno = 0 
	 AND dcs.orddate BETWEEN "'.$start.'" AND "'.$end.'"
	 AND ('.$salesPerson.')
	 AND ('.$customertype.')
	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
	 GROUP BY manufacturers.manufacturers_id';

	$result = mysqli_query($db,$SQL);

	$dcbrandvalue = [];

	while($row = mysqli_fetch_assoc($result)){

		if(array_key_exists($row['manufacturers_id'], $dcbrandvalue))
			$dcbrandvalue[$row['manufacturers_id']] += $row['value'];
		else
			$dcbrandvalue[$row['manufacturers_id']] = $row['value'];

		if(!isset($brandwisesales[$row['manufacturers_id']])){
			$brandwisesales[$row['manufacturers_id']]['id'] = $row['manufacturers_id'];
			$brandwisesales[$row['manufacturers_id']]['name'] = $manufacturers[$row['manufacturers_id']];
		}

	}
	
	//invoicebrandvalue
	$SQL = 'SELECT manufacturers.manufacturers_id,
			SUM(
				CASE WHEN (invoice.gst = "inclusive" AND invoice.services = 1) 
					THEN ((invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity)/1.16)
				WHEN (invoice.gst = "inclusive" AND invoice.services = 0)
					THEN ((invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity)/1.17)
				ELSE
					(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity)
				END) as value from invoicedetails INNER JOIN invoiceoptions on
	 (invoicedetails.invoiceno = invoiceoptions.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
	 INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno
	 INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=invoice.debtorno
	 INNER JOIN debtortype on debtortype.typeid=debtorsmaster.typeid
	 INNER JOIN custbranch ON (custbranch.debtorno=invoice.debtorno AND 
		custbranch.branchcode=invoice.branchcode)
	 INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
	 WHERE invoice.returned = 0
	 AND invoice.inprogress = 0
	 AND invoice.invoicesdate BETWEEN "'.$start.'" AND "'.$end.'"
	 AND ('.$salesman.')
	 AND ('.$customertype.')
	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
	 GROUP BY manufacturers.manufacturers_id';

	$result = mysqli_query($db,$SQL);

	$invoicebrandvalue = [];

	while($row = mysqli_fetch_assoc($result)){

		if(array_key_exists($row['manufacturers_id'], $invoicebrandvalue))
			$invoicebrandvalue[$row['manufacturers_id']] += $row['value'];
		else
			$invoicebrandvalue[$row['manufacturers_id']] = $row['value'];

		if(!isset($brandwisesales[$row['manufacturers_id']])){
			$brandwisesales[$row['manufacturers_id']]['id'] = $row['manufacturers_id'];
			$brandwisesales[$row['manufacturers_id']]['name'] = $manufacturers[$row['manufacturers_id']];
		}

	}

	$data = [];

	foreach ($brandwisesales as $key => $value) {
		$data[] = [
			'0' => utf8_encode($value['name']),
			'1' => isset($quotationValueBrand[$key]) ? number_format($quotationValueBrand[$key]):0,
			'2' => isset($ocbrandvalue[$key]) ? number_format($ocbrandvalue[$key]):0,
			'3' => isset($dcbrandvalue[$key]) ? number_format($dcbrandvalue[$key]):0,
			'4' => isset($invoicebrandvalue[$key]) ? number_format($invoicebrandvalue[$key]):0,
		];
	}
	
	echo json_encode($data);
	return;

?>

	
