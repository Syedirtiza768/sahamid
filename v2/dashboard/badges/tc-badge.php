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
}
if($salesman == "'" ){
    $salesman = "'$name'";
}
else{
$salesman = $salesman . "','" . $name . "'";
}

$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
$res = mysqli_query($db, $SQL);

$canAccess = [];

while ($row = mysqli_fetch_assoc($res))
    $canAccess[] = $row['can_access'];

$SQL = "SELECT      stockissuance.salesperson,
                    SUM(stockissuance.issued*stockmaster.materialcost) as totalValue
						
					FROM stockissuance,
						stockmaster
						
					WHERE stockissuance.stockid=stockmaster.stockid
					AND stockissuance.salesperson IN($salesman)
 GROUP BY stockissuance.salesperson
ORDER BY totalValue desc
            ";
$res = mysqli_query($db, $SQL);

$totalCart = NULL;
while ($clients = mysqli_fetch_assoc($res)) {
    $totalCart = $clients['totalValue'] + $totalCart;
}
$totalCart = locale_number_format(round($totalCart,0));

?>


<div class="col-lg-2 col-md-12 col-6 mb-4">
    <div class="card">
        <div class="card-body salescase">
            <div class="card-title d-flex align-items-start justify-content-between">
                <div class="avatar flex-shrink-0">
                    <img src="asset/img/icons/unicons/7.svg" style="width:50; height:auto" alt="chart success" class="rounded" />
                </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Cart</span>
            <?php if($totalCart == NULL){ ?>
            <h3 class="card-title mb-2 totalcart" style="color:red" id="totalcarts"> <span id="usingCSSBlink"> 0 </span></h3>
            <?php } else{ ?>
            <h3 class="card-title mb-2 totalcart" style="color:red;" id="totalcarts"><span id="usingCSSBlink"><?php echo $totalCart ?></span></h3>
            <?php } ?>
            <!-- <hr>
            <h5 class="total"> Total: 12352</h5> -->
        </div>
    </div>
</div>