<?php
	
	$PathPrefix='../../';
	
	include('../../quotation/misc.php');
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	$salesperson 	= $_GET['salesperson'];
	$cCode  	= $_GET['cCode'];
	$cName 	= str_replace(" ", "%", $_GET['cName']);
	$holdReason = [
		"1" => "Good History",
		"20" => "Watch",
		"51" => "In Liquidation"
	];

	if($salesman == "All"){
		$salesman = "";
	}
	
	$SQL = "SELECT debtorsmaster.debtorno, debtorsmaster.name,debtorsmaster.dba,debtorsmaster.ntn,debtorsmaster.salestaxinvoiceaddress ,debtorsmaster.gst 
				FROM debtorsmaster INNER JOIN custbranch ON debtorsmaster.debtorno=custbranch.debtorno 
				WHERE salesman like '$salesperson' AND debtorsmaster.debtorno like '%$cCode%' AND name like '%$cName%'
				GROUP BY debtorsmaster.debtorno";
	$resultDebtorInfo = mysqli_query($db, $SQL);
	/*$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100)) *invoicedetails.quantity)*invoiceoptions.quantity
	) as value, debtorsmaster.name, debtorsmaster.debtorno FROM invoice INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno 
	INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno AND 
	invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno) 
	INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoice.debtorno
	 INNER JOIN custbranch ON custbranch.debtorno=debtorsmaster.debtorno
	 WHERE salesman like '$salesperson' AND debtorsmaster.debtorno like '%$cCode%' AND name like '%$cName%'
	AND invoice.returned = 0 AND invoice.inprogress = 0 GROUP BY invoice.debtorno";

	$resultDebtorPaymentInfo = mysqli_query($db, $SQL);*/
	
/*	$responseDebtorInfo = [];*/
    $response=[];
	while($rowDebtorInfo = mysqli_fetch_assoc($resultDebtorInfo)){
    $debtorno = $rowDebtorInfo['debtorno'];
    $ntn = $rowDebtorInfo['ntn'];
    $gst = $rowDebtorInfo['gst'];
    $salestaxinvoiceaddress = $rowDebtorInfo['salestaxinvoiceaddress'];
    $rowDebtorInfo['ntn'] = "<input type='text' data-debtorno='$debtorno' data-old='$ntn' class='ntn' value='$ntn'>";
    $rowDebtorInfo['gst'] = "<input type='text' data-debtorno='$debtorno' data-old='$gst' class='gst' value='$gst'>";
    $rowDebtorInfo['salestaxinvoiceaddress'] = "<textarea data-debtorno='$debtorno' class='salestaxinvoiceaddress' data-old='$salestaxinvoiceaddress'>$salestaxinvoiceaddress</textarea>";
    $response[]=$rowDebtorInfo;
    }


	/*$response=[];
	foreach ($rowDebtorInfo as $key => $value) {
		$response[]=$value;
	}*/
	//$response=$responseDebtorInfo;
	//$response['salesVolume']=$responsePaymentInfo['value'];
	
	//$response[] = array_merge($responseDebtorInfo,$responsePaymentInfo);
	utf8_encode_deep($response);

	$fres = [];
	$fres['status'] = "success";
	$fres['data'] = $response;

	$eresponse = json_encode($fres);

	echo $eresponse;

	return;
	