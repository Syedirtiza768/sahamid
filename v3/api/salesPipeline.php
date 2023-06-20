<?php

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	//Opportunity Pipeline
	$SQL = "SELECT count(*) as count FROM salescase 
			LEFT OUTER JOIN salesorders ON salescase.salescaseref = salesorders.salescaseref
			WHERE salesorders.orderno IS NULL
			AND salescase.closed = 0
			AND commencementdate <= '".date("Y-m-t 23:59:59")."'
			AND commencementdate >= '".date("Y-m-01 00:00:00")."'";

	$oP  = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

	//Quotaion Pipeline
	$SQL = "SELECT count(*) as count FROM salescase 
			INNER JOIN salesorders ON salescase.salescaseref = salesorders.salescaseref
			LEFT OUTER JOIN ocs ON salescase.salescaseref = ocs.salescaseref
			WHERE salescase.podate = '0000-00-00 00:00:00' 
			AND ocs.orderno IS NULL
			AND salescase.closed = 0 
			AND commencementdate <= '".date("Y-m-t 23:59:59")."'
			AND commencementdate >= '".date("Y-m-01 00:00:00")."'
			GROUP BY salescase.salescaseref";

	$qP  = mysqli_num_rows(mysqli_query($db, $SQL));

	//PO Pipeline
	$SQL = "SELECT count(*) as count FROM salescase 
			INNER JOIN salesorders ON salescase.salescaseref = salesorders.salescaseref
			LEFT OUTER JOIN ocs ON salescase.salescaseref = ocs.salescaseref
			LEFT OUTER JOIN dcs ON salescase.salescaseref = dcs.salescaseref
			WHERE (salescase.podate != '0000-00-00 00:00:00' 
				OR ocs.orderno IS NOT NULL)
			AND (dcs.orderno IS NULL)
			AND salescase.closed = 0 
			AND commencementdate <= '".date("Y-m-t 23:59:59")."'
			AND commencementdate >= '".date("Y-m-01 00:00:00")."'
			GROUP BY salescase.salescaseref";

	$poP  = mysqli_num_rows(mysqli_query($db, $SQL));

	//DC Pipeline
	$SQL = "SELECT count(*) as count FROM salescase 
			INNER JOIN salesorders ON salescase.salescaseref = salesorders.salescaseref
			LEFT OUTER JOIN dcs ON salescase.salescaseref = dcs.salescaseref
			WHERE dcs.orderno IS NOT NULL
			AND dcs.inprogress = 1
			AND dcs.invoicegroupid IS NULL
			AND commencementdate <= '".date("Y-m-t 23:59:59")."'
			AND commencementdate >= '".date("Y-m-01 00:00:00")."'
			GROUP BY salescase.salescaseref";

	$dcP  = mysqli_num_rows(mysqli_query($db, $SQL));

	//Invoice Pipeline
	$SQL = "SELECT count(*) as count FROM salescase 
			INNER JOIN dcs ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN invoice ON invoice.groupid = dcs.invoicegroupid
			WHERE dcs.invoicegroupid IS NOT NULL
			AND commencementdate <= '".date("Y-m-t 23:59:59")."'
			AND commencementdate >= '".date("Y-m-01 00:00:00")."'
			GROUP BY salescase.salescaseref";

	$iP  = mysqli_num_rows(mysqli_query($db, $SQL));

	$res = [];

	$res[] = [
		'Opportunity',
		(int)($oP?:0)
	];

	$res[] = [
		'Quotation',
		(int)($qP?:0)
	];

	$res[] = [
		'Purchase Order',
		(int)($poP?:0)
	];

	$res[] = [
		'Delivery Chalan',
		(int)($dcP?:0)
	];

	$res[] = [
		'Invoice',
		(int)($iP?:0)
	];

	echo json_encode($res);

