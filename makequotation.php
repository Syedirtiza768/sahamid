<?php
 
	include('includes/session.inc');
	include('quotation/misc.php');

	if(!checkParms()){
		header('Location: '.$RootPath."/salescase.php");
		exit;
	}  

	if(isIncorrectSalesCase()){
		header('Location: '.$RootPath."/salescase.php");
		exit;
	}

	$salescaseref = $_GET['salescaseref'];
	$debtorNo 	  = $_GET['DebtorNo'];
	$branchCode   = $_GET['BranchCode'];
	$selectedcustomer = $_GET['selectedcustomer'];

	if(isset($_GET['orderno']))
		$orderno = $_GET['orderno'];

	include('includes/FreightCalculation.inc');
	include('includes/SQL_CommonFunctions.inc');
	include('includes/CountriesArray.php');
	
	$conn = createDBConnection();

	$SQL = "SELECT custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
				custbranch.specialinstructions,
				custbranch.estdeliverydays,
				custbranch.salesman
			FROM custbranch
			WHERE custbranch.branchcode='".$_GET['BranchCode']."'
			AND custbranch.debtorno = '".$_GET['DebtorNo']."'";

	$customerInfoResult = mysqli_query($conn, $SQL);

	if(mysqli_num_rows($customerInfoResult) == 0){

		header('Location: '.$RootPath."/salescase.php");
		exit;

	}


	$tempSQL = 'SELECT count(*) as count FROM salesordersip WHERE salescaseref="'.$_GET['salescaseref'].'"';

	$resp = mysqli_query($conn,$tempSQL);

	$co = mysqli_fetch_assoc($resp);

	if(isset($_GET['discard'])){
		
		if($_GET['discard'] == 'true'){
			
			$SQL = "DELETE FROM `salesordersip` WHERE salescaseref='".$_GET['salescaseref']."'";
			
			mysqli_query($conn,$SQL);

			$SQL = "DELETE FROM `salesorderoptionsip` WHERE orderno='".$orderno."'";
			
			mysqli_query($conn,$SQL);
			
			$SQL = "DELETE FROM `salesorderitemsip` WHERE orderno='".$orderno."'";
			
			mysqli_query($conn,$SQL);

			header('Location: '.$_SERVER['HTTP_REFERER']);

			exit;
			
		}
		
	}
	
	$quickQuotation = 0;
	
	if(isset($_GET['quickQuotation'])){
		$quickQuotation = 1;
	}
	
	if($co['count'] == 0 && isNewQuotation()){

		$customerInfo = mysqli_fetch_assoc($customerInfoResult);
		
        $SQL = "INSERT INTO `salesordersip`(`salescaseref`, `debtorno`, `branchcode`, `buyername`,`fromstkloc`,`deladd1`,`deladd2`,`deladd3`,`deladd4`,`deladd5`,`deladd6`,`deliverto`,`contactphone`,`contactemail`,`shipvia`,`deliverblind`,`afterdays`,`salesperson`,`quotedate`,`confirmeddate`,`deliverydate`,`quickQuotation`,`rate_clause`,`rate_validity`) 
				VALUES ('".$salescaseref."','".$debtorNo."', '".$branchCode."','".$selectedcustomer."','MT','".$customerInfo['braddress1']."','".$customerInfo['braddress2']."','".$customerInfo['braddress3']."','".$customerInfo['braddress4']."','".$customerInfo['braddress5']."','".$customerInfo['braddress6']."','".$customerInfo['brname']."','".$customerInfo['phoneno']."','".$customerInfo['email']."','".$customerInfo['defaultshipvia']."','".$customerInfo['deliverblind']."','".$customerInfo['estdeliverydays']."','".$customerInfo['salesman']."','".date('Y-m-d')."','".date('Y-m-d')."','".date('Y-m-d')."','".$quickQuotation."','usd','".date('Y-m-d',strtotime(date('Y-m-d')." +15 days"))."')";

		mysqli_query($conn, $SQL);

		$orderno = mysqli_insert_id($conn);
		$SQL = "SELECT max(id) as id FROM exchange_rate";
	$res = mysqli_query($db, $SQL);
	$id = mysqli_fetch_assoc($res)['id'];

	$SQL = "SELECT * FROM exchange_rate WHERE id=$id";
	$res = mysqli_query($db, $SQL);
	$rates = mysqli_fetch_assoc($res);

	$rates = json_encode($rates);

	$SQL = "UPDATE salesordersip 
			SET clause_rates = '$rates'
			WHERE orderno = $orderno";
	mysqli_query($db, $SQL);
		
	}else if($co['count'] == 1){

		$tempSQL = 'SELECT orderno FROM salesordersip WHERE salescaseref="'.$_GET['salescaseref'].'"';

		$result = mysqli_query($conn, $tempSQL);

		$row = mysqli_fetch_assoc($result);

		$orderno = $row['orderno'];

	}

	$SQL = "SELECT categoryid, categorydescription FROM stockcategory";

	$result = mysqli_query($conn, $SQL);
	
	$stockCategories = [];

	while($row = mysqli_fetch_assoc($result)){
		$stockCategories[$row['categoryid']] = $row['categorydescription'];
	}

	$SQL = "SELECT locationname,loccode FROM locations";

	$result = mysqli_query($conn, $SQL);

	$stkLocations = [];

	while($row = mysqli_fetch_assoc($result)){
		$stkLocations[$row['loccode']] = $row['locationname'];		
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>S A Hamid ERP</title>
		<meta name="keywords" content="HTML5 Admin Template" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<meta charset="utf-8"/>


		<link rel="stylesheet" href="quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="quotation/assets/stylesheets/skins/default.css" />

		<script src="quotation/assets/vendor/modernizr/modernizr.js"></script>
		
		<script>
		  	var lineno = 0;
			var vlineno = 0;
			var options = [];
			var voptions = [];
			var items = [];
		</script>

		<style>
			<?php 
			
				if($_SESSION['UsersRealName'] == "Ali Imran"){
					
					echo ".bongastyle{";
					echo "background:#dcad38;";
					echo "}";
					
				}
			
			?>
		
			.order-detailss{
				border: 1px black solid;
				border-radius: 4px;
				background: #f9f9f9;
				color: black !important;
			}
			
			#order-details .col-md-3{
				color:black !important;
			}
			
			.toggleviewnone{
				display: none;
			}

			th,td{
				text-align: center;
			}

			#searchresults_length label select {
				color: black;
			}

			#searchresults_length,#searchresults_info{
				color: black;
			}

			#searchresults_filter label,.datatables-footer,.datatables-header{
				width: 100%;
			}

			#searchresults thead th{
				border: 1px white solid;
				border-bottom: 0px;
			}

			#searchresults tfoot th{
				border: 1px white solid;
				border-top: 0px;
			}

			#searchresults td{
				border: 1px #424242 solid;
			}

			#scrollUp{
				position: fixed;
				bottom: 50px;
				right: 0;
				padding:10px;
				color: white;
				background: #424242;
			}

			html, body {
			    height: 100%;
			}
			                
			.overlay{
			    position: fixed;
			    top: 0px;
			    left: 0px;
			    right: 0px;
			    bottom: 0px;
			    color: white;
			    background-color: #424242;
			    overflow: auto;
			   	z-index: 200;
			   	display: none;

			    .overlay-content {
			        height: 100%;
			        overflow: scroll;
			    }
			}
			  
			.background-content{
			    height: 100%;
			    overflow: auto;
			}

			.red{
				border: red 1px dotted;
			}

			.inp{
			    border: 1px solid #E5E7E9;
				border-radius: 6px;
				height: 46px;
				padding: 12px;
				outline: none;
			}

			.rendering{
				-webkit-animation: myfirst 5s linear 2s infinite alternate; 
    			animation: opac 1s linear 0s infinite alternate;
			}

			#itemsadded th{
				background: #aaa;
				border: 1px white solid;
			}

			#itemsadded td{
				background: white;
				color: black;
				border: 1px black solid;
			}

			#order-details select{
				height: inherit;
				margin: 2px auto;
			}

			#order-details input,#order-details textarea{
				height: inherit;
				margin: 2px auto;
			}

			@-webkit-keyframes opac {
			    0%   {opacity: 1;}
			    25%  {opacity: .7;}
			    50%  {opacity: .5;}
			    75%  {opacity: .2;}
			    100% {opacity: 0;}
			}

			/* Standard syntax */
			@keyframes opac {
			    0%   {opacity: 1;}
			    25%  {opacity: .7;}
			    50%  {opacity: .5;}
			    75%  {opacity: .2;}
			    100% {opacity: 0;}
			}
			
			.panel-actions{
				display: flex !important;
			}
			
			.abf{
				margin-right: 10px;
			}
		</style>

	</head>
	<body>
	<input type="hidden" name="quickQuotation" id="quickQuotation" value="">
	<input type="hidden" name="FormID" id="FormID" value="<?php echo $_SESSION['FormID']; ?>">
	<input type="hidden" name="orderno" id="orderno" value="<?php echo $orderno; ?>">
	<input type="hidden" name="rootpath" id="rootpath" value="<?php echo $RootPath; ?>">
	<input type="hidden" name="salesref" id="salesref" value="<?php echo $salescaseref; ?>">
    <span id="searchoverlay" class="light overlay">
	<div class="overlay-content" style=" background-color:#ecedf0">
      	<div class="" style="height:50px; background:#424242">
        	<span id="closesearchoverlay" class="pull-right btn btn-danger" style=" padding:15px; height:50px; cursor:pointer">Close</span>
      	</div>

      	<div id="itemsadded" style="padding-left: 15px; padding-right: 15px;">
      		<h2 style="text-align:center; color:black">Line # </span><span id="slnum"></span> , <span>O</span>ption # </span><span id="sonum"></span></h2>
      		<table width="40%" border="1px" style="margin: auto auto;">
      			<thead>
      				<tr>
      					<th>Item Code</th>
      					<th> Brand </th>
      					<th>Description</th>
      				</tr>
      			</thead>
      			<tbody id="cicis" style="background: white; border: 1px white solid">
      				
      			</tbody>
      		</table>
      	</div>
	    <h2 style="text-align:center; color:black">Search <span>I</span>tems</h2>
      	<div class="col-md-12" style="margin-bottom: -20px; text-align: center; color:black">
      		<div class="col-md-4">Category</div>
      		<div class="col-md-4">Description</div>
      		<div class="col-md-4">Stock Code</div>
      	</div>
      	<div class="col-md-12">
	      	<form class="form-inline" style="margin:20px; text-align:center; color: #aaa;">
	      		<div>
	      		
				<div class="col-md-4">
	        	<select style="width:100%; margin-bottom: 10px; color: #655E5D" id="scat" class="" name="scategory">
	        		<option value="All">All</option>
		        <?php 
		        	foreach ($stockCategories as $key => $value) {
		        ?>
	        		<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
		        <?php
		        	} 
		        ?>
	        	</select>
	        	</div>
	        	</div>
	        	
		        <div class="col-md-4">
		        <input style="width: 100%; margin-bottom: 10px; color: #655E5D" class="inp" type="text">
		        </div>
		        
		        <div class="col-md-4">
		        	<input id="scode" class="inp" style="width: 100%; margin-bottom: 10px; color: #655E5D" type="text">
		        </div>
			</form>

	      	
		</div>
		<div class="col-md-6">
		    <input class="btn btn-warning" style="margin:0 auto; width:100%; margin-bottom: 10px;" type="submit" id="bcr" value="Clear Results">
		</div>
		<div class="col-md-6">
	      	<input class="btn btn-primary" style="margin:0 auto; width:100%; margin-bottom: 10px;" type="submit" id="bss" value="Search">
		</div>
		<div class="alert alert-danger"  style="text-align:center; clear: both; margin: 20px 15px">
			<strong>Items with no material cost will generate warning and cannot be added.</strong>
		</div>
		<div id="resultscontainer" class="" style=" background-color:#ecedf0">
			<table id="searchresults" width="100%" class="responsive">
				<thead>
					<tr style="background:#424242; color:white">
						<th>Item Code</th>
						<th>Model #</th>
						<th>Part #</th>
						<th>QOH</th>
						<th>IS-QTY</th>
						<th>Manufacturer</th>
						<th>Description</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody id="srb" style="color: black">
					
				</tbody>
				<tfoot>
					<tr style="background:#424242; color:white">
						<th>Item Code</th>
						<th>Model #</th>
						<th>Part #</th>
						<th>QOH</th>
						<th>IS-QTY</th>
						<th>Manufacturer</th>
						<th>Description</th>
						<th>Action</th>
					</tr>
				</tfoot>
			</table>
		</div>
      </div>
    </span>
	
	<section class="body background-content">

      <header style="font-size:1em; padding:7px; width:100%; background:#424242;">
      	<span style="color:white">
      		<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
      		&nbsp;|&nbsp;
      		<span style="color:#ccc">
      			<?php echo stripslashes($_SESSION['UsersRealName']); ?>
          </span>
      		<span class="pull-right" style="background:#424242; padding: 0 10px;">
      			<a href="<?php echo $RootPath; ?>/index.php" style="color: white; text-decoration: none;">Main Menu</a>
      			<a class="bold" href="<?php echo $RootPath; ?>/Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
      		</span>
      	</span>
      </header>
      <h2 style="text-align:center">Q<span style="font-size:17px">UOTATION</span></h2>
      <h2 style="text-align:center; margin:0"><span style="font-size:17px">Value: <span id="totalquotationvalue"></span></span></h2>
			<section role="main" class="content-body">

        <div class="col-md-12">
					<div class="tabs">
						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#basic" data-toggle="tab" aria-expanded="true">Basic Information</a>
							</li>
							<li class="">
								<a href="#items" data-toggle="tab" aria-expanded="true">Bill Of Quantities</a>
							</li>
							<li class="">
								<a href="#description" data-toggle="tab" aria-expanded="false">Description</a>
							</li>
							<li class="">
								<a href="#groupdiscount" data-toggle="tab" aria-expanded="false">Group Discount</a>
							</li>
							<li class="">
								<a href="#order-details" data-toggle="tab" aria-expanded="false">Quotation Details</a>
							</li>
							<li class="">
								<a href="#epreview" data-toggle="tab" aria-expanded="false">Preview</a>
							</li>
							<li class="">
								<a href="#saveqt" data-toggle="tab" aria-expanded="false">Save</a>
							</li>
						</ul>
						<div class="tab-content">
							<div id="basic" class="tab-pane active">
								<div class="row">
								  <div class="col-md-3">
									Sales Case Ref:
								  </div>
								  <div class="col-md-9">
									<?php echo $salescaseref; ?>
								  </div>
								  <div class="col-md-3">
									Order#:
								  </div>
								  <div id="eorderno" class="col-md-9">
									Loading...
								  </div>
								  <div class="col-md-3">
									Client Name
								  </div>
								  <div id="clientnamebasic" class="col-md-9">
									Loading...
								  </div>
								  <div class="col-md-3">
									Salesman:
								  </div>
								  <div id="salesman" class="col-md-9">
									Loading...
								  </div>
								  <div class="col-md-3">
									Location:
								  </div>
								  <div id="clientlocationbasic" class="col-md-9">
									Loading...
								  </div>
								</div>
							</div>
							<div id="items" class="tab-pane">
								<div id="linescontainer"></div>
								<input type="hidden" class="line">
								<input type="hidden" class="option">
								<input type="hidden" class="option_required">
								<input type="hidden" class="option_quantity">
								<!-- <button id="addline" type="button" name="button" onclick="addline()" class="btn btn-primary">Add New Line</button> -->
								<button id="addline" href="#chooseLine" type="button" name="button" onclick="$('#chooseLine').show()" class="btn btn-primary" 
								data-toggle="popover" title="Choose Line Bellow To Copy:">Add New Line</button>
								<div id="chooseLine"></div>
								
								
							</div>
							<div id="description" class="tab-pane">
								<div id="linesdescriptioncontainer"></div>
							</div>
							<div id="groupdiscount" class="tab-pane">
								<div id="brandslist" class="row">
									
								</div>
								<button class="btn btn-success col-md-12" style="margin-top: 15px" id="updategroupdiscountsbutton">Update Discounts</button>
								<div style="clear: both;"></div>
							</div>
							<div id="order-details" class="tab-pane bongastyle">
								<div class="col-md-12">
									<div class="col-md-3">
										Deliver To:		
									</div>
									<div class="col-md-9">
										<input type="text" class="order-detailss deliverto" autofocus="autofocus" required="required" maxlength="40" name="deliverto" value="" title="Enter the name of the customer to deliver this order to" style="width: 300px">
									</div>
									<div class="col-md-3">
										Deliver from the warehouse at:
									</div>
									<div class="col-md-9">
										<select class="order-detailss fromstkloc" name="fromstkloc" style="width: 300px">
										<?php 
											foreach ($stkLocations as $key => $value) {
										?>
											<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
										<?php
											}
										?>
											
										</select>
									</div>
									<div class="col-md-3">
										Estimated Delivery Date:
									</div>
									<div class="col-md-9">
										<input class="order-detailss deliverydate" alt="d/m/Y" type="text" size="15" maxlength="14" name="deliverydate" value="" title="Enter the estimated delivery date requested by the customer">
									</div>
									<div class="col-md-3">
										Quote Date:
									</div>
									<div class="col-md-9">
										<input class="order-detailss quotedate" alt="d/m/Y" type="text" size="15" maxlength="14" name="quotedate" value=""> <input class="order-detailss umqd" alt="d/m/Y" type="checkbox" size="15" maxlength="14" name="umqd" value="">
									</div>
									<div class="col-md-3">
										Confirmed Order Date:
									</div>
									<div class="col-md-9">
										<input class="order-detailss confirmeddate" alt="d/m/Y" type="text" size="15" maxlength="14" name="confirmeddate" value="">
									</div>
									<div class="col-md-3">
										Delivery Address 1 (Street):
									</div>
									<div class="col-md-9">
										<input class="order-detailss deladd1" type="text" maxlength="40" name="deladd1" value="" style="width: 300px">
									</div>
									<div class="col-md-3">
										Delivery Address 2 (Street):
									</div>
									<div class="col-md-9">
										<input class="order-detailss deladd2" type="text" style="width: 300px" maxlength="40" name="deladd2" value="">
									</div>
									<div class="col-md-3">
										Delivery Address 3 (Suburb/City):
									</div>
									<div class="col-md-9">
										<input class="order-detailss deladd3" type="text" style="width: 300px" maxlength="40" name="deladd3" value="">
									</div>
									<div class="col-md-3">
										Delivery Address 4 (State/Province):
									</div>
									<div class="col-md-9">
										<input class="order-detailss deladd4" type="text" style="width: 300px" maxlength="40" name="deladd4" value="">
									</div>
									<div class="col-md-3">
										Delivery Address 5 (Postal Code):
									</div>
									<div class="col-md-9">
										<input class="order-detailss deladd5" type="text" style="width: 300px" maxlength="40" name="deladd5" value="">
									</div>
									<div class="col-md-3">
										Country:
									</div>
									<div class="col-md-9">
										<select class="order-detailss deladd6" name="deladd6" style="width: 300px">
										<?php 
											foreach ($CountriesArray as $CountryEntry => $CountryName){
										?>
										<option value="<?php echo $CountryName; ?>"><?php echo $CountryName; ?></option>
										<?php 
											}
										?>
										</select>
									</div>
									<div class="col-md-3">
										Contact Person:
									</div>
									<div class="col-md-9">
										<input class="order-detailss contactperson" type="tel" style="width: 300px" maxlength="25" name="contactperson" value="" title="Enter the name of the contact at the delivery address.">
									</div>
									<div class="col-md-3">
										Contact Phone Number:
									</div>
									<div class="col-md-9">
										<input class="order-detailss contactphone" type="tel" style="width: 300px" maxlength="25" name="contactphone" value="" title="Enter the telephone number of the contact at the delivery address.">
									</div>
									<div class="col-md-3">
										Contact Email:
									</div>
									<div class="col-md-9">
										<input class="order-detailss contactemail" type="email" style="width: 300px" maxlength="38" name="contactemail" value="" title="Enter the email address of the contact at the delivery address">
									</div>
									<div class="col-md-3">
										Customer Reference:
									</div>
									<div class="col-md-9">
										<input class="order-detailss customerref" type="text" style="width: 300px" maxlength="50" name="customerref" value="" title="Enter the customer's purchase order reference relevant to this order">
									</div>
									<div class="col-md-3">
										Advance%:
									</div>
									<div class="col-md-9">
										<input class="order-detailss advance" type="number" style="width: 300px" maxlength="25" name="advance" value="">
									</div>
									<div class="col-md-3">
										Delivery%:
									</div>
									<div class="col-md-9">
										<input class="order-detailss delivery" type="number" style="width: 300px" maxlength="25" name="delivery" value="">
									</div>
									<div class="col-md-3">
										Comminsioning%:
									</div>
									<div class="col-md-9">
										<input class="order-detailss commisioning" type="number" style="width: 300px" maxlength="25" name="commisioning" value="">
									</div>
									<div class="col-md-3">
										After%:
									</div>
									<div class="col-md-9">
										<input class="order-detailss after" type="number" style="width: 300px" maxlength="25" name="after" value="">
									</div>
									<div class="col-md-3">
										GST Clause:
									</div>
									<div class="col-md-9">
										<input class="order-detailss gst" type="text" style="width: 300px" maxlength="150" name="gst" value="">
									</div>
									<div class="col-md-3">
										After Days
									</div>
									<div class="col-md-9">
										<select class="order-detailss afterdays" name="afterdays" style="width: 300px">
											<option selected="selected" value="">Select Credit Days</option>
          									<option value="15">15 Days</option>
									        <option value="30">30 Days</option>
									        <option value="45">45 Days</option>
											<option value="60">60 Days</option>
									  	</select>
									</div>
									<div class="col-md-3">
										Quoted Price Valid Till
									</div>
									<div class="col-md-9">
										<select class="order-detailss validity" name="validity" style="width: 300px">
											<option value="0">None</option>
          									<option value="5">5 Days</option>
											<option value="15">15 Days</option>
									        <option value="30">30 Days</option>
									        <option value="45">45 Days</option>
											<option value="60">60 Days</option>
									  	</select>
									</div>
									<div class="col-md-3">
										Services
									</div>
									<div class="col-md-9" style="text-align: left;">
										<input class="order-detailss services" type="checkbox" name="services" value="" style="width: 300px">
									</div>
                                    <div class="col-md-3">
                                        Print
                                    </div>
                                    <div class="col-md-9">
                                    <table cellspacing="3px">
                                        <tr><td style="text-align: left;width: 200px;"> Only Local Price </td><td> <input class="order-detailss printexchange1" type="radio" name="printexchange" value="0" ></td></tr>
                                        <tr><td style="text-align: left;width: 200px;"> With Exchange Prices </td><td> <input class="order-detailss printexchange2" type="radio" name="printexchange" value="1" ></td></tr>
                                        <tr><td style="text-align: left;width: 200px;"> Only Exchange Prices </td><td> <input class="order-detailss printexchange3" type="radio" name="printexchange" value="2" ></td></tr>






                                    </table>
                                    </div>
									<div class="col-md-3">
										GST
									</div>
									<div class="col-md-9">
										<select class="order-detailss GSTadd" name="GSTadd" style="width: 300px">
											<option selected="selected" value="">None</option>
									        <option value="inclusive">Inclusive GST</option>
									        <option value="exclusive">Exclusive GST</option>
									  	</select>
									</div>
									<div class="col-md-3">
										Witholding Tax
									</div>
									<div class="col-md-9">
										<select class="order-detailss WHT" name="WHT" style="width: 300px">
										<option value=0>None</option>
								        <option value=4.5>4.5%</option>
								        <option value=6.5>6.5%</option>
									  	</select>
									</div>
                                    <div class="col-md-3">
                                        Freight Clause
                                    </div>
                                    <div class="col-md-9">
                                        <select class="order-detailss freightclause" name="freightclause" style="width: 300px">
                                            <option value='EXW'>EXW</option>
                                            <option value='FOR'>FOR</option>

                                        </select>
                                    </div>
									<div class="col-md-3">
										Comments:
									</div>
									<div class="col-md-9">
										<textarea class="order-detailss comments" name="comments"  style="width: 300px" rows="5"></textarea>
									</div>
									<div class="col-md-3">
										Rate Clause:
									</div>
									<div class="col-md-9">
										<select class="order-detailss rate_clause" id="orderDetailsRateClause" name="rate_clause" style="width: 300px">

											<option value="aed">AED</option>
											<option value="usd">USD</option>
											<option value="euro">EURO</option>
											<option value="pound">POUND</option>
									  	</select>
									 
									  	<button class="btn btn-warning" id="updateQuoteRateValidityPrices">
									  		Update
									  	</button>
									</div>
									<div class="col-md-3">
										Rate Clause Validity:
									</div>
									<div class="col-md-9">

										<input type="date" class="order-detailss rate_validity" id="rate_validity_date" name="rate_validity">
									</div>
								</div>
								<h5 style="color: white">...</h5>
							</div>
							<div id="epreview" class="tab-pane">
								<button id="erbtn" class="btn btn-primary" style="width:100%; margin-bottom: 20px;">Render</button>
								<div id="epreviewcontainer">
									
								</div>
							</div>
							<div id="saveqt" class="tab-pane">
								
								<div id="warningscontainer">
									
								</div>
								<button id="proceedanyway" class="btn btn-success" style="width:100%; margin-top: 20px; display: none;">Proceed Anyway</button>
								<button id="checkforwarnings" class="btn btn-success" style="width:100%; margin-top: 20px">Save</button>
							</div>
						</div>
					</div>
				</div>

			</section>

		<section id="rendering" style="height: 100px; width: 100%; position: fixed; bottom: 0; left: 0; background: #424242;text-align: center;">

			<h1>Rendering Quotation View</h1>
			
		</section>

		<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index:150">
			Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
		</footer>
		</section>

		<script src="quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="quotation/assets/vendor/nprogress/nprogress.js"></script>
		<script src="quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="quotation/assets/vendor/pnotify/pnotify.custom.js"></script>

		<script src="quotation/assets/javascripts/theme.js"></script>
		<script type="text/javascript" src="includes/textboxio/textboxio.js"></script>
		<script src="quotation/assets/quotations.js?version=<?php echo generateRandom() ?>"></script>
  		
	</body>
</html>
