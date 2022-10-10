<?php
$PathPrefix = "../";
include('../includes/session.inc');
include('../includes/SQL_CommonFunctions.inc');


$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

$transno=$_GET['transno'];
$reason=$_GET['reason'];
DB_Txn_Begin($db);
$SQL="SELECT * FROM `supptrans` INNER JOIN `banktrans` ON (banktrans.transno=supptrans.transno AND banktrans.type=supptrans.type)
WHERE supptrans.transno LIKE '$transno' AND banktrans.type=22 ORDER BY `ovamount` ASC";
$row=mysqli_fetch_assoc(mysqli_query($db,$SQL));

$account=$row['bankact'];
$chequeno=$row['ref'];
$chequeamount=$row['ovamount'];
$id=$row['id'];
$transno=$row['transno'];
$type=$row['type'];
$SQL="SELECT * FROM `suppallocs` WHERE `transid_allocfrom` = $id";
DB_query($SQL, $db);
//check for old entries
$SQLQ="SELECT * FROM gltrans  WHERE typeno=$transno AND type=22";

$resQ=mysqli_query($db,$SQLQ);
$count=0;
while($rowc=mysqli_fetch_assoc($resQ))
{
    //print_r($rowc);
    $count++;
}
if($count >= 1){
    $SQLB = "SELECT * FROM supptrans INNER JOIN suppallocs ON supptrans.id=suppallocs.transid_allocto 
WHERE supptrans.id IN( SELECT transid_allocto 
FROM `suppallocs` WHERE `transid_allocfrom` = $id ORDER BY `id` ASC)
AND `transid_allocfrom` = $id";
    $resultB = mysqli_query($db, $SQLB);

    while ($rowB = mysqli_fetch_assoc($resultB)) {
        $type = $rowB['type'];
        $typeno = $rowB['transno'];
        $amount = $rowB['amt'];
        $ovamount = $rowB['ovamount'];
        $reverseHistory = 'Reversed Cheque:<br/>' . $chequeno . '<br/>Reversed Alloc:<br/>' . $amount
            . '<br/>Reason:<br/>' . $reason . '<br/>Cheque Amount:<br/>' . -1 * $chequeamount;

        $SQLC = "UPDATE supptrans SET alloc=alloc-$amount,
                  reverseHistory='$reverseHistory',
                  settled=0
                  WHERE transno=$typeno AND type=$type";
        DB_query($SQLC, $db);
        $SQLD = "UPDATE supptrans SET processed=-1 
                  WHERE alloc=0";
        DB_query($SQLD, $db);
    }
        $SQLA = "SELECT * FROM supptrans WHERE id = $id";
    $resultA = mysqli_query($db, $SQLA);
    $rowA = mysqli_fetch_assoc($resultA);
    $type = $rowA['type'];
    $typeno = $rowA['transno'];
    $amount = $rowA['amt'];
    $ovamount = $rowA['ovamount'];
    $reverseHistory = 'Reversed Cheque:<br/>' . $chequeno . '<br/>Reversed Alloc:<br/>' . $amount
        . '<br/>Reason:<br/>' . $reason . '<br/>Cheque Amount:<br/>' . -1 * $chequeamount;

    $SQL = "UPDATE supptrans SET alloc=0,
                  reverseHistory='$reverseHistory',
                  settled=0
                  WHERE transno=$typeno AND type=$type";
    DB_query($SQL, $db);
    $SQL = "UPDATE supptrans SET processed=-1 
                  WHERE alloc=0";
    DB_query($SQL, $db);

    $SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
						) VALUES (
							'$type',
							'" . $typeno . "',
							'" . date('Y-m-d') . "',
							'" . $PeriodNo . "',
							'$account',
							'Reverse Entry For Payment (" . $_GET['transno'] . ")',
							'" . ($ovamount) . "'
						)";
    DB_query($SQL, $db);

        $SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
						) VALUES (
							'$type',
							'" . $typeno . "',
							'" . date('Y-m-d') . "',
							'" . $PeriodNo . "',
							'2100',
							'Reverse Entry For Payment (" . $_GET['transno'] . ")',
							'" . -1 * $ovamount . "'
						)";
        DB_query($SQL, $db);


}
else {
    $SQLZ = "SELECT * FROM supptrans INNER JOIN suppallocs ON supptrans.id=suppallocs.transid_allocto 
WHERE supptrans.id IN( SELECT transid_allocto 
FROM `suppallocs` WHERE `transid_allocfrom` = $id ORDER BY `id` ASC)
AND `transid_allocfrom` = $id";
    $resultZ = mysqli_query($db, $SQLZ);

    while ($rowZ = mysqli_fetch_assoc($resultZ)) {
        $type = $rowZ['type'];
        $typeno = $rowZ['transno'];
        $amount = $rowZ['amt'];
        $ovamount = $rowZ['ovamount'];
        $reverseHistory = 'Reversed Cheque:<br/>' . $chequeno . '<br/>Reversed Alloc:<br/>' . $amount
            . '<br/>Reason:<br/>' . $reason . '<br/>Cheque Amount:<br/>' . -1 * $chequeamount;

        $SQLX= "UPDATE supptrans SET alloc=alloc-$amount,
                  reverseHistory='$reverseHistory',
                  settled=0
                  WHERE transno=$typeno AND type=$type";
        DB_query($SQLX, $db);
        $SQLP = "UPDATE supptrans SET processed=-1 
                  WHERE alloc=0";
        DB_query($SQLP, $db);


        $SQLM = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
						) VALUES (
							'$type',
							'" . $typeno . "',
							'" . date('Y-m-d') . "',
							'" . $PeriodNo . "',
							'$account',
							'Reverse Entry For Payment (" . $_GET['transno'] . ")',
							'" . ($amount - $row['GSTtotalamt']) . "'
						)";
        DB_query($SQLM, $db);

            $SQLN = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount
						) VALUES (
							'$type',
							'" . $typeno . "',
							'" . date('Y-m-d') . "',
							'" . $PeriodNo . "',
							'2100',
							'Reverse Entry For Payment (" . $_GET['transno'] . ")',
							'" . -1 * ($amount - $row['GSTtotalamt']) . "'
						)";
            DB_query($SQLN, $db);


    }
}

$SQL="DELETE 
FROM `suppallocs` WHERE `transid_allocfrom` = $id";
DB_query($SQL, $db);
$SQL="
INSERT INTO reversedallocationhistoryvendor(client,transno,chequeDate,chequeno,amount,chequefilepath,chequedepositfilepath)
 SELECT suppliers.suppname,
                supptrans.transno,
				supptrans.trandate,
				supptrans.transtext,
				ROUND(ABS(supptrans.ovamount)) as amount,
				chequefilepath as cheque,
				chequedepositfilepath as deposit
				FROM supptrans
				INNER JOIN suppliers ON suppliers.supplierid = supptrans.supplierno
				WHERE supptrans.type = 22 AND 
				supptrans.transno = $transno";
DB_query($SQL, $db);
$UserID=$_SESSION['UsersRealName'];
$SQL="UPDATE reversedallocationhistoryvendor SET 
                  reason='$reason',
                  UserID='$UserID',
                  revdate=CURDATE() 
                  WHERE transno=$transno";
DB_query($SQL, $db);


$SQL="DELETE FROM `supptrans` WHERE `transno` LIKE '$transno' AND type=22";
DB_query($SQL, $db);
$SQL="DELETE FROM `banktrans` WHERE `transno` = $transno AND type=22";
DB_query($SQL, $db);

// Reverse GL Entries will be made [reverse entry for the receipt and in case of settled reverse entry for gst recoverable and account receivabble]

// Cust allocations will be deleted
// Debtor Trans Entries for invoice, shopsale, MPO will be adjusted for allocaated amount
// Debtor Trans for receipt will be deleted
// Bank transaction for receipt will be deleted
DB_Txn_Commit($db);

echo json_encode([

    'status' => 'success',
    'message' => 'Saved Successfully.'

]);
?>