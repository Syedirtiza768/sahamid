<?php

	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

?>

<div class="col-md-8" style="padding:0px 30px" data-code="salesTargetClientWise">
	<div class="card ">
		<div style="background: white; color: black; cursor: pointer; z-index: 15;  width:100%;">
			<i class="fa fa-trash removeWidget"></i>
		</div>
		<div id="salesPersonCustomerTSalesman" class="item-content">
		</div>
	</div>
</div>


<script>
		$(document).ready(function(){
			$.get("api/salesmancustomertargets.php", function(res, status){
		
			res = JSON.parse(res);
			console.log(res);
			
			Highcharts.chart('salesPersonCustomerTSalesman', {
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
					formatter: function () {
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
	</script>