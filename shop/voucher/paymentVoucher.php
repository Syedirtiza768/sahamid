<?php

	$PathPrefix = "../../";
	include("../../includes/session.inc");

    $type=$_GET['type'];

    if(!userHasPermission($db,"create_pv")){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    if ($_GET['json'])
    {

    }


        $SQL = "SELECT * FROM shop_vendors";
        $parties = mysqli_query($db, $SQL);
        $SQL = "SELECT bankaccountname,
                        bankaccounts.accountcode,
                        bankaccounts.currcode
                FROM bankaccounts
                INNER JOIN chartmaster
                    ON bankaccounts.accountcode=chartmaster.accountcode
                INNER JOIN bankaccountusers
                    ON bankaccounts.accountcode=bankaccountusers.accountcode
                WHERE bankaccountusers.userid = '" . $_SESSION['UserID'] ."'
                ORDER BY bankaccountname";
        $bankaccounts = mysqli_query($db, $SQL);


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
            body{
                font-size:18px;
            }
            h5{
                font-size:18px;
            }
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

               <?php echo'<i class="fa fa-arrow-left" aria-hidden="true"></i> Payment Voucher';

	      		?>
	      	</h3>

	      	<div class="col-md-12" style="margin-bottom: 15px;">
	      		<div class="col-md-12" style="border: 2px solid #424242;">
	      			<div class="row" style="background-color: #424242; margin-bottom: 15px; padding-bottom: 15px">
	      				<div class="col-md-12" align="center">
	      					<div class="row">
	      						<div class="col-md-4" align="right">

                                    <h5 class="color-white">Select Vendor:</h5>
                                    <select id="partylist">
                                        <option value="">Select Vendor</option>
                                        <?php while($row = mysqli_fetch_assoc($parties)){ ?>

                                            <option value="<?php echo $row['vid']; ?>"><?php echo $row['name']; ?></option>


                                        <?php }echo "</select>"; ?>


				      			</div>




                        <div class="col-md-4">
                            <h5 class="color-white">Vendor Code:</h5>
                            <input type="text" class="input-info" id="pid">
                            <input type="hidden" class="input-info" id="partyName">
                        </div>
                                <div class="col-md-4">
                                    <h5 class="color-white">Bank Account</h5>
                                    <select id="bankaccount">
                                        <option value="">Select Bank Account</option>
                                        <?php while($row = mysqli_fetch_assoc($bankaccounts)){ ?>

                                            <option value="<?php echo $row['accountcode']; ?>"><?php echo $row['bankaccountname']; ?></option>


                                        <?php }echo "</select>"; ?>

                                </div>









					<div class="col-md-12" style="margin-bottom: 15px; background-color: #424242;">
                        <h1>Voucher Details</h1>
						<select id="instrumentType">
                                    <option value="">Instrument Type</option>
                                    <option value="Cash">Cash</option>
                                    <option value="DD">DD</option>
                                    <option value="Pay Order">Pay Order</option>
                                    <option value="Cheque">Cheque</option>
                                    </select>

                            <input name="" type="text"  placeholder="Instrument No" value="" id="instrumentNo" disabled style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; width:30%; text-align:center">
                            <input name="" type="date"  placeholder="Instrument Date" value="" id="instrumentDate" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; width:30%; text-align:center">





                    </div>
                    <div class="col-md-12"  style="margin-bottom: 15px; background-color: #424242;">
                        <div class="row" style="background-color: #424242; margin-bottom: 15px; padding-bottom: 15px" >

                        <div class="col-md-4" align="center">
                        <input name=""  type="number"  placeholder="Amount" value="" id="amount" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; text-align:center">
                        </div>
                        <div class="col-md-4">
                            <input name="" type="text"  placeholder="Narrative" value="" id="narrative" style="color: #424242; border:1px solid #424242; border-radius: 7px; padding:5px; width:100%; text-align:center">
                        </div>
                        <div class="col-md-4">
                            <input name=""  type="text"  placeholder="Customer Reference" value="" id="ref" style="color: #424242; border:1px solid #424242; border-radius: 7px; padding:5px; width:100%; text-align:center">
                        </div>
                        </div>
                    </div>
                                <div class="col-md-12"  style="margin-bottom: 15px; background-color: #424242;">
                                    <div class="row" style="background-color: #424242; margin-bottom: 15px; padding-bottom: 15px" >
                                        <?php if(userHasPermission($db,"advance_payment_voucher")){
                                        ?>
                                        <div class="col-md-6" align="center">

                                            <label class="color-white">Advance Option</label>
                                            <input name=""  type="checkbox"   value="" id="advance" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; text-align:center">
                                        </div>
                                       <?php
                                         }
                                         else{
                                        ?>
                                             <div class="col-md-6" align="center" hidden>

                                                 <label class="color-white">Advance Option</label>
                                                 <input name=""  type="checkbox"   value="" id="advance" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; text-align:center">
                                             </div>
                                         <?php }
                                       ?>
                                        <div class="col-md-6">
                                            <label class="color-white">On Account Allocation</label>
                                            <input name=""  type="checkbox"   value="checked" id="onAccountAlloc" style="color: #424242; border:1px solid #424242; border-radius: 7px; width: 100%; padding:5px; text-align:center">
                                        </div>

                                    </div>
                                </div>
                                <div class="col-md-12" style="margin-bottom: 15px; background-color: #424242;">
                                    <textarea id="description" style="color: #424242; border:1px solid #424242; border-radius: 7px; padding:5px;text-align: center;"
                                        placeholder="Description" cols="100"></textarea>


                                </div>
                                <div class="col-md-12" style="margin-bottom: 15px; background-color: #424242;">



                            <table class="table" id="toBeAllocated"></table>

                            <!--<tr class="color-white">
                                <td colspan="3">Total Allocated: </td>
                                <td colspan="2">Left To Allocate</td>-->




                                    <hr/>
                    </div>

                                <div class="col-md-12"  style="margin-bottom: 15px; background-color: #424242;" align="center">
                                    <div class="row" style="background-color: #424242; margin-bottom: 15px; padding-bottom: 15px" align="center" >

                                        <div class="col-md-4" align="center">
                                            <label class="color-white">Cheque</label><input type="file" id="chequefile" name="chequefile">

                                        </div>
                                        <div class="col-md-4" align="left">
                                            <label class="color-white">Cheque Deposit Receipt</label>
                                            <input type="file" id="cdrfile" name="cdrfile">

                                        </div>
                                        <div class="col-md-4" align="left">
                                            <label class="color-white">Cash Receieve Voucher</label>
                                            <input type="file" id="crvfile" name="crvfile">

                                        </div>
                                    </div>
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

			var partylistSelect = null;

			$(document).ready(function(){


				partylistSelect = $("#partylist").select2();
                $("#partylist").show();
                $("#instrumentType").on("change", function(){

                    if ( $("#instrumentType").val()=="Cash" || $("#instrumentType").val()=="")
                        $("#instrumentNo").prop("disabled",true);
                    else
                        $("#instrumentNo").prop("disabled",false);
                });


                    var start = new Date();

                    $(window).unload(function() {
                        var end = new Date();
                        $.ajax({
                            url: "http://localhost/monitoring/log.php",
                            data: {'timeSpent': end - start},
                            async: false
                        })
                    });


            });








			$("#proceed").on("click", function(){

                let fd = new FormData();
                let files = $('#chequefile')[0].files[0];
                fd.append('chequefile', files);
                files = $('#cdrfile')[0].files[0];
                fd.append('cdrfile', files);
                files = $('#crvfile')[0].files[0];
                fd.append('crvfile', files);

                if($("#partylist").val() == "") {
                    swal("Error", "Select Vendor", "error");

                    return;
                }
                if($("#bankaccount").val() == "") {
                    swal("Error", "Select Bank Account", "error");

                    return;
                }
                if(($("#instrumentType").val() != "Cash")&&($("#instrumentNo").val() == "")) {

                        swal("Error", "Enter Instrument Number", "error");

                        return;
                    }
                    if((($("#instrumentType").val() != "Cash"))&&(document.getElementById("chequefile").files.length == 0 )){
                        swal("Error", "Uplaod Cheque File", "error");
                        return;
                    }



                if($("#instrumentType").val() == "") {
                    swal("Error", "Select Instrument Type", "error");

                    return;
                }
                if($("#instrumentDate").val() == "") {
                    swal("Error", "Select Date", "error");

                    return;
                }
                let number=document.getElementsByClassName("transno");
                let listNumber=[];
                for (let i=0;i<number.length;i++)
                {
                    listNumber[i]=(number[i].innerHTML);

                }
                let id=document.getElementsByClassName("transid");
                let listId=[];
                for (let i=0;i<id.length;i++)
                {
                    listId[i]=(id[i].innerHTML);

                }

                let remaining=document.getElementsByClassName("remaining");
                let listRemaining=[];
                let sumRemaining=0;
                //let flag=1;
                for (let i=0;i<remaining.length;i++)
                {
                    listRemaining[i]=(remaining[i].innerHTML);
                    sumRemaining=parseFloat(sumRemaining)+parseFloat(listRemaining[i]);
                }
                let toBeAllocated=document.getElementsByClassName("amount");
                let listToBeAllocated=[];
                let sumTobeAllocated=0;
                for (let i=0;i<toBeAllocated.length;i++)
                {
                    listToBeAllocated[i]=(toBeAllocated[i].value);
                    sumTobeAllocated=parseFloat(sumTobeAllocated)+parseFloat(listToBeAllocated[i]);
                }
                let flag;
                console.log($("#amount").val());
                console.log("here");
                console.log(sumTobeAllocated);
                for (let i=0;i<remaining.length;i++)
                {

                    if ((parseFloat(listRemaining[i])-parseFloat(listToBeAllocated[i]))==0)
                        continue;

                    if ((parseFloat(listRemaining[i])-parseFloat(listToBeAllocated[i]))>parseFloat('0'))
                    {

                        continue;


                    }
                    flag = 'flag';



                }

				/*let reff = $(this);
				reff.prop("disabled",true);*/

				let party = {};

				if($("#partylist").val() == ""){
					swal("Error","Party Not Selected!!!","error");
					reff.prop("disabled",false);
					return;
				}

                if((parseFloat($("#amount").val()) > parseFloat(sumTobeAllocated))&&(($("#advance").is(":not(:checked)")))){

                    swal("Error", "Amount entered greater than remaining amount", "error");

                    return;
                }
                if(($("#amount").val() < sumTobeAllocated)){

                    swal("Error", "Amount entered smaller than allocated amount", "error");

                    return;
                }

                if(flag=="flag"){
                    swal("Error", "Line amount entered greater than remaining amount", "error");

                    return;
                }




                let pass = true;
				let message = "";



				if(!pass){
					swal("Error",message,"error");
					ref.prop("disabled",false);
					return;
				}

                let type=605;

                let amount=$("#amount").val();
                let partyName=$("#partyName").val();
                let partyType=$("#partyType").val();
                let ref=$("#ref").val();
                let narrative=$("#narrative").val();
                let description=$("#description").val();
                let instrumentType=$("#instrumentType").val();
                let instrumentNo=$("#instrumentNo").val();
                let instrumentDate=$("#instrumentDate").val();
                let pid=$("#pid").val();
                let bankaccount=$("#bankaccount").val();
                let dba=$("#dba").val();
                let salesman=$("#salesman").val();
                let address1=$("#address1").val();
                let address2=$("#address2").val();
                let address3=$("#address3").val();




                let reff = $(this);
                reff.prop("disabled",true);

               generateBill(fd,type,amount,ref,narrative,description,instrumentType,instrumentNo,instrumentDate,pid,bankaccount,partyName,partyType,
                    dba,salesman,address1,address2,address3,listNumber,listId,listRemaining,listToBeAllocated);
           });


			$("#partylist").on("change", function(){
                var end = new Date();
                $.ajax({
                    url: "http://localhost/monitoring/log.php",
                    data: {'timeSpent': end},
                    async: false
                });
                $('#onAccountAlloc').prop("checked",false);
                $('#amount').val(0);


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



				$.get("api/extended/getCustomerData.php?branchCode="+val+"&type=pv", function(res, status){
					res = JSON.parse(res);
					
					if(res.status != "error"){




						$("#partyName").val(res.data.partyinfo.brname);
						$("#pid").val(res.data.partyinfo.branchcode);
                        $("#toBeAllocated").html("<tr style='color:lightgoldenrodyellow; font-size:18px;'><td><b>Total Due: </b></td><td id='totalDue'><b>PKR "+parseFloat(res.data.totalDue).toLocaleString()+"</b></td></tr>");
                        $("#toBeAllocated").append("<tr style='color:lightgoldenrodyellow; font-size:18px;'>" +
                            "<td><b>Un Allocated: </b></td><td id='advanceDisplay'></td></tr>"
                        );


                        $("#toBeAllocated").append("<tr style='color:lightgoldenrodyellow; font-size:18px;'><td>Type</td><td hidden>id</td><td>Number</td><td>Date</td><td>Amount (PKR)</td><td></td><td>Being Allocated</td></tr>");
                        $.each(res.data.transinfo,function () {
                            $("#toBeAllocated").append("<tr class='allocLine' style='color:lightgoldenrodyellow; font-weight:bolder;font-size:18px;' align='left'><td>"+this.typename+"</td><td class='transid' hidden>"+this.id+"</td><td class='transno' align='left'>"+this.transno+"</td><td>"+this.trandate+"</td><td class='remaining'>"+Math.round(parseFloat(this.total))+"</td><td></td><td style='text-color:black;'><input class='amount' style='background-color:#424242; color:lightgoldenrodyellow;' type='text' value=0> </td></tr>");
                        })




					}else{

						swal("Error",res.message,"error");

					}
				});

			});





            function generateBill(fd,type,amount,ref,narrative,description,instrumentType,instrumentNo,instrumentDate,pid,bankaccount,partyName,partyType,
                                  dba,salesman,address1,address2,address3,listNumber,listId,listRemaining,listToBeAllocated){
                for (let i=0;i<listNumber.length;i++)
                    fd.append('listNumber[]', listNumber[i]);

                for (let i=0;i<listRemaining.length;i++)
                    fd.append('listRemaining[]', listRemaining[i]);
                for (let i=0;i<listToBeAllocated.length;i++)
                    fd.append('listToBeAllocated[]', listToBeAllocated[i]);
                for (let i=0;i<listId.length;i++)
                    fd.append('listId[]', listId[i]);



                fd.append('type', type);
                fd.append('ref', ref);
                fd.append('narrative', narrative);
                fd.append('description', description);
                fd.append('instrumentType', instrumentType);
                fd.append('instrumentNo', instrumentNo);
                fd.append('instrumentDate', instrumentDate);
                fd.append('pid', pid);
                fd.append('bankaccount', bankaccount);
                fd.append('partyName', partyName);
                fd.append('partyType', partyType);
                fd.append('dba', dba);
                fd.append('salesman', salesman);
                fd.append('address1', address1);
                fd.append('address2', address2);
                fd.append('address3', address3);
                fd.append('FormID', '<?php echo $_SESSION['FormID']; ?>');
                fd.append('amount', amount);
               $.ajax({
                    url: 'api/extended/generateBill.php',
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(res){
                        res = JSON.parse(res);
                        if(res.status == "success"){
                            window.location = "paymentVoucherPrint.php?orderno="+res.voucherID+"&supptrans="+res.supptrans;
                        }
                        else{
                            alert('error');
                        }
                    },
                });


            }

            $('#onAccountAlloc').on("change",function () {
                let amt=$("#amount").val();
                let remaining=amt;

                $(".allocLine").each(function () {
                    $(this).find(".amount").val(0);
                });
                if($('#onAccountAlloc').is(":checked"))
                {
                    $(".allocLine").each(function () {


                        if ((remaining)>=parseFloat($(this).find(".remaining").html()))
                            $(this).find(".amount").val($(this).find(".remaining").html());
                        if ((remaining)<parseFloat($(this).find(".remaining").html())&&(remaining>0))
                            $(this).find(".amount").val(remaining);
                        if ((remaining)===0) {
                            $(this).find(".amount").val(0);

                        }
                        remaining=remaining-parseFloat($(this).find(".remaining").html());

                    })

                }
                else
                {
                    $(".allocLine").each(function () {
                        $(this).find(".amount").val(0);
                    });

                }
                let toBeAllocated=document.getElementsByClassName("amount");
                let listToBeAllocated=[];
                let sumTobeAllocated=0;
                for (let i=0;i<toBeAllocated.length;i++)
                {
                    listToBeAllocated[i]=(toBeAllocated[i].value);
                    sumTobeAllocated=parseFloat(sumTobeAllocated)+parseFloat(listToBeAllocated[i]);
                }
                let advanceDisplay = parseFloat(amt) - parseFloat(sumTobeAllocated);

                    $('#advanceDisplay').html(advanceDisplay);
                  

            });
            $('#amount').on("keyup",function () {


                let amt=$("#amount").val();
                let remaining=amt;

                $(".allocLine").each(function () {
                    $(this).find(".amount").val(0);
                });

                if($('#onAccountAlloc').is(":checked"))
                {
                    $(".allocLine").each(function () {


                        if ((remaining)>=parseFloat($(this).find(".remaining").html()))
                            $(this).find(".amount").val($(this).find(".remaining").html());
                        if ((remaining)<parseFloat($(this).find(".remaining").html())&&(remaining>0))
                            $(this).find(".amount").val(remaining);
                        if ((remaining)===0) {
                            $(this).find(".amount").val(0);

                        }
                        remaining=remaining-parseFloat($(this).find(".remaining").html());

                    })

                }
                else
                {
                    $(".allocLine").each(function () {
                        $(this).find(".amount").val(0);
                    });

                }
                let toBeAllocated=document.getElementsByClassName("amount");
                let listToBeAllocated=[];
                let sumTobeAllocated=0;
                for (let i=0;i<toBeAllocated.length;i++)
                {
                    listToBeAllocated[i]=(toBeAllocated[i].value);
                    sumTobeAllocated=parseFloat(sumTobeAllocated)+parseFloat(listToBeAllocated[i]);
                }
                let advanceDisplay = parseFloat(amt) - parseFloat(sumTobeAllocated);


                    $('#advanceDisplay').html(advanceDisplay);
              

            });


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