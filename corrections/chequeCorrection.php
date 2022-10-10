<?php
$PathPrefix = "../";
include('../includes/session.inc');
include('../includes/SQL_CommonFunctions.inc');
$AllowAnyone = true;
$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

$transno=$_GET['transno'];
$reason=$_GET['reason'];
DB_Txn_Begin($db);
$SQL="SELECT * FROM `debtortrans` WHERE `transno` LIKE '$transno' AND type=12 ORDER BY `ovamount` ASC";
$row=mysqli_fetch_assoc(mysqli_query($db,$SQL));
//print_r($row);
$account=$row['bankaccount'];
$chequeno=$row['invtext'];
$chequeamount=$row['ovamount'];
$id=$row['id'];
$transno=$row['transno'];
$type=$row['type'];
$SQL="SELECT * 
FROM `custallocns` WHERE `transid_allocfrom` = $id";
DB_query($SQL, $db);
$SQL="SELECT * FROM debtortrans INNER JOIN custallocns ON debtortrans.id=custallocns.transid_allocto 
WHERE debtortrans.id IN( SELECT transid_allocto 
FROM `custallocns` WHERE `transid_allocfrom` = $id ORDER BY `id` ASC)
AND `transid_allocfrom` = $id";
$result=mysqli_query($db,$SQL);

while($row=mysqli_fetch_assoc($result)){
    $type=$row['type'];
    $typeno=$row['transno'];
    $amount=$row['amt'];
    $ovamount=$row['ovamount'];
    $reverseHistory='Reversed Cheque:<br/>'.$chequeno.'<br/>Reversed Alloc:<br/>'.$amount
    .'<br/>Reason:<br/>'.$reason.'<br/>Cheque Amount:<br/>'.-1*$chequeamount;

    $SQL="UPDATE debtortrans SET alloc=alloc-$amount,
                  reverseHistory='$reverseHistory',
                  settled=0,
				WHT=0,
				GSTwithhold=0,
				WHTamt=0,
				GSTamt=0
                  WHERE transno=$typeno AND type=$type";
    DB_query($SQL, $db);
    $SQL="UPDATE debtortrans SET processed=-1 
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
							'Reverse Entry For Receipt (".$_GET['transno'].")',
							'" . -1*($amount-$row['GSTtotalamt']) . "'
						)";
    DB_query($SQL, $db);
    if($row['settled']==1)
    {
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
							'1100',
							'Reverse Entry For Receipt (".$_GET['transno'].")',
							'" . $ovamount . "'
						)";
        DB_query($SQL, $db);

        if($row['GSTtotalamt']>0){
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
							'2310',
							'Reverse Entry For Receipt (".$_GET['transno'].")',
							'" . -1*$row['GSTtotalamt'] . "'
						)";
            DB_query($SQL, $db);
        }

        if($row['WHTamt']>0){
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
							'3',
							'Reverse Entry For Receipt (".$_GET['transno'].")',
							'" . -1*$row['WHTamt'] . "'
						)";
            DB_query($SQL, $db);
        }
    }
}

$SQL="DELETE 
FROM `custallocns` WHERE `transid_allocfrom` = $id";
DB_query($SQL, $db);
$SQL="
INSERT INTO reversedallocationhistory(client,transno,chequeDate,chequeno,amount,chequefilepath,chequedepositfilepath)
 SELECT debtorsmaster.name,
                debtortrans.transno,
				debtortrans.trandate,
				debtortrans.invtext,
				ROUND(ABS(debtortrans.ovamount)) as amount,
				chequefilepath as cheque,
				chequedepositfilepath as deposit
				FROM debtortrans
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				WHERE debtortrans.type = 12 AND 
				debtortrans.transno = $transno";
DB_query($SQL, $db);
$UserID=$_SESSION['UsersRealName'];
$SQL="UPDATE reversedallocationhistory SET 
                  reason='$reason',
                  UserID='$UserID'
                  WHERE transno=$transno";
DB_query($SQL, $db);


$SQL="DELETE FROM `debtortrans` WHERE `transno` LIKE '$transno' AND type=12";
DB_query($SQL, $db);
$SQL="DELETE FROM `banktrans` WHERE `transno` = $transno AND type=12";
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