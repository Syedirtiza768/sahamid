<?php

include('../misc.php');

$db = createDBConnection();

$SQL = "SELECT salesman FROM salescase 
			WHERE salescaseref='" . $_POST['salescaseref'] . "'";
$result = mysqli_query($db, $SQL);
$row = mysqli_fetch_assoc($result);

$SQL = "SELECT DISTINCT stockid FROM ogpsalescaseref 
			WHERE salescaseref='" . $_POST['salescaseref'] . "'";
$result1 = mysqli_query($db, $SQL);
if (mysqli_num_rows($result1) > 0) {
	$stockid = NULL;
	while ($row1 = mysqli_fetch_assoc($result1)) {
		$stockid = "'" . $row1['stockid'] . "'," . $stockid;
	}
	$stockid = substr($stockid, 0, -1);
	$SQL = "SELECT  stockmaster.stockid,
			stockmaster.description,
			stockmaster.mnfcode,
			stockmaster.longdescription,
			stockmaster.mnfCode,
			stockmaster.mnfpno,
			stockmaster.mbflag,
			stockmaster.discontinued,
			ogpsalescaseref.quantity AS qoh,
			stockmaster.units,
			manufacturers.manufacturers_name as mname,
			stockmaster.decimalplaces
		FROM stockmaster 
		INNER JOIN manufacturers
		ON stockmaster.brand = manufacturers.manufacturers_id
		INNER JOIN stockissuance
		ON stockmaster.stockid=stockissuance.stockid
		INNER JOIN ogpsalescaseref
		ON stockmaster.stockid=ogpsalescaseref.stockid
		where stockissuance.salesperson = '" . $row['salesman'] . "'
		AND stockmaster.stockid IN(" . $stockid . ")
		AND stockissuance.issued > 0
		AND stockmaster.stockid NOT LIKE '%\t%'
		AND ogpsalescaseref.quantity != ''
		AND ogpsalescaseref.salescaseref = '". $_POST['salescaseref'] ."'
		ORDER BY stockissuance.issued desc";

	$result = mysqli_query($db, $SQL);

	while ($row = mysqli_fetch_assoc($result)) {

		$row['action'] = "<button class='btn btn-success' style='width:100%; height:100%; margin:0' onclick='insertitem(\"" . $row['stockid'] . "\",\"" . $row['mname'] . "\")'>Add To Option</button>";
		$response[] = $row;
	}

	utf8_encode_deep($response);

	$fres = [];
	$fres['status'] = "success";
	$fres['data'] = $response;

	$eresponse = json_encode($fres);

	echo $eresponse;
} else {
	$SQL = "SELECT  stockmaster.stockid,
					stockmaster.description,
					stockmaster.mnfcode,
					stockmaster.longdescription,
					stockmaster.mnfCode,
					stockmaster.mnfpno,
					stockmaster.mbflag,
					stockmaster.discontinued,
					stockissuance.issued AS qoh,
					stockmaster.units,
					manufacturers.manufacturers_name as mname,
					stockmaster.decimalplaces
				FROM stockmaster 
				INNER JOIN manufacturers
				ON stockmaster.brand = manufacturers.manufacturers_id
				INNER JOIN stockissuance
				ON stockmaster.stockid=stockissuance.stockid
				where stockissuance.salesperson = '" . $row['salesman'] . "'
				AND (stockmaster.stockid LIKE '%" . $_POST['StockCode'] . "%'
						OR stockmaster.mnfcode LIKE '%" . $_POST['StockCode'] . "%'
						OR stockmaster.mnfpno LIKE '%" . $_POST['StockCode'] . "%'
						OR stockmaster.description LIKE '%" . $_POST['StockCode'] . "%')
				AND stockissuance.issued > 0
				AND stockmaster.stockid NOT LIKE '%\t%'
				ORDER BY stockissuance.issued desc";

	$result = mysqli_query($db, $SQL);

	while ($row = mysqli_fetch_assoc($result)) {

		$row['action'] = "<button class='btn btn-success' style='width:100%; height:100%; margin:0' onclick='insertitem(\"" . $row['stockid'] . "\",\"" . $row['mname'] . "\")'>Add To Option</button>";
		$response[] = "$row";
	}
	utf8_encode_deep($response);

	$fres = [];
	$fres['status'] = "success";
	$fres['data'] = $response;

	$eresponse = NULL;

	echo $eresponse;
}





return;
