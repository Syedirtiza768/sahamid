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
$table = 'salescaseclosedreporting';

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
	array( 'db' => 'stage',   'dt' => 3 ),
	array( 'db' => 'closingreason',   'dt' => 4 ),
	array( 'db' => 'closingremarks',   'dt' => 5 ),
	array(
		'db'        => 'closingdate',
		'dt'        => 6,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date("d/m/Y", strtotime($d));
		}
	),
	
	array( 'db' => 'salesman',   'dt' => 7 ),
	array(
		'db'        => 'commencementdate',
		'dt'        => 8,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date("d/m/Y", strtotime($d));
		}
	),
	array( 'db' => 'debtorname',     'dt' => 9 ),
	
	array(
		'db'        => 'enquirydate',
		'dt'        => 10,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date("d/m/Y", strtotime($d));
		}
	),
	array( 'db' => 'enquiryvalue',     'dt' => 11 ),
	array( 'db' => 'orderno',  'dt' => 12,
		'formatter' => function( $d, $row ) {
			$href = 'PDFQuotation.php/?QuotationNo='.$d;
			return '<a href='.$href.' target="_blank">'.$d.'</a>';
		}

	),
	array( 'db' => 'quotationvalue',     'dt' => 13 ),
	
/*	
	array(
		'db'        => 'lastquotationdate',
		'dt'        => 7,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date( 'jS M y', strtotime($d));
		}),
*/
	
		
	array(
		'db'        => 'podate',
		'dt'        => 14,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date("d/m/Y", strtotime($d));
		}
	),
	
/*	array( 'db' => 'salescaseref',  'dt' => 11,
		'formatter' => function( $d, $row ) {
			$href = 'polink.php/?salescaseref='.$d;
			return '<a href="javascript:void(0);"
 NAME="My Window Name"  title=" My title here "
 onClick=window.open("polink.php/?salescaseref='.$d.'","Ratting","width=550,height=170,0,status=0,scrollbars=1");>PO LINKS</a>';
		}

	),*/
	
	array( 'db' => 'dclink',  'dt' => 15,
		'formatter' => function( $d, $row ) {
				$href='"javascript:void(0);"
 NAME="My Window Name"  title=" My title here "
 onClick=window.open("dclinks.php/?dclink='.$d.'","Ratting","width=550,height=170,0,status=0,scrollbars=1")';
			return '<a href='.$href.'>' . $d  . '</a>';
		}

	),
	
	array( 'db' => 'commentcode',  'dt' => 16,
		'formatter' => function( $d, $row ) {
			$href='"javascript:void(0);"
 NAME="My Window Name"  title=" My title here "
 onClick=window.open("lastcomment.php/?commentcode='.$d.'","Ratting","width=550,height=170,0,status=0,scrollbars=1")';
			return '<a href='.$href.' >' . $d  . '</a>';
		}

	),
	
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
if ($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 8 
OR $_SESSION['AccessLevel'] == 11 OR $_SESSION['AccessLevel'] == 23 OR $_SESSION['AccessLevel'] == 27)
{echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns)
);}
else
{echo json_encode(
	SSP::complex($_GET, $sql_details, $table, $primaryKey, $columns, $whereResult = null, $whereAll)
);}



