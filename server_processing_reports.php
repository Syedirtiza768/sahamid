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
$table = 'advancedreporting'.$_SESSION['UserID'].'';

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
		array( 'db' => 'salescasedescription',   'dt' => 2 ,
	'formatter' => function( $d, $row ) {
	return stripslashes($d);
	}
	),
	
	array( 'db' => 'salesman',   'dt' => 3 ),
	array(
		'db'        => 'commencementdate',
		'dt'        => 4,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date("d/m/Y", strtotime($d));
		}
	),
	array( 'db' => 'debtorname',     'dt' => 5 ),
	

	array( 'db' => 'enquiryvalue',     'dt' => 6 ),
	
	array( 'db' => 'quotationvalue',     'dt' => 7 ),
	array( 'db' => 'ocvalue',     'dt' => 8 ),
	array( 'db' => 'dcvalue',     'dt' => 9 ),
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
if ($_SESSION['AccessLevel'] != 14)
{echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns)
);}
else
{echo json_encode(
	SSP::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult = null, $whereAll)
);}



