<?php

	$PathPrefix='../../';
	
	include('../misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$response = [];
	
	if(!validHeaders()){

		$response = [
			'status' => 'error',
			'message' => 'Refresh or ReLogin Required.'
		];

		echo json_encode($response);
		return;		

	}

	if(!isset($_POST['salescaseref']) || !isset($_POST['orderno'])){

		$response = [
			'status' => 'error',
			'message' => 'Missing Parameters.'
		];

		echo json_encode($response);
		return;	

	}

	$salescaseref = $_POST['salescaseref'];
	$orderno = $_POST['orderno'];

	$db = createDBConnection();

	//Sales Order
	$SQL = "SELECT * FROM invoice WHERE invoiceno='".$orderno."' AND inprogress=1";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		$response = [
			'status' => 'error',
			'message' => 'Invoice not found.'
		];

		echo json_encode($response);
		return;	

	}

	$SQL = "SELECT quantity FROM invoiceoptions WHERE invoiceno='".$orderno."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){

		echo json_encode([
				'status' => 'error',
				'message' => 'No Line or Option added'
			]);
		return;
	
	}

	while($row = mysqli_fetch_assoc($res)){

		if($row['quantity'] == 0){
			
			echo json_encode([
				'status' => 'error',
				'message' => 'Option with 0 quantity found'
			]);
			return;
		
		}

	}

	$SQL = "SELECT quantity FROM invoicedetails WHERE invoiceno='".$orderno."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){

		echo json_encode([
				'status' => 'error',
				'message' => 'Option with 0 items found'
			]);
		return;
	
	}

	while($row = mysqli_fetch_assoc($res)){

		if($row['quantity'] == 0){

			echo json_encode([
				'status' => 'error',
				'message' => 'Item with 0 quantity found'
			]);
			return;
			
		}

	}

	$SQL = "SELECT * FROM invoiceoptions WHERE invoiceno='".$orderno."'";
	$res = mysqli_query($db, $SQL);

	$isCompleted = true;

	while($row = mysqli_fetch_assoc($res)){

		$SQL = "UPDATE dcoptions SET qtyinvoiced = qtyinvoiced+".$row['quantity']."
				WHERE orderno='".$row['orderno']."'
				AND lineno='".$row['lineno']."'
				AND optionno='".$row['optionno']."'";
		mysqli_query($db, $SQL);

		// $SQL = "SELECT quantity,qtyinvoiced FROM dcoptions
		// 		WHERE orderno='".$row['orderno']."'
		// 		AND lineno='".$row['lineno']."'
		// 		AND optionno='".$row['optionno']."'";
		// $r = mysqli_query($db, $SQL);
		// $a = mysqli_fetch_assoc($r);

		// if($a['quantity'] > $a['qtyinvoiced'])
		// 	$isCompleted = false;

	}
	//Transaction begin
	DB_Txn_Begin($db);

	//Redo Save Invoice Completed Check

	$SQL = "SELECT groupid FROM invoice WHERE invoiceno='".$orderno."'";
	$grp = mysqli_fetch_assoc(mysqli_query($db, $SQL))['groupid'];

	$SQL = "SELECT dcnos FROM dcgroups WHERE id='".$grp."'";
	$nos = mysqli_fetch_assoc(mysqli_query($db,$SQL))['dcnos'];

	foreach(explode(",",$nos) as $dc){

		if(trim($dc) == '')
			continue;

		$SQL = "SELECT * FROM dcoptions WHERE orderno='".$dc."'
				AND quantity-qtyinvoiced > 0";

		if(mysqli_num_rows(mysqli_query($db,$SQL)) > 0){
			$isCompleted = false;
			break;
		}

	}

	if($isCompleted){

		$SQL = "UPDATE dcgroups SET status=1 WHERE id='".$grp."'";
		mysqli_query($db, $SQL);

	}

	//Redo Save Invoice Completed Check Done

	$SQL = "SELECT * FROM invoicedetails WHERE invoiceno='".$orderno."'";
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		$SQL = "UPDATE dcdetails SET qtyinvoiced = qtyinvoiced+".$row['quantity']."
				WHERE orderno='".$row['orderno']."'
				AND orderlineno='".$row['orderlineno']."'
				AND lineoptionno='".$row['lineoptionno']."'
				AND stkcode='".$row['stkcode']."'";
		mysqli_query($db, $SQL);

	}

	$SQL = "UPDATE invoice SET inprogress='0',
			invoicedate='".date("Y-m-d")."' 
			WHERE invoiceno='".$orderno."'
			AND salescaseref='".$salescaseref."'";

	$result = mysqli_query($db, $SQL);

	if(!$result){

		$response = [
			'status' => 'error',
			'message' => 'Update Failed.'
		];

		echo json_encode($response);
		return;	

	}

	//ACCOUNTS

	//Calculating invoice value and period number

	$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$sqlinvoicevalue="SELECT invoice.salescaseref, invoicedetails.invoiceno,
 	SUM(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*
 	invoicedetails.quantity*invoiceoptions.quantity ) AS value
 	FROM invoicedetails 
 	INNER JOIN invoiceoptions 
 	ON (invoicedetails.invoiceno = invoiceoptions.invoiceno 
 		AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
 	INNER JOIN invoice ON invoice.invoiceno = invoicedetails.invoiceno 
 	WHERE invoicedetails.invoiceoptionno = 0 
 	AND invoiceoptions.invoiceoptionno = 0 
 	AND invoice.invoiceno='".$orderno ."'";

 	$resultsqlinvoicevalue=DB_query($sqlinvoicevalue,$db);
 	$rowinvoicevalue=DB_fetch_array($resultsqlinvoicevalue);
 	$rowinvoiceval=$rowinvoicevalue['value'];
 	$SQL="SELECT gst,services,invoicesdate FROM invoice WHERE invoiceno='". $orderno . "'";
 	$result=DB_query($SQL,$db);
 	$row=DB_fetch_array($result);
	// print_r($row);

 	$recievable  = 0;
 	$sales 		 = 0;

	if($row['gst'] == '' AND strtotime($row['invoicesdate']) < strtotime('2023-02-14')){
		$recievable = $rowinvoicevalue['value'];
		$sales = $rowinvoicevalue['value'];
		$tax=0;
	}
	elseif($row['gst'] == '' AND strtotime($row['invoicesdate']) >= strtotime('2023-02-14')){
		$recievable = $rowinvoicevalue['value'];
		$sales = $rowinvoicevalue['value'];
		$tax=0;
	}


 	if ($row['gst']=='exclusive' AND $row['services']==1 AND strtotime($row['invoicesdate']) < strtotime('2023-02-14')) {
 		$rowinvoiceval  = $rowinvoicevalue['value'];
	 	$recievable 	= $rowinvoiceval*1.16;
	 	$sales 			= $rowinvoicevalue['value'];
	 	$tax 			= $recievable/1.16*0.16;
 	}
 	elseif ($row['gst']=='exclusive' AND $row['services']==1 AND strtotime($row['invoicesdate']) >= strtotime('2023-02-14')) {
 		$rowinvoiceval  = $rowinvoicevalue['value'];
	 	$recievable 	= $rowinvoiceval*1.16;
	 	$sales 			= $rowinvoicevalue['value'];
	 	$tax 			= $recievable/1.16*0.16;
 	}


  	if ($row['gst']=='exclusive' AND $row['services']==0 AND strtotime($row['invoicesdate']) < strtotime('2023-02-14')) {	 
 		$rowinvoiceval  = $rowinvoicevalue['value'];
 		$recievable 	= $rowinvoiceval*1.17;
	 	$sales 			= $rowinvoicevalue['value'];
	 	$tax 			= $recievable/1.17*0.17;
 	}
  	elseif ($row['gst']=='exclusive' AND $row['services']==0 AND strtotime($row['invoicesdate']) >= strtotime('2023-02-14')) {	 
 		$rowinvoiceval  = $rowinvoicevalue['value'];
 		$recievable 	= $rowinvoiceval*1.18;
	 	$sales 			= $rowinvoicevalue['value'];
	 	$tax 			= $recievable/1.18*0.18;
 	}


 	if ($row['gst']=='inclusive' AND $row['services']==1 AND strtotime($row['invoicesdate']) < strtotime('2023-02-14')) {
 		$rowinvoiceval  = $rowinvoicevalue['value'];
 		$recievable  	= $rowinvoicevalue['value'];
	 	$sales  		= $rowinvoiceval/1.16;
	 	$tax 			= $sales*0.16;
 	}
 	elseif ($row['gst']=='inclusive' AND $row['services']==1 AND strtotime($row['invoicesdate']) >= strtotime('2023-02-14')) {
 		$rowinvoiceval  = $rowinvoicevalue['value'];
 		$recievable  	= $rowinvoicevalue['value'];
	 	$sales  		= $rowinvoiceval/1.16;
	 	$tax 			= $sales*0.16;
 	}


  	if ($row['gst']=='inclusive' AND $row['services']==0 AND strtotime($row['invoicesdate']) < strtotime('2023-02-14')) {
 		$rowinvoiceval  = $rowinvoicevalue['value'];
 		$recievable  	= $rowinvoicevalue['value'];
	 	$sales  		= $rowinvoiceval/1.17;
	 	$tax 			= $sales*0.17;
 	}
  	if ($row['gst']=='inclusive' AND $row['services']==0 AND strtotime($row['invoicesdate']) >= strtotime('2023-02-14')) {
 		$rowinvoiceval  = $rowinvoicevalue['value'];
 		$recievable  	= $rowinvoicevalue['value'];
	 	$sales  		= $rowinvoiceval/1.18;
	 	$tax 			= $sales*0.18;
 	}
//-------------------------------------------------------


// Inserting debtortrans
	$SQL = "SELECT debtorno,branchcode FROM invoice WHERE invoiceno='".$orderno."'";
	$ind = mysqli_fetch_assoc(mysqli_query($db, $SQL));

	$SQL = "INSERT INTO debtortrans (transno,
									type,
									debtorno,
									branchcode,
									trandate,
									inputdate,
									prd,
									reference,
									tpe,
									order_,
									ovamount,
									WHTamt,
									GSTamt,
									GSTtotalamt,
									ovgst,
									ovfreight,
									rate,
									invtext,
									shipvia,
									consignment,
									packages,
									salesperson,
									processed )
								VALUES (
									'". $orderno . "',
									10,
									'" . $ind['debtorno'] . "',
									'" . $ind['branchcode'] . "',
									'" . date('Y-m-d H-i-s') . "',
									'" . date('Y-m-d H-i-s') . "',
									'" . $PeriodNo . "',
									'',
									'',
									'',
									'" . $recievable. "',
									'".($recievable*0.045)."',
									'" . ($tax*0.2) . "',
									'" . ($tax) . "',	
									'',
									'',
									'1',
									'',
									'',
									'',
									'',
									'" . $_SESSION['Items'.$identifier]->SalesPerson . "',
									'-1')";

	$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
	$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
 	$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

//Accounts Sales
	$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount
								) 
			VALUES (
					10,
					'". $orderno . "',
					'" . date('Y-m-d H-i-s') . "',
					'" . $PeriodNo . "',
					1,
					'Invoice No " . $orderno . "',
					'" . (-1*$sales) . "'
					)";
	if( !$Result = DB_query($SQL,$db) ) {
		$Error = _('Could not update General Ledger');
	}
// Accounts Recievable
	$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
						) VALUES (
							10,
							'". $orderno . "',
							'" . date('Y-m-d H-i-s') . "',
							'" . $PeriodNo . "',
							1100,
							'Invoice No " . $orderno . "','".$recievable. "')";
	if( !DB_query($SQL,$db) ) {
		$Error = _('Could not update General Ledger');
	}

	if($tax > 0){

		// GST Recoverable
		$SQL = "INSERT INTO gltrans (
								type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount
							) VALUES (
								10,
								'". $orderno . "',
								'" . date('Y-m-d H-i-s') . "',
								'" . $PeriodNo . "',
								2310,
								'GST for Invoice No " . $orderno . "','".(-1*$tax). "')";
		if( !DB_query($SQL,$db) ) {
			$Error = _('Could not update General Ledger');
		}

	}


	//--------SUP

	//Transaction Commit
	DB_Txn_Commit($db);
	closeDBConnection($db);

	if(isset($Error)){

		echo json_encode([
			'status' => 'error',
			'message' => 'Transaction Failed.'
		]);
		return;
	}
	
	echo json_encode([
			'status' => 'success',
			'message' => 'Updated Successfully.'
		]);

	

?>