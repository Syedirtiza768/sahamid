<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
session_start();
include ('includes/MiscFunctions.php');
$table = 'branditemanalysis'.$_SESSION['UserID'].'';

// Table's primary key
$primaryKey = 'branditemanalysisindex';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => 'stockid',   'dt' => 0 ),
	array( 'db' => 'mnfCode',   'dt' => 1 ),
	array( 'db' => 'mnfpno',   'dt' => 2 ),
	array( 'db' => 'description',   'dt' => 3 ),
	array( 'db' => 'stockid',   'dt' => 4 ),
	array( 'db' => 'QuotationCount',     'dt' => 5, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
		array( 'db' => 'OCCount',     'dt' => 6, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
		array( 'db' => 'DCCount',     'dt' => 7, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'QtyQuotation',     'dt' => 8, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
		array( 'db' => 'QtyOC',     'dt' => 9, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'QtyDC',     'dt' => 10, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'AvgDiscountQ',     'dt' => 11, 
		'formatter' => function( $d, $row ) {
		//	return $d;
		$d=$d*100;
			return locale_number_format($d,2);
		}),
	array( 'db' => 'AvgDiscountO',     'dt' => 12, 
		'formatter' => function( $d, $row ) {
		//	return $d;
		$d=$d*100;
			return locale_number_format($d,2);
		}),
	array( 'db' => 'AvgDiscountD',     'dt' => 13, 
		'formatter' => function( $d, $row ) {
		//	return $d;
		$d=$d*100;
			return locale_number_format($d,2);
		}),
	array( 'db' => 'AvgPriceQ',     'dt' => 14, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'AvgPriceO',     'dt' => 15, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'AvgPriceD',     'dt' => 16, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'ListPrice',     'dt' => 17, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'TotalOGPMTO',     'dt' => 18, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'TotalOGPHO',     'dt' => 19, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'QOH',     'dt' => 20, 
		'formatter' => function( $d, $row ) {
		//	return $d;
			return locale_number_format($d,0);
		}),
	
	
	
	
	)
;

// SQL server connection information
$sql_details = array(
	'user' => 'irtiza',
	'pass' => 'netetech321',
	'db'   => 'sahamid',
	'host' => 'localhost'
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );
$whereAll = "salesman = '".$_SESSION['UsersRealName']."'";
if (1)
{echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns)
);}



