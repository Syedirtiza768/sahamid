<?php

include('includes/session.inc');
?>
<?php
/* $Id: header.inc 6310 2013-08-29 10:42:50Z daintree $ */

	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $RootPath
	//  $Title - should be defined in the page this file is included with
	if (!isset($RootPath)){
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		if ($RootPath == '/' OR $RootPath == "\\") {
			$RootPath = '';
		}
	}

	$ViewTopic = isset($ViewTopic)?'?ViewTopic=' . $ViewTopic : '';
	$BookMark = isset($BookMark)? '#' . $BookMark : '';
	$StrictXHTML=False;

	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}
	}
	if($Title == _('Copy a BOM to New Item Code')){//solve the cannot modify heaer information in CopyBOM.php scritps
		ob_start();
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title> S A HAMID ERP</title>';
	//echo '<link rel="shortcut icon" href="'. $RootPath.'/favicon.ico" />';
	//echo '<link rel="icon" href="' . $RootPath.'/favicon.ico" />';
	if ($StrictXHTML) {
		echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
	} else {
		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
	}
	echo '<link href="' . $RootPath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
	echo '<link href="' . $RootPath . '/css/'. $_SESSION['Theme'] .'/foundation.css" rel="stylesheet" type="text/css" />';
	
	echo '<script type="text/javascript" src = "'.$RootPath.'/javascripts/MiscFunctions.js"></script>';
		$db=mysqli_connect('localhost','irtiza','netetech321','sahamid');
//echo $_POST['filtertype'];
		$SQL = '
		CREATE TABLE IF NOT EXISTS `salescasereporting'.$_SESSION['UserID'].'` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL ,
  `branchcode` varchar(10) NOT NULL ,
  `commencementdate` datetime NOT NULL,
  `prospect` varchar(100) NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `enquiryfile` varchar(50) NOT NULL,
  `enquirydate` datetime NOT NULL,
  `lastquotationdate` datetime NOT NULL,
  `orderno` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `pofile` varchar(50) NOT NULL,
  `podate` datetime NOT NULL,
  `ocdocumentfile` varchar(50) NOT NULL,
  `ocdocumentdate` datetime NOT NULL,
  `dclink` int(11) NOT NULL,
	`closingreason` text,
  `commentcode` int(40) NOT NULL
) 
';
mysqli_query($db,$SQL);
$SQL ='
CREATE TABLE IF NOT EXISTS `quotationvalue'.$_SESSION['UserID'].'` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
)
';

mysqli_query($db,$SQL);
$SQL ='
CREATE TABLE IF NOT EXISTS `ocvalue'.$_SESSION['UserID'].'` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
)
';

mysqli_query($db,$SQL);
$SQL ='
CREATE TABLE IF NOT EXISTS `dcvalue'.$_SESSION['UserID'].'` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
)
';

mysqli_query($db,$SQL);

$SQL = 'truncate salescasereporting'.$_SESSION['UserID'].'';
mysqli_query($db,$SQL);
$SQL = 'truncate quotationvalue'.$_SESSION['UserID'].'';
mysqli_query($db,$SQL);
$SQL = 'truncate ocvalue'.$_SESSION['UserID'].'';
mysqli_query($db,$SQL);
$SQL = 'truncate dcvalue'.$_SESSION['UserID'].'';
mysqli_query($db,$SQL);

 $SQL = '
INSERT INTO quotationvalue'.$_SESSION['UserID'].'(salescaseref,orderno,value)

SELECT salesorders.salescaseref, salesorderdetails.orderno,
 SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
 ) as value from salesorderdetails INNER JOIN salesorderoptions on
 (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno WHERE salesorderdetails.lineoptionno = 0 
 and salesorderoptions.optionno = 0 
 AND salesorderdetails.orderno in 
 (
	SELECT MAX(orderno) from salesorders group by salescaseref
 
 
 )
 GROUP BY salesorderdetails.orderno

';
mysqli_query($db,$SQL);

 $SQL = '
INSERT INTO ocvalue'.$_SESSION['UserID'].'(salescaseref,orderno,value)

SELECT ocs.salescaseref, ocs.orderno,
 SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity
 ) as value from ocdetails INNER JOIN ocoptions on
 (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
 INNER JOIN ocs on ocs.orderno = ocdetails.orderno WHERE ocdetails.lineoptionno = 0 
 and ocoptions.optionno = 0 
 
 GROUP BY ocdetails.orderno

';
mysqli_query($db,$SQL);

 $SQL = '
INSERT INTO dcvalue'.$_SESSION['UserID'].'(salescaseref,orderno,value)

SELECT dcs.salescaseref, dcs.orderno,
 SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
 ) as value from dcdetails INNER JOIN dcoptions on
 (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
 INNER JOIN dcs on dcs.orderno = dcdetails.orderno WHERE dcdetails.lineoptionno = 0 
 and dcoptions.optionno = 0 
 
 GROUP BY dcdetails.orderno

';
mysqli_query($db,$SQL);
if($_POST['filtertype']=='date')
{
$SQL = '

SELECT * FROM salescase
INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno

WHERE
`commencementdate` BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
';
$totalcases=mysqli_query($db,$SQL);

}
else if($_POST['filtertype']=='podate')
{
	$SQL = '

SELECT * FROM salescase
INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno

WHERE
salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
';
	
	
$totalcases=mysqli_query($db,$SQL);
	
}

if($_POST['filtertype']=='date')
{
$SQL = '


SELECT distinct salescase.salescaseindex, salescase.salescaseref,salescase.podate FROM dc 
right outer join salescase on dc.salescaseref=salescase.salescaseref 
INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno

WHERE salescase.podate!="0000-00-00 00:00:00"
 AND
`commencementdate` BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 

AND salescase.salescaseref not in (
SELECT salescaseref from salesorders

)

ORDER BY salescase.salescaseindex
';
$totalanomolycases=mysqli_query($db,$SQL);

}
else if($_POST['filtertype']=='podate'){
	
$SQL = '


SELECT distinct salescase.salescaseindex, salescase.salescaseref,salescase.podate FROM dc 
right outer join salescase on dc.salescaseref=salescase.salescaseref 
INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno

WHERE salescase.podate!="0000-00-00 00:00:00"
 AND
salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 

AND salescase.salescaseref not in (
SELECT salescaseref from salesorders

)

ORDER BY salescase.salescaseindex
';	
	
	
$totalanomolycases=mysqli_query($db,$SQL);
	
}


if($_POST['filtertype']=='date')
{

$SQL = '


SELECT  COUNT(*) as quotecount, 
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
`commencementdate` BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 


AND quotationvalue'.$_SESSION['UserID'].'.value>0

';
$totalquotationvalue=mysqli_query($db,$SQL);

}
else if($_POST['filtertype']=='podate')
{
	
$SQL = '


SELECT  COUNT(*) as quotecount, 
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 


AND quotationvalue'.$_SESSION['UserID'].'.value>0

';
	
	
$totalquotationvalue=mysqli_query($db,$SQL);
	
}

if($_POST['filtertype']=='date')
{

$SQL = '


SELECT COUNT(*) as quotecount,
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
 
`commencementdate` BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 
AND (salescase.closingreason LIKE "%high prices%" OR salescase.closingreason LIKE "%Delivery time offered is not acceptable by customer%" 

OR salescase.closingreason LIKE "%want in equal%")
';
$totallostvalue=mysqli_query($db,$SQL)or mysqli_error();

}
else if($_POST['filtertype']=='podate'){
	
$SQL = '


SELECT COUNT(*) as quotecount,
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
 
salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 
AND (salescase.closingreason LIKE "%high prices%" OR salescase.closingreason LIKE "%Delivery time offered is not acceptable by customer%" 

OR salescase.closingreason LIKE "%want in equal%")
';
$totallostvalue=mysqli_query($db,$SQL);
	
}
if($_POST['filtertype']=='date')
{

$SQL='
SELECT COUNT(*) as quotecount,
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
 
`commencementdate` BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 
AND (salescase.closingreason LIKE "%internal cross%" OR salescase.closingreason LIKE "%external cross%"

OR salescase.closingreason LIKE "%Customer has cancelled the enquiry%"

OR salescase.closingreason LIKE "%any other reason%" )

';
$totalcanceledvalue=mysqli_query($db,$SQL);

}
else if($_POST['filtertype']=='podate'){
	
$SQL='
SELECT COUNT(*) as quotecount,
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
 
salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 
AND (salescase.closingreason LIKE "%internal cross%" OR salescase.closingreason LIKE "%external cross%"

OR salescase.closingreason LIKE "%Customer has cancelled the enquiry%"

OR salescase.closingreason LIKE "%any other reason%" )

';
	
$totalcanceledvalue=mysqli_query($db,$SQL);
	
}
if($_POST['filtertype']=='date')
{

$SQL='
SELECT COUNT(*) as quotecount,
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
 
`commencementdate` BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 
AND (salescase.closingreason LIKE "%Out of scope%" OR quotationvalue'.$_SESSION['UserID'].'.value=0)

';
$totalregrettedvalue=mysqli_query($db,$SQL);

}
else if($_POST['filtertype']=='podate'){
	
$SQL='
SELECT COUNT(*) as quotecount,
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
 
salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 
AND (salescase.closingreason LIKE "%Out of scope%" OR quotationvalue'.$_SESSION['UserID'].'.value=0)

';
$totalregrettedvalue=mysqli_query($db,$SQL);

}
if($_POST['filtertype']=='date')
{

$SQL='
SELECT COUNT(*) as quotecount,
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
 
`commencementdate` BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 
AND salescase.closingreason = "" AND salescase.podate = "0000-00-00 00:00:00"
AND quotationvalue'.$_SESSION['UserID'].'.value>0
';
$totalpipelinevalue=mysqli_query($db,$SQL);

}
else if($_POST['filtertype']=='podate'){
	
$SQL='
SELECT COUNT(*) as quotecount,
 SUM(quotationvalue'.$_SESSION['UserID'].'.value) as value from quotationvalue'.$_SESSION['UserID'].' INNER JOIN salescase on
 quotationvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
 
salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 
AND salescase.closingreason = "" AND salescase.podate = "0000-00-00 00:00:00"
AND quotationvalue'.$_SESSION['UserID'].'.value>0
';
$totalpipelinevalue=mysqli_query($db,$SQL);
	
}
if($_POST['filtertype']=='date')
{


$SQL = '


SELECT  COUNT(*) as quotecount, 
 SUM(ocvalue'.$_SESSION['UserID'].'.value) as value from ocvalue'.$_SESSION['UserID'].' 
 INNER JOIN salescase on
 ocvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
salescase.commencementdate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 


AND ocvalue'.$_SESSION['UserID'].'.value>0

';
$totalpovalue=mysqli_query($db,$SQL);

}
else if($_POST['filtertype']=='podate'){
	

$SQL = '


SELECT  COUNT(*) as quotecount, 
 SUM(ocvalue'.$_SESSION['UserID'].'.value) as value from ocvalue'.$_SESSION['UserID'].' 
 INNER JOIN salescase on
 ocvalue'.$_SESSION['UserID'].'.salescaseref = salescase.salescaseref
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 WHERE
salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
 


AND ocvalue'.$_SESSION['UserID'].'.value>0

';$totalpovalue=mysqli_query($db,$SQL);
	
}
if($_POST['filtertype']=='date')
{

$SQL = '
INSERT INTO `salescasereporting'.$_SESSION['UserID'].'`(`salescaseindex`, salescaseref, `salescasedescription`,
 `salesman`, `debtorname`, `branchcode`, `commencementdate`,`enquiryfile`,
 `enquirydate`, enquiryvalue, `lastquotationdate`, orderno,  `pofile`, `podate`, `ocdocumentfile`, `ocdocumentdate`,
 dclink, commentcode, quotationvalue,closingreason)
SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
 `salesman`, `name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
 `enquirydate`,enquiryvalue, `lastquotationdate`, salesorders.orderno,  `pofile`, salescase.`podate`,
 `ocdocumentfile`, `ocdocumentdate`, dispatchid, commentcode, quotationvalue'.$_SESSION['UserID'].'.value,closingreason
 FROM salescase LEFT OUTER JOIN dc ON salescase.salescaseref = dc.salescaseref
	
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 LEFT OUTER JOIN salesorders ON salescase.salescaseref=salesorders.salescaseref
 LEFT OUTER JOIN quotationvalue'.$_SESSION['UserID'].' ON quotationvalue'.$_SESSION['UserID'].'.salescaseref = salesorders.salescaseref
 LEFT OUTER JOIN 
 salescasecomments 
 ON salescasecomments.salescaseref =  salescase.salescaseref
 

 WHERE (salescasecomments.time IN (SELECT MAX(time) FROM salescasecomments GROUP BY salescaseref)
        OR salescasecomments.time IS NULL
        
        )
		AND 
		(salesorders.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
        OR salesorders.orderno IS NULL
        
        )
	 
		AND 
		(quotationvalue'.$_SESSION['UserID'].'.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
        OR quotationvalue'.$_SESSION['UserID'].'.orderno IS NULL
        
        )
	
		
	
		
		AND 
		(dc.dispatchid IN (SELECT MAX(dispatchid) FROM dc GROUP BY salescaseref)
        OR dc.dispatchid IS NULL
        
        )
		
		
		AND `commencementdate` BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
'


		
;	
mysqli_query($db,$SQL);

}
else if($_POST['filtertype']=='podate'){
	
	
$SQL = '
INSERT INTO `salescasereporting'.$_SESSION['UserID'].'`(`salescaseindex`, salescaseref, `salescasedescription`,
 `salesman`, `debtorname`, `branchcode`, `commencementdate`,`enquiryfile`,
 `enquirydate`, enquiryvalue, `lastquotationdate`, orderno,  `pofile`, `podate`, `ocdocumentfile`, `ocdocumentdate`,
 dclink, commentcode, quotationvalue,closingreason)
SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
 `salesman`, `name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
 `enquirydate`,enquiryvalue, `lastquotationdate`, salesorders.orderno,  `pofile`, salescase.`podate`,
 `ocdocumentfile`, `ocdocumentdate`, dispatchid, commentcode, quotationvalue'.$_SESSION['UserID'].'.value,closingreason
 FROM salescase LEFT OUTER JOIN dc ON salescase.salescaseref = dc.salescaseref
	
 LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
 LEFT OUTER JOIN salesorders ON salescase.salescaseref=salesorders.salescaseref
 LEFT OUTER JOIN quotationvalue'.$_SESSION['UserID'].' ON quotationvalue'.$_SESSION['UserID'].'.salescaseref = salesorders.salescaseref
 LEFT OUTER JOIN 
 salescasecomments 
 ON salescasecomments.salescaseref =  salescase.salescaseref
 

 WHERE (salescasecomments.time IN (SELECT MAX(time) FROM salescasecomments GROUP BY salescaseref)
        OR salescasecomments.time IS NULL
        
        )
		AND 
		(salesorders.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
        OR salesorders.orderno IS NULL
        
        )
	 
		AND 
		(quotationvalue'.$_SESSION['UserID'].'.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
        OR quotationvalue'.$_SESSION['UserID'].'.orderno IS NULL
        
        )
	
		
	
		
		AND 
		(dc.dispatchid IN (SELECT MAX(dispatchid) FROM dc GROUP BY salescaseref)
        OR dc.dispatchid IS NULL
        
        )
		
		
		AND salescase.podate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"

AND `name`LIKE"%'.$_POST['customer'].'%" AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
'


		
;
mysqli_query($db,$SQL);

}


?>

	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">
	<title>DataTables example - Server-side processing</title>
	<link rel="stylesheet" type="text/css" href="media/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="media/css/buttons.dataTables.css">
	
	<link rel="stylesheet" type="text/css" href="resources/syntax/shCore.css">
	<link rel="stylesheet" type="text/css" href="resources/demo.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.css"/>
 
<script type="text/javascript" src="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.js"></script>

	<style type="text/css" class="init">
	
	</style>
	<script type="text/javascript" src="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.js"></script>


	<script type="text/javascript" language="javascript" src="includes/jquery.js">
	</script>
	<script type="text/javascript" language="javascript" src="media/js/jquery.dataTables.js">
	</script>
	
	<script type="text/javascript" language="javascript" src="resources/syntax/shCore.js">
	</script>
	<script type="text/javascript" language="javascript" src="resources/demo.js">
	</script>
		<script type="text/javascript" language="javascript" src="media/js/dataTables.buttons.js">
	</script>	
	<script type="text/javascript" language="javascript" src="media/js/jszip.min.js">
	</script>	
	
	<script type="text/javascript" language="javascript" src="media/js/jquery.pdfmake.min.js">
	</script>	
	<script type="text/javascript" language="javascript" src="media/js/vfs_fonts.js">
	</script>
	<script type="text/javascript" language="javascript" src="media/js/buttons.html5.js">
	</script>
	
	
<?php


if ($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 23)
{
echo'
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		"processing": true,
		"serverSide": true,
		 "lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		"ajax": "server_processing_analysis_new.php",	
		      "dom": \'Blfrtip\',
        "buttons": [
            \'copyHtml5\',
            \'excelHtml5\',
            \'csvHtml5\',
            \'pdfHtml5\'
        ]
	
		} 
		
		
		
		
		).columnFilter();
	
		
} )
$(document).ready(function() {
    $(\'example\').DataTable( {
        "dom": \'Bfrtip\',
        buttons: [
            \'copyHtml5\',
            \'excelHtml5\',
            \'csvHtml5\',
            \'pdfHtml5\'
        ]
    } );
} )
	</script>';
}
else if ($_SESSION['AccessLevel'] == 11 OR $_SESSION['AccessLevel'] == 8)
{
echo'
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		"processing": true,
		"serverSide": true,
		 "lengthMenu": [[10, 25, 50,100], [10, 25, 50,100]],
		"ajax": "server_processing_analysis_new.php",	
		      "dom": \'Blfrtip\',
        "buttons": [
            \'copyHtml5\',
            \'excelHtml5\',
            \'csvHtml5\',
            \'pdfHtml5\'
        ]
	
		} 
		
		
		
		
		).columnFilter();
	
		
} )
$(document).ready(function() {
    $(\'example\').DataTable( {
        "dom": \'Bfrtip\',
        buttons: [
            \'copyHtml5\',
            \'excelHtml5\',
            \'csvHtml5\',
            \'pdfHtml5\'
        ]
    } );
} )
	</script>';
}
else

{

echo'
<script type="text/javascript" language="javascript" class="init">

$(document).ready(function() {
	$(\'#example\').DataTable( {
		"processing": true,
		"serverSide": true,
		 "lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		"ajax": "server_processing_analysis_new.php",	
		    "dom": \'Bfrtip\',
        buttons: [
         
        ]
     
		} 
		
		
		
		
		).columnFilter();
	
		
} )
$(document).ready(function() {
    $(\'#example\').DataTable( {
      "dom": \'Bfrtip\',
        buttons: [
         
        ]
     } );
} )


	</script>
';	
	
	
	
}

;
?>


<?php
echo '</head>';
	
	
	echo '<body>';
	echo '<input type="hidden" name="Lang" id="Lang" value="'.$Lang.'" />';

    echo '<div id="CanvasDiv">';
	echo '<div id="HeaderDiv">';
	echo '<div id="HeaderWrapDiv">';


	

		echo '<div id="AppInfoDiv">'; //===HJ===
			echo '<div id="AppInfoCompanyDiv">';
				echo '<img src="'.$RootPath.'/css/'.$Theme.'/images/company.png" title="'._('Company').'" alt="'._('Company').'"/>' . stripslashes($_SESSION['CompanyRecord']['coyname']);
			echo '</div>';
			echo '<div id="AppInfoUserDiv">';
				echo '<a href="#"><img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="User" alt="'._('User').'"/>' . stripslashes($_SESSION['UsersRealName']) . '</a>';
			echo '</div>';
			echo '<div id="AppInfoModuleDiv">';
				// Make the title text a class, can be set to display:none is some themes
				echo $Title;
			echo '</div>';
		echo '</div>'; // AppInfoDiv


		echo '<div id="QuickMenuDiv" style = "padding-right:250px;"><ul>';

		echo '<li><a href="'.$RootPath.'/index.php">' . _('Main Menu') . '</a></li>'; //take off inline formatting, use CSS instead ===HJ===

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){
			
			echo '<li><a href="'.$RootPath.'/SelectProduct.php">' ._('Items')     . '</a></li>';
			
			
		}

		echo '<li><a href="'.$RootPath.'/Logout.php" onclick="return confirm(\''._('Are you sure you wish to logout?').'\');">' . _('Logout') . '</a></li>';

		echo '</ul></div>'; // QuickMenuDiv
	
	echo '</div>'; // HeaderWrapDiv
	echo '</div>'; // Headerdiv
	echo '<div id="BodyDiv">';
	echo '<div id="BodyWrapDiv">';
	
	
	$totalquotationvaluerow=mysqli_fetch_assoc($totalquotationvalue);
	$totalpovaluerow=mysqli_fetch_assoc($totalpovalue);
	$totalregrettedvaluerow=mysqli_fetch_assoc($totalregrettedvalue);
	$totallostvaluerow=mysqli_fetch_assoc($totallostvalue);
	$totalcanceledvaluerow=mysqli_fetch_assoc($totalcanceledvalue);
	$totalpipelinevaluerow=mysqli_fetch_assoc($totalpipelinevalue);

echo '<h4>Filters</h4>'.
'<table border=1><tr><td colspan="2">Between: '.	$_POST['startdate'].' AND '. $_POST['enddate'].'</td></tr>'.

'<tr><td>Customer: '.$_POST['customer'].'</td><td> SalesPerson: '. $_POST['salesperson'].'</td></tr></table>';	

echo '<table border="4">';
echo "<tr><td><h2 align='left'> Total sales cases </h2></td><td> 
</td><td>       <h2> Count.".mysqli_num_rows($totalcases)."</h2>
</td></tr>";
echo "<tr><td><h2 align='left'> Regretted </h2></td>
<td></td><td><h2>Count.".$totalregrettedvaluerow['quotecount']."
</h2></td></tr>";
echo "<tr><td><h2 align='left'> Anomoly Cases </h2></td>
<td></td><td><h2>Count.".mysqli_num_rows($totalanomolycases)."
</h2></td></tr>";
echo "<tr><td><h2 align='left'> Total Proper Quotations </h2></td><td>       <h2> Rs.".locale_number_format($totalquotationvaluerow['value'],0)."</h2>
</td><td>       <h2> Count.".locale_number_format($totalquotationvaluerow['quotecount'],0)."</h2>
</td></tr>";


echo "<tr><td><h3 align='left'> Total Value of POs </h3></td><td><h3>Rs.".locale_number_format($totalpovaluerow['value'],0)."
</h3></td><td><h3>Count.".$totalpovaluerow['quotecount']."
</h3></td></tr>";
echo "<tr><td><h3 align='left'> Total Value of Quotations Lost </h3></td><td><h3>Rs.".locale_number_format($totallostvaluerow['value'],0)."
</h3></td><td><h3>Count.".$totallostvaluerow['quotecount']."
</h3></td></tr>";
echo "<tr><td><h3 align='left'> Total Value of Quotations cancelled</h3> </td>
<td><h3>Rs.".locale_number_format($totalcanceledvaluerow['value'],0)."
</h3><td><h3>Count.".$totalcanceledvaluerow['quotecount']."
</h3></td></tr>";
echo "<tr><td><h3 align='left'> Total Value of Quotations in Pipeline </h3></td>
<td><h3>Rs.".locale_number_format($totalpipelinevaluerow['value'],0)."
</h3></td><td><h3>Count.".$totalpipelinevaluerow['quotecount']."
</h3></td></tr>";

echo '</table>';
echo '<h3>Anomoly Cases where PO or DC is made without quotation</h3><table border="4">

<tr><td>sno</td><td>salescaseref</td><td>podate</td>';

while($totalanomolycasesrows=mysqli_fetch_assoc($totalanomolycases))
{
	echo '<tr><td>'.$totalanomolycasesrows['salescaseindex'].'</td><td>'.$totalanomolycasesrows['salescaseref'].'</td>
	<td>'.$totalanomolycasesrows['podate'].'</td></tr>';

	
	
}
echo'</table>';
?>
<table id="example" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
					<th>Sno.</th>
					<th>Date</th>
					<th>Salescaseref</th>
						<th>Client's Name</th>
						<th>Salesman</th>
						<th>Quotation No</th>
						<th>Items Desc.</th>
						
					
						<th>Quote Value</th>
						<th>Quote Status</th>
						<th>Salesperson Remarks</th>
						<th>PO Status</th>
						
						<th>PO Date</th>
						
						<th>PO Value</th>
						<th>DC nos.</th>
						<th>DC date</th>
						<th>DC Value</th>
						<th>Last Remarks</th>
						
					</tr>
				</thead>
				<tfoot>
					<tr>
							<th>Sno.</th>
					<th>Date</th>
					<th>Salescaseref</th>
						<th>Client's Name</th>
						<th>Salesman</th>
						<th>Quotation No</th>
						<th>Items Desc.</th>
						
					
						<th>Quote Value</th>
						<th>Quote Status</th>
						<th>Salesperson Remarks</th>
						<th>PO Status</th>
						
						<th>PO Date</th>
						
						<th>PO Value</th>
						<th>DC nos.</th>
						<th>DC date</th>
						<th>DC Value</th>
						<th>Last Remarks</th>
						
					</tr>
				</tfoot>
			</table>;

<?php
include('includes/footer.inc');
?>