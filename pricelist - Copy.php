t<?php

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
$SQL = "truncate pricelist";

mysqli_query($db,$SQL);

 $SQL = 'INSERT INTO `pricelist`(`brand`, `lastcost`, `materialcost`, `lastcostupdate`, `lastupdatedby`, 
 `mnfCode`, `mnfpno`,`itemcondition`,`categorydescription`, QOH) SELECT `manufacturers_name`, `lastcost`, `materialcost`,
 `lastcostupdate`, `lastupdatedby`, `mnfCode`, `mnfpno`,`abbreviation`,`categorydescription`,SUM(locstock.quantity) 
 FROM stockmaster INNER JOIN manufacturers ON stockmaster.brand = manufacturers.manufacturers_id 
 
 INNER JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid INNER JOIN itemcondition 
 ON stockmaster.conditionID = itemcondition.conditionID
 
 INNER JOIN locstock ON stockmaster.stockid = locstock.stockid
 
 group by locstock.stockid
';

	mysqli_query($db,$SQL);
	
	
	
?>

	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">
	<title>Price List</title>
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
	
	<script type="text/javascript" language="javascript" class="init">


	
$(document).ready(function() {
	$('#example').DataTable( {
		"processing": true,
		"serverSide": true,
		 "lengthMenu": [[10, 25, 50,-1], [10, 25, 50,"All"]],
		"ajax": "server_processing_pricelist.php",	
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
					<th>Brand</th>
					<th>Category Description</th>
					
						<th>mnfpno</th>
						<th>mnfCode</th>
						<th>Item Condition</th>
						<th>Last Update</th>
						<th>Update Person</th>
						<th>Last Price</th>
						<th>Current Price</th>
						<th>QOH</th>
						
					
						
					</tr>
				</thead>
				<tfoot>
					<tr>
					<th>Brand</th>
					<th>Category Description</th>
					
						<th>mnfpno</th>
						<th>mnfCode</th>
						<th>Item Condition</th>
						<th>Last Update</th>
						<th>Update Person</th>
						<th>Last Price</th>
						<th>Current Price</th>
						<th>QOH</th>
					</tr>
				</tfoot>
			</table>;


<?php
include('includes/footer.inc');
?>