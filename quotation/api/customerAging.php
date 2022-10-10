<?php
	$AllowAnyone=true;
	$PathPrefix='../../';

	include('../../quotation/misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	$debtorno=$_GET['debtorno'];
	$SQL = "SELECT * FROM debtorsmaster WHERE debtorno='$debtorno'";
	$res=mysqli_query($db,$SQL);
	$debtorinfo=mysqli_fetch_assoc($res);
	$dueDays=$debtorinfo['dueDays'];
	$paymentExpected=$debtorinfo['paymentExpected'];
	$creditlimit=$debtorinfo['creditlimit'];

	$SQL = 'SELECT SUM(
					CASE WHEN GSTwithhold = 0 AND WHT = 0 
						THEN ovamount - alloc
					WHEN GSTwithhold = 0 AND WHT = 1 
						THEN ovamount - alloc - WHTamt
					WHEN GSTwithhold = 1 AND WHT = 0 
						THEN ovamount - alloc - GSTamt
					WHEN GSTwithhold = 1 AND WHT = 1 
						THEN ovamount - alloc - GSTamt - WHTamt
					END
				) AS due 
			FROM debtortrans 
			INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
			WHERE debtortrans.debtorno="'.$debtorno.'" 
			AND debtortrans.type=10 
			AND debtortrans.settled=0 
			AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= '.$dueDays; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);
	$due=mysqli_fetch_assoc($res)['due'];
	echo "$due";
	//echo "Customer Payment Due Days: $dueDays, Overdue "




?>