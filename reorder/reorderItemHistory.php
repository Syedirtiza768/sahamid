 <?php

	$PathPrefix = "../";
	include("../includes/session.inc");
	
	$dateTime = date("Y-m-d H:i:s");
	$user     = $_SESSION['UsersRealName'];

	$SQL = "SELECT reorderitems.* , stockmaster.description,
					stockmaster.mnfcode, stockmaster.mnfpno
			FROM reorderitems 
			INNER JOIN stockmaster ON reorderitems.stockid = stockmaster.stockid
			WHERE approved_by IS NOT NULL
			AND rejected_by IS NULL";
			
	if(isset($_GET['stockid'])){
		
		$stockid = trim($_GET['stockid']);
		
		$SQL .= " AND reorderitems.stockid='$stockid'";
	
	}

	$SQL .= " ORDER BY reorderitems.id DESC";
	$items = mysqli_query($db, $SQL);
	
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
		<link rel="stylesheet" href="../shop/parchi/inward/assets/searchSelect.css" />

		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>

		<style>
			
			.request-container{
				display:flex;
				justify-content: center;
				margin: 15px;
				margin-top: 15px;
				margin-bottom: 50px;
			}
			
			.footer{
				background:#424242; 
				bottom:0; width:100%; 
				text-align:center; 
				padding: 5px;
				position: fixed;
			}
			
			.form{
				display: flex;
				flex-direction: column;
				width: 300px;
			}
			
			.request-header{
				text-align: center;
				background: #e0e0e0;
				border-radius: 10px 10px 0 0;
				padding: 5px;
				margin-bottom: 0;
			}
			
			.request-header button{
				padding: 1px 10px !important;
			}
			
			.request-body{
				background: white;
				padding: 25px 15px;
				display: flex;
				flex-direction: column;
			}
			
			.request-body label{
				margin: 10px 0;
			}
			
			.input{
				width: 100%;
				border: 1px solid #ccc;
				padding: 5px;
			}
			
			.request-submit{
				border-radius: 0 0 7px 7px;
			}
			
			.existing-requests{
				flex: 1;
				margin-left: 10px;
				height: calc(100vh - 150px);
				overflow: hidden;
				overflow-y: scroll;
			}
			
			.request{
				display: flex;
				background: #efefef;
				margin-bottom: 5px;
			}
			
			.request .details{
				flex: 1;
				padding: 5px;
			}
			
			.request .details p{
				margin-bottom: 0;
				display: inline-block;
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

	      	<h3 style="text-align: center; font-variant-caps: petite-caps;">
	      		<i class="fa fa-sign-in" aria-hidden="true"></i> 
	      		ReOrder Request History
	      	</h3>

			<div class="request-container">
				
				<div class="existing-requests">
					
					<h3 class="request-header">
						(<?php echo mysqli_num_rows($items); ?>) Approved Requests <?php echo isset($_GET['stockid']) ? "For ".$_GET['stockid']:""; ?>
					</h3>
					
					<div class="request-body">
					
						<table class="table table-responsive table-striped">
							<thead>
								<tr>
									<th>Sr#</th>
									<th>Stock ID</th>
									<th>MNFCode</th>
									<th>MNFPNo</th>
									<?php if(!isset($_GET['stockid'])){ ?>
									<th>Description</th>
									<?php } ?>
									<th>Requested By</th>
									<th>REQ-QTY</th>
									<th>REQ-Date</th>
									<th>Approved By</th>
									<th>APR-QTY</th>
									<th>APR-Date</th>
									<th>Comment</th>
									<th>Response</th>
								</tr>
							</thead>
							<tbody>
								<?php while($item = mysqli_fetch_assoc($items)){ ?>
								<tr>
									<td><?php echo $item['id']; ?></td>
									<td><?php echo $item['stockid']; ?></td>
									<td><?php echo $item['mnfcode']; ?></td>
									<td><?php echo $item['mnfpno']; ?></td>
									<?php if(!isset($_GET['stockid'])){ ?>
									<td><?php echo $item['description']; ?></td>
									<?php } ?>
									<td><?php echo $item['requested_by']; ?></td>
									<td><?php echo $item['requested_qty']; ?></td>
									<td><?php echo date('d/m/Y', strtotime($item['requested_date'])); ?></td>
									<td><?php echo $item['approved_by']; ?></td>
									<td><?php echo $item['approved_qty']; ?></td>
									<td><?php echo date('d/m/Y', strtotime($item['approved_date'])); ?></td>
									<td><?php echo $item['comment']; ?></td>
									<td><?php echo $item['resComment']; ?></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>						
					</div>
					
				</div>
			
			</div>

	      	<footer class="footer">
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
		<script src="../shop/parchi/inward/assets/searchSelect.js"></script>
		
		<script>
			$(".table").DataTable();
		</script>	
	
	</body>
</html>