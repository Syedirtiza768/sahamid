<?php

if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}


$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
$res = mysqli_query($db, $SQL);

$canAccess = [];

while ($row = mysqli_fetch_assoc($res))
	$canAccess[] = $row['can_access'];


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
// $quotes = "'".$quotes."'";
$SQL = "SELECT count(*) as count FROM salescase 
INNER JOIN www_users ON www_users.realname = salescase.salesman
WHERE salescase.salesman IN($salesman)
AND commencementdate <= '" . date("Y-m-31 23:59:59") . "'
				AND commencementdate >= '" . date("Y-m-01 00:00:00") . "'";

$salescaseCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
$res = mysqli_query($db, $SQL);

$canAccess = [];

while ($row = mysqli_fetch_assoc($res))
	$canAccess[] = $row['can_access'];

$SQL = "SELECT count(*) as count FROM salescase 
INNER JOIN www_users ON www_users.realname = salescase.salesman
WHERE salescase.salesman IN( $salesman)
AND commencementdate <= '" . date("Y-m-31 23:59:59") . "'
				AND commencementdate >= '" . date("Y-m-01 00:00:00") . "'
AND debtorno LIKE 'SR%'";


$salescaseCountSR = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "'";
$res = mysqli_query($db, $SQL);

$canAccess = [];

while ($row = mysqli_fetch_assoc($res))
	$canAccess[] = $row['can_access'];

$SQL = "SELECT count(*) as count FROM salescase 
INNER JOIN www_users ON www_users.realname = salescase.salesman
WHERE salescase.salesman IN( $salesman)
AND commencementdate <= '" . date("Y-m-31 23:59:59") . "'
				AND commencementdate >= '" . date("Y-m-01 00:00:00") . "'
AND debtorno LIKE 'MT%'";

$salescaseCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
?>

<div class="border rounded-lg w-full sm:w-10/12 h-40" style="box-shadow: inset 0px 3px 6px #00000029, 0px 25px 30px #0000001A;">
	<div class="font-serif py-4 font-semibold text-center text-lg text-slate-800"><span>Salescase</span>
	<!-- <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	<i class='bx bxs-down-arrow'></i>
	</button>
	<div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3" id="target">
		<a class="dropdown-item" id="salescase">Salescase</a>
		<a class="dropdown-item" id="salescaseMT">Salescase (MT)</a>
		<a class="dropdown-item" id="salescaseSR">Salescase (SR)</a>
	</div> -->
	</div>
	<div class="flex justify-between items-center">
		<div class="ml-4 text-lg py-2 font-bold text-center text-slate-700" id="salescaseCount"><?php echo $salescaseCount; ?></div>
		<div class="mr-8"><img class="w-16" src="dashV2-assets/img/quotation.png"></img></div>
	</div>
	<div class="ml-6 text-xs py-6 font-medium text-gray-600"><span></span></div>
</div>


<!-- <div class="col-lg-2 col-md-12 col-6 mb-4" style="margin-left:17%;">
	<div class="card">
		<div class="card-body salescase">
			<div class="card-title d-flex align-items-start justify-content-between">
				<div class="avatar flex-shrink-0">
					<img src="asset/img/icons/unicons/5.svg" style="width:50; height:auto" alt="chart success" class="rounded" />
				</div>
				<div class="dropdown ">
					<button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="bx bx-dots-vertical-rounded"></i>
					</button>
					<div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3" id="target">
						<a class="dropdown-item" id="salescase">Salescase</a>
						<a class="dropdown-item" id="salescaseMT">Salescase (MT)</a>
						<a class="dropdown-item" id="salescaseSR">Salescase (SR)</a>
					</div>
				</div>
			</div>
			<div id="salescaseDiv">
				<span class="fw-semibold d-block mb-1">Salescase</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="salescaseCount"><?php echo $salescaseCount; ?></h3>

			</div>
			<div id="salescaseSRDiv" style="display: none;">
				<span class="fw-semibold d-block mb-1">Salescase SR</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="salescaseCountSR"><?php echo $salescaseCountSR; ?></h3>

			</div>
			<div id="salescaseMTDiv" style="display: none;">
				<span class="fw-semibold d-block mb-1">Salescase MT</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="salescaseCountMT"><?php echo $salescaseCountMT; ?></h3>

			</div>
			<hr>
			<h5 class="total"> Salescase </h5>
		</div>
	</div>
</div> -->