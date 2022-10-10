<?php

	$PathPrefix = "../../../";
	include("../../../includes/session.inc");
	
	if(!userHasPermission($db,"list_inward_parchi")){
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}
	$_SESSION['svid']=$_GET['svid'];
    $_SESSION['filter']=$_GET['filter'];
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>List Inward Bazar Parchi</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/select2/select2.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


		<script src="../../../quotation/assets/vendor/modernizr/modernizr.js"></script>
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
						<a href="<?php echo $RootPath; ?>/../../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
						<a class="bold" href="<?php echo $RootPath; ?>/../../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
					</span>
				</span>
			</header>

	      	<div class="col-md-12" style="text-align: center; margin-top: 20px; margin-bottom: 30px;">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
							<th>SR#</th>
	      					<th>Parchi #</th>
	      					<th>SVID</th>
	      					<th>Vendor</th>
	      					<th>Date Created</th>
	      					<th>Amount</th>
                            <th>GST</th>
							<th>Advance</th>
	      					<th>Settled</th>
	      					<th>State</th>
							<th>Created By</th>
							<?php if(userHasPermission($db,"edit_inward_parchi")){ ?>
								<th>Edit</th>
							<?php } ?>
							<th>IGP</th>
	      					<th>Print</th>
							<?php if(userHasPermission($db,"inward_parchi_internal")){ ?>
								<th>Print</th>
							<?php } ?>
	      				</tr>
	      			</thead>
	      			<tbody>
	      				
	      			</tbody>
	      		</table>
	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index: 20">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>
		</section>

		<script src="../../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../../quotation/assets/javascripts/theme.js"></script>
		<script src="../../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../../../media/js/dataTables.buttons.js"></script>
		<script src="../../../media/js/buttons.html5.js"></script>


	</body>
	<script>
		
		(function( $ ) {

			'use strict';

			var datatableInit = function() {

				datatable = $('#datatable').DataTable({
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    },
				    buttons: [
				        { 
				            extend: 'csv',
				        }
				    ],
				});

				$('#datatable_length')
					.find("label")
					.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Inward Bazar Parchi <?php if(userHasPermission($db,'create_inward_parchi' )) echo "<a class='btn btn-success' style='font-variant-caps: normal' target='_blank' href='inwardParchi.php'>Make New</a><a class='btn btn-info' style='font-variant-caps: normal' id='download-csv'>Download CSV</a>"; ?></h3>");

			};

			$(function() {


				datatableInit();
				$("tbody tr td").html("Loading...");

				$.ajax({
					type: 'GET',

					url: `api/listInwardBazarParchiApi.php`,
					dataType: "json",
					success: function(response) { 
						datatable.rows.add(response).draw(false);
					},
					error: function(){
						$("tbody tr td").html("Error...");
					}
				});
			});

			$(document.body).on("click", "#download-csv", function(e){
				e.preventDefault();
				datatable.button('.buttons-csv').trigger();
			});

		}).apply( this, [ jQuery ]);

	</script>
</html>
