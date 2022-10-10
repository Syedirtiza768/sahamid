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

	if(!isset($_POST['line']) || !isset($_POST['orderno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$lineIndex = $_POST['line'];
	$orderno   = $_POST['orderno'];

	$db = createDBConnection();

	$SQL = "SELECT * FROM `dclines` 
			WHERE lineindex='".$lineIndex."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Line Fetch failed.'
		];

		echo json_encode($response);
		return;	

	}

	$lineDetails = mysqli_fetch_assoc($result);

	$SQL = "SELECT * FROM dcdetails WHERE orderno='".$orderno."' AND orderlineno='".$lineDetails['lineno']."'";
	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) > 0){

		$response = [
			'status' => 'error',
			'message' => 'Line contains items so cannot be deleted.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "DELETE FROM `dclines` 
			WHERE lineindex='".$lineIndex."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Line Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "SELECT * FROM `dcoptions` 
			WHERE lineno='".$lineDetails['lineno']."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Option Fetch Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$optionDetails = mysqli_fetch_assoc($result);

	$SQL = "DELETE FROM `dcoptions` 
			WHERE lineno='".$lineDetails['lineno']."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Option Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "SELECT * FROM dcs WHERE orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'DC fetch failed.'
		];

		echo json_encode($response);
		return;	

	}

	$dcDetails = mysqli_fetch_assoc($result);

	$SQL = "SELECT salesman FROM salescase WHERE salescaseref='".$dcDetails['salescaseref']."'";
	$result = mysqli_query($db, $SQL);
	$salesman = mysqli_fetch_assoc($result)['salesman'];

	$SQL = "SELECT * FROM `dcdetails` 
			WHERE orderlineno='".$lineDetails['lineno']."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	$optionQuantity = $optionDetails['quantity'];

	$itemss = [];

	while($row = mysqli_fetch_assoc($result)){

		$stockid 	 = $row['stkcode'];
		$stkQuantity = $row['quantity'];

		$SQL   = "SELECT issued,dc FROM stockissuance WHERE stockid='".$stockid."'AND salesperson='".$salesman."'";
		$res   = mysqli_query($db, $SQL);
		$quant = mysqli_fetch_assoc($res);

		$issuedQuantity = $quant['issued'];
		$dcQuantity 	= $quant['dc'];

		$blablaquantity = ($optionQuantity * $stkQuantity);

		$newIssued = $issuedQuantity + $blablaquantity;
		$newDCVal  = $dcQuantity - $blablaquantity;

		$SQL = "UPDATE `stockissuance` SET issued='".$newIssued."',dc='".$newDCVal."' 
				WHERE salesperson='".$salesman."'
				AND stockid='".$stockid."'";

		mysqli_query($db, $SQL);

		$itemss[] = [

			'stkcode' => $stockid,
			'qoh' => $newIssued,

		];

	}

	$SQL = "DELETE FROM `dcdetails` 
			WHERE orderlineno='".$lineDetails['lineno']."'
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Option Items Delete Failed.'
		];

		echo json_encode($response);
		return;	

	}

	reorderlines($orderno, $db);

	$response = [
			'status' => 'success',
			'data' => [
				'line' => $lineIndex,
				'items' => isset($itemss) ? $itemss : "nan"
			]
		];

	echo json_encode($response);
	return;	

?>