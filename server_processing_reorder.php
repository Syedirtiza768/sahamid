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







$table = 'reorderlevel';

// Table's primary key
$primaryKey = 'reorderlevelindex';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array( 'db' => 'categorydescription',   'dt' => 0 ),
	
	
	
	array( 'db' => 'mnfpno',  'dt' => 1),
	array( 'db' => 'mnfCode',   'dt' => 2 ),
	array( 'db' => 'manufacturers_name',   'dt' => 3 ),
	
	array( 'db' => 'description',  'dt' => 4),
	array( 'db' => 'conditionID',  'dt' => 5),
	array( 	'db' => 'locationname',   'dt' => 6 ),
	array( 	'db' => 'IGP',   'dt' => 7 ),
	array( 	'db' => 'OGP',   'dt' => 8 ),
	array( 	'db' => 'DC',   'dt' => 9 ),
	
	array(	'db'        => 'quantity','dt'        => 10),
	array( 	'db' => 'reorderlevel',     'dt' => 11 ),
	array(	'db'        => 'needed','dt'        => 12)
	
	
	)
;

// SQL server connection information
$sql_details = array(
	'user' => 'root',
	'pass' => '',
	'db'   => 'sahamid',
	'host' => 'localhost'
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( 'ssp.class.php' );
//$condition = "manufacturers_name = $_GET[manufacturers_name]";


echo json_encode(

	SSP::complex( $_GET, $sql_details, $table, $primaryKey, $columns, null,null )
	
	);


