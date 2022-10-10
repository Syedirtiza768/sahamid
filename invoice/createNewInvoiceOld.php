<?php

	if (!isset($PathPrefix)) {
		$PathPrefix='../';
	}
	
	include('misc.php');
	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');


	$dcnostr = ",".$_GET['dcno'];
	$dcnos = $_GET['dcno'];
	$dcnos = explode(",",$dcnos);
	array_pop($dcnos);

	$OrdersToMakeInvoiceFor = '';
	
	if(count($dcnos) == 0){
		echo [ "status" => "error" ];
		return;
	}

	$db = createDBConnection();

	$pass = true;
	
	foreach($dcnos as $dcno){

		$SQL = "SELECT count(*) as count FROM dcgroups WHERE dcnos LIKE '%,".$dcno.",%'";
		$res = mysqli_query($db, $SQL);
		$count = mysqli_fetch_assoc($res)['count'];

		if($count != 0){
			$pass = false;
			break;
		}

		if ($OrdersToMakeInvoiceFor==''){
			$OrdersToMakeInvoiceFor .= " orderno='" . $dcno . "'";
		} else {
			$OrdersToMakeInvoiceFor .= " OR orderno='" . $dcno . "'";
		}
			
	}

	if(!$pass){
		echo json_encode([ "status" => "error" ]);
		return;
	}

	$SQL = "INSERT INTO dcgroups(dcnos) VALUES ('".$dcnostr."')";
	mysqli_query($db, $SQL);
	
	$groupID = mysqli_insert_id($db);

	$RequestNo = GetNextTransNo(10, $db);
	
	//invoice table insert
	$SQL="INSERT INTO `invoice`(`salescaseref`, `pono`, `debtorno`,
	 `branchcode`, `customerref`, `buyername`, `comments`,  `ordertype`, 
	 `shipvia`, `deladd1`, `deladd2`, `deladd3`, `deladd4`, `deladd5`, `deladd6`,
	 `contactphone`, `contactemail`, `deliverto`, `deliverblind`, `freightcost`, 
	 `gst`, `fromstkloc`, `salesperson`)
	 SELECT DISTINCT `salescaseref`, `customerref`,`debtorno`,
		`branchcode`, `customerref`,  `buyername`, `comments`,
		`ordertype`, `shipvia`, `deladd1`, `deladd2`, `deladd3`,
		`deladd4`, `deladd5`, `deladd6`, `contactphone`, 
		`contactemail`, `deliverto`, `deliverblind`, `freightcost`, 
		 `gst`, 
		 `fromstkloc`,  `salesperson` FROM dcs WHERE 
		".$OrdersToMakeInvoiceFor." LIMIT 0,1";
		
	DB_query($SQL,$db);
	$invoiceID = mysqli_insert_id($db);
	
	//invoice table invoice related update
	$SQL="UPDATE invoice SET inprogress=1,invoiceno = ".$RequestNo.", groupid='".$groupID."' WHERE invoiceno=0";
	DB_query($SQL,$db);

	foreach($dcnos as $dcno){
		$SQL = "UPDATE dcs SET invoicegroupid='".$groupID."' WHERE orderno='".$dcno."'";
		DB_query($SQL,$db);
	}
	
	
	//------------------------------------------------------------------------------------
//invoicedcs table insert
//------------------------------------------------------------------------------------
// invoicedetails table insert	
$SQL="
SELECT * FROM dcoptions WHERE quantity-qtyinvoiced=0";
$result=DB_query($SQL,$db);

$SQL="INSERT INTO `invoicedetails`(`orderlineno`,
 `orderno`, `lineoptionno`, `optionitemno`,`invoiceoptionitemno`,
 `internalitemno`,  `stkcode`,
 `quantityoc`, `unitprice`, `quantity`, `estimate`, `discountpercent`,
 `actualdispatchdate`, `completed`, `narrative`, `itemdue`, `poline`)
SELECT `orderlineno`, `orderno`, `lineoptionno`, 
	`optionitemno`,`optionitemno`, `internalitemno`, `stkcode`, `quantityoc`, `unitprice`, `quantity`,
	`estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`,
	`poline`FROM dcdetails
	WHERE 
	".$OrdersToMakeInvoiceFor."
 ";
 
DB_query($SQL,$db);
//-----------------------------------------------------------------------------------
//invoicedetails invoice related update
$SQL="UPDATE invoicedetails SET invoiceno = ".$RequestNo." WHERE ".$OrdersToMakeInvoiceFor."AND invoiceno=0";
DB_query($SQL,$db);

//-----------------------------------------------------------------------------------
//invoicelines table insert
$SQL="INSERT INTO `invoicelines`(`orderno`,
 `lineno`, `clientrequirements`) 
 SELECT `orderno`, `lineno`, `clientrequirements` FROM dclines
	WHERE 
	".$OrdersToMakeInvoiceFor."
	
 ";
	
	DB_query($SQL,$db);
//------------------------------------------------------------------------------------	
//invoicelines invoice related update
$SQL="UPDATE invoicelines SET invoiceno = ".$RequestNo." WHERE ".$OrdersToMakeInvoiceFor." AND invoiceno=0";
DB_query($SQL,$db);

//-----------------------------------------------------------------------------------
//invoiceoptions table insert
	$SQL = "INSERT INTO `invoiceoptions`( `orderno`, `lineno`, `optionno`,
	`optiontext`, `stockstatus`, `quantity`)
	SELECT `orderno`, `lineno`, `optionno`, `optiontext`,
	`stockstatus`, `quantity`-`qtyinvoiced` FROM dcoptions WHERE 
	(".$OrdersToMakeInvoiceFor.")
	AND quantity-qtyinvoiced>0
	";
	DB_query($SQL,$db);
//invoiceoptions invoice related update

$SQL="UPDATE invoiceoptions SET invoiceno = ".$RequestNo." WHERE ".$OrdersToMakeInvoiceFor."AND invoiceno=0";
DB_query($SQL,$db);

//-----------------------------------------------------------------------------------
	
//reindexing
$SQLD = "

			UPDATE invoicedetails
			SET invoiceinternalitemno = (SELECT (@rownum := @rownum + 1) FROM (SELECT @rownum := -1) r) where 
			invoiceno='" . $RequestNo . "'";
			DB_query($SQLD,$db);	
			$SQLD = "UPDATE invoicelines
			SET invoicelineno = (SELECT (@rownum := @rownum + 1) FROM (SELECT @rownum := -1) r) where 
			invoiceno='" . $RequestNo . "'";
			DB_query($SQLD,$db);

//$SQL ="SELCT orderno FROM invoice WHERE 
//	".$OrdersToMakeInvoiceFor." ";
//$result=	DB_query($SQL,$db);
//WHILE ($row=DB_fetch_array($result))
//{
	
	$SQL="UPDATE invoicedetails SET invoicelineno=(
	SELECT DISTINCT invoicelineno FROM invoicelines
	WHERE invoicedetails.orderno=invoicelines.orderno
	AND invoicedetails.orderlineno=invoicelines.lineno LIMIT 0,1
	
	
	)";
	DB_query($SQL,$db);
$SQL="UPDATE invoiceoptions SET invoicelineno=(
	SELECT invoicelineno FROM invoicelines
	WHERE invoiceoptions.orderno=invoicelines.orderno
	AND invoiceoptions.lineno=invoicelines.lineno
	AND invoiceoptions.invoiceno=invoicelines.invoiceno
	AND invoiceoptions.invoiceno='" . $RequestNo . "'
	)
	WHERE invoiceoptions.invoiceno='" . $RequestNo . "'
	";
	DB_query($SQL,$db);
	$SQL="DELETE FROM invoicedetails WHERE invoicelineno NOT IN (SELECT DISTINCT invoicelineno 
FROM invoiceoptions WHERE invoiceno=".$RequestNo.")";
DB_query($SQL,$db);
	$SQL="DELETE FROM invoicelines WHERE invoicelineno NOT IN (SELECT DISTINCT invoicelineno 
FROM invoiceoptions WHERE invoiceno=".$RequestNo.")";
DB_query($SQL,$db);
$SQLD = "

			UPDATE invoicedetails
			SET invoiceinternalitemno = (SELECT (@rownum := @rownum + 1) FROM (SELECT @rownum := -1) r) where 
			invoiceno='" . $RequestNo . "'";
			DB_query($SQLD,$db);	
			$SQLD = "UPDATE invoicelines
			SET invoicelineno = (SELECT (@rownum := @rownum + 1) FROM (SELECT @rownum := -1) r) where 
			invoiceno='" . $RequestNo . "'";
			DB_query($SQLD,$db);

//$SQL ="SELCT orderno FROM invoice WHERE 
//	".$OrdersToMakeInvoiceFor." ";
//$result=	DB_query($SQL,$db);
//WHILE ($row=DB_fetch_array($result))
//{
	
	$SQL="UPDATE invoicedetails SET invoicelineno=(
	SELECT DISTINCT invoicelineno FROM invoicelines
	WHERE invoicedetails.orderno=invoicelines.orderno
	AND invoicedetails.orderlineno=invoicelines.lineno LIMIT 0,1
	
	
	)";
	DB_query($SQL,$db);
/*	$SQL="UPDATE invoiceoptions SET invoicelineno=(
	SELECT invoicelineno FROM invoicelines
	WHERE invoiceoptions.orderno=invoicelines.orderno
	AND invoiceoptions.lineno=invoicelines.lineno
	AND invoiceoptions.invoiceno=invoicelines.invoiceno
	
	)
	WHERE invoiceoptions.invoiceno='" . $RequestNo . "'
	";
	DB_query($SQL,$db);	*/
	$SQL="SELECT stockcategory.categorydescription FROM invoicedetails INNER JOIN
	stockmaster ON invoicedetails.stkcode=stockmaster.stockid INNER JOIN 
	stockcategory ON stockmaster.categoryid=stockcategory.categoryid
	WHERE invoicedetails.invoiceno=".$RequestNo."
 	";
 	$result=DB_query($SQL,$db);
 	$categorytext="";
 	WHILE ($myrow=DB_fetch_array($result))
 	{
	 $categorytext.=$myrow['categorydescription']."<br/>";
	 
 	}
 	$SQL="UPDATE invoicelines SET clientrequirements='".$categorytext."'";
	DB_query($SQL,$db);
	
	
	echo json_encode([
						'status' => 'redirect',
						'invoiceno' => $RequestNo
					]);
	

?>