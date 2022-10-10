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

	$SQL = "SELECT invoice.debtorno,
 				   debtorsmaster.name,
				   invoice.invoiceno,
				   invoice.salescaseref,
				   invoice.branchcode,
				   invoice.customerref,
				   invoice.shopinvoiceno,
				   invoice.comments,
				   invoice.services,
				   invoice.ordertype,
				   invoice.shipvia,
				   invoice.deliverto,
				   invoice.deladd1,
				   invoice.deladd2,
				   invoice.deladd3,
				   invoice.deladd4,
				   invoice.deladd5,
				   invoice.deladd6,
				   invoice.contactphone,
				   invoice.contactemail,
				   invoice.salesperson,
				   invoice.gst,
				   invoice.freightcost,
				   invoice.podate,
				   invoice.invoicesdate,
				   invoice.due,
				   invoice.expected,
				   salescase.salesman as salesmann,
				   debtorsmaster.currcode,
				   currencies.decimalplaces,
				   paymentterms.terms,
				   invoice.fromstkloc,
				   debtorsmaster.customerpoline,
				   locations.locationname,
				   custbranch.estdeliverydays,
				   custbranch.salesman
				FROM invoice
				INNER JOIN salescase
				ON invoice.salescaseref = salescase.salescaseref
				INNER JOIN debtorsmaster
				ON invoice.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch
				ON invoice.debtorno = custbranch.debtorno
				AND invoice.branchcode = custbranch.branchcode
				INNER JOIN paymentterms
				ON debtorsmaster.paymentterms=paymentterms.termsindicator
				INNER JOIN locations
				ON locations.loccode=invoice.fromstkloc
				INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
				WHERE invoice.invoiceno = '" . $orderno . "'
				AND inprogress=1";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Invoice Not Found!!!'
		];

		echo json_encode($response);
		return;	

	}

	$details = mysqli_fetch_assoc($result);

	//Items
	$SQL = "SELECT  invoicedetails.internalitemno,
					invoicedetails.invoicedetailsindex,
					invoicedetails.invoicelineno as orderlineno,
					invoicedetails.invoiceoptionno as lineoptionno,
					invoicedetails.invoiceoptionitemno as optionitemno,
					invoicedetails.stkcode,
					stockmaster.description,
					stockmaster.longdescription,
					stockmaster.materialcost,
					stockmaster.volume,
					stockmaster.grossweight,
					stockmaster.units,
					stockmaster.serialised,
					stockmaster.nextserialno,
					stockmaster.eoq,
					invoicedetails.unitprice,
					invoicedetails.quantity,
					invoicedetails.discountpercent,
					invoicedetails.actualdispatchdate,
					invoicedetails.qtyinvoiced,
					invoicedetails.narrative,
					invoicedetails.itemdue,
					invoicedetails.poline,
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
					invoicedetails.completed
				FROM invoicedetails INNER JOIN stockmaster
				ON invoicedetails.stkcode = stockmaster.stockid
				INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
				INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
				WHERE  locstock.loccode = '" . $details['fromstkloc'] . "'
				AND invoicedetails.invoiceno ='" . $orderno . "'
				ORDER BY invoicedetails.orderlineno";

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
	$SQL = "SELECT * FROM invoiceoptions 
			WHERE  invoiceoptions.invoiceno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);


	$options = [];

	while($row = mysqli_fetch_assoc($result)){

		$line 		 = $row['invoicelineno'];
		$optionindex = $row['invoiceoptionno'];

		//echo "Invoice Option Line: ".$line."<br>";

		$options[$line][$optionindex] = $row;

		$options[$line][$optionindex]['items'] = 
			((isset($items[$line]) && isset($items[$line][$optionindex]))
			? $items[$line][$optionindex] : []);


	}

	//Lines
	$SQL = "SELECT * FROM invoicelines 
			WHERE  invoicelines.invoiceno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	$lines = [];

	while($row = mysqli_fetch_assoc($result)){

		$lineindex = $row['invoicelineno'];

		//echo "Invoice Line: ".$lineindex."<br>";
		
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