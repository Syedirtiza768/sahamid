<?php

	include("../../quotation/misc.php");
	session_start();

	if(!isset($_POST['document']) || !isset($_POST['cat']) || !isset($_POST['brand']) || !isset($_POST['stockid']) || !isset($_POST['desc'])){

		echo json_encode([

				'status'  => 'error',
				'message' => 'Missing Parameters.'

			]);
		return;

	}

	$document 	= $_POST['document'];
	$cat 		= $_POST['cat'];
	$brand 		= $_POST['brand'];
	$stockid 	= str_replace(" ", "%", $_POST['stockid']);
	$desc 		= str_replace(" ", "%", $_POST['desc']);

	$db = createDBConnection();

	$SQL = "SELECT  stockmaster.stockid,
					stockmaster.mnfcode,
					stockmaster.mnfpno,
					stockmaster.description,
					stockmaster.units,
					manufacturers.manufacturers_name as mname,
					SUM(locstock.quantity) AS qoh
			FROM stockmaster
			INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
			INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
			INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
			WHERE (stockmaster.stockid LIKE '%".$stockid."%'
				OR stockmaster.mnfcode LIKE '%".$stockid."%'
				OR stockmaster.mnfpno LIKE '%".$stockid."%')
			AND stockmaster.description LIKE '%".$desc."%'
			AND stockmaster.stockid NOT LIKE '%\t%'
			";

	if($cat != "ALL"){
		$SQL .= "AND stockmaster.categoryid LIKE '%" . $cat . "%' ";
	}

	if($brand != "ALL"){
		$SQL .= "AND manufacturers.manufacturers_id = '".$brand."' ";
	}
	
	$SQL .= "GROUP BY stockmaster.stockid
			 ORDER BY stockmaster.stockid";	

	$res = mysqli_query($db, $SQL);

	$rows = [];

	$SQL = "SELECT stockid FROM procurement_document_details WHERE pdid=$document";
	$existing = mysqli_query($db, $SQL);

	$stockids = [];
	while($row = mysqli_fetch_assoc($existing)){ $stockids[$row['stockid']] = $row['stockid']; }

	while($row = mysqli_fetch_assoc($res)){

		$SQL = "SELECT SUM(approved_qty) as qty 
				FROM reorderitems 
				WHERE stockid='".$row['stockid']."'
				AND approved_date IS NOT NULL
				AND rejected_date IS NULL 
				AND fulfilled_date IS NULL";
		$row['ack'] = (mysqli_fetch_assoc(mysqli_query($db, $SQL))['qty']?:0);

		$SQL = "SELECT SUM(CASE WHEN (stockmoves.type=512) THEN stockmoves.qty ELSE 0 END) AS dc 
				FROM stockmoves
				WHERE stockid='".$row['stockid']."'
				AND trandate >= (now() - INTERVAL 12 MONTH)
				GROUP BY stockid";
		$dr = mysqli_fetch_assoc(mysqli_query($db, $SQL));

		$SQL = "SELECT  SUM(CASE WHEN (stockmoves.type=510) THEN stockmoves.qty ELSE 0 END) AS igp, 
						SUM(CASE WHEN (stockmoves.type=511 OR stockmoves.type=513) THEN stockmoves.qty ELSE 0 END) AS ogp
				FROM stockmoves
				WHERE stockid='".$row['stockid']."'
				AND trandate >= (now() - INTERVAL 12 MONTH)
				AND (stockmoves.loccode = 'HO') 
				GROUP BY stockid";
		$hr = mysqli_fetch_assoc(mysqli_query($db, $SQL));

		$SQL = "SELECT  SUM(CASE WHEN (stockmoves.type=510) THEN stockmoves.qty ELSE 0 END) AS igp, 
						SUM(CASE WHEN (stockmoves.type=511 OR stockmoves.type=513) THEN stockmoves.qty ELSE 0 END) AS ogp
				FROM stockmoves
				WHERE stockid='".$row['stockid']."'
				AND trandate >= (now() - INTERVAL 12 MONTH)
				AND (stockmoves.loccode = 'SR') 
				GROUP BY stockid";
		$sr = mysqli_fetch_assoc(mysqli_query($db, $SQL));

		$SQL = "SELECT  SUM(locstock.quantity) as qoh
			FROM stockmaster
			INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
			WHERE stockmaster.stockid = '".$row['stockid']."'
			AND (locstock.loccode = 'HO'
				OR locstock.loccode = 'MT'
				OR locstock.loccode = 'SR')
			GROUP BY stockmaster.stockid";

		$is = mysqli_query($db, $SQL);
		$row['is'] = mysqli_fetch_assoc($is)['qoh'];

		$row['igp'] 	= ($hr['igp']?:0);
		$row['ogp'] 	= ($hr['ogp']?:0);
		$row['igp-sr'] 	= ($sr['igp']?:0);
		$row['ogp-sr'] 	= ($sr['ogp']?:0);
		$row['dc']  	= ($dr['dc']?:0);

		if(array_key_exists($row['stockid'], $stockids)){
			$row['action'] = "<button class='btn btn-warning'>Already Added</button>";
		}else{
			$row['action'] = "<button data-stockid='".$row['stockid']."' class='btn btn-info addnewitemcls'>
								Add Item
							</button>";
		}
		
		$rows[] = $row;

	}

	$response = [];
	$response['status'] = "success";
	$response['data'] = $rows;

	echo json_encode($response);
	return;