<?php
	include('includes/session.inc');
    if(!userHasPermission($db, 'popipeline')) {
        header("Location: /sahamid/v2/reportLinks.php");
        exit;
    }
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
	
	include('erp/includes/head.inc');
	
	echo '<script type="text/javascript" src = "'.$RootPath.'/javascripts/MiscFunctions.js"></script>';
		
		
?>
	<link rel="shortcut icon" type="image/ico" href="http://www.datatables.net/favicon.ico">
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">
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

</head>
<body>

<?php

	include('erp/includes/header.php');

	echo '<input type="hidden" name="Lang" id="Lang" value="'.$Lang.'" />';
?>
<h4 style="margin: 0; background: #c5bd99; text-align: center; padding: 10px;">PO Pipeline</h4>
	<table>
		<thead class="hilight">
			<tr>
				<td colspan="2"><h4 style="margin:0; text-align: center;">Filters</h4></td>
			</tr>
		</thead>
		<tr>
			<td colspan="2">Between: 
				(<?php echo date("d/m/Y", strtotime($_POST['startdate'])); ?>)
				&nbsp;AND&nbsp; 
				(<?php echo date("d/m/Y", strtotime($_POST['enddate'])); ?>)
			</td>
		</tr>
		<tr>
			<?php 
				if(isset($_POST['customer']) && $_POST['customer'] != ""){
					echo '<td>Customer: '.$_POST['customer'].'</td></tr><tr>';
				}
				
				if(isset($_POST['customertype']) && $_POST['customertype'] != ""){
					echo '<td>Customer Type: '.$_POST['customertype'].'</td></tr><tr>';
				}
				if(isset($_POST['salesperson'])  && $_POST['salesperson'] != ""){
					echo '<td>Sales Person: '.$_POST['salesperson'].'</td></tr>';
				}
			?>
		</tr>
	</table>
<div class="container-fluid">
	<table id="example" class="display responsive" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th>salescaseindex</th>
				<th>salescaseref</th>
				<th>salescasedescription</th>
				<th>salesman</th>
				<th>commencementdate</th>
				<th>debtorname</th>
				<th>enquiryvalue</th>
				<th>Quotation Value</th>
				<th>OC Value</th>
				<th>DC Value</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>salescaseindex</th>
				<th>salescaseref</th>
				<th>salescasedescription</th>
				<th>salesman</th>
				<th>commencementdate</th>
				<th>debtorname</th>
				<th>enquiryvalue</th>
				<th>Quotation Value</th>
				<th>OC Value</th>
				<th>DC Value</th>
			</tr>
		</tfoot>
	</table>
</div>
<?php 
	
	include('erp/includes/footer.php'); 

if ($_SESSION['AccessLevel'] == 10 OR $_SESSION['AccessLevel'] == 22 OR $_SESSION['AccessLevel'] == 23 OR $_SESSION['AccessLevel'] == 27)
{
echo'
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		responsive:true,
		"processing": true,
		"sAjaxDataProp":"",
		 "lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		"ajax": "popipelineajax.php?start='.$_POST["startdate"].'&end='.$_POST["enddate"].'&cus='.$_POST['customer'].'&customertype='.$_POST['customertype'].'&slps='.$_POST['salesperson'].'",
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
		responsive:true,
		"processing": true,
		"sAjaxDataProp":"",
		 "lengthMenu": [[10, 25, 50,100], [10, 25, 50,100]],
		"ajax": "popipelineajax.php?start='.$_POST["startdate"].'&end='.$_POST["enddate"].'&cus='.$_POST['customer'].'&customertype='.$_POST['customertype'].'&slps='.$_POST['salesperson'].'",	
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
		responsive:true,
		"processing": true,
		"sAjaxDataProp":"",
		 "lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		"ajax": "popipelineajax.php?start='.$_POST["startdate"].'&end='.$_POST["enddate"].'&cus='.$_POST['customer'].'&customertype='.$_POST['customertype'].'&slps='.$_POST['salesperson'].'",	
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
