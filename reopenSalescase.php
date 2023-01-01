<?php 
	
	include('includes/session.inc');
	include('includes/SQL_CommonFunctions.inc');
    if(!userHasPermission($db,"reopen_salescase")){
        header("Location: /sahamid");
        return;
    }


if(isset($_POST['salescaseref'])){

		$salescaseref = trim($_POST['salescaseref']);

		$SQL = "SELECT * FROM salescase WHERE closed=1 AND salescaseref='".$salescaseref."'";
		$res = DB_Query($SQL, $db);

		if(mysqli_num_rows($res) != 1){
			
			$_SESSION['reopensalescasemsg'] = "Salescase is already open or does not exist...";
			header("Location: ".htmlspecialchars($_SERVER["PHP_SELF"]));
			exit;
		
		}

		$SQL = "UPDATE salescase SET closed=0,closingreason='' WHERE salescaseref='".$salescaseref."'";
		$res = DB_Query($SQL, $db);

		if(!$res){

			$_SESSION['reopensalescasemsg'] = "Attempt to open salescase failed...";

		}else{

			$_SESSION['reopensalescasemsg'] = "Salescase successfully opened...";

		}
		
		header("Location: ".htmlspecialchars($_SERVER["PHP_SELF"]));
		exit;

	}

?>
<!DOCTYPE html>
<html>
<head>

	<title>Reopen Salescase</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="stylesheet" href="quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="quotation/assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="quotation/assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="quotation/assets/vendor/sweetalert/sweetalert.css" />
	<link rel="stylesheet" href="quotation/assets/stylesheets/skins/default.css" />
	<link rel="stylesheet" href="quotation/assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />

	<script src="quotation/assets/vendor/modernizr/modernizr.js"></script>

	<style>
		th{
			text-align: center;
		}
		td{
			text-align: center;
		    vertical-align: inherit !important;
		}
		.dataTables_filter label{
			width: 100% !important;
		}

		.dataTables_filter input{
		    border: 1px #ccc solid;
			border-radius: 5px;
		}
		.textLeft{
			text-align: left;
		}
		.dataTables_length select{
			height: auto !important;
		}
	</style>

	<script>
		var datatable = null;
	</script>

</head>
<body>
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

  	<div style="width: 100%; min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; flex-direction: column;">
  		
  		<h3 style="font-family: initial;">Salescaseref</h3>
  		<?php 

  			if(isset($_SESSION['reopensalescasemsg']))
  				echo '<h5 style="font-family: initial;">'.$_SESSION['reopensalescasemsg'].'</h5>';

  		?>
  		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
  			<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
  			<input type="text" name="salescaseref" style="width: 300px; border: 1px solid #424242; border-radius: 6px; padding: 6px; display: block;" placeholder="Enter salescaseref here" required="">
  			<input type="submit" class="btn btn-success" style="width: 300px; margin:10px 0" value="ReOpen Salescase">
  		</form>

  	</div>


  	<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; z-index:150; padding: 5px">
		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	</footer>

	<script src="quotation/assets/vendor/jquery/jquery.js"></script>
	<script src="quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="quotation/assets/javascripts/theme.js"></script>
	<script src="quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
	<script src="quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>

</body>
</html>

<?php 

	if(isset($_SESSION['reopensalescasemsg']))
		unset($_SESSION['reopensalescasemsg']);

?>