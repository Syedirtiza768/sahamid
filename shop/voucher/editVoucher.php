<?php

	$PathPrefix = "../../";
	include("../../includes/session.inc");

    if($_GET['json']==1)
    {
       $id=$_GET['orderno'];
       $SQL = "SELECT * FROM voucher WHERE id=$id";
       $res = mysqli_query($db, $SQL);
       $data = mysqli_fetch_assoc($res);
       echo json_encode($data);
       return;

    }


    $type=$_GET['type'];
    if($type=='rv' && !userHasPermission($db,"edit_receipt_voucher")){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    if($type=='pv' && !userHasPermission($db,"edit_receipt_voucher")){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }

	if ($_GET['type']=='rv') {
        $SQL = "SELECT custbranch.branchcode as debtorno,custbranch.brname as name 
			FROM custbranch
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = custbranch.debtorno
			WHERE debtorsmaster.dba='SA HAMID AND COMPANY'
			OR debtorsmaster.dba='SAH'";
        $parties = mysqli_query($db, $SQL);

        $SQL = "SELECT * FROM dba";
        $dba = mysqli_query($db, $SQL);

        $SQL = "SELECT * FROM debtortype";
        $debtortype = mysqli_query($db, $SQL);

        $SQL = "SELECT salesmancode, salesmanname FROM salesman";
        $salesman = mysqli_query($db, $SQL);
    }
	if ($_GET['type']=='pv') {
        $SQL = "SELECT * FROM shop_vendors";
        $parties = mysqli_query($db, $SQL);

    }
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
			.color-white{
				color: white;
			}
			.input-info{
				width: 100%;
				border: 1px solid #424242;
				border-radius: 7px;
			}
			#partylist, #dbalist, #debtortype, #salesman{
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

               <?php if ($_GET['type']=='rv') {
	      		echo'<i class="fa fa-arrow-right" aria-hidden="true"></i> RV';
	      		}
               if ($_GET['type']=='pv') {
                   echo'<i class="fa fa-arrow-left" aria-hidden="true"></i> PV';
               }
	      		?>
	      	</h3>

	      	<div class="col-md-12" style="margin-bottom: 15px;">
	      		<div class="col-md-12" style="border: 2px solid #424242;">
	      			<div class="row" style="background-color: #424242; margin-bottom: 15px; padding-bottom: 15px">
	      				<div class="col-md-12" align="center">
	      					<div class="row">
	      						<div class="col-md-4">
                                  <?php  if ($_GET['type']=='pv') {?>
                                    <h5 class="color-white">Select Vendor:</h5>
                                    <select id="partylist">
                                        <option value="">Select Vendor</option>
                                        <option value="addNew">Add New</option>
                                        <?php while($row = mysqli_fetch_assoc($parties)){ ?>

                                            <option value="<?php echo $row['pid']; ?>"><?php echo $row['name']; ?></option>


                                        <?php }echo "</select>";} ?>

                                        <?php  if ($_GET['type']=='rv') {?>
				      				        <h5 class="color-white">Select Customer:</h5>
					      			        <select id="partylist">
					      				    <option value="">Select Customer</option>

					      				    <option value="addNew">Add New</option>

					      				    <?php while($row = mysqli_fetch_assoc($parties)){ ?>
										    <option value="<?php echo $row['pid']; ?>">
											<?php echo $row['name']; ?>
										    </option>
                                            <?php }echo "</select>";} ?>
				      			</div>
                                <?php  if ($_GET['type']=='rv') {?>
				      			<div class="col-md-4">
				      				<h5 class="color-white">Customer Name:</h5>
				      				<input type="text" class="input-info" id="partyName" disabled>
					      		</div>
					      		<div class="col-md-4">
				      				<h5 class="color-white">Customer Code:</h5>
				      				<input type="text" class="input-info" id="pid" disabled>
					      		</div>
	      					</div>
	      					<div class="row">
				      			<div class="col-md-4">
				      				<h5 class="color-white">DBA:</h5>
				      				<select id="dba" disabled>
					      				<option value="">Select DBA</option>
					      				<option value="SAH">SAH</option>
					      				<option value="SA HAMID AND COMPANY">SA HAMID AND COMPANY</option>
					      			</select>
					      		</div>
					      		<div class="col-md-4">
				      				<h5 class="color-white">Customer Type:</h5>
				      				<select id="partyType" disabled>
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
				      				<input type="text" class="input-info" id="address1" disabled>
				      			</div>
				      			<div class="col-md-4">
				      				<h5 class="color-white">Address Line 2:</h5>
				      				<input type="text" class="input-info" id="address2" disabled>
				      			</div>
				      			<div class="col-md-4">
				      				<h5 class="color-white">Address Line 3:</h5>
				      				<input type="text" class="input-info" id="address3" disabled>
				      			</div>
	      					</div>


                        <?php } ?>


                        <?php  if ($_GET['type']=='pv') {?>
                        <div class="col-md-4">
                            <h5 class="color-white">Vendor Name:</h5>
                            <input type="text" class="input-info" id="partyName" disabled>
                        </div>
                        <div class="col-md-4">
                            <h5 class="color-white">Vendor Code:</h5>
                            <input type="text" class="input-info" id="pid" disabled>
                        </div>





                <?php } ?>


					<div class="col-md-12" style="margin-bottom: 15px; background-color: #424242;">
                        <h1>Voucher Details</h1>
						<select id="instrumentType">
                                    <option value="">Instrument Type</option>
                                    <option>Cash</option>
                                    <option>DD</option>
                                    <option>Pay Order</option>
                                    <option>Cheque</option>
                                    </select>

                            <input name="" type="text"  placeholder="Instrument No" value="" id="instrumentNo" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; width:30%; text-align:center">
                            <input name="" type="date"  placeholder="Instrument Date" value="" id="instrumentDate" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; width:30%; text-align:center">





                    </div>
                    <div class="col-md-12" style="margin-bottom: 15px; background-color: #424242;">

                        <input name="" type="number"  placeholder="Amount Paid" value="" id="amount" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 35%; padding:5px; text-align:center">
                        <input name="" type="text"  placeholder="Customer Reference" value="" id="ref" style="color: #424242; border:1px solid #424242; border-radius: 7px; padding:5px; width:35%; text-align:center">


                    </div>
                    <div class="col-md-12" style="margin-bottom: 15px; background-color: #424242;">


                        <textarea id="description" style="color: #424242; border:1px solid #424242; border-radius: 7px; padding:5px;text-align: center;"
                                  placeholder="Description" cols="153" rows="10"></textarea>


                    </div>

					<button class="btn btn-success" id="proceed"><h4>Save</h4></button>


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
            let orderno = "<?php echo $_GET['orderno']?>";

			var partylistSelect = null;

			$(document).ready(function(){


				partylistSelect = $("#partylist").select2();
                $.get("editVoucher.php?json=1&orderno="+orderno, function(res, status){
                    res = JSON.parse(res);

                    if(res.status != "error"){


                        $("#pid").val(res.branchcode);
                        $("#salesman").val(res.salesman);
                        $("#dba").val(res.dba);
                        $("#partyType").val(res.typeid);
                        $("#address1").val(res.braddress1);
                        $("#address2").val(res.braddress2);
                        $("#address3").val(res.braddress3);
                        $("#amount").val(res.amount);
                        $("#partyName").val(res.partyname);
                        $("#partyType").val(res.partytype);
                        $("#ref").val(res.ref);
                        $("#description").val(res.description);
                        $("#instrumentType").val(res.instrumentType);
                        $("#instrumentNo").val(res.instrumentNo);
                        $("#instrumentDate").val(res.instrumentDate);
                        $("#pid").val(res.pid);
                        $("#dba").val(res.dba);
                        $("#salesman").val(res.salesman);
                        $("#address1").val(res.address1);
                        $("#address2").val(res.address2);
                        $("#address3").val(res.address3);


                    }else{

                        swal("Error",res.message,"error");

                    }
                });


			});








			$("#proceed").on("click", function(){

				let reff = $(this);
				reff.prop("disabled",true);




				if($("#partylist").val() == "addNew"){
					
					if($("#partyName").val().trim() == ""){

						swal("Error","Enter New Party Name","error");
						reff.prop("disabled",false);
						return;

					}






					/*party['type'] = "new";

					party["name"] 		= $("#partyName").val();
                    party["salesman"] 	= $("#salesman").val();
                    party["dba"] 		= $("#dbalist").val();
                    party["ctype"] 	= $("#debtortype").val();
                    party["address1"] 	= $("#address1").val();
                    party["address2"] 	= $("#address2").val();
                    party["address3"] 	= $("#address3").val();*/

				}/*else{

					party['type'] = "old";
					party['branchcode'] = $("#partylist").val();

				}*/


				let pass = true;
				let message = "";



				if(!pass){
					swal("Error",message,"error");
					ref.prop("disabled",false);
					return;
				}



                let amount=$("#amount").val();
                let partyName=$("#partyName").val();
                let partyType=$("#partyType").val();
                let ref=$("#ref").val();
                let description=$("#description").val();
                let instrumentType=$("#instrumentType").val();
                let instrumentNo=$("#instrumentNo").val();
                let instrumentDate=$("#instrumentDate").val();
                let pid=$("#pid").val();
                let dba=$("#dba").val();
                let salesman=$("#salesman").val();
                let address1=$("#address1").val();
                let address2=$("#address2").val();
                let address3=$("#address3").val();


                updateBill(amount,ref,description,instrumentType,instrumentNo,instrumentDate,pid,partyName,partyType,
                    dba,salesman,address1,address2,address3);

			});

			$("#partylist").on("change", function(){

				let val = $(this).val();

				if(val == ""){
					
					$("#partyName").val("");
                    $("#partyType").val("");
					$("#partycode").val("");
					$("#salesman").val("");
					$("#dba").val("");
					$("#debtortype").val("");
					$("#address1").val("");
					$("#address2").val("");
					$("#address3").val("");

					$("#partyName").prop("disabled", true);
                    $("#partyType").prop("disabled", true);
					$("#salesman").prop("disabled", true);
					$("#dba").prop("disabled", true);
					$("#debtortype").prop("disabled", true);
					$("#address1").prop("disabled", true);
					$("#address2").prop("disabled", true);
					$("#address3").prop("disabled", true);

					return;

				}

				if(val == "addNew"){

					$("#partyName").val("");
                    $("#partyType").val("");
					$("#partycode").val("");
					$("#salesman").val("");
					$("#dba").val("");
					$("#debtortype").val("");
					$("#address1").val("");
					$("#address2").val("");
					$("#address3").val("");

					$("#partyName").prop("disabled", false);
                    $("#partyType").prop("disabled", false);
					$("#salesman").prop("disabled", false);
					$("#dba").prop("disabled", false);
					$("#debtortype").prop("disabled", false);
					$("#address1").prop("disabled", false);
					$("#address2").prop("disabled", false);
					$("#address3").prop("disabled", false);

					return;

				}else{

					$("#partyName").prop("disabled", true);
                    $("#partyType").prop("disabled", true);
					$("#salesman").prop("disabled", true);
					$("#dba").prop("disabled", true);
					$("#debtortype").prop("disabled", true);
					$("#address1").prop("disabled", true);
					$("#address2").prop("disabled", true);
					$("#address3").prop("disabled", true);

				}

				$.get("api/getCustomerData.php?branchCode="+val+"&type="+vouchertype, function(res, status){
					res = JSON.parse(res);
					
					if(res.status != "error"){

						$("#partyName").val(res.data.brname);
						$("#pid").val(res.data.branchcode);
						$("#salesman").val(res.data.salesman);
						$("#dba").val(res.data.dba);
						$("#partyType").val(res.data.typeid);
						$("#address1").val(res.data.braddress1);
						$("#address2").val(res.data.braddress2);
						$("#address3").val(res.data.braddress3);

					}else{

						swal("Error",res.message,"error");

					}
				});

			});





            function updateBill(amount,ref,description,instrumentType,instrumentNo,instrumentDate,pid,partyName,partyType,
                                  dba,salesman,address1,address2,address3){
                let orderno = "<?php echo $_GET['orderno']?>";

                $.post("api/updateBill.php?orderno="+orderno,{


                    amount:amount,
                    ref:ref,
                    description:description,
                    instrumentType:instrumentType,
                    instrumentNo:instrumentNo,
                    instrumentDate:instrumentDate,
                    pid:pid,
                    partyName:partyName,
                    partyType:partyType,
                    dba:dba,
                    salesman:salesman,
                    address1:address1,
                    address2:address2,
                    address3:address3,
                    FormID:'<?php echo $_SESSION['FormID']; ?>'
                }, function(res){
                    res = JSON.parse(res);
                    if(res.status == "success"){
                        window.location = "voucherPrint.php?orderno="+orderno;
                    }
                });

            }



			$(document.body).on("keypress","textarea",function(event) {
			   if (event.which == 13) {
			      event.preventDefault();
			      var s = $(this).val();
			      $(this).val(s+"\n");
			   }
			})






		</script>

	</body>
</html>