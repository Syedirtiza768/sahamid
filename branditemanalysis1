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
		
	$_SESSION['startdate']=$_POST['startdate'];
$_SESSION['enddate']=$_POST['enddate'];

$_SESSION['Brand']=$_POST['Brand'];


echo $SQL ='
CREATE TABLE IF NOT EXISTS `branditemanalysis'.$_SESSION['UserID'].'` (
  `branditemanalysisindex` int(11) NOT NULL,
  `stockid` varchar(40) NOT NULL,
  `mnfCode` varchar(100) NOT NULL,
  `mnfpno` varchar(100) NOT NULL,
   `description` varchar(50) NOT NULL,
	`QuotationCount` int(11) NOT NULL,
	`OCCount` int(11) NOT NULL,
	`DCCount` int(11) NOT NULL,
	`QtyQuotation` int(11) NOT NULL,
	`QtyOC` int(11) NOT NULL,
	`QtyDC` int(11) NOT NULL,
	
	`AvgDiscountQ` double NOT NULL,
	`AvgDiscountO` double NOT NULL,
	`AvgDiscountD` double NOT NULL,
	`AvgPriceQ` double NOT NULL,
	`AvgPriceO` double NOT NULL,
	`AvgPriceD` double NOT NULL,
	`ListPrice` int NOT NULL,
	`TotalOGPMTO` int NOT NULL,
	`TotalOGPHO` int NOT NULL,
	`QOH` int NOT NULL
	)
';
//mysqli_query($db,$SQL);
$SQL = 'truncate branditemanalysis'.$_SESSION['UserID'].'';
//mysqli_query($db,$SQL);
$SQL='
insert INTO branditemanalysis'.$_SESSION['UserID'].'(stockid,mnfCode,mnfpno,description,Listprice)
SELECT stockid,mnfCode,mnfpno,description,materialcost FROM stockmaster WHERE brand='.$_SESSION['Brand'].'
';
//mysqli_query($db,$SQL);
echo $SQL='update branditemanalysis'.$_SESSION['UserID'].' SET QuotationCount=(

SELECT COUNT(salesorders.orderno) as count FROM salesorders inner join salesorderdetails 
on salesorders.orderno=salesorderdetails.orderno
WHERE SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].' 
AND branditemanalysis'.$_SESSION['UserID'].'.stockid=salesorderdetails.stkcode
AND salesorders.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 
GROUP BY branditemanalysis'.$_SESSION['UserID'].'.stockid HAVING count>0

),OCCount=(

SELECT COUNT(ocs.orderno) as count FROM ocs inner join ocdetails 
on ocs.orderno=ocdetails.orderno 
WHERE SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].' 
AND branditemanalysis'.$_SESSION['UserID'].'.stockid=ocdetails.stkcode
AND ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 
GROUP BY branditemanalysis'.$_SESSION['UserID'].'.stockid HAVING count>0

),DCCount=(

SELECT COUNT(dcs.orderno) as count FROM dcs inner join dcdetails 
on dcs.orderno=dcdetails.orderno 
WHERE SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].' 
AND branditemanalysis'.$_SESSION['UserID'].'.stockid=dcdetails.stkcode
AND dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 
GROUP BY branditemanalysis'.$_SESSION['UserID'].'.stockid HAVING count>0

),AvgDiscountQ=(
SELECT AVG(discountpercent) as avgdiscounto FROM `salesorderdetails`
WHERE branditemanalysis'.$_SESSION['UserID'].'.stockid=salesorderdetails.stkcode
AND salesorderdetails.orderno IN (
SELECT orderno FROM salesorders WHERE 
salesorders.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)
  GROUP BY salesorderdetails.stkcode

)
,AvgDiscountO=(
SELECT AVG(discountpercent) as avgdiscounto FROM `ocdetails`
WHERE branditemanalysis'.$_SESSION['UserID'].'.stockid=ocdetails.stkcode
AND ocdetails.orderno IN (
SELECT orderno FROM ocs WHERE 
ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)

  GROUP BY ocdetails.stkcode

),AvgDiscountD=(
SELECT AVG(discountpercent) as avgdiscountd FROM `dcdetails`

WHERE branditemanalysis'.$_SESSION['UserID'].'.stockid=dcdetails.stkcode
AND dcdetails.orderno IN (
SELECT orderno FROM dcs WHERE 
dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)

  GROUP BY dcdetails.stkcode

),AvgPriceQ=(SELECT AVG(unitprice*(1-discountpercent)) as 
price 
FROM `salesorderdetails` 
WHERE branditemanalysis'.$_SESSION['UserID'].'.stockid=salesorderdetails.stkcode
AND salesorderdetails.orderno IN (
SELECT orderno FROM salesorders WHERE 
salesorders.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)

GROUP BY stkcode 
),AvgPriceO=(SELECT AVG(unitprice*(1-discountpercent)) as 
price 
FROM `ocdetails` 
WHERE branditemanalysis'.$_SESSION['UserID'].'.stockid=ocdetails.stkcode
AND ocdetails.orderno IN (
SELECT orderno FROM ocs WHERE 
ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)

GROUP BY stkcode 
),AvgPriceD=(SELECT AVG(unitprice*(1-discountpercent)) as 
price 
FROM `dcdetails` 
WHERE branditemanalysis'.$_SESSION['UserID'].'.stockid=dcdetails.stkcode
AND dcdetails.orderno IN (
SELECT orderno FROM dcs WHERE 
dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)

GROUP BY stkcode 
), QtyQuotation=(
SELECT SUM(salesorderdetails.quantity*salesorderoptions.quantity) as quantity
FROM `salesorderdetails`

 inner join salesorderoptions on (salesorderdetails.orderno = salesorderoptions.orderno 
 AND salesorderdetails.orderlineno = salesorderoptions.lineno
AND salesorderdetails.lineoptionno = salesorderoptions.optionno
AND salesorderoptions.optionno = 0
 )
 
WHERE SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].' 
AND salesorderdetails.orderno IN (
SELECT orderno FROM salesorders WHERE 
salesorders.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)

AND
 branditemanalysis'.$_SESSION['UserID'].'.stockid=salesorderdetails.stkcode
GROUP BY stkcode HAVING quantity>0

)';
//mysqli_query($db,$SQL);
$SQL='update branditemanalysis'.$_SESSION['UserID'].' SET 
QtyOC=(
SELECT SUM(ocdetails.quantity*ocoptions.quantity) as quantity
FROM `ocdetails`
 
 inner join ocoptions on (ocdetails.orderno = ocoptions.orderno 
 AND ocdetails.orderlineno = ocoptions.lineno
AND ocdetails.lineoptionno = ocoptions.optionno ) 
WHERE SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].'
AND ocdetails.orderno IN (
SELECT orderno FROM ocs WHERE 
ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)
 AND
 branditemanalysis'.$_SESSION['UserID'].'.stockid=ocdetails.stkcode
GROUP BY stkcode HAVING quantity>0


)';
//mysqli_query($db,$SQL);
$SQL='update branditemanalysis'.$_SESSION['UserID'].' SET 
QtyDC=(

SELECT SUM(dcdetails.quantity*dcoptions.quantity) as quantity
FROM `dcdetails`
inner join dcoptions on (dcdetails.orderno = dcoptions.orderno 
AND dcdetails.orderlineno = dcoptions.lineno
AND dcdetails.lineoptionno = dcoptions.optionno ) 
WHERE SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].' 
AND dcdetails.orderno IN (
SELECT orderno FROM dcs WHERE 
dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 

)

AND
 branditemanalysis'.$_SESSION['UserID'].'.stockid=dcdetails.stkcode
GROUP BY stkcode HAVING quantity>0
)';

//mysqli_query($db,$SQL);
$SQL='update branditemanalysis'.$_SESSION['UserID'].' SET 
TotalOGPMTO=(

SELECT SUM(qty) FROM stockmoves 
WHERE SUBSTRING(stockid, 1, 3) = '.$_SESSION['Brand'].' 
AND stockmoves.trandate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 
AND type=511 AND loccode="MT" AND 
 branditemanalysis'.$_SESSION['UserID'].'.stockid=stockmoves.stockid
GROUP BY stockid
)';

//mysqli_query($db,$SQL);
$SQL='update branditemanalysis'.$_SESSION['UserID'].' SET 
TotalOGPHO=(

SELECT SUM(qty) FROM stockmoves 
WHERE SUBSTRING(stockid, 1, 3) = '.$_SESSION['Brand'].' 
AND stockmoves.trandate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 
AND type=511 AND loccode="HO" AND 
 branditemanalysis'.$_SESSION['UserID'].'.stockid=stockmoves.stockid
GROUP BY stockid
)';

//mysqli_query($db,$SQL);
$SQL='update branditemanalysis'.$_SESSION['UserID'].' SET 
QOH=(

SELECT SUM(quantity) FROM locstock 
WHERE SUBSTRING(stockid, 1, 3) = '.$_SESSION['Brand'].' AND 
 branditemanalysis'.$_SESSION['UserID'].'.stockid=locstock.stockid
GROUP BY stockid
)';

//mysqli_query($db,$SQL);

?>

	
	
	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">
	<title>DataTables example - Server-side processing</title>
	<link rel="stylesheet" type="text/css" href="media/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="media/css/buttons.dataTables.css">
	<link rel="stylesheet" type="text/css" href="extensions/responsive/css/responsive.dataTables.min.css">
	
	<link rel="stylesheet" type="text/css" href="resources/syntax/shCore.css">
	<link rel="stylesheet" type="text/css" href="resources/demo.css">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.1.1/css/responsive.dataTables.min.css"/>
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
	<script type="text/javascript" src="extensions/responsive/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" src="extensions/Buttons/js/buttons.colVis.min.js"></script>
	
	
	
<?php


if ($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 23)
{
echo'
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		"processing": true,
		"responsive": true,
		"serverSide": true,
		 "lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		"ajax": "server_processing_branditem.php",	
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
	processing: true,
		responsive: true,
		serverSide: true,
		  lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		ajax: "server_processing_branditem.php",	
		      dom: \'Blfrtip\',
       buttons: [
            \'copyHtml5\',
            \'excelHtml5\',
            \'csvHtml5\',
			 
            \'pdfHtml5\',
			 \'colvis\'
	]
		} 
		
		
		
		
		).columnFilter();
	
		
} )
$(document).ready(function() {
    $(\'example\').DataTable( {
        dom: \'Bfrtip\',
        buttons: [
            \'copyHtml5\',
            \'excelHtml5\',
            \'csvHtml5\',
			 \'colvis\',
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
		"ajax": "server_processing_branditem.php",	
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
	echo '<h4>Filters</h4>'.
'<table border=1><tr><td colspan="2">Between: '.date("d/m/Y", strtotime($_SESSION['startdate'])).' AND '. date("d/m/Y", strtotime($_SESSION['enddate'])).'</td></tr>'.

'<tr><td>Customer: '.$_SESSION['customer'].'</td><td> SalesPerson: '. $_SESSION['salesperson'].'</td></tr></table>';	

?>
<div>
<table id="example" class="display responsive nowrap" cellspacing="0">
				<thead>
					<tr>
					<th>Itemcode</th>
					<th>MnfCode</th>
					<th>PartNo</th>
					<th>Description</th>
					<th>SalesPerson History</th>
					<th>QuotationCount</th>	
					<th>OCCount</th>	
					<th>DCCount</th>	
					
					<th>QtyQuotation</th>
					<th>QtyOC</th>
					<th>QtyDC</th>
					
					<th>AvgDiscountQ</th>
					<th>AvgDiscountO</th>
					<th>AvgDiscountD</th>
					
					<th>AvgPriceQ</th>
					<th>AvgPriceO</th>
					<th>AvgPriceD</th>
					
					<th>ListPrice</th>
					<th>TotalOGPMTO</th>
					<th>TotalOGPHO</th>
					<th>QOH</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
					<th>Itemcode</th>
					<th>MnfCode</th>
					<th>PartNo</th>
					<th>Description</th>
					<th>SalesPerson History</th>
					<th>QuotationCount</th>	
					<th>OCCount</th>	
					<th>DCCount</th>	
					
					<th>QtyQuotation</th>
					<th>QtyOC</th>
					<th>QtyDC</th>
					
					<th>AvgDiscountQ</th>
					<th>AvgDiscountO</th>
					<th>AvgDiscountD</th>
					
					<th>AvgPriceQ</th>
					<th>AvgPriceO</th>
					<th>AvgPriceD</th>
					
					<th>ListPrice</th>
					<th>TotalOGPMTO</th>
					<th>TotalOGPHO</th>
					<th>QOH</th>
					</tr>
				</tfoot>
			</table>;
</div>

<?php
include('includes/footer.inc');
?>