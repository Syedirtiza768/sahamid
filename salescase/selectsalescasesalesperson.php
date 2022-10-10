<?php 
	
	$PathPrefix='../';
	include('misc.php');
	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');

	$SQL = "SELECT loccode,locationname FROM locations";
	$loc = mysqli_query($db, $SQL);

	$SQL = "SELECT realname FROM www_users WHERE fullaccess=10";
	$dir = mysqli_query($db, $SQL);

	$SQL = "SELECT companyname FROM dba";
	$dba = mysqli_query($db, $SQL);

?>

<!DOCTYPE html>
<html>
	<head>

		<title>S A Hamid ERP</title>
		
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		
		<link rel="stylesheet" href="../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="../quotation/assets/stylesheets/skins/default.css" />
		<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
		<link rel="stylesheet" href="assets/selectSalescase.css?version=<?php echo generateRandom() ?>" />

		<script src="../quotation/assets/vendor/modernizr/modernizr.js"></script>

		<style>
			.filters-area{
				width: 100%;
				height: auto;
				transition: height .3s;
				position: relative;
				display: flex;
				background: #dcdcdc;
				padding-bottom: 15px;
				border-radius: 7px 0px 7px 7px;
			}
			.filter-button{
				float: right;
				margin-top: 20px;
			}
			.input{
				border: 1px solid #424242;
				border-radius: 7px;
				width: 100%;
				height: 30px;
				padding: 3px;
			}
			.filtercase{
				width: 100%; 
				margin:10px 0;
			}
		</style>

	</head>
	<body style="color:black;">
		<input type="hidden" id="filters" value="<?php echo $_GET['url']; ?>">
		<section class="body">
	      	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
	      		<span style="color:white">
	      			<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> &nbsp;|&nbsp;
	      			<span style="color:#ccc">
	      				<?php echo stripslashes($_SESSION['UsersRealName']); ?>
	          		</span>
	      			<span class="pull-right" style="background:#424242; padding: 0 10px;">
	      				<a 	href="<?php echo $RootPath; ?>/../index.php" 
	      					style="color: white; text-decoration: none;">
	      					Main Menu
	      				</a>
	      				<a 	class="bold" 
	      					href="<?php echo $RootPath; ?>/../Logout.php" 
	      					style="color: white; text-decoration: none; margin-left:20px;">
	      					Logout
	      				</a>
	      			</span>
	      		</span>
	      	</header>


      		<div class="col-md-12" style="margin-bottom: 40px; margin-top: 20px;">
      			<table class="table table-bordered table-striped mb-none" id="datatable">
	      			<thead>
	      				<tr style="background-color: #424242; color: white">
		      				<th class="fit">SNo</th>
		      				<th class="fit">Salescaseref</th>
		      				<th class="fit">Description</th>
		      				<th class="">Client</th>
		      				<th class="fit no-wrap">QNo</th>
							<th class="fit no-wrap">PO Date</th>
		      				<th class="fit no-wrap">Quotation Value</th>
		      				<th class="">Action</th>
		      			</tr>
	      			</thead>
	      			<tfoot>
	      				<tr style="background-color: #424242;" class="asdasa">
	      					<th class="fit">SNo</th>
		      				<th class="fit">Salescaseref</th>
		      				<th class="fit">Description</th>
		      				<th class="">Client</th>
		      				<th class="fit no-wrap">QNo</th>
							<th class="fit no-wrap">PO Date</th>
		      				<th class="fit no-wrap">Quotation Value</th>
		      				<th class="">Action</th>
	      				</tr>
	      			</tfoot>
	      			<tbody>
	      				
	      			</tbody>
	      		</table>
      		</div>

	      	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; padding: 5px; z-index: 99">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>
      	</section>
		<script src="../quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
		<script src="../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
		<script src="../quotation/assets/javascripts/theme.js"></script>
		<script src="../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
		<script src="../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
		<script src="assets/selectSalescaseSalesperson.js?version=<?php echo generateRandom() ?>"></script>
		<script>
			$(".filter-button").on("click", function(){
				let ref = $(this);
				if(ref.attr("data-enabled") == "true"){
					ref.attr("data-enabled","false");
					$(".filters-area").css("height","0px");
					$(".filters-area").addClass("collapse");
				}else{
					ref.attr("data-enabled","true");
					$(".filters-area").css("height","auto");
					$(".filters-area").removeClass("collapse");
				}
			});
		</script>
	</body>
</html>