<?php

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	if(isset($_GET['json'])){
		
		$SQL = "SELECT 	debtorsmaster.name, debtorsmaster.dba,
						SUM(CASE WHEN (invoice.gst = 'inclusive' AND invoice.services = 1) 
							THEN ((invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity)/1.16)
						WHEN (invoice.gst = 'inclusive' AND invoice.services = 0)
							THEN ((invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity)/1.17)
						ELSE
							(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity)
						END) as value
				FROM invoicedetails
				INNER JOIN invoice ON invoice.invoiceno = invoicedetails.invoiceno
				INNER JOIN invoiceoptions ON (invoicedetails.invoiceno = invoiceoptions.invoiceno 
					AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno)
				INNER JOIN stockmaster ON invoicedetails.stkcode = stockmaster.stockid
				INNER JOIN debtorsmaster ON invoice.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch ON custbranch.branchcode = invoice.branchcode
				WHERE invoice.returned = 0
				AND invoice.inprogress = 0";
				
		/*
			$SQL = "SELECT 	debtorsmaster.name, debtorsmaster.dba,
						CASE  	WHEN  dcs.gst LIKE '%inclusive%' 
									THEN (dcdetails.unitprice* (1 - dcdetails.discountpercent)
											*dcdetails.quantity*dcoptions.quantity)*0.83 
								ELSE 
									(dcdetails.unitprice* (1 - dcdetails.discountpercent)
									*dcdetails.quantity*dcoptions.quantity
						) END as value
				FROM dcdetails
				INNER JOIN dcs ON dcs.orderno = dcdetails.orderno
				INNER JOIN dcoptions ON (dcdetails.orderno = dcoptions.orderno 
					AND dcdetails.orderlineno = dcoptions.lineno)
				INNER JOIN stockmaster ON dcdetails.stkcode = stockmaster.stockid
				INNER JOIN debtorsmaster ON dcs.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch ON custbranch.branchcode = dcs.branchcode
				WHERE stockmaster.brand = '".$_POST['manufacturer']."'";	
		*/
		
		if($_POST['manufacturer'] != "all"){
			$SQL .= " AND stockmaster.brand = '".$_POST['manufacturer']."'";
		}
		
		if(isset($_POST['stockid']) && $_POST['stockid'] != ""){
			$SQL .= " AND stockmaster.stockid = '".$_POST['stockid']."'";
		}		
		
		if(isset($_POST['from']) && $_POST['from'] != ""){
			$SQL .= " AND invoice.invoicesdate >= '".$_POST['from']."'";
		}
		
		if(isset($_POST['to']) && $_POST['to'] != ""){
			$SQL .= " AND invoice.invoicesdate <= '".$_POST['to']."'";
		}
		
		$SQL .= " GROUP BY custbranch.branchcode";
		
		$response = [];
				
		$res = mysqli_query($db, $SQL);
		
		while($row = mysqli_fetch_assoc($res)){
			$response[] = [
				0 => $row['name'],
				1 => $row['dba'],
				2 => round($row['value'])
			];
		}
		
		echo json_encode($response);
		return;
	}
	
	$SQL = "SELECT manufacturers_id as id, manufacturers_name as name FROM manufacturers";
	$manufacturers = mysqli_query($db, $SQL);

?>
<!DOCTYPE html>
<html>
<head>

	<title>Brandwise Sales To Customer</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
	<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>

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
	<style>
	.table-head{position: relative; top:60px; background: #424242; left: 0; color: white;} .table-head th{border: 1px solid white;} .center-fit{width: 1%; white-space: nowrap; text-align: center;} .modal{display:none;position:fixed;z-index:1000;left:0;top: 0;width: 100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4);}.modal-content{background-color:#fefefe;margin: 5% auto;padding: 20px;border: 1px solid #888;width: 80%;}.close {color: #aaa;float: right;font-size: 28px;font-weight: bold;}.close:hover,.close:focus{color: black;text-decoration: none;cursor: pointer;}.tasktitle{border:1px solid #424242; border-radius: 7px; padding: 5px; width: 100%}.taskdescription{border:1px solid #424242; border-radius: 7px;padding: 5px; width: 100%; max-width: 100%; min-width: 100%;min-height: 100px;max-height: 100px;}#saveNewTask{margin-top:15px;}.warningsbox{display:none; margin-top: 15px;}.dontFBreak{width: 1%; white-space: nowrap;}td{vertical-align: middle !important;}.tooltip {position: relative;display: inline-block;border-bottom: 1px dotted black;visibility: visible !important;opacity: 1 !important;z-index: 998 !important;}.tooltip .tooltiptext {visibility: hidden;width: 400px;background-color: black;color: #fff;text-align: center;border-radius: 6px;padding: 10px;white-space: pre-wrap;position: absolute;top: -17px;left: 105%;}.tooltip:hover .tooltiptext {visibility: visible;background: #424242;}.dataTables_wrapper .dataTables_filter input{border:1px solid #424242; border-radius: 7px; padding:6px;} #datatb_wrapper{padding-top:5px;padding-bottom: 10px;}#datatb_info{padding-left:10px}
	</style>
	<link rel="stylesheet" href="../../v2/assets/datatables/datatables.min.css" />
	<link rel="stylesheet" href="../../v2/assets/datatables/buttons.datatables.min.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

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
      			<a href="<?php echo $RootPath; ?>/../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
      			<a class="bold" href="<?php echo $RootPath; ?>/../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
      		</span>
      	</span>
  	</header>

  	<div class="col-md-12" style="margin-top:15px">
		<select id="manufacturer" class="col-md-4">
			<option value="all">All Brands</option>
			<?php while($row = mysqli_fetch_assoc($manufacturers)){ ?>
				<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
			<?php } ?>
		</select>
		<input type="date" class="col-md-4" id="from-date">
		<input type="date" class="col-md-4" id="to-date">
		<input type="text" class="col-md-4" id="stockid" placeholder="StockID">
		<button class="col-md-12 btn btn-info" id="getTheResults">Shuu</button>
	</div>
	
	<div class="col-md-12" style="margin-top:15px; margin-bottom: 50px;">
		<table class="table table-responsive table-striped" id="datatable">
			<thead>
				<tr style="background:#424242; color:white;">
					<th>Customer Name</th>
					<th>DBA</th>
					<th>Value</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
			<tfoot>
				<tr>
					<th>---</th>
					<th>---</th>
					<th>---</th>
				</tr>
			</tfoot>
		</table>
	</div>

  	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index:150; padding: 5px">
		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	</footer>

	<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
	<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../quotation/assets/javascripts/theme.js"></script>
	<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>
	<script src="../../v2/assets/datatables/jquery.dataTables.min.js"></script>
	<script src="../../v2/assets/datatables/dataTables.buttons.min.js"></script>
	<script src="../../v2/assets/datatables/buttons.html5.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>

	<script>
		(function( $ ) {

			'use strict';

			var datatableInit = function() {

				datatable = $('#datatable').DataTable({
					"aoColumnDefs": [
						{ "sClass": "textLeft", "aTargets": [ 1 ] }
					],
					"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
					dom: 'Bfrtip',
					buttons: [
			            'excelHtml5',
			            'csvHtml5',
			        ],
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    },
				    drawCallback: function () {
				      	var api = this.api();
				     	$( api.table().footer() ).html(
				     	 	'<tr style="background-color:#424242; color:white">'+
				     	 	'<th>--</th><th>--</th>'+
				     	 	'<th>'+numberWithCommas(api.column(2,{search:'applied'}).data().sum())+'</th></tr>'
				       	);
				    }
				});

			};

			$(function() {
				datatableInit();
			});

		}).apply( this, [ jQuery ]);
		
		$("#getTheResults").on("click", function(){
			
			let manufacturer = $("#manufacturer").val();
			let from = $("#from-date").val();
			let to = $("#to-date").val();
			let stockid = $("#stockid").val();
			let FormID = "<?php echo $_SESSION['FormID']; ?>";
			
			let ref = $(this);
			ref.prop("disabled",true);
			
			$.post("brandItemSalesToCustomer.php?json",{
				manufacturer,
				from,
				to,
				stockid,
				FormID
			}, function(res, status, soma){
				
				res = JSON.parse(res);
				
				datatable.clear().draw();
				datatable.rows.add(res).draw();
				
				ref.prop("disabled",false);
				
			});
			
		});
		
		const numberWithCommas = (x) => {
			let parts = x.toString().split(".");
			parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			return parts.join(".");
		}
		
	</script>


</body>
</html>