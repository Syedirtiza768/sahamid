<?php

include('../../../configg.php');
session_start();
function GetNextTransNo($TransType, $conn)
{
    // Lock the table
    $lockSQL = "LOCK TABLES systypes WRITE";
    if (!mysqli_query($conn, $lockSQL)) {
        die("Error locking table: " . mysqli_error($conn));
    }
    // Get current transaction number
    $selectSQL = "SELECT typeno FROM systypes WHERE typeid = '" . mysqli_real_escape_string($conn, $TransType) . "'";
    $result = mysqli_query($conn, $selectSQL);
    if (!$result) {
        die("Error fetching transaction number: " . mysqli_error($conn));
    }
    $row = mysqli_fetch_row($result);
    $nextTransNo = $row[0] + 1;

    // Update transaction number
    $updateSQL = "UPDATE systypes SET typeno = '" . $nextTransNo . "' WHERE typeid = '" . mysqli_real_escape_string($conn, $TransType) . "'";
    if (!mysqli_query($conn, $updateSQL)) {
        die("Error updating transaction number: " . mysqli_error($conn));
    }

    // Unlock the table
    $unlockSQL = "UNLOCK TABLES";
    if (!mysqli_query($conn, $unlockSQL)) {
        die("Error unlocking table: " . mysqli_error($conn));
    }

    return $nextTransNo;
}



$client      = $_POST['client'];
$items       = $_POST['items'];
$payment     = $_POST['payment'];
$advance     = $_POST['advance'];
$ondelivery     = $_POST['ondelivery'];
$commision     = $_POST['commision'];
$paymentin     = $_POST['paymentin'];
$expected     = $_POST['expected'];
$crname      = $_POST['name'];
$discount      = $_POST['discount'];
$discountPKR = $_POST['discountPKR'];
$showTotalOption = $_POST['showTotalOption'];
$dispatched = $_POST['dispatchvia'];
$creferance = $_POST['creferance'];
$paid         = $_POST['paid'];


if (!isset($items) || count($items) <= 0 || !isset($client)) {
    $response = [
        'status' => 'error',
        'message' => 'missing parms'
    ];

    echo json_encode($response);
    return;
}




//create client & verify its created
$newClientID = "SR-7" . GetNextTransNo(701, $conn);


//Client
$SQL = "INSERT INTO estimatedebtormaster (debtorno,name,address1,address2,address3,address4,address5,address6,currcode,
							clientsince,holdreason,paymentterms,discount,discountcode,pymtdiscount,creditlimit,
							salestype,invaddrbranch,taxref,customerpoline,typeid,dba,language_id)
				VALUES ('$newClientID','" . $client['name'] . "','" . $client['address1'] . "','',
						'','','','','PKR','" . date('Y-m-d H:i:s') . "','1','CA','0','','0','1000',
						'11','0','','0','" . $client['ctype'] . "','" . $client['dba'] . "','en_GB.utf8')";

if (!mysqli_query($conn, $SQL)) {

    $response = [
        'status' => 'error',
        'message' => 'Client Creation Failed'
    ];

    echo json_encode($response);
    return;
}

//Branch
$SQL = "INSERT INTO estimatecustbranch (branchcode,debtorno,brname,braddress1,braddress2,braddress3,braddress4,
							braddress5,braddress6,specialinstructions,estdeliverydays,salesman,phoneno,faxno,
							contactname,area,email,defaultlocation,brpostaddr1,brpostaddr2,brpostaddr3,
							brpostaddr4,brpostaddr5,disabletrans,defaultshipvia,custbranchcode,deliverblind)
				VALUES ('$newClientID','$newClientID','" . $client['name'] . "','" . $client['address1'] . "',
						'','','','','','','0','" . $client['salesman'] . "',
						'" . $client['phone'] . "','','','15','','MT','','','','','','0','1','','1')";

if (!mysqli_query($conn, $SQL)) {
    $response = [
        'status' => 'error',
        'message' => 'Branch Creation Failed'
    ];

    echo json_encode($response);
    return;
}

$customer = [];
$customer['debtorno']     = $newClientID;
$customer['branchcode'] = $newClientID;
$customer['salesman']     = $client['salesman'];
$customer['ref'] = !empty($client['reference']) ? $client['reference'] : "None";


// $SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='" . $customer['salesman'] . "'";
// $customer['salesman'] = mysqli_fetch_assoc(mysqli_query($conn, $SQL))['salesmanname'];

// if ($payment == "csv") {
//     $customer['salesman'] = $_POST['salesman'];
// }

$selectedDebtorNo = $customer['debtorno'];

$SQL = "SELECT dueDays, paymentExpected FROM estimatedebtormaster WHERE debtorno = '$selectedDebtorNo'";
$debtorsMaster = mysqli_fetch_assoc(mysqli_query($conn, $SQL));
$cDate = date('Y-m-d');
$dueDays = date('Y-m-d', strtotime(" + " . $debtorsMaster['dueDays'] . " days"));
$expectedDays = date('Y-m-d', strtotime(" + " . $debtorsMaster['paymentExpected'] . " days"));

//Create Bill Here
$billId = GetNextTransNo(750, $conn);
// $PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $conn);

$SQL = "INSERT INTO estimateshopsale(orderno, debtorno, branchcode, orddate, payment, salesman, advance,ondelivery, commision, paymentin, expectedin,
						crname,discount,showTotalOption,created_by,dispatchedvia,customerref,paid,discountPKR,accounts,due,expected) 
			VALUES ('$billId','" . $customer['debtorno'] . "','" . $customer['branchcode'] . "','" . date('Y-m-d') . "',
					'$payment','" . $customer['salesman'] . "','" . $advance . "','" . $ondelivery . "','" . $commision . "','" . $paymentin . "','" . $expected . "','" . htmlentities($client['name'], ENT_QUOTES) . "',
					'" . $discount . "','" . $showTotalOption . "','" . $_SESSION['UsersRealName'] . "','" . htmlentities($dispatched, ENT_QUOTES) . "','" . $customer['ref'] . "','" . $paid . "','" . $discountPKR . "',0,'$dueDays','$expectedDays')";

if (!mysqli_query($conn, $SQL)) {

    $response = [
        'status' => 'error',
        'message' => $SQL
    ];

    echo json_encode($response);
    return;
}

$totalValue = 0;

foreach ($items as $item) {

    $item['desc'] = str_replace("&#10;", "<br>", $item['desc']);
    $item['note'] = str_replace("&#10;", "<br>", $item['note']);

    $item['desc'] = htmlentities($item['desc'], ENT_QUOTES);
    $item['note'] = htmlentities($item['note'], ENT_QUOTES);

    $totalValue += $item['price'] * $item['quantity'];

    $SQL = "INSERT INTO estimateshopsalelines (orderno, description, notes, quantity, price, uom, deliveryStatus) 
				VALUES ('$billId','" . $item['desc'] . "','" . $item['note'] . "','" . $item['quantity'] . "',
						'" . $item['price'] . "','" . $item['uom'] . "' ,'" . $item['deliveryStatus'] . "')";

    if (!mysqli_query($conn, $SQL)) {

        $response = [
            'status' => 'error',
            'message' => 'Line Creation Failed'
        ];

        echo json_encode($response);
        return;
    }
}


$data = [];
$data['module'] = "shopsale";
$data['code'] = $billId;

// QRcode::png(json_encode($data), '../../../qrcodes/shopsale/' . $billId . '-shopSaleQR.png', 'L', 14, 2);

$response = [
    'status' => 'success',
    'code' => $billId
];

echo json_encode($response);
return;
