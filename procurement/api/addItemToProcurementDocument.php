<?php

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['id']) || !isset($_POST['stockid'])){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parms.'
			]);
		return;
	}

	$id 		= $_POST['id'];
	$stockid 	= $_POST['stockid'];

	$SQL = "SELECT * FROM procurement_document 
			WHERE canceled_date IS NULL 
			AND received_date IS NULL
			AND id=$id";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		echo json_encode([
				'status' => 'error',
				'message' => 'Shipment received or order canceled.'
			]);
		return;
	}

	$SQL = "SELECT * FROM procurement_document_details 
			WHERE stockid='$stockid'
			AND pdid=$id";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) > 0){
		echo json_encode([
				'status' => 'error',
				'message' => 'Item Already Added.'
			]);
		return;
	}

	$SQL = "INSERT INTO procurement_document_details(pdid,stockid)
			VALUES ($id, '$stockid')";
	
	if(!DB_query($SQL, $db)){
		echo json_encode([
				'status' => 'error',
				'message' => 'Error Occured when adding item.'
			]);
		return;
	}

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
			WHERE stockmaster.stockid='$stockid'
			GROUP BY stockmaster.stockid";
	$res = mysqli_query($db, $SQL);
	$item = mysqli_fetch_assoc($res);
	
	$SQL = "SELECT SUM(approved_qty) as qty 
			FROM reorderitems 
			WHERE stockid='".$item['stockid']."'
			AND approved_date IS NOT NULL
			AND rejected_date IS NULL 
			AND fulfilled_date IS NULL";
	$item['ack'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['qty'];

	$SQL = "SELECT SUM(CASE WHEN (stockmoves.type=512) THEN stockmoves.qty ELSE 0 END) AS dc 
			FROM stockmoves
			WHERE stockid='".$row['stockid']."'
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
	
	$item['igp'] 	= ($hr['igp']?:0);
	$item['ogp'] 	= ($hr['ogp']?:0);
	$item['igp-sr'] = ($sr['igp']?:0);
	$item['ogp-sr'] = ($sr['ogp']?:0);
	$item['dc']  	= ($dr['dc']?:0);
	$item['is'] 	= (mysqli_fetch_assoc($is)['qoh']?:0);


	$item['client_required'] = 0;
	$item['safety_inventory'] = 0;
	$item['stock'] = 0;
	$item['price'] = 0;
	
	$response = [
		'status' => 'success',
		'data'	 => $item
	];

	echo json_encode($response);
