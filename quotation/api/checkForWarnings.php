<?php

	include('../misc.php');

	$response = [];
	
	if(!validHeaders()){

		$response = [
			'status' => 'error',
			'message' => 'Refresh or ReLogin Required.'
		];

		echo json_encode($response);
		return;		

	}

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];
	
	if(isInValidSalesCase($salescaseref, $orderno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();

	$SQL = "SELECT salesordersip.debtorno,
 				   debtorsmaster.name,
				   salesordersip.branchcode,
				   salesordersip.existing,
				   salesordersip.eorderno,
				   salesordersip.customerref,
				   salesordersip.comments,
				   salesordersip.orddate,
				   salesordersip.ordertype,
				   salesordersip.shipvia,
				   salesordersip.deliverto,
				   salesordersip.deladd1,
				   salesordersip.deladd2,
				   salesordersip.deladd3,
				   salesordersip.deladd4,
				   salesordersip.deladd5,
				   salesordersip.deladd6,
				   salesordersip.quotedate,
				   salesordersip.confirmeddate,
				   salesordersip.contactphone,
				   salesordersip.contactemail,
				   salesordersip.salesperson,
				   salesordersip.GSTadd,
				   salesordersip.gst,
				   salesordersip.WHT,
				   salesordersip.services,
				   salesordersip.freightcost,
				   salesordersip.advance,
				   salesordersip.delivery,
				   salescase.salesman as salesmann,
				   salesordersip.commisioning,
				   salesordersip.after,
				   salesordersip.afterdays,
				   salesordersip.deliverydate,
				   debtorsmaster.currcode,
				   currencies.decimalplaces,
				   paymentterms.terms,
				   salesordersip.fromstkloc,
				   salesordersip.printedpackingslip,
				   salesordersip.datepackingslipprinted,
				   salesordersip.quotation,
				   salesordersip.deliverblind,
				   salesordersip.quickQuotation,
				   debtorsmaster.customerpoline,
				   locations.locationname,
				   custbranch.estdeliverydays,
				   custbranch.salesman
				FROM salesordersip
				INNER JOIN salescase
				ON salesordersip.salescaseref = salescase.salescaseref
				INNER JOIN debtorsmaster
				ON salesordersip.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch
				ON salesordersip.debtorno = custbranch.debtorno
				AND salesordersip.branchcode = custbranch.branchcode
				INNER JOIN paymentterms
				ON debtorsmaster.paymentterms=paymentterms.termsindicator
				INNER JOIN locations
				ON locations.loccode=salesordersip.fromstkloc
				INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
				WHERE salesordersip.orderno = '" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Quotation Not Found!!!'
		];

		echo json_encode($response);
		return;	

	}

	$details = mysqli_fetch_assoc($result);
	
	if($details['quickQuotation'] == 1){
		
		echo json_encode([
			'status' => 'bypass',
			'formid' => $_SESSION['FormID']
		]);
		return;
		
	}

	//Items
	$SQL = "SELECT  salesorderdetailsip.internalitemno,
					salesorderdetailsip.salesorderdetailsindex,
					salesorderdetailsip.orderlineno,
					salesorderdetailsip.lineoptionno,
					salesorderdetailsip.optionitemno,
					salesorderdetailsip.stkcode,
					stockmaster.description,
					stockmaster.longdescription,
					stockmaster.materialcost,
					stockmaster.volume,
					stockmaster.grossweight,
					stockmaster.units,
					stockmaster.serialised,
					stockmaster.nextserialno,
					stockmaster.eoq,
					salesorderdetailsip.unitprice,
					salesorderdetailsip.quantity,
					salesorderdetailsip.discountpercent,
					salesorderdetailsip.actualdispatchdate,
					salesorderdetailsip.qtyinvoiced,
					salesorderdetailsip.narrative,
					salesorderdetailsip.itemdue,
					salesorderdetailsip.poline,
					locstock.quantity as qohatloc,
					stockmaster.mbflag,
					stockmaster.discountcategory,
					stockmaster.decimalplaces,
					stockmaster.lastupdatedby,
					stockmaster.lastcostupdate,
					stockmaster.mnfcode,
					stockmaster.mnfpno,
					stockmaster.categoryid,
					manufacturers.manufacturers_name as manu_name,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
					salesorderdetailsip.completed
				FROM salesorderdetailsip INNER JOIN stockmaster
				ON salesorderdetailsip.stkcode = stockmaster.stockid
				INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
				INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
				WHERE  locstock.loccode = '" . $details['fromstkloc'] . "'
				AND salesorderdetailsip.orderno ='" . $orderno . "'
				ORDER BY salesorderdetailsip.orderlineno";

	$result = mysqli_query($db, $SQL);

	$items = [];

	while($row = mysqli_fetch_assoc($result)){

		$line 	= $row['orderlineno'];
		$option = $row['lineoptionno'];

		if(!(array_key_exists($line, $items))){

			$items[$line] = [];			

		}

		if(!(array_key_exists($option, $items[$line]))){

			$items[$line][$option] = [];

		}

		$items[$line][$option][] = $row;

	}


	//Options
	$SQL = "SELECT * FROM salesorderoptionsip 
			WHERE  salesorderoptionsip.orderno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	$options = [];

	while($row = mysqli_fetch_assoc($result)){

		$line 		 = $row['lineno'];
		$optionindex = $row['optionindex'];

		$options[$line][$optionindex] = $row;

		$options[$line][$optionindex]['items'] = 
			((isset($items[$line]) && isset($items[$line][$optionindex]))
			? $items[$line][$optionindex] : []);


	}

	//Lines
	$SQL = "SELECT * FROM salesorderlinesip 
			WHERE  salesorderlinesip.orderno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	$lines = [];

	while($row = mysqli_fetch_assoc($result)){

		$lineindex = $row['lineindex'];
		
		$lines[$lineindex] = $row;

		$lines[$lineindex]['options'] = (isset($options[$lineindex])) ? $options[$lineindex] : [];

	}

	closeDBConnection($db);

	$details['lines'] = $lines;

	$errors = [];
	$errors['status']  = "";
	$errors['lines']   = count($lines);
	$errors['elines']  = 0;
	$errors['eoptions']= 0;
	$errors['options'] = 0;
	$errors['itemsuo'] = 0;
	$errors['items']   = 0;
	$errors['itemswq'] = 0;
	$errors['formid'] = $_SESSION['FormID'];

	foreach ($lines as $lkey => $lvalue) {

		if(count($lvalue['options']) == 0){
			$errors['elines'] += 1;
			continue;
		}
		
		foreach($lvalue['options'] as $okey => $ovalue){

			if(count($ovalue['items']) == 0){
				$errors['eoptions'] += 1;
				continue;
			}

			if($ovalue['quantity'] == 0){
				$errors['options'] += 1;
				$errors['itemsuo'] += count($ovalue['items']);
				continue;
			}

			foreach ($ovalue['items'] as $ikey => $ivalue) {
				
				if($ivalue['quantity'] == 0){

					$errors['items'] += 1;
				
				}else{

					$errors['itemswq'] += 1;

				}

			}

		}

	}

	echo json_encode($errors);

?>