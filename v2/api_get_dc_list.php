<?php
// api_get_dc_list.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ob_start();

include_once("config.php");

function sendJsonResponse($data, $statusCode = 200) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

try {
    if (!isset($_SESSION['UserID'])) {
        sendJsonResponse(['error' => 'Authentication required. Please login again.'], 401);
    }
    
    if (!userHasPermission($db, "show_dc_attachments_view")) {
        sendJsonResponse(['error' => 'Access denied'], 403);
    }
    
    if (!isset($_POST['from']) || !isset($_POST['to'])) {
        sendJsonResponse(['error' => 'From and To dates are required'], 400);
    }
    
    $from = mysqli_real_escape_string($db, $_POST['from']);
    $to = mysqli_real_escape_string($db, $_POST['to']);
    
    if (empty($from) || empty($to)) {
        sendJsonResponse(['error' => 'From and To dates are required'], 400);
    }
    
    if (!userHasPermission($db, "show_all_dcs_attachments")) {
        $SQL = 'SELECT dcs.orderno as dcno, dcs.orddate as date, dcs.salescaseref, debtorsmaster.name as client, debtorsmaster.dba 
                FROM dcs 
                INNER JOIN debtorsmaster on debtorsmaster.debtorno=dcs.debtorno
                INNER JOIN salescase ON dcs.salescaseref=salescase.salescaseref
                INNER JOIN salesman ON salescase.salesman=salesman.salesmanname
                WHERE (salesman.salesmanname ="' . $_SESSION['UsersRealName'] . '"
                OR salesman.salesmancode IN (SELECT salescase_permissions.user 
                FROM salescase_permissions WHERE salescase_permissions.can_access = "' . $_SESSION['UserID'] . '"))
                AND dcs.orddate >= "' . $from . '"
                AND dcs.orddate <= "' . $to . '"';
    } else {
        $SQL = 'SELECT dcs.orderno as dcno, dcs.orddate as date, dcs.salescaseref, debtorsmaster.name as client, debtorsmaster.dba 
                FROM dcs 
                INNER JOIN debtorsmaster on debtorsmaster.debtorno=dcs.debtorno
                WHERE dcs.orddate >= "' . $from . '"
                AND dcs.orddate <= "' . $to . '"';
    }
    
    $res = mysqli_query($db, $SQL);
    $response = [];
    
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $response[] = $row;
        }
        sendJsonResponse($response);
    } else {
        sendJsonResponse(['error' => 'Database query failed: ' . mysqli_error($db)], 500);
    }
    
} catch (Exception $e) {
    sendJsonResponse(['error' => 'Server error: ' . $e->getMessage()], 500);
}
?>