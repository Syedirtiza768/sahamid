<?php

	$PathPrefix = "../../";
	include("../../includes/session.inc");
	
	if(!userHasPermission($db, 'create_shop_sale')){
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}

	$SQL = "SELECT custbranch.branchcode as debtorno,custbranch.brname as name 
			FROM custbranch
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = custbranch.debtorno
			WHERE custbranch.branchcode NOT LIKE 'MV-%'  
			AND debtorsmaster.dba='SA HAMID AND COMPANY'
			OR debtorsmaster.dba='SAH'";
	$clients = mysqli_query($db, $SQL);

	$SQL = "SELECT * FROM dba";
	$dba = mysqli_query($db, $SQL);

	$SQL = "SELECT * FROM debtortype";
	$debtortype = mysqli_query($db, $SQL);

	$SQL = "SELECT salesmancode, salesmanname FROM salesman";
	$salesman = mysqli_query($db, $SQL);

?>

<!DOCTYPE html>
<html>
	<head>
		<title>ERP-SAHamid</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/nanoscroller/nanoscroller.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables/media/css/jquery.dataTables.css">
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css">
		<link rel="stylesheet" href="../../quotation/assets/vendor/pnotify/pnotify.custom.css">
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../parchi/inward/assets/searchSelect.css" />


		<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>

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
			.noCustomerSelected{
				text-align: center;
				width: 100%;
				height: 100px
			}
			.itemsContainer{
				width: 100%;
			}
			.itemdet, .notes{
				width: 100%;
				height: 40px;
				min-height: 60px;
				border: 1px solid #424242;
				border-radius: 7px;
				padding: 3px;
			}
			.number{
				border: 1px solid #424242;
				border-radius: 7px;
			}
			.quantity{
				width: 50px;
			}
			.price{
				width: 80px;
			}
			.subtotal{
				width: 100px;
			}
			.uom{
				padding: 0px;
				height: auto;
			}
			.color-white{
				color: white;
			}
			.input-info{
				width: 100%;
				border: 1px solid #424242;
				border-radius: 7px;
			}
			#clientslist, #dbalist, #debtortype, #salesman{
				width: 100%; 
				border: 1px solid #424242; 
				padding: 0px; 
				height: 30px;
			}
			#proceed{
				width: 100%; 
				margin-bottom: 15px; 
				padding: 10px 0;
			}
			.discountPercent, .discountPKR{
				width: 55px;
				border: 1px solid #424242;
				border-radius: 7px;
				color: #424242;
				padding: 0 3px;
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
	      				<a href="<?php echo $RootPath; ?>/../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
	      				<a class="bold" href="<?php echo $RootPath; ?>/../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
	      			</span>
	      		</span>
	      		<input type="hidden" id="FormID" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
	      	</header>

	      	<h3 style="text-align: center; font-variant-caps: petite-caps;">
	      		<i class="fa fa-sign-in" aria-hidden="true"></i> 
	      		Showroom
	      	</h3>
            <h2 style="text-align:center; margin:0"><span style="font-size:17px">Value: <span id="totalquotationvalue"></span></span></h2>


            <div class="col-md-12" style="margin-bottom: 15px;">
	      		<div class="col-md-12" style="border: 2px solid #424242;">
                    <div class="col-md-12" style="color: black; font-size: .7em">
                        <h3>
                            <div class="col-md-6" style="color: white; font-size: .7em">Payment Type: </div>
                            <?php if(userHasPermission($db, 'can_create_csv')){ ?>
                                <label class="col-md-3" style="color: white; border-left: 1px solid white; padding-bottom: 15px; font-size: .7em">
                                    <input name="payment-type" type="radio" class="payment-type" value="csv" id="payment-csv"> CSV (Cash Sale)
                                </label>
                            <?php } ?>
                            <?php if(userHasPermission($db, 'can_create_crv')){ ?>
                                <label class="col-md-3" style="color: white; border-left: 1px solid white; padding-bottom: 15px; font-size: .7em">
                                    <input name="payment-type" type="radio" class="payment-type" value="crv" id="payment-crv"> CRV
                                </label>
                            <?php } ?>
                        </h3>
                    </div>
	      			<div class="row" style="background-color: #424242; margin-bottom: 15px; padding-bottom: 15px">
	      				<div class="col-md-12">
	      					<div class="row">
	      						<div class="col-md-4">
				      				<h5 class="color-white">Select Customer:</h5>
					      			<select id="clientslist">
					      				<option value="">Select Customer</option>
										<?php if(userHasPermission($db, 'insert_shop_client')){ ?>
					      				<option value="addNew">Add New Customer</option>
										<?php } ?>
					      				<?php while($row = mysqli_fetch_assoc($clients)){ ?>
										<option value="<?php echo $row['debtorno']; ?>">
											<?php echo $row['name']; ?>
										</option>
					      				<?php } ?>
					      			</select>
				      			</div>
				      			<div class="col-md-4">
				      				<h5 class="color-white">Customer Name:</h5>
				      				<input type="text" class="input-info" id="customername">
					      		</div>
					      		<div class="col-md-4">
				      				<h5 class="color-white">Customer Code:</h5>
				      				<input type="text" class="input-info" id="customercode">
					      		</div>
	      					</div>
	      					<div class="row">
				      			<div class="col-md-4">
				      				<h5 class="color-white">DBA:</h5>
				      				<select id="dbalist" disabled>
					      				<option value="">Select DBA</option>
					      				<option value="SAH">SAH</option>
					      				<option value="SA HAMID AND COMPANY">SA HAMID AND COMPANY</option>
					      			</select>
					      		</div>
					      		<div class="col-md-4">
				      				<h5 class="color-white">Customer Type:</h5>
				      				<select id="debtortype" disabled>
					      				<option value="">Select Type</option>
					      				<?php while($row = mysqli_fetch_assoc($debtortype)){ ?>
										<option value="<?php echo $row['typeid']; ?>">
											<?php echo $row['typename']; ?>
										</option>
					      				<?php } ?>
					      			</select>
					      		</div>
					      		<div class="col-md-4">
				      				<h5 class="color-white">Salesman:</h5>
				      				<select id="salesman" disabled>
					      				<option value="">Select Salesman</option>
					      				<?php while($row = mysqli_fetch_assoc($salesman)){ ?>
										<option value="<?php echo $row['salesmancode']; ?>">
											<?php echo $row['salesmanname']; ?>
										</option>
					      				<?php } ?>
					      			</select>
				      			</div>
	      					</div>
	      					<div class="row">
					      		<div class="col-md-4">
				      				<h5 class="color-white">Address Line 1 (Street):</h5>
				      				<input type="text" class="input-info" id="address1">
				      			</div>
				      			<div class="col-md-4">
				      				<h5 class="color-white">Address Line 2:</h5>
				      				<input type="text" class="input-info" id="address2">
				      			</div>
				      			<div class="col-md-4">
				      				<h5 class="color-white">Address Line 3:</h5>
				      				<input type="text" class="input-info" id="address3">
				      			</div>
	      					</div>

	      				</div>
	      			</div>

                    <div class="col-md-12" style="border: 2px solid #424242; min-height: 400px">

                        <h4 style="text-align: center; background-color: #424242; padding: 15px 0; margin: 0 -15px; color: white">
		      				Items
		      			</h4>
						<table class="itemsContainer table table-striped" border="1" style="margin-bottom: 15px;">
							<thead>
								<tr style="background: #424242; color: white">
									<th style="width: 1%">Sr#</th>
									<th>Name</th>
									<th>Model/Comments/Notes</th>
									<th style="width: 1%">Quantity</th>
									<th style="width: 1%">UOM</th>
									<th style="width: 1%">Price</th>
									<th style="width: 1%">SubTotal</th>
									<th style="width: 1%">Action</th>
								</tr>
							</thead>
							<tbody id="itemsContainer">
								
							</tbody>
							<tfoot>
								<tr style="background: #424242; color: white">
									<th colspan="4" style="text-align: right;">Discount <sub>%</sub> </th>
									<th>
										<input type="number" class="discountPercent" value="0">
									</th>
									<th colspan="2" style="text-align: right;">Discount <sub>PKR</sub> </th>
									<th>
										<input type="number" class="discountPKR" value="0">
									</th>
									
								</tr>
								<tr style="background: #424242; color: white">
									<th colspan="6" style="text-align: right;">Total: </th>
									<th colspan="2"><span id="totalValue"></span><sub> PKR</sub></th>
								</tr>
							</tfoot>
						</table>
						<h4 style="float: right;">
							<button class="btn btn-success" id="addNewItem">Add Item</button>
						</h4>
		      		</div>
					<?php if(userHasPermission($db, 'can_create_csv') || userHasPermission($db, 'can_create_crv')){ ?>
		      		<div class="col-md-12" style="margin-bottom: 15px; background-color: #424242;">

		      			<div style="clear: both"></div>
		      			<h3 style="border-top:1px solid white; padding-top: 15px; display: none">
		      				<div class="col-md-6" style="color: white; font-size: .7em">Advance: </div>
		      				<label class="col-md-6" style="color: white; border-left: 1px solid white; padding-bottom: 15px">
		      					 <input name="" type="number" class="advance-payment" value="" id="advance-payment" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px"> 
		      				</label>
		      			</h3>
		      			<div style="clear: both"></div>
		      			<h3 style="border-top:1px solid white; padding-top: 15px; display: none">
		      				<div class="col-md-2" style="color: white; font-size: .7em">
		      					Customer Name (Optional): 
		      				</div>
		      				<label class="col-md-4" style="color: white; padding-bottom: 15px">
		      					 <input name="" type="text" class="csv-cust-name" value="" id="csv-cust-name" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px"> 
		      				</label>
		      				<div class="col-md-2" style="color: white; font-size: .7em; border-left: 1px solid white;">
		      					SalesPerson Cart: 
		      				</div>
                            <!---->
		      				<label class="col-md-4" style="color: white; padding-bottom: 15px">
		      					<select name="" 
		      					 	type="text" 
		      					 	class="csv-salesperson" 
		      					 	id="csv-salesperson" 
		      					 	style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; font-size: .6em;"> 
									<option value="">Select SalesPerson</option>
									<option value="Ashfaq Ahmad">Ashfaq Ahmad</option>
                                    <option value="Mohsin Iqbal">Mohsin Iqbal</option>
                                    <option value="Yasir Mehfooz">Yasir Mehfooz</option>
                                    <option value="Mushahid Hussain">Mushahid Hussain</option>
									<option value="Sultan Mahmood">Sultan Mahmood</option>
									<option value="Mazhar Iqbal">Mazhar Iqbal</option>
									<option value="Jalal Nasir">Jalal Nasir</option>
									<option value="Nasir Hussain">Nasir Hussain</option>
									<option value="Meer Zaman">Meer Zaman</option>
                                    <option value="Muhammad Bilal">Muhammad Bilal</option>
                                    <option value="Aamir Obaid">Aamir Obaid</option>
                                    <option value="Ejaz Ahmed">Ejaz Ahmed</option>
                                    <option value="Zohaib Ahmed">Zohaib Ahmed</option>
                                    <option value="Muhammad Arif">Muhammad Arif</option>
                                    <option value="Ahsan Qureshi">Ahsan Qureshi</option>
                                    <option value="Usman Sarwar">Usman Sarwar</option>
                                    <option value="Mubashar Tahir">Mubashar Tahir</option>
                                    <option value="Ali Imran">Ali Imran</option>
                                    <option value="Sajjad Ahmed Khan">Sajjad Ahmed Khan</option>
                                    <option value="Ammar Hafeez">Ammar Hafeez</option>
                                    <option value="Muhammad Shehzad">Muhammad Shehzad</option>
		      					</select>
		      				</label>
		      			</h3>
		      		</div>
					<?php } ?>
					<div class="col-md-12" style="margin-bottom: 15px; background-color: #424242;">
						<h3 style="display: flex; justify-content: space-between; align-items:center;">
							<input name="" type="text" class="shipvia" placeholder="Dispatch Via" value="" id="shipvia" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; width:30%; text-align:center">
							<input name="" type="text" class="creferance" placeholder="Customer Referance" value="" id="creferance" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; width:30%; text-align:center">
							<input name="" type="number" class="amountpaid" placeholder="Amount Paid" value="" id="amountpaid" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; width:30%; text-align:center">
						</h3>
					</div>
					
					<button class="btn btn-success" id="proceed">Proceed</button>

	      		</div>
	      	</div>
	      	
			<div style="clear: both;"></div>

	      	<footer style="background:#424242; bottom:0; width:100%; text-align:center; padding: 5px">
      			Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
      		</footer>
	      	
		</section>
      	
		<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../../quotation/assets/vendor/pnotify/pnotify.custom.js"></script>
		<script src="../../quotation/assets/javascripts/theme.js"></script>
		<script src="../parchi/inward/assets/searchSelect.js"></script>


		<script>
		
			var clientsListSelect = null;

			$(document).ready(function(){
				addNewItem();

				clientsListSelect = $("#clientslist").select2();


			});

			$("#addNewItem").on("click", function(){
				addNewItem();
			});

			$(document.body).on("click",'.deleteItem',function(){
				$(this).parent().parent().remove();

				let count = 1;
				$("#itemsContainer").find("tr").each(function(){
					$(this).find(".indexCount").html(count);
					count++;
				});

			});

			$(document.body).on("change",'.quantity',function(){

				let ref = $(this);

				if($(this).val() < 0){
					$(this).val(0);
				}
				
				updateSubTotal(ref);
				recalculateTotal();
                updateQuotationValue();

			});

			$(document.body).on("change",'.price',function(){
				
				let ref = $(this);

				if($(this).val() < 0){
					$(this).val(0);
				}
				
				updateSubTotal(ref);
				recalculateTotal();
                updateQuotationValue();

			});

			$("#proceed").on("click", function(){

				let ref = $(this);
				ref.prop("disabled",true);

				let client = {};

				if($("#clientslist").val() == ""){
					swal("Error","Client Not Selected!!!","error");
					ref.prop("disabled",false);
					return;
				}

				if($("#clientslist").val() == "addNew"){
					
					if($("#customername").val().trim() == ""){

						swal("Error","Enter New Customer Name","error");
						ref.prop("disabled",false);
						return;

					}

					if($("#salesman").val() == ""){

						swal("Error","Select Customer Salesman","error");
						ref.prop("disabled",false);
						return;

					}

					if($("#dbalist").val() == ""){

						swal("Error","Select DBA","error");
						ref.prop("disabled",false);
						return;

					}

					if($("#debtortype").val() == ""){

						swal("Error","Select Customer Type","error");
						ref.prop("disabled",false);
						return;

					}

					if($("#address1").val().trim() == ""){

						swal("Error","Enter Customer Street Address","error");
						ref.prop("disabled",false);
						return;

					}

					client['type'] = "new";

					client["name"] 		= $("#customername").val();
					client["salesman"] 	= $("#salesman").val();
					client["dba"] 		= $("#dbalist").val();
					client["ctype"] 	= $("#debtortype").val();
					client["address1"] 	= $("#address1").val();
					client["address2"] 	= $("#address2").val();
					client["address3"] 	= $("#address3").val();

				}else{

					client['type'] = "old";
					client['branchcode'] = $("#clientslist").val();

				}

				let count = 0;
				let pass = true;
				let message = "";
				let items = {};

				$("#itemsContainer").find("tr").each(function(){

					let quantity = $(this).find(".quantity").val();
					let price = $(this).find(".price").val();
					let desc = $(this).find(".itemdet").val();
					let note = $(this).find(".notes").val();
					let uom = $(this).find(".uom").val();

					if(pass){
						if(quantity <= 0){
							message = "Item with 0 Quantity Found.";
							pass = false;
						}else if(price <= 0){
							message = "Item with 0 Price Found.";
							pass = false;
						}else if(desc.trim() == ""){
							message = "Item without details found.";
							pass = false;
						}
					}

					items[count] = {};

					items[count]["quantity"] = quantity;
					items[count]["price"] = price;
					items[count]["desc"] = desc.replace("\n","&#10;");
					items[count]["note"] = note.replace("\n","&#10;");
					items[count]["uom"] = uom;

					count++;

				});

				if(!pass){
					swal("Error",message,"error");
					ref.prop("disabled",false);
					return;
				}

				if(count == 0){
					swal("Error","No Items Added.","error");
					ref.prop("disabled",false);
					return;
				}

				let payment = $("input[name=payment-type]:checked").val();

				if(typeof payment == "undefined"){
					swal("Error","Payment Type Not Selected!!!","error");
					ref.prop("disabled",false);
					return;
				}

				let advance = $("#advance-payment").val() != "" ? $("#advance-payment").val() : 0; 

				let name = $("#csv-cust-name").val();

				let discount = $(".discountPercent").val();
				let discountPKR = $(".discountPKR").val();

				let salesman = $("#csv-salesperson").val();
				if(payment == "csv"){
					if(salesman == ""){
						swal("Error","Salesman not selected!!!","error");
						ref.prop("disabled",false);
						return;
					}
				}
				
				let dispatchvia = $("#shipvia").val().trim();
				let customerref = $("#creferance").val().trim();
				let paid		= $("#amountpaid").val();

				generateBill(client,items,payment,advance,name,discount,discountPKR,salesman,dispatchvia,customerref,paid);

			});

			$("#clientslist").on("change", function(){

				let val = $(this).val();

				if(val == ""){
					
					$("#customername").val("");
					$("#customercode").val("");
					$("#salesman").val("");
					$("#dbalist").val("");
					$("#debtortype").val("");
					$("#address1").val("");
					$("#address2").val("");
					$("#address3").val("");

					$("#customername").prop("disabled", true);
					$("#salesman").prop("disabled", true);
					$("#dbalist").prop("disabled", true);
					$("#debtortype").prop("disabled", true);
					$("#address1").prop("disabled", true);
					$("#address2").prop("disabled", true);
					$("#address3").prop("disabled", true);

					return;

				}

				if(val == "addNew"){

					$("#customername").val("");
					$("#customercode").val("");
					$("#salesman").val("");
					$("#dbalist").val("");
					$("#debtortype").val("");
					$("#address1").val("");
					$("#address2").val("");
					$("#address3").val("");

					$("#customername").prop("disabled", false);
					$("#salesman").prop("disabled", false);
					$("#dbalist").prop("disabled", false);
					$("#debtortype").prop("disabled", false);
					$("#address1").prop("disabled", false);
					$("#address2").prop("disabled", false);
					$("#address3").prop("disabled", false);

					return;

				}

				$.get("api/getCustomerData.php?branchCode="+val, function(res, status){
					res = JSON.parse(res);
					
					if(res.status != "error"){

						$("#customername").val(res.data.brname);
						$("#customercode").val(res.data.branchcode);
						$("#salesman").val(res.data.salesman);
						$("#dbalist").val(res.data.dba);
						$("#debtortype").val(res.data.typeid);
						$("#address1").val(res.data.braddress1);
						$("#address2").val(res.data.braddress2);
						$("#address3").val(res.data.braddress3);


                        window.totalOutstanding= parseInt(res.data.credit);
                        window.currentCredit =  window.totalOutstanding?window.totalOutstanding:0;
                        window.creditLimit = parseInt(res.data.creditlimit);

                        window.formID = res.data.formid;
                        window.debtorno = res.data.debtorno;
                        if(res.data.branchcode!="WALKIN01")
                        {

                            let overLimit = "";
                        if ((window.currentCredit) > window.creditLimit) {
                            $('#totalquotationvalue').css("color", "red");
                            overLimit = ` ( ${window.creditLimit - (window.currentCredit)} Over Credit Limit)`;
                        } else {
                            $('#totalquotationvalue').css("color", "#424242");
                            overLimit = `${window.creditLimit - (window.currentCredit)}`;
                        }


                        formID = res.data.formid;
                        window.formID = res.data.formid;
                        let statementLink = `
			<form id="printStatementForm" action="/sahamid/reports/balance/custstatement/../../../customerstatement.php" method="post" target="_blank">
				<input type="hidden" name="FormID" value="${window.formID}">
				<input type="hidden" name="cust" value="${window.debtorno}">
				<input type="submit" value="Customer Statement" class="btn-info" style="padding: 8px; border:1px #424242 solid;">
			</form>
		`;
                        let html = "<span style='font-size: larger;'><b>Kindly share the ledger with the customer. Your cooperation is highly valuable for the company</b></span>";
                        html += `<center><table border="2" style="font-size: large;color:red;">

					         <tr><td> &nbsp;Total Outstanding &nbsp;</td><td> &nbsp;Credit Remaining &nbsp;</td></tr>` +
                            `<tr><td>${(Math.round(window.totalOutstanding).toLocaleString())}</td><td>${(overLimit)}</td></tr></table></center><br/>`;
                        html += `<center>${(statementLink)}</center>`;
                        //	$('#totalquotationvalue').html(`<table>Total Outstanding (${(Math.round(window.totalOutstanding).toLocaleString())}) Document Total: `+Math.round(quotTotal).toLocaleString()+overLimit.toLocaleString());
                        $('#totalquotationvalue').html(html);
                        swal({
                            title: "Alert!!!",
                            text: html,
                            type: 'warning',
                            confirmButtonColor: "#cc3f44",
                            confirmButtonText: 'Print Statement',
                            closeOnConfirm: true,
                            html: true
                        }, function () {
                            confirmed = true;
                            $("#printStatementForm").submit();
                        });

                        }
                        updateQuotationValue();

                    }else{

						swal("Error",res.message,"error");

					}
				});

			});

			function recalculateTotal(){

				let total = 0;

				$("#itemsContainer").find("tr").each(function(){
					total += parseInt($(this).find(".subtotal").val());
				});

				let discount = $(".discountPercent").val()/100;

				total = total * (1 - discount);

				total -= $(".discountPKR").val();

				$("#totalValue").html("");

				$("#totalValue").html(Math.round(total*100)/100);

			}

			function updateSubTotal(ref){

				let quantity = ref.parent().parent().find(".quantity").val();
				let price 	 = ref.parent().parent().find(".price").val();

				ref.parent().parent().find(".subtotal").val(Math.round(quantity*price*100)/100);
                updateQuotationValue();

			}
			
			function addNewItem(){	

				let count = 0;

				$("#itemsContainer").find("tr").each(function(){
					count++;
				});
								
				let html = "<tr>";
				html += '<td style="text-align: center;" class="indexCount">'+(count+1)+'</td>';
				html += '<td><textarea name="" class="itemdet"></textarea></td>';
				html += '<td><textarea name="" class="notes"></textarea></td>';
				html += '<td><input type="number" class="number quantity" value="0"></td>';
				html += '<td><select class="number uom">';
				<?php 
					$SQL = "SELECT * FROM unitsofmeasure"; 
					$res = mysqli_query($db, $SQL); 
					while($row = mysqli_fetch_assoc($res)){
				?>
				html += '<option value="<?php echo $row['unitname']; ?>"><?php echo $row['unitname']; ?></option>';
				<?php } ?>
				html += '</select></td>';
				html += '<td><input type="number" class="number price" value="0"></td>';
				html += '<td><input type="number" class="number subtotal" value="0" disabled></td>';
				html += '<td style="text-align: center;">';
				html += '<button class="btn btn-danger deleteItem" style="font-size: 8px">X</button>';
				html += '</td>';
				html += '</tr>';

				$("#itemsContainer").append(html);

			}

			function generateBill(client, items, payment, advance, name, discount, discountPKR, salesman, dispatchvia, creferance, paid){

				$.post("api/generateBill.php",{
					client:client,
					items:items,
					payment:payment,
					advance:advance,
					name:name,
					discount:discount,
					discountPKR:discountPKR,
					salesman:salesman,
					dispatchvia:dispatchvia,
					creferance:creferance,
					paid:paid,
					FormID:'<?php echo $_SESSION['FormID']; ?>'
				}, function(res, something, something2){
					res = JSON.parse(res);
					if(res.status == "success"){
						window.location = "editShopSale.php?orderno="+res.code;
					}
				});

			}

			$(document.body).on("keypress","textarea",function(event) {
			   if (event.which == 13) {
			      event.preventDefault();
			      var s = $(this).val();
			      $(this).val(s+"\n");
			   }
			});

			$(".payment-type").on("change",function(){

				if($(this).val() == "csv"){
					$("#csv-cust-name").parent().parent().css("display","block");
					$("#advance-payment").parent().parent().css("display","none");
					
					$("#clientslist").prop("disabled",true);
					
					clientsListSelect.val("WALKIN01").trigger("change");
					
				}else{
					$("#csv-cust-name").parent().parent().css("display","none");
					$("#advance-payment").parent().parent().css("display","block");
					$("#advance-payment").val(0);
					
					$("#clientslist").prop("disabled",false);
					
					clientsListSelect.val("").trigger("change");
					
				}

			});

			$(".discountPercent").on("change", function(){

				if($(this).val() == "")
					$(this).val(0);

				recalculateTotal();

			});

			$(".discountPKR").on("change", function(){

				if($(this).val() == "")
					$(this).val(0);

				recalculateTotal();

			});
            function updateQuotationValue(){

                var quotTotal = 0;

                $('.subtotal').each(function(){
                    console.log('hello');
                    quotTotal += Number($(this).val());

                });

                console.log(window.currentCredit + quotTotal);
                console.log(window.creditLimit);
                console.log(window.totalOutstanding);
                let overLimit = "";
                if((window.currentCredit + quotTotal) > window.creditLimit){
                    $('#totalquotationvalue').css("color","red");
                    overLimit = ` ( ${window.creditLimit - (window.currentCredit + quotTotal)} Over Credit Limit)`;
                }else{
                    $('#totalquotationvalue').css("color","#424242");
                    overLimit = `${window.creditLimit - (window.currentCredit + quotTotal)}`;
                }

                let statementLink= `
			<form action="/sahamid/reports/balance/custstatement/../../../customerstatement.php" method="post" target="_blank">
				<input type="hidden" name="FormID" value="${window.formID}">
				<input type="hidden" name="cust" value="${window.debtorno}">
				<input type="submit" value="Customer Statement" class="btn-info" style="padding: 8px; border:1px #424242 solid;">
			</form>
		`;
                let html="";
                html+=`<center><table border="2" style="font-size: large;"><tr><td> &nbsp;Total Outstanding &nbsp;</td><td>&nbsp; Document Total&nbsp; </td><td> &nbsp;Credit Remaining &nbsp;</td></tr>` +
                    `<tr><td>${(Math.round(window.totalOutstanding).toLocaleString())}</td><td>${(Math.round(quotTotal).toLocaleString())}</td><td>${(overLimit)}</td></tr></table></center><br/>`;
                html+=`<center>${(statementLink)}</center>`;
                //	$('#totalquotationvalue').html(`<table>Total Outstanding (${(Math.round(window.totalOutstanding).toLocaleString())}) Document Total: `+Math.round(quotTotal).toLocaleString()+overLimit.toLocaleString());
                $('#totalquotationvalue').html(html);


            }

		</script>

	</body>
</html>