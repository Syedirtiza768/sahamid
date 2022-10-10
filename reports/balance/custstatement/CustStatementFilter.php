<?php 
	
	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$client = $_GET['client'];

	if(isset($client) && trim($client) != ""){
	
		$SQL = "SELECT * FROM debtorsmaster 
            INNER JOIN custbranch ON debtorsmaster.debtorno = custbranch.debtorno";
        if(!userHasPermission($db, "executive_listing")) {
            $SQL.= ' INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman ';

        }
        if(!userHasPermission($db, "executive_listing")) {
            $SQL.= ' INNER JOIN www_users ON salesman.salesmanname = www_users.realname ';

        }

		$SQL.=" WHERE (custbranch.debtorno LIKE '%$client%'
			OR name LIKE '%$client%')";
        if(!userHasPermission($db, "executive_listing")) {
            $SQL.= ' AND ( salesman.salesmanname ="'.$_SESSION['UsersRealName'].'"
				OR  www_users.userid
		                IN (SELECT salescase_permissions.can_access FROM salescase_permissions
		                WHERE salescase_permissions.user = "'.$_SESSION['UserID'].'"'.')) ';

        }

		$res = mysqli_query($db, $SQL);
	
	}else{

		$res = [];
	
	}


	

?>
<!DOCTYPE html>
<html>
<head>

	<title>Customer Balance</title>

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
		    vertical-align: inherit !important;
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
		.dataTables_length select{
			height: auto !important;
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

  	<div class="col-md-12" style="margin-top: 20px">
  		
  		<div class="col-md-4 col-md-offset-4">
  			
	  		<form>
	  			Client Name: 
	  			<input type="text" name="client" style="width: 100%; border:1px #424242 solid; border-radius: 4px; padding:10px" placeholder="Client Name or Debtor No Here...">
	  			<input type="submit" value="Search"  style="width: 100%; border:1px #424242 solid; border-radius: 4px; padding:10px; margin: 10px 0;" class="btn-success">
	  		</form>

  		</div>

  	</div>

  	<div class="col-md-12" style="margin-bottom: 20px">
  		<table class="table table-bordered table-striped mb-none" id="datatable">
  			<thead>
  				<tr style="background-color: #424242; color: white">
  					<th>Debtor #</th>
					<th>DBA</th>
  					<th style="text-align: left;">Customer Name</th>
  					<th>From Date</th>
  					<th>Action</th>
  				</tr>
  			</thead>
  			<tbody>
  				<?php if(!is_array($res)) while ($customer = mysqli_fetch_assoc($res)) { ?>
  					<tr>
  						<td class="btn-primary"><?php echo $customer['debtorno']; ?></td>
  						<td><?php echo $customer['dba']; ?></td>
						<td><?php echo $customer['name']; ?></td>
						<td>
							<form action="<?php echo $RootPath; ?>/../../../customerstatement.php" method="post">
							<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
							<input type="hidden" name="cust" value="<?php echo $customer['debtorno']; ?>">
							<input type="date" name="fromdate" style="padding: 4px; margin: 0; border: 1px #424242 solid; border-radius: 6px; line-height: initial !important;">
						</td>
						<td class="btn-info">
							<input type="submit" value="View" class="btn-info" style="width: 100%; padding: 4px; border:1px #424242 solid;">
							</form>
						</td>
  					</tr>
  				<?php } ?>
  			</tbody>
  		</table>
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

	<script>
		(function( $ ) {

			'use strict';

			var datatableInit = function() {

				datatable = $('#datatable').DataTable({
					"aoColumnDefs": [
						{ "sClass": "textLeft", "aTargets": [ 2 ] }
					],
					"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
					"responsive":true,
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    }
				});

				$('#datatable_length').find("label").html("<span style='margin:0; padding:0; padding-right:20px; font-variant: petite-caps; font-size:2.4rem'><i class='fa fa-users' aria-hidden='true'></i> Customer Statement Filter</span> ");
			};

			$(function() {
				datatableInit();
			});

		}).apply( this, [ jQuery ]);
		
	</script>


</body>
</html>