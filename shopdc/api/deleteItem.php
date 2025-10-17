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

if (!isset($_POST['itemIndex'])) {

	$response = [
		'status' => 'error',
		'message' => 'Missing Parameters.'
	];

	echo json_encode($response);
	return;
}

$index = $_POST['itemIndex'];
$grbno = $_POST['grbno'];

$db = createDBConnection();

$SQL = "SELECT * FROM `dcdetails` WHERE dcdetailsindex=" . $index . "";

$result = mysqli_query($db, $SQL);

$item = mysqli_fetch_assoc($result);

$SQL = "SELECT * FROM dcs WHERE orderno='" . $item['orderno'] . "'";

$result = mysqli_query($db, $SQL);

$dc = mysqli_fetch_assoc($result);

$SQL = "SELECT salesman FROM salescase WHERE salescaseref='" . $dc['salescaseref'] . "'";
$result = mysqli_query($db, $SQL);
$salesman = mysqli_fetch_assoc($result)['salesman'];

$SQL = "SELECT * FROM dcoptions WHERE orderno='" . $item['orderno'] . "' 
			AND lineno='" . $item['orderlineno'] . "' 
			AND optionno='" . $item['lineoptionno'] . "'";
$res = mysqli_query($db, $SQL);
$row = mysqli_fetch_assoc($res);

$optionQuantity = $row['quantity'];

$blablaQuantity = ($optionQuantity * $item['quantity']);

$SQL = "SELECT issued,dc FROM stockissuance WHERE stockid='" . $item['stkcode'] . "'
			AND salesperson='" . $salesman . "'";

$result = mysqli_query($db, $SQL);
$quant = mysqli_fetch_assoc($result);

$issuedQuantity = $quant['issued'];
$dcQuantity = $quant['dc'];

$newIssued = $issuedQuantity + $blablaQuantity;
$newDCVal  = $dcQuantity - $blablaQuantity;
$stockid = $item['stkcode'];
if (isset($_POST['grbno'])) {

	$SQL = "INSERT INTO `grbdetails`(`orderno`,`stkcode`,`quantity`)
                    VALUES ('" . $grbno . "','" . $stockid . "','" . $blablaQuantity . "')";

	$result = mysqli_query($db, $SQL);
}
$SQL = "UPDATE `stockissuance` SET issued='" . $newIssued . "',dc='" . $newDCVal . "' 
			WHERE salesperson='" . $salesman . "'
			AND stockid='" . $item['stkcode'] . "'";

mysqli_query($db, $SQL);


// 	$SQL = "UPDATE `ogpsalescaseref` SET quantity= quantity + ".$blablaQuantity."
// 	WHERE salesman='".$salesman."'
// 	AND stockid='".$item['stkcode']."'
// 	AND salescaseref = '".$dc['salescaseref']."'";

// mysqli_query($db, $SQL);

// Define your key values
$salescaseref = $dc['salescaseref'];
$stockid = $item['stkcode'];

// Step 1: Count matching rows
$checkSQL = "
    SELECT COUNT(*) AS count, SUM(quantity) AS totalQty 
    FROM ogpsalescaseref 
    WHERE salescaseref = '$salescaseref' 
      AND stockid = '$stockid' 
      AND salesman = '$salesman'
";
$checkResult = mysqli_query($db, $checkSQL);
$row = mysqli_fetch_assoc($checkResult);
$count = $row['count'];
$totalQty = $row['totalQty'] ?? 0;

if ($count > 1) {
    // Step 2: Reset all existing quantities to 0
    $resetSQL = "
        UPDATE ogpsalescaseref 
        SET quantity = 0 
        WHERE salescaseref = '$salescaseref' 
          AND stockid = '$stockid' 
          AND salesman = '$salesman'
    ";
    mysqli_query($db, $resetSQL);

    // Step 3: Calculate total quantity to insert
    $newTotal = $totalQty + $blablaQuantity;

    // Step 4: Insert new record with combined quantity
    $insertSQL = "
        INSERT INTO ogpsalescaseref (salescaseref, salesman, stockid, quantity)
        VALUES ('$salescaseref', '$salesman', '$stockid', '$newTotal')
    ";
    mysqli_query($db, $insertSQL);

} else {
    // Step 5: If only one record exists, update that one
    $updateSQL = "
        UPDATE ogpsalescaseref 
        SET quantity = quantity + $blablaQuantity 
        WHERE salescaseref = '$salescaseref' 
          AND stockid = '$stockid' 
          AND salesman = '$salesman'
    ";
    mysqli_query($db, $updateSQL);
}




$stockid = $item['stkcode'];
$orderno = $item['orderno'];
$fromstkloc = $dc['fromstkloc'];
$deliverto = $dc['deliverto'];
$totalItemQuantityInDCOption = $optionQuantity * $item['quantity'];

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
$row = mysqli_fetch_assoc($result);

$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

if ($quantityStockMovementDC > 0) {

	$topTransNo = $row['transno'];
	$period = $row['prd'];
	$topQuantityStockMoves = $row['qty'];
	$stkmoveno = $row['stkmoveno'];

	if (($transNo == $topTransNo && $period == $PeriodNo) || isset($_POST['grbno'])) {

		$quantityToSave = $topQuantityStockMoves - $totalItemQuantityInDCOption;
	} else {

		$quantityToSave = (-1) * $totalItemQuantityInDCOption;
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

	if ($period != $PeriodNo || $topTransNo != $transNo || isset($_POST['grbno'])) {
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

$SQL = "DELETE FROM `dcdetails` WHERE dcdetailsindex=" . $index . "";

$result = mysqli_query($db, $SQL);

if (!$result) {

	$response = [
		'status' => 'error',
		'message' => 'Delete Failed.'
	];

	echo json_encode($response);
	return;
}

$response = [
	'status' => 'success',
	'data' => [
		'index' => $index,
		'stkcode' => $item['stkcode'],
		'qoh' => $newIssued,
	]
];

echo json_encode($response);
return;
