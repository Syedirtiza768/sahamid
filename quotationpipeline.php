<?php
include('includes/session.inc');
if (!userHasPermission($db, 'quotationpipeline')) {
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
if (!isset($RootPath)) {
	$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
}

$ViewTopic = isset($ViewTopic) ? '?ViewTopic=' . $ViewTopic : '';
$BookMark = isset($BookMark) ? '#' . $BookMark : '';
$StrictXHTML = False;

if (!headers_sent()) {
	if ($StrictXHTML) {
		header('Content-type: application/xhtml+xml; charset=utf-8');
	} else {
		header('Content-type: text/html; charset=utf-8');
	}
}
if ($Title == _('Copy a BOM to New Item Code')) { //solve the cannot modify heaer information in CopyBOM.php scritps
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

echo '<script type="text/javascript" src = "' . $RootPath . '/javascripts/MiscFunctions.js"></script>';


?>
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/Ionicons/css/ionicons.min.css">

<link rel="stylesheet" href="<?php echo $NewRootPath; ?>quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/dist/css/AdminLTE.min.css">
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/dist/css/skins/_all-skins.min.css">
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/plugins/pace/pace.min.css">
<link rel="stylesheet" href="<?php echo $NewRootPath; ?>shop/parchi/inward/assets/searchSelect.css">


<script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/jquery/dist/jquery.min.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/fastclick/lib/fastclick.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/Chart.js/Chart.js"></script>
<script src="<?php echo $NewRootPath; ?>quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
<script src="<?php echo $NewRootPath; ?>quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/dist/js/adminlte.min.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/PACE/pace.min.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/dist/js/demo.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/plugins/highcharts.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/plugins/funnel.js"></script>
<script src="<?php echo $NewRootPath; ?>shop/parchi/inward/assets/searchSelect.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/datatables/dataTables.buttons.min.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/datatables/buttons.html5.min.js"></script>
<script src="<?php echo $NewRootPath; ?>v2/assets/popper.min.js"></script>

<script src="<?php echo $NewRootPath; ?>quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>

</head>

<body>

	<?php

	include('erp/includes/header.php');

	echo '<input type="hidden" name="Lang" id="Lang" value="' . $Lang . '" />';
	?>
	<h4 style="margin: 0; background: #c5bd99; text-align: center; padding: 10px;">Quotation Pipeline</h4>
	<table>
		<thead class="hilight">
			<tr>
				<td colspan="2">
					<h4 style="margin:0; text-align: center;">Filters</h4>
				</td>
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
			if (isset($_POST['customer']) && $_POST['customer'] != "") {
				echo '<td>Customer: ' . $_POST['customer'] . '</td></tr><tr>';
			}

			if (isset($_POST['customertype']) && $_POST['customertype'] != "0") {
				echo '<td>Customer Type: ' . $_POST['customertype'] . '</td></tr><tr>';
			}
			if (isset($_POST['salesperson'])  && $_POST['customertype'] != "0") {
				echo '<td>Sales Person: ' . $_POST['salesperson'] . '</td></tr>';
			}
			?>
		</tr>
	</table>
	<div class="container-fluid">
		<table id="example" class="display responsive" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th style="width:5px">salescaseindex</th>
					<th style="width:5px">salescaseref</th>
					<th style="width:5px">salescasedescription</th>
					<th style="width:5px">salesman</th>
					<th style="width:5px">commencementdate</th>
					<th style="width:5px">debtorname</th>
					<th style="width:5px">enquiryvalue</th>
					<th style="width:5px">Quotation Value</th>
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
				</tr>
			</tfoot>
		</table>
	</div>
	<?php

	include('erp/includes/footer.php');

	if ($_SESSION['AccessLevel'] == 10 or $_SESSION['AccessLevel'] == 22 or $_SESSION['AccessLevel'] == 23 or $_SESSION['AccessLevel'] == 27) {
		echo '
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		responsive:true,
		"processing": true,
		"sAjaxDataProp":"",
		 "lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		"ajax": "quotationpipelineajax.php?start=' . $_POST["startdate"] . '&end=' . $_POST["enddate"] . '&cus=' . $_POST['customer'] . '&customertype=' . $_POST['customertype'] . '&slps=' . $_POST['salesperson'] . '",
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
	} else if ($_SESSION['AccessLevel'] == 11 or $_SESSION['AccessLevel'] == 8) {
		echo '
<script type="text/javascript" language="javascript" class="init">
$(document).ready(function() {
	$(\'#example\').DataTable( {
		responsive:true,
		"processing": true,
		"sAjaxDataProp":"",
		 "lengthMenu": [[10, 25, 50,100], [10, 25, 50,100]],
		"ajax": "quotationpipelineajax.php?start=' . $_POST["startdate"] . '&end=' . $_POST["enddate"] . '&cus=' . $_POST['customer'] . '&customertype=' . $_POST['customertype'] . '&slps=' . $_POST['salesperson'] . '",	
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
	} else {

		echo '
<script type="text/javascript" language="javascript" class="init">

$(document).ready(function() {
	$(\'#example\').DataTable( {
		responsive:true,
		"processing": true,
		"sAjaxDataProp":"",
		 "lengthMenu": [[10, 25, 50,100, -1], [10, 25, 50,100, "All"]],
		"ajax": "quotationpipelineajax.php?start=' . $_POST["startdate"] . '&end=' . $_POST["enddate"] . '&cus=' . $_POST['customer'] . '&customertype=' . $_POST['customertype'] . '&slps=' . $_POST['salesperson'] . '",	
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
	};
	?>