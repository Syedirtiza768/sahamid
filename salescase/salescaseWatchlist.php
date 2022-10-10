<?php

	$PathPrefix='../';

  	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');

	$SQL = "SELECT salescase_watchlist.salescaseref,
				salescase_watchlist.review_on,
				salescase_watchlist.notes,
				salescase_watchlist.priority,
				debtorsmaster.name as client_name,
				debtorsmaster.dba,
				salescase.priority as salescase_priority,
				salescase.closed as salescase_closed,
				salescase.closingreason as salescase_closingreason,
				salescase.closingremarks as salescase_closingremarks,
				salesman.salesmanname,
				salescase.salescasedescription,
				salescase.commencementdate as salescase_created_at,
				salescase_watchlist.created_at
			FROM salescase_watchlist 
			INNER JOIN salescase ON salescase_watchlist.salescaseref = salescase.salescaseref
			INNER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno
				AND custbranch.branchcode = salescase.branchcode)
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE salescase_watchlist.userid='".$_SESSION['UserID']."'
			AND salescase_watchlist.deleted=0";

	if(isset($_GET['date']) && $_GET['date'] != ""){
		$SQL .= " AND salescase_watchlist.review_on<='".$_GET['date']."'";
	}
	
	$SQL .= " ORDER BY salescase_watchlist.priority";
			
	$res = mysqli_query($db, $SQL);

	$watchList = [];

	while($row = mysqli_fetch_assoc($res)){

		$watchList[] = $row;

	}

	$watchList = array_map(function($item){

					$item['created_at'] = date('d/m/Y',strtotime($item['created_at']));
					$item['salescase_created_at'] = date('d/m/Y',strtotime($item['salescase_created_at']));

					if($item['review_on'] == "0000-00-00" || $item['review_on'] == ""){
						$item['review_on'] = "Not Scheduled";
					}else{
							$item['review_on'] = date('d/m/Y',strtotime($item['review_on']));
					}

					$item['status'] = (($item['salescase_closed'] == 0) ? 'Open':'Closed');
					
					if($item['salescase_priority'] == "high"){
						$item['color'] = 'btn-danger';
					}else if($item['salescase_priority'] == "low"){
						$item['color'] = 'btn-info';
					}

					return $item;

				}, $watchList);

?>

<!DOCTYPE html>
<html class="">
	<head>
		<meta charset="UTF-8">

		<title>S A Hamid ERP</title>
		<meta name="keywords" content="HTML5 Admin Template" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />

		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>

		<style>

			body{
				background-image: url('assets/images/cardboard.png');
	      		background-size: contain;
			}

			.listContainer{
				border: 2px solid #424242;
				border-radius: 8px;
				margin-top: 10px;
				margin-bottom: 40px;
				margin-left: 0px;
				margin-right: 0px;
				padding: 10px;
			}

			.listItem{
				background: white;
				border-radius: 10px;
				margin-top: 10px;
			}
			
			.priorityline{
				height: 6px;
				width: 100%;
				margin-right: -30px;
			}

			.textarea{
				min-width: calc(100% - 30px);
				min-height: 70px;
				max-height: 70px;
				margin-left: 15px;
				border: 1px #424242 solid;
				border-radius: 7px;
			}

			.filters{
				display: flex;
				width: 100%;
				justify-content: flex-end;
			}

			.filters div{
				margin-left: 5px;
			}
			
			.setreviewdate{
				height:25px; 
				border:1px solid #424242; 
				border-radius:7px;
			}
			
			.left-green{
				border-left:6px solid green;
			}
			
			.left-red{
				border-left:6px solid red;
			}
			
			.left-orange{
				border-left:6px solid orange;
			}
			
			.tooltip {
				position: relative;
				display: inline-block;
				border-bottom: 1px dotted black;
				visibility: visible !important;
				opacity: 1 !important;
			}

			.tooltip .tooltiptext {
				visibility: hidden;
				width: 400px;
				background-color: black;
				color: #fff;
				text-align: center;
				border-radius: 6px;
				padding: 10px;
				white-space: pre-wrap;
				
				/* Position the tooltip */
				position: absolute;
				z-index: 1;
				top: -5px;
				left: 105%;
			}
			
			.tooltip:hover .tooltiptext {
				visibility: visible;
				background: #424242;
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
	      	</header>
			
			<h2 style="text-align: center; font-family: cursive;">Salescase Watchlist</h2>

	      	<div class="col-md-12">
	      		
				<div class="filters" style="text-align: right;">
					<div>
						<a href="salescaseWatchlist.php" class="btn btn-success" style="display: inline-block; margin-bottom: 5px;">View All</a>
					</div>
					<div>
						<form action="salescaseWatchlist.php">
							<input type="hidden" value="<?php echo date('Y-m-d'); ?>" name="date">
							<input type="submit" class="btn btn-success" value="Salescases To Be Reviewed Today">
						</form>
					</div>
				</div>

				<div class="row listContainer">
					<?php foreach($watchList as $item){ ?>
					<div class="col-md-6">
						<div class="listItem 
									<?php echo ($item['priority'] == 1) ? 'left-red':'' ?>
									<?php echo ($item['priority'] == 2) ? 'left-orange':'' ?>
									<?php echo ($item['priority'] == 3) ? 'left-green':'' ?>
									">
							<div class="priorityline <?php echo $item['color']; ?>"></div>
							<div class="col-md-7">
								<h5>Salescaseref</h5>
								<h5 class="tooltip">-- <?php echo $item['salescaseref']; ?>
									<span class='tooltiptext'><?php echo $item['salescasedescription']; ?></span>
								</h5>
								<h5>Client (<?php echo $item['dba']; ?>)</h5>
								<h5>-- <?php echo $item['client_name']; ?></h5>
								<h5>Salesman</h5>
								<h5>-- <?php echo $item['salesmanname']; ?></h5>
								<h5>Status</h5>
								<h5>-- <?php echo $item['status']; ?></h5>
								<?php if($item['salescase_closed'] == 1){ ?>
								<h5>Closing Reason:</h5>
								<h5>-- <?php echo $item['review_on']; ?></h5>
								<?php } ?>
							</div>
							<div class="col-md-5">
								<h5>Priority:</h5>
								<h5>
									<select style="width: 140px; height: 20px; padding: 0px" 
											data-salescaseref="<?php echo $item['salescaseref']; ?>" 
											class="watchListPriority">
										<option value="1" <?php echo ($item['priority'] == 1) ? 'selected':'' ?>>1</option>
										<option value="2" <?php echo ($item['priority'] == 2) ? 'selected':'' ?>>2</option>
										<option value="3" <?php echo ($item['priority'] == 3) ? 'selected':'' ?>>3</option>
									</select>
								</h5>
								<!--<h5>Added On Priority:</h5>
								<h5>-- <?php echo $item['created_at']; ?></h5>-->
								<h5>Salescase Commencement:</h5>
								<h5>-- <?php echo $item['salescase_created_at']; ?></h5>
								<h5>Next Review Date:</h5>
								<h5>-- <?php echo $item['review_on']; ?></h5>
								<h5>Set Review Date:</h5>
								<h5>-- 
									<input  type="date"
											data-salescaseref="<?php echo $item['salescaseref']; ?>"									
											class="setreviewdate">
								</h5>
								<?php if($item['salescase_closed'] == 1){ ?>
								<h5>Closing Date:</h5>
								<h5>-- <?php echo $item['review_on']; ?></h5>
								<?php } ?>
							</div>
							<!-- <textarea class="textarea" placeholder="Notes"><?php echo $item['notes']; ?></textarea> -->
							<a href="api/removeSalescaseFromWatchlist.php?salescaseref=<?php echo $item['salescaseref']; ?>" class="btn btn-danger col-sm-6" onclick="return confirm('Are you sure?')">Remove From Watchlist</a>
							<a href="salescaseview.php?salescaseref=<?php echo $item['salescaseref']; ?>" class="btn btn-success col-sm-6" target="_blank">View Salescase</a>
							<div style="clear: both;"></div>
						</div>
					</div>
					<?php } ?>
				</div>

	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; padding: 5px">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>

      	</section>

		<script src="../quotation/assets/vendor/jquery/jquery.js"></script>

      	<script>
      		$(".watchListPriority").on("change",function(){
      			let ref = $(this);
      			let salescaseref = ref.attr("data-salescaseref");
      			let val = $(this).val();
      			$.post('api/updateWatchListPriority.php',
      					{
      						FormID:'<?php echo $_SESSION['FormID']; ?>',
      						salescaseref:salescaseref,
      						priority:val
      					}, function(dasd,dasd){
      						ref.css("border","2px solid green");
      					});
      		});
			
			$(".setreviewdate").on("change",function(){
      			let ref = $(this);
      			let salescaseref = ref.attr("data-salescaseref");
      			let val = $(this).val();
      			$.post('api/updateSalescaseReviewDate.php',
      					{
      						FormID:'<?php echo $_SESSION['FormID']; ?>',
      						salescaseref:salescaseref,
      						review:val
      					}, function(dasd,dasd){
      						ref.css("border","2px solid green");
      					});
      		});
			
      	</script>
    </body>
</html>

