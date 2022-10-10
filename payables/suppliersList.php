<?php

	$PathPrefix='../';

	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');
	if(!userHasPermission($db, 'suppliers_List')) {

			
			header("Location: /sahamid/v2/reportLinks.php");
			exit;
		}

	$SQL = "SELECT supplierid,suppname FROM suppliers";
	$res = mysqli_query($db, $SQL);


?>

<!DOCTYPE html>
<html>
	<head>
		<title>ERP-SAHamid</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/nanoscroller/nanoscroller.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="../quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />

		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>

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
		</style>

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
	      				<a href="<?php echo $RootPath; ?>/../index.php" style="color: white; text-decoration: none;">Main Menu</a>
	      				<a class="bold" href="<?php echo $RootPath; ?>/../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	      			</span>
	      		</span>
	      		<input type="hidden" id="FormID" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
	      	</header>

			<h3 style="text-align: center; font-family: initial;">
				Suppliers List
			</h3>

	      	<div class="col-md-12" style="margin-bottom: 50px">
	      		<table class="table table-striped" style="width: 100%; margin-bottom: 50px; border: 1px solid #424242">
		      		<thead>
		      			<tr style="background: #424242; color: white">
		      				<th style="white-space: nowrap;">Supplier ID</th>
		      				<th>Supplier Name</th>
		      				<th></th>
		      			</tr>
		      		</thead>
		      		<tbody>
		      			<?php while ($row = mysqli_fetch_assoc($res)) { ?>
		      				<tr>
		      					<td style="width: 1%; white-space: nowrap;"><?php echo $row['supplierid']; ?></td>
		      					<td><?php echo $row['suppname']; ?></td>
		      					<td style="width: 1%">
		      						<a  href="../Payments.php?SupplierID=<?php echo $row['supplierid']; ?>" 
		      							class="btn btn-info"
		      							target="_blank"
		      							style="font-size:11px; white-space: nowrap;">Enter Payment</a>
		      					</td>
		      				</tr>
		      			<?php } ?>
		      		</tbody>
		      	</table>
	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; padding: 5px">
      			Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
      		</footer>

  		</section>

		<script src="../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
		<script src="../quotation/assets/javascripts/theme.js"></script>

	</body>

</html>	