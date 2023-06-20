<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "salesFunnelSR";

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$res = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

		//Opportunity Pipeline
		$SQL = "SELECT count(*) as count FROM salescase 
				LEFT OUTER JOIN salesorders ON salescase.salescaseref = salesorders.salescaseref
				WHERE salesorders.orderno IS NULL
				AND salescase.closed = 0
				AND salescase.debtorno LIKE 'SR%'
				AND commencementdate <= '".date("Y-m-t 23:59:59")."'
				AND commencementdate >= '".date("Y-m-01 00:00:00")."'";
		$oP  = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

		//Quotaion Pipeline
		$SQL = "SELECT count(*) as count FROM salescase 
				INNER JOIN salesorders ON salescase.salescaseref = salesorders.salescaseref
				LEFT OUTER JOIN ocs ON salescase.salescaseref = ocs.salescaseref
				WHERE salescase.podate = '0000-00-00 00:00:00' 
				AND ocs.orderno IS NULL
				AND salescase.debtorno LIKE 'SR%'
				AND salescase.closed = 0 
				AND commencementdate <= '".date("Y-m-t 23:59:59")."'
				AND commencementdate >= '".date("Y-m-01 00:00:00")."'
				GROUP BY salescase.salescaseref";
		$qP  = mysqli_num_rows(mysqli_query($db, $SQL));

		//PO Pipeline
		$SQL = "SELECT count(*) as count FROM salescase 
				INNER JOIN salesorders ON salescase.salescaseref = salesorders.salescaseref
				LEFT OUTER JOIN ocs ON salescase.salescaseref = ocs.salescaseref
				LEFT OUTER JOIN dcs ON salescase.salescaseref = dcs.salescaseref
				WHERE (salescase.podate != '0000-00-00 00:00:00' 
					OR ocs.orderno IS NOT NULL)
				AND (dcs.orderno IS NULL)
				AND salescase.debtorno LIKE 'SR%'
				AND salescase.closed = 0 
				AND commencementdate <= '".date("Y-m-t 23:59:59")."'
				AND commencementdate >= '".date("Y-m-01 00:00:00")."'
				GROUP BY salescase.salescaseref";
		$poP  = mysqli_num_rows(mysqli_query($db, $SQL));

		//DC Pipeline
		$SQL = "SELECT count(*) as count FROM salescase 
				INNER JOIN salesorders ON salescase.salescaseref = salesorders.salescaseref
				LEFT OUTER JOIN dcs ON salescase.salescaseref = dcs.salescaseref
				WHERE dcs.orderno IS NOT NULL
				AND dcs.inprogress = 1
				AND dcs.invoicegroupid IS NULL
				AND salescase.debtorno LIKE 'SR%'
				AND commencementdate <= '".date("Y-m-t 23:59:59")."'
				AND commencementdate >= '".date("Y-m-01 00:00:00")."'
				GROUP BY salescase.salescaseref";
		$dcP  = mysqli_num_rows(mysqli_query($db, $SQL));

		//Invoice Pipeline
		$SQL = "SELECT count(*) as count FROM salescase 
				INNER JOIN dcs ON salescase.salescaseref = dcs.salescaseref
				INNER JOIN invoice ON invoice.groupid = dcs.invoicegroupid
				WHERE dcs.invoicegroupid IS NOT NULL
				AND salescase.debtorno LIKE 'SR%'
				AND commencementdate <= '".date("Y-m-t 23:59:59")."'
				AND commencementdate >= '".date("Y-m-01 00:00:00")."'
				GROUP BY salescase.salescaseref";
		$iP  = mysqli_num_rows(mysqli_query($db, $SQL));

		$res = [];

		$res[] = [
			'Opportunity',
			(int)($oP?:0)
		];

		$res[] = [
			'Quotation',
			(int)($qP?:0)
		];

		$res[] = [
			'Purchase Order',
			(int)($poP?:0)
		];

		$res[] = [
			'Delivery Chalan',
			(int)($dcP?:0)
		];

		$res[] = [
			'Invoice',
			(int)($iP?:0)
		];

		$value = json_encode($res);
		$refreshed_at = date('Y-m-d H:i:s');

		$SQL = "SELECT * FROM cache WHERE unique_key = '$key'";
		$ress = mysqli_query($db, $SQL);

		if(mysqli_num_rows($ress) == 0){
			$SQL = "INSERT INTO cache(unique_key,value,refreshed_at) 
					VALUES('$key','$value','$refreshed_at')";
		}else{
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
<div class="col-md-4 item" 
	 style="height:250px; overflow:auto; width:45%" 
	 data-loaded="false"
	 data-code="salesFunnelSR">
	 <div style="position: relative; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; width:100%;">
	 <?php
		if ($permission == "*") {
		?>
		<select class="js-example-basic-multiple funnelSRdata" name="states[]" multiple="multiple" style="width:95%;">
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
			<select class="js-example-basic-multiple funnelSRdata" name="states[]" multiple="multiple" style="width:95%;">
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
		<span class="store-data" onclick="myFunctionFunnelSR()"><i style="color:red;" class="fa fa-search" aria-hidden="true"></i></span>
		<i class="fa fa-trash removeWidget"></i>
	</div>
	 <div class="item-content" id="funnelcontsr" style="background: white; width:98.3%">
	 </div>
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
	 	$(document).ready(function(){
	 		res = <?php echo json_encode($res); ?>;
			console.log(res);
			Highcharts.setOptions({
				lang: {
					thousandsSep: ','
				}
			});

			Highcharts.chart('funnelcontsr', {
				chart: {
					type: 'funnel',
					height: 250
				},
				title: {
					text: 'Sales Funnel SR (This Month)'
				},
				plotOptions: {
					series: {
						dataLabels: {
							enabled: true,
							format: '<b>{point.name}</b> ({point.y:,.0f})',
							color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
							softConnector: true
						},
						center: ['29%', '50%'],
						neckWidth: '20%',
						neckHeight: '30%',
						width: '60%'
					}
				},
				legend: {
					enabled: false
				},
				series: [{
					name: 'Quantity',
					data: res
				}]
			});
	 	});
	 </script>
	 <script>
		function myFunctionFunnelSR() {
			var data = $(".funnelSRdata").val();

			for (var i = 0; i < data.length; i++) {
				if (data.hasOwnProperty(i)) {
					data[i] = "'" + data[i] + "'";
				}
			}
			var salesman = data.toString();
			console.log(salesman);
			$.ajax({
				type: "POST",
				url: "dashboard/widgets/updated_widgets/salesFunnelSRUpdate.php",
				data: {
					salesman: salesman
				},
				success: function(data) {
					var data = JSON.parse(data);
					console.log(data);
					var res = data;

					Highcharts.setOptions({
						lang: {
							thousandsSep: ','
						}
					});

					Highcharts.chart('funnelcontsr', {
						chart: {
							type: 'funnel',
							height: 250
						},
						title: {
							text: 'Sales Funnel (This Month)'
						},
						plotOptions: {
							series: {
								dataLabels: {
									enabled: true,
									format: '<b>{point.name}</b> ({point.y:,.0f})',
									color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
									softConnector: true
								},
								center: ['29%', '50%'],
								neckWidth: '20%',
								neckHeight: '30%',
								width: '60%'
							}
						},
						legend: {
							enabled: false
						},
						series: [{
							name: 'Quantity',
							data: res
						}]
					});
				}
			});
		};
	</script>
</div>