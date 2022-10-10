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

// DB table to use
$table = 'salescase';

// Table's primary key
$primaryKey = 'salescaseindex';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => 'salescaseindex',   'dt' => 0 ),
	
	array( 'db' => 'salescaseref',  'dt' => 1,
		'formatter' => function( $d, $row ) {
			$href = 'salescase.php/?salescaseref='.$d;
			return '<a href='.$href.' target="_blank">'.$d.'</a>';
		}

	),
	array( 'db' => 'salescasedescription',   'dt' => 2 ),
	
	array( 'db' => 'salesman',   'dt' => 3 ),
	array(
		'db'        => 'commencementdate',
		'dt'        => 4,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date( 'jS M y', strtotime($d));
		}
	),
	array( 'db' => 'value',     'dt' => 5 ),
	
	
	array(
		'db'        => 'enquirydate',
		'dt'        => 6,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date( 'jS M y', strtotime($d));
		}
	),
	array(
		'db'        => 'lastquotationdate',
		'dt'        => 7,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date( 'jS M y', strtotime($d));
		}),

	array(
		'db'        => 'podate',
		'dt'        => 8,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date( 'jS M y', strtotime($d));
		}),
		
	array(
		'db'        => 'ocdocumentdate',
		'dt'        => 9,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date( 'jS M y', strtotime($d));
		}
	)
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

// Validate the JSONP to make use it is an okay Javascript function to execute
$jsonp = preg_match('/^[$A-Z_][0-9A-Z_$]*$/i', $_GET['callback']) ?
	$_GET['callback'] :
	false;

if ( $jsonp ) {
	echo $jsonp.'('.json_encode(
		SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
	).');';
}

