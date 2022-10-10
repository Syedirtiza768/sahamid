<?php

	$PathPrefix = "../";
	
	include('../includes/session.inc');
	
	if(!userHasPermission($db, 'quick_quotation')){
		header("Location: /sahamid/index.php");
		exit;
	}

	if($_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 8 || $_SESSION['AccessLevel'] == 10){	
	
		$SQL = "SELECT  salesorders.salescaseref,
						salesorders.orderno,
						salesorders.branchcode,
						custbranch.brname,
						salescase.salesman,
						debtorsmaster.dba
				FROM salesorders
				INNER JOIN salescase ON salesorders.salescaseref = salescase.salescaseref
				INNER JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
				WHERE salesorders.quickQuotation=1
				AND salesorders.withoutItems=1";
				
	} else {
		
		$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
		$res = mysqli_query($db, $SQL);

		$canAccess = [];

		while($row = mysqli_fetch_assoc($res))
			$canAccess[] = $row['can_access'];
		
		$SQL = "SELECT  salesorders.salescaseref,
						salesorders.orderno,
						salesorders.branchcode,
						custbranch.brname,
						salescase.salesman,
						debtorsmaster.dba
				FROM salesorders
				INNER JOIN salescase ON salesorders.salescaseref = salescase.salescaseref
				INNER JOIN debtorsmaster ON salesorders.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch ON salesorders.branchcode = custbranch.branchcode
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				WHERE salesorders.quickQuotation=1
				AND salesorders.withoutItems=1
				AND ( salescase.salesman ='".$_SESSION['UsersRealName']."'
					OR www_users.userid IN ('".implode('\',\'', $canAccess)."') )";
		
	}
	
	$res = mysqli_query($db, $SQL);
	
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
			.dataTables_filter label{width: 100% !important;}
			.dataTables_filter input{border: 1px #ccc solid;border-radius: 5px;}
			.selected{background-color: #acbad4 !important;}
			th{text-align: center;}
			td{text-align: center;}
			.abc td{padding:5px 15px !important;}
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

	      	<div class="col-md-12" style="text-align: center; border-bottom: 2px #424242 solid; margin-top:40px;">
	      		
				<table class="table table-bordered table-striped table-responsive mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
	      					<th>Sr#</th>
	      					<th>SalescaseRef</th>
	      					<th>Client Name</th>
	      					<th>Branch Code</th>
							<th>DBA</th>
							<th>Salesman</th>
							<th>Quotation#</th>
							<th>Action</th>
	      				</tr>
	      			</thead>
	      			<tbody>
	      				<?php $count = 1; while($row = mysqli_fetch_assoc($res)){ ?>
							<tr>
								<td><?php echo $count; ?></td>
								<td><?php echo $row['salescaseref']; ?></td>
								<td><?php echo $row['brname']; ?></td>
								<td><?php echo $row['branchcode']; ?></td>
								<td><?php echo $row['dba']; ?></td>
								<td><?php echo $row['salesman']; ?></td>
								<td><?php echo $row['orderno']; ?></td>
								<td><a class="btn btn-success" target="_blank" href="/sahamid/salescase/salescaseview.php?salescaseref=<?php echo $row['salescaseref']; ?>">Salescase</a></td>
							</tr>
						<?php $count++; } ?>
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

				$('#datatable_length').find("label").html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Quick Quotation Without Items or quantity</h3>")

			};

			$(function() {
				datatableInit();
			});

		}).apply( this, [ jQuery ]);
		
		

	</script>
</html>
