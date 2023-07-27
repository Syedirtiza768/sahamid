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

// 	$SQL = "SELECT count(*) as count FROM salesorders 
// 			WHERE orddate <= '".date("Y-m-t")."'
//         AND orddate >= '".date("Y-m-01")."'";

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

$SQL = "SELECT count(*) as count FROM salesorders 
				INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE orddate <= '" . date("Y-m-t") . "'
				AND orddate >= '" . date("Y-m-01") . "'
				AND ( salescase.salesman IN( $salesman)
					OR www_users.userid IN ('" . implode("','", $canAccess) . "') )";

// }

$quotationCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

$SQL = "SELECT count(*) as count FROM salesorders 
				INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE orddate <= '" . date("Y-m-t") . "'
				AND orddate >= '" . date("Y-m-01") . "'
				AND salescase.debtorno LIKE 'SR%'
				AND ( salescase.salesman IN( $salesman)
					OR www_users.userid IN ('" . implode("','", $canAccess) . "') )";

$quotationCountSR = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];


$SQL = "SELECT count(*) as count FROM salesorders 
				INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE orddate <= '" . date("Y-m-t") . "'
				AND orddate >= '" . date("Y-m-01") . "'
				AND salescase.debtorno LIKE 'MT%'
				AND ( salescase.salesman IN( $salesman)
					OR www_users.userid IN ('" . implode("','", $canAccess) . "') )";

$quotationCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

?>
<?php
$SQL = 'SELECT salesorders.orderno as quoteno,SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
) as totalamount from salesorderdetails INNER JOIN salesorderoptions on (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
INNER JOIN salescase on salescase.salescaseref=salesorders.salescaseref
INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno

AND salesorders.orddate BETWEEN  "' . date("Y-m-01") . '" AND "' . date("Y-m-31") . '"
WHERE salesorderdetails.lineoptionno = 0  
and salesorderoptions.optionno = 0 
AND salescase.salesman IN( ' . $salesman . ')
GROUP BY salesorderdetails.orderno
ORDER BY salesorderdetails.orderno';
$ressData = mysqli_query($db, $SQL);
if ($ressData != NULL) {
	while ($rowData = mysqli_fetch_assoc($ressData)) {
		$qtotal  += $rowData['totalamount'];
	}
} else {
	$qtotal = 0;
}
$qtotal = locale_number_format(round($qtotal, 0));
?>


<div class="border rounded-lg w-full sm:w-10/12 h-40" style="box-shadow: inset 0px 3px 6px #00000029, 0px 25px 30px #0000001A;">
	<div class="font-serif py-4 font-semibold text-center text-lg text-slate-800"><span>Quotation</span>
	</div>
	<div class="flex justify-between items-center">
		<div class="ml-4 text-lg py-2 font-bold text-center text-slate-700"><span id="quotationCount"><?php echo $quotationCount; ?></span></div>
		<div class="mr-8"><img class="w-16" src="dashV2-assets/img/salescase.png"></img></div>
	</div>
	<div class="ml-6 text-xs py-6 font-medium text-gray-600">Total: <span id="quototal">Total: <?php echo $qtotal; ?></span></div>
</div>

<!-- 
<div class="col-lg-2 col-md-12 col-6 mb-4">
	<div class="card">
		<div class="card-body salescase">
			<div class="card-title d-flex align-items-start justify-content-between">
				<div class="avatar flex-shrink-0">
					<img src="asset/img/icons/unicons/2.svg" alt="chart success" class="rounded" />
				</div>
				<div class="dropdown">
					<button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="bx bx-dots-vertical-rounded"></i>
					</button>
					<div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
						<a class="dropdown-item" id="quotation"">Quotation</a>
						<a class=" dropdown-item" id="quotationMT"">Quotation (MT)</a>
						<a class=" dropdown-item" id="quotationSR"">Quotation (SR)</a>
					</div>
				</div>
			</div>
			<div id=" quotationDiv">
							<span class="fw-semibold d-block mb-1">Quotation</span>
							<h3 class="card-title mb-2" style="color:#66c732" id="quotationCount"><?php echo $quotationCount; ?></h3>
					</div>
					<div id="quotationSRDiv" style="display: none;">
						<span class="fw-semibold d-block mb-1">Quotation SR</span>
						<h3 class="card-title mb-2" style="color:#66c732" id="quotationCountSR"><?php echo $quotationCountSR; ?></h3>
					</div>
					<div id="quotationMTDiv" style="display: none;">
						<span class="fw-semibold d-block mb-1">Quotation MT</span>
						<h3 class="card-title mb-2" style="color:#66c732" id="quotationCountMT"><?php echo $quotationCountMT; ?></h3>
					</div>
					<hr>
					<h5>Total: <span class="total" id="quototal"><?php echo $qtotal; ?></span></h5>
				</div>
			</div>
		</div> -->