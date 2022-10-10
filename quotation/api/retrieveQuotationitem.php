<?php 
$var= "MAddy";
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

	if(!isset($_POST['lineno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];


		echo json_encode($response);
		return;	

	}
    
	$lineno = $_POST['lineno'];
    $db = createDBConnection();
	mysqli_set_charset($db,"utf8");

	$SQL = "SELECT  salesorderdetailsip.salesorderdetailsindex,
					salesorderdetailsip.orderlineno,
					salesorderdetailsip.lineoptionno,
					salesorderdetailsip.stkcode,
					stockmaster.description,
					salesorderdetailsip.unitprice,
					salesorderdetailsip.unitrate,
					salesorderdetailsip.quantity,
					salesorderdetailsip.discountpercent,
					stockmaster.lastcostupdate,
					stockmaster.mnfcode,
					stockmaster.mnfpno,
					manufacturers.manufacturers_name as manu_name,
					stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost
				FROM salesorderdetailsip INNER JOIN stockmaster
				ON salesorderdetailsip.stkcode = stockmaster.stockid
				INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
				WHERE salesorderdetailsip.orderlineno ='" . $lineno . "'
				ORDER BY salesorderdetailsip.orderlineno";


                $items = [];
	$result = mysqli_query($db, $SQL);
	while($row = mysqli_fetch_array($result)){
		$items['salesorderdetailsindex'] = $row['salesorderdetailsindex'];
        $items['orderlineno'] = $row['orderlineno'];
		$items['lineoptionno'] = $row['lineoptionno'];
        $items['stkcode'] = $row['stkcode'];
		$items['description'] = $row['description'];
        $items['unitprice'] = $row['unitprice'];
		$items['unitrate'] = $row['unitrate'];
        $items['quantity'] = $row['quantity'];
		$items['discountpercent'] = $row['discountpercent'];
        $items['lastcostupdate'] = $row['lastcostupdate'];
		$items['mnfcode'] = $row['mnfcode'];
        $items['mnfpno'] = $row['mnfpno'];
        $items['manu_name'] = $row['manu_name'];
		$items['standardcost'] = $row['standardcost'];

		$details[] = $items;
    }

	// // $response['status'] = "success";
    // // $response['formid'] = $_SESSION['FormID'];
	// // $response['data']	= $details;
    // // $response['credit'] = $credit;




    // // utf8_encode_deep($response);
    echo json_encode($details);

	return;

?>