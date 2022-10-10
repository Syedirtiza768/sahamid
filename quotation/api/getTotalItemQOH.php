<?php 

	include('../misc.php');

	$stockid = $_GET['stockid'];

	$db = createDBConnection();

	$SQL = "SELECT  SUM(locstock.quantity) as qoh
			FROM stockmaster
			INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
			WHERE stockmaster.stockid = '".$stockid."'
			GROUP BY stockmaster.stockid";

	$qoh = mysqli_query($db, $SQL);

	$SQL = "SELECT  SUM(locstock.quantity) as qoh
			FROM stockmaster
			INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
			WHERE stockmaster.stockid = '".$stockid."'
			AND (locstock.loccode = 'HO'
				OR locstock.loccode = 'MT'
				OR locstock.loccode = 'SR')
			GROUP BY stockmaster.stockid";

	$is = mysqli_query($db, $SQL);

	echo json_encode([
			'status' => 'success',
			'qoh'	 => mysqli_fetch_assoc($qoh)['qoh'],
			'is'	 => mysqli_fetch_assoc($is)['qoh']
		]);
