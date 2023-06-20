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

    $SQL = "SELECT * FROM salesman WHERE salesmanname IN($salesman)";
	$res = mysqli_query($db, $SQL);
    $totalTarget  = NULL;
while ($row = mysqli_fetch_assoc($res)) {
    $totalTarget = $row['target'] + $totalTarget;
}
?>

<div class="col-lg-2 col-md-12 col-6 mb-4">
    <div class="card">
        <div class="card-body salescase">
            <div class="card-title d-flex align-items-start justify-content-between">
                <div class="avatar flex-shrink-0">
                    <img src="asset/img/icons/unicons/4.svg" style="width:50; height:auto" alt="chart success" class="rounded" />
                </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Sales Target</span>
            <?php if($totalTarget == NULL){ ?>
            <h3 class="card-title mb-2" style="color:#66c732" id="salestarget"> 0 </h3>
            <?php } else{ ?>
            <h3 class="card-title mb-2" style="color:#66c732;" id="salestarget"><?php echo round($totalTarget, 0); ?></h3>
            <?php } ?>
            <!-- <hr>
            <h5 class="total"> Total: 12352</h5> -->
        </div>
    </div>
</div>