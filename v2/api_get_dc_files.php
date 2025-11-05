<?php
// api_get_dc_files.php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production, 1 for debugging

// Start output buffering to prevent any accidental output
ob_start();

include_once("config.php");

// Helper function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    // Clear any previous output
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Helper function to process files
function processFiles($basePath, $filePrefix, $dcno, $fileType) {
    $fileList = "";
    try {
        $filePath = glob($basePath . $filePrefix . $dcno . '*.pdf');
        $ind = 0;
        
        foreach ($filePath as $file) {
            if (strpos($file, $dcno) !== false) {
                $ind++;
                if (strpos($file, "deleted") !== false) {
                    $fileList .= '<br /><a target="_blank" class="btn btn-danger col-md-12" style="margin:5px 0" href="' . $GLOBALS['RootPath'] . '/' . $file . '">Attachment' . $ind . "</a>";
                } else {
                    $fileList .= '<br /><a target="_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href="' . $GLOBALS['RootPath'] . '/' . $file . '">Attachment' . $ind . "</a> <input type='button' id='removeFile' data-basepath='" . $file . "' data-orderno='" . $dcno . "' class='btn btn-danger removeFile' name='removeFile' value='X'>";
                }
            }
        }
    } catch (Exception $e) {
        // Silently continue if file processing fails
        error_log("File processing error: " . $e->getMessage());
    }
    
    $uploadForm = "<form class='attachmentForm' enctype='multipart/form-data' method='post'>
        <input type='hidden' name='FormID' value='" . ($_SESSION['FormID'] ?? '') . "' />
        <input type='file' id='attach" . $fileType . "File" . $dcno . "' data-orderno='" . $dcno . "' class='attach" . $fileType . "File' name='" . $fileType . "'>
        <input type='button' id='upload" . $fileType . "File' data-orderno='" . $dcno . "' class='upload" . $fileType . "File' name='upload" . $fileType . "File' value='Upload'>
        </form>";
    
    return $uploadForm . $fileList;
}

// Main API logic
try {
    // Check if user is logged in
    if (!isset($_SESSION['UserID'])) {
        sendJsonResponse(['error' => 'Authentication required. Please login again.'], 401);
    }
    
    // Check permission
    if (!userHasPermission($db, "show_dc_attachments_view")) {
        sendJsonResponse(['error' => 'Access denied. You do not have permission to view DC attachments.'], 403);
    }
    
    if (!isset($_POST['dcno']) || empty($_POST['dcno'])) {
        sendJsonResponse(['error' => 'DC number is required'], 400);
    }
    
    $dcno = mysqli_real_escape_string($db, $_POST['dcno']);
    
    // Get GRB data
    $grbarray = [];
    $SQLgrb = "SELECT * FROM grb WHERE dcno = '$dcno'";
    $resgrb = mysqli_query($db, $SQLgrb);
    if ($resgrb) {
        while ($rowgrb = mysqli_fetch_assoc($resgrb)) {
            $grbarray[] = '<a target="_blank" href="../PDFGRB.php?grbno=' . $rowgrb['orderno'] . '">' . $rowgrb['orderno'] . '</a>';
        }
    }

    // Get invoice data
    $invoicearray = [];
    $SQLinvoice = "SELECT dcs.orderno, invoice.invoiceno 
                   FROM dcs 
                   INNER JOIN invoice ON dcs.invoicegroupid = invoice.groupid
                   WHERE dcs.orderno = '$dcno'";
    $resinvoice = mysqli_query($db, $SQLinvoice);
    if ($resinvoice) {
        while ($rowinvoice = mysqli_fetch_assoc($resinvoice)) {
            $invoicearray[] = '<a target="_blank" href="../PDFInvoice.php?InvoiceNo=' . $rowinvoice['invoiceno'] . '">' . $rowinvoice['invoiceno'] . '</a>';
        }
    }

    // Process file attachments
    $polist = processFiles("../" . $_SESSION['part_pics_dir'] . '/', 'PurchaseOrder_', $dcno, 'PO');
    $couriersliplist = processFiles("../" . $_SESSION['part_pics_dir'] . '/', 'CourierSlip_', $dcno, 'CourierSlip');
    $invoicelist = processFiles("../" . $_SESSION['part_pics_dir'] . '/', 'Invoice_', $dcno, 'Invoice');
    $commercialinvoicelist = processFiles("../" . $_SESSION['part_pics_dir'] . '/', 'CommercialInvoice_', $dcno, 'CommercialInvoice');
    $grblist = processFiles("../" . $_SESSION['part_pics_dir'] . '/', 'GRB_', $dcno, 'GRB');

    $response = [
        'success' => true,
        'polist' => $polist,
        'couriersliplist' => $couriersliplist,
        'oldinvoicelist' => $invoicelist,
        'oldcommercialinvoicelist' => $commercialinvoicelist,
        'oldgrblist' => $grblist,
        'invoicelist' => implode("<br/>", $invoicearray),
        'grblist' => implode("<br/>", $grbarray)
    ];

    sendJsonResponse($response);
    
} catch (Exception $e) {
    sendJsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
}
?>