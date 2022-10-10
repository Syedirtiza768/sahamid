 <?php

	$PathPrefix = "../";
	include("../includes/session.inc");
	
	$dateTime = date("Y-m-d H:i:s");
	$user     = $_SESSION['UsersRealName'];
	
	if(isset($_GET['json'])){
		
		if(isset($_POST['newRequest'])){
			
			$salescaseref = trim($_POST['salescaseref']);
			$stockid 	  = trim($_POST['stockid']);
			$quantity     = $_POST['quantity'];
			$comment      = $_POST['comment'];
			$comment 	  = htmlentities($comment);
			
			if($salescaseref != ""){
			
				$SQL = "SELECT * FROM salescase WHERE salescaseref='$salescaseref'";
				$res = mysqli_query($db, $SQL);
				
				if(mysqli_num_rows($res) != 1){
					
					echo json_encode([
						'status'  => 'error',
						'message' => 'Salescase Not Found.'
					]);
					return;
					
				}
			
			}
			
			$SQL = "SELECT * FROM stockmaster WHERE stockid='$stockid'";
			$res = mysqli_query($db, $SQL);
			
			if(mysqli_num_rows($res) != 1){
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Invalid StockID.'
				]);
				return;
				
			}
			
			$SQL = "SELECT * FROM reorderitems WHERE stockid='$stockid' AND requested_by='$user' AND approved_by IS NULL AND rejected_by IS NULL";
			$res = mysqli_query($db, $SQL);
			
			if(mysqli_num_rows($res) > 0){
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Another request for the same item is still pending.'
				]);
				return;
				
			}
			
			if($salescaseref != ""){
			
				$SQL = "INSERT INTO reorderitems (salescaseref, stockid, requested_by, requested_qty, requested_date,comment)
						VALUES('$salescaseref','$stockid','$user','$quantity','$dateTime','$comment')";
					
			}else{
				
				$SQL = "INSERT INTO reorderitems (stockid, requested_by, requested_qty, requested_date,comment)
						VALUES('$stockid','$user','$quantity','$dateTime','$comment')";
				
			}
			
			if(DB_query($SQL, $db)){
				
				echo json_encode([
					'status'  => 'success',
					'message' => 'Request Submitted Successfully.',
					'stockid' => $stockid,
					'salescaseref' => $salescaseref,
					'quantity' => $quantity,
					'id' => $_SESSION['LastInsertId'],
					'date' => date('d/m/Y'),
					'comment' => $comment
				]);
				return;
				
			}else{
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Request Failded For Some Reason.',
					'SQL'     => $SQL
				]);
				return;
				
			}
			
		}
		
		if(isset($_POST['deleteRequest'])){
			
			if(!isset($_POST['id'])){
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Missing Parms.'
				]);
				return;
			
			}
			
			$id = (int) $_POST['id'];
			
			$SQL = "DELETE FROM reorderitems WHERE id='$id' AND requested_by='$user' AND approved_by IS NULL";
			DB_query($SQL, $db);
			
			echo json_encode([
				'status'  => 'success',
				'message' => 'Request Removed Successfully.'
			]);
			return;
			
		}
		
		return;
		
	}
	
	$SQL = "SELECT reorderitems.*, stockmaster.mnfcode, stockmaster.mnfpno
			FROM reorderitems 
			INNER JOIN stockmaster ON reorderitems.stockid = stockmaster.stockid
			WHERE reorderitems.requested_by='$user'
			AND reorderitems.rejected_date IS NULL
			AND reorderitems.fulfilled_date IS NULL
			ORDER BY reorderitems.id DESC";
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
		<script>var datatable;</script>
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
			
			<?php if(!isset($_POST['salescaseref'])){ ?>
			.salescaseref{
				display: none !important;
			}
			<?php } ?>
			
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
	      		ReOrder Request
	      	</h3>

			<div class="request-container">
			
			<?php if(!isset($_GET['existing'])){ ?>
				<div class="form">
				
					<h3 class="request-header">
						New Request
					</h4>
					<div class="request-body">
						<label class="salescaseref">
							Salescaseref
							<input type="text" id="salescaseref" class="input" value="<?php echo $_POST['salescaseref']; ?>" disabled/>
						</label>
						<label>
							Stockid
							<input  type="text" 
									id="stockid" 
									class="input" 
									value="<?php echo $_POST['stockid']; ?>"
									<?php if(isset($_POST['stockid'])){ ?>
									disabled
									<?php } ?>
							/>
						</label>
						<label>
							Quantity
							<input type="number" id="quantity" class="input" value="0"/>
						</label>
						<label>
							Comment
							<textarea id="comment" class="input"></textarea>
						</label>
					</div>
					<button class="request-submit btn btn-primary">
						Submit
					</button>
				</div>

			<?php } ?>
				
				<div class="existing-requests">
					
					<h3 class="request-header">
						Existing Requests
						<!--<button class="btn btn-primary">ALL</button>
						<button class="btn btn-success">Approved</button>
						<button class="btn btn-warning">Rejected</button>-->
					</h3>
					
					<div class="request-body">
						
						<table class="table table-responsive table-striped">
							<thead>
								<tr>
									<th>Sr#</th>
									<th>StockID</th>
									<th>MNFCode</th>
									<th>MNFPNo</th>
									<th>Salescase</th>
									<th>QTY</th>
									<th>Transit QTY</th>
									<th>Date</th>
									<th>Status</th>
									<th>Comment</th>
									<th>Response</th>
									<th>Action</th>
								</tr>
							</thead>
							<tbody>
								<?php while($item = mysqli_fetch_assoc($items)){ ?>
								<tr data-approved="<?php echo ($item['approved_by']) ? "true":"false"; ?>">
									<td><?php echo $item['id']; ?></td>
									<td><?php echo $item['stockid']; ?></td> 
									<td><?php echo $item['mnfcode']; ?></td> 
									<td><?php echo $item['mnfpno']; ?></td> 
									<td><?php echo $item['salescaseref']; ?></td> 
									<td><?php echo $item['requested_qty']; ?></td>
									<td>
									<?php 
										$SQL = "SELECT SUM(quantity) as qty
												FROM procurement_document_details
												INNER JOIN procurement_document 
													ON procurement_document.id = procurement_document_details.pdid
												WHERE procurement_document.canceled_date IS NULL
												AND procurement_document.received_date IS NULL
												AND procurement_document.publish_date IS NOT NULL
												AND procurement_document_details.stockid='".$item['stockid']."'";
										$res = mysqli_query($db, $SQL);
										echo mysqli_fetch_assoc($res)['qty'];
									?>
									</td>
									<td><?php echo date("d/m/Y",strtotime($item['requested_date'])); ?></td> 
									<td>
										<?php if($item['approved_by']){ ?>
										 | <b><i class="fa fa-check"></i></b> | <?php echo ($item['approved_qty']); ?>								
										<?php } ?>
										<?php if($item['rejected_by']){ ?>
										| <b><i class="fa fa-times"></i></b> |
										<?php } ?>
									</td>
									<td><?php echo $item['comment']; ?></td>
									<td><?php echo $item['resComment']; ?></td>
									<td>
									<?php if(!$item["approved_by"] && !$item["rejected_by"]){ ?>
									<button class="btn btn-danger delete-request" data-id="<?php echo $item['id']; ?>">&times;</button>
									<?php } ?>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
						<?php while(1 == 2){ ?>
						<section class="request">
							<div class="details" data-approved="<?php echo ($item['approved_by']) ? "true":"false"; ?>" >
								<p>Stock ID: <?php echo $item['stockid']; ?></p> | 
								<?php if($item['salescaseref'] != null){ ?>
								<p>Salescaseref: <?php echo $item['salescaseref']; ?></p> | 
								<?php } ?>
								<p>Requested QTY: <?php echo $item['requested_qty']; ?></p> | 
								<p>Request Date: <?php echo date("d/m/Y",strtotime($item['requested_date'])); ?></p> | 
								<?php if($item['approved_by']){ ?>
								<p>Approved QTY: <?php echo ($item['approved_qty']); ?></p> |
								<p><b>Approved</b></p> | 								
								<?php } ?>
								<?php if($item['rejected_by']){ ?>
								<p><b>Rejected</b></p> | 
								<?php } ?>
							</div>
							<?php if(!$item["approved_by"] && !$item["rejected_by"]){ ?>
							<button class="btn btn-danger delete-request" data-id="<?php echo $item['id']; ?>">&times;</button>
							<?php } ?>
						</section>
						<?php } ?>
						
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
			var FormID = '<?php echo $_SESSION['FormID']?>';
			$(".request-submit").on("click", function(){
				
				let salescaseref = $("#salescaseref").val().trim();
				let stockid = $("#stockid").val().trim();
				let quantity = $("#quantity").val().trim();
				let comment = $("#comment").val().trim();
				let newRequest = true;
				
				if(stockid == ""){
					swal("Alert","StockID Cannot be empty.","warning");
					return;
				}			
				
				if(quantity <= 0){
					swal("Alert","Quantity has to be a value greater then 0.","warning");
					return;
				}

				if(comment == ""){
					swal("Alert","Comment cannot be empty.","warning");
					return;
				}	
				
				let ref = $(this);
				ref.prop("disabled",true);
				
				$.post("reorderRequest.php?json",{
					salescaseref,
					stockid,
					quantity,
					newRequest,
					comment,
					FormID
				}, function(res, status){
					
					res = JSON.parse(res);
					
					if(res.status == "success"){
						swal("Success",res.message,"success");
						addRequestToList(res);
						$("#stockid").val("");
						$("#stockid").prop("disabled",false);
						$("#quantity").val(0);
						$("#comment").val("");
					}else{
						swal("Error",res.message,"error");
					}
					
					ref.prop("disabled",false);
					
				});
				
			});
			
			$(document.body).on("click", ".delete-request", function(){
				
				if(confirm("Are you sure you want to cancel this request?")){
					
					let id = $(this).attr("data-id");
					let deleteRequest = true;
					let ref = $(this);
					
					ref.prop("disabled",true);
					
					$.post("reorderRequest.php?json",{
						FormID,id,deleteRequest
					}, function(res, status){
						
						res = JSON.parse(res);
						
						if(res.status == "success"){
							swal("Success",res.message,"success");
							//ref.closest("tr").remove();
							datatable.row(ref.closest("tr")).remove().draw();
						}else{
							swal("ERROR",res.message,"error");
						}
						
						ref.prop("disabled",false);
						
					});
					
				}
				
			});
			
			function addRequestToList(data){
				let html = `
					<tr data-approved="false">
						<td>${data.id}</td>
						<td>${data.stockid}</td> 
						<td>${data.mnfcode}</td> 
						<td>${data.mnfpno}</td> 
						<td>${data.salescaseref}</td> 
						<td>${data.quantity}</td>
						<td>${data.date}</td> 
						<td></td>
						<td>${data.comment}</td>
						<td>
							<button class="btn btn-danger delete-request" data-id="${data.id}">&times;</button>
						</td>
					</tr>
				`;
				
				$(".existing-requests .request-body tbody").prepend(html);
			}

			<?php if(isset($_GET['existing'])){ ?>
			$(document).ready(function(){
				datatable = $(".table").DataTable();
			});
			<?php } ?>
		
		</script>

	</body>
</html>