<?php

	$PathPrefix = "../../../";
	include("../../../includes/session.inc");
	
	if(!userHasPermission($db,"mpi_weekly_payments")){
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>List Inward Bazar Parchi</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/select2/select2.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
        <link rel="stylesheet" href="assets/searchSelect.css" />
        <link href='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css'>




        <script src="../../../quotation/assets/vendor/modernizr/modernizr.js"></script>
		<script>
			var datatable = null;
		</script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        <script src='https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
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
						<a href="<?php echo $RootPath; ?>/../../../index.php" style="color: white; text-decoration: none;">Main Menu</a>
						<a class="bold" href="<?php echo $RootPath; ?>/../../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
					</span>
				</span>
			</header>
            <div style="font-size: 18px;">
            <div class="col-md-12">
                <h2>Market Weekly Projections</h2>
            </div>
            <div class="col-md-12">
                <h3>Search Filters</h3>
            </div>
            <div class="col-md-12">

                <div class="col-md-4">
                    <div class="col-md-2">From</div>
                    <div  class="col-md-2"><input id="from" type="date"> </div>
                </div>
                <div class="col-md-4">
                    <div class="col-md-2">To</div>
                    <div  class="col-md-2"><input id="to" type="date"> </div>
                </div>
                               <div><br/>&nbsp;&nbsp;<br/></div>
            </div>

                <div class="col-md-4">
                    <div class="col-md-3"></div>
                    <div class="col-md-4"><button class="btn btn-primary filtercase">Search</button></div>

                </div>
            </div>
            </div>
	      	<div class="col-md-12" style="text-align: center; margin-top: 20px; margin-bottom: 30px;">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">

                </table>
	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index: 20">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>
		</section>

		<script src="../../../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../../../quotation/assets/javascripts/theme.js"></script>
		<script src="../../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
		<script src="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="../../../media/js/dataTables.buttons.js"></script>
		<script src="../../../media/js/buttons.html5.js"></script>
        <script src="assets/searchSelect.js"></script>


    </body>
	<script>
        var datatableInit = function() {

            datatable = $('#datatable').DataTable({
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                },
                buttons: [
                    {
                        extend: 'csv',
                    }
                ],
            });


        };
        $(document).ready(function(){

            $(".filtercase").on("click",function (e) {
                let filter = "filters=yes";

                let from = $("#from").val().trim();

                if(from != ""){
                    filter += "&from="+from;
                }
                let to = $("#to").val().trim();

                if(to != ""){
                    filter += "&to="+to;
                }
                let iHtml="<?php echo "<tr><td>test cell";?>"+filter+"<?php echo"</td></tr>"?>";

                $('#datatable').html(iHtml);


                $.get("api/mpiweeklypayments.php?"+filter,function (res,status) {

                    datatable.rows.add(JSON.parse(res)).draw(false);
                })

            });
            datatableInit();
            $("thead").html("");
            $("tbody tr td").html("Apply Filters And Search");



        });
        datatable.clear().draw();

        $("tbody tr td").html("Searching...");
        /*$(".filtercase").on("click",function (e) {
            $.get("api/mpiweeklypayments.php",function (res,status) {
                datatable.rows.add(JSON.parse(res)).draw(false);
            })

        });
*/


	</script>
</html>
