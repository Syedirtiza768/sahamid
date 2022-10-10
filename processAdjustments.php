<?php 

	include('includes/session.inc');	
	include('includes/SQL_CommonFunctions.inc');
	include('invoice/misc.php');

	$db = createDBConnection();

	$i=0;

	//3 - WHT 
	//7 - GSTwithheld

	DB_Txn_Begin($db);
	while(isset($_POST['id'.$i])){

		$id 		= $_POST['id'.$i];
		$oWHT 		= $_POST['oWHT'.$i];
		$oGST 		= $_POST['oGST'.$i];
		$WHT  		= $_POST['WHT'.$i] ? 1:0;
		$GST 		= $_POST['GST'.$i] ? 1:0;
		$WHTamt 	= $_POST['WHTamt'.$i];
		$GSTamt 	= $_POST['GSTamt'.$i];
		$settled   	= $_POST['settled'.$i] ? 1:0;
		$invoice   	= $_POST['invoiceid'.$i];
		$transdate 	= $_POST['transdate'.$i];
		$gstTotal 	= $_POST['gsttotalamt'.$i];
		$paid 		= $_POST['paid'.$i];
		$total 		= $_POST['total'.$i];

		$percent	= ($paid / $total) * 100;

		$transdate = (date('d/m/Y',strtotime(explode(' ',$transdate)[0]))); 

		$recoverableEntry = $gstTotal;

		$receiveableEntry = ($paid);

		$PeriodNo = GetPeriod($transdate, $db);

		$transdate = FormatDateForSQL($transdate);

		$SQL = "UPDATE debtortrans SET 
				settled='".$settled."',
				WHT='".$WHT."',
				GSTwithhold='".$GST."',
				WHTamt='".$WHTamt."',
				GSTamt='".$GSTamt."'
				WHERE id=".$id;
		
		DB_query($SQL,$db);

		if($GST == 1 && $oGST == 1){
			
			$SQL = "UPDATE gltrans SET 
					amount='".$GSTamt."'
					WHERE type=10
					AND periodno='".$PeriodNo."'
					AND account=7
					AND narrative='GSTwitheld for Invoice No: ".$invoice."'";
			if( !$Result = DB_query($SQL,$db) ) {
				$Error = _('Could not update exchange difference in General Ledger');
			}

			$receiveableEntry += $GSTamt;

		}

		if($WHT == 1 && $oWHT == 1){
			
			$SQL = "UPDATE gltrans SET 
					amount='".$WHTamt."'
					WHERE type=10
					AND periodno='".$PeriodNo."'
					AND account=3
					AND narrative='WHT for Invoice No: ".$invoice."'";
			if( !$Result = DB_query($SQL,$db) ) {
				$Error = _('Could not update exchange difference in General Ledger');
			}

			$receiveableEntry += $WHTamt;

		}

		if($WHT == 1 && $oWHT == 0){
			
			$SQL = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount
					) VALUES (
						'10',
						'" . $_POST['invoiceid'.$i] . "',
						'" . $transdate . "',
						'" . $PeriodNo . "',
						'3',
						'WHT for Invoice No: ".$invoice."',
						'".$WHTamt."'
					)";
			if( !$Result = DB_query($SQL,$db) ) {
				$Error = _('Could not update exchange difference in General Ledger');
			}

			$receiveableEntry += $WHTamt;

		}

		if($GST == 1 && $oGST == 0){
			
			$SQL = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount
					) VALUES (
						'10',
						'" . $invoice . "',
						'" . $transdate . "',
						'" . $PeriodNo . "',
						'7',
						'GSTwitheld for Invoice No: ".$invoice."',
						'".$GSTamt."'
					)";
			if( !$Result = DB_query($SQL,$db) ) {
				$Error = _('Could not update exchange difference in General Ledger');
			}

			$receiveableEntry += $GSTamt;

		}

		if($settled == 1){

			$SQL = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount
					) VALUES (
						'10',
						'".$invoice."',
						'".$transdate."',
						'". $PeriodNo."',
						'2310',
						'Against Invoice (".$invoice.")',
						'" . ($recoverableEntry) . "'
					)";

			if( !$Result = DB_query($SQL,$db) ) {
				$Error = _('Could not update exchange difference in General Ledger');
			}

			$SQL = "INSERT INTO gltrans (
						type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount
					) VALUES (
						'10',
						'".$invoice."',
						'".$transdate."',
						'". $PeriodNo."',
						'1100',
						'Against Invoice (".$invoice.")',
						'" . (-1 * $receiveableEntry) . "'
					)";

			if( !$Result = DB_query($SQL,$db) ) {
				$Error = _('Could not update exchange difference in General Ledger');
			}

		}

		$i++;

	}

	if (empty($Error) ) {
			$Result = DB_Txn_Commit($db);
			header("Location: ".$_SERVER['HTTP_REFERER']);
	} else {
			$Result = DB_Txn_Rollback($db);
			prnMsg($Error,'error');
	}