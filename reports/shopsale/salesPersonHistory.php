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
$SQL="SELECT DISTINCT custbranch.debtorno,name,dba FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno=debtorsmaster.debtorno INNER JOIN shopsale ON shopsale.debtorno=debtorsmaster.debtorno INNER JOIN salesman ON shopsale.salesman=salesman.salesmanname WHERE salesman.salesmancode='$salesman'";
$result=mysqli_query($db,$SQL);
$arr=[];
//$clientlist=[];
//Overall shopsale value
$SQL = 'SELECT 
	SUM((shopsalesitems.quantity*shopsalesitems.rate*(1-shopsalesitems.discountpercent/100))*shopsalelines.quantity) as overallInvoiceValue from shopsalesitems 
	 INNER JOIN shopsale on shopsale.orderno = shopsalesitems.orderno
	 INNER JOIN stockmaster on stockmaster.stockid=shopsalesitems.stockid
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN shopsalelines ON (shopsalesitems.orderno=shopsalelines.orderno AND 
		shopsalesitems.lineno=shopsalelines.id)
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno
	 WHERE shopsale.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"';
$overallInvoiceValue=mysqli_fetch_assoc(mysqli_query($db,$SQL))['overallInvoiceValue'];
//	$arr['overallInvoiceValue']=$overallInvoiceValue;
//Salesperson Total shopsale value
$SQL = 'SELECT 
	SUM((shopsalesitems.quantity*shopsalesitems.rate*(1-shopsalesitems.discountpercent/100))*shopsalelines.quantity) as salespersonTotalInvoiceValue from shopsalesitems 
	 INNER JOIN shopsale on shopsale.orderno = shopsalesitems.orderno
	 INNER JOIN stockmaster on stockmaster.stockid=shopsalesitems.stockid
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno
	 INNER JOIN shopsalelines ON (shopsalesitems.orderno=shopsalelines.orderno AND 
		shopsalesitems.lineno=shopsalelines.id)
	 WHERE shopsale.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	 	 AND shopsale.salesman = "'.$salesmanname.'"';

$salespersonTotalInvoiceValue=mysqli_fetch_assoc(mysqli_query($db,$SQL))['salespersonTotalInvoiceValue'];
$arr['salespersonTotalInvoiceValue']=$salespersonTotalInvoiceValue;
$arr['salespersonTotalInvoicePercentage']=$salespersonTotalInvoiceValue/$overallInvoiceValue*100;



while($row=mysqli_fetch_assoc($result))
{	$SQL = 'SELECT 
	SUM((shopsalesitems.quantity*shopsalesitems.rate*(1-shopsalesitems.discountpercent/100))*shopsalelines.quantity) as clientsTotalInvoiceValue from shopsalesitems 
	 INNER JOIN shopsale on shopsale.orderno = shopsalesitems.orderno
	 INNER JOIN stockmaster on stockmaster.stockid=shopsalesitems.stockid
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno
	 INNER JOIN shopsalelines ON (shopsalesitems.orderno=shopsalelines.orderno AND 
		shopsalesitems.lineno=shopsalelines.id)
	 WHERE shopsale.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	AND shopsale.debtorno="'.$row['debtorno'].'"
	AND shopsale.salesman = "'.$salesmanname.'"';
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
	//if($arr[$key]['clientsTotalInvoiceValue']==null OR $arr[$key]['clientsTotalInvoiceValue']==0 )
	//	continue;
	$SQL = 'SELECT manufacturers.manufacturers_id,manufacturers.manufacturers_name,stockmaster.materialcost,
	SUM((shopsalesitems.quantity*shopsalesitems.rate*(1-shopsalesitems.discountpercent/100))*shopsalelines.quantity) as invoiceValue from shopsalesitems 
	 INNER JOIN shopsale on shopsale.orderno = shopsalesitems.orderno
	 INNER JOIN stockmaster on stockmaster.stockid=shopsalesitems.stockid
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 INNER JOIN shopsalelines ON (shopsalesitems.orderno=shopsalelines.orderno AND 
		shopsalesitems.lineno=shopsalelines.id)
	 INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno
	 WHERE shopsale.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
	AND shopsale.salesman = "'.$salesmanname.'"
	AND debtorsmaster.debtorno = "'.$key.'"
	GROUP BY manufacturers.manufacturers_id
	ORDER BY invoiceValue desc
	';


	$result = mysqli_query($db,$SQL);
	while($row=mysqli_fetch_assoc($result))
	{
		if($arr[$key]['clientsTotalInvoiceValue']==null OR $arr[$key]['clientsTotalInvoiceValue']==0 )
			continue;
		$commulative=0;

		$manufacturers_id=$row['manufacturers_id'];

		$invoiceValue=$row['invoiceValue'];

		$arr[$key][$manufacturers_id]['brand']=$row['manufacturers_name'];

		$arr[$key][$manufacturers_id]['invoiceValue']=$invoiceValue;
		$arr[$key][$manufacturers_id]['percentageSale']=$invoiceValue/$arr[$key]['clientsTotalInvoiceValue']*100;

		if($arr[$key][$manufacturers_id]['invoiceValue']>0){
//first calculate the  discount factor and then apply it to material cost
 			$SQLItems = 'SELECT stockmaster.stockid,stockmaster.mnfCode,stockmaster.mnfpno, stockmaster.description,manufacturers.manufacturers_name,
		AVG(shopsalesitems.rate) as materialcost,
		AVG(shopsalesitems.discountpercent) as discountpercent,
		SUM(shopsalesitems.quantity) as itemQty,
		AVG(shopsale.discount) as averageInvoiceFactor,
		SUM((shopsalesitems.quantity*shopsalesitems.rate*(1-shopsalesitems.discountpercent/100))*shopsalelines.quantity) as 
		exclusivegsttotalamount from shopsalesitems 
		INNER JOIN shopsalelines ON (shopsalesitems.orderno=shopsalelines.orderno AND 
		shopsalesitems.lineno=shopsalelines.id)
		INNER JOIN shopsale on shopsale.orderno = shopsalesitems.orderno
	   	INNER JOIN stockmaster on stockmaster.stockid=shopsalesitems.stockid
	 	INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 	INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno
		WHERE 
		shopsale.orddate BETWEEN "'.$startDate.'" AND "'.$toDate.'"
		AND shopsale.salesman = "'.$salesmanname.'"
		AND debtorsmaster.debtorno = "'.$key.'"
		AND stockmaster.brand  = '.$row['manufacturers_id'].'
		GROUP BY shopsalesitems.stockid
		ORDER BY exclusivegsttotalamount desc
		
		 LIMIT 0,5';


			$resultItems = mysqli_query($db,$SQLItems);
			$topItems=[];
			while($rowItems=mysqli_fetch_assoc($resultItems))
			{

				$topItems[]=$rowItems;

			}



			$resultItems = mysqli_query($db,$SQLItems);





			$arr[$key][$manufacturers_id]['topItems']=$topItems;


		}


	}


	/*
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
         INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno
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
         INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno
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

    $SQL = 'SELECT manufacturers.manufacturers_id,
        SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
         ) as dcValue from dcdetails INNER JOIN dcoptions on
         (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno)
         INNER JOIN dcs on dcs.orderno = dcdetails.orderno
         INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
         INNER JOIN stockmaster on stockmaster.stockid=dcdetails.stkcode
         INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
         INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno
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
            $arr[$key][$manufacturers_id]['dcValue']=$dcValue;
        }


    */
}


?>

















