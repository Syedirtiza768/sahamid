<?php
if (!isset($db)) {
    session_start();
    $db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
    return;
}
$key = "outstandingTotal";
if (isset($_POST['salesman'])) {
    $salesman = $_POST['salesman'];

    $SQL = "
    SELECT count(*) as count FROM salesorders 
    INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
    INNER JOIN www_users ON www_users.realname = salescase.salesman
            WHERE salescase.salesman IN( $salesman)
			AND orddate <= '".date("Y-m-t")."'
            AND orddate >= '".date("Y-m-01")."'";
    $result = mysqli_query($db, $SQL);
    
    if ($result === FALSE) {
        $salescaseCount  = 0;
    } else {
        $salescaseCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
    }

    echo $salescaseCount;
}
