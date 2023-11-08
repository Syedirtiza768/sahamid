<?php

	$PathPrefix='../';

	include('../includes/session.inc');
	include('../includes/CountriesArray.php');

	include('misc.php');

	$RootPath = explode("/shopdc", $RootPath)[0];

	if(!(isset($_GET['ModifyOrderNumber']) && isset($_GET['salescaseref']))){

		if(isset($_GET['salescaseref']))
			header('Location: ../salescase.php?salescaseref='.$_GET['salescaseref']);
		else
			header('Location: ../salescase.php');

		exit;

	}

	$salescaseref = $_GET['salescaseref'];
	$orderno 	  = $_GET['ModifyOrderNumber'];

	if(isIncorrectSalesCase($salescaseref)){

		returnErrorResult($RootPath,$salescaseref, "Invalid Salescase");
		exit;

	}

	$db = createDBConnection();

	$SQL = "SELECT * FROM dcs 
			WHERE salescaseref='".$salescaseref."' 
			AND orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		returnErrorResult($RootPath,$salescaseref, "DC not found");
		exit;

	}

	$SQL = "SELECT locationname,loccode FROM locations";

	$result = mysqli_query($db, $SQL);

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

		<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="../quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />

		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>
		
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
				color: black !important;
				border-radius: 4px;
				background: #f9f9f9;
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
				color: white;
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
			}
			
			.overlay-content {
				height: 100%;
				overflow: scroll;
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

			#slips a{
				margin:10px 0;
			}

			/* Standard syntax */
			@keyframes opac {
			    0%   {opacity: 1;}
			    25%  {opacity: .7;}
			    50%  {opacity: .5;}
			    75%  {opacity: .2;}
			    100% {opacity: 0;}
			}

		</style>

	</head>
	<body>
	<input type="hidden" name="orderno" id="orderno" value="<?php echo $orderno; ?>">
	<input type="hidden" name="rootpath" id="rootpath" value="<?php echo $RootPath; ?>">
	<input type="hidden" name="salesref" id="salesref" value="<?php echo $salescaseref; ?>">
    <span id="searchoverlay" class="light overlay">
	<div class="overlay-content" >
      	<div class="" style="height:50px; background:#424242">
        	<span id="closesearchoverlay" class="pull-right btn btn-danger" style=" padding:15px; height:50px; cursor:pointer">Close</span>
      	</div>

      	<div id="itemsadded" style="padding-left: 15px; padding-right: 15px;">
      		<h2 style="text-align:center">L<span style="font-size:17px">INE # </span><span id="slnum"></span> , <span>O</span><span style="font-size:17px">PTION # </span><span id="sonum"></span></h2>
      		<table width="40%" border="1px" style="margin: auto auto;">
      			<thead>
      				<tr>
      					<th>Item Code</th>
      					<th>Brand</th>
      					<th>Description</th>
      				</tr>
      			</thead>
      			<tbody id="cicis" style="background: white; border: 1px white solid">
      				
      			</tbody>
      		</table>
      	</div>
	    <h2 style="text-align:center">S<span style="font-size:17px">EARCH</span> <span>I</span><span style="font-size:17px">TEMS</span></h2>
      	<div class="col-md-12" style="margin-bottom: -20px; text-align: center;">
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
		<div class="alert alert-warning"  style="text-align:center; clear: both; margin: 20px 15px">
			<strong>Items with no material cost will generate warning and cannot be added.</strong>
		</div>
		<div id="resultscontainer">
			<table id="searchresults" width="100%" class="responsive">
				<thead>
					<tr>
						<th>Item Code</th>
						<th>Model #</th>
						<th>Part #</th>
						<th>QOH</th>
						<th>Manufacturer</th>
						<th>Description</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody id="srb" style="color: black">
					
				</tbody>
				<tfoot>
					<tr>
						<th>Item Code</th>
						<th>Model #</th>
						<th>Part #</th>
						<th>QOH</th>
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
      <h2 style="text-align:center">S<span style="font-size:17px">HOP </span>D<span style="font-size:17px">ELIVERY</span> C<span style="font-size:17px">HALLAN</span></h2>
      <h2 style="text-align:center; margin:0"><span style="font-size:17px"> <span id="totalquotationvalue"></span></span></h2>
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
								<a href="#order-details" data-toggle="tab" aria-expanded="false">Order Details</a>
							</li>
							<li class="">
								<a href="#slips" data-toggle="tab" aria-expanded="false">Slips</a>
							</li>
							<li class="">
								<a href="#epreview" data-toggle="tab" aria-expanded="false">Preview</a>
							</li>
							<li class="saveclass">
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
									Order# (DC):
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
								<button id="addline" type="button" name="button" onclick="addline()" class="btn btn-primary">Add New Line</button>
							</div>
							<div id="description" class="tab-pane">
								<div id="linesdescriptioncontainer"></div>
								<button id="addlinefromdescription" type="button" name="button" onclick="addline()" class="btn btn-primary" style="margin-top:20px">Add New Line</button>
							</div>
							<div id="groupdiscount" class="tab-pane">
								<div id="brandslist" class="row">
									
								</div>
								<button class="btn btn-success col-md-12" style="margin-top: 15px" id="updategroupdiscountsbutton">
									Update Discounts
								</button>
								<div style="clear: both;"></div>
							</div>
							<div id="order-details" class="tab-pane bongastyle">
								<div class="col-md-12">
									<div class="col-md-3">
										Market Purchase:
									</div>
									<div class="col-md-9">
										<input type="checkbox" class="order-detailss mp" name="mp"> 
									</div>
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
										<input class="order-detailss quotedate" alt="d/m/Y" type="text" size="15" maxlength="14" name="quotedate" value="">
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
										<input class="order-detailss customerref" type="text" style="width: 300px" maxlength="25" name="customerref" value="" title="Enter the customer's purchase order reference relevant to this order">
									</div>
									<div class="col-md-3">
										Advance%:
									</div>
									<div class="col-md-9">
										<input class="order-detailss advance" type="number" style="width: 300px" maxlength="25" name="advance" value="">
									</div>
									<div class="col-md-3">
										Dispatch Through: 
									</div>
									<div class="col-md-9">
										<input class="order-detailss dispatchthrough" type="text" style="width: 300px" maxlength="25" name="dispatchthrough" value="">
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
                                        Services
                                    </div>
                                    <div class="col-md-9">
                                        <input class="order-detailss services" type="checkbox" name="services" value="" style="width: 300px">
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

										  <button class="btn btn-warning" id="updateDCGST">
									  		Update
									  	</button>
									</div>
									<div class="col-md-3">
										Comments:
									</div>
									<div class="col-md-9">
										<textarea class="order-detailss comments" name="comments"  style="width: 300px" rows="5"></textarea>
									</div>
								</div>
								<h5 style="color: white">...</h5>
							</div>
							<div id="slips" class="tab-pane">
								<div class="row" id="slipscontainer">
									
								</div>
							</div>
							<div id="epreview" class="tab-pane">
								<button id="erbtn" class="btn btn-primary" style="width:100%; margin-bottom: 20px;">Render</button>
								<div id="epreviewcontainer">
									
								</div>
							</div>
							<div id="saveqt" class="tab-pane saveclass">
								<?php if(userHasPermission($db,'save_shop_dc')){ ?>
								<div id="warningscontainer">
									
								</div>
								<button id="proceedanyway" class="btn btn-success" style="width:100%; margin-top: 20px; display: none;">Proceed Anyway</button>
								<button id="checkforwarnings" class="btn btn-success" style="width:100%; margin-top: 20px">Save</button>
								<?php } ?>
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

		<script src="../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../quotation/assets/vendor/nprogress/nprogress.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>

		<script src="../quotation/assets/javascripts/theme.js"></script>
		<script type="text/javascript" src="../includes/textboxio/textboxio.js"></script>
		<script src="dc.js?version=<?php echo generateRandom() ?>"></script>
  
	</body>
</html>
