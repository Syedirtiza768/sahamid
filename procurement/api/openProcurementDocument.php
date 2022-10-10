<?php

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	include('stages.php');

	if(!isset($_POST['id'])){
		echo json_encode([
				'status' => 'error',
				'message' => 'Missing Parms.'
			]);
		return;
	}

	$id = $_POST['id'];

	$SQL = "SELECT procurement_document.id,
				   suppliers.suppname as supplier,
				   procurement_document.commencement_date as startdate,
				   procurement_document.stage,
				   procurement_document.timeline,
				   procurement_document.eta_date as eta,
				   procurement_document.publish_date as published,
				   procurement_document.canceled_date as canceled,
				   procurement_document.received_date as received
			FROM procurement_document 
			INNER JOIN suppliers ON suppliers.supplierid = procurement_document.supplierid
			WHERE procurement_document.id = $id";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) != 1){
		echo json_encode([
				'status' => 'error',
				'message' => 'The Document You are trying to open was discarded'
			]);
		return;
	}

	$res = mysqli_fetch_assoc($res);

	$timeline = [];
	foreach (json_decode($res['timeline']) as $key => $value) {
		$timeline[] = [
			'stage' => $value->stage,
			'date'	=> date("d/m/Y",strtotime($value->date))
		];
	}

	$document 				= [];
	$document['id'] 		= $res['id'];
	$document['stage'] 		= $res['stage'];
	$document['spermission']= $stages[$document['stage']];
	$document['nextstage']  = $res['stage'];
	$document['supplier']	= $res['supplier'];
	$document['eta']		= $res['eta'] ? date("Y-m-d",strtotime($res['eta'])):"0000-00-00";
	$document['published']	= $res['published'];
	$document['canceled']   = $res['canceled'];
	$document['received']   = $res['received'];
	$document['timeline']	= $timeline;
	$document['items']		= [];

	$documentID = $document['id'];

	$SQL = "SELECT  stockmaster.stockid,
					stockmaster.mnfcode,
					stockmaster.mnfpno,
					stockmaster.description,
					stockmaster.units,
					manufacturers.manufacturers_name as mname,
					procurement_document_details.client_required,
					procurement_document_details.safety_inventory,
					procurement_document_details.stock,
					procurement_document_details.quantity,
					procurement_document_details.price,
					SUM(locstock.quantity) AS qoh
			FROM procurement_document_details
			INNER JOIN stockmaster ON stockmaster.stockid = procurement_document_details.stockid
			INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
			INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
			WHERE procurement_document_details.pdid='$documentID'
			GROUP BY stockmaster.stockid
			ORDER BY procurement_document_details.id";
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){
		$SQL = "SELECT SUM(approved_qty) as qty 
				FROM reorderitems 
				WHERE stockid='".$row['stockid']."'
				AND approved_date IS NOT NULL
				AND rejected_date IS NULL 
				AND fulfilled_date IS NULL";
		$row['ack'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['qty'];

		$SQL = "SELECT  SUM(CASE WHEN (stockmoves.type=510) THEN stockmoves.qty ELSE 0 END) AS igp, 
						SUM(CASE WHEN (stockmoves.type=511 OR stockmoves.type=513) THEN stockmoves.qty ELSE 0 END) AS ogp, 
						SUM(CASE WHEN (stockmoves.type=512) THEN stockmoves.qty ELSE 0 END) AS dc 
				FROM stockmoves
				WHERE stockid='".$row['stockid']."'
				AND trandate >= (now() - INTERVAL 12 MONTH)
				GROUP BY stockid";
		$r = mysqli_fetch_assoc(mysqli_query($db, $SQL));

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

		$row['igp'] 	= $hr['igp'];
		$row['ogp'] 	= $hr['ogp'];
		$row['igp-sr'] 	= $sr['igp'];
		$row['ogp-sr'] 	= $sr['ogp'];
		$row['dc']  	= $r['dc'];
		$row['is'] 		= mysqli_fetch_assoc($is)['qoh'];

		$document['items'][] = $row;
	}

	echo json_encode([
			'status' 	=> 'success',
			'document' 	=> $document
		]);
	return;