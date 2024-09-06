<?php

	$PathPrefix='../../../../';
	include('../../../../includes/session.inc');
	include('../../../../includes/SQL_CommonFunctions.inc');

	$SearchString = '%' . $_POST['term'] . '%';
	$selectedItem = $_POST['itemIndex'];
	$parchi = $_POST['parchi'];
	$obo = $_POST['obo'];

	$SQL = "SELECT stockid FROM ogpmporef 
			WHERE salesman='" . $obo . "'";
$result1 = mysqli_query($db, $SQL);
if (mysqli_num_rows($result1) > 0) {
	$searchid = null;
	while ($row1 = mysqli_fetch_assoc($result1)) {
		$searchid = "'" . $row1['stockid'] . "'" . ',' . $searchid;
	}
	$searchid = substr_replace($searchid, "", -1);
	$SQL = "SELECT  stockmaster.stockid,
					stockmaster.mnfcode,
					stockmaster.mnfpno,
					stockmaster.description,
					ogpmporef.quantity AS qoh,
					manufacturers.manufacturers_name as mname
			FROM stockmaster 
			INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
			INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
			INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN stockissuance ON stockmaster.stockid = stockissuance.stockid
			INNER JOIN ogpmporef ON stockmaster.stockid = ogpmporef.stockid
			WHERE (stockcategory.stocktype='F' 
				OR stockcategory.stocktype='D' 
				OR stockcategory.stocktype='L')
			AND (stockmaster.mnfCode IN ($searchid)
			OR stockmaster.stockid IN ($searchid)
			OR stockmaster.description IN ($searchid))
			AND stockmaster.mbflag <>'G'
			AND ogpmporef.salesman = '$obo'
			AND ogpmporef.mpo = '$parchi'
			AND stockissuance.salesperson='$obo'
			AND stockmaster.discontinued=0
			AND stockissuance.issued > 0
			AND stockmaster.stockid NOT LIKE '%\t%'
			GROUP BY locstock.stockid
			ORDER BY stockmaster.stockid";

	$res = mysqli_query($db, $SQL);

	$response = [];

	while($row = mysqli_fetch_assoc($res)){
		$row['action'] = "<button class='btn btn-success' onclick='attachSKU(\"".$selectedItem."\",\"".$row['stockid']."\")'>Attach</button>";
		$response[] = $row;
	}

	echo json_encode([
			'status' => 'success',
			'data'	 => $response
		]);
	}
	// else{
	// 	$SQL = "SELECT  stockmaster.stockid,
	// 				stockmaster.mnfcode,
	// 				stockmaster.mnfpno,
	// 				stockmaster.description,
	// 				stockissuance.issued AS qoh,
	// 				manufacturers.manufacturers_name as mname
	// 		FROM stockmaster 
	// 		INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
	// 		INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
	// 		INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
	// 		INNER JOIN stockissuance ON stockmaster.stockid = stockissuance.stockid
	// 		WHERE (stockcategory.stocktype='F' 
	// 			OR stockcategory.stocktype='D' 
	// 			OR stockcategory.stocktype='L')
	// 		AND (stockmaster.mnfCode LIKE '" . $SearchString . "'
	// 		OR stockmaster.stockid LIKE '" . $SearchString. "'
	// 		OR stockmaster.description LIKE '%" . $SearchString. "%')
	// 		AND stockmaster.mbflag <>'G'
	// 		AND stockissuance.salesperson='$obo'
	// 		AND stockmaster.discontinued=0
	// 		AND stockissuance.issued > 0
	// 		AND stockmaster.stockid NOT LIKE '%\t%'
	// 		GROUP BY locstock.stockid
	// 		ORDER BY stockmaster.stockid";

	// $res = mysqli_query($db, $SQL);

	// $response = [];

	// while($row = mysqli_fetch_assoc($res)){
	// 	$row['action'] = "<button class='btn btn-success' onclick='attachSKU(\"".$selectedItem."\",\"".$row['stockid']."\")'>Attach</button>";
	// 	$response[] = $row;
	// }

	// echo json_encode([
	// 		'status' => 'success',
	// 		'data'	 => $response
	// 	]);
	// }