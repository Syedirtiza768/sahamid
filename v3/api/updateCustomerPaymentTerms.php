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
	
	$SQL = "SELECT debtorsmaster.debtorno, debtorsmaster.name,debtorsmaster.dba, debtorsmaster.dueDays, 
				debtorsmaster.paymentExpected, debtorsmaster.creditlimit,	
				debtorsmaster.holdreason,debtorsmaster.disabletrans,debtorsmaster.disableshopdc 
				FROM debtorsmaster INNER JOIN custbranch ON debtorsmaster.debtorno=custbranch.debtorno 
				WHERE salesman like '$salesperson' AND debtorsmaster.debtorno like '%$cCode%' AND name like '%$cName%'
				GROUP BY debtorsmaster.debtorno";
	$resultDebtorInfo = mysqli_query($db, $SQL);
	$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100)) *invoicedetails.quantity)*invoiceoptions.quantity
	) as value, debtorsmaster.name, debtorsmaster.debtorno FROM invoice INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno 
	INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno AND 
	invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno) 
	INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoice.debtorno
	 INNER JOIN custbranch ON custbranch.debtorno=debtorsmaster.debtorno
	 WHERE salesman like '$salesperson' AND debtorsmaster.debtorno like '%$cCode%' AND name like '%$cName%'
	AND invoice.returned = 0 AND invoice.inprogress = 0 GROUP BY invoice.debtorno";

	$resultDebtorPaymentInfo = mysqli_query($db, $SQL);
	
	$responseDebtorInfo = [];

	while($rowDebtorInfo = mysqli_fetch_assoc($resultDebtorInfo)){

		$dueDays = $rowDebtorInfo['dueDays'];
		$expectedDays = $rowDebtorInfo['paymentExpected'];
		$debtorno = $rowDebtorInfo['debtorno'];
		$creditlimit = $rowDebtorInfo['creditlimit'];
		$holdreason = $rowDebtorInfo['holdreason'];
		$rowDebtorInfo['dueDays'] = "<input type='number' data-debtorno='$debtorno' data-old='$duedays' class='dueDays' value='$dueDays'>";
		$rowDebtorInfo['paymentExpected'] =  '<input type="number" class="expectedDays" data-debtorno = "'.$debtorno.'" name="expectedDays" value='.$expectedDays.'>';
		$rowDebtorInfo['creditlimit'] = "<input type='number' data-debtorno='$debtorno' data-old='$creditlimit' class='creditlimit' value='$creditlimit'>";
		$checked = (($rowDebtorInfo['disabletrans']==1) ? "checked":"");
		$rowDebtorInfo['disabletrans'] = "<input type='checkbox' id='disabletrans' data-debtorno='$debtorno' class='disabletrans' name='disabletrans' $checked value=1>";
        $checked = (($rowDebtorInfo['disableshopdc']==1) ? "checked":"");
        $rowDebtorInfo['disableshopdc'] = "<input type='checkbox' id='disableshopdc' data-debtorno='$debtorno' class='disableshopdc' name='disableshopdc' $checked value=1>";

        $rowDebtorInfo['holdreason']="<select style='width:100%; margin-bottom: 10px; color: #655E5D' data-debtorno='$debtorno' id='holdreason' class='holdreason' name='holdreason'>";
	        		
		foreach ($holdReason as $key => $value) {
		if($holdreason==$key)        
	     	$rowDebtorInfo['holdreason'].="<option value='$key' selected > $value </option>";
	 	else
	 		$rowDebtorInfo['holdreason'].="<option value='$key'> $value </option>";
		   
		}        	
		$rowDebtorInfo['holdreason'].="</select>";
	//	$rowDebtorInfo['holdreason'] = $holdReason[$rowDebtorInfo['holdreason']];
		
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['debtorno']=$rowDebtorInfo['debtorno'];
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['name']=$rowDebtorInfo['name'];
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['dba']=$rowDebtorInfo['dba'];
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['dueDays']=$rowDebtorInfo['dueDays'];
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['paymentExpected']=$rowDebtorInfo['paymentExpected'];
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['creditlimit']=$rowDebtorInfo['creditlimit'];
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['holdreason']=$rowDebtorInfo['holdreason'];
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['disabletrans']=$rowDebtorInfo['disabletrans'];
        $responseDebtorInfo[$rowDebtorInfo['debtorno']]['disableshopdc']=$rowDebtorInfo['disableshopdc'];
		$responseDebtorInfo[$rowDebtorInfo['debtorno']]['salesVolume']= 0;

		
		//$responseDebtorInfo[$rowDebtorInfo['debtorno']][]=$rowDebtorInfo;
		
	}
	
	//$responsePaymentInfo=[];
	while($rowDebtorPaymentInfo = mysqli_fetch_assoc($resultDebtorPaymentInfo)){
		$responseDebtorInfo[$rowDebtorPaymentInfo['debtorno']]['salesVolume']=$rowDebtorPaymentInfo['value'];
	}

	$response=[];
	foreach ($responseDebtorInfo as $key => $value) {
		$response[]=$value;
	}
	//$response=$responseDebtorInfo;
	//$response['salesVolume']=$responsePaymentInfo['value'];
	
	//$response[] = array_merge($responseDebtorInfo,$responsePaymentInfo);
	utf8_encode_deep($responseDebtorInfo);

	$fres = [];
	$fres['status'] = "success";
	$fres['data'] = $response;

	$eresponse = json_encode($fres);

	echo $eresponse;

	return;
	