<?php 
	if (!isset($PathPrefix)) {
		$PathPrefix='../';
	}
	include('../includes/session.inc');
	include('misc.php');

	$db = createDBConnection();

	$SQL = "SELECT invoice.invoiceno,
				invoice.invoicedate,
				invoice.shopinvoiceno,
				invoice.groupid as id,
				dcgroups.dcnos as dcnos
			FROM invoice
			INNER JOIN dcgroups ON dcgroups.id = invoice.groupid 
			WHERE invoice.inprogress = 0
			AND invoice.returned = 0";

	$res = mysqli_query($db, $SQL);

	$pInv = [];
    $SQL = "SELECT debtorsmaster.name,debtorsmaster.debtorno,dcs.orderno FROM dcs 
                                INNER JOIN debtorsmaster ON debtorsmaster.debtorno = dcs.debtorno";

    $ress = mysqli_query($db,$SQL);
    $custinfo = [];
    while($row = mysqli_fetch_assoc($ress)){
        $custinfo[$row['orderno']]=$row;
    }
	while($row = mysqli_fetch_assoc($res)){

		$invoice = [];
		$client = null;

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

				if($client == null){
					/*$SQL = "SELECT dcs.debtorno FROM dcs
							WHERE dcs.orderno='".$dc."'";
							
					$clientres = mysqli_fetch_assoc(mysqli_query($db, $SQL));*/
					$debtorno  = $custinfo[$dc]['debtorno'];
					$client    = $custinfo[$dc]['name'];
				}

			}

		$pInv[$invoice['group']]['debtorno'] = $debtorno; 
		$pInv[$invoice['group']]['client'] = $client; 
		$pInv[$invoice['group']]['dclistn'] = $invoice['dclistn'];
		$pInv[$invoice['group']]['invoices'][] = [
				'invoiceno' => $row['invoiceno'],
				'shopinvoiceno' => $row['shopinvoiceno'],
				'date' => $row['invoicedate']
			];

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
			td{
				text-align: center;
			}

			.abc td{
				padding:5px 15px !important;
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
						<a class="btn btn-default" role="button" href="inprogressinvoice.php">InProgress</a>
						<a class="btn btn-default" role="button" style="background-color: #424242; color: white" href="completelyinvoiced.php">Completely Invoiced</a>
					</div>      	
			    </div>
	      	</div>

	      	<div class="col-md-12" style="text-align: center; border-bottom: 2px #424242 solid">
	      		<table class="table table-bordered table-striped table-responsive mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
	      					<th>Group ID</th>
	      					<th>Client</th>
	      					<th>DC List</th>
	      					<th>Invoices</th>
	      				</tr>
	      			</thead>
	      			<tbody>
	      				<?php 
	      					foreach($pInv as $groupID => $inv){
	      						echo '<tr>';
		      					echo '<td valign="center" style="vertical-align: middle;">'.$groupID.'</td>';
		      					echo '<td valign="center" style="vertical-align: middle;">';
		      					echo '<a target="_blank" href="'.$RootPath.'/../adjustments.php?';
		      					echo 'DebtorNo='.$inv['debtorno'].'">';
		      					echo $inv['client'].'</a></td>';
		      					echo '<td valign="center" style="vertical-align: middle;">'.$inv['dclistn'].'</td>';
		      					echo '<td valign="center" style="vertical-align: middle; text-align:-webkit-center">';
		      					echo '<table border="1" class="abc" width="100%">';
		      					echo '<tr>';
		      					echo '<th style="padding: 5px">Invoice</th>';
		      					echo '<th style="padding: 5px">Print</th>';
		      					echo '<th style="padding: 5px">Shop Invoice #</th>';
		      					echo '<th style="padding: 5px">IncludedDC</th>';
		      					echo '<th style="padding: 5px">Date</th>';
		      					echo '</tr>';
		      					foreach ($inv['invoices'] as $invoice) {

		      						$SQL = "SELECT orderno FROM invoicedetails WHERE invoiceno='".$invoice['invoiceno']."' GROUP BY orderno";
		      						$resDC = mysqli_query($db, $SQL);
					
		      			?>
	      								<tr>
	      									<td style="vertical-align: middle;""><?php echo $invoice['invoiceno']; ?></td>
	      									<td>
	      										<a class="btn btn-info" href="<?php echo $RootPath; ?>/../PDFInvoice.php?InvoiceNo=<?php echo $invoice['invoiceno']; ?>">Internal</a>
	      										<a class="btn btn-primary" href="<?php echo $RootPath; ?>/../PDFInvoiceCommercial.php?InvoiceNo=<?php echo $invoice['invoiceno']; ?>">Commercial</a>
	      										<a class="btn btn-warning" href="<?php echo $RootPath; ?>/../PDFInvoiceTax.php?InvoiceNo=<?php echo $invoice['invoiceno']; ?>">Tax</a>
	      									</td>
	      									<td><?php echo $invoice['shopinvoiceno']; ?></td>
	      									<td>
	      									<?php
	      										while($r = mysqli_fetch_assoc($resDC)){
	      											echo $r['orderno']."<br>";
	      										}
	      									?>
	      									</td>
	      									<td><?php echo date("d/m/Y",strtotime($invoice['date'])); ?></td>
	      								</tr>
		      						
		      						<?php
		      						//echo $invoice."<br>";
		      					}
		      					echo '</table>';
		      					echo '</td>';
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

				$('#datatable_length').find("label").html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Invoiced DC Groups</h3>")

			};

			$(function() {
				datatableInit();
			});

		}).apply( this, [ jQuery ]);
		
		

	</script>
</html>
