<?php

$PathPrefix = '../../';

include('../misc.php');
include('../../includes/session.inc');
include('../../includes/SQL_CommonFunctions.inc');

$response = [];

if (!validHeaders()) {

	$response = [
		'status' => 'error',
		'message' => 'Refresh or ReLogin Required.'
	];

	echo json_encode($response);
	return;
}

if (
	!isset($_POST['salescaseref']) || !isset($_POST['orderno'])
	|| !isset($_POST['item']) || !isset($_POST['name'])
	|| !isset($_POST['value'])
) {

	$response = [
		'status' => 'error',
		'message' => 'Missing Parameters.'
	];

	echo json_encode($response);
	return;
}

$salescaseref 	= $_POST['salescaseref'];
$orderno 		= $_POST['orderno'];
$grbno 		    = $_POST['grbno'];
$item 			= explode("item", $_POST['item'])[1];
$name 			= $_POST['name'];
$value 			= $_POST['value'];
$minimum		= 1;

$db = createDBConnection();

if ($name == "quantity") {

	$SQL = "SELECT salesman FROM salescase 
				WHERE salescaseref='" . $salescaseref . "'";

	$result = mysqli_query($db, $SQL);

	$salesman = mysqli_fetch_assoc($result)['salesman'];

	$SQL = "SELECT stkcode,quantity,orderlineno,lineoptionno FROM dcdetails 
				WHERE dcdetailsindex='" . $item . "'";

	$result = mysqli_query($db, $SQL);

	$stock = mysqli_fetch_assoc($result);

	$stockid = $stock['stkcode'];
	$stkQuantity = $stock['quantity'];
	$orderlineno = $stock['orderlineno'];
	$lineoptionno = $stock['lineoptionno'];

	$SQL = "SELECT * FROM dcoptions WHERE orderno='" . $orderno . "' 
				AND lineno='" . $orderlineno . "' 
				AND optionno='" . $lineoptionno . "'";

	$res = mysqli_query($db, $SQL);
	$row = mysqli_fetch_assoc($res);

	$optionQuantity = $row['quantity'];

	$SQL = "SELECT quantity FROM ogpsalescaseref 
				WHERE stockid='" . $stockid . "'
				AND salesman='" . $salesman . "'
				AND salescaseref = '" . $salescaseref . "'";

	$result = mysqli_query($db, $SQL);

	$quant = mysqli_fetch_assoc($result);

	$issuedQuantityref = $quant['quantity'];

	$SQL = "SELECT issued,dc FROM stockissuance WHERE stockid='" . $stockid . "'AND salesperson='" . $salesman . "'";
	$result = mysqli_query($db, $SQL);
	$quant = mysqli_fetch_assoc($result);

	$issuedQuantity = $quant['issued'];
	$dcQuantity = $quant['dc'];

	if ($value <= 0) {

		$response = [
			'status' => 'alert',
			'message' => 'Quantity cannot be 0 or less.',
			'minimum' => $stkQuantity,
		];

		echo json_encode($response);
		return;
	}

	$minimum = 0;

	$quantityDifference = ($optionQuantity * $value) - ($stkQuantity * $optionQuantity);

	if (($optionQuantity * $value) == 0) {

		$quantityDifference = 0;
	}

	if ($issuedQuantityref < 1 && $quantityDifference > 0) {

		$response = [
			'status' => 'alert',
			'message' => 'Issued Quantity is 0 or less.',
			'minimum' => $stkQuantity,
		];

		echo json_encode($response);
		return;
	}

	if ($issuedQuantityref < $quantityDifference  && ($optionQuantity * $value > $issuedQuantityref || $value < $stkQuantity)) {

		$response = [
			'status'  => 'alert',
			'message' => 'cannot give more quantity then assigned.',
			'minimum' => $stkQuantity,
		];

		echo json_encode($response);
		return;
	}

	if ($value > $stkQuantity) {
		$difference = $optionQuantity * $value;
		$difference = $difference - $stkQuantity;
		$SQL = "UPDATE ogpsalescaseref SET quantity = quantity - $difference WHERE salescaseref = '" . $salescaseref . "'
			AND stockid='" . $stockid . "'
			AND salesman='" . $salesman . "'";

		$result = mysqli_query($db, $SQL);
	}
	if ($value < $stkQuantity) {
		$difference = $stkQuantity - $value;
		$SQL = "UPDATE ogpsalescaseref SET quantity = quantity + $difference WHERE salescaseref = '" . $salescaseref . "'
			AND stockid='" . $stockid . "'
			AND salesman='" . $salesman . "'";

		$result = mysqli_query($db, $SQL);
	}

	$newIssued = $issuedQuantity - $quantityDifference;
	$salescaseIssued = $issuedQuantityref - $quantityDifference;
	$newDCVal  = $dcQuantity + $quantityDifference;
	//GRB
	if ($newIssued >= $salescaseIssued && isset($_POST['grbno'])) {

		$SQL = "INSERT INTO `grbdetails`(`orderno`,`stkcode`,`quantity`)
			VALUES ('" . $grbno . "','" . $stockid . "','" . -1 * $quantityDifference . "')";

		$SQL = "UPDATE `stockissuance` SET issued='" . $newIssued . "',dc='" . $newDCVal . "'
				WHERE salesperson='" . $salesman . "'
				AND stockid='" . $stockid . "'";
		$result = mysqli_query($db, $SQL);
	} else if (isset($_POST['grbno'])) {
		return;
	}

	$SQL = "UPDATE `stockissuance` SET issued='" . $newIssued . "',dc='" . $newDCVal . "' 
				WHERE salesperson='" . $salesman . "'
				AND stockid='" . $stockid . "'";
		mysqli_query($db, $SQL);

	//Calculate Total Item Quantity In DC
	$totalItemQuantityInDC = 0;

	// echo "Initial: ".$totalItemQuantityInDC." : TOTAL DC<br>";

	$SQL = "SELECT * FROM dcdetails WHERE stkcode='" . $stockid . "' AND orderno='" . $orderno . "'";

	$result = mysqli_query($db, $SQL);
	while ($row = mysqli_fetch_assoc($result)) {

		// echo "Line NO: ".$row['orderlineno']." : LINE NO<br>";
		// echo "Option NO: ".$row['lineoptionno']." : Option NO<br>";

		if ($row['orderlineno'] == $orderlineno && $row['lineoptionno'] == $lineoptionno) {

			$SQLOptionQuantity = "SELECT * FROM dcoptions 
									WHERE orderno='" . $orderno . "' 
									AND lineno='" . $row['orderlineno'] . "'
									AND optionno='" . $row['lineoptionno'] . "'";
			$optionQuantityResult = mysqli_query($db, $SQLOptionQuantity);
			$optionQuantityRow	  = mysqli_fetch_assoc($optionQuantityResult);
			$optionQuantity		  =	$optionQuantityRow['quantity'];

			$totalItemQuantityInDC += $optionQuantity * $value;

			// echo "Option: ".$optionQuantity." : Option Quantity<br>";
			// echo "Value: ".$value." : Item Quantity<br>";
			// echo "Initial: ".$optionQuantity*$value." : Option TOTAL<br>";
			// echo "Initial: ".$totalItemQuantityInDC." : TOTAL DC<br>";

		} else {



			$SQLOptionQuantity = "SELECT * FROM dcoptions 
									WHERE orderno='" . $orderno . "' 
									AND lineno='" . $row['orderlineno'] . "'
									AND optionno='" . $row['lineoptionno'] . "'";
			$optionQuantityResult = mysqli_query($db, $SQLOptionQuantity);
			$optionQuantityRow	  = mysqli_fetch_assoc($optionQuantityResult);
			$optionQuantity		  =	$optionQuantityRow['quantity'];

			$itemQuantityForOption = $optionQuantity * $row['quantity'];

			$totalItemQuantityInDC += $itemQuantityForOption;

			// echo "Option: ".$optionQuantity." : Option Quantity<br>";
			// echo "Stock Quantity: ".$row['quantity']." : Item Quantity<br>";
			// echo "Initial: ".$itemQuantityForOption." : Option TOTAL<br>";
			// echo "Iteration: ".$totalItemQuantityInDC." : OTHER TOTAL DC<br>";

		}
	}

	//Fetch Item Movements latest DC #
	$SQL = "SELECT stkmoveno as max,transno,stockid FROM stockmoves 
				WHERE stockid='" . $stockid . "' ORDER BY stkmoveno DESC";

	$result = mysqli_query($db, $SQL);
	$row = mysqli_fetch_assoc($result);
	$transNo = $row['transno'];
	$movementID = $row['max'];

	$SQL = "SELECT * FROM stockmoves 
				WHERE transno='" . $orderno . "' AND stockid='" . $stockid . "' ORDER BY stkmoveno DESC";

	$result = mysqli_query($db, $SQL);
	$quantityStockMovementDC = mysqli_num_rows($result);

	$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

	$topTransNo = "";
	$period = "";
	$stkmoveno = 0;

	if ($quantityStockMovementDC == 0)
		$quantityToSave = $totalItemQuantityInDC;
	else {

		$topQuantityStockMoves = 0;
		$totalQuantityInMovement = 0;

		while ($row = mysqli_fetch_assoc($result)) {

			if ($topTransNo == "" && $period == "") {
				$topTransNo = $row['transno'];
				$period = $row['prd'];
				$topQuantityStockMoves = $row['qty'];
				$stkmoveno = $row['stkmoveno'];
			}

			$totalQuantityInMovement += $row['qty'];
		}

		$quantityDifferenceSM = $totalItemQuantityInDC - $totalQuantityInMovement;

		if (($transNo == $topTransNo && $period == $PeriodNo) || isset($_POST['grbno'])) {

			$quantityToSave = $topQuantityStockMoves + $quantityDifferenceSM;
		} else {
			$quantityToSave = $quantityDifferenceSM;
		}
	}

	$SQL = "SELECT fromstkloc,deliverto FROM dcs WHERE orderno='" . $orderno . "'";
	$result = mysqli_query($db, $SQL);
	$row = mysqli_fetch_assoc($result);
	$fromstkloc = $row['fromstkloc'];
	$deliverto = $row['deliverto'];

	$SQL = "SELECT locstock.quantity FROM locstock
				WHERE locstock.stockid='" . $stockid . "'
				AND loccode= '" . $fromstkloc . "'";

	$ResultQ = DB_query($SQL, $db);
	if (DB_num_rows($ResultQ) == 1) {
		$LocQtyRow = DB_fetch_row($ResultQ);
		$QtyOnHandPrior = $LocQtyRow[0];
	} else {
		$QtyOnHandPrior = 0;
	}

	if ($movementID == null || $quantityStockMovementDC == 0 || $period != $PeriodNo || $topTransNo != $transNo || isset($_POST['grbno'])) {
		if (isset($_POST['grbno'])) {
			$SQL = "INSERT INTO stockmoves(stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
					VALUES ('" . $stockid . "','514','" . $grbno . "','" . $fromstkloc . "','" . Date('Y-m-d') . "',
						'" . _('GRB') . ' ' . DB_escape_string($grbno . ' Against DC#' . $orderno) . "','" . $quantityToSave . "',
						'" . $PeriodNo . "','" . $QtyOnHandPrior . "')";

			mysqli_query($db, $SQL);
		} else {
			$SQL = "INSERT INTO stockmoves(stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
			VALUES ('" . $stockid . "','512','" . $orderno . "','" . $fromstkloc . "','" . Date('Y-m-d') . "',
							'" . _('Delivered To') . ' ' . DB_escape_string($deliverto) . "','" . $quantityToSave . "',
					'" . $PeriodNo . "','" . $QtyOnHandPrior . "')";

			mysqli_query($db, $SQL);
		}
	} else {

		$SQL = "UPDATE stockmoves SET qty='" . $quantityToSave . "' 
					WHERE stockid='" . $stockid . "' 
					AND transno='" . $topTransNo . "'
					AND stkmoveno='" . $stkmoveno . "'";

		mysqli_query($db, $SQL);
	}

	$SQL = "UPDATE `dcdetails` 
				SET `quantity`='" . $value . "'
				WHERE `dcdetailsindex`=" . $item . "";
} else {

	if ($name == "uprice") {

		if (!isset($_POST['discount'])) {

			$response = [
				'status' => 'error',
				'message' => 'Invalid Parameters.'
			];

			echo json_encode($response);
			return;
		}

		$uprice = $value;
		$discount = $_POST['discount'] / 100;
	} else {

		$response = [
			'status' => 'error',
			'message' => 'Invalid Parameters.'
		];

		echo json_encode($response);
		return;
	}

	$SQL = "UPDATE `dcdetails` 
				SET `discountpercent`='" . $discount . "'
				WHERE `dcdetailsindex`=" . $item . "";
}

$result = mysqli_query($db, $SQL);

closeDBConnection($db);

if (!$result) {

	$response = [
		'status' => 'error',
		'message' => 'Save Failed.'
	];

	echo json_encode($response);
	return;
}


$response = [

	'status' => 'success',
	'data' => [
		'salescaseref'	=> $salescaseref,
		'orderno'		=> $orderno,
		'name' 			=> $name,
		'value' 		=> $value,
		'discount' 		=> isset($discount) ? $discount : "nan",
		'minimum'		=> $minimum,
		'qoh'			=> isset($newIssued) ? $newIssued : "nan",
		'salesqoh'			=> isset($salescaseIssued) ? $salescaseIssued : "nan",
		'stkcode'		=> isset($stockid) ? $stockid : "nan",
	]

];

echo json_encode($response);
return;
