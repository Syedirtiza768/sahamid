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

    $key = "companyDetails";

    $months = [];
    $salescases = [];
    $quotations = [];
    $sales = [];
    $recoveries = [];

    for ($month = -5; $month <= 0; $month++) {

        $startDate = date('Y-m-01', strtotime($month . " month", strtotime(date("F") . "1")));
        $endDate = date('Y-m-t', strtotime($month . " month", strtotime(date("F") . "1")));

        //Salescases
        $SQL = "SELECT * FROM salescase 
                        WHERE commencementdate >= '" . date('Y-m-01 00:00:00', strtotime($month . " month", strtotime(date("F") . "1"))) . "'
                        AND commencementdate <= '" . date('Y-m-t 23:23:59', strtotime($month . " month", strtotime(date("F") . "1"))) . "'";
        $salescases[] = mysqli_num_rows(mysqli_query($db, $SQL));

        //Quotation Values
        $SQL = 'SELECT salesorders.salescaseref, salesorderdetails.orderno, SUM(salesorderdetails.unitprice*
						(1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity) as value
					FROM salesorderdetails 
					INNER JOIN salesorderoptions ON (salesorderdetails.orderno = salesorderoptions.orderno 
						AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
					INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno
					INNER JOIN salesman ON salesman.salesmancode = salesorders.salesperson
            WHERE salesman.salesmanname IN(' . $salesman . ')
			AND salesorderdetails.lineoptionno = 0 
					AND salesorderoptions.optionno = 0
					AND salesorders.orddate >= "' . $startDate . '"
					AND salesorders.orddate <= "' . $endDate . '"';
        $quotation = mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'];
        $quotations[] = ($quotation ? round($quotation) : 0);

        //Sales
        $SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
            *invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman,
            invoice.services,invoice.gst
            FROM invoice 
            INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
            INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
            INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
                AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
                AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
            INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
            WHERE salesman.salesmanname IN(' . $salesman . ')
            AND invoice.returned = 0
                AND invoice.inprogress = 0
                AND invoiceoptions.invoiceoptionno = 0
                        AND invoice.invoicesdate >= '" . $startDate . "'
                        AND invoice.invoicesdate <= '" . $endDate . "'
                        GROUP BY invoice.invoiceno";
                        
                        
        $result = mysqli_query($db, $SQL);
        if ($result === FALSE) {
            $sales[] = 0;
        } else {
            $res = mysqli_query($db, $SQL);
            
                    $saleValue = 0;
            
                    while ($row = mysqli_fetch_assoc($res)) {
            
                        $percent = $row['services'] == 1 ? 1.16 : 1.17;
            
                        if ($row['gst'] == "inclusive") {
                            $saleValue += $row['price'] / $percent;
                        } else {
                            $saleValue += $row['price'];
                        }
                    }
            
                    $sales[] = ($saleValue ? round($saleValue) : 0);
        }

        $SQL = "SELECT transid_allocfrom as f,transid_allocto as t,datealloc as d,SUM(amt) as amt, dt.trandate as cd, 
                invoice.shopinvoiceno,invoiced.settled, invoiced.alloc as totalalloc, salesman.salesmanname,
                invoiced.ovamount as totalamt, debtorsmaster.name,invoice.invoicedate,invoice.invoicesdate
                FROM custallocns,debtortrans as invoiced
                INNER JOIN invoice on invoice.invoiceno = invoiced.transno
                INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoiced.debtorno
                INNER JOIN custbranch ON custbranch.branchcode = invoiced.branchcode
                INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
                INNER JOIN debtortrans as dt ON dt.debtorno = invoiced.debtorno
            WHERE salesman.salesmanname IN(' . $salesman . ')
            AND dt.trandate >= '$startDate'
                AND dt.trandate <= '$endDate'
                AND invoiced.id = transid_allocto
                AND dt.id = transid_allocfrom
                AND invoiced.type = 10
                AND invoiced.reversed = 0
                        
                        
                        
                        ";

$result = mysqli_query($db, $SQL);
if ($result === FALSE) {
    $recovery = 0;
} else {
    $recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];
}
        
        $recoveries[] = ($recovery ? round($recovery) : 0);

        $months[] = date("M-Y", strtotime($month . " month", strtotime(date("F") . "1")));
    }

    $response = [

        'months'  => $months,
        'data'  => [
            [
                'name' => 'Quotation',
                'data' => $quotations
            ],
            [
                'name' => 'Sales',
                'data' => $sales
            ],
            [
                'name' => 'Recoveries',
                'data' => $recoveries
            ]
        ]
    ];

    echo json_encode($response);
}
