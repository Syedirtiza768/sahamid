<?php
// Database connection
$db_host = 'localhost';
$db_username = 'irtiza';
$db_password = 'netetech321';
$db_name = 'sahamid';

$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="stock_analysis_export.csv"');

$output = fopen('php://output', 'w');

// Inputs with fallback/defaults
$from = $_POST['from'] ?? '2024-01-01';
$to = $_POST['to'] ?? date('Y-m-d');
$Location = $_POST['Location'] ?? '';

// Write column headers
fputcsv($output, [
    'Stock ID.',
    'Manufacturers Code',
    'Part No.',
    'Description',
    'Brand',
    'Int. Ref. Quantity',
    'DC Quantity',
    'DC Value',
    'Inv. Quantity',
    'Inv. Value',
    'Inv. Factor',
    'Shop Sale Quantity',
    'Shop Sale Value',
    'Shop Sale Factor',
    'Final Ref. Quantity',
    'Current List Price'
]);

// Brand Mapping
$brandMap = [];
$SQL = "CREATE VIEW refA AS SELECT stockid,MAX(stkmoveno) as stkmoves,loccode FROM stockmoves 
        
        WHERE trandate<='$from' AND trandate>='2016-01-01' AND loccode IN ('HO','MT','SR') GROUP BY stockid,loccode";
mysqli_query($conn, $SQL);
$brandQuery = mysqli_query($conn, "SELECT stockmaster.stockid,manufacturers.manufacturers_name,
        stockmaster.description,stockmaster.materialcost,
        stockmaster.mnfCode,stockmaster.mnfpno,loccode,SUM(newqoh) as qohA FROM stockmoves INNER JOIN stockmaster
        ON stockmaster.stockid=stockmoves.stockid  INNER JOIN manufacturers 
        ON manufacturers.manufacturers_id=stockmaster.brand 
        WHERE stkmoveno IN (SELECT stkmoves FROM refA)
        GROUP BY stockid");
while ($row = mysqli_fetch_assoc($brandQuery)) {
    $brandMap[$row['stockid']] = [
        'name' => $row['manufacturers_name'],
        'qohA' => $row['qohA'],
    ];
}

// 1. Internal Reference Quantities
$refQty = [];
$SQL = "DROP VIEW refA";
mysqli_query($conn, $SQL);
$SQL = 'SELECT stockmaster.stockid,
    AVG(invoicedetails.unitprice*(1-invoicedetails.discountpercent)) as invoicecost,
    AVG(invoicedetails.discountpercent*100) as averageInvoiceFactor,
    SUM(invoicedetails.quantity*invoiceoptions.quantity) as invoiceitemQty,
    SUM(CASE 
        WHEN invoice.gst LIKE "%inclusive%" THEN  
            (invoicedetails.unitprice * (1 - invoicedetails.discountpercent) * invoicedetails.quantity * invoiceoptions.quantity)
        ELSE 
            (invoicedetails.unitprice * (1 - invoicedetails.discountpercent) * invoicedetails.quantity * invoiceoptions.quantity) * 1.17
    END) as invoiceexclusivegsttotalamount
    FROM invoicedetails 
    INNER JOIN invoiceoptions 
        ON invoicedetails.invoiceno = invoiceoptions.invoiceno 
        AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno 
    INNER JOIN invoice 
        ON invoice.invoiceno = invoicedetails.invoiceno 
    INNER JOIN salescase 
        ON salescase.salescaseref = invoice.salescaseref 
    INNER JOIN stockmaster 
        ON stockmaster.stockid = invoicedetails.stkcode 
    INNER JOIN manufacturers 
        ON manufacturers.manufacturers_id = stockmaster.brand 
    INNER JOIN debtorsmaster 
        ON debtorsmaster.debtorno = salescase.debtorno 
    WHERE invoice.inprogress = 0 
        AND invoice.returned = 0 
        AND invoice.invoicesdate BETWEEN "' . $from . '"
        AND debtorsmaster.debtorno LIKE "%' . $Location . '%"
    GROUP BY invoicedetails.stkcode';

$refRes = mysqli_query($conn, $SQL);

while ($row = mysqli_fetch_assoc($refRes)) {
    $stockid = $row['stockid'];
    $refQty[$stockid] = [
        'invoicecost' => $row['invoicecost'],
        'averageInvoiceFactor' => $row['averageInvoiceFactor'],
        'invoiceitemQty' => $row['invoiceitemQty'],
        'invoiceexclusivegsttotalamount' => $row['invoiceexclusivegsttotalamount'],
    ];
}

$SQL = "CREATE VIEW refB AS SELECT stockid,MAX(stkmoveno) as stkmoves,loccode FROM stockmoves 
        
        WHERE trandate<='$to' AND trandate>='2016-01-01' AND loccode IN ('HO','MT','SR') GROUP BY stockid,loccode";
mysqli_query($conn, $SQL);
$SQL = "SELECT stockid,SUM(newqoh) as qohB FROM stockmoves
        WHERE stkmoveno IN (SELECT stkmoves FROM refB)
        GROUP BY stockid";

$res = mysqli_query($conn, $SQL);



$response4 = [];
while ($row = mysqli_fetch_assoc($res)) {
    $stkcde = $row['stockid'];
    $response4[$stkcde] = $row;
}


// Stock Items
$stockSQL = "SELECT stockid, mnfCode, mnfpno, description, materialcost, brand FROM stockmaster";
$stockResult = mysqli_query($conn, $stockSQL);

// Output rows
while ($stock = mysqli_fetch_assoc($stockResult)) {
    $stockid = $stock['stockid'];
    $brandId = $stock['brand'];

    $invQty = $invData[$stockid]['qty'] ?? 0;
    $invAmt = $invData[$stockid]['amount'] ?? 0;
    $invFactor = ($invData[$stockid]['avg_price'] ?? 0) > 0 ? round($invAmt / $invData[$stockid]['avg_price'], 2) : 0;

    $saleQty = $saleData[$stockid]['qty'] ?? 0;
    $saleAmt = $saleData[$stockid]['amount'] ?? 0;
    $saleFactor = ($saleData[$stockid]['avg_price'] ?? 0) > 0 ? round($saleAmt / $saleData[$stockid]['avg_price'], 2) : 0;

    fputcsv($output, [
        $stockid,
        $stock['mnfCode'],
        $stock['mnfpno'],
        $stock['description'],
        $brandMap[$stockid]['name'] ?? 'N/A',       // column 5: Manufacturer name
        $brandMap[$stockid]['qohA'] ?? 0,            // column 6: Quantity on hand (qohA)
        $dcData[$stockid]['qty'] ?? 0,
        $dcData[$stockid]['amount'] ?? 0,
        $refQty[$stockid]['invoiceitemQty'] ?? 0,
        $refQty[$stockid]['invoiceexclusivegsttotalamount'] ?? 0,
        $refQty[$stockid]['averageInvoiceFactor'] ?? 0,
        $saleQty,
        $saleAmt,
        $saleFactor,
        $response4[$stockid]['qohB'] ?? 0,
        $stock['materialcost']
    ]);
}

fclose($output);
exit;
