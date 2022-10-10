<?php

	$PathPrefix = "../../";
	include("../../includes/session.inc");
	
	if(!userHasPermission($db, 'shopsale_list')){
		
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
		
	}

	if(isset($_GET['json'])){

		$from 	= trim($_GET['from']);
		$to 	= trim($_GET['to']);

		$SQL = "SELECT  shopsale.orderno as sr,
						shopsale.mp,
						shopsale.payment,
						shopsale.complete,
						shopsale.orddate,
						shopsale.advance,
						shopsale.branchcode,
						shopsale.crname,
						shopsale.salesman,
						custbranch.brname as name,
						(SUM(shopsalelines.price*shopsalelines.quantity) * (1 - (shopsale.discount/100))) - shopsale.discountPKR as amt
				FROM shopsale
				INNER JOIN shopsalelines ON shopsale.orderno = shopsalelines.orderno
				INNER JOIN custbranch ON shopsale.branchcode = custbranch.branchcode
				WHERE shopsale.orddate >= '$from'
				AND shopsale.orddate <= '$to'
				GROUP BY shopsalelines.orderno";

		$res = mysqli_query($db, $SQL);

		$response = [];

		while($row = mysqli_fetch_assoc($res)){

			$row['orddate'] = date('Y/m/d',strtotime($row['orddate']));
			
			if($row['branchcode'] == "WALKIN01"){
				$row['name'] = html_entity_decode($row['crname']);
			}
			
			$response[] = $row;

		}

		echo json_encode($response);
		return;

	}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>ERP-SAHamid</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/nanoscroller/nanoscroller.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="../../quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../parchi/inward/assets/searchSelect.css" />


		<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>

		<style type="text/css">
			table{
				margin-top: 20px;
				margin-bottom: 20px;
			}
			.inputstyle{
				height: 25px;
				border:1px solid #424242;
				border-radius: 5px;
				margin-left: 5px;
			}
			.itemrow{
				margin-bottom: 10px
			}
			.itemname {
			    width: 400px;
			    padding: 5px;
			}
			.itemquantity, .discount {
			    width: 80px;
			    text-align: center;
			}
			.actualprice, .listprice, .itemprice{
				width: 90px;
				text-align: center;
			}
			.removeitem{
				width: 100px
			}
			.noCustomerSelected{
				text-align: center;
				width: 100%;
				height: 100px
			}
			.itemsContainer{
				width: 100%;
			}
			.itemdet, .notes{
				width: 100%;
				height: 40px;
				min-height: 40px;
				max-height: 40px;
				border: 1px solid #424242;
				border-radius: 7px;
				padding: 3px;
			}
			.number{
				border: 1px solid #424242;
				border-radius: 7px;
			}
			.quantity{
				width: 50px;
			}
			.price{
				width: 80px;
			}
			.subtotal{
				width: 100px;
			}
			.uom{
				padding: 0px;
				height: auto;
			}
			.color-white{
				color: white;
			}
			.input-info{
				width: 100%;
				border: 1px solid #424242;
				border-radius: 7px;
			}
			#clientslist, #dbalist, #debtortype, #salesman{
				width: 100%; 
				border: 1px solid #424242; 
				padding: 0px; 
				height: 30px;
			}
			#proceed{
				width: 100%; 
				margin-bottom: 15px; 
				padding: 10px 0;
			}
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

			td{
				padding:4px 0px !important;
				vertical-align: middle !important;
			}

			.small{
				font-size: 12px;
			}

			.datetype{
				font-size: 14px;
			    border: 1px solid #424242;
			    border-radius: 7px;
			}

		</style>

		<script>
			var datatable = null;
		</script>
	</head>
	<body>

		<section class="body" style="overflow: auto">

	      	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
	      		<span style="color:white">
	      			<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
	      			&nbsp;|&nbsp;
	      			<span style="color:#ccc">
	      				<?php echo stripslashes($_SESSION['UsersRealName']); ?>
	          		</span>
	      			<span class="pull-right" style="background:#424242; padding: 0 10px;">
	      				<a href="<?php echo $RootPath; ?>/../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
	      				<a class="bold" href="<?php echo $RootPath; ?>/../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	      			</span>
	      		</span>
	      		<input type="hidden" id="FormID" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
	      	</header>

	      	<div class="col-md-12" style="text-align: center; margin-top: 20px; margin-bottom: 50px;">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
	      					<th style="width: 1%; white-space: nowrap;">Sr #</th>
	      					<th style="width: 1%; white-space: nowrap;">CCode</th>
	      					<th>Name</th>
	      					<th>Salesman</th>
	      					<th style="width: 1%; white-space: nowrap;">Amount <sub>PKR</sub></th>
	      					<th style="width: 1%; white-space: nowrap;">Advance <sub>PKR</sub></th>
	      					<th style="width: 50px; white-space: nowrap;">Date</th>
	      					<th>Add Items</th>
	      					<th>Orignal</th>
	      					<th>Print</th>
	      					<th>Internal</th>
	      				</tr>
	      			</thead>
	      			<tbody>
	      				
	      			</tbody>
	      		</table>
	      	</div>

	      	<footer style="background:#424242; bottom:0; width:100%; position: fixed; text-align:center; padding: 5px; z-index: 10">
      			Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
      		</footer>
	      	
		</section>
      	
		<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
		<script src="../../quotation/assets/javascripts/theme.js"></script>
		<script src="../../media/js/dataTables.buttons.js"></script>
		<script src="../../media/js/buttons.html5.js"></script>
		<script src="../parchi/inward/assets/searchSelect.js"></script>

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
					    "columns": [
				            { "data": "sr" },
				            { "data": "branchcode" },
				            { "data": "name" },
				            { "data": "salesman" },
				            { "data": "amt" },
				            { "data": "advance" },
				            { "data": "orddate" },
				        ],
				        "columnDefs": [ 
						  	{
							    "targets": 7,
							    "data": "sr",
							    "render": function ( data, type, row, meta ) {
							    	
									let html = "";
									<?php if(userHasPermission($db, 'shop_sale_internal_view')){ ?>
							    	if(row['complete'] == 0){
							    		html += "<a class='btn btn-warning small' href='editShopSale.php?orderno="+data+"' target='_blank'>Add Internal Items</a> ";
							    	}
									<?php } ?>
									
							      	return html;
							    
								}
						  	},
						  	{
							    "targets": 8,
							    "data": "sr",
							    "render": function ( data, type, row, meta ) {
							    	
									let html = "";
							    	html += "<a class='btn btn-danger small' ";
							    	html += "href='shopSalePrint.php?orderno="+data+"&orignal' target='_blank'>";
							    	html += "Print</a>";
							      	return html;
							    
								}
						  	},
						  	{
							    "targets": 9,
							    "data": "sr",
							    "render": function ( data, type, row, meta ) {
							    	
									let html = "";
							    	html += "<a class='btn btn-danger small' ";
							    	html += "href='shopSalePrint.php?orderno="+data+"' target='_blank'>";
							    	html += "Print</a>";
							      	return html;
							    
								}
						  	},
						  	{
							    "targets": 10,
							    "data": "sr",
							    "render": function ( data, type, row, meta ) {
							    	
									let html = "";
									html += "<a class='btn btn-info small'";
							    	html += "href='shopSalePrintInternal.php?orderno="+data+"&internal' target='_blank'>";
							    	html += "Internal Print</a>";
							      	
							      	return html;
							    
								}
						  	}      
						]
					});
					
					$('#datatable_length')
						.find("label")
						.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Shop Sale Reprint <input type='date' class='from_date datetype'> <span style='font-size:14px'>TO</span> <input type='date' class='to_date datetype'> <a class='btn btn-primary' style='font-variant-caps: normal' id='search-yay'>Search</a>&nbsp;<?php if(userHasPermission($db, 'create_shop_sale')){ ?><a class='btn btn-success' style='font-variant-caps: normal' target='_blank' href='shopSale.php'>New Sale</a><?php } ?>&nbsp;<?php if(in_array($_SESSION['AccessLevel'], ['8','10','22'])){ ?><a class='btn btn-info download-csv' style='font-variant-caps: normal'>CSV</a> <?php } ?></h3>");

				};

				$(function() {
					datatableInit();
				});

				$(document.body).on("click", "#search-yay", function(e){
					e.preventDefault();

					let from = $(".from_date").val();
					let to 	 = $(".to_date").val();

					if(from == ""){
						alert("From Date Is Required...");
						return;
					}

					if(to == ""){
						alert("To Date Is Required...");
						return;
					}

					$("tbody tr td").html("Loading...");
					
					datatable.clear().draw();

					$.ajax({
						type: 'GET',
						url: "shopSaleList.php?json=true&from="+from+"&to="+to,
						dataType: "json",
						success: function(response) { 
							datatable.rows.add(response).draw(false);
						},
						error: function(){
							$("tbody tr td").html("Error...");
						}
					});
				});

				$(document.body).on("click", ".download-csv", function(e){
					e.preventDefault();
					datatable.button('.buttons-csv').trigger();
				});

			}).apply( this, [ jQuery ]);

		</script>

	</body>
</html>