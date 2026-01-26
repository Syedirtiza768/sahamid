<?php

$PathPrefix = "../../../../";
include("../../../../includes/session.inc");
include('../../../../includes/SQL_CommonFunctions.inc');

if (!userHasPermission($db, "inward_slip_igp")) {
	echo json_encode([

		'status' => 'error',
		'message' => 'Permission Denied!'

	]);
	return;
}

if (!isset($_POST['parchino']) || trim($_POST['parchino']) == "") {

	echo json_encode([

		'status' => 'error',
		'message' => 'Missing Parameters'

	]);
	return;
}

$parchi = trim($_POST['parchino']);

$SQL = "SELECT * FROM bazar_parchi WHERE parchino='" . $parchi . "' AND inprogress=1 AND discarded=0 AND settled=0 AND igp_created=0";
$res = mysqli_query($db, $SQL);

if (mysqli_num_rows($res) != 1) {

	echo json_encode([

		'status' => 'error',
		'message' => 'Parchi Not Found or saved or IGP already created saved.',

	]);
	return;
}

$parchiDetails = mysqli_fetch_assoc($res);

if ($parchiDetails['svid'] == '') {

	echo json_encode([

		'status' => 'error',
		'message' => 'Orignal Vendor Not Attached.',

	]);
	return;
}

$SQL = "SELECT * FROM bpitems WHERE deleted_at IS NULL AND stockid='' AND parchino='" . $parchi . "'";
$res = mysqli_query($db, $SQL);

if (mysqli_num_rows($res) > 0) {

	echo json_encode([

		'status' => 'error',
		'message' => 'Items without orignal SKU found.',

	]);
	return;
}

$SQL = "SELECT * FROM bpitems WHERE deleted_at IS NULL AND parchino='" . $parchi . "'";
$res = mysqli_query($db, $SQL);

if (mysqli_num_rows($res) <= 0) {

	echo json_encode([

		'status' => 'error',
		'message' => 'Items Not Found(Should be discarded)',

	]);
	return;
}

$totalValue = 0;
$error = 0;

$count = 1;
$items = [];

while ($row = mysqli_fetch_assoc($res)) {

	$row['index'] = $count;
	$items[] = $row;

	if ($row['quantity_received'] <= 0) {

		echo json_encode([

			'status' => 'error',
			'message' => 'Item with 0 Quantity Found.'

		]);
		$error = 1;
		break;
	}

	$totalValue += ($row['quantity_received'] * $row['price']);
	$count++;
}

if ($parchiDetails['igp_created'] == 1) {

	echo json_encode([

		'status' => 'error',
		'message' => 'IGP Already Created.',

	]);
	return;
}

if ($error == 1) {
	return;
}

DB_Txn_Begin($db);

$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

//Generate IGP
$RequestNo 	= GetNextTransNo(38, $db);
$loc  	 	= $_SESSION['UserStockLocation'];
$date 	 	= date('Y-m-d');
$src 		= "From Vendor: " . $parchiDetails['svid'];
$manager 	= $_SESSION['UsersRealName'];
$narr 	 	= "Against ParchiNo: " . $parchi;

$SQL = "INSERT INTO igp (dispatchid,loccode,despatchdate,receivedfrom,storemanager,narrative)
				VALUES('" . $RequestNo . "','" . $loc . "','" . $date . "','" . $src . "','" . $manager . "','" . $narr . "')";
DB_query($SQL, $db);

foreach ($items as $item) {
	$SQL = "INSERT INTO igpitems (dispatchitemsid,dispatchid,stockid,quantity,comments)
					VALUES('" . $item['index'] . "','" . $RequestNo . "','" . $item['stockid'] . "','" . $item['quantity_received'] . "','')";
	DB_query($SQL, $db);

	$SQL = "SELECT decimalplaces FROM stockmaster WHERE stockid='" . $item['stockid'] . "'";
	$decimal = mysqli_fetch_assoc(mysqli_query($db, $SQL))['decimalplaces'];

	$SQL = "SELECT locstock.quantity FROM locstock
					WHERE locstock.stockid='" . $item['stockid'] . "' 
					AND loccode='" . $loc . "'";
	$ResultQ = DB_query($SQL, $db);
	$QtyOnHandPrior = 0;
	if (DB_num_rows($ResultQ) == 1) {
		$LocQtyRow = DB_fetch_row($ResultQ);
		$QtyOnHandPrior = $LocQtyRow[0];
	}

	$SQL = "INSERT INTO stockmoves (stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
					VALUES ('" . $item['stockid'] . "',510,'" . $RequestNo . "','" . $loc . "','" . $date . "',
							'From " . DB_escape_string($src) . "','" . round($item['quantity_received'], $decimal) . "','" . $PeriodNo . "',
							'" . round($QtyOnHandPrior + $item['quantity_received'], $decimal) . "')";

	$ErrMsg =  'CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE : ';
	$ErrMsg .= 'The stock movement record for the incoming stock cannot be added because';
	$DbgMsg =  'The following SQL to insert the stock movement record was used';
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

	$SQL = "UPDATE locstock SET quantity = quantity + '" . round($item['quantity_received'], $decimal) . "'
					WHERE stockid='" . $item['stockid'] . "'
					AND loccode='" . $loc . "'";

	$ErrMsg = 'CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE : ';
	$ErrMsg .= 'The location stock record could not be updated because';
	$DbgMsg =  'The following SQL to update the stock record was used';
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

	$substore = 9;

	if ($loc == "HO") {
		$substore = 10;
	} else if ($loc == "MT") {
		$substore = 5;
	} else if ($loc == "SR") {
		$substore = 9;
	} else if ($loc == "VSR") {
		$substore = 11;
	}

	$SQL = "UPDATE substorestock SET quantity = quantity + '" . round($item['quantity_received'], $decimal) . "'
					WHERE stockid='" . $item['stockid'] . "' AND substoreid='" . $substore . "'";

	$ErrMsg = 'CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE : ';
	$ErrMsg .= 'The location stock record could not be updated because';
	$DbgMsg =  'The following SQL to update the stock record was used';
	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

	$total_value = $item['price'] * $item['quantity_received'];
	$SQL = "INSERT INTO igp_parchi (stockid, quantity, parchino, total_value, price, pdate)
                VALUES('" . $item['stockid'] . "', 
                       '" . $item['quantity_received'] . "', 
                       '" . $parchi . "', 
                       '" . $total_value . "', 
                       '" . $item['price'] . "', 
                       '" . $date . "')";
	DB_query($SQL, $db);
}

$SQL = "UPDATE bazar_parchi 
				SET igp_created=1,
					igp_id='" . $RequestNo . "',
					updated_at='" . date("Y-m-d H:i:s") . "'  
				WHERE parchino='" . $parchi . "'";
DB_query($SQL, $db);

DB_Txn_Commit($db);

echo json_encode([

	'status' => 'success',
	'message' => 'IGP created Successfully.'

]);
