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

    $response = [];

    $SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS totalBalance 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
                WHERE salesmanname IN(' . $salesman . ')
				AND debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"';

    $res = mysqli_query($db, $SQL);

    $response['status'] = 'success';
    $response['totalBalance'] = (round(mysqli_fetch_assoc($res)['totalBalance'], 2));

    $SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE shopsale.salesman IN(' . $salesman . ')
				AND debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) < 30';

    $res = mysqli_query($db, $SQL);
    $response['add'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

    $SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE shopsale.salesman IN(' . $salesman . ')
				AND debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) >= 30 
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) < 60';

    $res = mysqli_query($db, $SQL);
    $response['tdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

    $SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE shopsale.salesman IN(' . $salesman . ')
				AND debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) >= 60 
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) < 90';

    $res = mysqli_query($db, $SQL);
    $response['sdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

    $SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE shopsale.salesman IN(' . $salesman . ')
				AND debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) >= 90 
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) < 120';

    $res = mysqli_query($db, $SQL);
    $response['ndd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

    $SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE shopsale.salesman IN(' . $salesman . ')
				AND debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) > 120';

    $res = mysqli_query($db, $SQL);
    $response['otdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

    $res = [];

    $res[] = [
        '0-30 Days',
        $response['add']
    ];

    $res[] = [
        '30+ Days',
        $response['tdd']
    ];

    $res[] = [
        '60+ Days',
        $response['sdd']
    ];

    $res[] = [
        '90+ Days',
        $response['ndd']
    ];

    $res[] = [
        '120+ Days',
        $response['otdd']
    ];

    $res[] = [
        'Total',
        $response['totalBalance']
    ];
    echo json_encode($res);
}
