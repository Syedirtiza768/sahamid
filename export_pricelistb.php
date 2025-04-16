<?php
//Database details
$db_host = 'localhost';
$db_username = 'irtiza';
$db_password = 'netetech321';
$db_name = 'sahamid';
//Create connection and select DB
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=pricelist.csv');

$output = fopen('php://output', 'w');

fputcsv($output, [
    'Stock ID', 'Brand', 'Category Description', 'mnfCode', 'mnfpno', 'Item Condition',
    'Last Update', 'Update Person', 'Last Price', 'Current Price',
    'QOH HOPS', 'QOH MTPS', 'QOH SRPS', 'QOH OS'
]);

$SQL = "SELECT stockmaster.stockid, manufacturers_name, lastcost, materialcost, lastcostupdate, 
        lastupdatedby, mnfCode, mnfpno, abbreviation, categorydescription
        FROM stockmaster 
        INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id
        INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid
        INNER JOIN itemcondition ON stockmaster.conditionID = itemcondition.conditionID
        GROUP BY stockmaster.stockid";
$res = mysqli_query($conn, $SQL);

function getLocationQuantities($loccode, $conn) {
    $qty = [];
    $sql = "SELECT stockid, quantity FROM locstock WHERE loccode='$loccode'";
    $res = mysqli_query($conn, $sql);
    while($row = mysqli_fetch_assoc($res)) {
        $qty[$row['stockid']] = $row['quantity'];
    }
    return $qty;
}

$qtyHOPS = getLocationQuantities('HOPS', $conn);
$qtyMTPS = getLocationQuantities('MTPS', $conn);
$qtySRPS = getLocationQuantities('SRPS', $conn);
$qtyOS = getLocationQuantities('OS', $conn);

while ($row = mysqli_fetch_assoc($res)) {
    $stockid = $row['stockid'];
    fputcsv($output, [
        $stockid,
        $row['manufacturers_name'],
        $row['categorydescription'],
        $row['mnfCode'],
        $row['mnfpno'],
        $row['abbreviation'],
        $row['lastcostupdate'],
        $row['lastupdatedby'],
        $row['lastcost'],
        $row['materialcost'],
        $qtyHOPS[$stockid] ?? 0,
        $qtyMTPS[$stockid] ?? 0,
        $qtySRPS[$stockid] ?? 0,
        $qtyOS[$stockid] ?? 0,
    ]);
}

fclose($output);
exit;
