<?php
//$AllowAnyone = true;
//$PathPrefix='../../';
//include_once('../../includes/session.inc');
//include_once('../../includes/SQL_CommonFunctions.inc');

$arr=[];


//sales Person

$salesman=$_POST['salesperson'];

$startDate=$_POST['startdate'];
$toDate=$_POST['enddate'];;
$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='$salesman'";
$salesmanname=mysqli_fetch_assoc(mysqli_query($db,$SQL))['salesmanname'];

//Salesperson's Customer List
 $SQL="SELECT DISTINCT custbranch.debtorno,name,dba FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno=debtorsmaster.debtorno INNER JOIN salescase ON salescase.debtorno=debtorsmaster.debtorno INNER JOIN salesman ON salescase.salesman=salesman.salesmanname WHERE salesman.salesmancode='$salesman'";
$result=mysqli_query($db,$SQL);
$arr=[];
//$clientlist=[];
//Overall invoice value
	 $SQL = 'SELECT 
	SUM(CASE WHEN invoice.gst LIKE "%inclusive%" THEN 
	 (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity )*0.83 
	 ELSE (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity) END)
	 as overallInvoiceValue from invoicedetails INNER JOIN invoiceoptions on
	 (invoicedetails.invoiceno = invoiceoptions.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
	 INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno
	 INNER JOIN salescase on salescase.salescaseref=invoice.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE invoicedetails.lineoptionno = 0 
	 
	 and invoiceoptions.optionno = 0 
	 AND invoice.inprogress = 0
	 AND invoice.returned = 0
	 AND invoice.invoicesdate BETWEEN "'.$startDate.'" AND "'.$toDate.'"';

	$overallInvoiceValue=mysqli_fetch_assoc(mysqli_query($db,$SQL))['overallInvoiceValue'];
//	$arr['overallInvoiceValue']=$overallInvoiceValue;
//Salesperson Total invoice value
	$SQL = 'SELECT 
	SUM(CASE WHEN invoice.gst LIKE "%inclusive%" THEN  
	(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity )*0.83 
	ELSE (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity) END)
	 as salespersonTotalInvoiceValue from invoicedetails INNER JOIN invoiceoptions on
	 (invoicedetails.invoiceno = invoiceoptions.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
	 INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno
	 INNER JOIN salescase on salescase.salescaseref=invoice.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE invoicedetails.lineoptionno = 0 
	 
	 and invoiceoptions.invoiceoptionno = 0 
	 AND invoice.inprogress = 0
	 AND invoice.returned = 0
	 AND salescase.salesman = "'.$salesmanname.'"
	 AND invoice.invoicesdate BETWEEN "'.$startDate.'" AND "'.$toDate.'"';

	$salespersonTotalInvoiceValue=mysqli_fetch_assoc(mysqli_query($db,$SQL))['salespersonTotalInvoiceValue'];
	$arr['salespersonTotalInvoiceValue']=$salespersonTotalInvoiceValue;
	$arr['salespersonTotalInvoicePercentage']=$salespersonTotalInvoiceValue/$overallInvoiceValue*100;



while($row=mysqli_fetch_assoc($result))
{	
	$SQL = 'SELECT 
	SUM(CASE WHEN invoice.gst LIKE "%inclusive%" THEN  (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity )*0.83 ELSE (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity) END) as clientsTotalInvoiceValue from invoicedetails INNER JOIN invoiceoptions on
	 (invoicedetails.invoiceno = invoiceoptions.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
	 INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno
	 INNER JOIN salescase on salescase.salescaseref=invoice.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE invoicedetails.lineoptionno = 0 
	 AND invoice.inprogress = 0
	 AND invoice.returned = 0
	 and invoiceoptions.optionno = 0 
	 AND invoice.invoicesdate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	AND invoice.debtorno="'.$row['debtorno'].'"
	 
	 AND salescase.salesman = "'.$salesmanname.'"';
	 $clientsTotalInvoiceValue=mysqli_fetch_assoc(mysqli_query($db,$SQL))['clientsTotalInvoiceValue'];
	if($clientsTotalInvoiceValue==null)
		continue;

	$str=$row['debtorno'];
	//$clientlist[]=$row['debtorno'];
	$arr[$str]=[];
	$arr[$str]['client']=$row['name'];
	$arr[$str]['dba']=$row['dba'];
	$arr[$str]['clientsTotalInvoiceValue']= $clientsTotalInvoiceValue;
	$arr[$str]['clientsTotalInvoicePercentage']= $clientsTotalInvoiceValue/$salespersonTotalInvoiceValue*100;
	$arr[$str]['clientsOverallInvoicePercentage']= $clientsTotalInvoiceValue/$overallInvoiceValue*100;
	
}

//Customer's Brand List
//calculating total invoice amount

foreach ($arr as $key => $value) {
	//invoice details
	$SQL = 'SELECT manufacturers.manufacturers_id,manufacturers.manufacturers_name,stockmaster.materialcost,
	SUM(CASE WHEN invoice.gst LIKE "%inclusive%" THEN  (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity )*0.83 ELSE (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity) END) as invoiceValue from invoicedetails INNER JOIN invoiceoptions on
	 (invoicedetails.invoiceno = invoiceoptions.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
	 INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno
	 INNER JOIN salescase on salescase.salescaseref=invoice.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE invoicedetails.lineoptionno = 0 
	 
	 and invoiceoptions.optionno = 0
	 AND invoice.inprogress = 0
	 AND invoice.returned = 0
	 AND invoice.invoicesdate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	 AND salescase.salesman = "'.$salesmanname.'"
	 AND debtorsmaster.debtorno = "'.$key.'"
	 GROUP BY manufacturers.manufacturers_id
	 ORDER BY invoiceValue desc
	 
	 ';

	$result = mysqli_query($db,$SQL);

//$invoiceValue=0;
	//var i=0;
	while($row=mysqli_fetch_assoc($result))
	{	
/*
	$SQL = 'SELECT 
	SUM(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity
	 ) as invoiceValue from invoicedetails INNER JOIN invoiceoptions on
	 (invoicedetails.invoiceno = invoiceoptions.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) 
	 INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno
	 INNER JOIN salescase on salescase.salescaseref=invoice.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE invoicedetails.lineoptionno = 0 
	 
	 and invoiceoptions.optionno = 0 
	 AND invoice.invoicesdate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	AND invoice.debtorno="'.$key.'"
	 
	 AND salescase.salesman LIKE "%'.$salesmanname.'%"';

	
	$totalInvoice=mysqli_fetch_assoc(mysqli_query($db,$SQL))['invoiceValue'];
*/
	$commulative=0;
		
		$manufacturers_id=$row['manufacturers_id'];
		
		$invoiceValue=$row['invoiceValue'];

		//$arr[$key][$manufacturers_id]=[];
		$arr[$key][$manufacturers_id]['brand']=$row['manufacturers_name'];

		$arr[$key][$manufacturers_id]['invoiceValue']=$invoiceValue;
		$arr[$key][$manufacturers_id]['percentageSale']=$invoiceValue/$arr[$key]['clientsTotalInvoiceValue']*100;
		
		if($arr[$key][$manufacturers_id]['invoiceValue']>0){
		$SQLItems = 'SELECT stockmaster.stockid,stockmaster.mnfCode,stockmaster.mnfpno, stockmaster.description,manufacturers.manufacturers_name,
		AVG(invoicedetails.unitprice) as materialcost,
		AVG(invoicedetails.discountpercent*100) as averageInvoiceFactor,SUM(invoicedetails.quantity*invoiceoptions.quantity) as itemQty,

	 	SUM(CASE WHEN invoice.gst LIKE "%inclusive%" THEN  (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity )*0.83 ELSE (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity) END) as exclusivegsttotalamount from invoicedetails INNER JOIN invoiceoptions on (invoicedetails.invoiceno = invoiceoptions.invoiceno AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno INNER JOIN salescase on salescase.salescaseref=invoice.salescaseref INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno 
		 WHERE invoice.inprogress = 0
	 	AND invoice.returned = 0
		
		 AND invoice.invoicesdate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
		 AND salescase.salesman = "'.$salesmanname.'"
		 AND debtorsmaster.debtorno = "'.$key.'"
		 AND stockmaster.brand  = '.$row['manufacturers_id'].'
		 GROUP BY invoicedetails.stkcode
		 ORDER BY exclusivegsttotalamount desc
		
		 LIMIT 0,5';


		$resultItems = mysqli_query($db,$SQLItems);
		$topItems=[];
		while($rowItems=mysqli_fetch_assoc($resultItems))
		{	

			$topItems[]=$rowItems;
		
/*
		$SQLDCF = 'SELECT
		AVG(dcdetails.discountpercent*100) as averageDCFactor

	 	from dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) INNER JOIN dcs on dcs.orderno = dcdetails.orderno INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref INNER JOIN stockmaster on stockmaster.stockid=dcdetails.stkcode INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno 
		 WHERE dcdetails.lineoptionno = 0 
		 and dcoptions.optionno = 0 
		 AND dcs.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
		 AND salescase.salesman LIKE "%'.$salesmanname.'%"
		 AND debtorsmaster.debtorno LIKE "%'.$key.'%"
		 
		 AND dcdetails.stkcode = "'.$rowItems['stockid'].'"';
		 $DCF=mysqli_fetch_assoc(mysqli_query($db,$SQLDCF))['averageDCFactor'];
		 foreach ($topItems as $key3 => $value3) {
		 	 $topItems[$key3]['averageDCFactor']=$DCF;
		 }
		*/

		}
		
	

		$resultItems = mysqli_query($db,$SQLItems);




		
		$arr[$key][$manufacturers_id]['topItems']=$topItems;


		}


	}


	
//quotation value
	$SQL = '
	SELECT manufacturers.manufacturers_id,
	SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
	 ) as quotationValue from salesorderdetails INNER JOIN salesorderoptions on
	 (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
	 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=salesorders.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=salesorderdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE salesorderdetails.lineoptionno = 0 
	 AND salesorders.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	 
	 and salesorderoptions.optionno = 0 
	 AND salesorderdetails.orderno in 
	 (
		SELECT MAX(orderno) from salesorders group by salescaseref
	 
	 
	 )
	 AND salescase.salesman LIKE "%'.$salesmanname.'%"
	 AND debtorsmaster.debtorno = "'.$key.'"
	 
	 GROUP BY manufacturers.manufacturers_id';

	$result = mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($result))
	{
		
		$manufacturers_id=$row['manufacturers_id'];
		$quotationValue=$row['quotationValue'];
	//	$arr[$key][$manufacturers_id]=[];
		$arr[$key][$manufacturers_id]['quotationValue']=$quotationValue;
	}
//ocvalue
	
	$SQL = 'SELECT manufacturers.manufacturers_id,
	SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity
	 ) as ocValue from ocdetails INNER JOIN ocoptions on
	 (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
	 INNER JOIN ocs on ocs.orderno = ocdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=ocs.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=ocdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE ocdetails.lineoptionno = 0 
	 
	 and ocoptions.optionno = 0 
	 
	 AND ocs.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	 AND salescase.salesman LIKE "%'.$salesmanname.'%"
	 AND debtorsmaster.debtorno = "'.$key.'"
	 
	 GROUP BY manufacturers.manufacturers_id';
	 $result = mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($result))
	{
		
		$manufacturers_id=$row['manufacturers_id'];
		$ocValue=$row['ocValue'];

		//$arr[$key][$manufacturers_id]=[];
		$arr[$key][$manufacturers_id]['ocValue']=$ocValue;
	}
//dc value
	

$SQL = 'SELECT manufacturers.manufacturers_id,
	SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
	 ) as dcValue from dcdetails INNER JOIN dcoptions on
	 (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
	 INNER JOIN dcs on dcs.orderno = dcdetails.orderno
	 INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
	 INNER JOIN stockmaster on stockmaster.stockid=dcdetails.stkcode
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 WHERE dcdetails.lineoptionno = 0 
	 
	 and dcoptions.optionno = 0 
	 AND dcs.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	 AND salescase.salesman LIKE "%'.$salesmanname.'%"
	 AND debtorsmaster.debtorno = "'.$key.'"
	 
	 GROUP BY manufacturers.manufacturers_id';

	$result = mysqli_query($db,$SQL);

	while($row=mysqli_fetch_assoc($result))
	{
		
		$manufacturers_id=$row['manufacturers_id'];
		$dcValue=$row['dcValue'];

		//$arr[$key][$manufacturers_id]=[];
		$arr[$key][$manufacturers_id]['dcValue']=$dcValue;
	}




}


//$serialized = serialize($arr);
 

//echo json_encode($arr);



/*foreach ($arr as $key => $value) {
	echo $key;
}*/

//$serialized = serialize($arr);
 
//Save the serialized array to a text file.
//file_put_contents('serialized.txt', $serialized);
 
//Retrieve the serialized string.
//$fileContents = file_get_contents('serialized.txt');
 
//Unserialize the string back into an array.
//$arrayUnserialized = unserialize($fileContents);


 //$arr=[];
//$arr=$arrayUnserialized;

 //printr($arr); 
//End result.
//var_dump($arrayUnserialized);
//echo json_encode($arr);
/*
foreach ($arr as $i => $values) {
    print "$i {\n";
    foreach ($values as $key => $value) {
        print "    $key => $value\n";
    }
    print "}\n";
}


*/

//Retrieve the serialized string.
//$fileContents = file_get_contents('serialized.txt');
//echo "done";
 //exit;
//Unserialize the string back into an array.p
 /*
$arrayUnserialized = unserialize($fileContents);


$arr=[];
$arr=$arrayUnserialized;


?>	

<h1>Sales Person History</h1>
<h2><?php echo $salesmanname.' From '.$startDate.' To '.$toDate ?></h2>
<?php 

foreach($arr as $key=>$value){

$SQL = "SELECT dba FROM debtorsmaster WHERE debtorno='$key'";
$dba=mysqli_fetch_assoc(mysqli_query($db,$SQL))['dba'];
$SQL = "SELECT name FROM debtorsmaster WHERE debtorno='$key'";
$debtorname=mysqli_fetch_assoc(mysqli_query($db,$SQL))['name'];

echo '<center><h2>Top Selling Brands To '.$debtorname. '('.$dba.')</h2></center>';


$commulative=0;
/*
//echo '<center><h3>Top Selling Brands</h3></center>';
foreach ($value as $key2 => $value2) {
$SQL = "SELECT manufacturers_name FROM manufacturers WHERE manufacturers_id='$key2'";
$brand=mysqli_fetch_assoc(mysqli_query($db,$SQL))['manufacturers_name'];

$commulative=$commulative+(($value2['percentageSale'])*100);
echo '<center><h3>'.$brand.'</h3></center>';
echo '<center><table class="table-striped" border=1>';
echo '<tr><td>Quotation Value</td><td>OC Value</td><td>DC Value</td><td>Invoice Value</td><td>percentage Sale</td>
<td>Commulative</td></tr>';
if ($value2['invoiceValue']>0){
	echo'<tr><td>'.locale_number_format($value2['quotationValue']).'</td><td>'.locale_number_format($value2['ocValue']).'</td><td>'.locale_number_format($value2['dcValue']).'</td>

	<td>'.locale_number_format($value2['invoiceValue']).'</td><td>'.($value2['percentageSale']*100) .'</td><td>'.locale_number_format($commulative).'</td></tr>';
echo '</table></center>';
echo '<center><h3>Top Selling Items of [<b><i>'.$brand.'</b></i>] to ['.$debtorname.']</h3></center>';
echo '<center><table class="table-striped" border=1>
	
	<td>Stock ID</td><td>manufacturers Code</td><td>Part No.</td><td>Description</td><td>SP</td><td>Invoice Factor</td><td>Discount Factor</td>';
	for ($i=0;$i<count($value2['topItems']);$i++){

		
		echo '<tr><td>'.$value2['topItems'][$i]['stockid'].'</td><td>'.$value2['topItems'][$i]['mnfCode'].'</td><td>'.$value2['topItems'][$i]['mnfpno'].'</td>

		<td>'.$value2['topItems'][$i]['description'].'</td><td>'.locale_number_format($value2['topItems'][$i]['exclusivegsttotalamount']) .'</td><td>'.locale_number_format($value2['topItems'][$i]['averageInvoiceFactor']).'</td><td>'.locale_number_format($value2['topItems'][$i]['averageDiscountFactor']).'</td></tr>';
	}
echo '</table></center>';
}


}



}

*/

?>

















