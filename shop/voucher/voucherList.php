<?php

	$PathPrefix = "../../";
	include("../../includes/session.inc");
	$type=$_GET['type'];
	
	if($type==604 && !userHasPermission($db,"list_receipt_voucher")){
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}
    if($type==605 && !userHasPermission($db,"list_payment_voucher")){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }


?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>List Vouchers</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/select2/select2.css" />
		<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


		<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>
		<script>
			var datatable = null;
		</script>
		<style>
			.dataTables_filter label{
				width: 100% !important;
			}

			.dataTables_filter input{
			    border: 1px #ccc solid;
    			border-radius: 5px;
			}

			th{
				text-align: center;
			}


		</style>

	</head>
	<body>

		<section class="body">
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
			</header>
            <section class="content-header">
                <div class="col-md-12">
                    <h1>Voucher List</h1>
                </div>
                <label>From Date</label>
                <input type="date" class="date fromDate">
                <label>To Date</label>
                <input type="date" class="date toDate">


         <?php       if (userHasPermission($db, "executive_listing")) { ?>
                <label style="margin-top: 15px;">Select Users </label>
                <select name="salesperson" class="salesperson" id="salesperson" multiple>
                    <?php
                    $sql = "SELECT www_users.realname FROM www_users";
                    $ErrMsg = _('The stock categories could not be retrieved because');
                    $DbgMsg = _('The SQL used to retrieve stock categories and failed was');
                    $result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
                    echo '<option value="">Select salesman</option>';
                    while ($myrow = DB_fetch_array($result)) {

                        echo '<option value="' . "'" . $myrow['realname'] . "'" . '">' . $myrow['realname'] . '</option>';

                    }
                    ?>
                    <?php
                    }
                    ?>
                </select>
                <button class="btn btn-success date searchData">Search</button>
            </section>

            <div class="col-md-12" style="text-align: center; margin-top: 20px; margin-bottom: 30px;">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
							<th>SR#</th>
	      					<th>Voucher #</th>
	      					<th>Party ID</th>
	      					<th>Name</th>
	      					<th>Date Created</th>
	      					<th>Amount</th>
                            <th>Salesman</th>
                            <th>Created By</th>
                            <th>Attachment</th>
                            <th>Booked</th>
	      					<th>Print</th>
                            <th>Duplicate</th>

	      				</tr>
	      			</thead>
	      			<tbody>
	      				
	      			</tbody>
	      		</table>
	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index: 20">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>
		</section>

		<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
        <script src="../../quotation/assets/vendor/select2/select2.js"></script>
		<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../quotation/assets/javascripts/theme.js"></script>
		<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>


	</body>
	<script>

		(function( $ ) {
            let salesmanListSelect = $("#salesperson").select2({ width: 'element' });

			'use strict';


			var datatableInit = function() {

				datatable = $('#datatable').DataTable({

					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Searching..."
				    },
				});

				$('#datatable_length')
					.find("label")
					.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Voucher List </h3>");

			};

			$(function() {


				datatableInit();
				$("tbody tr td").html("Choose Filters");
				

			});
            $(".searchData").on("click", function(){
                let salesperson = $("#salesperson").val();
                console.log(salesperson);
                let from  = $(".fromDate").val();
                let to  = $(".toDate").val();
                let FormID = '<?php echo $_SESSION['FormID']; ?>';
                $("tbody tr td").html("Loading...");
                //
                $.post("api/voucherList.php?type=<?php echo $type?>",{salesperson,from,to,FormID},function(res, status){
                    res = JSON.parse(res);
                    datatable.clear().draw();
                    datatable.rows.add(res).draw();
                });
            });


        }).apply( this, [ jQuery ]);
        $('#datatable').on('change','.booked',function(){
            let orderno = $(this).attr('data-orderno');
            let value = $(this).val();
            $.post("api/voucherListUpdate.php",{orderno,value,name:'booked'},function(data, status){
                console.log("Data: " + data + "\nStatus: " + status);
            });
        });
        $('#datatable').on('change','.booked',function(){
            let orderno = $(this).attr('data-orderno');
            let value = $(this).val();
            $.post("api/voucherListUpdate.php",{orderno,value,name:'booked'},function(data, status){
                console.log("Data: " + data + "\nStatus: " + status);
            });
        });



        $('#datatable').on('click','.uploadFile',function(){
                var ref=$(this);
                var orderno = $(this).attr('data-orderno');
                var fd = new FormData();


                var files = $('#attachFile'+orderno)[0].files[0];

                fd.append('voucher', files);
                fd.append('orderno',orderno);

                $.ajax({
                    url: 'api/uploadFile.php',
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(res){
                        if(res){
                            ref.parent().html(res);
                        }
                        else{
                            alert('file not uploaded');
                        }
                    },
                });

            });



    </script>
</html>
