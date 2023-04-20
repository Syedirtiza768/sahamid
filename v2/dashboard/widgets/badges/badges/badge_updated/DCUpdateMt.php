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
    SELECT count(*) as count FROM dcs 
				INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
            WHERE salescase.salesman IN( $salesman)
			AND orddate <= '".date("Y-m-31")."'
			AND orddate >= '".date("Y-m-01")."
            AND salescase.debtorno LIKE 'MT%'";
    $result = mysqli_query($db, $SQL);
    
    if ($result === FALSE) {
        $salescaseCount  = 0;
    } else {
        $salescaseCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
    }

    echo $salescaseCount;
}
