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
	|| !isset($_POST['name']) || !isset($_POST['value'])
	|| !isset($_POST['option'])
) {

	$response = [
		'status' => 'error',
		'message' => 'Missing Parameters.'
	];

	echo json_encode($response);
	return;
}

$salescaseref = $_POST['salescaseref'];
$orderno = $_POST['orderno'];
$grbno  = $_POST['grbno'];
$option = $_POST['option'];
$name = $_POST['name'];
$value = $_POST['value'];

$SQL = "SELECT fromstkloc,deliverto FROM dcs WHERE orderno='" . $orderno . "'";
$result = mysqli_query($db, $SQL);
$row = mysqli_fetch_assoc($result);
$fromstkloc = $row['fromstkloc'];
$deliverto = $row['deliverto'];

$value = str_replace("'", "\'", $value);

if (!($name == "stockstatus" || $name == "quantity")) {

	$response = [
		'status' => 'error',
		'message' => 'Invalid Parameters.',
		'name'	=> $name
	];

	echo json_encode($response);
	return;
}

$db = createDBConnection();

if ($name == "quantity") {

	if ($value < 1) {

		$response = [
			'status' => 'alert',
			'message' => 'Quantity cannot be 0 or less.'
		];

		echo json_encode($response);
		return;
	}

	$SQL = "SELECT salesman FROM salescase WHERE salescaseref='" . $salescaseref . "'";
	$result = mysqli_query($db, $SQL);
	$salesman = mysqli_fetch_assoc($result)['salesman'];

	$SQL = "SELECT * FROM dcoptions WHERE optionindex='" . $option . "'";
	$res = mysqli_query($db, $SQL);
	$row = mysqli_fetch_assoc($res);

	$optionQuantity = $row['quantity'];

	$SQL = "SELECT * FROM dcdetails 
				WHERE orderno='" . $orderno . "'
				AND orderlineno='" . $row['lineno'] . "'
				AND lineoptionno='" . $row['optionno'] . "'";
	$res = mysqli_query($db, $SQL);

	while ($row = mysqli_fetch_assoc($res)) {

		$SQL = "SELECT stkcode,quantity FROM dcdetails WHERE dcdetailsindex='" . $row['dcdetailsindex'] . "'";
		$result = mysqli_query($db, $SQL);
		$stock = mysqli_fetch_assoc($result);
		$stockid = $stock['stkcode'];
		$stkQuantity = $stock['quantity'];

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

		$quantityDifference = ($row['quantity'] * $value) - ($row['quantity'] * $optionQuantity);

		if ($issuedQuantityref < $quantityDifference && ($row['quantity'] * $value > $issuedQuantityref || $value < $stkQuantity)) {

			$response = [
				'status'  => 'alert',
				'message' => 'cannot give more quantity then assigned.',
				'minimum' => $stkQuantity,
			];

			echo json_encode($response);
			return;
		}

		$new = $row['quantity'] * $value;
		$old = $row['quantity'] * $optionQuantity;

		if ($new > $old) {

			$difference = $new;
			$difference = $difference - $old;
			$SQL = "UPDATE ogpsalescaseref SET quantity = quantity - $difference WHERE salescaseref = '" . $salescaseref . "'
				AND stockid='" . $stockid . "'
				AND salesman='" . $salesman . "'";
			$result = mysqli_query($db, $SQL);
		}
		if ($new < $old) {
			$difference = $old - $new;
			$SQL = "UPDATE ogpsalescaseref SET quantity = quantity + $difference WHERE salescaseref = '" . $salescaseref . "'
				AND stockid='" . $stockid . "'
				AND salesman='" . $salesman . "'";

			$result = mysqli_query($db, $SQL);
		}
	}

	$SQL = "SELECT * FROM dcoptions WHERE optionindex='" . $option . "'";
	$res = mysqli_query($db, $SQL);
	$row = mysqli_fetch_assoc($res);
	$givenLineNo = $row['lineno'];
	$givenOptionNo = $row['optionno'];

	$SQL = "SELECT * FROM dcdetails 
				WHERE orderno='" . $orderno . "'
				AND orderlineno='" . $row['lineno'] . "'
				AND lineoptionno='" . $row['optionno'] . "'";
	$res = mysqli_query($db, $SQL);

	$itemss = [];

	while ($row = mysqli_fetch_assoc($res)) {

		$SQL = "SELECT stkcode,quantity FROM dcdetails WHERE dcdetailsindex='" . $row['dcdetailsindex'] . "'";
		$result = mysqli_query($db, $SQL);
		$stock = mysqli_fetch_assoc($result);
		$stockid = $stock['stkcode'];
		$stkQuantity = $stock['quantity'];

		$SQL = "SELECT issued,dc FROM stockissuance WHERE stockid='" . $stockid . "'AND salesperson='" . $salesman . "'";
		$result = mysqli_query($db, $SQL);
		$quant = mysqli_fetch_assoc($result);

		$issuedQuantity = $quant['issued'];
		$dcQuantity = $quant['dc'];

		$quantityDifference = ($row['quantity'] * $value) - ($row['quantity'] * $optionQuantity);

		$newIssued = $issuedQuantity - $quantityDifference;
		$newDCVal  = $dcQuantity + $quantityDifference;
		//GRB
		if ($newIssued > $issuedQuantity && isset($_POST['grbno'])) {

			$SQL = "INSERT INTO `grbdetails`(`orderno`,`stkcode`,`quantity`)
			VALUES ('" . $grbno . "','" . $stockid . "','" . -1 * $quantityDifference . "')";

			$result = mysqli_query($db, $SQL);
		} else if (isset($_POST['grbno']))
			return;

		$SQL = "UPDATE `stockissuance` SET issued='" . $newIssued . "',dc='" . $newDCVal . "' 
				WHERE salesperson='" . $salesman . "'
				AND stockid='" . $stockid . "'";

		mysqli_query($db, $SQL);

		$itemss[] = [

			'stkcode' => $stockid,
			'qoh' => $newIssued,

		];

		$SQL = "SELECT * FROM dcdetails 
					WHERE orderno='" . $orderno . "'
					AND stkcode='" . $stockid . "'";

		$itemsResult = mysqli_query($db, $SQL);

		$totalItemQuantityInDC = 0;

		// echo "Initial: ".$totalItemQuantityInDC." : TOTAL DC<br>";

		while ($itemRow = mysqli_fetch_assoc($itemsResult)) {

			// echo "Line NO: ".$itemRow['orderlineno']." : LINE NO<br>";
			// echo "Option NO: ".$itemRow['lineoptionno']." : Option NO<br>";	

			if ($itemRow['orderlineno'] == $givenLineNo && $itemRow['lineoptionno'] == $givenOptionNo) {

				$totalItemQuantityInDC += ($itemRow['quantity'] * $value);

				// echo "Option: ".$value." : Option Quantity<br>";
				// echo "Stock Quantity: ".$itemRow['quantity']." : Item Quantity<br>";
				// echo "Initial: ".$itemRow['quantity']*$value." : Option TOTAL<br>";
				// echo "Initial: ".$totalItemQuantityInDC." : TOTAL DC<br>";

			} else {

				$SQL = "SELECT quantity FROM dcoptions 
							WHERE orderno='" . $orderno . "'
							AND lineno='" . $itemRow['orderlineno'] . "'
							AND optionno='" . $itemRow['lineoptionno'] . "'";

				$dcOptionQuantityResult = mysqli_query($db, $SQL);

				$optionQuantityDC = mysqli_fetch_assoc($dcOptionQuantityResult)['quantity'];

				$totalItemQuantityInDC += ($itemRow['quantity'] * $optionQuantityDC);

				// echo "Option: ".$optionQuantityDC." : Option Quantity<br>";
				// echo "Stock Quantity: ".$itemRow['quantity']." : Item Quantity<br>";
				// echo "Initial: ".($itemRow['quantity'] * $optionQuantityDC)." : Option TOTAL<br>";
				// echo "Iteration: ".$totalItemQuantityInDC." : OTHER TOTAL DC<br>";

			}
		}

		$SQL = "SELECT stkmoveno as max,transno,stockid FROM stockmoves 
					WHERE stockid='" . $stockid . "' ORDER BY stkmoveno DESC";

		$result = mysqli_query($db, $SQL);
		$smrow = mysqli_fetch_assoc($result);
		$transNo = $smrow['transno'];
		$movementID = $smrow['max'];

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

			while ($abrow = mysqli_fetch_assoc($result)) {

				if ($topTransNo == "" && $period == "") {
					$topTransNo = $abrow['transno'];
					$period = $abrow['prd'];
					$topQuantityStockMoves = $abrow['qty'];
					$stkmoveno = $abrow['stkmoveno'];
				}

				$totalQuantityInMovement += $abrow['qty'];
			}

			$quantityDifferenceSM = $totalItemQuantityInDC - $totalQuantityInMovement;

			if (($transNo == $topTransNo && $period == $PeriodNo) || isset($_POST['grbno'])) {
				$quantityToSave = $topQuantityStockMoves + $quantityDifferenceSM;
			} else {
				$quantityToSave = $quantityDifferenceSM;
			}
		}

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
	}
}

$SQL = "UPDATE `dcoptions` SET `" . $name . "`='" . $value . "' 
			WHERE orderno='" . $orderno . "'
			AND optionindex='" . $option . "'";

$result = mysqli_query($db, $SQL);

if (!$result) {

	$response = [
		'status' => 'error',
		'message' => 'Update Failed.'
	];

	echo json_encode($response);
	return;
}

$response['status'] = "success";
$response['data'] = [

	'name'	=>	$name,
	'value'	=>	$value,
	'items' => isset($itemss) ? $itemss : "nan"

];

echo json_encode($response);
return;
