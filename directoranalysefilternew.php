<?php

include('includes/session.inc');
include('configg.php');
if (!userHasPermission($db, 'directorreports')) {
	header("Location: /sahamid/v2/reportLinks.php");
	exit;
}
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$customer = (isset($_POST['customer'])) ? $_POST['customer'] : 0;
$slps = (isset($_POST['salesperson'])) ? $_POST['salesperson'] : 0;
$customertype = (isset($_POST['customertype'])) ? $_POST['customertype'] : 0;

if ($slps) {
	$slps = implode(",", $slps);
}
if ($customertype) {
	$customertype = implode(",", $customertype);
}

?>
<?php

/* $Id: header.inc 6310 2013-08-29 10:42:50Z daintree $ */

// Titles and screen header
// Needs the file config.php loaded where the variables are defined for
//  $RootPath
//  $Title - should be defined in the page this file is included with
if (!isset($RootPath)) {
	$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
	if ($RootPath == '/' or $RootPath == "\\") {
		$RootPath = '';
	}
}

$ViewTopic = isset($ViewTopic) ? '?ViewTopic=' . $ViewTopic : '';
$BookMark = isset($BookMark) ? '#' . $BookMark : '';
$StrictXHTML = False;

if (!headers_sent()) {
	if ($StrictXHTML) {
		header('Content-type: application/xhtml+xml; charset=utf-8');
	} else {
		header('Content-type: text/html; charset=utf-8');
	}
}
if ($Title == _('Copy a BOM to New Item Code')) { //solve the cannot modify heaer information in CopyBOM.php scritps
	ob_start();
}
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title> S A HAMID ERP</title>';
//echo '<link rel="shortcut icon" href="'. $RootPath.'/favicon.ico" />';
//echo '<link rel="icon" href="' . $RootPath.'/favicon.ico" />';
if ($StrictXHTML) {
	echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
} else {
	echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
}
include('erp/includes/head.inc');
echo '<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>';
echo '</head>';
echo '<body>';
include('erp/includes/header.php');

echo '<input type="hidden" name="Lang" id="Lang" value="' . $Lang . '" />';
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<div class="container-fluid">
		<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
<script>$('select').selectpicker();</script>
		<table>
			<tr style="background:#e2f5ff">
				<td style="text-align:center" colspan=2>
					<h4 style="margin:0px">Director Reports</h4>
				</td>
			</tr>
			<tr>
				<td>Documents Between</td>
				<td> <input type="date" name="startdate" required> AND <input type="date" name="enddate" required></td>
			</tr>
			<tr>
				<td>Partial Customer Name</td>
				<td> <input type="text" name="customer" style="background-color:#fffafa; height:40px; width:72%"></td>
			</tr>

			<tr>
				<td>Partial Customer Type</td>
				<td>
					<select name="customertype[]" style="width:40px" class="selectpicker" multiple data-live-search="true">
					<option value="All">Choose All</option>
						<?php
						$result = mysqli_query($conn, "SELECT typename FROM debtortype");
						while ($row = mysqli_fetch_array($result)) {
						?>
							<option value="<?php echo $row["typename"]; ?>"><?php echo $row["typename"]; ?></option>
						<?php
						}
						?>
					</select>
				</td><br>
			</tr>

			<tr>
				<td>Partial Salesperson Name</td>
				<td>
					<select name="salesperson[]" style="width:40px" class="selectpicker" multiple data-live-search="true">
					<option value="All">Choose All</option>
						<?php
						$result = mysqli_query($conn, "SELECT salesmanname FROM salesman");
						while ($row = mysqli_fetch_array($result)) {
						?>
							<option value="<?php echo $row["salesmanname"]; ?>"><?php echo $row["salesmanname"]; ?></option>
						<?php
						}
						?>
					</select>
				</td><br>
			</tr>

			<tr>
				<td align="center" colspan="2"><input id="submit" type="submit" class="btn" style="display:inline-block; width:100%; background:#34a7e8;"></td>
			</tr>
		</table>
</form>

<table style="<?php
				if (isset($_POST['startdate']) && isset($_POST['enddate']))
					echo 'display: table';
				else
					echo 'display: none';
				?>">
	<tr class="hilight">
		<td style="text-align:center" colspan=3>
			<h4 style="margin:0px">Available Reports
				(<?php echo date('d-m-Y', strtotime($_POST['startdate'])); ?>) To (<?php echo date('d-m-Y', strtotime($_POST['enddate'])); ?>)
			</h4>
		</td>
	</tr>
	<tr>
		<td>
			<form target="_blank" action="brandwisesalesdirector.php" method="post">
				<input type="hidden" name="startdate" value="<?php echo $_POST['startdate']; ?>">
				<input type="hidden" name="enddate" value="<?php echo $_POST['enddate']; ?>">
				<input type="hidden" name="customer" value="<?php echo $customer; ?>">
				<input type="hidden" name="salesperson" value="<?php echo $slps; ?>">
				<input type="hidden" name="customertype" value="<?php echo $customertype; ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input class="btn" type="submit" value="Brandwise Sales" style="width:100%; background:#616161" target="_blank"></input>
			</form>
		</td>
		<td>
			<form target="_blank" action="customerwisesalesdirector.php" method="post">
				<input type="hidden" name="startdate" value="<?php echo $_POST['startdate']; ?>">
				<input type="hidden" name="enddate" value="<?php echo $_POST['enddate']; ?>">
				<input type="hidden" name="customer" value="<?php echo $customer; ?>">
				<input type="hidden" name="salesperson" value="<?php echo $slps; ?>">
				<input type="hidden" name="customertype" value="<?php echo $customertype; ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input class="btn" type="submit" value="Customerwise Sales" style="width:100%; background:#616161" target="_blank"></input>
			</form>
		</td>
		<td>
			<form target="_blank" action="salespersonwisesalesdirector.php" method="post">
				<input type="hidden" name="startdate" value="<?php echo $_POST['startdate']; ?>">
				<input type="hidden" name="enddate" value="<?php echo $_POST['enddate']; ?>">
				<input type="hidden" name="customer" value="<?php echo $customer; ?>">
				<input type="hidden" name="salesperson" value="<?php echo $slps; ?>">
				<input type="hidden" name="customertype" value="<?php echo $customertype; ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input class="btn" type="submit" value="Salespersonwise Sales" style="width:100%; background:#616161" target="_blank"></input>
			</form>
		</td>
	</tr>
	<tr>
		<td>
			<form target="_blank" action="opportunitypipeline.php" method="post">
				<input type="hidden" name="startdate" value="<?php echo $_POST['startdate']; ?>">
				<input type="hidden" name="enddate" value="<?php echo $_POST['enddate']; ?>">
				<input type="hidden" name="customer" value="<?php echo $customer; ?>">
				<input type="hidden" name="salesperson" value="<?php echo $slps; ?>">
				<input type="hidden" name="customertype" value="<?php echo $customertype; ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input class="btn" type="submit" value="Opportunity Pipeline" style="width:100%; background:#616161" target="_blank"></input>
			</form>
		</td>
		<td>
			<form target="_blank" action="quotationpipeline.php" method="post">
				<input type="hidden" name="startdate" value="<?php echo $_POST['startdate']; ?>">
				<input type="hidden" name="enddate" value="<?php echo $_POST['enddate']; ?>">
				<input type="hidden" name="customer" value="<?php echo $customer; ?>">
				<input type="hidden" name="salesperson" value="<?php echo $slps; ?>">
				<input type="hidden" name="customertype" value="<?php echo $customertype; ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input class="btn" type="submit" value="Quotation Pipeline" style="width:100%; background:#616161" target="_blank"></input>
			</form>
		</td>
		<td>
			<form target="_blank" action="popipeline.php" method="post">
				<input type="hidden" name="startdate" value="<?php echo $_POST['startdate']; ?>">
				<input type="hidden" name="enddate" value="<?php echo $_POST['enddate']; ?>">
				<input type="hidden" name="customer" value="<?php echo $customer; ?>">
				<input type="hidden" name="salesperson" value="<?php echo $slps; ?>">
				<input type="hidden" name="customertype" value="<?php echo $customertype; ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input class="btn" type="submit" value="PO pipeline Pipeline" style="width:100%; background:#616161" target="_blank"></input>
			</form>
		</td>
	</tr>
	<tr>
		<td>
			<form target="_blank" action="closedcases.php" method="post">
				<input type="hidden" name="startdate" value="<?php echo $_POST['startdate']; ?>">
				<input type="hidden" name="enddate" value="<?php echo $_POST['enddate']; ?>">
				<input type="hidden" name="customer" value="<?php echo $customer; ?>">
				<input type="hidden" name="salesperson" value="<?php echo $slps; ?>">
				<input type="hidden" name="customertype" value="<?php echo $customertype; ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input class="btn" type="submit" value="Closed Cases" style="width:100%; background:#616161" target="_blank"></input>
			</form>
		</td>
		<td>
			<form target="_blank" action="anomolycases.php" method="post">
				<input type="hidden" name="startdate" value="<?php echo $_POST['startdate']; ?>">
				<input type="hidden" name="enddate" value="<?php echo $_POST['enddate']; ?>">
				<input type="hidden" name="customer" value="<?php echo $customer; ?>">
				<input type="hidden" name="salesperson" value="<?php echo $slps; ?>">
				<input type="hidden" name="customertype" value="<?php echo $customertype; ?>">
				<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
				<input class="btn" type="submit" value="Anomoly (DC value > OC value)" style="width:100%; background:#616161" target="_blank">
			</form>
		</td>

	</tr>

</table>

</div>
<?php
include('erp/includes/footer.php');
?>
</body>

</html>