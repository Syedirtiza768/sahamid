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

$SQL = "SELECT * FROM salesman WHERE salesmanname IN($salesman)";
$res = mysqli_query($db, $SQL);
$totalTarget  = NULL;
while ($row = mysqli_fetch_assoc($res)) {
    $totalTarget = $row['target'] + $totalTarget;
}
$Target = $totalTarget / 12;
$actualTarget = $Target * 12;
$actualTarget = locale_number_format(round($actualTarget, 0));

while ($row = mysqli_fetch_assoc($res))
    $canAccess[] = $row['can_access'];


$months = [];
$acheived = [];

for ($i = 1; $i <= 12; $i++) {

    $month = 0;
    if ($i <= 9)
        $month .= $i;
    else
        $month = $i;

    $startDate = date('Y-' . $month . '-01');
    $endDate = date('Y-' . $month . '-31');


    $SQL = "SELECT 
        SUM(debtortrans.ovamount - debtortrans.GSTtotalamt) as price
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
         AND invoice.invoicesdate >= '" . $startDate . "'
				  AND invoice.invoicesdate <= '" . $endDate . "'";

    $sale = mysqli_fetch_assoc(mysqli_query($db, $SQL))['price'];

    $acheived[]  = ($i > (int)(date('m'))) ? null : ((int)($sale ?: 0));
    $months[] = date("M", strtotime($startDate));
}

$monthsRemaining = 12;
$salesTotal      = 0;
foreach ($acheived as $acheived) {

    $salesTotal += $acheived;

    if ($i <= (int)(date('m')))
        $monthsRemaining--;

    $i++;
}
$targetAcheived = locale_number_format(round($salesTotal, 0));
$totalTarget = locale_number_format(round($totalTarget, 0));
if ($actualTarget != 0) {
    $acheiveRatio = ($targetAcheived * 100) / $actualTarget;
} else {
    $acheiveRatio = 0;
}
$acheiveRatio = round($acheiveRatio, 2);
?>
<style>
    .flex-container {
        display: flex;
    }

    .flex-container>div {
        margin: 2px;
        padding: 10px;
        font-size: 20px;
    }
</style>

<div class="lg:border-r-2 lg:border-slate-400 lg:flex-none lg:w-1/3 h-full">
    <div class="flex justify-center items-center flex-col ...">
        <img class="max-w-xl mx-auto w-12 mt-6" src="dashV2-assets/img/salesTarget.png"></img>
        <div class="font-semibold text-md mt-2"><span>Total Sales Target</span></div>
        <?php if ($targetAcheived == NULL) { ?>
            <div class="font-semibold text-md mt-2"><span> 0 </span></div>
        <?php } else { ?>
            <div class="font-semibold text-md mt-2"><span id="actualTarget"> <?php echo $totalTarget ?> </span></div>
        <?php } ?>

    </div>
</div>
<div class="lg:border-r-2 lg:border-slate-400 lg:flex-none lg:w-1/3">
    <div class="flex justify-center items-center flex-col ...">
        <img class="max-w-xl mx-auto w-12 mt-6" src="dashV2-assets/img/at.png"></img>
        <div class="font-semibold text-md mt-2"><span>Acheived Target</span></div>
        <?php if ($targetAcheived == NULL) { ?>
            <div class="font-semibold text-md mt-2"><span> 0 </span></div>
        <?php } else { ?>
            <div class="font-semibold text-md mt-2"><span id="targetAcheived"> <?php echo $targetAcheived ?> </span></div>
        <?php } ?>
    </div>
</div>
<div class="lg:flex-none lg:w-1/3 h-full">
    <div class="flex justify-center items-center flex-col ...">
        <img class="max-w-xl mx-auto w-12 mt-6" src="dashV2-assets/img/ratio.png"></img>
        <div class="font-semibold text-md mt-2"><span>Acheived Ratio</span></div>
        <?php if ($targetAcheived == NULL) { ?>
            <div class="font-semibold text-md mt-2"><span> 0% </span></div>
        <?php } else { ?>
            <div class="font-semibold text-md mt-2"><span id="acheiveRatio"> <?php echo $acheiveRatio ?>%</span></div>
        <?php } ?>
        
    </div>
</div>

<!-- 
<div class="col-lg-6 col-md-12 col-6 mb-4" style="margin:auto;">
    <div class="card">
        <div class="card-body salescase">
            <div class="card-title d-flex align-items-start justify-content-between">
                <div class="avatar flex-shrink-0">
                    <img src="asset/img/icons/unicons/design.svg" style="width:60; height:auto;" alt="chart success" class="rounded" />
                </div>
            </div>
            <div class="flex-container">
                <div>
                    <span class="fw-semibold d-block mb-1">Total Target</span>
                    <?php if ($targetAcheived == NULL) { ?>
                        <h3 class="card-title mb-2" style="color:#66c732" id="actualTarget"> 0 </h3>
                    <?php } else { ?>
                        <h3 class="card-title mb-2" style="color:#66c732;" id="actualTarget"><?php echo $actualTarget ?></h3>
                    <?php } ?>
                </div>
                <div style=" border-left: 3px solid #66c732;">

                </div>
                <div>
                    <span class="fw-semibold d-block mb-1">Acheived Target</span>
                    <?php if ($targetAcheived == NULL) { ?>
                        <h3 class="card-title mb-2" style="color:#66c732" id="targetAcheived"> 0 </h3>
                    <?php } else { ?>
                        <h3 class="card-title mb-2" style="color:#66c732;" id="targetAcheived"><?php echo $targetAcheived ?></h3>
                    <?php } ?>
                </div>

                <div style=" border-left: 3px solid #66c732;">

                </div>
                <div>
                    <span class="fw-semibold d-block mb-1">Acheive Ratio</span>
                    <?php if ($targetAcheived == NULL) { ?>
                        <h3 class="card-title mb-2" style="color:#66c732" id="acheiveRatio"> 0 </h3>
                    <?php } else { ?>
                        <h3 class="card-title mb-2" style="color:#66c732;" id="acheiveRatio"><?php echo $acheiveRatio ?>%</h3>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div> -->