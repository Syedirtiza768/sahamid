<?php

	$PathPrefix='../';

	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');

	$invoiceno 	= $_GET['invoiceno'];
	$error 		= null; 

	if(!isset($invoiceno) || trim($invoiceno) == ""){
		echo json_encode([
			'status' => 'error',
			'message' => 'missing parameters'
			]);
		return;
	}

	//check if the invoice is already returned
	$SQL = "SELECT * FROM invoice WHERE invoiceno='".$invoiceno."'";
	$res = mysqli_query($db, $SQL);
	$inv = mysqli_fetch_assoc($res);

	if(!$inv || $inv['inprogress'] == 1 || $inv['returned'] == 1){

		echo json_encode([
			'status' => 'error',
			'message' => 'Invoice InProgress or Already reversed!!!'
		]);
		return;

	}

	//Step 1
	$SQL = "SELECT * FROM debtortrans WHERE type=10 AND transno='".$invoiceno."'";
	$res = mysqli_query($db, $SQL);
	$trans = mysqli_fetch_assoc($res);

	if(!$trans || $trans['alloc'] != 0 || $trans['reversed'] == 1){

		echo json_encode([
			'status' => 'error',
			'message' => 'Transaction Not Found or Invoice already paid or Already reversed!!!'
		]);
		return;

	}

	$SQL = "UPDATE debtortrans SET settled=1,reversed=1 WHERE id='".$trans['id']."'";
	DB_Query($SQL, $db);

	$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

	$SQL = "INSERT INTO debtortrans (transno,type,debtorno,branchcode,trandate,inputdate,prd,ovamount,rate,salesperson,processed)
			VALUES ('".$trans['transno']."',13,'".$trans['debtorno']."','".$trans['branchcode']."','".date('Y-m-d H-i-s')."',
					'".date('Y-m-d H-i-s')."','".$PeriodNo."','".(-1*$trans['ovamount'])."','1','".$trans['salesperson']."','-1')";
	DB_Query($SQL, $db);

	$SQL = "SELECT * FROM gltrans WHERE type=10 AND typeno='".$invoiceno."'";
	$res = DB_Query($SQL,$db);

	while($row = mysqli_fetch_assoc($res)){

		$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
				VALUES (10,'".$invoiceno."','".date('Y-m-d H-i-s')."','".$PeriodNo."',
						'".$row['account']."','"."RE ".$row['narrative']."','".(-1*$row['amount'])."')";
		DB_Query($SQL,$db);

	}

	$SQL = "UPDATE invoice SET returned=1 WHERE invoiceno='".$invoiceno."'";
	DB_Query($SQL,$db);

	$SQL = "UPDATE dcgroups SET status=0 WHERE id='".$inv['groupid']."'";
	DB_Query($SQL,$db);

    $SQL = "UPDATE dcs SET invoicegroupid=NULL WHERE invoicegroupid='".$inv['groupid']."'";
    DB_Query($SQL,$db);

    $SQL = "DELETE FROM dcgroups WHERE id='".$inv['groupid']."'";
    DB_Query($SQL,$db);

	$SQL = "SELECT * FROM invoiceoptions WHERE invoiceno='".$invoiceno."'";
	$res = DB_Query($SQL,$db);

	while($row = mysqli_fetch_assoc($res)){

		$SQL = "UPDATE dcoptions SET qtyinvoiced=(qtyinvoiced - ".$row['quantity'].")
				WHERE orderno='".$row['orderno']."'
				AND lineno='".$row['lineno']."'
				AND optionno='".$row['optionno']."'";
		DB_Query($SQL, $db); 

	}

	echo json_encode([
		'status' => 'success',
		'message' => 'Yo'
	]);
	return;
