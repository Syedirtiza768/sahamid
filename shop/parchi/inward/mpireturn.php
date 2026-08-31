<?php 

	$PathPrefix='../../../';
	
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');


    if(!userHasPermission($db, 'mpi_return')) {


        header("Location: /sahamid/v2/reportLinks.php");
        exit;
    }

	$SQL = "SELECT *,bazar_parchi.transno as mpino FROM bazar_parchi
			WHERE bazar_parchi.type=601
			AND bazar_parchi.inprogress=0
			AND bazar_parchi.settled=0
			AND bazar_parchi.transno
			NOT IN 
			(
			  SELECT supptrans.transno FROM suppallocs INNER JOIN supptrans ON supptrans.id = suppallocs.transid_allocto 
			  WHERE supptrans.type=601
			)
			AND bazar_parchi.transno
			IN 
			(
			  SELECT gltrans.typeno FROM gltrans  
			  WHERE gltrans.type=601
			)";

	$res = mysqli_query($db, $SQL);

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>S A Hamid ERP</title>
		<meta name="keywords" content="HTML5 Admin Template" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="../../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../../../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="../../../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/select2/select2.css" />
		<link rel="stylesheet" href="../../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />


		<script src="../../../quotation/assets/vendor/modernizr/modernizr.js"></script>
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

			.selected{
				background-color: #acbad4 !important;
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
						<a href="<?php echo $RootPath; ?>/../index.php" style="color: white; text-decoration: none;">Main Menu</a>
						<a class="bold" href="<?php echo $RootPath; ?>/../../../Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
					</span>
				</span>
			</header>

	      	<div class="col-md-12" style="text-align: center; border-bottom: 2px #424242 solid; margin-top: 20px">
	      		<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
	      					<th>mpi No</th>

	      					<th>Action</th>
	      				</tr>
	      			</thead>
	      			<tbody>
	      				<?php while($row = mysqli_fetch_assoc($res)){ ?>

	      					<tr>
	      						<td><?php echo $row['transno']; ?></td>

	      						<td><button id="inv<?php echo $row['mpino']; ?>" class="btn btn-success invreturn">Return mpi</button></td>
	      					</tr>

	      				<?php } ?>
	      			</tbody>
	      		</table>
	      		
	      	</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center">
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


	</body>
	<script>
		
		(function( $ ) {

			'use strict';

			var datatableInit = function() {

				datatable = $('#datatable').DataTable({
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    }
				});

				$('#datatable_length').find("label").html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Return mpi</h3>")

			};

			$(document).ready(function(){
				datatableInit()
			})

		}).apply( this, [ jQuery ]);
		
		$(".invreturn").on("click", function(){
			
			var mpino = $(this).attr("id").split("inv")[1];
			callmpireturn(mpino, $(this));

		});
		
		function callmpireturn(mpino, ref){
			swal({
			  title: "Are you sure?",
			  text: "It will pass reverse entry for the mpi.!",
			  type: "warning",
			  showCancelButton: true,
			  confirmButtonClass: "btn-success",
			  confirmButtonText: "Yes, Reverse mpi!",
			  showLoaderOnConfirm: true,
			  closeOnConfirm: false
			},
			function(){

				$.ajax({
					type: 'GET',
					url: "<?php echo $RootPath; ?>"+"/mpireturnprocess.php?mpino="+mpino,
					dataType: "json",
					success: function(response) {
						
						if(response.status == "success"){
							ref.prop("disabled",true);
							swal("Success","mpi returned Successfully!!!","success");
						}else{
							swal("Error","Failed!!!","error");
						}

					},
					error: function(){
						swal("Error","Something didn't go the right way...","error");
					}
				});
	
			});
			
		}

	</script>
</html>

