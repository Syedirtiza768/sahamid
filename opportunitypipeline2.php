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
		

if ($_SESSION['filtertype']=='podate')
	header('Location:selectsalescaseanalysefilternew.php');

$SQL ='
CREATE TABLE IF NOT EXISTS `quotationvaluereports'.$_SESSION['UserID'].'` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
)
';

mysqli_query($db,$SQL);
$SQL ='
CREATE TABLE IF NOT EXISTS `ocvaluereports'.$_SESSION['UserID'].'` (
  `ocvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
)
';

mysqli_query($db,$SQL);
$SQL ='
CREATE TABLE IF NOT EXISTS `dcvaluereports'.$_SESSION['UserID'].'` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL
)
';

mysqli_query($db,$SQL);
$SQL = '
		CREATE TABLE IF NOT EXISTS `advancedreporting'.$_SESSION['UserID'].'` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL ,
  `branchcode` varchar(10) NOT NULL ,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
  
) ';

mysqli_query($db,$SQL);


$SQL = 'truncate quotationvaluereports'.$_SESSION['UserID'].'';
mysqli_query($db,$SQL);
$SQL = 'truncate ocvaluereports'.$_SESSION['UserID'].'';
mysqli_query($db,$SQL);
$SQL = 'truncate dcvaluereports'.$_SESSION['UserID'].'';
mysqli_query($db,$SQL);
$SQL = 'truncate advancedreporting'.$_SESSION['UserID'].'';
mysqli_query($db,$SQL);

$SQL = ' INSERT INTO quotationvaluereports'.$_SESSION['UserID'].'(salescaseref,value)
SELECT  
salesorders.salescaseref,
SUM(salesorderdetails.unitprice* (1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
 ) as value from salesorderdetails INNER JOIN salesorderoptions on
 (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
 INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
 INNER JOIN salescase on salescase.salescaseref=salesorders.salescaseref
 
 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
 WHERE salesorderdetails.lineoptionno = 0 
 
 and salesorderoptions.optionno = 0 
 AND salesorderdetails.orderno in 
 (
	SELECT MAX(orderno) from salesorders group by salescaseref
 
 
 )
 AND salesorders.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 AND salescase.salesman LIKE "%'.$_SESSION['salesperson'].'%"
 AND debtorsmaster.name LIKE "%'.$_SESSION['customer'].'%"
 
 GROUP BY salesorders.salescaseref
 

 ';

mysqli_query($db,$SQL);

 $SQL = ' INSERT INTO ocvaluereports'.$_SESSION['UserID'].'(salescaseref,value)
SELECT  
ocs.salescaseref,
SUM(ocdetails.unitprice* (1 - ocdetails.discountpercent)*ocdetails.quantity*ocoptions.quantity
 ) as value from ocdetails INNER JOIN ocoptions on
 (ocdetails.orderno = ocoptions.orderno AND ocdetails.orderlineno = ocoptions.lineno) 
 INNER JOIN ocs on ocs.orderno = ocdetails.orderno
 INNER JOIN salescase on salescase.salescaseref=ocs.salescaseref
 
 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
 WHERE ocdetails.lineoptionno = 0 
 
 and ocoptions.optionno = 0 
 
 AND ocs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 AND salescase.salesman LIKE "%'.$_SESSION['salesperson'].'%"
 AND debtorsmaster.name LIKE "%'.$_SESSION['customer'].'%"
 
 GROUP BY ocs.salescaseref
 

 ';

mysqli_query($db,$SQL);
 $SQL = ' INSERT INTO dcvaluereports'.$_SESSION['UserID'].'(salescaseref,value)
SELECT  
dcs.salescaseref,
SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
 ) as value from dcdetails INNER JOIN dcoptions on
 (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
 INNER JOIN dcs on dcs.orderno = dcdetails.orderno
 INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
 INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
 WHERE dcdetails.lineoptionno = 0 
 
 and dcoptions.optionno = 0 
 AND dcs.orddate BETWEEN "'.$_SESSION['startdate'].'" AND "'.$_SESSION['enddate'].'"
 AND salescase.salesman LIKE "%'.$_SESSION['salesperson'].'%"
 AND debtorsmaster.name LIKE "%'.$_SESSION['customer'].'%"
 
 GROUP BY dcs.salescaseref 
 
 ';

mysqli_query($db,$SQL);
$SQL = '
		CREATE TABLE IF NOT EXISTS `advancedreporting'.$_SESSION['UserID'].'` (
  `salescaseindex` int(40) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `salescasedescription` text NOT NULL,
  `salesman` varchar(35) NOT NULL,
  `debtorname` varchar(40) NOT NULL ,
  `branchcode` varchar(10) NOT NULL ,
  `commencementdate` datetime NOT NULL,
  `enquiryvalue` int(11) NOT NULL,
  `quotationvalue` int(11) NOT NULL,
  `ocvalue` int(11) NOT NULL,
  `dcvalue` int(11) NOT NULL
  
) ';

$SQL = '
INSERT INTO advancedreporting'.$_SESSION['UserID'].'(salescaseindex,salescaseref,salescasedescription,
salesman,debtorname,branchcode,commencementdate,enquiryvalue)
SELECT  salescase.salescaseindex,salescase.salescaseref,salescase.salescasedescription,
salescase.salesman,debtorsmaster.name,salescase.branchcode,salescase.commencementdate,
salescase.enquiryvalue FROM 
quotationvaluereports'.$_SESSION['UserID'].' RIGHT OUTER JOIN salescase ON  
 quotationvaluereports'.$_SESSION['UserID'].'.salescaseref= salescase.salescaseref
 INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
 UNION
 
SELECT  salescase.salescaseindex,salescase.salescaseref,salescase.salescasedescription,
salescase.salesman,debtorsmaster.name,salescase.branchcode,salescase.commencementdate,
salescase.enquiryvalue FROM 
ocvaluereports'.$_SESSION['UserID'].' RIGHT OUTER JOIN salescase ON  
 ocvaluereports'.$_SESSION['UserID'].'.salescaseref= salescase.salescaseref
 INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
 UNION
SELECT  salescase.salescaseindex,salescase.salescaseref,salescase.salescasedescription,
salescase.salesman,debtorsmaster.name,salescase.branchcode,salescase.commencementdate,
salescase.enquiryvalue FROM 
dcvaluereports'.$_SESSION['UserID'].' RIGHT OUTER JOIN salescase ON  
 dcvaluereports'.$_SESSION['UserID'].'.salescaseref= salescase.salescaseref
 INNER JOIN debtorsmaster ON debtorsmaster.debtorno = salescase.debtorno
 
 
 ';
 

mysqli_query($db,$SQL);
 $SQL='UPDATE advancedreporting'.$_SESSION['UserID'].'
SET advancedreporting'.$_SESSION['UserID'].'.dcvalue 
=
(
	SELECT dcvaluereports'.$_SESSION['UserID'].'.value
	FROM dcvaluereports'.$_SESSION['UserID'].'
	WHERE 
	
	advancedreporting'.$_SESSION['UserID'].'.salescaseref=
	dcvaluereports'.$_SESSION['UserID'].'.salescaseref
)

';
mysqli_query($db,$SQL);
 $SQL='UPDATE advancedreporting'.$_SESSION['UserID'].'
SET advancedreporting'.$_SESSION['UserID'].'.ocvalue 
=(
	SELECT ocvaluereports'.$_SESSION['UserID'].'.value
	FROM ocvaluereports'.$_SESSION['UserID'].'
	WHERE 
	
	advancedreporting'.$_SESSION['UserID'].'.salescaseref=
	ocvaluereports'.$_SESSION['UserID'].'.salescaseref
)';

mysqli_query($db,$SQL);

 $SQL='UPDATE advancedreporting'.$_SESSION['UserID'].'
SET advancedreporting'.$_SESSION['UserID'].'.quotationvalue 
=
(
	SELECT quotationvaluereports'.$_SESSION['UserID'].'.value
	FROM quotationvaluereports'.$_SESSION['UserID'].'
	WHERE 
	
	advancedreporting'.$_SESSION['UserID'].'.salescaseref=
	quotationvaluereports'.$_SESSION['UserID'].'.salescaseref
)

';mysqli_query($db,$SQL);


 $SQL1 = '
		DELETE FROM  advancedreporting'.$_SESSION['UserID'].'
		
		WHERE salescaseref IN (
		SELECT DISTINCT salesorders.salescaseref FROM salesorders INNER JOIN salescase ON 
		salesorders.salescaseref=salescase.salescaseref WHERE salescase.closed=1
		UNION
		SELECT DISTINCT ocs.salescaseref FROM ocs INNER JOIN salescase ON 
		ocs.salescaseref=salescase.salescaseref WHERE salescase.closed=1
		
		UNION
		SELECT DISTINCT dcs.salescaseref FROM dcs INNER JOIN salescase ON 
		dcs.salescaseref=salescase.salescaseref WHERE salescase.closed=1
		)
		OR salescaseref IN (
		
		SELECT salescaseref from salescase WHERE
		 
		 (salescase.commencementdate < "'.$_SESSION['startdate'].'" OR

		 salescase.commencementdate >"'.$_SESSION['enddate'].'")  
 
		
)


';

$SQL = '
		DELETE FROM  advancedreporting'.$_SESSION['UserID'].'
		
		WHERE salescaseref IN (
		SELECT DISTINCT salesorders.salescaseref FROM salesorders 
		UNION
		SELECT DISTINCT ocs.salescaseref FROM ocs
		
		UNION
		SELECT DISTINCT dcs.salescaseref FROM dcs 
		UNION
		
		SELECT salescaseref from salescase WHERE
		 
		 (salescase.commencementdate < "'.$_SESSION['startdate'].'" OR

		 salescase.commencementdate >"'.$_SESSION['enddate'].'")  
		 
		 UNION SELECT salescaseref FROM salescase WHERE salescase.closed=1
 
		
)


';
mysqli_query($db,$SQL);
 


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
		"ajax": "server_processing_reports.php",	
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
		"ajax": "server_processing_reports.php",	
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
		"ajax": "server_processing_reports.php",	
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
<table id="example" class="display" cellspacing="0" width="100%">


				<thead>
										<tr>
					<th>salescaseindex</th>
					<th>salescaseref</th>
					<th>salescasedescription</th>
					<th>salesman</th>
					
					
					<th>commencementdate</th>
					<th>debtorname</th>
					
					<th>enquiryvalue</th>
					<th>quotationvaluevalue</th>
					<th>ocvalue</th>	
					<th>dcvalue</th>
							
					</tr>
				</thead>
				<tfoot>
					<tr>
					<th>salescaseindex</th>
					<th>salescaseref</th>
					<th>salescasedescription</th>
					<th>salesman</th>
					<th>debtorname</th>
					
					<th>commencementdate</th>
					
					<th>enquiryvalue</th>
					<th>quotationvaluevalue</th>
					<th>ocvalue</th>	
					<th>dcvalue</th>
							
					</tr>
				</tfoot>
			</table>;


<?php
include('includes/footer.inc');
?>