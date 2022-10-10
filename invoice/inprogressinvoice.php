<?php 
	if (!isset($PathPrefix)) {
		$PathPrefix='../';
	}
	include('../includes/session.inc');
	include('misc.php');

	$db = createDBConnection();

	$SQL = "SELECT * FROM dcgroups WHERE status = 0";
	$res = mysqli_query($db, $SQL);

	$pInv = [];

	while($row = mysqli_fetch_assoc($res)){

		$invoice = [];
		$invoice['group'] = $row['id'];
		$dcnos 	 = $row['dcnos'];

		$invoice['dc'] = [];
		$invoice['dclist'] = $dcnos;
		$invoice['dclistn'] = '';
		$dcListOld  = explode(",",$dcnos);

		foreach($dcListOld as $dc)
			if(trim($dc) != ""){
				$invoice['dc'][] = $dc;
				$invoice['dclistn'] .= $dc."<br>";
			}

		$SQL = "SELECT * FROM invoice 
				WHERE groupid='".$invoice['group']."'
				AND inprogress=1";
		$countResult = mysqli_query($db,$SQL);
		$invoiceCount = mysqli_num_rows($countResult);
		$invoice['existing'] = 0;

		if($invoiceCount > 0){
			$invoice['existing'] = 1;
			$invoice['invoiceno'] =  mysqli_fetch_assoc($countResult)['invoiceno'];
		}

		$pInv[] = $invoice;

	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>S A Hamid ERP</title>
		<meta name="keywords" content="HTML5 Admin Template" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/select2/select2.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>
		<script>
			var datatable = null;
		</script>
		<style>
			.dataTables_filter label{
				width: 100% !important;
			}

			.dataTables_filter input{
			    border: 1px #ccc solid;
    			border-radius: 5px;
			}

			.selected{
				background-color: #acbad4 !important;
			}

			th{
				text-align: center;
			}

		</style>

	</head>
	<body>
   
		<section class="body">
	      	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
				<span style="color:white">
					<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
					&nbsp;|&nbsp;
					<span style="color:#ccc">
						<?php echo stripslashes($_SESSION['UsersRealName']); ?>
				  </span>
					<span class="pull-right" style="background:#424242; padding: 0 10px;">
						<a href="<?php echo $RootPath; ?>/../index.php" style="color: white; text-decoration: none;">Main Menu</a>
						<a class="bold" href="<?php echo $RootPath; ?>/../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
					</span>
				</span>
			</header>

	      	<div class="row" style="margin: 10px; border-bottom: 2px #424242 solid; padding-bottom: 10px">
		      	<div class="col-md-6">
			      	<h2 style="margin: 0px; padding: 0px; font-variant: petite-caps;">Invoicing</h2>
			    </div>

			    <div class="col-md-6">
					<div class="btn-group btn-group-justified">
						<a class="btn btn-default" role="button" href="newinvoice.php">Create New</a>
						<a class="btn btn-default" role="button" style="background-color: #424242; color: white" href="inprogressinvoice.php">InProgress</a>
						<a class="btn btn-default" role="button" href="completelyinvoiced.php">Completely Invoiced</a>
					</div>      	
			    </div>
	      	</div>

	      	<div class="col-md-12" style="text-align: center; border-bottom: 2px #424242 solid">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
	      					<th>Group ID</th>
	      					<th>DC List</th>
	      					<th>Action</th>
	      				</tr>
	      			</thead>
	      			<tbody>
	      				<?php 
	      					foreach($pInv as $inv){
	      						echo '<tr>';
		      					echo '<td valign="center" style="vertical-align: middle;">'.$inv['group'].'</td>';
		      					echo '<td valign="center" style="vertical-align: middle;">'.$inv['dclistn'].'</td>';
		      					if($inv['existing'] == 0)
		      						echo '<td valign="center" style="vertical-align: middle;">
		      								<a class="btn btn-success" style="width:300px;" target="_blank" href="continueInvoiceOnDCGroup.php?groupID='.$inv['group'].'">Create New Invoice
		      								</a>
		      							</td>';
		      					else{
		      						echo '<td valign="center" style="vertical-align: middle;">
		      							<a class="btn btn-info" style="width:300px;" href="invoice.php?ModifyOrderNumber='.$inv['invoiceno'].'">
		      								Continue
		      							</a><br>
		      							<a class="btn btn-danger" style="width:300px;" href="discardInvoice.php?invoice='.$inv['invoiceno'].'">
		      								Discard
		      							</a>
		      							</td>';
		      					}
		      					echo '</tr>';
	      					}
	      				?>
	      			</tbody>
	      		</table>
	      		
	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>
		</section>

		<script src="../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../quotation/assets/javascripts/theme.js"></script>
		<script src="../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>


	</body>
	<script>
		
		(function( $ ) {

			'use strict';

			var datatableInit = function() {

				$('#datatable').dataTable({
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    }
				});

				$('#datatable_length').find("label").html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Partially Invoiced DC's</h3>")

			};

			$(function() {
				datatableInit();
			});

		}).apply( this, [ jQuery ]);
		
		

	</script>
</html>
