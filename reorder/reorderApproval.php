 <?php

	$PathPrefix = "../";
	include("../includes/session.inc");
	
	if(!userHasPermission($db, 'reorder_items_approval')){
		header("Location: /sahamid/index.php");
		exit;
	}
	
	$dateTime = date("Y-m-d H:i:s");
	$user     = $_SESSION['UsersRealName'];
	
	if(isset($_GET['json'])){
		
		if(isset($_POST['getUserRequests'])){
			
			$userName = $_POST['user'];
			
			$SQL = "SELECT reorderitems.*, stockmaster.description,
							stockmaster.mnfcode, stockmaster.mnfpno, manufacturers.manufacturers_name as brand
					FROM reorderitems 
					INNER JOIN stockmaster ON reorderitems.stockid = stockmaster.stockid
					INNER JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
					WHERE approved_by IS NULL
					AND rejected_by IS NULL
					ORDER BY reorderitems.id DESC";
			$items = mysqli_query($db, $SQL);
			
			$sr = 1;
			$data = [];
			
			while($item = mysqli_fetch_assoc($items)){

				if(!isset($data[$item['stockid']])){

					$SQL = "SELECT SUM(locstock.quantity) AS qoh FROM locstock WHERE stockid='".$item['stockid']."'";
					$qoh = mysqli_fetch_assoc(mysqli_query($db,$SQL))['qoh'];

					$data[$item['stockid']] = [
						'stockid' 		=> $item['stockid'],
						'description' 	=> $item['description'],
						'mnfpno'		=> $item['mnfpno'],
						'mnfcode'		=> $item['mnfcode'],
						'brand'			=> $item['brand'],
						'qoh'			=> $qoh,
						'users'			=> []
					];

				}

				$data[$item['stockid']]['users'][] = [
					'requested_by' 	=> $item['requested_by'],
					'requested_qty' => $item['requested_qty'],
					'approved_qty'  => $item['approved_qty'],
					'comment'		=> $item['comment'],
					'id'			=> $item['id'],
					'salescaseref'	=> $item['salescaseref'],
				];
			
			}

			foreach ($data as $item) {

				$keep = false;

				foreach ($item['users'] as $user) {
					if($user['requested_by'] == $userName)
						$keep = true;
				}

				if($keep){
					$res[] = $item;
				}

			}
			
			echo json_encode([
				'status' => 'success',
				'data' 	 => ($userName == "All") ? $data:$res
			]);
			return;
			
		}
		
		if(isset($_POST['approveRequest'])){
			
			if(!isset($_POST['id']) || !isset($_POST['quantity']) || !isset($_POST['comment'])){
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Missing Parms'
				]);
				return;
				
			}
			
			$id = (int) $_POST['id'];
			$quantity = $_POST['quantity'];
			$comment = $_POST['comment'];
			$comment = htmlentities($comment);
			
			if(!($quantity > 0)){
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Quantity Needs to be greater then 0.'
				]);
				return;
				
			}
			
			$SQL = "SELECT * FROM reorderitems WHERE id=$id AND approved_by IS NULL AND rejected_by IS NULL";
			$res = mysqli_query($db, $SQL);
			
			if(mysqli_num_rows($res) != 1){
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Request Not Found.'
				]);
				return;
				
			}
			
			$request = mysqli_fetch_assoc($res);
			
			// if($request['requested_qty'] < $quantity){
				
			// 	echo json_encode([
			// 		'status'  => 'error',
			// 		'message' => 'Approved quantity cannot be greater then requested quantity.',
			// 	]);
			// 	return;
				
			// }
			
			$SQL = "UPDATE reorderitems
					SET approved_qty=$quantity,
						approved_by='$user',
						approved_date='$dateTime',
						resComment='$comment'
					WHERE id=$id";
			
			if(DB_query($SQL, $db)){
			
				echo json_encode([
					'status' => 'success'
				]);
				return;
				
			}else{
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Something went wrong.',
					'$SQL'    => $SQL
				]);
				return;
				
			}
			
		}
		
		if(isset($_POST['rejectRequest'])){
			
			if(!isset($_POST['id']) || !isset($_POST['comment'])){
				echo json_encode([
					'status'  => 'error',
					'message' => 'Missing Parms'
				]);
				return;
			}
			
			$id = (int) $_POST['id'];
			
			$comment = $_POST['comment'];
			$comment = htmlentities($comment);
			
			$SQL = "SELECT * FROM reorderitems WHERE id=$id AND approved_by IS NULL AND rejected_by IS NULL";
			$res = mysqli_query($db, $SQL);
			
			if(mysqli_num_rows($res) != 1){
				echo json_encode([
					'status'  => 'error',
					'message' => 'Request Not Found.'
				]);
				return;
			}

			$SQL = "UPDATE reorderitems
					SET rejected_by='$user',
						rejected_date='$dateTime',
						resComment='$comment'
					WHERE id=$id";
			
			if(DB_query($SQL, $db)){
			
				echo json_encode([
					'status' => 'success'
				]);
				return;
				
			}else{
				
				echo json_encode([
					'status'  => 'error',
					'message' => 'Something went wrong.',
					'$SQL'    => $SQL
				]);
				return;
				
			}
			
		} 
		
		return;
		
	} 
	
	$SQL = "SELECT requested_by, count(*) as count 
			FROM reorderitems
			WHERE approved_by IS NULL 
			AND rejected_by IS NULL
			GROUP BY requested_by";
	$users = mysqli_query($db, $SQL);
	
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
				width: 200px;
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
		
			.user{
				background: #efefef;
				padding: 7px;
				margin-bottom: 5px;
				border: 1px solid #ccc;
				border-radius: 7px;
				display: flex;
				justify-content: space-between;
				cursor: pointer;
			}
			
			.user:hover{
				
				background: green;
				color: white;
				
			}
			
			.selected{
				background: #424242;
				color: white;
			}
			
			.qtyInput{
				width: 80px;
				border-radius: 7px;
				padding: 4px;
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
	      		ReOrder Requests By User
	      	</h3>

			<div class="request-container">
			
				<div class="form">
				
					<h3 class="request-header">
						Users
					</h4>
					<div class="request-body">
						<div class="user" data-username="All">
								<span>All</span>
								<span></span>
						</div>
						<?php while($user = mysqli_fetch_assoc($users)){ ?>
						<div class="user" data-username="<?php echo $user['requested_by']; ?>">
								<span><?php echo $user['requested_by']; ?></span>
								<span><?php echo $user['count']; ?></span>
						</div>
						<?php } ?>
					</div>
					
				</div>
				
				<div class="existing-requests">
					
					<h3 class="request-header">
						User Requests
					</h3>
					
					<div class="request-body">
						
						<table class="table table-responsive table-striped">
							<thead>
								<tr>
									<th>
										<span style="border-bottom: 2px solid #424242">Stock ID</span>
									</th>
									<th>
										<span style="border-bottom: 2px solid #424242">Brand</span>
									</th>
									<th colspan="3">
										<span style="border-bottom: 2px solid #424242">Description</span>
									</th>
									<th>
										<span style="border-bottom: 2px solid #424242">MNFCode</span>
									</th>
									<th>
										<span style="border-bottom: 2px solid #424242">MNFPNo</span>
									</th>
									<th>
										<span style="border-bottom: 2px solid #424242">QOH</span>
									</th>
								</tr>
							</thead>
							<tbody class="populateRequestsHere">
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
			var FormID = '<?php echo $_SESSION['FormID']?>';
		
			$(".user").on("click", function(){
				
				let ref = $(this);
				
				if(ref.hasClass("selected")){
					return;
				}
				
				$(".user").each(function(){
					$(this).removeClass("selected");
				});
				
				ref.addClass("selected");
				
				let getUserRequests = true;
				let user = ref.attr("data-username");
				
				$(".populateRequestsHere").html("");
				
				$.post("reorderApproval.php?json",{FormID,user,getUserRequests},function(res,status){
				
					res = JSON.parse(res);
					
					if(res.status == "success"){
						populateRequests(res.data);
					}else{
						swal("Error",res.message,"error");
					}
				
				});
				
			});
			
			$(document.body).on("click", ".approveRequest", function(){
				
				let ref = $(this);
				let id  = ref.attr("data-id");
				let approveRequest = true;
				let parent = ref.closest("tr");
				let quantity = parent.find(".qtyInput").val();
				let comment = parent.find(".resComment").val();

				if(comment.trim() == ""){
					swal("Alert","Comment Is Required.","warning");
					return;
				}

				if(!confirm("Are you sure you want to approve this request?")){
					return;
				}
				
				if(quantity > 0){
		
					$.post("reorderApproval.php?json",{FormID,id,approveRequest,quantity,comment},function(res,status){
					
						res = JSON.parse(res);
						
						if(res.status == "success"){
							parent.remove();
						}else{
							swal("Error",res.message,"error");
						}
					
					});
				
				}else{
					
					swal("Alert","Approved quantity needs to be greater then 0 in order to approve the reorder request.","warning");
					return;
					
				}
				
			});
			
			$(document.body).on("click", ".rejectRequest", function(){
				
				let ref = $(this);
				let id  = ref.attr("data-id");
				let rejectRequest = true;
				let parent = ref.closest("tr");
				let comment = parent.find(".resComment").val();

				if(comment.trim() == ""){
					swal("Alert","Comment Is Required.","warning");
					return;
				}
				
				if(!confirm("Are you sure you want to reject this request?")){
					return;
				}
				
				$.post("reorderApproval.php?json",{FormID,id,rejectRequest,comment},function(res,status){
				
					res = JSON.parse(res);
					
					if(res.status == "success"){
						parent.remove();
					}else{
						swal("Error",res.message,"error");
					}
				
				});
				
			});
			
			function populateRequests(requests){
				
				$.each(requests, function(key, data){
					
					let html = `
							<tr>
								<td colspan=7></td>
							</tr>
							<tr>
								<td colspan=7></td>
							</tr>
							<tr>
								<td>${data.stockid}</td>
								<td>${data.brand}</td>
								<td colspan="3">${data.description}</td>
								<td>${data.mnfcode}</td>
								<td>${data.mnfpno}</td>
								<td>${data.qoh}</td>
							</tr>
						`;

					let userHTML = "";
					$.each(data.users, function(uKey, uData){

						userHTML += `
							<tr>
								<td>${uData.requested_by}</td>
								<td>${(uData.salescaseref != null) ? uData.salescaseref:""}</td>
								<td>${uData.comment}</td>
								<td>${uData.requested_qty}</td>
								<td>
									<input type="number" class="input qtyInput" data-id="${uData.id}" value="${uData.approved_qty}"/>
								</td>
								<td>
									<textarea class="input resComment" style="height:27px; width:150px"></textarea>
								</td>
								<td>
									<div style="display:flex;">
										<button class="btn btn-success approveRequest btn-xs" data-id="${uData.id}">
											<i class="fa fa-check"></i>
										</button>
										<button class="btn btn-danger btn-xs rejectRequest" data-id="${uData.id}">
											<i class="fa fa-times"></i>
										</button>
									</div>
								</td>

							</tr>
						`;

					});

					html += `
						<tr>
							<td colspan="7" style="border-left:5px solid #424242 ">
								<table class="table table-responsive table-striped">
									<thead>
										<tr style="background:#424242; color: #ccc">
											<th style="width:150px; min-width:150px; max-width:150px;">User</th>
											<th style="width:100px; min-width:100px; max-width:100px;">Salescaseref</th>
											<th style="width:200px; min-width:200px; max-width:200px;">Comment</th>
											<th style="white-space: nowrap;">REQ-QTY</th>
											<th>APR-QTY</th>
											<th>Res-comment</th>
											<th>Action</th>
										</tr>
									</thead>
									
									<tbody>
										${userHTML}
									</tbody>
								</table>

							</td>
						</tr>

									<tr>
											<td colspan=7></td>
									</tr>
						
					`;
					
					$(".populateRequestsHere").append(html);
					
				});
				
			}
		</script>

	</body>
</html> 