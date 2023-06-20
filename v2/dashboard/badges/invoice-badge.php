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
while ($row = mysqli_fetch_assoc($res)) {
    $members[] = $row['members'];
}
$salesman = implode(',', $members);
$choices = explode(",", $salesman);
$salesman = "'" . implode("','", $choices) . "";
$salesman = $salesman . "','" . $name . "'";

$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
$res = mysqli_query($db, $SQL);

$canAccess = [];

while ($row = mysqli_fetch_assoc($res))
    $canAccess[] = $row['can_access'];

$SQL = "SELECT 
debtortrans.ovamount as price,
salesman.salesmanname,
invoice.invoiceno,
invoice.shopinvoiceno,
invoice.invoicedate,
invoice.invoicesdate,
debtorsmaster.name as client, 
debtorsmaster.dba,
invoice.services,
invoice.gst,
debtortrans.alloc,
debtortrans.settled
FROM invoice 
INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
INNER JOIN debtorsmaster ON invoice.debtorno = debtorsmaster.debtorno
INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
INNER JOIN debtortrans ON (debtortrans.type = 10
                            AND debtortrans.transno = invoice.invoiceno
                            AND debtortrans.reversed = 0)
WHERE salesman.salesmanname IN( $salesman)
AND invoice.returned = 0
AND invoice.inprogress = 0
AND invoice.invoicesdate <= '" . date("Y-m-31 23:59:59") . "'
AND invoice.invoicesdate >= '" . date("Y-m-01 00:00:00") . "'
		";

$totalScore = NULL;
$resp = mysqli_query($db, $SQL);
while ($row = mysqli_fetch_assoc($resp)) {
    $totalScore = $row['price'] + $totalScore;
}
?>

<div class="col-lg-2 col-md-12 col-6 mb-4">
    <div class="card">
        <div class="card-body salescase">
            <div class="card-title d-flex align-items-start justify-content-between">
                <div class="avatar flex-shrink-0">
                    <img src="asset/img/icons/unicons/design.svg" style="width:40; height:auto" alt="chart success" class="rounded" />
                </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Score</span>
            <?php if($totalScore == NULL){ ?>
            <h3 class="card-title mb-2" style="color:#66c732" id="totalScore"> 0 </h3>
            <?php } else{ ?>
            <h3 class="card-title mb-2" style="color:#66c732;" id="totalScore"><?php echo round($totalScore, 0); ?></h3>
            <?php } ?>
            <!-- <hr>
            <h5 class="total">Total Score</h5> -->
        </div>
    </div>
</div>