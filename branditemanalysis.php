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

	//Items or Stock
	$SQL = 'SELECT stockid,mnfCode,mnfpno,description,materialcost FROM stockmaster WHERE brand='.$_SESSION['Brand'].'';

	$itemsArray = [];

	$items=mysqli_query($db,$SQL);

	while($myitemrow=mysqli_fetch_assoc($items))
		$itemsArray[] = $myitemrow;
	
	//Quotation Count
	$SQL = "SELECT stkcode,COUNT(salesorders.orderno) as count FROM salesorders inner join salesorderdetails 
	on salesorders.orderno=salesorderdetails.orderno
	WHERE SUBSTRING(stkcode, 1, 3) = '".$_SESSION['Brand']."' 
	AND salesorders.orddate BETWEEN '".$_SESSION['startdate']."' AND '".$_SESSION['enddate']."'
	GROUP BY salesorderdetails.stkcode HAVING count>0";
	
	$quotationCounts = [];
	
	$abc=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($abc)){
		
		$quotationCounts[$row['stkcode']] = $row;
		
	}
	
	//OCCount
	$SQL = 'SELECT ocdetails.stkcode,COUNT(ocs.orderno) as count FROM ocs inner join ocdetails 
	on ocs.orderno=ocdetails.orderno 
	WHERE SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].' 
	AND ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'" 
	GROUP BY ocdetails.stkcode HAVING count>0';
	
	$OCCounts = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$OCCounts[$row['stkcode']] = $row;
		
	}
	
	//DC Count
	$SQL = 'SELECT dcdetails.stkcode,COUNT(dcs.orderno) as count FROM dcs inner join dcdetails 
	on dcs.orderno=dcdetails.orderno 
	WHERE SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].'
	AND dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	GROUP BY dcdetails.stkcode HAVING count>0';

	$DCCounts = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$DCCounts[$row['stkcode']] = $row;
		
	}
	
	//AvgDiscountQ
	$SQL = 'SELECT salesorderdetails.stkcode,AVG(discountpercent) as avg FROM `salesorderdetails`
	INNER JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
		WHERE salesorders.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	GROUP BY salesorderdetails.stkcode';
	
	$AvgDiscountQ = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$AvgDiscountQ[$row['stkcode']] = $row;
		
	}
	
	//AvgDiscountO
	$SQL = 'SELECT ocdetails.stkcode,AVG(discountpercent) as avg FROM `ocdetails`
	INNER JOIN ocs ON ocs.orderno=ocdetails.orderno
		WHERE ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	GROUP BY ocdetails.stkcode';
	
	$AvgDiscountO = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$AvgDiscountO[$row['stkcode']] = $row;
		
	}
	
	//AvgDiscountD
	$SQL = 'SELECT dcdetails.stkcode,AVG(discountpercent) as avg FROM `dcdetails`
	INNER JOIN dcs ON dcs.orderno=dcdetails.orderno
		WHERE dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	GROUP BY dcdetails.stkcode';
	
	$AvgDiscountD = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$AvgDiscountD[$row['stkcode']] = $row;
		
	}
	
	//AvgPriceQ
	$SQL = 'SELECT stkcode,AVG(unitprice*(1-discountpercent)) as price FROM `salesorderdetails`
	INNER JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
		WHERE salesorders.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	GROUP BY stkcode';
	
	$AvgPriceQ = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$AvgPriceQ[$row['stkcode']] = $row;
		
	}
	
	//Avg PriceO
	$SQL = 'SELECT stkcode,AVG(unitprice*(1-discountpercent)) as price FROM `ocdetails`
	INNER JOIN ocs ON ocs.orderno=ocdetails.orderno
		WHERE ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	GROUP BY stkcode';
	
	$AvgPriceO = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$AvgPriceO[$row['stkcode']] = $row;
		
	}
	//Avg PriceD
	$SQL = 'SELECT stkcode,AVG(unitprice*(1-discountpercent)) as price FROM `dcdetails`
	INNER JOIN dcs ON dcs.orderno=dcdetails.orderno
		WHERE dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	GROUP BY stkcode';
	
	$AvgPriceD = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$AvgPriceD[$row['stkcode']] = $row;
		
	}

	//QtyQuotation
	$SQL = 'SELECT stkcode,SUM(salesorderdetails.quantity*salesorderoptions.quantity) as quantity FROM `salesorderdetails`
	inner join salesorderoptions on 
	(salesorderdetails.orderno = salesorderoptions.orderno 
	AND salesorderdetails.orderlineno = salesorderoptions.lineno
	AND salesorderdetails.lineoptionno = salesorderoptions.optionno
	AND salesorderoptions.optionno = 0)
	INNER JOIN salesorders ON salesorders.orderno=salesorderdetails.orderno
		WHERE salesorders.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	AND SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].' 
	GROUP BY stkcode HAVING quantity>0';
	
	$QtyQuotation = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$QtyQuotation[$row['stkcode']] = $row;
		
	}
	
	//QtyOC
	$SQL = 'SELECT stkcode,SUM(ocdetails.quantity*ocoptions.quantity) as quantity FROM `ocdetails`
	inner join ocoptions on 
	(ocdetails.orderno = ocoptions.orderno 
	AND ocdetails.orderlineno = ocoptions.lineno
	AND ocdetails.lineoptionno = ocoptions.optionno ) 
	INNER JOIN ocs ON ocs.orderno=ocdetails.orderno
		WHERE ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	AND
	 SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].'
	GROUP BY stkcode HAVING quantity>0';
	
	$QtyOC = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$QtyOC[$row['stkcode']] = $row;
		
	}
	
	//QtyDC
	$SQL = 'SELECT stkcode,SUM(dcdetails.quantity*dcoptions.quantity) as quantity FROM `dcdetails`
	inner join dcoptions on (dcdetails.orderno = dcoptions.orderno 
	AND dcdetails.orderlineno = dcoptions.lineno
	AND dcdetails.lineoptionno = dcoptions.optionno ) 
	INNER JOIN dcs ON dcs.orderno=dcdetails.orderno
		WHERE dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
	AND
	 SUBSTRING(stkcode, 1, 3) = '.$_SESSION['Brand'].' 
	GROUP BY stkcode HAVING quantity>0';
	
	$QtyDC = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$QtyDC[$row['stkcode']] = $row;
		
	}
	
	//TotalOGPMTO
	$SQL = 'SELECT stockid as stkcode,SUM(qty) as total FROM stockmoves 
	WHERE SUBSTRING(stockid, 1, 3) = '.$_SESSION['Brand'].' 
	AND stockmoves.trandate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"	 
	AND type=511 AND loccode="MT"
	GROUP BY stockid';
	
	$TotalOGPMTO = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$TotalOGPMTO[$row['stkcode']] = $row;
		
	}
	
	//TotalOGPHO
	$SQL = 'SELECT stockid as stkcode,SUM(qty) as total FROM stockmoves 
	WHERE SUBSTRING(stockid, 1, 3) = '.$_SESSION['Brand'].' 
	AND stockmoves.trandate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"	 
	AND type=511 AND loccode="HO"
	GROUP BY stockid';
	
	$TotalOGPHO = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$TotalOGPHO[$row['stkcode']] = $row;
		
	}	
	//QOH
	$SQL = 'SELECT stockid as stkcode,SUM(quantity) as total FROM locstock 
	WHERE SUBSTRING(stockid, 1, 3) = '.$_SESSION['Brand'].'
	GROUP BY stockid';
	
	$QOH = [];
	
	$temp=mysqli_query($db,$SQL);
	
	while($row=mysqli_fetch_assoc($temp)){
		
		$QOH[$row['stkcode']] = $row;
		
	}	

?>

	
	
	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">
	<title>DataTables example - Server-side processing</title>
		<link rel="stylesheet" type="text/css" href="media/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="extensions/Buttons/css/buttons.dataTables.css">
	<link rel="stylesheet" type="text/css" href="extensions/responsive/css/responsive.dataTables.min.css">
	
	<link rel="stylesheet" type="text/css" href="resources/syntax/shCore.css">
	<link rel="stylesheet" type="text/css" href="resources/demo.css">
	
	<style type="text/css" class="init">
	
	</style>
	

	<script type="text/javascript" language="javascript" src="includes/jquery.js">
	</script>
	<script type="text/javascript" language="javascript" src="media/js/jquery.dataTables.js">
	</script>
	
	<script type="text/javascript" language="javascript" src="resources/syntax/shCore.js">
	</script>
	<script type="text/javascript" language="javascript" src="resources/demo.js">
	</script>
	<script type="text/javascript" language="javascript" src="media/js/jszip.min.js">
	</script>	
	
	<script type="text/javascript" language="javascript" src="media/js/jquery.pdfmake.min.js">
	</script>	
	<script type="text/javascript" language="javascript" src="media/js/vfs_fonts.js">
	</script>
	
	
	<script type="text/javascript" src="extensions/responsive/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" src="extensions/Buttons/js/buttons.colVis.min.js"></script>
	
	<script type="text/javascript" src="extensions/Buttons/js/buttons.html5.js"></script>
		<script type="text/javascript" src="extensions/Buttons/js/dataTables.buttons.min.js"></script>

	
<?php

	

if ($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 23)
{
echo'
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		"processing": true,
		"responsive": true,
		
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
	processing: true,
		responsive: true,
	
		  lengthMenu: [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		
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
'<table border=1><tr><td colspan="2">Between: '.date("d/m/Y", strtotime($_SESSION['startdate'])).' AND '. date("d/m/Y", strtotime($_SESSION['enddate'])).'</td></tr>'.

'<tr><td>Customer: '.$_SESSION['customer'].'</td><td> SalesPerson: '. $_SESSION['salesperson'].'</td></tr></table>';	

echo "<table id='example' class='display responsive nowrap'>
		<thead>
			<tr>
			<th>Stock ID</th>
			<th>MNF Code</th>
			<th>mnfpno</th>
			<th>description</th>
			<th>stockid</th>
			<th>quotation count</th>
			<th>OC Count</th>
			<th>DC Count</th>
			<th>AvgDiscountQ</th>
			<th>AvgDiscountO</th>
			<th>AvgDiscountD</th>
			<th>AvgPriceQ</th>
			<th>AvgPriceO</th>
			<th>AvgPriceD</th>
			<th>QtyQuotation</th>
			<th>QtyOC</th>
			<th>QtyDC</th>
			<th>TotalOGPMTO</th>
			<th>TotalOGPHO</th>
			<th>QOH</th>
			</tr>
		</thead>
		<tbody>";
	
	foreach($itemsArray as $item){
		
		echo "<tr><td>".$item['stockid']."</td>";
		echo "<td>".$item['mnfCode']."</td>";
		echo "<td>".$item['mnfpno']."</td>";
		echo "<td>".$item['description']."</td>";
		echo "<td>".$item['stockid']."</td>";
		if(array_key_exists($item['stockid'], $quotationCounts))
			echo "<td>".$quotationCounts[$item['stockid']]['count']."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $OCCounts))
			echo "<td>".$OCCounts[$item['stockid']]['count']."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $DCCounts))
			echo "<td>".$DCCounts[$item['stockid']]['count']."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $AvgDiscountQ))
			echo "<td>".locale_number_format($AvgDiscountQ[$item['stockid']]['avg']*100,2) ."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $AvgDiscountO))
			echo "<td>".locale_number_format($AvgDiscountO[$item['stockid']]['avg']*100,2) ."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $AvgDiscountD))
			echo "<td>".locale_number_format($AvgDiscountD[$item['stockid']]['avg']*100,2) ."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $AvgPriceQ))
			echo "<td>".locale_number_format($AvgPriceQ[$item['stockid']]['price'],0)."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $AvgPriceO))
			echo "<td>".locale_number_format($AvgPriceO[$item['stockid']]['price'],0)."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $AvgPriceD))
			echo "<td>".locale_number_format($AvgPriceD[$item['stockid']]['price'],0)."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $QtyQuotation))
			echo "<td>".$QtyQuotation[$item['stockid']]['quantity']."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $QtyOC))
			echo "<td>".$QtyOC[$item['stockid']]['quantity']."</td>";
		else
			echo "<td>0</td>";

		if(array_key_exists($item['stockid'], $QtyDC))
			echo "<td>".$QtyDC[$item['stockid']]['quantity']."</td>";
		else
			echo "<td>0</td>";	
		
		if(array_key_exists($item['stockid'], $TotalOGPMTO))
			echo "<td>".$TotalOGPMTO[$item['stockid']]['total']."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $TotalOGPHO))
			echo "<td>".$TotalOGPHO[$item['stockid']]['total']."</td>";
		else
			echo "<td>0</td>";
		
		if(array_key_exists($item['stockid'], $QOH))
			echo "<td>".$QOH[$item['stockid']]['total']."</td>";
		else
			echo "<td>0</td>";
		
		echo "</tr>";
		
	}
		
	echo "
		</tbody>
	</table>";

include('includes/footer.inc');
?>