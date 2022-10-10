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

	$opportunitypipeline = [];

	//quotationvaluereports (salescaseref,value)
	$SQL = 'SELECT salesorders.salescaseref,
	SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
	 ) as value from salesorderdetails INNER JOIN salesorderoptions on
	 (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
	 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=salesorders.salescaseref
	 
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE salesorderdetails.lineoptionno = 0 
	 
	 and salesorderoptions.optionno = 0 
	 AND salesorderdetails.orderno in 
	 (
		SELECT MAX(orderno) from salesorders group by salescaseref
	 )
	 
	 GROUP BY salesorders.salescaseref';

	$result = mysqli_query($db,$SQL);

	$quotationvaluereports = [];

	while($row = mysqli_fetch_assoc($result)){

		$quotationvaluereports[$row['salescaseref']] = $row['value'];

	}

	//ocvaluereports (salescaseref,value)
	$SQL = 'SELECT ocs.salescaseref,
	SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity
	 ) as value from ocdetails INNER JOIN ocoptions on
	 (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
	 INNER JOIN ocs on ocs.orderno = ocdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=ocs.salescaseref
	 
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE ocdetails.lineoptionno = 0 
	 
	 and ocoptions.optionno = 0 
	 
	
	 
	 GROUP BY ocs.salescaseref';

	$result = mysqli_query($db,$SQL);

	$ocvaluereports = [];

	while($row = mysqli_fetch_assoc($result)){

		$ocvaluereports[$row['salescaseref']] = $row['value'];

	}

	//dcvaluereports (salescaseref,value)
	$SQL = 'SELECT dcs.salescaseref,
	SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
	 ) as value from dcdetails INNER JOIN dcoptions on
	 (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
	 INNER JOIN dcs on dcs.orderno = dcdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE dcdetails.lineoptionno = 0 
	 
	 and dcoptions.optionno = 0 
	 
	 
	 GROUP BY dcs.salescaseref';

	$result = mysqli_query($db,$SQL);

	$dcvaluereports = [];

	while($row = mysqli_fetch_assoc($result)){

		$dcvaluereports[$row['salescaseref']] = $row['value'];

	}

	//Sales Case
	$SQL = 'SELECT salescase.salescaseindex,salescase.salescaseref, salescase.salescasedescription,
			salescase.salesman,salescase.commencementdate,debtorsmaster.name,salescase.enquiryvalue FROM salescase 
			INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
			WHERE salescase.commencementdate > "'.$start.'" 
			AND salescase.commencementdate <"'.$end.'"
			AND salescase.salesman LIKE "%'.$salesPerson.'%"
			AND debtorsmaster.name LIKE "%'.$customer.'%"
			AND salescase.closed=0';

	$result = mysqli_query($db,$SQL);

	while($row = mysqli_fetch_assoc($result)){

		if(array_key_exists($row['salescaseref'], $quotationvaluereports)){
			if(array_key_exists($row['salescaseref'], $ocvaluereports)){
				if(array_key_exists($row['salescaseref'], $dcvaluereports)){

					$oc = round($ocvaluereports[$row['salescaseref']]);
					$dc = round($dcvaluereports[$row['salescaseref']]);

					if($dc > $oc){

						$opportunitypipeline[] = [

							'0' => utf8_encode($row['salescaseindex']),
							'1' => utf8_encode($row['salescaseref']),
							'2' => utf8_encode($row['salescasedescription']),
							'3' => utf8_encode($row['salesman']),
							'4' => utf8_encode($row['commencementdate']),
							'5' => utf8_encode($row['name']),
							'6' => utf8_encode($row['enquiryvalue']),
							'7' => utf8_encode($quotationvaluereports[$row['salescaseref']]),
							'8' => $oc,
							'9' => $dc

						];

					}

				}

			}

		}

	}

	echo json_encode($opportunitypipeline);
	return;

?>