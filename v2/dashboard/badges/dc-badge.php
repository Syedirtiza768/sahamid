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
$dcCount = locale_number_format(round($dcCount,0));

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
	$dcCountSR = locale_number_format(round($dcCountSR,0));
}

$SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate <= '" . date("Y-m-31") . "'
			AND orddate >= '" . date("Y-m-01") . "'
			AND salescase.debtorno LIKE 'MT%'
			AND salescase.salesman = IN( $salesman)";

$result = mysqli_query($db, $SQL);
if ($result === FALSE) {
	$dcCountMT  = 0;
} else {
	$dcCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
	$dcCountMT = locale_number_format(round($dcCountMT,0));
}

?>
<?php

$SQL = "SELECT * FROM user_permission WHERE userid='" . $_SESSION['UserID'] . "' AND permission='*' ";
$ressData = mysqli_query($db, $SQL);
while ($rowData = mysqli_fetch_assoc($ressData)) {
	$permission = $rowData['permission'];
}
?>

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
				<hr>
				<h5 class="total"> Total: 12352</h5>
			</div>
			<div id="dcSRDiv" style="display: none;">
				<span class="fw-semibold d-block mb-1">DC SR</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="dcCountSR"><?php echo $dcCountSR; ?></h3>
				<hr>
				<h5 class="total"> Total: 12352</h5>
			</div>
			<div id="dcMTDiv" style="display: none;">
				<span class="fw-semibold d-block mb-1">DC MT</span>
				<h3 class="card-title mb-2" style="color:#66c732" id="dcCountMT"><?php echo $dcCountMT; ?></h3>
				<hr>
				<h5 class="total"> Total: 12352</h5>
			</div>
		</div>
	</div>
</div>