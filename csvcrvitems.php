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

	$customerInput = isset($_POST['customer']) ? trim($_POST['customer']) : '';
	$salespersonInput = isset($_POST['salesperson']) ? trim($_POST['salesperson']) : '';
	$customer = mysqli_real_escape_string($db, $customerInput);
	$salesperson = mysqli_real_escape_string($db, $salespersonInput);

	// One row per shop-sale component (shopsalesitems) per selling line (shopsalelines).
	// Lines with no component are listed too, so every sold line appears once or more.
	// Component Subtotal uses the rate saved on the component; Line Sale Value is what the
	// customer was charged for the line after header discounts, which is what the edit
	// screen's internal discount is meant to make the components add up to
	// (see docs/BI_RECONCILIATION_SHOP_SALES.md).
	// shopsalesitems has no index on orderno/lineno, so components are joined to their line
	// by the line's primary key, and lines without components are added in a second query.
	$lineColumns = "shopsale.orderno,
		UPPER(shopsale.payment) AS channel,
		shopsale.orddate,
		shopsale.due,
		shopsale.salesman,
		shopsale.discount AS headerdiscount,
		shopsale.discountPKR AS headerdiscountpkr,
		debtorsmaster.name AS customer,
		ordertotals.gross AS ordergross,
		shopsalelines.id AS lineid,
		shopsalelines.description AS linedescription,
		shopsalelines.quantity AS lineqty,
		shopsalelines.price AS lineprice,
		linecomponents.unitsubtotal,
		linecomponents.unsaveddiscount";

	$headerJoins = "INNER JOIN shopsale ON shopsale.orderno = shopsalelines.orderno
		INNER JOIN debtorsmaster ON debtorsmaster.debtorno = shopsale.debtorno
		INNER JOIN (SELECT orderno, SUM(quantity * price) AS gross
			FROM shopsalelines
			GROUP BY orderno) AS ordertotals ON ordertotals.orderno = shopsale.orderno
		LEFT JOIN (SELECT orderno, lineno,
				SUM(quantity * rate * (1 - discountpercent / 100)) AS unitsubtotal,
				SUM(rate <= 1 AND discountpercent = 0) AS unsaveddiscount
			FROM shopsalesitems
			GROUP BY orderno, lineno) AS linecomponents ON (linecomponents.orderno = shopsalelines.orderno AND linecomponents.lineno = shopsalelines.id)";

	$filterSQL = "WHERE shopsale.complete = 1
		AND " . $channelSQL . "
		AND shopsale.orddate BETWEEN '" . mysqli_real_escape_string($db, $startdate) . "' AND '" . mysqli_real_escape_string($db, $enddate) . "'
		AND debtorsmaster.name LIKE '%" . $customer . "%'
		AND shopsale.salesman LIKE '%" . $salesperson . "%'";

	$SQL = "SELECT " . $lineColumns . ",
		shopsalesitems.id AS componentid,
		shopsalesitems.rate,
		shopsalesitems.discountpercent,
		stockmaster.stockid,
		stockmaster.mnfCode,
		stockmaster.mnfpno,
		stockmaster.description,
		manufacturers.manufacturers_name,
		stockmaster.materialcost AS catalogcost,
		(shopsalesitems.quantity * shopsalelines.quantity) AS qty,
		(shopsalesitems.quantity * shopsalesitems.rate * (1 - shopsalesitems.discountpercent / 100) * shopsalelines.quantity) AS subtotal
		FROM shopsalesitems
		INNER JOIN shopsalelines ON (shopsalelines.orderno = shopsalesitems.orderno AND shopsalelines.id = shopsalesitems.lineno)
		" . $headerJoins . "
		LEFT JOIN stockmaster ON stockmaster.stockid = shopsalesitems.stockid
		LEFT JOIN manufacturers ON manufacturers.manufacturers_id = stockmaster.brand
		" . $filterSQL . "
		UNION ALL
		SELECT " . $lineColumns . ",
		NULL AS componentid, NULL AS rate, NULL AS discountpercent,
		NULL AS stockid, NULL AS mnfCode, NULL AS mnfpno, NULL AS description,
		NULL AS manufacturers_name, NULL AS catalogcost, NULL AS qty, NULL AS subtotal
		FROM shopsalelines
		" . $headerJoins . "
		" . $filterSQL . "
		AND linecomponents.lineno IS NULL
		ORDER BY orderno, lineid, componentid";

	$result = mysqli_query($db, $SQL) or die('Could not load CSV / CRV items.');

	// A line passes when its components add up to its sale value within PKR 1
	$checkTolerance = 1;
	$checkLabels = array(
		'ok'          => 'OK',
		'unattached'  => 'No component attached',
		'unsaved'     => 'Cost 0 or 1.00, discount not saved',
		'above'       => 'Components above sale',
		'below'       => 'Components below sale'
	);

	$data = [];
	$subtotalTotal = 0;
	$saleTotal = 0;
	$lineChecks = array_fill_keys(array_keys($checkLabels), 0);
	$seenLines = [];
	while($row = mysqli_fetch_assoc($result)){
		// Spread header discounts over lines in proportion to their value, as the edit screen does
		$netFactor = ($row['ordergross'] != 0)
			? ($row['ordergross'] * (1 - $row['headerdiscount'] / 100) - $row['headerdiscountpkr']) / $row['ordergross']
			: 1;
		$row['linesale'] = $row['lineqty'] * $row['lineprice'] * $netFactor;

		if ($row['unitsubtotal'] === null) {
			$row['check'] = 'unattached';
		} else {
			$difference = $row['unitsubtotal'] * $row['lineqty'] - $row['linesale'];
			if (abs($difference) <= $checkTolerance) {
				$row['check'] = 'ok';
			} elseif ($row['unsaveddiscount'] > 0) {
				$row['check'] = 'unsaved';
			} else {
				$row['check'] = ($difference > 0) ? 'above' : 'below';
			}
		}

		if (!isset($seenLines[$row['lineid']])) {
			$seenLines[$row['lineid']] = true;
			$saleTotal += $row['linesale'];
			$lineChecks[$row['check']]++;
		}

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
	'<tr><td>Channel: '.htmlspecialchars($channelLabel).'</td><td>Customer: '.htmlspecialchars($customerInput).'</td></tr>'.
	'<tr><td colspan="2">SalesPerson: '.htmlspecialchars($salespersonInput).'</td></tr></table>';

	echo '<p>Rows: '.count($data).' &nbsp; Lines: '.count($seenLines).
		' &nbsp; Line Sale Value: '.number_format($saleTotal, 2).
		' &nbsp; Component Subtotal: '.number_format($subtotalTotal, 2).'</p>';

	echo '<table border=1><tr><th>Line Check</th><th>Lines</th></tr>';
	foreach ($checkLabels as $checkKey => $checkLabel) {
		echo '<tr><td>'.htmlspecialchars($checkLabel).'</td><td>'.$lineChecks[$checkKey].'</td></tr>';
	}
	echo '</table>';

	echo '<p><small>Line Sale Value is the line quantity times its sale price, less its share of the header discount; it repeats on every component row of the line, so total it once per line. '.
		'Saved Rate is the component rate copied from the stock master when the item was attached; Catalogue Cost is the stock master cost today. '.
		'Component Subtotal uses the saved rate and discount. Line Check compares the components of each line with its sale value. '.
		'Totals above cover all rows, not the table search.</small></p>';

?>
	<table id="example" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>Sno.</th>
						<th>Channel</th>
						<th>Order #</th>
						<th>Line Description</th>
						<th>Line Qty</th>
						<th>Sale Price</th>
						<th>Line Sale Value</th>
						<th>Stock ID</th>
						<th>MNF Code</th>
						<th>MNF #</th>
						<th>Description</th>
						<th>MNF Name</th>
						<th>Quantity</th>
						<th>Saved Rate</th>
						<th>Catalogue Cost</th>
						<th>Discount %</th>
						<th>Component Subtotal</th>
						<th>Line Check</th>
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
						$hasComponent = ($row['componentid'] !== null);
						echo '<td>'.htmlspecialchars($row['orderno']).'</td>';
						echo '<td>'.htmlspecialchars($row['linedescription']).'</td>';
						echo '<td>'.htmlspecialchars($row['lineqty']).'</td>';
						echo '<td>'.number_format($row['lineprice'], 2, '.', '').'</td>';
						echo '<td>'.number_format($row['linesale'], 2, '.', '').'</td>';
						echo '<td>'.htmlspecialchars($row['stockid']).'</td>';
						echo '<td>'.htmlspecialchars($row['mnfCode']).'</td>';
						echo '<td>'.htmlspecialchars($row['mnfpno']).'</td>';
						echo '<td>'.htmlspecialchars($row['description']).'</td>';
						echo '<td>'.htmlspecialchars($row['manufacturers_name']).'</td>';
						echo '<td>'.htmlspecialchars($row['qty']).'</td>';
						echo '<td>'.($hasComponent ? number_format($row['rate'], 2, '.', '') : '').'</td>';
						echo '<td>'.($hasComponent ? number_format($row['catalogcost'], 2, '.', '') : '').'</td>';
						echo '<td>'.($hasComponent ? number_format($row['discountpercent'], 2, '.', '') : '').'</td>';
						echo '<td>'.($hasComponent ? number_format($row['subtotal'], 2, '.', '') : '').'</td>';
						$checkStyle = ($row['check'] === 'ok') ? '' : ' style="color:#b00020;font-weight:bold"';
						echo '<td'.$checkStyle.'>'.htmlspecialchars($checkLabels[$row['check']]).'</td>';
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
						<th>Line Description</th>
						<th>Line Qty</th>
						<th>Sale Price</th>
						<th>Line Sale Value</th>
						<th>Stock ID</th>
						<th>MNF Code</th>
						<th>MNF #</th>
						<th>Description</th>
						<th>MNF Name</th>
						<th>Quantity</th>
						<th>Saved Rate</th>
						<th>Catalogue Cost</th>
						<th>Discount %</th>
						<th>Component Subtotal</th>
						<th>Line Check</th>
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
