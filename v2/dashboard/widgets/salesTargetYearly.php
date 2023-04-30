<?php

if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}

$key = "salesTargetYearlyTotal-" . $_SESSION['UserID'];

$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '" . date('Y-m-d') . " 00:00:01'";
$res = mysqli_query($db, $SQL);

if (mysqli_num_rows($res) == 1) {
	$response = json_decode(mysqli_fetch_assoc($res)['value']);
} else {

	$allowed = [8, 10, 22];

	if (in_array((int)$_SESSION['AccessLevel'], $allowed)) {
		$SQL = "SELECT SUM(target) as target FROM salesman";
	} else {
		$SQL = "SELECT target FROM salesman WHERE salesmanname='" . $_SESSION['UsersRealName'] . "'";
	}

	$yearlySalesTarget = mysqli_fetch_assoc(mysqli_query($db, $SQL))['target'];

	$months = [];
	$sales = [];

	for ($i = 1; $i <= 12; $i++) {

		$month = 0;
		if ($i <= 9)
			$month .= $i;
		else
			$month = $i;

		$startDate = date('Y-' . $month . '-01');
		$endDate = date('Y-' . $month . '-31');

		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman  FROM invoice 
					INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
					INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
					INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
					INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
						AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
						AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
					WHERE invoice.returned = 0
					AND invoice.inprogress = 0";

		if (!in_array($_SESSION['AccessLevel'], $allowed)) {
			$SQL .=	" AND salesman.salesmanname = '" . $_SESSION['UsersRealName'] . "'";
		}

		$SQL .=	" AND invoice.invoicesdate >= '" . $startDate . "'
					AND invoice.invoicesdate <= '" . $endDate . "'";

		$sale = mysqli_fetch_assoc(mysqli_query($db, $SQL))['price'];

		$sales[]  = ($i > (int)(date('m'))) ? null : ((int)($sale ?: 0));
		$months[] = date("M", strtotime($startDate));
	}

	$targets = [];

	$monthsRemaining = 12;
	$salesTotal 	 = 0;

	$totalVal = 0;
	$i = 1;
	foreach ($sales as $sale) {

		$target = ($yearlySalesTarget - $salesTotal) / $monthsRemaining;

		$targets[] = (int)$target;
		echo $target;
		$salesTotal += $sale;

		if ($i <= (int)(date('m')))
			$monthsRemaining--;

		$i++;
	}

	$response = [
		'months' => $months,
		'data' => [
			[
				'name' => "Sales",
				'data' => $sales
			],
			[
				'name' => "Target",
				'data' => $targets
			]
		],
		'total' => $salesTotal
	];

	$value = json_encode($response);
	$refreshed_at = date('Y-m-d H:i:s');

	$SQL = "SELECT * FROM cache WHERE unique_key = '$key'";
	$ress = mysqli_query($db, $SQL);

	if (mysqli_num_rows($ress) == 0) {
		$SQL = "INSERT INTO cache(unique_key,value,refreshed_at) 
					VALUES('$key','$value','$refreshed_at')";
	} else {
		$SQL = "UPDATE cache 
					SET value = '$value',
						refreshed_at = '$refreshed_at'
					WHERE unique_key = '$key'";
	}

	mysqli_query($db, $SQL);
}

?>

<?php

$SQL = "SELECT * FROM user_permission WHERE userid='" . $_SESSION['UserID'] . "' AND permission='*' ";
$ressData = mysqli_query($db, $SQL);
while ($rowData = mysqli_fetch_assoc($ressData)) {
	$permission = $rowData['permission'];
}

?>

<div class="col-md-12 item" style="height:250px; overflow:auto; width:95%" data-code="salesTargetYearly">
	<link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
	<div style="position: relative; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; width:100%">
		<?php
		if ($permission == "*") {
			?>
		<select class="js-example-basic-multiple SalesYearlydata" name="states[]" multiple="multiple" style="width:95%;">
		<?php
				$SQL = "SELECT * FROM salesman ";
				$result = mysqli_query($db, $SQL);
				while ($row_salesman = mysqli_fetch_assoc($result)) {
				?>
					<option value="<?php echo $row_salesman['salesmanname']; ?>"><?php echo $row_salesman['salesmanname']; ?></option>
				<?php }
				?>
			</select>
		<?php } else {
			$SQL = "SELECT can_access FROM salescase_permissions WHERE user='" . $_SESSION['UserID'] . "' ";
			$resss = mysqli_query($db, $SQL); ?>
		<select class="js-example-basic-multiple SalesYearlydata" name="states[]" multiple="multiple" style="width:95%;">
				<?php while ($row = mysqli_fetch_assoc($resss)) {

					$SQL = "SELECT realname FROM www_users WHERE userid='" . $row['can_access'] . "' ";
					$result = mysqli_query($db, $SQL);
					while ($row_data = mysqli_fetch_assoc($result)) {
				?>
						<option value="<?php echo $row_data['realname']; ?>"><?php echo $row_data['realname']; ?></option>
				<?php }
				} ?>
			</select>
		<?php } ?>
		<span class="store-data" onclick="myFunctionYearlySales()"><i style="color:red;" class="fa fa-search" aria-hidden="true"></i></span>
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div id="salespersonTargetHistory" class="item-content" style="width:98.5%; background:white"></div>
	<script>
		$(document).ready(function() {
			$('.js-example-basic-multiple').select2({
				placeholder: {
					text: 'Select an option'
				}
			});
		});
	</script>
	<script>
		$(document).ready(function() {
			res = <?php echo json_encode($response); ?>;

			Highcharts.chart('salespersonTargetHistory', {

				title: {
					text: 'Sales Target Yearly '
				},

				chart: {
					height: 250
				},

				subtitle: {
					text: '---------------------------'
				},

				xAxis: {
					categories: res.months
				},

				yAxis: {
					title: {
						text: ''
					}
				},
				legend: {
					layout: 'vertical',
					align: 'right',
					verticalAlign: 'middle'
				},

				plotOptions: {

				},

				series: res.data,

				responsive: {
					rules: [{
						condition: {
							maxWidth: 500
						},
						chartOptions: {
							legend: {
								layout: 'horizontal',
								align: 'center',
								verticalAlign: 'bottom'
							}
						}
					}]
				}

			});
		});
	</script>

	<script>
		function myFunctionYearlySales() {
			var data = $(".SalesYearlydata").val();

			for (var i = 0; i < data.length; i++) {
				if (data.hasOwnProperty(i)) {
					data[i] = "'" + data[i] + "'";
				}
			}
			var salesman = data.toString();
			console.log(salesman);
			$.ajax({
				type: "POST",
				url: "dashboard/widgets/updated_widgets/yearlySaleUpdate.php",
				data: {
					salesman: salesman
				},
				success: function(data) {
					var data = JSON.parse(data);
					console.log(data);
					var res = data;

					Highcharts.chart('salespersonTargetHistory', {

						title: {
							text: 'Sales Target Yearly '
						},

						chart: {
							height: 250
						},

						subtitle: {
							text: '---------------------------'
						},

						xAxis: {
							categories: res.months
						},

						yAxis: {
							title: {
								text: ''
							}
						},
						legend: {
							layout: 'vertical',
							align: 'right',
							verticalAlign: 'middle'
						},

						plotOptions: {

						},

						series: res.data,

						responsive: {
							rules: [{
								condition: {
									maxWidth: 500
								},
								chartOptions: {
									legend: {
										layout: 'horizontal',
										align: 'center',
										verticalAlign: 'bottom'
									}
								}
							}]
						}

					});
				}
			});
		};
	</script>
</div>