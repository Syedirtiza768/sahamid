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

	$db=mysqli_connect('localhost','irtiza','netetech321','sahamid');
	
	$SQL = 'SELECT igpitems.dispatchid,stockmaster.stockid, stockmaster.mnfCode, 
	 stockmaster.mnfpno, manufacturers.manufacturers_name, igpitems.quantity,
	 stockmaster.materialcost, igpitems.quantity*stockmaster.materialcost as subt,
	 igp.loccode,igp.storemanager,igp.receivedfrom,igp.narrative,
	 igp.despatchdate from igpitems 
	 INNER JOIN igp on igp.dispatchid = igpitems.dispatchid
	 INNER JOIN stockmaster on stockmaster.stockid=igpitems.stockid
	 INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 AND igp.despatchdate BETWEEN "'.$_POST['startdate'].'" AND "'.$_POST['enddate'].'"
	 
	 ORDER BY igpitems.dispatchid';


	$result = mysqli_query($db,$SQL);

	$data = [];
	while($row = mysqli_fetch_assoc($result)){
		$data[] = $row;
	}

	function formatDate($date){
		
		if($date == '0000-00-00 00:00:00')
			return "";
		return date('Y/m/d', strtotime($date));
		
	}
	
	
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


if ($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 23)
{
echo'
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		"processing": true,
		responsive:true,
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

'</table>';	


?>
<table id="example" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>Sno.</th>
						<th>IGP #</th>
						<th>Stock ID</th>
						<th>MNF Code</th>
						<th>MNF #</th>
						<th>MNF Name</th>
						<th>Quantity</th>
						<th>Material Cost</th>
						<th>Sub Total</th>
						<th>Location Code</th>
						<th>Store Manager</th>
						<th>Received From</th>
						<th>Narrative</th>
						<th>Dispatch Date</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$count = 1;
					foreach($data as $row){
						echo '<tr>';
						echo '<td>'.$count++.'</td>';
						echo '<td>'.$row['dispatchid'].'</td>';
						echo '<td>'.$row['stockid'].'</td>';
						echo '<td>'.$row['mnfCode'].'</td>';
						echo '<td>'.$row['mnfno'].'</td>';
						echo '<td>'.$row['manufacturers_name'].'</td>';
						echo '<td>'.$row['quantity'].'</td>';
						echo '<td>'.$row['materialcost'].'</td>';
						echo '<td>'.$row['subt'].'</td>';
						echo '<td>'.$row['loccode'].'</td>';
						echo '<td>'.$row['storemanager'].'</td>';
						echo '<td>'.$row['receivedfrom'].'</td>';
						echo '<td>'.$row['narrative'].'</td>';
						echo '<td>'.$row['despatchdate'].'</td>';
						echo '</tr>';
					}
				?>
				</tbody>
				<tfoot>
					<tr>
						<th>Sno.</th>
						<th>IGP #</th>
						<th>Stock ID</th>
						<th>MNF Code</th>
						<th>MNF #</th>
						<th>MNF Name</th>
						<th>Quantity</th>
						<th>Material Cost</th>
						<th>Sub Total</th>
						<th>Location Code</th>
						<th>Store Manager</th>
						<th>Received From</th>
						<th>Narrative</th>
						<th>Dispatch Date</th>
					</tr>
				</tfoot>
			</table>;

<?php
include('includes/footer.inc');
?>