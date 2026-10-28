<?php
include_once __DIR__ . '/../../../../../db_config.php';
if (!isset($db)) {
    session_start();
    $db = mysqli_connect(SAHAMID_DB_HOST, SAHAMID_DB_USER, SAHAMID_DB_PASSWORD, 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
    return;
}
$key = "outstandingTotal";
if (isset($_POST['salesman'])) {
    $salesman = $_POST['salesman'];

    $SQL = "SELECT count(*) as count FROM salescase 
            INNER JOIN www_users ON www_users.realname = salescase.salesman
            WHERE salescase.salesman IN( $salesman)
			AND commencementdate <= '2021-04-30 23:59:59'
			AND commencementdate >= '2021-04-01 00:00:00'
			AND debtorno LIKE 'MT%'";
    $result = mysqli_query($db, $SQL);
    
    if ($result === FALSE) {
        $salescaseCount  = 0;
    } else {
        $salescaseCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
    }

    echo $salescaseCount;
}
