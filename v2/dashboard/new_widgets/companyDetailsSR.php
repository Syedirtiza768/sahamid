<?php

if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}

$key = "companyDetailsSR";

$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '" . date('Y-m-d') . " 00:00:01'";
$res = mysqli_query($db, $SQL);

if (mysqli_num_rows($res) == 1) {
	$response = json_decode(mysqli_fetch_assoc($res)['value']);
} else {

	$months = [];
	$salescases = [];
	$quotations = [];
	$sales = [];
	$recoveries = [];

	for ($month = -5; $month <= 0; $month++) {

		$startDate = date('Y-m-01', strtotime($month . " month", strtotime(date("F") . "1")));
		$endDate = date('Y-m-t', strtotime($month . " month", strtotime(date("F") . "1")));

		//Salescases
		$SQL = "SELECT * FROM salescase 
					WHERE commencementdate >= '" . date('Y-m-01 00:00:00', strtotime($month . " month", strtotime(date("F") . "1"))) . "'
					AND commencementdate <= '" . date('Y-m-t 23:23:59', strtotime($month . " month", strtotime(date("F") . "1"))) . "'";
		$salescases[] = mysqli_num_rows(mysqli_query($db, $SQL));

		//Quotation Values
		$SQL = 'SELECT salesorders.salescaseref, salesorderdetails.orderno, SUM(salesorderdetails.unitprice*
						(1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity) as value
					FROM salesorderdetails 
					INNER JOIN salesorderoptions ON (salesorderdetails.orderno = salesorderoptions.orderno 
						AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
					INNER JOIN salesorders ON salesorders.orderno = salesorderdetails.orderno
					WHERE salesorderdetails.lineoptionno = 0 
					AND salesorderoptions.optionno = 0
					AND salesorders.debtorno LIKE "SR%"
					AND salesorders.orddate >= "' . $startDate . '"
					AND salesorders.orddate <= "' . $endDate . '"';
		$quotation = mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'];
		$quotations[] = ($quotation ? round($quotation) : 0);

		//Sales
		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman,
							invoice.services,invoice.gst
					FROM invoice 
					INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
					INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
					INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
						AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
						AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
					WHERE invoice.returned = 0
					AND invoice.inprogress = 0
					AND invoice.debtorno LIKE 'SR%'
					AND invoiceoptions.invoiceoptionno = 0
					AND invoice.invoicesdate >= '" . $startDate . "'
					AND invoice.invoicesdate <= '" . $endDate . "'
					GROUP BY invoice.invoiceno";
		$res = mysqli_query($db, $SQL);

		$saleValue = 0;

		while ($row = mysqli_fetch_assoc($res)) {

			$percent = $row['services'] == 1 ? 1.16 : 1.17;

			if ($row['gst'] == "inclusive") {
				$saleValue += $row['price'] / $percent;
			} else {
				$saleValue += $row['price'];
			}
		}

		$sales[] = ($saleValue ? round($saleValue) : 0);

		$SQL = "SELECT transid_allocfrom as f,transid_allocto as t,datealloc as d,SUM(amt) as amt, dt.trandate as cd, 
			invoice.shopinvoiceno,invoiced.settled, invoiced.alloc as totalalloc, salesman.salesmanname,
			invoiced.ovamount as totalamt, debtorsmaster.name,invoice.invoicedate,invoice.invoicesdate
			FROM custallocns,debtortrans as invoiced
			INNER JOIN invoice on invoice.invoiceno = invoiced.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoiced.debtorno
			INNER JOIN custbranch ON custbranch.branchcode = invoiced.branchcode
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			INNER JOIN debtortrans as dt ON dt.debtorno = invoiced.debtorno
			WHERE dt.trandate >= '$startDate'
			AND dt.trandate <= '$endDate'
			AND invoiced.id = transid_allocto
			AND dt.id = transid_allocfrom
			AND invoiced.type = 10
			AND invoiced.reversed = 0
					AND debtorsmaster.debtorno LIKE 'SR%'
					";
		$recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];
		$recoveries[] = ($recovery ? round($recovery) : 0);

		$months[] = date("M-Y", strtotime($month . " month", strtotime(date("F") . "1")));
	}

	$response = [

		'months'  => $months,
		'data'  => [
			[
				'name' => 'Quotation',
				'data' => $quotations
			],
			[
				'name' => 'Sales',
				'data' => $sales
			],
			[
				'name' => 'Recoveries',
				'data' => $recoveries
			]
		]
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

<div class="col-md-8" style="padding:5px 50px"  data-code="companyDetailsSR">
	<div class="card ">
		<div style="position: relative;  background: white; color: black; cursor: pointer; z-index: 15;  width:100%;">
			<i class="fa fa-trash removeWidget"></i>
		</div>
		<div id="detailscomparisonchartsr" class="item-content">
		</div>
	</div>
</div>


	<script>
		$(document).ready(function() {
			res = <?php echo json_encode($response) ?>;

			Highcharts.chart('detailscomparisonchartsr', {

				title: {
					text: 'Company Analysis SR'
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
		function myCDSRFunction() {
			var data = $(".CDSRdata").val();
			for (var i = 0; i < data.length; i++) {
				if (data.hasOwnProperty(i)) {
					data[i] = "'" + data[i] + "'";
				}
			}
			var salesman = data.toString();
			console.log(salesman);
			$.ajax({
				type: "POST",
				url: "dashboard/widgets/updated_widgets/compDetailUpdateSR.php",
				data: {
					salesman: salesman
				},
				success: function(data) {
					var data = JSON.parse(data);
					console.log(data);
					var res = data;


					Highcharts.chart('detailscomparisonchartsr', {

						title: {
							text: 'Company Analysis SR'
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
