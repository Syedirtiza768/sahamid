<?php 
	
	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
	if(!userHasPermission($db, 'Supp_Balance_Sheet')) {

			
			header("Location: /sahamid/v2/reportLinks.php");
			exit;
		}

?>
<!DOCTYPE html>
<html>
<head>

	<title>Supplier Balance</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="stylesheet" href="../../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../../../quotation/assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="../../../quotation/assets/vendor/sweetalert/sweetalert.css" />
	<link rel="stylesheet" href="../../../quotation/assets/stylesheets/skins/default.css" />
	<link rel="stylesheet" href="../../../quotation/assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<script src="../../../quotation/assets/vendor/modernizr/modernizr.js"></script>

	<style>
		th{
			text-align: center;
		}
		td{
			text-align: center;
			vertical-align: middle !important;
		}
		.dataTables_filter label{
			width: 100% !important;
		}

		.dataTables_filter input{
		    border: 1px #ccc solid;
			border-radius: 5px;
		}
		.textLeft{
			text-align: left;
		}
	</style>

	<script>
		var datatable = null;
	</script>

</head>
<body>
	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
      	<span style="color:white">
      		<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
      		&nbsp;|&nbsp;
      		<span style="color:#ccc">
      			<?php echo stripslashes($_SESSION['UsersRealName']); ?>
          </span>
      		<span class="pull-right" style="background:#424242; padding: 0 10px;">
      			<a href="<?php echo $RootPath; ?>/../../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
      			<a class="bold" href="<?php echo $RootPath; ?>/../../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
      		</span>
      	</span>
  	</header>

  	<div class="col-md-12" style="text-align: center; margin-top: 20px">
  		<table class="table table-bordered table-striped mb-none" id="datatable">
  			<thead>
  				<tr style="background-color: #424242; color: white">
  					<th style="width: 1%; white-space: nowrap;">Supplier #</th>
  					<th style="text-align: left;">Supplier Name</th>
					<th>Amount Due</th>
  					<th></th>
					<th>Ledger Status</th>
  				</tr>
  			</thead>
  			<tbody>
  				
  			</tbody>
  			<tfoot>
  				<tr style="background-color: #424242; color: white">
  					<th style="width: 1%; white-space: nowrap;">Supplier #</th>
  					<th style="text-align: left;">Supplier Name</th>
  					<th>Amount Due</th>
  					<th></th>
					<th>Ledger Status</th>
  				</tr>
  			</tfoot>
  		</table>
  		
  	</div>
	
	<div class="col-md-12">
	
		<div class="btn-danger" style="text-align:center; padding:20px; margin:20px 0; margin-bottom: 50px">
		
			<h3 style='margin:0; padding:0; font-variant: petite-caps;'><i class='fa fa-money' aria-hidden='true'></i> Total Balance Payable: <span id="totalBalance">Calculating Balance...</span> <span style="font-size:1.5rem">PKR</span></h3>
		
		</div>
	
	</div>

  	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index:150; padding: 5px">
		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	</footer>

	<script src="../../../quotation/assets/vendor/jquery/jquery.js"></script>
	<script src="../../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../../quotation/assets/javascripts/theme.js"></script>
	<script src="../../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
	<script src="../../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
	<script src="../../../quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>

	<script>
		(function( $ ) {

			'use strict';

			var datatableInit = function() {

				datatable = $('#datatable').DataTable({
					"aoColumnDefs": [
						{ "sClass": "textLeft", "aTargets": [ 1 ] }
					],
					"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    },
				    drawCallback: function () {
				      	var api = this.api();
				     	// $( api.table().footer() ).html(
				     	// 	'<tr style="background-color:#424242; color:white">'+
				     	// 	'<th>--</th><th>--</th><th>--</th>'+
				     	// 	'<th>'+api.column(3,{search:'applied'}).data().sum()+'</th>'+
				     	// 	'<th>'+api.column(4,{search:'applied'}).data().sum()+'</th>'+
				     	// 	'<th>'+api.column(5,{search:'applied'}).data().sum()+'</th>'+
				     	// 	'<th>'+api.column(6,{search:'applied'}).data().sum()+'</th>'+
				     	// 	'<th>'+api.column(7,{search:'applied'}).data().sum()+'</th></tr>'
				      // 	);
				    }
				});

				$('#datatable_length').find("label").html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'><i class='fa fa-money' aria-hidden='true'></i> Supplier Balance Sheet <a class='btn btn-warning' href='../../../SupplierAllocations.php' target='_blank'>Allocate Balance</a></h3>");
			};

			$(function() {
				datatableInit();
				$("tbody tr td").html("Loading...");
				
				$.ajax({
					type: 'GET',
					url: "<?php echo $RootPath; ?>"+"/SuppBalanceSheetAPI.php",
					dataType: "json",
					success: function(response) { 
						datatable.rows.add(response).draw(false);
					},
					error: function(){
						$("tbody tr td").html("Error...");
					}
				});
				
				$.ajax({
					type: 'GET',
					url: "<?php echo $RootPath; ?>"+"/TotalSuppBalanceAPI.php",
					dataType: "json",
					success: function(response) { 
						$("#totalBalance").html(response.totalBalance);
					},
					error: function(){
						$("#totalBalance").html("-Fetch Failed-");
					}
				});
				
			});

		}).apply( this, [ jQuery ]);
		
	</script>


</body>
</html>