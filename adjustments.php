<?php 

	include('includes/session.inc');	
	include('includes/SQL_CommonFunctions.inc');
	include('invoice/misc.php');

	if(!isset($_GET['DebtorNo'])){
		echo "Debtor";
		return;
	}

	$debtorno = $_GET['DebtorNo'];
	$db = createDBConnection();

	$SQL = "SELECT * FROM debtorsmaster WHERE debtorno='".$debtorno."'";
	$res = mysqli_query($db, $SQL);
	
	if(mysqli_num_rows($res) != 1){
		echo "Wrong";
		return;
	}

	$debtor = mysqli_fetch_assoc($res);

	$SQL = "SELECT debtortrans.id,
					debtortrans.GSTwithhold,
					debtortrans.WHT,
					debtortrans.WHTamt,
					debtortrans.GSTamt,
					debtortrans.GSTtotalamt,
					invoice.shopinvoiceno,
					typename,
					transno,
					trandate,
					rate,
					ovamount+ovgst+ovfreight+ovdiscount as total,
					diffonexch,
					alloc
			FROM debtortrans 
			INNER JOIN systypes ON debtortrans.type = systypes.typeid
			INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
			WHERE debtortrans.settled=0
			AND debtortrans.type = 10
			AND debtortrans.debtorno='" . $_GET['DebtorNo'] . "'
			ORDER BY debtortrans.trandate";

	$res = mysqli_query($db, $SQL);

	$data = [];

	while($row = mysqli_fetch_assoc($res)){
		$data[$row['id']] = $row;
	}

	$SQL= "SELECT debtortrans.id,
					debtortrans.GSTwithhold,
					debtortrans.WHT,
					debtortrans.WHTamt,
					debtortrans.GSTamt,
					debtortrans.GSTtotalamt,
					custallocns.WHT as cWHT,
					custallocns.GSTwithhold as cGSTwithhold,
					typename,
					transno,
					trandate,
					rate,
					ovamount+ovgst+ovfreight+ovdiscount AS total,
					diffonexch,
					debtortrans.alloc-custallocns.amt AS prevallocs,
					amt,
					custallocns.id AS allocid
			FROM debtortrans INNER JOIN systypes
			ON debtortrans.type = systypes.typeid
			INNER JOIN custallocns
			ON debtortrans.id=custallocns.transid_allocto
			WHERE debtortrans.settled=0
			AND debtortrans.type = 10
			AND debtorno='" . $_GET['DebtorNo'] . "'
			ORDER BY debtortrans.trandate";
	
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){
		$data[$row['id']]['GSTwithhold'] 	= $row['GSTwithhold'];
		$data[$row['id']]['WHT'] 			= $row['WHT'];
		$data[$row['id']]['WHTamt'] 		= $row['WHTamt'];
		$data[$row['id']]['GSTamt'] 		= $row['GSTamt'];
		$data[$row['id']]['cWHT'] 			= $row['cWHT'];
		$data[$row['id']]['cGSTwithhold'] 	= $row['cGSTwithhold'];
		$data[$row['id']]['typename'] 		= $row['typename'];
		$data[$row['id']]['transno'] 		= $row['transno'];
		$data[$row['id']]['trandate'] 		= $row['trandate'];
		$data[$row['id']]['rate'] 			= $row['rate'];
		$data[$row['id']]['total'] 			= $row['total'];
		$data[$row['id']]['diffonexch'] 	= $row['diffonexch'];
		$data[$row['id']]['prevallocs'] 	= $row['prevallocs'];
		$data[$row['id']]['allocid'] 		= $row['allocid'];
		$data[$row['id']]['amt'] 			= $row['amt'];
		$data[$row['id']]['GSTtotalamt'] 	= $row['GSTtotalamt'];
	}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">

		<title>S A Hamid ERP</title>
		<meta name="keywords" content="HTML5 Admin Template" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

		<link rel="stylesheet" href="quotation/assets/vendor/bootstrap/css/bootstrap.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/font-awesome/css/font-awesome.css" />
		<link rel="stylesheet" href="quotation/assets/stylesheets/theme.css" />
		<link rel="stylesheet" href="quotation/assets/vendor/sweetalert/sweetalert.css" />
		<link rel="stylesheet" href="quotation/assets/stylesheets/skins/default.css" />

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
						<a href="<?php echo $RootPath; ?>/index.php" style="color: white; text-decoration: none;">Main Menu</a>
						<a class="bold" href="<?php echo $RootPath; ?>/Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
					</span>
				</span>
			</header>

			<h2 style="text-align: center; font-variant: petite-caps;">Leftover Adjustments</h2>
			<h4 style="text-align: center;">For</h4>
			<h3 style="text-align: center;"><?php echo $debtor['name']; ?></h3>
			<div class="col-md-12" style="margin-bottom: 60px">
				<form method="POST" action="<?php echo $RootPath; ?>/processAdjustments.php ">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
				<table class="table-responsive table table-stripped" border="1">
					<tr style="color: white; background-color: #424242">
						<th>Invoice #</th>
						<th>S Invoice #</th>
						<th>Total</th>
						<th>Paid</th>
						<th>WHT</th>
						<th>WHTamt</th>
						<th>GST</th>
						<th>GSTamt</th>
						<th>Settled</th>
					</tr>
					<?php $i=0; foreach($data as $key => $row){ ?>
					<tr style="text-align: center;">
						<td><?php echo $row['transno']; ?></td>
						<td><?php echo $row['shopinvoiceno']; ?></td>
						<td><?php echo round($row['total']); ?></td>
						<td><?php echo locale_number_format(abs($row['alloc']),2); ?></td>
						<td>
							<input <?php echo ($row['WHT'] == 1) ? "style='display:none'":"" ?> type="checkbox" name="WHT<?php echo $i; ?>" <?php echo ($row['WHT'] == 1) ? "checked":""; ?>>
						</td>
						<td>
							<input style="border: 1px solid grey; width: 70px" type="number" step="any" name="WHTamt<?php echo $i; ?>" value="<?php echo ($row['WHTamt']); ?>">
						</td>
						<td>
							<input <?php echo ($row['GSTwithhold'] == 1) ? "style='display:none'":"" ?> type="checkbox" name="GST<?php echo $i; ?>" <?php echo ($row['GSTwithhold'] == 1) ? "checked":""; ?>>
						</td>
						<td>
							<input style="border: 1px solid grey; width: 70px" type="number" step="any" name="GSTamt<?php echo $i; ?>" value="<?php echo ($row['GSTamt']); ?>">
						</td>
						<td>
							<input type="hidden" name="transdate<?php echo $i; ?>" value="<?php echo $row['trandate']; ?>">
							<input type="hidden" name="oWHT<?php echo $i; ?>" value="<?php echo $row['WHT']; ?>">
							<input type="hidden" name="oGST<?php echo $i; ?>" value="<?php echo $row['GSTwithhold']; ?>">
							<input type="hidden" name="id<?php echo $i; ?>" value="<?php echo $key; ?>">
							<input type="hidden" name="invoiceid<?php echo $i; ?>" value="<?php echo $row['transno']; ?>">
							<input type="hidden" name="gsttotalamt<?php echo $i; ?>" value="<?php echo $row['GSTtotalamt']; ?>">
							<input type="hidden" name="total<?php echo $i; ?>" value="<?php echo $row['total']; ?>">
							<input type="hidden" name="paid<?php echo $i; ?>" value="<?php echo $row['alloc']; ?>">
							<input type="checkbox" name="settled<?php echo $i; ?>">
						</td>
					</tr>
					<?php $i++; } ?>
				</table>
					<input type="submit" class="btn btn-success pull-right" name="" value="Save">

				</form>
			</div>

			<footer style="background:#424242; position:fixed; bottom:0; width:100%; text-align:center; padding:5px">
	      		Powered By&nbsp;:&nbsp;<span class="bold" style="color:#aaa;">Compresol</span>
	      	</footer>

		</section>

		<script src="quotation/assets/vendor/jquery/jquery.js"></script>
		<script src="quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	</body>
</html>