<?php

if (!isset($db)) {
    session_start();
    $db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
    return;
}

if (isset($_POST['salesman']) || (isset($_POST['from']) && isset($_POST['to']))) {
    $salesman = $_POST['salesman'];
    $salesman = "'" . implode("', '", $salesman) . "'";
    $from = $_POST['from'];
    $to = $_POST['to'];


    // Salescase badge value update
    $SQL = "SELECT count(*) as count FROM salescase 
INNER JOIN www_users ON www_users.realname = salescase.salesman
WHERE salescase.salesman IN($salesman)
AND commencementdate >= '$from'
				AND commencementdate <= '$to'";
    $salescaseCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    $SQL = "SELECT count(*) as count FROM salescase 
	INNER JOIN www_users ON www_users.realname = salescase.salesman
	WHERE salescase.salesman IN($salesman)
	AND commencementdate >= '$from'
					AND commencementdate <= '$to'
AND debtorno LIKE 'SR%'";
    $salescaseCountSR = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];


    $SQL = "SELECT count(*) as count FROM salescase 
INNER JOIN www_users ON www_users.realname = salescase.salesman
WHERE salescase.salesman IN($salesman)
AND commencementdate >= '$from'
				AND commencementdate <= '$to'
AND debtorno LIKE 'MT%'";
    $salescaseCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];


    // Quotations badge value update
    $SQL = "SELECT count(*) as count FROM salesorders 
				INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
                WHERE salescase.salesman IN($salesman)
				AND orddate >= '$from'
				AND orddate <= '$to' ";
    $quotationCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    $SQL = "SELECT count(*) as count FROM salesorders 
				INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
                WHERE salescase.salesman IN($salesman)
				AND orddate >= '$from'
				AND orddate <= '$to' 
                AND salescase.debtorno LIKE 'SR%'";
    $quotationCountSR = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    $SQL = "SELECT count(*) as count FROM salesorders 
				INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
                WHERE salescase.salesman IN($salesman)
				AND orddate >= '$from'
				AND orddate <= '$to' 
                AND salescase.debtorno LIKE 'MT%'";
    $quotationCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    // Order Confirmation badge value update
    $SQL = "SELECT count(*) as count FROM ocs 
				INNER JOIN salescase ON salescase.salescaseref = ocs.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE salescase.salesman IN($salesman)
				AND orddate >= '$from'
				AND orddate <= '$to'";
    $ocCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    $SQL = "SELECT count(*) as count FROM ocs 
				INNER JOIN salescase ON salescase.salescaseref = ocs.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE salescase.salesman IN($salesman)
				AND orddate >= '$from'
				AND orddate <= '$to'
                AND salescase.debtorno LIKE 'SR%'";
    $ocCountSR = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    $SQL = "SELECT count(*) as count FROM ocs 
				INNER JOIN salescase ON salescase.salescaseref = ocs.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE salescase.salesman IN($salesman)
				AND orddate >= '$from'
				AND orddate <= '$to'
                AND salescase.debtorno LIKE 'MT%'";
    $ocCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    // Delivery Challan badge value update
    $SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate >= '$from'
			AND orddate <= '$to'
			AND salescase.salesman IN($salesman)";
    $dcCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    $SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate >= '$from'
			AND orddate <= '$to'
			AND salescase.salesman IN($salesman)
            AND salescase.debtorno LIKE 'SR%'";
    $dcCountSR = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    $SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate >= '$from'
			AND orddate <= '$to'
			AND salescase.salesman IN($salesman)
            AND salescase.debtorno LIKE 'MT%'";
    $dcCountMT = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

    // Pending Delivery Challan badge value update
    $SQL = "SELECT dcs.orderno as dcno,dcs.orddate as date,dcs.salescaseref,debtorsmaster.name as client,
			salescase.salesman,debtorsmaster.dba,SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	) as totalamount,dcs.gst, CASE  WHEN  dcs.gst LIKE '%inclusive%' THEN SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	)*0.83 ELSE SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
		 	)   END as exclusivegsttotalamount from dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
		INNER JOIN dcs on dcs.orderno = dcdetails.orderno
		INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
		INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
		WHERE salescase.salesman IN($salesman)
        AND dcdetails.lineoptionno = 0  
		AND dcoptions.optionno = 0 
		AND dcs.orddate >= '$from'
		AND dcs.orddate <= '$to'
		AND dcs.courierslipdate = '0000-00-00 00:00:00' AND dcs.invoicedate='0000-00-00 00:00:00' 
		AND dcs.grbdate='0000-00-00 00:00:00'
		AND dcs.invoicegroupid is null
		GROUP BY dcs.orderno
		";
    $pdcCount = NULL;
    $resp = mysqli_query($db, $SQL);
    while ($row = mysqli_fetch_assoc($resp)) {
        $pdcCount = $row['totalamount'] + $pdcCount;
    }
    $pdcCount = round($pdcCount, 0);


    // Total Score badge value update
    $SQL = "SELECT 
debtortrans.ovamount as price,
salesman.salesmanname,
invoice.invoiceno,
invoice.shopinvoiceno,
invoice.invoicedate,
invoice.invoicesdate,
debtorsmaster.name as client, 
debtorsmaster.dba,
invoice.services,
invoice.gst,
debtortrans.alloc,
debtortrans.settled
FROM invoice 
INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
INNER JOIN debtorsmaster ON invoice.debtorno = debtorsmaster.debtorno
INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
INNER JOIN debtortrans ON (debtortrans.type = 10
                            AND debtortrans.transno = invoice.invoiceno
                            AND debtortrans.reversed = 0)
WHERE salesman.salesmanname IN($salesman)
AND invoice.returned = 0
AND invoice.inprogress = 0
AND invoice.invoicesdate >= '$from'
AND invoice.invoicesdate <= '$to'
		";
    $totalScore = NULL;
    $resp = mysqli_query($db, $SQL);
    while ($row = mysqli_fetch_assoc($resp)) {
        $totalScore = $row['price'] + $totalScore;
    }
    $totalScore = round($totalScore, 0);


    // Total Sales Taregt badge value update
    $SQL = "SELECT * FROM salesman WHERE salesmanname IN($salesman)";
    $res = mysqli_query($db, $SQL);
    $salestarget  = NULL;
    while ($row = mysqli_fetch_assoc($res)) {
        $salestarget = $row['target'] + $salestarget;
    }
    $salestarget = round($salestarget, 0);

    // Total Outstanding badge value update
    $SQL = "SELECT  debtorsmaster.debtorno,
debtorsmaster.name,
debtorsmaster.dba,
invoice.shopinvoiceno,
invoice.invoicesdate,
invoice.branchcode,
invoice.invoiceno,
invoice.comment,
ROUND(debtortrans.ovamount) as total,
ROUND(debtortrans.alloc) as paid,
(
CASE WHEN GSTwithhold = 0 AND WHT = 0 
    THEN ovamount - alloc
WHEN GSTwithhold = 0 AND WHT = 1 
    THEN ovamount - alloc - WHTamt
WHEN GSTwithhold = 1 AND WHT = 0 
    THEN ovamount - alloc - GSTamt
WHEN GSTwithhold = 1 AND WHT = 1 
    THEN ovamount - alloc - GSTamt - WHTamt
END
) AS remaining  ,
salescase.salesman as salesperson,
invoice.salescaseref,
invoice.due,
invoice.expected,
debtortrans.state,
dcgroups.dcnos
FROM debtorsmaster
INNER JOIN invoice ON (invoice.debtorno = debtorsmaster.debtorno
AND invoice.returned = 0)
INNER JOIN salescase ON invoice.salescaseref=salescase.salescaseref
INNER JOIN dcgroups ON dcgroups.id = invoice.groupid
INNER JOIN debtortrans ON (debtortrans.transno = invoice.invoiceno AND debtortrans.type = 10
        AND debtortrans.reversed = 0
        AND debtortrans.settled = 0)
INNER JOIN www_users ON salescase.salesman = www_users.realname AND ( salescase.salesman IN( $salesman ))
		";

    $outstanding = NULL;
    $resp = mysqli_query($db, $SQL);
    if ($resp === FALSE) {
        $outstanding  = 0;
    } else {
        while ($row = mysqli_fetch_assoc($resp)) {
            $outstanding = $row['remaining'] + $outstanding;
        }
        $outstanding = round($outstanding, 0);

    }

    $data = array(
        'salescaseCount' => $salescaseCount,
        'salescaseCountSR' => $salescaseCountSR,
        'salescaseCountMT' => $salescaseCountMT,

        'quotationCount' => $quotationCount,
        'quotationCountSR' => $quotationCountSR,
        'quotationCountMT' => $quotationCountMT,

        'ocCount' => $ocCount,
        'ocCountSR' => $ocCountSR,
        'ocCountMT' => $ocCountMT,

        'dcCount' => $dcCount,
        'dcCountSR' => $dcCountSR,
        'dcCountMT' => $dcCountMT,

        'pdcCount' => $pdcCount,

        'totalScore' => $totalScore,

        'salestarget' => $salestarget,

        'outstanding' => $outstanding,
    );
    echo json_encode($data);
}
