<?php

include('includes/session.inc');
?>
<?php

	header("Location: salescase/selectsalescaseclosed.php");
	exit;
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
		
	//Quotation Values
	$SQL = 'SELECT salesorders.salescaseref, salesorderdetails.orderno, SUM(salesorderdetails.unitprice*
		(1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity) as value
		from salesorderdetails INNER JOIN salesorderoptions on (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
		INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
		WHERE salesorderdetails.lineoptionno = 0 and salesorderoptions.optionno = 0
		GROUP BY salesorderdetails.orderno';
	
	$result = mysqli_query($db,$SQL);	
		
	$quotationValues = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$quotationValues[$row['orderno']] = $row['value'];
		
	}
	
	//Delivery Chalans
	$SQL = 'SELECT salescaseref,orderno FROM dcs 
		WHERE (dcs.orderno IN (SELECT MAX(orderno) FROM dcs GROUP BY salescaseref)
		OR dcs.orderno IS NULL)';
		
	$result = mysqli_query($db,$SQL);	
		
	$deliveryChalans = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$deliveryChalans[$row['salescaseref']] = $row['orderno'];
		
	}

	//Sales Orders
	$SQL = 'SELECT salescaseref,orderno FROM salesorders 
		WHERE (salesorders.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
		OR salesorders.orderno IS NULL)';
		
	$result = mysqli_query($db,$SQL);	
		
	$salesOrders = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$salesOrders[$row['salescaseref']] = $row['orderno'];
		
	}
	
	//Sales Case Comments
	$SQL = 'SELECT salescaseref,commentcode FROM salescasecomments 
		WHERE (salescasecomments.time IN (SELECT MAX(time) FROM salescasecomments GROUP BY salescaseref)
		OR salescasecomments.time IS NULL)';

	$result = mysqli_query($db,$SQL);	
		
	$salesCaseComments = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$salesCaseComments[$row['salescaseref']] = $row['commentcode'];
		
	}

	if($_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 8 || $_SESSION['AccessLevel'] == 10){
		
		//Sales Case
		$SQL = 'SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
		`salesman`,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
		`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
		`ocdocumentfile`, `ocdocumentdate` FROM salescase 
		LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
		WHERE salescase.closed = 1';
	
	}else{

		$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
		$res = mysqli_query($db, $SQL);

		$canAccess = [];

		while($row = mysqli_fetch_assoc($res))
			$canAccess[] = $row['can_access'];

		$SQL = 'SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
		salescase.salesman,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
		`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
		`ocdocumentfile`, `ocdocumentdate` FROM salescase 
		INNER JOIN www_users ON www_users.realname = salescase.salesman
		LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
		WHERE salescase.closed = 1
		AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
		OR www_users.userid IN ("'.implode('","', $canAccess).'") )';	
	
	}
		
	$result = mysqli_query($db,$SQL);
	
	$data = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$d = [];
		
		$name = utf8_encode($row['name']);
		$description = utf8_encode($row['salescasedescription']);
		
		$d[] = $row['salescaseindex'];
		$d[] = '<a href="salescase.php/?salescaseref='.$row['salescaseref'].'" target="_blank">'.$row['salescaseref'].'</a>';
		$d[] = stripslashes($description);
		$d[] = $row['salesman'];
		$d[] = $row['commencementdate'];
		$d[] = $name;
		$d[] = $row['enquirydate'];
		$d[] = locale_number_format($row['enquiryvalue'],0);
		$d[] = '<a href="PDFQuotation.php/?QuotationNo='.$salesOrders[$row['salescaseref']].'" target="_blank">'.$salesOrders[$row['salescaseref']].'</a>';
		$d[] = locale_number_format($quotationValues[$salesOrders[$row['salescaseref']]],0);
		$d[] = $row['podate'];
		$d[] = '<a href="javascript:void(0);"
			NAME="My Window Name"  title=" My title here "
			onClick=window.open("dclinks.php/?dclink='.$deliveryChalans[$row['salescaseref']].'","Ratting","width=550,height=170,0,status=0,scrollbars=1")>' . $deliveryChalans[$row['salescaseref']]  . '</a>';
		$href='"javascript:void(0);"
 NAME="My Window Name"  title=" My title here "
 onClick=window.open("lastcomment.php/?commentcode='.$salesCaseComments[$row['salescaseref']].'","Ratting","width=550,height=170,0,status=0,scrollbars=1")';
		$d[] = '<a href='.$href.' >' . $salesCaseComments[$row['salescaseref']]  . '</a>';
		
		$data[] = $d;
		
	}
	
	function to_time_custom($date){
		
		$firstExplode = explode(" ", $date);
		$secondExplode = explode("-",$firstExplode[0]);
		
		return $secondExplode[2]."/".$secondExplode[1]."/".$secondExplode[0];
	}
	
	$serialized = json_encode($data);
	
	//file_put_contents("testfile1234.txt", '{"data":'.$serialized.'}');
	echo "<script>";
	echo "var dataset=".$serialized.";";
	echo "</script>";
	
	/*print_r($data);
	
	return;		
		
			
	$SQL = 'SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
		`salesman`, `name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
		`enquirydate`,enquiryvalue, `lastquotationdate`, salesorders.orderno,  `pofile`, salescase.`podate`,
		`ocdocumentfile`, `ocdocumentdate`, dispatchid, commentcode FROM salescase 
		
		LEFT OUTER JOIN dc ON salescase.salescaseref = dc.salescaseref
		LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
		LEFT OUTER JOIN salesorders ON salescase.salescaseref=salesorders.salescaseref
		LEFT OUTER JOIN salescasecomments ON salescasecomments.salescaseref =  salescase.salescaseref
 
		WHERE (salescasecomments.time IN (SELECT MAX(time) FROM salescasecomments GROUP BY salescaseref)
			OR salescasecomments.time IS NULL)
		AND (salesorders.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
			OR salesorders.orderno IS NULL)	
		AND (dc.dispatchid IN (SELECT MAX(dispatchid) FROM dc GROUP BY salescaseref)
			OR dc.dispatchid IS NULL)
		AND salescase.closed = 1';

	$result = mysqli_query($db,$SQL);
	
	$somethingElse = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$somethingElse[] = [
		
			'0' 	=> $row['salescaseindex'],
			'1' 	=> $row['salescaseref'],
			'2' 	=> $row['salescasedescription'],
			'3' 	=> $row['salesman'],
			'4' 	=> $row['name'],
			'5' 	=> $row['salescase'],
			'6' 	=> $row['branchcode'],
			'7' 	=> $row['commencementdate'],
			'8' 	=> $row['enquiryfile'],
			'9' 	=> $row['enquirydate'],
			'10' 	=> $row['enquiryvalue'],
			'11' 	=> $row['lastquotationdate'],
			'12' 	=> $row['orderno'],
			'13' 	=> $row['pofile'],
	
		];
		
	}
	
	$file = fopen("somefile.txt","w");
	$abc = $somethingElse;
	fwrite($file,$abc);
	fclose($file);
	
	
	echo $abc;
	return;*/
		
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
  `commentcode` int(40) NOT NULL
) 
';
//mysqli_query($db,$SQL);
$SQL ='
CREATE TABLE IF NOT EXISTS `quotationvalue'.$_SESSION['UserID'].'` (
  `quotationvalueindex` int(11) NOT NULL,
  `salescaseref` varchar(40) NOT NULL,
  `orderno` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  `podate` datetime NOT NULL
)
';

//mysqli_query($db,$SQL);

$SQL = 'truncate salescasereporting'.$_SESSION['UserID'].'';
//mysqli_query($db,$SQL);
$SQL = 'truncate quotationvalue'.$_SESSION['UserID'].'';
//mysqli_query($db,$SQL);

$SQL = '
INSERT INTO quotationvalue'.$_SESSION['UserID'].'(salescaseref,orderno,value)

SELECT salesorders.salescaseref, salesorderdetails.orderno, SUM(salesorderdetails.unitprice*
(1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity
) as value
from salesorderdetails INNER JOIN salesorderoptions on (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
WHERE salesorderdetails.lineoptionno = 0
 and salesorderoptions.optionno = 0

GROUP BY salesorderdetails.orderno

';
//mysqli_query($db,$SQL);

$SQL = '
INSERT INTO `salescasereporting'.$_SESSION['UserID'].'`(`salescaseindex`, salescaseref, `salescasedescription`,
 `salesman`, `debtorname`, `branchcode`, `commencementdate`,`enquiryfile`,
 `enquirydate`, enquiryvalue, `lastquotationdate`, orderno,  `pofile`, `podate`, `ocdocumentfile`, `ocdocumentdate`,
 dclink, commentcode, quotationvalue)
SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
 `salesman`, `name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
 `enquirydate`,enquiryvalue, `lastquotationdate`, salesorders.orderno,  `pofile`, salescase.`podate`,
 `ocdocumentfile`, `ocdocumentdate`, dispatchid, commentcode, quotationvalue'.$_SESSION['UserID'].'.value
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
		
		AND salescase.closed = 0
		
'


		
;	

//mysqli_query($db,$SQL);


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
	<script type="text/javascript" src="extensions/responsive/js/dataTables.responsive.min.js"></script>

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
echo '<script type="text/javascript" src = "'.$RootPath.'/extensions/plugins/sorting/date-uk.js"></script>';
if ($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 23 OR $_SESSION['AccessLevel'] == 27)
{
echo'
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		"responsive": true,
		columns:[
			null,
			null,
			null,
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			null,
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			null,
		],
		"processing": true,
		data: dataset,
		"lengthMenu": [[10], [10]],
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
		data: dataset,
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
	var table = $(\'#example\').DataTable( {
		"responsive": true,
		columns:[
			null,
			null,
			null,
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			null,
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			null,
		],
		"processing": true,
		data: dataset,
		columnDefs:[
			{type: "date-eu", targets:5}
		],
		"lengthMenu": [[10], [10]],
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
		data: dataset,
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
		"responsive": true,
		columns:[
			null,
			null,
			null,
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			null,
			null,
			{
				"type" : "date",
				"render":function(value){
					if(value == "0000-00-00 00:00:00")
						return "";
					var date = value.split(" ")[0];
					var splitDate = date.split("-");
					return splitDate[0]+"/"+splitDate[1]+"/"+splitDate[2];
				}
			},
			null,
			null,
		],
		"processing": true,
		data: dataset,
		"lengthMenu": [[10], [10]],	
		"dom": \'Blfrtip\',
        buttons: [
         
        ]
     
		} 
		
		
		
		
		).columnFilter();
	
		
} )
$(document).ready(function() {
    $(\'#example\').DataTable( {
      "dom": \'Blfrtip\',
	  data: dataset,
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
?>
<table id="example" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
					<th>Sno.</th>
					<th>Sales Case Ref</th>
					<th>Description</th>
					<th>Salesman</th>
					<th>Start Date</th>
					<th>Client's Name</th>
					<th>Enquiry Date</th>
					<th>Enquiry Value</th>
					<th>Quote Link</th>
					<th>Quote Value</th>
					<th>PO Date</th>
					<th>DC Links</th>
					<th>Last Comment</th>
					</tr>
				</thead>
				<tbody>
					<?php
						/*foreach($data as $dat){
							echo "<tr>";
							echo "<td>".$dat['salescaseindex']."</td>";
							echo "<td>".$dat['salescaseref']."</td>";
							echo "<td>".$dat['salescasedescription']."</td>";
							echo "<td>".$dat['salesman']."</td>";
							echo "<td>".$dat['commencementdate']."</td>";
							echo "<td>".$dat['name']."</td>";
							echo "<td>".$dat['enquirydate']."</td>";
							echo "<td>".$dat['enquiryvalue']."</td>";
							echo "<td>".$dat['orderno']."</td>";
							echo "<td>".$dat['quotationValue']."</td>";
							echo "<td>".$dat['enquiryvalue']."</td>";
							echo "<td>".$dat['enquiryvalue']."</td>";
							echo "<td>".$dat['enquiryvalue']."</td>";
							echo "</tr>";
						}	*/	
					?>
				</tbody>
				<tfoot>
					<tr>
					<th>Sno.</th>
					<th>Sales Case Ref</th>
					<th>Description</th>
					<th>Salesman</th>
					<th>Start Date</th>
					<th>Client's Name</th>
					<th>Enquiry Date</th>
					<th>Enquiry Value</th>
					<th>Quote Link</th>
					<th>Quote Value</th>
					<th>PO Date</th>
					<th>DC Links</th>
					<th>Last Comment</th>
					</tr>
				</tfoot>
			</table>;

<?php
include('includes/footer.inc');
?>