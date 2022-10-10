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
	mysqli_set_charset($db,"utf8");

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
				   (CASE salesordersip.rate_clause
					WHEN ''
					THEN 'usd'
					ELSE salesordersip.rate_clause
					END) as rate_clause,
				  
				   DATE_ADD( salesordersip.orddate, INTERVAL 15 DAY) as rate_validity,
				 
				   debtorsmaster.currcode,
				   debtorsmaster.creditlimit,
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
/*  $SQL="UPDATE salesorderdetailsip INNER JOIN stockmaster ON salesorderdetailsip.stkcode=stockmaster.stockid
            SET salesorderdetailsip.discountpercent=1-salesorderdetailsip.unitrate/stockmaster.materialcost
            WHERE salesorderdetailsip.orderno = '$orderno'";
mysqli_query($db, $SQL);*/
/* $SQL="UPDATE salesorderdetailsip INNER JOIN stockmaster ON salesorderdetailsip.stkcode=stockmaster.stockid
                SET unitrate=stockmaster.materialcost*(1-discountpercent),
                salesorderdetailsip.discountpercent=1-salesorderdetailsip.unitrate/stockmaster.materialcost
                WHERE salesorderdetailsip.orderno = '$orderno'
                AND unitrate >0";
mysqli_query($db, $SQL);*/
/*$SQL="UPDATE salesorderdetails INNER JOIN stockmaster ON salesorderdetails.stkcode=stockmaster.stockid
                SET salesorderdetails.discountpercent=1-salesorderdetails.unitrate/stockmaster.materialcost
                WHERE salesorderdetails.orderno = '$orderno'
                AND unitrate >0";
mysqli_query($db, $SQL);*/

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

	$response['status'] = "success";
    $response['formid'] = $_SESSION['FormID'];
	$response['data']	= $details;
    $response['credit'] = $credit;




    utf8_encode_deep($response);
    echo json_encode($response);

	return;

?>