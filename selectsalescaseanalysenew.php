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
	
	//Session Variables
	$_SESSION['startdate']=$_POST['startdate'];
	$_SESSION['enddate']=$_POST['enddate'];
	$_SESSION['customer']=$_POST['customer'];
	$_SESSION['salesperson']=$_POST['salesperson'];
	$_SESSION['filtertype']=$_POST['filtertype'];	
	
	//Filter Type
	if($_POST['filtertype'] == "date")
		$filterType = "commencementdate";
	else if($_POST['filtertype'] == "podate")
		$filterType = "salescase.podate";	
	
	//DB Connection
	$db=mysqli_connect('localhost','irtiza','netetech321','sahamid');
		
	//Quotation Values
	$SQL = 'SELECT salesorders.salescaseref, salesorderdetails.orderno,
		SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity) AS value 
		FROM salesorderdetails 
		INNER JOIN salesorderoptions ON (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
		INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno WHERE salesorderdetails.lineoptionno = 0 
		AND salesorderoptions.optionno = 0 
		AND salesorderdetails.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
		GROUP BY salesorderdetails.orderno';

	$result = mysqli_query($db,$SQL);
	
	$quotationValues = [];
	
	while($row = mysqli_fetch_assoc($result))
		$quotationValues[$row['salescaseref']] = $row;
	
	//OC Values
	$SQL = 'SELECT ocs.salescaseref, ocs.orderno,
		SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity) AS value 
		FROM ocdetails 
		INNER JOIN ocoptions ON (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
		INNER JOIN ocs ON ocs.orderno = ocdetails.orderno 
		WHERE ocdetails.lineoptionno = 0 
		AND ocoptions.optionno = 0 
		GROUP BY ocdetails.orderno';
	
	$result = mysqli_query($db,$SQL);
	
	$ocValues = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$ocValues[$row['salescaseref']][] = $row;
	
	}
	
	//DC Values
	$SQL = 'SELECT dcs.salescaseref, dcs.orderno,
		SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity) as value 
		FROM dcdetails INNER JOIN dcoptions ON (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
		INNER JOIN dcs on dcs.orderno = dcdetails.orderno WHERE dcdetails.lineoptionno = 0 
		AND dcoptions.optionno = 0 
		GROUP BY dcdetails.orderno';
		
	$result = mysqli_query($db,$SQL);
	
	$dcValues = [];
	
	while($row = mysqli_fetch_assoc($result))
		$dcValues[$row['salescaseref']][] = $row;

	//Sales Cases
	$SQL = 'SELECT * FROM salescase
		LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno
		WHERE '.$filterType.' BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"
		AND `name`LIKE"%'.$_POST['customer'].'%" 
		AND `salesman`LIKE"%'.$_POST['salesperson'].'%" ';
	
	$result = mysqli_query($db,$SQL);
	
	$salesCases = [];

	while($row = mysqli_fetch_assoc($result))
		$salesCases[] = $row;
	
	//Total Cases
	$totalCasesCount = count($salesCases);

	//Total Anomaly Cases
	$SQL = 'SELECT distinct salescase.salescaseindex, salescase.salescaseref,salescase.podate FROM dc 
		right outer join salescase on dc.salescaseref=salescase.salescaseref 
		INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno
		WHERE salescase.podate!="0000-00-00 00:00:00"
		AND '.$filterType.' BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"
		AND `name`LIKE"%'.$_POST['customer'].'%" 
		AND `salesman`LIKE"%'.$_POST['salesperson'].'%" 
		AND salescase.salescaseref not in (SELECT salescaseref from salesorders)
		ORDER BY salescase.salescaseindex';
		
	$totalanomolycases = mysqli_query($db,$SQL);	
	
	//Delivery Chalans
	$SQL = 'SELECT salescaseref,orderno,orddate FROM dcs';
		
	$result = mysqli_query($db,$SQL);	
		
	$deliveryChalans = [];
	
	while($row = mysqli_fetch_assoc($result))
		$deliveryChalans[$row['salescaseref']][] = $row;
	
	//Sales Orders
	$SQL = 'SELECT salescaseref,orderno FROM salesorders 
		WHERE (salesorders.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
		OR salesorders.orderno IS NULL)';
		
	$result = mysqli_query($db,$SQL);	
		
	$salesOrders = [];
	
	while($row = mysqli_fetch_assoc($result))
		$salesOrders[$row['salescaseref']] = $row['orderno'];
	
	//Sales Case Comments
	$SQL = 'SELECT salescase.salescaseref,commentcode,comment,time FROM salescasecomments 
		INNER JOIN salescase ON salescasecomments.salescaseref=salescase.salescaseref 
		where salescase.salesman=salescasecomments.username';

	$result = mysqli_query($db,$SQL);	
		
	$salesCaseComments = [];
	
	while($row = mysqli_fetch_assoc($result))
		$salesCaseComments[$row['salescaseref']][] = $row;
	
	//Sales Case Comments b
	$SQL = 'SELECT salescase.salescaseref,commentcode,comment,time FROM salescasecomments 
		INNER JOIN salescase ON salescasecomments.salescaseref=salescase.salescaseref 
		where salescase.salesman!=salescasecomments.username';

	$result = mysqli_query($db,$SQL);	
		
	$salesCaseCommentsb = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$dt = [];
		$dt['salescaseref'] = $row['salescaseref'];
		$dt['commentcode'] = $row['commentcode'];
		$dt['comment'] = utf8_encode($row['comment']);
		$dt['time'] = $row['time'];
		$salesCaseCommentsb[$row['salescaseref']][] = $dt;
	
	}
	//All Stocks
	$SQL = 'SELECT * FROM `stockmaster` INNER JOIN manufacturers ON 
		stockmaster.brand=manufacturers.manufacturers_id';
		
	$result = mysqli_query($db,$SQL);
	
	$stocks = [];
	
	while($row = mysqli_fetch_assoc($result))
		$stocks[$row['stockid']] = $row;
	
	//SalesCase for items description
	$SQL = 'SELECT * FROM `salescase` INNER JOIN salesorders ON 
		salesorders.salescaseref=salescase.salescaseref INNER JOIN salesorderdetails
		ON salesorders.orderno=salesorderdetails.orderno';
		
	$result = mysqli_query($db,$SQL);
	
	$itemsDescription = [];
	
	while($row = mysqli_fetch_assoc($result)){

		if(array_key_exists($row['salescaseref'],$itemsDescription))
			$itemsDescription[$row['salescaseref']] .= $stocks[$row['stkcode']]['manufacturers_name'].' '.$row['stkcode'].'<br/>';
		else
			$itemsDescription[$row['salescaseref']] = $stocks[$row['stkcode']]['manufacturers_name'].' '.$row['stkcode'].'<br/>';
			
	}	
	
	$totalQuotationValue = [];
	$totalQuotationValue['quoteCount'] 	= 0;
	$totalQuotationValue['value'] 		= 0;
	
	$totalLostValue = [];
	$totalLostValue['quoteCount'] 	= 0;
	$totalLostValue['value'] 		= 0;
	
	$totalCanceledValue = [];
	$totalCanceledValue['quoteCount'] 	= 0;
	$totalCanceledValue['value'] 		= 0;
	
	$totalRegrettedValue = [];
	$totalRegrettedValue['quoteCount'] 	= 0;
	$totalRegrettedValue['value'] 		= 0;

	$totalPipelineValue = [];
	$totalPipelineValue['quoteCount'] 	= 0;
	$totalPipelineValue['value'] 		= 0;
	
	$totalPOValue = [];
	$totalPOValue['quoteCount'] 	= 0;
	$totalPOValue['value'] 			= 0;
	
	$totalDCValue = [];
	$totalDCValue['quoteCount'] 	= 0;
	$totalDCValue['value'] 			= 0;
	
	$data = [];
		
	foreach($salesCases as $salesCase){
		
		$quotationValue = null;
		$quotationValue = $quotationValues[$salesCase['salescaseref']];
		
		$ocValue = null;
		$ocValue = $ocValues[$salesCase['salescaseref']];

		$dcValue = null;
		$dcValue = $dcValues[$salesCase['salescaseref']];
				
		if($quotationValue != null && $quotationValue != ""){
		
			if($quotationValue['value'] > 0){
				
				$totalQuotationValue['quoteCount'] 	+= 1;
				$totalQuotationValue['value'] 		+= $quotationValue['value'];					
				
			}
			

			if(strlen(stristr($salesCase['closingreason'],'high prices')) > 0 || 
				strlen(stristr($salesCase['closingreason'],'want in equal')) > 0 || 
				strlen(stristr($salesCase['closingreason'],'Delivery time offered is not acceptable by customer')) > 0){
				
				$totalLostValue['quoteCount'] 	+= 1;
				$totalLostValue['value'] 		+= $quotationValue['value'];					
				
			}
			
			if(strlen(stristr($salesCase['closingreason'],'internal cross')) > 0 || 
				strlen(stristr($salesCase['closingreason'],'external cross')) > 0 || 
				strlen(stristr($salesCase['closingreason'],'Customer has cancelled the enquiry')) > 0 ||
				strlen(stristr($salesCase['closingreason'],'any other reason')) > 0){
				
				$totalCanceledValue['quoteCount'] 	+= 1;
				$totalCanceledValue['value'] 		+= $quotationValue['value'];					
				
			}
			
			if(strlen(stristr($salesCase['closingreason'],'Out of scope')) > 0 || $quotationValue['value'] == 0){
				
				$totalRegrettedValue['quoteCount'] 	+= 1;
				$totalRegrettedValue['value'] 		+= $quotationValue['value'];					
				
			}
			
			if($salesCase['closingreason'] == '' && $quotationValue['value'] > 0 && $salesCase['podate'] == "0000-00-00 00:00:00"){
				
				$totalPipelineValue['quoteCount'] 	+= 1;
				$totalPipelineValue['value'] 		+= $quotationValue['value'];					
				
			}
			
		}
		
		if($ocValue != null && $ocValue != ""){
			
			
			foreach($ocValue as $val){
				
				if($val['value'] > 0){
				
					$totalPOValue['quoteCount'] += 1;
					$totalPOValue['value'] 		+= $val['value'];					
					
				}
				
			}
			
		}
		
		if($dcValue != null && $dcValue != ""){
			
			foreach($dcValue as $val){
				
				if($val['value'] > 0){
					
					$totalDCValue['quoteCount'] += 1;
					$totalDCValue['value'] 		+= $val['value'];					
					
				}
				
			}
			
		}
		
		
		$d = [];
		
		$quoteStatus = "";
		$quoteStatus = getQuoteStatus($salesCase,$quotationValue['value']);
		
		$name = utf8_encode($salesCase['name']);
		
		$poVal = 0;
		
		if($salesCase['podate'] != "0000-00-00 00:00:00")
		if($ocValue != null || $ocValue != "")
			foreach($ocValue as $val)
				$poVal = $val['value'];
				
		$dcNos = "";
		$dcTime = "";
		if(array_key_exists($salesCase['salescaseref'],$deliveryChalans))
			foreach($deliveryChalans[$salesCase['salescaseref']] as $dc){
				
				$dcNos .= $dc['orderno'].'<br>';
				$dcTime = date("d/m/Y", strtotime($dc['orddate'])).'<br>';
			}
		
 		$dcVal = 0;
		if($dcValue != null || $dcValue != "")
			foreach($dcValue as $val){
				$dcVal += $val['value'];
			}
		
		$d[] = $salesCase['salescaseindex'];
		$d[] = formatDate($salesCase['commencementdate']);
		$d[] = utf8_encode('<a href="salescase.php/?salescaseref='.$salesCase['salescaseref'].'" target="_blank">'.$salesCase['salescaseref'].'</a>');
		$d[] = utf8_encode($name);
		$d[] = utf8_encode($salesCase['salesman']);
		$d[] = utf8_encode('<a href="PDFQuotation.php/?QuotationNo='.$salesOrders[$salesCase['salescaseref']].'" target="_blank">'.$salesOrders[$salesCase['salescaseref']].'</a>');
		$d[] = utf8_encode($itemsDescription[$salesCase['salescaseref']]);
		$d[] = utf8_encode(locale_number_format($quotationValue['value'],0));
		$d[] = utf8_encode($quoteStatus);
		$d[] = utf8_encode(getRemarks($salesCaseComments,$salesCase['salescaseref']));
		$d[] = utf8_encode(($salesCase['podate'] == "0000-00-00 00:00:00") ? "Not Uploaded" : "Uploaded") ;
		$d[] = utf8_encode(formatDate($salesCase['podate']));
		$d[] = utf8_encode($poVal);
		$d[] = utf8_encode($dcNos);
		$d[] = utf8_encode($dcTime);
		$d[] = round($dcVal);
		$d[] = utf8_encode(getRemarks($salesCaseCommentsb,$salesCase['salescaseref']));
		
		$data[] = $d;
		
	}
	
	function getRemarks($object, $ref){
		
		$comments = null;
		$comments = $object[$ref];
		if($comments == null || $comments == "")
			return "";
		
		$remarks = "";
		foreach($comments as $comment){
			
			$remarks .= $comment['comment'].' '.$comment['time']."<br>";
			
		}
		
		return $remarks;
		
	}
	
	function getQuoteStatus($rowR, $value){
		
		if ($rowR['closingreason']=='high prices'
			OR $rowR['closingreason']== "Delivery time offered is not acceptable by customer"
			OR $rowR['closingreason']=="Customer doesn't want in equal")
			return 'LOST-'.$rowR['closingreason'];
		if ($rowR['closingreason']=='internal cross'
			OR $rowR['closingreason']== "external cross" 
			OR $rowR['closingreason']=="Customer has cancelled the enquiry"
			OR $rowR['closingreason']=="any other reason")
			return 'Cancelled-'.$rowR['closingreason'];
		if ($rowR['closingreason']=='' 
			AND $rowR['podate']=='0000-00-00 00:00:00' 
			AND $value != 0 )
			return 'In Pipeline';
		if ($rowR['closingreason'] != '' AND $rowR['podate']=='0000-00-00 00:00:00' 
			AND $value == 0 )
			return 'Out of Scope';
		if ($rowR['podate']>'0000-00-00 00:00:00')
			return 'PO Uploaded';
		return "In Pipeline";
		
	}
	
	function formatDate($date){
		
		if($date == '0000-00-00 00:00:00')
			return "";
		return date('Y/m/d', strtotime($date));
		
	}
	
	//echo "Total Cases Count: ".$totalCasesCount."<br>";
	//echo "Total Quotation Value: ".json_encode($totalQuotationValue)."<br>";
	//echo "Total Lost Value: ".json_encode($totalLostValue)."<br>";
	//echo "Total Canceled Value: ".json_encode($totalCanceledValue)."<br>";
	//echo "Total Regretted Value: ".json_encode($totalRegrettedValue)."<br>";
	//echo "Total Pipeline Value: ".json_encode($totalPipelineValue)."<br>";
	//echo "Total PO Value: ".json_encode($totalPOValue)."<br>";
	//echo "Total DC Value: ".json_encode($totalDCValue)."<br>";
	//echo "Data: ".json_encode($data);
	
	echo "<script>";
	echo "var dataset=".json_encode($data).";";
	echo "</script>";
	
?>

	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">
	<title>DataTables example - Server-side processing</title>
	<link rel="stylesheet" type="text/css" href="media/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="media/css/buttons.dataTables.css">
	
	<style type="text/css" class="init">
	
	</style>


	
	<script type="text/javascript" language="javascript" src="includes/jquery.js">
	</script>
	<script type="text/javascript" language="javascript" src="media/js/jquery.dataTables.js">
	</script>
	<script type="text/javascript" src="extensions/responsive/js/dataTables.responsive.min.js"></script>
	
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


if ($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 23 OR $_SESSION['AccessLevel'] == 27)
{
echo'
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		"processing": true,
		responsive:true,
		data: dataset,
		"lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
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
		responsive:true,
		data: dataset,
		"lengthMenu": [[10, 25, 50,100], [10, 25, 50,100]],	
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
		responsive:true,
		data: dataset,
		"lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],	
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
'<table border=1><tr><td colspan="2">Between: '.	$_POST['startdate'].' AND '. $_POST['enddate'].'</td></tr>'.

'<tr><td>Customer: '.$_POST['customer'].'</td><td> SalesPerson: '. $_POST['salesperson'].'</td></tr></table>';	

echo '<table border="4">';
echo "<tr><td><h2 align='left'> Total sales cases </h2></td><td> 
</td><td>       <h2> Count.".$totalCasesCount."</h2>
</td></tr>";
echo "<tr><td><h2 align='left'> Regretted </h2></td>
<td></td><td><h2>Count.".$totalRegrettedValue['quoteCount']."
</h2></td></tr>";
echo "<tr><td><h2 align='left'> Anomoly Cases </h2></td>
<td></td><td><h2>Count.".mysqli_num_rows($totalanomolycases)."
</h2></td></tr>";
echo "<tr><td><h2 align='left'> Total Proper Quotations </h2></td><td>       <h2> Rs.".locale_number_format($totalQuotationValue['value'],0)."</h2>
</td><td>       <h2> Count.".locale_number_format($totalQuotationValue['quoteCount'],0)."</h2>
</td></tr>";


echo "<tr><td><h3 align='left'> Total Value of POs </h3></td><td><h3>Rs.".locale_number_format($totalPOValue['value'],0)."
</h3></td><td><h3>Count.".$totalPOValue['quoteCount']."
</h3></td></tr>";
echo "<tr><td><h3 align='left'> Total Value of DCs </h3></td><td><h3>Rs.".locale_number_format($totalDCValue['value'],0)."
</h3></td><td><h3>Count.".$totalDCValue['quoteCount']."
</h3></td></tr>";
echo "<tr><td><h3 align='left'> Total Value of Quotations Lost </h3></td><td><h3>Rs.".locale_number_format($totalLostValue['value'],0)."
</h3></td><td><h3>Count.".$totalLostValue['quoteCount']."
</h3></td></tr>";
echo "<tr><td><h3 align='left'> Total Value of Quotations cancelled</h3> </td>
<td><h3>Rs.".locale_number_format($totalCanceledValue['value'],0)."
</h3><td><h3>Count.".$totalCanceledValue['quoteCount']."
</h3></td></tr>";
echo "<tr><td><h3 align='left'> Total Value of Quotations in Pipeline </h3></td>
<td><h3>Rs.".locale_number_format($totalPipelineValue['value'],0)."
</h3></td><td><h3>Count.".$totalPipelineValue['quoteCount']."
</h3></td></tr>";
echo "<tr><td><h3 align='left'> Success Ratio (OC/Q) </h3></td>
<td><h3>In terms of Value: ".locale_number_format($totalPOValue['value']/$totalQuotationValue['value'],2)."
</h3></td><td><h3>In terms of Count: ".locale_number_format($totalPOValue['quoteCount']/$totalQuotationValue['quoteCount'],2)."
</h3></td></tr>";
echo '</table>';
echo '<a href="brandwisesales.php" target="_blank"><h3>Brandwise Sales</h3></a><table border="4">';
echo '<a href="customerwisesales.php" target="_blank"><h3>Customerwise Sales</h3></a><table border="4">';
echo '<a href="salespersonwisesales.php" target="_blank"><h3>Salespersonwise Sales</h3></a><table border="4">';
echo '<h3>Anomoly Cases where PO or DC is made without quotation</h3><table border="4" >

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