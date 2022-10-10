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

	if(!isset($_POST['orderno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$orderno = $_POST['orderno'];

	$db = createDBConnection();

	$SQL = "SELECT ocs.debtorno,
 				   debtorsmaster.name,
				   ocs.orderno,
				   ocs.inprogress,
				   ocs.branchcode,
				   ocs.customerref,
				   ocs.comments,
				   ocs.orddate,
				   ocs.ordertype,
				   ocs.shipvia,
				   ocs.deliverto,
				   ocs.deladd1,
				   ocs.deladd2,
				   ocs.deladd3,
				   ocs.deladd4,
				   ocs.deladd5,
				   ocs.deladd6,
				   ocs.quotedate,
				   ocs.confirmeddate,
				   ocs.contactphone,
				   ocs.contactemail,
				   ocs.salesperson,
				   ocs.GSTadd,
				   ocs.gst,
				   ocs.WHT,
				   ocs.services,
				   ocs.freightcost,
				   ocs.advance,
				   ocs.delivery,
				   salescase.salesman as salesmann,
				   ocs.commisioning,
				   ocs.after,
				   ocs.afterdays,
				   ocs.deliverydate,
				   debtorsmaster.currcode,
				   currencies.decimalplaces,
				   paymentterms.terms,
				   ocs.fromstkloc,
				   ocs.printedpackingslip,
				   ocs.datepackingslipprinted,
				   ocs.quotation,
				   ocs.deliverblind,
				   debtorsmaster.customerpoline,
				   locations.locationname,
				   custbranch.estdeliverydays,
				   custbranch.salesman
				FROM ocs
				INNER JOIN salescase
				ON ocs.salescaseref = salescase.salescaseref
				INNER JOIN debtorsmaster
				ON ocs.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch
				ON ocs.debtorno = custbranch.debtorno
				AND ocs.branchcode = custbranch.branchcode
				INNER JOIN paymentterms
				ON debtorsmaster.paymentterms=paymentterms.termsindicator
				INNER JOIN locations
				ON locations.loccode=ocs.fromstkloc
				INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
				WHERE ocs.orderno = '" . $orderno . "'";

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
	$SQL = "SELECT  ocdetails.internalitemno,
					ocdetails.ocdetailsindex,
					ocdetails.orderlineno,
					ocdetails.lineoptionno,
					ocdetails.optionitemno,
					ocdetails.stkcode,
					stockmaster.description,
					stockmaster.longdescription,
					stockmaster.materialcost,
					stockmaster.volume,
					stockmaster.grossweight,
					stockmaster.units,
					stockmaster.serialised,
					stockmaster.nextserialno,
					stockmaster.eoq,
					ocdetails.unitprice,
					ocdetails.quantity,
					ocdetails.discountpercent,
					ocdetails.actualdispatchdate,
					ocdetails.qtyinvoiced,
					ocdetails.narrative,
					ocdetails.itemdue,
					ocdetails.poline,
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
					ocdetails.completed
				FROM ocdetails INNER JOIN stockmaster
				ON ocdetails.stkcode = stockmaster.stockid
				INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
				INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
				WHERE  locstock.loccode = '" . $details['fromstkloc'] . "'
				AND ocdetails.orderno ='" . $orderno . "'
				ORDER BY ocdetails.orderlineno";

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
	$SQL = "SELECT * FROM ocoptions 
			WHERE  ocoptions.orderno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	$options = [];

	while($row = mysqli_fetch_assoc($result)){

		$line 		 = $row['lineno'];
		$optionindex = $row['optionno'];

		$options[$line][$optionindex] = $row;

		$options[$line][$optionindex]['items'] = 
			((isset($items[$line]) && isset($items[$line][$optionindex]))
			? $items[$line][$optionindex] : []);


	}

	//Lines
	$SQL = "SELECT * FROM oclines 
			WHERE  oclines.orderno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	$lines = [];

	while($row = mysqli_fetch_assoc($result)){

		$lineindex = $row['lineno'];
		
		$lines[$lineindex] = $row;

		$lines[$lineindex]['options'] = (isset($options[$lineindex])) ? $options[$lineindex] : [];

	}

	closeDBConnection($db);

	$details['lines'] = $lines;

	$response['status'] = "success";
	$response['formid'] = $_SESSION['FormID'];
	$response['data']	= $details;

	utf8_encode_deep($response);
	echo json_encode($response);
	return;


?>