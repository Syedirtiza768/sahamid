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

$SQL = "SELECT dcs.orderno as dcno,dcs.orddate as date,dcs.salescaseref,debtorsmaster.name as client,
			salescase.salesman,debtorsmaster.dba,SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	) as totalamount,dcs.gst, CASE  WHEN  dcs.gst LIKE '%inclusive%' THEN SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	)*0.83 ELSE SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	)   END as exclusivegsttotalamount from dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
		INNER JOIN dcs on dcs.orderno = dcdetails.orderno
		INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
		INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
		WHERE salescase.salesman IN( $salesman)
        AND dcdetails.lineoptionno = 0  
		AND dcoptions.optionno = 0 
		AND dcs.orddate <= '" . date("Y-m-31 23:59:59") . "'
		AND dcs.orddate >= '" . date("Y-m-01 00:00:00") . "'
		AND dcs.courierslipdate = '0000-00-00 00:00:00' AND dcs.invoicedate='0000-00-00 00:00:00' 
		AND dcs.grbdate='0000-00-00 00:00:00'
		AND dcs.invoicegroupid is null
		GROUP BY dcs.orderno
		";

$totalPending = NULL;
$resp = mysqli_query($db, $SQL);
while ($row = mysqli_fetch_assoc($resp)) {
    $totalPending = round($row['totalamount']) + $totalPending;
}
$totalPending = locale_number_format(round($totalPending,0));
?>

<div class="col-lg-2 col-md-12 col-6 mb-4">
    <div class="card">
        <div class="card-body salescase">
            <div class="card-title d-flex align-items-start justify-content-between">
                <div class="avatar flex-shrink-0">
                    <img src="asset/img/icons/unicons/6.svg" style="width:50; height:auto" alt="chart success" class="rounded" />
                </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Pending DC's</span>
            <?php if($totalPending == NULL){ ?>
            <h3 class="card-title mb-2" style="color:red" id="pdcCount"><span id="usingCSSBlink"> 0 </span></h3>
            <?php } else{ ?>
            <h3 class="card-title mb-2" style="color:red;" id="pdcCount"><span id="usingCSSBlink"> <?php echo $totalPending ?> </span></h3>
            <?php } ?>
            <!-- <hr>
            <h5 class="total">Pending DC's</h5> -->
        </div>
    </div>
</div>