<?php 
	$AllowAnyone = true;

	$PathPrefix='../../';
	include_once('../../includes/session.inc');
	include_once('../../includes/SQL_CommonFunctions.inc');
	$SQL = "SELECT salesmancode,salesmanname FROM salesman";
	$salesman = mysqli_query($db, $SQL);
	
?>

<!DOCTYPE html>
<html>
<head>

	<title>SalesPersonHistory</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
	<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
	<link rel="stylesheet" href="../../v2/assets/datatables/datatables.min.css" />
	<link rel="stylesheet" href="../../v2/assets/datatables/buttons.datatables.min.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
	<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>

	<style>
		@media print {
		  	.nonprint, .hidden-print{
		  		display: none;
		  	}
		}
	</style>

</head>
<body>
	
	<h1 style="text-align: center;">Salesperson History</h1>	
	<div class="container"tyle="text-align: center;" >
	
	</div>
	<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
	<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../quotation/assets/javascripts/theme.js"></script>
	<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>
	<script src="../../v2/assets/datatables/jquery.dataTables.min.js"></script>
	<script src="../../v2/assets/datatables/dataTables.buttons.min.js"></script>
	<script src="../../v2/assets/datatables/buttons.html5.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>
	<form action="salesPersonHistoryRedo.php" method="post">
	<div class="table">
	<center><table>
		<tr style="background:#e2f5ff">
			<td style="text-align:center" colspan=2><h4 style="margin:0px">Sales Person's History</h4></td>
		</tr>
		<tr>
			<td>History Between</td>
			<td> <input type="date" name="startdate" required> AND <input type="date"  name="enddate" required></td>
		</tr>
		
		<tr>
			<td>Select Sales Person
			<td> 
			<select name="salesperson" >
	      					
	      					<?php foreach($salesman as $salesperson){
								echo '<option value="'.$salesperson['salesmancode'].'">'.$salesperson['salesmanname'].'</option>';
							} ?>
	      				</select>
	      	</td>
		</tr>
		<tr>
			<td align="center" colspan="2"><input type="submit" class="btn" style="display:inline-block; width:100%; background:#34a7e8;"></td>
		</tr>
	</table></center>
	</div>	


	</form>	

	
</body>
</html>
