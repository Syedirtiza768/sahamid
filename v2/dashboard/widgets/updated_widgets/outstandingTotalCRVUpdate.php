<?php
include_once __DIR__ . '/../../../../db_config.php';

if (!isset($db)) {
	session_start();
	$db = mysqli_connect(SAHAMID_DB_HOST, SAHAMID_DB_USER, SAHAMID_DB_PASSWORD, 'sahamid');
}

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['UserID'])) {
	http_response_code(401);
	echo json_encode(['error' => 'Authentication required']);
	return;
}

include_once __DIR__ . '/outstandingSalesmanAccess.php';
$access = dashboardOutstandingSalesmanAccess($db);
$filter = dashboardOutstandingSalesmanFilter($access);
$salesmanWhere = dashboardOutstandingSalesmanWhere($db, 'shopsale.salesman', $filter);

if ($salesmanWhere === null) {
	$rows = [];
} else {
	$sql = "SELECT debtorsmaster.debtorno,
	               debtorsmaster.name AS client,
	               shopsale.orderno,
	               shopsale.orddate,
	               shopsale.salesman AS salesperson,
	               ROUND(debtortrans.ovamount, 2) AS total,
	               ROUND(debtortrans.alloc, 2) AS paid,
	               ROUND(debtortrans.ovamount - debtortrans.alloc, 2) AS remaining,
	               shopsale.due,
	               shopsale.expected,
	               debtortrans.state,
	               DATEDIFF(CURDATE(), shopsale.orddate) AS age
	        FROM debtortrans
	        INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
	        INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
	        WHERE debtortrans.type = 750
	          AND debtortrans.settled = 0
	          AND debtortrans.reversed = 0
	          AND shopsale.payment = 'crv'
	          AND ABS(debtortrans.ovamount - debtortrans.alloc) > 0.009
	          $salesmanWhere
	        ORDER BY shopsale.orddate, shopsale.orderno";
	$result = mysqli_query($db, $sql);
	if (!$result) {
		http_response_code(500);
		echo json_encode(['error' => 'Unable to load CRV outstanding details']);
		return;
	}

	$rows = [];
	while ($row = mysqli_fetch_assoc($result)) {
		$rows[] = $row;
	}
}

$chartAmounts = [0, 0, 0, 0, 0];
$totalBalance = 0;
foreach ($rows as $row) {
	$remaining = (float) $row['remaining'];
	$age = (int) $row['age'];
	$totalBalance += $remaining;
	if ($age < 30) {
		$chartAmounts[0] += $remaining;
	} elseif ($age < 60) {
		$chartAmounts[1] += $remaining;
	} elseif ($age < 90) {
		$chartAmounts[2] += $remaining;
	} elseif ($age < 120) {
		$chartAmounts[3] += $remaining;
	} else {
		$chartAmounts[4] += $remaining;
	}
}

$chart = [
	['0-30 Days', round($chartAmounts[0], 2)],
	['30+ Days', round($chartAmounts[1], 2)],
	['60+ Days', round($chartAmounts[2], 2)],
	['90+ Days', round($chartAmounts[3], 2)],
	['120+ Days', round($chartAmounts[4], 2)],
	['Total', round($totalBalance, 2)],
];

echo json_encode([
	'status' => 'success',
	'salesmen' => $access['salesmen'],
	'chart' => $chart,
	'rows' => $rows,
]);
