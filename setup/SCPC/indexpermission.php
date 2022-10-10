<?php

	$PathPrefix='../../';

	include('../../includes/SQL_CommonFunctions.inc');
	include('../../includes/session.inc');

	$SQL = "SELECT userid,realname FROM www_users";
	$res = mysqli_query($db,$SQL);
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>S A Hamid ERP</title>
		<meta name="keywords" content="HTML5 Admin Template" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="../../quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />

		<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>

	</head>

	<body>
		
		<section class="body background-content">

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

	      	<h3 style="font-family: inherit; text-align: center;">Salescase Permission Controller</h3>

	      	<div class="row">
	      		<div class="col-md-8 col-md-offset-2" >
	      			<table class="table table-striped" style="padding-bottom: 40px">
	      				<thead style="background: #424242; color: white">
	      					<th>Name</th>
	      					<th style="text-align: right;">Action</th>
	      				</thead>
	      				<tbody>
      						<?php while($row = mysqli_fetch_assoc($res)) { ?>
	      						<tr>
	      							<td><?php echo $row['realname']; ?></td>
	      							<td style="text-align: right;"><a href="<?php echo $RootPath; ?>/editSalescasePermission.php?userid=<?php echo $row['userid']; ?>">Edit</a></td>
	      						</tr>
	      					<?php } ?>
	      				</tbody>
	      			</table>
	      		</div>
	      	</div>

	      	<div style="min-height: 50px"></div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index:150">
				Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
			</footer>

	  	</section>	

	  	<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../quotation/assets/vendor/nprogress/nprogress.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>

		<script src="../../quotation/assets/javascripts/theme.js"></script>

	</body>

</html>