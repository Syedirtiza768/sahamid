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

	
	$SQLX = "truncate stockusage";
mysqli_query($db,$SQLX);
	
	$SQLY = "truncate reorderlevel";
mysqli_query($db,$SQLY);
	$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']),$db);
$SQLP = "INSERT INTO `stockusage`(`stockid`, `loccode`, `IGP`, `OGP`, `DC`)
SELECT stockmoves.stockid, stockmoves.loccode, 
	SUM(CASE WHEN (stockmoves.type=510) THEN stockmoves.qty ELSE 0 END) AS igp, SUM(CASE WHEN (stockmoves.type=511 OR 
	stockmoves.type=513) THEN stockmoves.qty ELSE 0 END) AS ogp, SUM(CASE WHEN (stockmoves.type=512) THEN stockmoves.qty 
	ELSE 0 END) AS dc FROM periods INNER JOIN stockmoves ON periods.periodno=stockmoves.prd WHERE periods.periodno 
	<=";
	$SQLP.= $CurrentPeriod;
	$SQLP.= " AND periods.periodno >";
	$SQLP.= $CurrentPeriod - $_SESSION['NumberOfPeriodsOfStockUsage']."
	GROUP BY stockmoves.stockid,loccode";
mysqli_query($db,$SQLP);
		

$SQLZ = "
INSERT INTO `reorderlevel`(`categorydescription`, `stockid`,`mnfpno`, `mnfCode`, `conditionID`,`manufacturers_name`, `description`,
 `locationname`, `quantity`, `reorderlevel`, `needed`,`IGP`,`OGP`,`DC`)SELECT stockcategory.categorydescription as categorydescription, stockmaster.stockid,
 stockmaster.mnfpno as mnfpno, stockmaster.mnfCode as mnfCode ,stockmaster.conditionID as conditionID , manufacturers.manufacturers_name as manufacturers_name , 
 stockmaster.description as description , locations.locationname as locationname, locstock.quantity as quantity, locstock.reorderlevel as reorderlevel,
 (locstock.reorderlevel-locstock.quantity) as needed, stockusage.IGP, stockusage.OGP,stockusage.DC FROM locstock, stockmaster, stockcategory, locations, manufacturers ,stockusage
 WHERE locstock.stockid=stockmaster.stockid AND stockmaster.stockid = stockusage.stockid AND stockusage.loccode = locations.loccode AND stockmaster.categoryid = stockcategory.categoryid AND locstock.loccode=locations.loccode 
 AND stockmaster.brand = manufacturers.manufacturers_id AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
";


mysqli_query($db,$SQLZ);










?>

	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">
	<title>Inventory Reorder Levels</title>
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
	<script type="text/javascript" language="javascript" src="media/js/dataTables.scroller.min.js">
	</script>
	
	<script type="text/javascript" language="javascript" src="media/js/buttons.html5.js">
	</script>
	<script type="text/javascript" language="javascript" class="init">


	
$(document).ready(function() {
	$('#example').DataTable( {
		"processing": true,
		"serverSide": true,
		 "lengthMenu": [[10, 25, 50,-1], [10, 25, 50,"All"]],
		"ajax": "server_processing_reorder.php",	
		      "dom": 'Blfrtip',
        "buttons": [
            'copyHtml5',
            'excelHtml5',
            'csvHtml5',
            'pdfHtml5'
        ]
	
		} 
		
		
		
		
		).columnFilter();
	
		
} )

	</script>
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
?>
<table id="example" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
					<tr>
					<th>categorydescription</th>
						<th>mnfpno</th>
						<th>mnfCode</th>
						<th>manufacturers_name</th>
						<th>description</th>
						<th>conditionID</th>
						<th>locationname</th>
						
						<th>IGPs</th>
						<th>OGPs</th>
						<th>DCs</th>
						<th>quantity</th>
						<th>reorderlevel</th>
						
						<th>needed</th>
						
					</tr>
				</thead>
				<tfoot>
					<tr>
					<th>categorydescription</th>
						<th>mnfpno</th>
						<th>mnfCode</th>
						<th>manufacturers_name</th>
						<th>description</th>
						<th>conditionID</th>
						<th>locationname</th>
						<th>IGPs</th>
						<th>OGPs</th>
						<th>DCs</th>
						<th>quantity</th>
						<th>reorderlevel</th>
						<th>needed</th>
						
					</tr>
				</tfoot>
			</table>;


<?php
include('includes/footer.inc');
?>