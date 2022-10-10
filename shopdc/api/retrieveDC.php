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

	$SQL = "SELECT dcs.debtorno,
 				   debtorsmaster.name,
				   dcs.orderno,
				   dcs.salescaseref,
				   dcs.inprogress,
				   dcs.GSTAdd,
				   dcs.branchcode,
				   dcs.customerref,
				   dcs.comments,
				   dcs.orddate,
				   dcs.ordertype,
				   dcs.shipvia,
				   dcs.deliverto,
				   dcs.deladd1,
				   dcs.deladd2,
				   dcs.deladd3,
				   dcs.deladd4,
				   dcs.deladd5,
				   dcs.deladd6,
				   dcs.quotedate,
				   dcs.confirmeddate,
				   dcs.contactphone,
				   dcs.contactemail,
				   dcs.salesperson,
				   dcs.gst,
				   dcs.freightcost,
				   dcs.advance,
				   dcs.delivery,
				   salescase.salesman as salesmann,
				   dcs.commisioning,
				   dcs.after,
				   dcs.afterdays,
				   dcs.deliverydate,
				   debtorsmaster.currcode,
				   currencies.decimalplaces,
				   paymentterms.terms,
				   dcs.fromstkloc,
				   dcs.printedpackingslip,
				   dcs.datepackingslipprinted,
				   dcs.quotation,
				   dcs.services,
				   dcs.deliverblind,
				   dcs.mp,
				   debtorsmaster.customerpoline,
				   debtorsmaster.creditlimit,
				   locations.locationname,
				   custbranch.estdeliverydays,
				   custbranch.salesman,
				   dcs.dispatchthrough
				FROM dcs
				INNER JOIN salescase
				ON dcs.salescaseref = salescase.salescaseref
				INNER JOIN debtorsmaster
				ON dcs.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch
				ON dcs.debtorno = custbranch.debtorno
				AND dcs.branchcode = custbranch.branchcode
				INNER JOIN paymentterms
				ON debtorsmaster.paymentterms=paymentterms.termsindicator
				INNER JOIN locations
				ON locations.loccode=dcs.fromstkloc
				INNER JOIN currencies
				ON debtorsmaster.currcode=currencies.currabrev
				WHERE dcs.orderno = '" . $orderno . "'";

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
	$SQL = "SELECT  dcdetails.internalitemno,
					dcdetails.dcdetailsindex,
					dcdetails.orderlineno,
					dcdetails.lineoptionno,
					dcdetails.optionitemno,
					dcdetails.stkcode,
					stockmaster.description,
					stockmaster.longdescription,
					stockmaster.materialcost,
					stockmaster.volume,
					stockmaster.grossweight,
					stockmaster.units,
					stockmaster.serialised,
					stockmaster.nextserialno,
					stockmaster.eoq,
					dcdetails.unitprice,
					dcdetails.quantity,
					dcdetails.discountpercent,
					dcdetails.actualdispatchdate,
					dcdetails.qtyinvoiced,
					dcdetails.narrative,
					dcdetails.itemdue,
					dcdetails.poline,
					stockissuance.issued AS qohatloc,
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
					dcdetails.completed
				FROM dcdetails 
				INNER JOIN stockmaster
				ON dcdetails.stkcode = stockmaster.stockid
				INNER JOIN stockissuance
				ON stockmaster.stockid=stockissuance.stockid
				INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
				INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
				WHERE  locstock.loccode = '" . $details['fromstkloc'] . "'
				AND stockissuance.salesperson = '".$details['salesmann']."'
				AND dcdetails.orderno ='" . $orderno . "'
				ORDER BY dcdetails.orderlineno";

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
	$SQL = "SELECT * FROM dcoptions 
			WHERE  dcoptions.orderno ='" . $orderno . "'";

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
	$SQL = "SELECT * FROM dclines 
			WHERE  dclines.orderno ='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);

	$lines = [];

	while($row = mysqli_fetch_assoc($result)){

		$lineindex = $row['lineno'];
		
		$lines[$lineindex] = $row;

		$lines[$lineindex]['options'] = (isset($options[$lineindex])) ? $options[$lineindex] : [];

	}

	$SQL = "SELECT * FROM parchi_dc WHERE dcno='".$orderno."'";
	$res = mysqli_query($db, $SQL);

	$slips = [];

	while($row = mysqli_fetch_assoc($res)){

		$slips[] = $row['parchino'];

	}
    $SQL = "SELECT SUM(
						CASE WHEN GSTwithhold = 0 AND WHT = 0 
							THEN ovamount - alloc
						WHEN GSTwithhold = 0 AND WHT = 1 
							THEN ovamount - alloc - WHTamt
						WHEN GSTwithhold = 1 AND WHT = 0 
							THEN ovamount - alloc - GSTamt
						WHEN GSTwithhold = 1 AND WHT = 1 
							THEN ovamount - alloc - GSTamt - WHTamt
						END
					) AS credit  
                FROM debtortrans 
                WHERE reversed = 0 
                AND settled = 0
                AND type = 10
                AND debtorno = '".$details['debtorno']."'";
    $res = mysqli_query($db, $SQL);
    $credit = mysqli_fetch_assoc($res)['credit'] ?:0;
    $SQL='SELECT debtorsmaster.debtorno FROM debtorsmaster WHERE debtorsmaster.debtorno IN 
                            (SELECT can_access FROM statement_access WHERE user = "'.$_SESSION['UserID'].'"'.') 
                            AND debtorsmaster.debtorno="'.$details['debtorno'].'"';
    $result=mysqli_query($db,$SQL);
    if (mysqli_num_rows($result)>=1) {
        $response['flag'] = "on";
    }


    closeDBConnection($db);

	$details['lines'] = $lines;
	$details['slips'] = $slips;

	$response['status'] = "success";
	$response['formid'] = $_SESSION['FormID'];
    $response['credit'] = $credit;
	$response['data']	= $details;

	utf8_encode_deep($response);
	echo json_encode($response);
	return;


?>