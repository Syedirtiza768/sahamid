<?php

	$PathPrefix = "";
	include("includes/session.inc");

	if(isset($_GET['json'])){

		if($_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 8 || $_SESSION['AccessLevel'] == 10){
		
			//Sales Case
			$SQL = 'SELECT  salescase.salescaseindex,
							salescase.salescaseref,
							salescase.salesman,
							dcs.orderno,
							dcs.orddate,
							dcs.mp,
							debtorsmaster.name 
					FROM salescase 
					LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
					INNER JOIN dcs ON dcs.salescaseref = salescase.salescaseref
					WHERE salescase.closed = 0
					AND dcs.shop=1
					AND dcs.inprogress=0';
		
		}else{

			$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
			$res = mysqli_query($db, $SQL);

			$canAccess = [];

			while($row = mysqli_fetch_assoc($res))
				$canAccess[] = $row['can_access'];

			$SQL = 'SELECT  salescase.salescaseindex,
							salescase.salescaseref,
							salescase.salesman,
							dcs.orderno,
							dcs.orddate,
							dcs.mp,
							debtorsmaster.name 
					FROM salescase 
					INNER JOIN www_users ON www_users.realname = salescase.salesman
					INNER JOIN dcs ON dcs.salescaseref = salescase.salescaseref
					LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
					WHERE salescase.closed = 0
					AND dcs.shop=1
					AND dcs.inprogress=0
					AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
					OR www_users.userid IN ("'.implode('","', $canAccess).'") )';	
		
		}

		$res = mysqli_query($db, $SQL);

		$response = [];

		while($row = mysqli_fetch_assoc($res)){

			$row['orddate'] = date('Y/m/d',strtotime($row['orddate']));
			$row['mp'] = ($row['mp'] == 1) ? "yes":"no";
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

		<link rel="stylesheet" href="quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/nanoscroller/nanoscroller.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="quotation/assets/stylesheets/skins/default.css" />


		<script src="quotation/assets/vendor/modernizr/modernizr.js"></script>

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
				padding:4px 6px !important;
				vertical-align: middle !important;
			}

			.small{
				font-size: 12px;
			}

			.shrink{
				width: 1%;
				white-space: nowrap;
				padding:5px;
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
	      				<a href="<?php echo $RootPath; ?>/index.php" style="color: white; text-decoration: none;">Main Menu</a>
	      				<a class="bold" href="<?php echo $RootPath; ?>/Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	      			</span>
	      		</span>
	      		<input type="hidden" id="FormID" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
	      	</header>

	      	<div class="col-md-12" style="text-align: center; margin-top: 20px; margin-bottom: 50px;">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
	      					<th style="width: 1%; white-space: nowrap;">Sr #</th>
	      					<th style="width: 1%; white-space: nowrap;">Salescaseref</th>
	      					<th>Salesman</th>
	      					<th style="width: 1%; white-space: nowrap;">DC No</th>
	      					<th style="width: 50px; white-space: nowrap;">MP</th>
	      					<th style="width: 50px; white-space: nowrap;">Date</th>
	      					<th style="width: 50px; white-space: nowrap;">Client</th>
	      					<th colspan="3">Actions</th>
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
      	
		<script src="quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
		<script src="quotation/assets/javascripts/theme.js"></script>

		<script>
		
			(function( $ ) {

				'use strict';

				var datatableInit = function() {

					datatable = $('#datatable').DataTable({
						language: {
					        search: "_INPUT_",
					        searchPlaceholder: "Search..."
					    },
					    "pageLength": 20,
					    "columns": [
				            { "data": "salescaseindex" },
				            { "data": "salescaseref" },
				            { "data": "salesman" },
				            { "data": "orderno" },
				            { "data": "mp" },
				            { "data": "orddate" },
				            { "data": "name" },
				        ],
				        "columnDefs": [ 
				        	{
							    "targets": 1,
							    "className": "shrink",
							    "render": function ( data, type, row, meta ) {
							      return data;
							    }
						  	},
						  	{
							    "targets": 2,
							    "className": "shrink",
							    "render": function ( data, type, row, meta ) {
							      return data;
							    }
						  	},
						  	{
							    "targets": 4,
							    "className": "shrink",
							    "render": function ( data, type, row, meta ) {
							      return data;
							    }
						  	},
						  	{
							    "targets": 6,
							    "className": "shrink",
							    "render": function ( data, type, row, meta ) {
							      return data;
							    }
						  	},
						  	{
							    "targets": 7,
							    "data": "salescaseref",
							    "render": function ( data, type, row, meta ) {
							    	let html = "<a class='btn btn-info small' target='_blank'";
							    	html += " href='salescase/salescaseview.php?salescaseref="+data+"'>";
							    	html += "Open Salescase</a> ";
							    	html += "<a class='btn btn-warning small' target='_blank'";
							    	html += " href='shopdc/makedc.php?salescaseref="+data+"&";
							    	html += "ModifyOrderNumber="+row['orderno']+"'>";
							    	html += "Edit DC</a> ";
							    	return html;
							    }
						  	}  
						]
					});

					$('#datatable_length')
						.find("label")
						.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Shop DC's Without Items</h3>");

				};

				$(function() {
					datatableInit();
					$("tbody tr td").html("Loading...");
					
					$.ajax({
						type: 'GET',
						url: "shopDCList.php?json=true",
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

		</script>

	</body>
</html>