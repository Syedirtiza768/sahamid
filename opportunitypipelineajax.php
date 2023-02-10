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

	

		if($customertype == "All" || $customertype == "0"){
			$customertype = 'debtortype.typename LIKE "%%"';
		} else {
			$customertype = explode(',', $customertype);
			foreach ($customertype as $values) {
			$customertypes[] = 'debtortype.typename = "' . $values . '" OR';
		}
		$customertype = implode(' ', $customertypes);
		$customertype = substr($customertype, 0, -2);
}
		
		if($salesPerson == "All" || $salesPerson == "0"){
			$salesPerson = 'salescase.salesman LIKE "%%"';
			$salesman = 'salesman.salesmanname LIKE "%%"';
		} else {
			$salesPerson = explode(',',$salesPerson);
		foreach ($salesPerson as $values) {
			$salesPersons[] = 'salescase.salesman = "' . $values . '" OR';
			$salesman[] = 'salesman.salesmanname = "' . $values . '" OR';
		}
		$salesPerson = implode(' ', $salesPersons);
		$salesman = implode(' ', $salesman);
		$salesPerson = substr($salesPerson, 0, -2);
		$salesman = substr($salesman, 0, -2);
		}

	$opportunitypipeline = [];

	//quotationvaluereports (salescaseref,value)
	$SQL = 'SELECT salesorders.salescaseref from salesorders';

	$result = mysqli_query($db,$SQL);

	$quotationvaluereports = [];

	while($row = mysqli_fetch_assoc($result)){

		$quotationvaluereports[$row['salescaseref']] = 'Boom';

	}

	//Sales Case
	$SQL = 'SELECT salescase.salescaseindex,salescase.salescaseref, salescase.salescasedescription,
			salescase.salesman,salescase.commencementdate,debtorsmaster.name,salescase.enquiryvalue FROM salescase 
			INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
			INNER JOIN debtortype on debtortype.typeid=debtorsmaster.typeid
			WHERE salescase.commencementdate > "'.$start.'" 
			AND salescase.commencementdate < "'.$end.'"
			AND '.$salesPerson.'
			AND '.$customertype.'
			AND debtorsmaster.name LIKE "%'.$customer.'%"
			AND salescase.closed=0';

	$result = mysqli_query($db,$SQL);

	while($row = mysqli_fetch_assoc($result)){

		// if(!array_key_exists($row['salescaseref'], $quotationvaluereports)){

			$opportunitypipeline[] = [

				'0' => utf8_encode($row['salescaseindex']),
				'1' => utf8_encode($row['salescaseref']),
				'2' => utf8_encode($row['salescasedescription']),
				'3' => utf8_encode($row['salesman']),
				'4' => utf8_encode($row['commencementdate']),
				'5' => utf8_encode($row['name']),
				'6' => utf8_encode($row['enquiryvalue'])

			];

		// }

	}

	echo json_encode($opportunitypipeline);
	return;

// 	//quotationvaluereports (salescaseref,value)
// 	$SQL = 'SELECT salesorders.salescaseref,
// 	SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
// 	 ) as value from salesorderdetails INNER JOIN salesorderoptions on
// 	 (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
// 	 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
// 	 INNER JOIN salescase on salescase.salescaseref=salesorders.salescaseref
	 
// 	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
// 	 INNER JOIN debtortype on debtortype.typeid=debtorsmaster.typeid
// 	 WHERE salesorderdetails.lineoptionno = 0 
	 
// 	 and salesorderoptions.optionno = 0 
// 	 AND salesorderdetails.orderno in 
// 	 (
// 		SELECT MAX(orderno) from salesorders group by salescaseref
// 	 )
// 	 AND salesorders.orddate BETWEEN "'.$start.'" AND "'.$end.'"
// 	 AND '.$salesPerson.'
// 	 AND '.$customertype.'
// 	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
// 	 GROUP BY salesorders.salescaseref';

// 	$result = mysqli_query($db,$SQL);

// 	$quotationvaluereports = [];

// 	while($row = mysqli_fetch_assoc($result)){

// 		$quotationvaluereports[$row['salescaseref']] = $row['value'];

// 	}

// 	//ocvaluereports (salescaseref,value)
// 	$SQL = 'SELECT ocs.salescaseref,
// 	SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity
// 	 ) as value from ocdetails INNER JOIN ocoptions on
// 	 (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
// 	 INNER JOIN ocs on ocs.orderno = ocdetails.orderno
// 	 INNER JOIN salescase on salescase.salescaseref=ocs.salescaseref
	 
// 	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
// 	 INNER JOIN debtortype on debtortype.typeid=debtorsmaster.typeid
// 	 WHERE ocdetails.lineoptionno = 0 
	 
// 	 and ocoptions.optionno = 0 
	 
// 	 AND ocs.orddate BETWEEN "'.$start.'" AND "'.$end.'"
// 	 AND '.$salesPerson.'
// 	 AND '.$customertype.'
// 	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
// 	 GROUP BY ocs.salescaseref';

// 	$result = mysqli_query($db,$SQL);

// 	$ocvaluereports = [];

// 	while($row = mysqli_fetch_assoc($result)){

// 		$ocvaluereports[$row['salescaseref']] = $row['value'];

// 	}

// 	//dcvaluereports (salescaseref,value)
// 	$SQL = 'SELECT dcs.salescaseref,
// 	SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
// 	 ) as value from dcdetails INNER JOIN dcoptions on
// 	 (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
// 	 INNER JOIN dcs on dcs.orderno = dcdetails.orderno
// 	 INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
// 	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
// 	 INNER JOIN debtortype on debtortype.typeid=debtorsmaster.typeid
// 	 WHERE dcdetails.lineoptionno = 0 
	 
// 	 and dcoptions.optionno = 0 
// 	 AND dcs.orddate BETWEEN "'.$start.'" AND "'.$end.'"
// 	 AND '.$salesPerson.'
// 	 AND '.$customertype.'
// 	 AND debtorsmaster.name LIKE "%'.$customer.'%"
	 
// 	 GROUP BY dcs.salescaseref';

// 	$result = mysqli_query($db,$SQL);

// 	$dcvaluereports = [];

// 	while($row = mysqli_fetch_assoc($result)){

// 		$dcvaluereports[$row['salescaseref']] = $row['value'];

// 	}

// 	echo json_encode([]);
// return;

// 	//advancedreporting (salescaseindex,salescaseref,salescasedescription,salesman,debtorname,branchcode,commencementdate,enquiryvalue)

// 	$SQL = 'SELECT  salescase.salescaseindex,salescase.salescaseref,salescase.salescasedescription,
// 	salescase.salesman,debtorsmaster.name,salescase.branchcode,salescase.commencementdate,
// 	salescase.enquiryvalue FROM 
// 	quotationvaluereports'.$_SESSION['UserID'].' RIGHT OUTER JOIN salescase ON  
// 	 quotationvaluereports'.$_SESSION['UserID'].'.salescaseref= salescase.salescaseref
// 	 INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
// 	 UNION
	 
// 	SELECT  salescase.salescaseindex,salescase.salescaseref,salescase.salescasedescription,
// 	salescase.salesman,debtorsmaster.name,salescase.branchcode,salescase.commencementdate,
// 	salescase.enquiryvalue FROM 
// 	ocvaluereports'.$_SESSION['UserID'].' RIGHT OUTER JOIN salescase ON  
// 	 ocvaluereports'.$_SESSION['UserID'].'.salescaseref= salescase.salescaseref
// 	 INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
	 
// 	 UNION
// 	SELECT  salescase.salescaseindex,salescase.salescaseref,salescase.salescasedescription,
// 	salescase.salesman,debtorsmaster.name,salescase.branchcode,salescase.commencementdate,
// 	salescase.enquiryvalue FROM 
// 	dcvaluereports'.$_SESSION['UserID'].' RIGHT OUTER JOIN salescase ON  
// 	 dcvaluereports'.$_SESSION['UserID'].'.salescaseref= salescase.salescaseref
// 	 INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno';
	 

// 	mysqli_query($db,$SQL);


//  $SQL='UPDATE advancedreporting'.$_SESSION['UserID'].'
// SET advancedreporting'.$_SESSION['UserID'].'.dcvalue 
// =
// (
// 	SELECT dcvaluereports'.$_SESSION['UserID'].'.value
// 	FROM dcvaluereports'.$_SESSION['UserID'].'
// 	WHERE 
	
// 	advancedreporting'.$_SESSION['UserID'].'.salescaseref=
// 	dcvaluereports'.$_SESSION['UserID'].'.salescaseref
// )

// ';
// mysqli_query($db,$SQL);
//  $SQL='UPDATE advancedreporting'.$_SESSION['UserID'].'
// SET advancedreporting'.$_SESSION['UserID'].'.ocvalue 
// =
// (
// 	SELECT ocvaluereports'.$_SESSION['UserID'].'.value
// 	FROM ocvaluereports'.$_SESSION['UserID'].'
// 	WHERE 
	
// 	advancedreporting'.$_SESSION['UserID'].'.salescaseref=
// 	ocvaluereports'.$_SESSION['UserID'].'.salescaseref
// )

// ';

// mysqli_query($db,$SQL);

//  $SQL='UPDATE advancedreporting'.$_SESSION['UserID'].'
// SET advancedreporting'.$_SESSION['UserID'].'.quotationvalue 
// =
// (
// 	SELECT quotationvaluereports'.$_SESSION['UserID'].'.value
// 	FROM quotationvaluereports'.$_SESSION['UserID'].'
// 	WHERE 
	
// 	advancedreporting'.$_SESSION['UserID'].'.salescaseref=
// 	quotationvaluereports'.$_SESSION['UserID'].'.salescaseref
// )

// ';mysqli_query($db,$SQL);


//  $SQL1 = '
// 		DELETE FROM  advancedreporting'.$_SESSION['UserID'].'
		
// 		WHERE salescaseref IN (
// 		SELECT DISTINCT salesorders.salescaseref FROM salesorders INNER JOIN salescase ON 
// 		salesorders.salescaseref=salescase.salescaseref WHERE salescase.closed=1
// 		UNION
// 		SELECT DISTINCT ocs.salescaseref FROM ocs INNER JOIN salescase ON 
// 		ocs.salescaseref=salescase.salescaseref WHERE salescase.closed=1
		
// 		UNION
// 		SELECT DISTINCT dcs.salescaseref FROM dcs INNER JOIN salescase ON 
// 		dcs.salescaseref=salescase.salescaseref WHERE salescase.closed=1
// 		)
// 		OR salescaseref IN (
		
// 		SELECT salescaseref from salescase WHERE
		 
// 		 (salescase.commencementdate < "'.$_SESSION['startdate'].'" OR

// 		 salescase.commencementdate >"'.$_SESSION['enddate'].'")  
 
		
// )


// ';

// $SQL = '
// 		DELETE FROM  advancedreporting'.$_SESSION['UserID'].'
		
// 		WHERE salescaseref IN (
// 		SELECT DISTINCT salesorders.salescaseref FROM salesorders 
// 		UNION
// 		SELECT DISTINCT ocs.salescaseref FROM ocs
		
// 		UNION
// 		SELECT DISTINCT dcs.salescaseref FROM dcs 
// 		UNION
		
// 		SELECT salescaseref from salescase WHERE
		 
// 		 (salescase.commencementdate < "'.$_SESSION['startdate'].'" OR

// 		 salescase.commencementdate >"'.$_SESSION['enddate'].'")  
		 
// 		 UNION SELECT salescaseref FROM salescase WHERE salescase.closed=1
 
		
// )';

// mysqli_query($db,$SQL);
 

// 
