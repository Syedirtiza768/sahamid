<?php

include('includes/session.inc');
	if(!userHasPermission($db, 'csv_crv_items_filter')) {

			header("Location: /sahamid/v2/reportLinks.php");
			exit;
		}

	// Results are only produced from the filter form (POST with FormID)
	if (!isset($_POST['startdate']) OR !isset($_POST['enddate'])) {
		header("Location: csvcrvitemsfilter.php");
		exit;
	}

	$startdate = $_POST['startdate'];
	$enddate = $_POST['enddate'];
	if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startdate) OR !preg_match('/^\d{4}-\d{2}-\d{2}$/', $enddate)) {
		die('Enter a valid start and end date.');
	}

	// Channel is a shop-sale payment type: CRV or CSV (both when blank)
	$channel = isset($_POST['channel']) ? strtoupper(trim($_POST['channel'])) : '';
	if (!in_array($channel, array('', 'CRV', 'CSV'), true)) {
		die('Choose CRV, CSV or both.');
	}
	$channelSQL = ($channel === '') ? "UPPER(shopsale.payment) IN ('CRV','CSV')" : "UPPER(shopsale.payment) = '" . $channel . "'";

	$customer = mysqli_real_escape_string($db, isset($_POST['customer']) ? $_POST['customer'] : '');
	$salesperson = mysqli_real_escape_string($db, isset($_POST['salesperson']) ? $_POST['salesperson'] : '');

	// One row per shop-sale component (shopsalesitems) per selling line (shopsalelines).
	// Quantities and subtotals follow topItemsShopsale.php; they are component measures,
	// not the posted shop-sale total (see docs/BI_RECONCILIATION_SHOP_SALES.md).
	$SQL = "SELECT shopsale.orderno,
		UPPER(shopsale.payment) AS channel,
		shopsale.orddate,
		shopsale.due,
		shopsale.salesman,
		debtorsmaster.name AS customer,
		stockmaster.stockid,
		stockmaster.mnfCode,
		stockmaster.mnfpno,
		stockmaster.description,
		manufacturers.manufacturers_name,
		stockmaster.materialcost AS listprice,
		(shopsalesitems.quantity * shopsalelines.quantity) AS qty,
		shopsalesitems.discountpercent,
		(shopsalesitems.quantity * shopsalesitems.rate * (1 - shopsalesitems.discountpercent / 100) * shopsalelines.quantity) AS subtotal
		FROM shopsalesitems
		INNER JOIN shopsalelines ON (shopsalelines.orderno = shopsalesitems.orderno AND shopsalelines.id = shopsalesitems.lineno)
		INNER JOIN shopsale ON shopsale.orderno = shopsalesitems.orderno
		INNER JOIN stockmaster ON stockmaster.stockid = shopsalesitems.stockid
		LEFT JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
		INNER JOIN debtorsmaster ON debtorsmaster.debtorno = shopsale.debtorno
		WHERE shopsale.complete = 1
		AND " . $channelSQL . "
		AND shopsale.orddate BETWEEN '" . mysqli_real_escape_string($db, $startdate) . "' AND '" . mysqli_real_escape_string($db, $enddate) . "'
		AND debtorsmaster.name LIKE '%" . $customer . "%'
		AND shopsale.salesman LIKE '%" . $salesperson . "%'
		ORDER BY shopsale.orderno, shopsalesitems.lineno, shopsalesitems.id";

	$result = mysqli_query($db, $SQL) or die('Could not load CSV / CRV items.');

	$data = [];
	$subtotalTotal = 0;
	while($row = mysqli_fetch_assoc($result)){
		$data[] = $row;
		$subtotalTotal += $row['subtotal'];
	}

	if (!isset($RootPath)){
		$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF']));
		if ($RootPath == '/' OR $RootPath == "\\") {
			$RootPath = '';
		}
	}

	$StrictXHTML=False;

	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf-8');
		} else {
			header('Content-type: text/html; charset=utf-8');
		}
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';

	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title> S A HAMID ERP</title>';
	if ($StrictXHTML) {
		echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />';
	} else {
		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf-8" />';
	}
	echo '<link href="' . $RootPath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
	echo '<link href="' . $RootPath . '/css/'. $_SESSION['Theme'] .'/foundation.css" rel="stylesheet" type="text/css" />';

	echo '<script type="text/javascript" src = "'.$RootPath.'/javascripts/MiscFunctions.js"></script>';
?>

	<meta name="viewport" content="initial-scale=1.0, maximum-scale=2.0">
	<link rel="stylesheet" type="text/css" href="media/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="media/css/buttons.dataTables.css">

	<script type="text/javascript" language="javascript" src="includes/jquery.js"></script>
	<script type="text/javascript" language="javascript" src="media/js/jquery.dataTables.js"></script>
	<script type="text/javascript" src="extensions/responsive/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" language="javascript" src="media/js/dataTables.buttons.js"></script>
	<script type="text/javascript" language="javascript" src="media/js/jszip.min.js"></script>
	<script type="text/javascript" language="javascript" src="media/js/buttons.html5.js"></script>

	<script type="text/javascript" language="javascript" class="init">
	$(document).ready(function() {
		$('#example').DataTable({
			"processing": true,
			responsive: true,
			"lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
			"dom": 'Blfrtip',
			"buttons": [
				'copyHtml5',
				'excelHtml5',
				'csvHtml5'
			]
		});
	});
	</script>
<?php
	echo '</head>';


	echo '<body>';
	echo '<input type="hidden" name="Lang" id="Lang" value="'.$Lang.'" />';

    echo '<div id="CanvasDiv">';
	echo '<div id="HeaderDiv">';
	echo '<div id="HeaderWrapDiv">';

		echo '<div id="AppInfoDiv">'; //===HJ===
			echo '<div id="AppInfoCompanyDiv">';
				echo '<img src="'.$RootPath.'/css/'.$Theme.'/images/company.png" title="'._('Company').'" alt="'._('Company').'"/>' . stripslashes($_SESSION['CompanyRecord']['coyname']);
			echo '</div>';
			echo '<div id="AppInfoUserDiv">';
				echo '<a href="#"><img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="User" alt="'._('User').'"/>' . stripslashes($_SESSION['UsersRealName']) . '</a>';
			echo '</div>';
			echo '<div id="AppInfoModuleDiv">';
				echo 'CSV / CRV Items';
			echo '</div>';
		echo '</div>'; // AppInfoDiv


		echo '<div id="QuickMenuDiv" style = "padding-right:250px;"><ul>';

		echo '<li><a href="'.$RootPath.'/index.php">' . _('Main Menu') . '</a></li>';

		if (count($_SESSION['AllowedPageSecurityTokens'])>1){

			echo '<li><a href="'.$RootPath.'/SelectProduct.php">' ._('Items')     . '</a></li>';

		}

		echo '<li><a href="'.$RootPath.'/Logout.php" onclick="return confirm(\''._('Are you sure you wish to logout?').'\');">' . _('Logout') . '</a></li>';

		echo '</ul></div>'; // QuickMenuDiv

	echo '</div>'; // HeaderWrapDiv
	echo '</div>'; // Headerdiv
	echo '<div id="BodyDiv">';
	echo '<div id="BodyWrapDiv">';

	$channelLabel = ($channel === '') ? 'CRV and CSV' : $channel;

	echo '<h4>Filters</h4>'.
	'<table border=1><tr><td colspan="2">Between: '.htmlspecialchars($startdate).' AND '.htmlspecialchars($enddate).'</td></tr>'.
	'<tr><td>Channel: '.htmlspecialchars($channelLabel).'</td><td>Customer: '.htmlspecialchars($customer).'</td></tr>'.
	'<tr><td colspan="2">SalesPerson: '.htmlspecialchars($salesperson).'</td></tr></table>';

	echo '<p>Rows: '.count($data).' &nbsp; Component Subtotal (rates): '.number_format($subtotalTotal, 2).'</p>';
	echo '<p><small>Catalogue Cost is the stock master cost today. Component Subtotal uses the rate copied from the stock master when the item was attached. Neither is the posted shop-sale amount.</small></p>';

?>
	<table id="example" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>Sno.</th>
						<th>Channel</th>
						<th>Order #</th>
						<th>Stock ID</th>
						<th>MNF Code</th>
						<th>MNF #</th>
						<th>Description</th>
						<th>MNF Name</th>
						<th>Quantity</th>
						<th>Catalogue Cost</th>
						<th>Discount %</th>
						<th>Component Subtotal</th>
						<th>Client</th>
						<th>Salesman</th>
						<th>Order Date</th>
						<th>Due Date</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$count = 1;
					foreach($data as $row){
						echo '<tr>';
						echo '<td>'.$count++.'</td>';
						echo '<td>'.htmlspecialchars($row['channel']).'</td>';
						echo '<td>'.htmlspecialchars($row['orderno']).'</td>';
						echo '<td>'.htmlspecialchars($row['stockid']).'</td>';
						echo '<td>'.htmlspecialchars($row['mnfCode']).'</td>';
						echo '<td>'.htmlspecialchars($row['mnfpno']).'</td>';
						echo '<td>'.htmlspecialchars($row['description']).'</td>';
						echo '<td>'.htmlspecialchars($row['manufacturers_name']).'</td>';
						echo '<td>'.htmlspecialchars($row['qty']).'</td>';
						echo '<td>'.htmlspecialchars($row['listprice']).'</td>';
						echo '<td>'.htmlspecialchars($row['discountpercent']).'</td>';
						echo '<td>'.number_format($row['subtotal'], 2, '.', '').'</td>';
						echo '<td>'.htmlspecialchars($row['customer']).'</td>';
						echo '<td>'.htmlspecialchars($row['salesman']).'</td>';
						echo '<td>'.htmlspecialchars($row['orddate']).'</td>';
						echo '<td>'.htmlspecialchars($row['due']).'</td>';
						echo '</tr>';
					}
				?>
				</tbody>
				<tfoot>
					<tr>
						<th>Sno.</th>
						<th>Channel</th>
						<th>Order #</th>
						<th>Stock ID</th>
						<th>MNF Code</th>
						<th>MNF #</th>
						<th>Description</th>
						<th>MNF Name</th>
						<th>Quantity</th>
						<th>Catalogue Cost</th>
						<th>Discount %</th>
						<th>Component Subtotal</th>
						<th>Client</th>
						<th>Salesman</th>
						<th>Order Date</th>
						<th>Due Date</th>
					</tr>
				</tfoot>
			</table>

<?php
include('includes/footer.inc');
?>
