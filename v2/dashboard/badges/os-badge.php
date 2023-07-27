<?php

if (!isset($db)) {
    session_start();
    $db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
    return;
}

$allowed = [8, 10, 22];

$SQL = "SELECT * FROM salesteam WHERE lead='" . $name . "'";
$res = mysqli_query($db, $SQL);
$members = [];
if ($res === FALSE) {
    $salesman = "'  $name  '";
} else {
    while ($row = mysqli_fetch_assoc($res)) {
        $members[] = $row['members'];
    }
    $salesman = implode(',', $members);
    $choices = explode(",", $salesman);
    $salesman = "'" . implode("','", $choices) . "";
    $salesman = $salesman . "','" . $name . "'";
}
$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
$res = mysqli_query($db, $SQL);

$canAccess = [];

while ($row = mysqli_fetch_assoc($res))
    $canAccess[] = $row['can_access'];

$SQL = "SELECT  debtorsmaster.debtorno,
debtorsmaster.name,
debtorsmaster.dba,
invoice.shopinvoiceno,
invoice.invoicesdate,
invoice.branchcode,
invoice.invoiceno,
invoice.comment,
ROUND(debtortrans.ovamount) as total,
ROUND(debtortrans.alloc) as paid,
(
CASE WHEN GSTwithhold = 0 AND WHT = 0 
    THEN ovamount - alloc
WHEN GSTwithhold = 0 AND WHT = 1 
    THEN ovamount - alloc - WHTamt
WHEN GSTwithhold = 1 AND WHT = 0 
    THEN ovamount - alloc - GSTamt
WHEN GSTwithhold = 1 AND WHT = 1 
    THEN ovamount - alloc - GSTamt - WHTamt
END
) AS remaining  ,
salescase.salesman as salesperson,
invoice.salescaseref,
invoice.due,
invoice.expected,
debtortrans.state,
dcgroups.dcnos
FROM debtorsmaster
INNER JOIN invoice ON (invoice.debtorno = debtorsmaster.debtorno
AND invoice.returned = 0)
INNER JOIN salescase ON invoice.salescaseref=salescase.salescaseref
INNER JOIN dcgroups ON dcgroups.id = invoice.groupid
INNER JOIN debtortrans ON (debtortrans.transno = invoice.invoiceno AND debtortrans.type = 10
        AND debtortrans.reversed = 0
        AND debtortrans.settled = 0)
INNER JOIN www_users ON salescase.salesman = www_users.realname AND ( salescase.salesman IN( $salesman ))
		";

$totaloutstanding = NULL;
$resp = mysqli_query($db, $SQL);
if ($resp === FALSE) {
    $totaloutstanding  = 0;
} else {
    while ($row = mysqli_fetch_assoc($resp)) {
        $totaloutstanding = $row['remaining'] + $totaloutstanding;
    }
    $totaloutstanding = locale_number_format(round($totaloutstanding, 0));
}
?>


<div class="border rounded-lg sm:w-11/12 shadow-2xl">
    <div class="flex justify-start">
        <div class="mr-6 ml-6 py-5 flex-none"><img class="w-12" src="dashV2-assets/img/os.png"></img></div>
        <div class="flex flex-col">
            <div class="font-serif mt-4 font-semibold text-gray-400">
                <h3>Total Outstandings</h3>
            </div>
            <?php if ($totaloutstanding == NULL) { ?>
                <div class="text-red-500 text-lg font-bold" id="outstanding"><span id="usingCSSBlink"> 0 </span></div>
            <?php } else { ?>
                <div class="text-red-500 text-lg font-bold" id="outstanding"><span id="usingCSSBlink"> <?php echo $totaloutstanding ?> </span></div>
            <?php } ?>
        </div>
    </div>
</div>


