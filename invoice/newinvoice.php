<?php 
	if (!isset($PathPrefix)) {
		$PathPrefix='../';
	}
	include('../includes/session.inc');
	
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
						<a class="btn btn-default" role="button" style="background-color: #424242; color: white">Create New</a>
						<a class="btn btn-default" role="button" href="inprogressinvoice.php">InProgress</a>
						<a class="btn btn-default" role="button" href="completelyinvoiced.php">Completely Invoiced</a>
					</div>      	
			    </div>
	      	</div>

	      	<div class="col-md-12" style="text-align: center; border-bottom: 2px #424242 solid">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
	      					<th>DC#</th>
	      					<th>Customer</th>
	      					<th>Branch</th>
	      					<th>PO#</th>
	      					<th>DC Date</th>
	      					<th>Order Total (pkr)</th>
	      				</tr>
	      			</thead>
	      			<tbody>
	      				
	      			</tbody>
	      		</table>
	      		
	      	</div>

	      	<div class="col-md-12" id="selecteddc" style="padding-top: 15px; padding-bottom: 50px; border-bottom: 2px #424242 solid">
	      		<div class="row" style="padding-bottom: 10px; display: flex; align-items: center;">
		      		<div class="col-md-6">
		      			<h3 style="margin: 0px; padding: 0px;  font-variant: petite-caps;">Selected DC's</h3>	      			
		      		</div>
		      		<div class="col-md-6">
		      			<div class="btn-group btn-group-justified">
							<a class="btn btn-primary" role="button" id="newinvoicebutton">Make Invoice</a>
						</div> 
		      		</div>
	      		</div>
	      		<table class="table table-bordered table-striped mb-none">
	      			<thead>
	      				<tr style="color: white; background-color: #424242">
	      					<th>DC#</th>
	      					<th>Customer</th>
	      					<th>Branch</th>
	      					<th>PO#</th>
	      					<th>DC Date</th>
	      					<th>Order Total (pkr)</th>
	      				</tr>
	      			</thead>
	      			<tbody id="selecteddccontainer">
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

				datatable = $('#datatable').DataTable({
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    }
				});

				$('#datatable_length').find("label").html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Available DC's</h3>")

				$('#datatable tbody').on( 'click', 'tr', function () {
					if($(this).find('td').html() != "No data available in table" && $(this).find('td').html() != "Loading..." && $(this).find('td').html() != "Error..."){
						$(this).toggleClass('selected');
						if($(this).hasClass("selected")){
							var dcno = $(this).find(".dcno").html();
							var customer = $(this).find(".cus").html();
							var branch = $(this).find(".brn").html();
							var pono = $(this).find(".pono").html();
							var ordd = $(this).find(".ordd").html();
							var value = $(this).find(".ordv").html();
							
							addDC(dcno, customer, branch, pono, ordd, value);
			
						}else{
							$('#selecteddccontainer').find("#"+$(this).find(".dcno").html()+"").remove();
						}
					}
			    } );

			};

			$(function() {
				datatableInit();
				$("tbody tr td").html("Loading...");
				
				$.ajax({
					type: 'GET',
					url: "<?php echo $RootPath; ?>"+"/api/getCompletedDC.php",
					dataType: "json",
					success: function(response) { 
						datatable.rows.add(response).draw(false);
					},
					error: function(){
						$("tbody tr td").html("Error...");
					}
				});
			});

		}).apply( this, [ jQuery ]);
		
		function addDC(dc,customer,branch,po,date,value){
			
			var html = "<tr id='"+dc+"'>";
			html += "<td class='dcno'>"+dc+"</td>";
			html += "<td class='customer'>"+customer+"</td>";
			html += "<td class='branch'>"+branch+"</td>";
			html += "<td class='po'>"+po+"</td>";
			html += "<td class='date'>"+date+"</td>";
			html += "<td class='value'>"+value+"</td>";
			html += "</tr>";
						
			$('#selecteddccontainer').append(html);
		}
		
		$("#newinvoicebutton").click(function(e){
			e.preventDefault();
			
			var count = 0;
			
			$("#selecteddccontainer > tr").each(function(){
				count += 1;
			});
			
			if(count != 0){
				
				var dcno = "";
				
				$("#selecteddccontainer > tr").each(function(){
					
					dcno += $(this).find(".dcno").html() + ",";
					
				});
				
				callCreateInvocie(dcno);
				
			}else {
				swal("Alert","No DC Selected!","warning");
			}
			
		});
		
		function callCreateInvocie(dcnos){
			swal({
			  title: "Are you sure?",
			  text: "It will create new invoice with selected dc numbers.!",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-success",
			  confirmButtonText: "Yes, Create Invoice!",
			  showLoaderOnConfirm: true,
			  closeOnConfirm: false
			},
			function(){

				$.ajax({
					type: 'GET',
					url: "<?php echo $RootPath; ?>"+"/createNewInvoice.php?dcno="+dcnos,
					dataType: "json",
					success: function(response) {
						
						if(response.status == "warnings"){
							console.log("stop trying to funck with the system");
						}else if(response.status == "redirect"){
							window.location = "<?php echo $RootPath; ?>/invoice.php?ModifyOrderNumber="+response.invoiceno;
						}else{
							swal("Error","No valid argument found!","error");
						}
					},
					error: function(){
						swal("Error","Something didn't go the right way...","error");
					}
				});
	
			});
			
		}

	</script>
</html>
