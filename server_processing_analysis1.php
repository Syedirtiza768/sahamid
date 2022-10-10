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
$table = 'salescasereporting'.$_SESSION['UserID'].'';

// Table's primary key
$primaryKey = 'salescaseindex';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
	array('db' => 'salescaseindex',     'dt' => 0 ),
	array(
		'db'        => 'commencementdate',
		'dt'        => 1,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date("d/m/Y", strtotime($d));
		}
	),
	array( 'db' => 'salescaseref',     'dt' => 2 ),
	array( 'db' => 'debtorname',     'dt' => 3 ),
	
	array( 'db' => 'salesman',   'dt' => 4 ),
	array( 'db' => 'orderno',  'dt' => 5,
		'formatter' => function( $d, $row ) {
			$href = 'PDFQuotation.php/?QuotationNo='.$d;
			return '<a href='.$href.' target="_blank">'.$d.'</a>';
		}

	),
		array( 'db' => 'salescasedescription',   'dt' => 6 ,
		
	'formatter' => function( $d, $row ) {
		
			$conn = mysqli_connect('localhost','root','','sahamid');
			$sql = 'SELECT * FROM `salescase` INNER JOIN salesorders ON 
			salesorders.salescaseref=salescase.salescaseref INNER JOIN salesorderdetails
			ON salesorders.orderno=salesorderdetails.orderno
			WHERE
			salescase.salescasedescription = "'.$d.'"';
			$result = mysqli_query($conn,$sql);
			$item='';
			while($myrow = mysqli_fetch_assoc($result))
			{
				$sqlA = 'SELECT * FROM `stockmaster` INNER JOIN manufacturers ON 
			stockmaster.brand=manufacturers.manufacturers_id
			WHERE
			stockmaster.stockid = "'.$myrow['stkcode'].'"';
		
			$resultA = mysqli_query($conn,$sqlA);
				$myrowA = mysqli_fetch_assoc($resultA);
				$item.= $myrowA['manufacturers_name'].' '.$myrow['stkcode'].'<br/>';
	
	
	
			}

			return $item;
	}
	),
	array( 'db' => 'quotationvalue',     'dt' => 7, 
		'formatter' => function( $d, $row ) {
			//return $d;
			return locale_number_format($d,0);
		}),
	array( 'db' => 'salescaseref',     'dt' => 8 ,//quotation status
	
	'formatter' => function( $d, $row ) {
		$conn = mysqli_connect('localhost','root','','sahamid');
		$sqlR = 'SELECT salescase.closingreason as closingreason,
				salescase.podate,
				quotationvalue'.$_SESSION['UserID'].'.value
				FROM salescase inner join quotationvalue'.$_SESSION['UserID'].'
		ON salescase.salescaseref= quotationvalue'.$_SESSION['UserID'].'.salescaseref
			where 
			salescase.salescaseref= "'.$d.'"';
			
			
			$resultR = mysqli_query($conn,$sqlR);
			
			$rowR=mysqli_fetch_assoc($resultR);
			if ($rowR['closingreason']=='high prices'
			OR $rowR['closingreason']== "Delivery time offered is not acceptable by customer" 

OR $rowR['closingreason']=="Customer doesn't want in equal"
			)
			return 'LOST-'.$rowR['closingreason'];
		if ($rowR['closingreason']=='internal cross'
			OR $rowR['closingreason']== "external cross
" 

OR $rowR['closingreason']=="Customer has cancelled the enquiry"
OR $rowR['closingreason']=="any other reason"
)
			return 'Cancelled-'.$rowR['closingreason'];
		if ($rowR['closingreason']=='' AND $rowR['podate']=='0000-00-00 00:00:00' AND $rowR['value']!= '0' )
			return 'In Pipeline';
		if ($rowR['podate']=='0000-00-00 00:00:00' AND $rowR['value']== '0' )
			return 'Out of Scope';
		if ($rowR['podate']>'0000-00-00 00:00:00')
			return 'PO Uploaded';
		
		}

	),
	array( 'db' => 'commentcode',  'dt' => 9,
		'formatter' => function( $d, $row ) {
			$conn = mysqli_connect('localhost','root','','sahamid');
			$sql = 'SELECT * FROM `salescasecomments` INNER JOIN salescase ON 
			salescasecomments.salescaseref=salescase.salescaseref 
			where 
			salescase.salesman=salescasecomments.username AND
			commentcode = "'.$d.'"';
			$result = mysqli_query($conn,$sql);
			$comment='';
			while($myrow = mysqli_fetch_assoc($result))
			{
				$comment.= $myrow['comment'].' '.$myrow['time'];
	
	
	
			}

			return $comment;
		}

	),
	
		
	array(
		'db'        => 'podate',
		'dt'        => 10,
		'formatter' => function( $d, $row ) {
			if ($d == '0000-00-00 00:00:00')
				return 'Not Uploaded';
			else
			return 'Uploaded';
		}
	),

		array(
		'db'        => 'podate',
		'dt'        => 11,
		'formatter' => function( $d, $row ) {
	if ($d == '0000-00-00 00:00:00')
				return '';
			else
			return date("d/m/Y", strtotime($d));
		}
	),
	array(
		'db'        => 'salescaseref',
		'dt'        => 12,
		'formatter' => function( $d, $row ) {
			$conn = mysqli_connect('localhost','root','','sahamid');
$sql = "SELECT quotationvalue".$_SESSION['UserID'].".value as value FROM quotationvalue".$_SESSION['UserID']."
INNER JOIN salescase on
 quotationvalue".$_SESSION['UserID'].".salescaseref = salescase.salescaseref
			where 
			quotationvalue".$_SESSION['UserID'].".salescaseref= '".$d."'
			
			and salescase.podate!= '0000-00-00 00:00:00'"
			
;
			$result = mysqli_query($conn,$sql);
			$myrow = mysqli_fetch_assoc($result);
			
				return $myrow['value'];
			
		}
	),
	//dcno
	array(
		'db'        => 'salescaseref',
		'dt'        => 13,
		'formatter' => function( $d, $row ) {
			$conn = mysqli_connect('localhost','root','','sahamid');
	$sql = 'SELECT * FROM dc
			where 
			salescaseref= "'.$d.'"';
			
			$dcno='';
			$result = mysqli_query($conn,$sql);
			while($myrow = mysqli_fetch_assoc($result))
				$dcno.= $myrow['dispatchid'].'<br>';
				return $dcno;
			
		}
	),
	//dc date
	array(
		'db'        => 'salescaseref',
		'dt'        => 14,
		'formatter' => function( $d, $row ) {
			$conn = mysqli_connect('localhost','root','','sahamid');
	$sql = 'SELECT * FROM dc
			where 
			salescaseref= "'.$d.'"';
			
			$dcno='';
			$result = mysqli_query($conn,$sql);
			while($myrow = mysqli_fetch_assoc($result))
				$dcno.= date("d/m/Y", strtotime($myrow['despatchdate'])).'<br>';
				return $dcno;
			
		}
	),
	
		array( 'db' => 'commentcode',  'dt' => 15,
		'formatter' => function( $d, $row ) {
			$conn = mysqli_connect('localhost','root','','sahamid');
			$sql = 'SELECT * FROM `salescasecomments` INNER JOIN salescase ON 
			salescasecomments.salescaseref=salescase.salescaseref 
			where 
			salescase.salesman!=salescasecomments.username AND
			commentcode = "'.$d.'"';
			$result = mysqli_query($conn,$sql);
			$comment='';
			while($myrow = mysqli_fetch_assoc($result))
			{
				$comment.= $myrow['comment'].' '.$myrow['time'];
	
	
	
			}

			return $comment;
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
if (1)
{echo json_encode(
	SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns)
);}




