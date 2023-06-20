<?php

if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}

$SQL = "SELECT * FROM salesman";
$salesPersons = mysqli_query($db, $SQL);

?>

<div class="col-md-8" style="padding:0px 30px" data-code="salesPersonTargetAll">
	<div class="card ">
		<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
			<i class="fa fa-trash removeWidget"></i>
		</div>
		<div class="box box-info" style="min-height: 250px">
			<div class="box-header item-content">
				<h3 class="box-title">
					SalesPerson Target <span id="selectedSalesPerson"></span>
					<select id="salesPersonList">
						<option value="">Select SalesPerson</option>
						<?php while ($row = mysqli_fetch_assoc($salesPersons)) { ?>
							<option value="<?php echo ($row['salesmancode']); ?>">
								<?php echo ($row['salesmanname']); ?>
							</option>
						<?php } ?>
					</select>
				</h3>
			</div>
			<div class="box-body no-padding">
				<div class="col-md-12" style="margin-top: 15px; height:600px">
					<div id="salesPersonCustomerT" style="width:100%; height:600px; background:white; display:flex; align-items:center; justify-content:center; cursor:pointer">Select SalesPerson From List...</div>
				</div>
			</div>
		</div>
	</div>
</div>


<script>
	$(document).ready(function() {

		$("#salesPersonList").on("change", function() {

			let selectedName = `( ${$(this).find(":selected").text()} )`;

			$.get("api/salesmancustomertargets.php?salesmancode=" + $(this).val(), function(res, status) {

				res = JSON.parse(res);

				$("#selectedSalesPerson").html(selectedName);

				Highcharts.chart('salesPersonCustomerT', {
					chart: {
						type: 'bar'
					},
					title: {
						text: 'Yearly Targets WRT Clients'
					},
					subtitle: {
						text: ''
					},
					xAxis: {
						categories: res.data.yaxis,
					},
					yAxis: {
						min: 0,
						title: {
							text: '',
							align: 'high'
						},
						labels: {
							overflow: 'justify'
						}
					},
					tooltip: {
						valueSuffix: '',
						formatter: function() {
							var axis = this.series.yAxis;

							return axis.defaultLabelFormatter.call({
								axis: axis,
								value: this.y
							});
						}
					},
					plotOptions: {
						bar: {
							dataLabels: {
								enabled: true
							}
						}
					},
					legend: {
						layout: 'vertical',
						align: 'right',
						verticalAlign: 'top',
						x: -40,
						y: 80,
						floating: true,
						borderWidth: 1,
						backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
						shadow: true
					},
					credits: {
						enabled: false
					},
					series: res.data.data
				});

			});

		});

	});
</script>