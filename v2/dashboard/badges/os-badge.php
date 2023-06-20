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
}
?>

<div class="col-lg-2 col-md-12 col-6 mb-4" style="margin-left:18%;">
    <div class="card">
        <div class="card-body salescase">
            <div class="card-title d-flex align-items-start justify-content-between">
                <div class="avatar flex-shrink-0">
                    <img src="asset/img/icons/unicons/6.svg" style="width:50; height:auto" alt="chart success" class="rounded" />
                </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Outstanding</span>
            <?php if($totaloutstanding == NULL){ ?>
            <h3 class="card-title mb-2" style="color:#66c732" id="outstanding"> 0 </h3>
            <?php } else{ ?>
            <h3 class="card-title mb-2" style="color:#66c732;" id="outstanding"><?php echo round($totaloutstanding, 0); ?></h3>
            <?php } ?>
            <!-- <hr>
            <h5 class="total">Pending DC's</h5> -->
        </div>
    </div>
</div>