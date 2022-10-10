<?php 
	
	$PathPrefix='../';
	include('misc.php');
	include('../includes/session.inc');
	
	$SQL = "SELECT loccode,locationname FROM locations";
	$loc = mysqli_query($db, $SQL);

	$SQL = "SELECT realname FROM www_users WHERE fullaccess=10";
	$dir = mysqli_query($db, $SQL);

	$SQL = "SELECT companyname FROM dba";
	$dba = mysqli_query($db, $SQL);

?>

<!DOCTYPE html>
<html class="">
	<head>
		<meta charset="UTF-8">
		<title>S A Hamid ERP</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />

		<style>
			.input{
				border: 1px solid #424242;
				border-radius: 7px;
				width: 100%;
				height: 40px;
				padding: 5px;
			}

			.filtercase{
				width: 100%; 
				margin:10px 0;
			}
		</style>

		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>

	</head>
	<body>
		<section class="body">
	      	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
	      		<span style="color:white">
	      			<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> &nbsp;|&nbsp;
	      			<span style="color:#ccc">
	      				<?php echo stripslashes($_SESSION['UsersRealName']); ?>
	          		</span>
	      			<span class="pull-right" style="background:#424242; padding: 0 10px;">
	      				<a  href="<?php echo $RootPath; ?>/../index.php" 
	      					style="color: white; text-decoration: none;">
	      					Main Menu
	      				</a>
	      				<a  class="bold" 
	      					href="<?php echo $RootPath; ?>/../Logout.php" 
	      					style="color: white; text-decoration: none; margin-left:20px;">
	      					Logout
	      				</a>
	      			</span>
	      		</span>
	      	</header>

	      	<section class="row">
	      		<h2 style="text-align: center; font-family: initial;">Select Salescase Filter</h2>
	      		<div class="col-md-12" style="margin: 0px 0; margin-top:0px">
	      			<div class="col-md-4">
	      				<h4 >Sales Person: </h4>
	      				<input type="text" class="input salesperson" placeholder="Partial or Full Sales Person Name">
						<h4 style="margin-top: 25px;">Location: </h4>
	      				<select name="" class="input location">
	      					<option value="">All</option>
							<?php foreach($loc as $location){
								echo '<option value="'.$location['loccode'].'">'.$location['locationname'].'</option>';
							} ?>
	      				</select>
	      				<h4 style="margin-top: 25px;">Director: </h4>
	      				<select name="" class="input director">
	      					<option value="">All</option>
	      					<?php foreach($dir as $director){
								echo '<option value="'.$director['realname'].'">'.$director['realname'].'</option>';
							} ?>
	      				</select>
						<h4 style="margin-top: 25px;">Priority: </h4>
	      				<select name="" class="input priority">
	      					<option value="">All</option>
	      					<option value="high">High</option>
	      					<option value="medium">Medium</option>
	      					<option value="low">Low</option>
	      				</select>
						<h4 style="margin-top: 25px;">Quotation: </h4>
	      				<select name="" class="input quotation">
	      					<option value="">All</option>
	      					<option value="yes">Yes</option>
	      					<option value="no">No</option>
	      				</select>
	      			</div>
	      			<div class="col-md-4">	
	      				<h4>Client Name: </h4>
	      				<input type="text" class="input client" placeholder="Partial or Full Client Name">
	      				<h4 style="margin-top: 25px;">Salescaseref: </h4>
	      				<input type="text" class="input salescaseref" placeholder="Partial or Full Salescaseref">
						<h4 style="margin-top: 25px;">DBA: </h4>
	      				<select name="" class="input dba">
	      					<option value="">All</option>
	      					<?php foreach($dba as $db){
								echo '<option value="'.$db['companyname'].'">'.$db['companyname'].'</option>';
							} ?>
	      				</select>
						<h4 style="margin-top: 25px;">Purchase Order: </h4>
	      				<select name="" class="input purchaseorder">
	      					<option value="">All</option>
	      					<option value="yes">Yes</option>
	      					<option value="no">No</option>
	      				</select>
						<h4 style="margin-top: 25px;">Delivery Chalan: </h4>
	      				<select name="" class="input deliverychalan">
	      					<option value="">All</option>
	      					<option value="yes">Yes</option>
	      					<option value="no">No</option>
	      				</select>
	      			</div>
					<div class="col-md-4">	
	      				<h4>From Date: </h4>
	      				<input type="date" class="input from_date"/>
						<h4 style="margin-top: 25px;">To Date: </h4>
	      				<input type="date" class="input to_date"/>
						<h4 style="margin-top: 25px;">For Month: </h4>
	      				<input type="month" class="input for_month"/>
						<h4 style="margin-top: 25px;">For Year: </h4>
	      				<select name="" class="input for_year">
							<option value="">All</option>
							<?php $year = (int) date('Y'); while($year > 2011){ ?>
	      					<option value="<?php echo $year; ?>" <?php echo (date('Y') == $year) ? "selected":""; ?>><?php echo $year--; ?></option>
							<?php } ?>
	      				</select>
	      				<a class="btn btn-primary filtercase" style="margin-top:15px">View Salescases</a>
						<a href="../selectsalescase.php" class="btn btn-info" style="width: 100%; margin-bottom:10px;">View All Salescases (Old)</a>
	      				<!--<a href="selectsalescase.php" class="btn btn-primary" style="width: 100%;">View All Salescases (New)</a>-->
      				</div>
	      		</div>
	      	</section>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; padding: 5px">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>
      	</section>
		<script src="../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="assets/selectSalescaseFilter.js?version=<?php echo generateRandom() ?>"></script>

	</body>
</html>