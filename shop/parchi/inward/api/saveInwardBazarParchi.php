<?php 

// Start output buffering to catch any unexpected output
ob_start();

$PathPrefix = "../../../../";
include "../../../../qrcode/qrlib.php";
include("../../../../includes/session.inc");
include('../../../../includes/SQL_CommonFunctions.inc');

if(!userHasPermission($db,"create_inward_parchi")){
    // Clear buffer and return JSON
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'User Does Not Have Access!!! Contact Admin.',
    ]);
    exit;
}

if(!isset($_POST['items']) || count($_POST['items']) < 1 || !isset($_POST['vendor']) || trim($_POST['vendor']) == "" || trim($_POST['obo']) == ""){
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing Parameters',
    ]);
    exit;
} 

$items = $_POST['items'];

$amount 	= 0;
$pass   	= true;
$message 	= "";

$on_behalf_of = trim($_POST['obo']);

foreach ($items as $item) {
    
    if(trim($item['name']) == ""){
        $pass = false;
        $message = "Empty Name for item passed...";
    }

    if($item['quantity'] <= 0){
        $pass = false;
        $message = "Item with 0 or less quantity found...";
    }
    
    if($item['model'] == ""){
        $pass = false;
        $message = "Item without modelno found...";
    }

    $amount += $item['price'];
}

if(!$pass){
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => $message,
    ]);
    exit;
}

DB_Txn_Begin($db);

$NIBPNo = GetNextTransNo(601, $db);

$SQL = "INSERT INTO bazar_parchi (type,transno,parchino,amount,svid,temp_vendor,user_id,created_at,updated_at,on_behalf_of)
        VALUES ('601','".$NIBPNo."','MPIW-".$NIBPNo."','".$amount."','','".trim($_POST['vendor'])."',
                '".$_SESSION['UserID']."','".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".htmlspecialchars($on_behalf_of,ENT_QUOTES)."')";
$result = DB_query($SQL, $db);
if(!$result) {
    DB_Txn_Rollback($db);
    ob_clean();
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to insert bazar parchi'
    ]);
    exit;
}

foreach ($items as $item) {
    
    $comment = "";
    if(trim($item['model']) != "")
        $comment = "Model No: ".htmlspecialchars(trim($item['model']),ENT_QUOTES);
    
    $SQL = "INSERT INTO bpitems (parchino,name,quantity,listprice,discount,price,created_at,updated_at,comments)
            VALUES ('MPIW-".$NIBPNo."','".addslashes($item['name'])."','".$item['quantity']."',
                    '".$item['listprice']."','".$item['discount']."','".$item['price']."',
                    '".date('Y-m-d H:i:s')."','".date('Y-m-d H:i:s')."','".$comment."')";
    $result = DB_query($SQL, $db);
    if(!$result) {
        DB_Txn_Rollback($db);
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to insert item: ' . $item['name']
        ]);
        exit;
    }
}

DB_Txn_Commit($db);

// QR Code Generation
$qrSuccess = false;
$qrMessage = "";

try {
    $data = [];
    $data['module'] = "MPIW";
    $data['code'] = 'MPIW-'.$NIBPNo;
    
    // Define paths
    $qrRelativePath = '../../../../qrcodes/bazar/MPIW/';
    $qrFilename = $NIBPNo . '-mpiwQR.png';
    $qrFullPath = $qrRelativePath . $qrFilename;
    
    // Create directory if it doesn't exist
    $qrDirectory = dirname($qrFullPath);
    if (!file_exists($qrDirectory)) {
        if (!mkdir($qrDirectory, 0777, true)) {
            throw new Exception("Failed to create directory: $qrDirectory");
        }
    }
    
    // Check if directory is writable
    if (!is_writable($qrDirectory)) {
        throw new Exception("Directory is not writable: $qrDirectory");
    }
    
    // Suppress any direct output from QRcode library
    ob_start();
    QRcode::png(json_encode($data), $qrFullPath, 'L', 14, 2);
    ob_end_clean();
    
    // Verify file was created
    if (file_exists($qrFullPath)) {
        $qrSuccess = true;
        $qrMessage = "QR code generated successfully";
    } else {
        throw new Exception("QR code file was not created");
    }
    
} catch (Exception $e) {
    $qrMessage = "QR generation failed: " . $e->getMessage();
    error_log($qrMessage);
}

// Clear output buffer before sending JSON
ob_clean();

// Prepare response
$response = [
    'status' => 'success',
    'message' => "Bazar Parchi created successfully.",
    'parchino' => 'MPIW-'.$NIBPNo
];

// Add QR code info to response
if ($qrSuccess) {
    $response['qr_code_path'] = 'qrcodes/bazar/MPIW/' . $qrFilename;
    $response['qr_message'] = $qrMessage;
} else {
    $response['qr_warning'] = $qrMessage;
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>