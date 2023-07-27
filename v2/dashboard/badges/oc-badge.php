<?php

if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}

$allowed = [8, 10, 22];
// if(in_array($_SESSION['AccessLevel'], $allowed)){

// 	$SQL = "SELECT count(*) as count FROM ocs 
// 			WHERE orddate <= '".date("Y-m-31 23:59:59")."'
// 			AND orddate >= '".date("Y-m-01 00:00:00")."";

// } else {

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

$SQL = "SELECT count(*) as count FROM ocs 
				INNER JOIN salescase ON salescase.salescaseref = ocs.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE salescase.salesman IN( $salesman)
				AND orddate <= '" . date("Y-m-31 23:59:59") . "'
				AND orddate >= '" . date("Y-m-01 00:00:00") . "'";

// }

// $result = mysqli_query($db, $SQL);
// if ($result === FALSE) {
//     $ocCount  = 0;
// } else {
$ocCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
// }

$SQL = "SELECT count(*) as count FROM ocs 
				INNER JOIN salescase ON salescase.salescaseref = ocs.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE orddate <= '" . date("Y-m-31 23:59:59") . "'
				AND orddate >= '" . date("Y-m-01 00:00:00") . "'
AND salescase.debtorno LIKE 'SR%'
AND salescase.salesman IN( $salesman)";

$ocCountSR = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

$SQL = "SELECT count(*) as count FROM ocs 
				INNER JOIN salescase ON salescase.salescaseref = ocs.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE orddate <= '" . date("Y-m-31 23:59:59") . "'
				AND orddate >= '" . date("Y-m-01 00:00:00") . "'
AND salescase.debtorno LIKE 'MT%'
AND salescase.salesman IN( $salesman)";

$ocCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];


?>
<?php
$SQL = 'SELECT ocs.orderno as ocno,SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity
) as totalamount from ocdetails INNER JOIN ocoptions on (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
INNER JOIN ocs on ocs.orderno = ocdetails.orderno
INNER JOIN salescase on salescase.salescaseref=ocs.salescaseref
INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno

AND ocs.orddate BETWEEN  "' . date("Y-m-01") . '" AND "' . date("Y-m-31") . '"
WHERE ocdetails.lineoptionno = 0  
and ocoptions.optionno = 0 
AND salescase.salesman IN( ' . $salesman . ')
GROUP BY ocdetails.orderno
ORDER BY ocdetails.orderno';
$ressData = mysqli_query($db, $SQL);
if ($ressData != NULL) {
	while ($rowData = mysqli_fetch_assoc($ressData)) {
		$octotal  += $rowData['totalamount'];
	}
} else {
	$octotal = 0;
}
$octotal = locale_number_format(round($octotal, 0));
?>

<div class="border rounded-lg w-full sm:w-10/12 h-40" style="box-shadow: inset 0px 3px 6px #00000029, 0px 25px 30px #0000001A;">
	<div class="font-serif py-4 font-semibold text-center text-lg text-slate-800"><span>Order
			Confirmation</span>
	</div>
	<div class="flex justify-between items-center">
		<div class="ml-4 text-lg py-2 font-bold text-center text-slate-700"><span id="ocCount"><?php echo $ocCount; ?></span></div>
		<div class="mr-8"><img class="w-16" src="dashV2-assets/img/quotation.png"></img></div>
	</div>
	<div class="ml-6 text-xs py-6 font-medium text-gray-600">Total: <span id="octotal"><?php echo $octotal; ?></span></div>
</div>
<!-- 

<div class="col-lg-2 col-md-12 col-6 mb-4">
	<div class="card">
		<div class="card-body salescase">
			<div class="card-title d-flex align-items-start justify-content-between">
				<div class="avatar flex-shrink-0">
					<img src="asset/img/icons/unicons/3.svg" style="width:50; height:auto" alt="chart success" class="rounded" />
				</div>
				<div class="dropdown">
					<button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="bx bx-dots-vertical-rounded"></i>
					</button>
					<div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
						<a class="dropdown-item" id="oc">Order Confirmation</a>
						<a class="dropdown-item" id="ocMT">Order Confirmation (MT)</a>
						<a class="dropdown-item" id="ocSR">Order Confirmation (SR)</a>
					</div>
				</div>
			</div>
			<div id="ocDiv">
				<span class="fw-semibold d-block mb-1">Order Confirmation</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="ocCount"><?php echo $ocCount; ?></h3>
			</div>
			<div id="ocSRDiv" style="display: none;">
				<span class="fw-semibold d-block mb-1">OC SR</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="ocCountSR"><?php echo $ocCountSR; ?></h3>
			</div>
			<div id="ocMTDiv" style="display: none;">
				<span class="fw-semibold d-block mb-1">OC MT</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="ocCountMT"><?php echo $ocCountMT; ?></h3>
			</div>
			<hr>
			<h5>Total: <span class="total" id="octotal"><?php echo $octotal; ?></span></h5>
		</div>
	</div>
</div> -->