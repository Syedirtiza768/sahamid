<?php

	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$mpino 	= $_GET['mpino'];
	$error 		= null;
    $SQL = "SELECT * FROM bazar_parchi INNER JOIN supptrans WHERE bazar_parchi.transno=supptrans.transno
                AND bazar_parchi.type=601
                 
                
                AND bazar_parchi.transno = $mpino
                AND supptrans.processed != -1";

    $res = mysqli_query($db, $SQL);

    if(mysqli_num_rows($res)>0){
        echo json_encode([
            'status' => 'error',
            'message' => 'Allocations already Made'
        ]);
        return;
    }


    if(!isset($mpino) || trim($mpino) == ""){
            echo json_encode([
                'status' => 'error',
                'message' => 'missing parameters'
                ]);
            return;
        }
    $SQL = "SELECT * FROM supptrans WHERE type=601 AND transno='".$mpino."' ORDER BY supptrans.trandate DESC";
    $res = mysqli_query($db, $SQL);
    $trans = mysqli_fetch_assoc($res);


    $PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
    $SQL = "UPDATE supptrans SET reversed=1,updated_by='".$_SESSION['UsersRealName']."' WHERE transno='".$mpino."' AND type=601";
    DB_Query($SQL, $db);
    $SQL = "INSERT INTO supptrans (transno,type,supplierno,trandate,inputdate,ovamount,rate,updated_by,processed)
                VALUES ('".$trans['transno']."',14,'".$trans['supplierno']."','".date('Y-m-d H-i-s')."',
                        '".date('Y-m-d H-i-s')."','".(-1*$trans['ovamount'])."','1','".$_SESSION['UsersRealName']."','-1')";
    DB_Query($SQL, $db);

	$SQL = "SELECT * FROM gltrans WHERE type=601 AND typeno='".$mpino."' AND ((gltrans.account = 2100 AND gltrans.amount<0) OR (gltrans.account = 4 AND gltrans.amount>0))
	AND (gltrans.narrative NOT LIKE '%RE%' OR  gltrans.narrative NOT LIKE '%Reverse%')
	ORDER BY counterindex DESC LIMIT 0,2";
	$res = DB_Query($SQL,$db);

	while($row = mysqli_fetch_assoc($res)){

		$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount) 
				VALUES (601,'".$mpino."','".date('Y-m-d H-i-s')."','".$PeriodNo."',
						'".$row['account']."','"."RE ".$row['narrative']."','".(-1*$row['amount'])."')";
		DB_Query($SQL,$db);

	}

	$SQL = "UPDATE bazar_parchi SET returned=1 WHERE transno='".$mpino."'";
	DB_Query($SQL,$db);
    $SQL = "UPDATE bazar_parchi SET inprogress=1 AND settled=0 WHERE transno='".$mpino."'";
    DB_Query($SQL,$db);
/*
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

	}*/

	echo json_encode([
		'status' => 'success',
		'message' => 'Yo'
	]);
	return;
