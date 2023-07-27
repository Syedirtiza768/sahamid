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

$SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate <= '" . date("Y-m-31") . "'
			AND orddate >= '" . date("Y-m-01") . "'
			AND salescase.salesman IN( $salesman)";

$dcCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
// $dcCount = locale_number_format(round($dcCount, 0));

$SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate <= '" . date("Y-m-31") . "'
			AND orddate >= '" . date("Y-m-01") . "'
			AND salescase.debtorno LIKE 'SR%'
			AND salescase.salesman = IN( $salesman)";


$result = mysqli_query($db, $SQL);
if ($result === FALSE) {
	$dcCountSR  = 0;
} else {
	$dcCountSR = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
	$dcCountSR = locale_number_format(round($dcCountSR, 0));
}

$SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate <= '" . date("Y-m-31") . "'
			AND orddate >= '" . date("Y-m-01") . "'
			AND salescase.debtorno LIKE 'MT%'
			AND salescase.salesman IN( $salesman)";

$result = mysqli_query($db, $SQL);
if ($result === FALSE) {
	$dcCountMT  = 0;
} else {
	$dcCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
	$dcCountMT = locale_number_format(round($dcCountMT, 0));
}

?>
<?php

$SQL = 'SELECT dcs.orderno as dcno,SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	) as totalamount from dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
		INNER JOIN dcs on dcs.orderno = dcdetails.orderno
		INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
		INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
		
		AND dcs.orddate BETWEEN  "' . date("Y-m-01") . '" AND "' . date("Y-m-31") . '"
		AND dcs.grbdate LIKE "0000-00-00 00:00:00"
		WHERE dcdetails.lineoptionno = 0  
			and dcoptions.optionno = 0 
		 	AND salescase.salesman IN( ' . $salesman . ')
		GROUP BY dcdetails.orderno
		ORDER BY dcdetails.orderno';
$ressData = mysqli_query($db, $SQL);
if ($ressData != NULL) {
	while ($rowData = mysqli_fetch_assoc($ressData)) {
		$total  += $rowData['totalamount'];
	}
} else {
	$total = 0;
}
$total = locale_number_format(round($total, 0));
?>

<div class="border rounded-lg w-full sm:w-10/12 h-40" style="box-shadow: inset 0px 3px 6px #00000029, 0px 25px 30px #0000001A;">
	<div class="font-serif py-4 font-semibold text-center text-lg text-slate-800"><span>Delivery
			Challan</span>
	</div>
	<div class="flex justify-between items-center">
		<div class="ml-4 text-lg py-2 font-bold text-center text-slate-700" ><span id="dcCount"> <?php echo $dcCount; ?></span></div>
		<div class="mr-8"><img class="w-16" src="dashV2-assets/img/dc.png"></img></div>
	</div>
	<div class="ml-6 text-xs py-6 font-medium text-gray-600">Total: <span id="dctotal"><?php echo $total; ?></span></div>
</div>
<!-- 
<div class="col-lg-2 col-md-12 col-6 mb-4">
	<div class="card">
		<div class="card-body salescase">
			<div class="card-title d-flex align-items-start justify-content-between">
				<div class="avatar flex-shrink-0">
					<img src="asset/img/icons/unicons/6.svg" style="width:50; height:auto" alt="chart success" class="rounded" />
				</div>
				<div class="dropdown">
					<button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="bx bx-dots-vertical-rounded"></i>
					</button>
					<div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
						<a class="dropdown-item" id="dc">Delivery Challan</a>
						<a class="dropdown-item" id="dcMT">Delivery Challan (MT)</a>
						<a class="dropdown-item" id="dcSR">Delivery Challan (SR)</a>
					</div>
				</div>
			</div>
			<div id="dcDiv">
				<span class="fw-semibold d-block mb-1">Delivery Challan</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="dcCount"><?php echo $dcCount; ?></h3>

			</div>
			<div id="dcSRDiv" style="display: none;">
				<span class="fw-semibold d-block mb-1">DC SR</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="dcCountSR"><?php echo $dcCountSR; ?></h3>

			</div>
			<div id="dcMTDiv" style="display: none;">
				<span class="fw-semibold d-block mb-1">DC MT</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="dcCountMT"><?php echo $dcCountMT; ?></h3>

			</div>
			<hr>
			<h5>Total: <span class="total" id="dctotal"><?php echo $total; ?></span></h5>
		</div>
	</div>
</div> -->