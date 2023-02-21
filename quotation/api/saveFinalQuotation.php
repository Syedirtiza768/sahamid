<?php

	if (!isset($PathPrefix)) {
		$PathPrefix='../../';
	}

	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

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
	$GSTorder = $_POST['GSTorder'];
	
	if(isInValidSalesCase($salescaseref, $orderno)){

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$db = createDBConnection();

/*$SQL="UPDATE salesorderdetailsip
                SET salesorderdetailsip.discountpercent=1-salesorderdetailsip.unitrate/salesorderdetailsip.unitprice
                WHERE salesorderdetailsip.orderno = '$orderno'";
mysqli_query($db, $SQL);*/

 /*   $SQL="UPDATE salesorderdetailsip,stockmaster ON salesorderdetailsip.stkcode=stockmaster.stockid
            SET salesorderdetailsip.discountpercent=stockmaster.materialcost/salesorderdetailsip.unitprice
            WHERE salesorderdetailsip.orderno = '$orderno'";
    mysqli_query($db, $SQL);
    $SQL="UPDATE salesorderdetails,stockmaster ON salesorderdetails.stkcode=stockmaster.stockid
        SET salesorderdetails.discountpercent=stockmaster.materialcost/salesorderdetails.unitprice
        WHERE salesorderdetails.orderno = '$orderno'";
    mysqli_query($db, $SQL);*/

	$SQL = "SELECT salesordersip.debtorno,
 				   debtorsmaster.name,
				   salesordersip.branchcode,
				   salesordersip.existing,
				   salesordersip.eorderno,
				   salesordersip.customerref,
				   salesordersip.salescaseref,
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
				   salesordersip.contactperson,
				   salesordersip.contactphone,
				   salesordersip.contactemail,
				   salesordersip.salesperson,
				   salesordersip.GSTadd,
				   salesordersip.gst,
				   salesordersip.WHT,
				   salesordersip.freightclause,
				   salesordersip.umqd,
				   salesordersip.validity,
				   salesordersip.services,
				   salesordersip.printexchange,
				   salesordersip.freightcost,
				   salesordersip.advance,
				   salesordersip.delivery,
				   salescase.salesman as salesmann,
				   salesordersip.commisioning,
				   salesordersip.after,
				   salesordersip.afterdays,
				   salesordersip.deliverydate,
				   salesordersip.quickQuotation,
				   salesordersip.revision,
				   salesordersip.revision_for,
				   salesordersip.rate_clause,
				   salesordersip.rate_validity,
				   salesordersip.clause_rates,
				   debtorsmaster.currcode,
				   currencies.decimalplaces,
				   paymentterms.terms,
				   salesordersip.fromstkloc,
				   salesordersip.printedpackingslip,
				   salesordersip.datepackingslipprinted,
				   salesordersip.quotation,
				   salesordersip.deliverblind,
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
					salesorderdetailsip.unitrate,
					salesorderdetailsip.quantity,
					salesorderdetailsip.discountpercent,
					salesorderdetailsip.discountupdated,
					salesorderdetailsip.actualdispatchdate,
					salesorderdetailsip.qtyinvoiced,
					salesorderdetailsip.narrative,
					salesorderdetailsip.estimate,
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
	$errors['lines']   = count($lines);
	$errors['elines']  = 0;
	$errors['eoptions']= 0;
	$errors['options'] = 0;
	$errors['itemsuo'] = 0;
	$errors['items']   = 0;
	$errors['itemswq'] = 0;

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

	$pass = true;

	foreach ($errors as $key => $value) {

		if($key == "itemswq" && $value <= 0){

			$pass = false;

		}else if($key == "lines" && $value <= 0){

			$pass = false;

		}else{

			if($key != "itemswq" && $key != "lines"){

				if($value > 0)
					$pass = false;

			}

		}

		if(!$pass)
			break;

	}

	
	$withoutItems = 0;
	
	if(!$pass && $details['quickQuotation'] != 1){
		
		$response = [
			'status' => 'error',
			'message' => 'Errors found.',
		];

		echo json_encode($response);
		return;	

	}
	
	if($details['quickQuotation'] == 1 && !$pass){
		$withoutItems = 1;
	}

	$db = createDBConnection();

	if($details['existing'] == 0){

		$eorderno = GetNextTransNo(30, $db);

		$SQL = "INSERT INTO salesorders (orderno,salescaseref,orddate)
				VALUES ('".$eorderno."','".$salescaseref."','".date('Y-m-d')."')";

		$res = mysqli_query($db, $SQL);

		if(!$res){

			$response = [
				'status' => 'error',
				'message' => 'New Insert failed.',
			];

			echo json_encode($response);
			return;	

		}

	}else{

		$eorderno = $details['eorderno'];

	}

	$salescaseref = $details['salescaseref'];



	$SQL = "SELECT GSTadd FROM salesorders 
			WHERE  salesorders.orderno ='" . $GSTorder . "'";

	$result = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($result)){
	$GST = $row['GSTadd'];
	}

	if($GST == 'update'){
		$details['GSTadd'] = 'update';
	}

	$SQL = "UPDATE `salesorders` SET 
			`debtorno`='".str_replace("'","\'",$details['debtorno'])."',
			`branchcode`='".str_replace("'","\'",$details['branchcode'])."',
			`customerref`='".str_replace("'","\'",$details['customerref'])."',
			`comments`='".str_replace("'","\'",$details['comments'])."',
			`ordertype`='".str_replace("'","\'",$details['ordertype'])."',
			`shipvia`='".str_replace("'","\'",$details['shipvia'])."',
			`deladd1`='".str_replace("'","\'",$details['deladd1'])."',
			`deladd2`='".str_replace("'","\'",$details['deladd2'])."',
			`deladd3`='".str_replace("'","\'",$details['deladd3'])."',
			`deladd4`='".str_replace("'","\'",$details['deladd4'])."',
			`deladd5`='".str_replace("'","\'",$details['deladd5'])."',
			`deladd6`='".str_replace("'","\'",$details['deladd6'])."',
			`contactperson`='".str_replace("'","\'",$details['contactperson'])."',
			`contactphone`='".str_replace("'","\'",$details['contactphone'])."',
			`contactemail`='".str_replace("'","\'",$details['contactemail'])."',
			`deliverto`='".str_replace("'","\'",$details['deliverto'])."',
			`deliverblind`='".str_replace("'","\'",$details['deliverblind'])."',
			`freightcost`='".str_replace("'","\'",$details['freightcost'])."',
			`advance`='".str_replace("'","\'",$details['advance'])."',
			`delivery`='".str_replace("'","\'",$details['delivery'])."',
			`commisioning`='".str_replace("'","\'",$details['commisioning'])."',
			`after`='".str_replace("'","\'",$details['after'])."',
			`gst`='".str_replace("'","\'",$details['gst'])."',
			`afterdays`='".str_replace("'","\'",$details['afterdays'])."',
			`fromstkloc`='".str_replace("'","\'",$details['fromstkloc'])."',
			`deliverydate`='".str_replace("'","\'",$details['deliverydate'])."',
			`confirmeddate`='".str_replace("'","\'",$details['confirmeddate'])."',
			`printedpackingslip`='".str_replace("'","\'",$details['printedpackingslip'])."',
			`datepackingslipprinted`='".str_replace("'","\'",$details['datepackingslipprinted'])."',
			`quotation`='1',
			`rate_clause`='".$details['rate_clause']."',
			`rate_validity`='".$details['rate_validity']."',
			`clause_rates`='".$details['clause_rates']."',
			`quotedate`='".str_replace("'","\'",$details['quotedate'])."',
			`salesperson`='".str_replace("'","\'",$details['salesperson'])."',
			`GSTadd`='".str_replace("'","\'",$details['GSTadd'])."',
			`services`='".str_replace("'","\'",$details['services'])."',
			`printexchange`='".str_replace("'","\'",$details['printexchange'])."',
			`WHT`='".str_replace("'","\'",$details['WHT'])."',
			`freightclause`='".str_replace("'","\'",$details['freightclause'])."',
			`umqd`='".str_replace("'","\'",$details['umqd'])."',
			`validity`='".str_replace("'","\'",$details['validity'])."',
			`quickQuotation`='".$details['quickQuotation']."',
			`withoutItems`='".$withoutItems."', 			
			`revision`='".$details['revision']."',  			
			`revision_for`='".$details['revision_for']."'  			
			WHERE salescaseref='".$salescaseref."'
			AND orderno ='".$eorderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Quotation Update Failed.',
		];

		echo json_encode($response);
		return;	

	}

	if($details['existing'] == 1){

		$SQL = "DELETE FROM `salesorderdetails` WHERE orderno='".$eorderno."'";

		mysqli_query($db, $SQL);

		$SQL = "DELETE FROM `salesorderoptions` WHERE orderno='".$eorderno."'";

		mysqli_query($db, $SQL);

		$SQL = "DELETE FROM `salesorderlines` WHERE orderno='".$eorderno."'";

		mysqli_query($db, $SQL);

	}

	$orderno = $eorderno;

	$lineNo = 0;

	foreach ($details['lines'] as $lkey => $lvalue) {
        if($details['existing'] == 1){
	    $SQL = "UPDATE quotationmodifications SET lineno=$lineNo,eorderno=$orderno WHERE lineno=".$lvalue['lineindex']."";
        mysqli_query($db, $SQL);
            }
		$SQL = "INSERT INTO `salesorderlines`
				(`orderno`, `lineno`, `clientrequirements`) 
				VALUES ('".$orderno."','".$lineNo."','".addslashes($lvalue['clientrequirements'])."')";

		$lrs = mysqli_query($db, $SQL);

		if(!$lrs){

			$response = [
				'status' => 'error',
				'message' => 'Line Insert Failed.',
				'sql' => $SQL
			];

			echo json_encode($response);
			return;	

		}

		$optionNo = 0;
		
		foreach($lvalue['options'] as $okey => $ovalue){

			$SQL = "INSERT INTO `salesorderoptions`
					(`orderno`, `lineno`, `optionno`, 
					`optiontext`, `stockstatus`, `quantity`,`uom`,`price`) 
					VALUES ('".str_replace("'","\'",$orderno)."','".str_replace("'","\'",$lineNo)."','".str_replace("'","\'",$optionNo)."',
					'".str_replace("'","\'",$ovalue['optiontext'])."','".str_replace("'","\'",$ovalue['stockstatus'])."',
					'".str_replace("'","\'",$ovalue['quantity'])."','".str_replace("'","\'",$ovalue['uom'])."','".$ovalue['price']."')";


			$ors = mysqli_query($db, $SQL);

			if(!$ors){

				$response = [
					'status' => 'error',
					'message' => 'Option Insert Failed.',
					'sql' => $SQL
				];

				echo json_encode($response);
				return;	

			}

			$itemsCount = 0;

			foreach ($ovalue['items'] as $ikey => $ivalue) {
				
				$SQL = "INSERT INTO `salesorderdetails`
						(`orderlineno`, `orderno`, `lineoptionno`, `optionitemno`, `internalitemno`, `stkcode`, `qtyinvoiced`, `unitprice`, `unitrate`,`lastcostupdate`,`lastupdatedby`, 
						`quantity`, `estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`, `poline`) 
						VALUES ('".$lineNo."','".$orderno."','".$optionNo."','".$itemsCount."','".$itemsCount."','".$ivalue['stkcode']."',
						'".$ivalue['qtyinvoiced']."','".$ivalue['standardcost']."','".$ivalue['unitrate']."','".$ivalue['lastcostupdate']."','".$ivalue['lastupdatedby']."','".$ivalue['quantity']."','".$ivalue['estimate']."',
						'".$ivalue['discountpercent']."','".$ivalue['actualdispatchdate']."','".$ivalue['completed']."','".$ivalue['narrative']."',
						'".$ivalue['itemdue']."','".$ivalue['poline']."')";

				$irs = mysqli_query($db, $SQL);
                $SQL="UPDATE salesorderdetails INNER JOIN stockmaster ON salesorderdetails.stkcode=stockmaster.stockid
                SET salesorderdetails.discountpercent=1-salesorderdetails.unitrate/stockmaster.materialcost
                WHERE salesorderdetails.orderno = '$orderno'";
                mysqli_query($db, $SQL);

				if(!$irs){

					$response = [
						'status' => 'error',
						'message' => 'Item Insert Failed.',
						'sql' => $SQL
					];

					echo json_encode($response);
					return;	

				}

				$itemsCount += 1;

			}

			$optionNo += 1;

		}

		$lineNo += 1;

	}

	$SQL = "DELETE FROM `salesorderdetailsip` WHERE orderno='".$_POST['orderno']."'";

	mysqli_query($db, $SQL);

	$SQL = "DELETE FROM `salesorderoptionsip` WHERE orderno='".$_POST['orderno']."'";

	mysqli_query($db, $SQL);

	$SQL = "DELETE FROM `salesorderlinesip` WHERE orderno='".$_POST['orderno']."'";

	mysqli_query($db, $SQL);

	$SQL = "DELETE FROM `salesordersip` WHERE orderno='".$_POST['orderno']."'";

	mysqli_query($db, $SQL);

	echo json_encode([]);

?>