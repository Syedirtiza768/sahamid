<?php 

  if(!isset($_GET['start']) || !isset($_GET['end'])){
    echo json_encode([]);
    return;
  }

  $db=mysqli_connect('localhost','irtiza','netetech321','sahamid');
	ini_set('max_execution_time', 180); //120 seconds

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
  $customerwisesales = [];

  //quotationvaluecustomer (customer,salesperson,value)
  $SQL = 'SELECT debtorsmaster.name, 
  salescase.salesman,
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
   
   AND salesorderoptions.optionno = 0 
   AND salesorderdetails.orderno IN 
   (
  	SELECT MAX(orderno) from salesorders group by salescaseref
   )
   AND salesorders.orddate BETWEEN "'.$start.'" AND "'.$end.'"
   
   AND ('.$salesPerson.')
   AND ('.$customertype.')
   AND debtorsmaster.name LIKE "%'.$customer.'%"
   
   GROUP BY debtorsmaster.name
   
   HAVING value>0';

  $result = mysqli_query($db,$SQL);

  $quotationvaluecustomer = [];

  while($row = mysqli_fetch_assoc($result)){

    if(array_key_exists($row['name'], $quotationvaluecustomer))
      $quotationvaluecustomer[$row['name']] += $row['value'];
    else
      $quotationvaluecustomer[$row['name']] = $row['value'];

    if(!isset($customerwisesales[$row['name']])){
      $customerwisesales[$row['name']]['customer'] = $row['name'];
      $customerwisesales[$row['name']]['salesperson'] = $row['salesman'];
    }

  }

  //ocvaluecustomer '(customer,salesperson,value)
  $SQL = 'SELECT debtorsmaster.name, salescase.salesman,
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
   
   GROUP BY debtorsmaster.name';

  $result = mysqli_query($db,$SQL);

  $ocvaluecustomer = [];

  while($row = mysqli_fetch_assoc($result)){

    if(array_key_exists($row['name'], $ocvaluecustomer))
      $ocvaluecustomer[$row['name']] += $row['value'];
    else
      $ocvaluecustomer[$row['name']] = $row['value'];

    if(!isset($customerwisesales[$row['name']])){
      $customerwisesales[$row['name']]['customer'] = $row['name'];
      $customerwisesales[$row['name']]['salesperson'] = $row['salesman'];
    }

  }

  //dcvaluecustomer (customer,salesperson,value)
  $SQL = 'SELECT debtorsmaster.name, salescase.salesman,
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
   AND dcoptions.optionno = 0 
   AND dcs.orddate BETWEEN "'.$start.'" AND "'.$end.'"
   AND ('.$salesPerson.')
   AND ('.$customertype.')
   AND debtorsmaster.name LIKE "%'.$customer.'%"
   GROUP BY debtorsmaster.name';

  $result = mysqli_query($db,$SQL);

  $dcvaluecustomer = [];

  while($row = mysqli_fetch_assoc($result)){

    if(array_key_exists($row['name'], $dcvaluecustomer))
      $dcvaluecustomer[$row['name']] += $row['value'];
    else
      $dcvaluecustomer[$row['name']] = $row['value'];

    if(!isset($customerwisesales[$row['name']])){
      $customerwisesales[$row['name']]['customer'] = $row['name'];
      $customerwisesales[$row['name']]['salesperson'] = $row['salesman'];
    }

  }
  
  //uninvoiceddcvaluecustomer (customer,salesperson,value)
  $SQL = 'SELECT debtorsmaster.name, salescase.salesman,
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
   AND dcoptions.optionno = 0
   AND dcs.invoicegroupid IS NULL
   AND dcs.grbdate = "0000-00-00 00:00:00"
   AND dcs.orddate BETWEEN "'.$start.'" AND "'.$end.'"
   AND ('.$salesPerson.')
   AND ('.$customertype.')
   AND debtorsmaster.name LIKE "%'.$customer.'%"
   GROUP BY debtorsmaster.name';

  $result = mysqli_query($db,$SQL);

  $uidcvaluecustomer = [];

  while($row = mysqli_fetch_assoc($result)){

    if(array_key_exists($row['name'], $uidcvaluecustomer))
      $uidcvaluecustomer[$row['name']] += $row['value'];
    else
      $uidcvaluecustomer[$row['name']] = $row['value'];

    if(!isset($customerwisesales[$row['name']])){
      $customerwisesales[$row['name']]['customer'] = $row['name'];
      $customerwisesales[$row['name']]['salesperson'] = $row['salesman'];
    }

  }
  
  //invoice (customer,salesperson,value)
  $SQL = 'SELECT salesman.salesmanname as salesman,debtorsmaster.name,
			SUM(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity
	 ) as value from invoicedetails 
	 INNER JOIN invoiceoptions on
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
	 GROUP BY debtorsmaster.name';

  $result = mysqli_query($db,$SQL);

  $invoicevaluecustomer = [];

  while($row = mysqli_fetch_assoc($result)){

    if(array_key_exists($row['name'], $invoicevaluecustomer))
      $invoicevaluecustomer[$row['name']] += $row['value'];
    else
      $invoicevaluecustomer[$row['name']] = $row['value'];

    if(!isset($customerwisesales[$row['name']])){
      $customerwisesales[$row['name']]['customer'] = $row['name'];
      $customerwisesales[$row['name']]['salesperson'] = $row['salesman'];
    }

  }


  $data = [];

  foreach ($customerwisesales as $key => $value) {
    $data[] = [
      '0' => utf8_encode($value['customer']),
      '1' => $value['salesperson'],
      '2' => isset($quotationvaluecustomer[$key]) ? number_format($quotationvaluecustomer[$key]):0,
      '3' => isset($ocvaluecustomer[$key]) ? number_format($ocvaluecustomer[$key]):0,
      '4' => isset($dcvaluecustomer[$key]) ? number_format($dcvaluecustomer[$key]):0,
	  '5' => isset($uidcvaluecustomer[$key]) ? number_format($uidcvaluecustomer[$key]):0,
	  '6' => isset($invoicevaluecustomer[$key]) ? number_format($invoicevaluecustomer[$key]):0,
    ];
  }
  
  echo json_encode($data);
  return;


?>