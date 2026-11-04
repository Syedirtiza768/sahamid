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
$salesmanWhere = dashboardOutstandingSalesmanWhere($db, 'salesman.salesmanname', $filter);

if ($salesmanWhere === null) {
	$rows = [];
} else {
	$remainingExpression = "CASE
		WHEN debtortrans.GSTwithhold = 0 AND debtortrans.WHT = 0 THEN debtortrans.ovamount - debtortrans.alloc
		WHEN debtortrans.GSTwithhold = 0 AND debtortrans.WHT = 1 THEN debtortrans.ovamount - debtortrans.alloc - debtortrans.WHTamt
		WHEN debtortrans.GSTwithhold = 1 AND debtortrans.WHT = 0 THEN debtortrans.ovamount - debtortrans.alloc - debtortrans.GSTamt
		WHEN debtortrans.GSTwithhold = 1 AND debtortrans.WHT = 1 THEN debtortrans.ovamount - debtortrans.alloc - debtortrans.GSTamt - debtortrans.WHTamt
		ELSE debtortrans.ovamount - debtortrans.alloc
	END";

	$sql = "SELECT debtorsmaster.debtorno,
	               debtorsmaster.name AS client,
	               invoice.invoiceno,
	               invoice.shopinvoiceno,
	               invoice.invoicesdate,
	               invoice.due,
	               invoice.expected,
	               debtortrans.state,
	               salesman.salesmanname AS salesperson,
	               ROUND($remainingExpression, 2) AS remaining,
	               DATEDIFF(CURDATE(), invoice.invoicesdate) AS age
	        FROM debtortrans
	        INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
	        INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
		        LEFT JOIN custbranch ON debtorsmaster.debtorno = custbranch.debtorno
	          AND custbranch.branchcode = (
	              SELECT MAX(branchcode) FROM custbranch WHERE debtorno = debtorsmaster.debtorno
	          )
		        LEFT JOIN salesman ON salesman.salesmancode = custbranch.salesman
	        WHERE debtortrans.type = 10
	          AND debtortrans.settled = 0
	          AND debtortrans.reversed = 0
	          AND debtortrans.debtorno LIKE 'SR%'
	          AND ABS($remainingExpression) > 0.009
	          $salesmanWhere
	        ORDER BY invoice.invoicesdate, invoice.invoiceno";
	$result = mysqli_query($db, $sql);
	if (!$result) {
		http_response_code(500);
		echo json_encode(['error' => 'Unable to load SR outstanding details']);
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
