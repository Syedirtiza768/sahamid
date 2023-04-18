<?php

if (!isset($db)) {
	session_start();
	$db = mysqli_connect('localhost', 'irtiza', 'netetech321', 'sahamid');
}

if (!isset($_SESSION['UserID'])) {
	return;
}

$key = "outstandingTotalCRV";

$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '" . date('Y-m-d') . " 00:00:01'";
$res = mysqli_query($db, $SQL);

if (mysqli_num_rows($res) == 1) {
	$res = json_decode(mysqli_fetch_assoc($res)['value']);
} else {

	$response = [];

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS totalBalance 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"';

	$res = mysqli_query($db, $SQL);

	$response['status'] = 'success';
	$response['totalBalance'] = (round(mysqli_fetch_assoc($res)['totalBalance'], 2));

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) < 30';

	$res = mysqli_query($db, $SQL);
	$response['add'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) >= 30 
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) < 60';

	$res = mysqli_query($db, $SQL);
	$response['tdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) >= 60 
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) < 90';

	$res = mysqli_query($db, $SQL);
	$response['sdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) >= 90 
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) < 120';

	$res = mysqli_query($db, $SQL);
	$response['ndd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "' . date('Y/m/d') . '",shopsale.orddate) > 120';

	$res = mysqli_query($db, $SQL);
	$response['otdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'], 2));

	$res = [];

	$res[] = [
		'0-30 Days',
		$response['add']
	];

	$res[] = [
		'30+ Days',
		$response['tdd']
	];

	$res[] = [
		'60+ Days',
		$response['sdd']
	];

	$res[] = [
		'90+ Days',
		$response['ndd']
	];

	$res[] = [
		'120+ Days',
		$response['otdd']
	];

	$res[] = [
		'Total',
		$response['totalBalance']
	];

	$value = json_encode($res);
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

<div class="col-md-4 item" style="height:250px; overflow:auto; width: 45%" data-code="outstandingCRV">
	<link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
	<div style="position:relative; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; width:100%;">
	<?php
		if ($permission == "*") {
		?>
		<select class="js-example-basic-multiple CRVdata" name="states[]" multiple="multiple" style="width:95%">
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
			<select class="js-example-basic-multiple CRVdata" name="states[]" multiple="multiple" style="width:95%">
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
		<span class="store-data" onclick="myFunctionCRV()"><i style="color:red;" class="fa fa-search" aria-hidden="true"></i></span>
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div id="totaloutstandingcontcrv" class="item-content" style="background: white; width:98.3%"></div>
	<script>
		$(document).ready(function() {
			$('.js-example-basic-multiple').select2();
			res = <?php echo json_encode($res); ?>

			Highcharts.chart('totaloutstandingcontcrv', {
				chart: {
					type: 'funnel',
					height: 250
				},
				title: {
					text: 'Total Outstanding (CRV)'
				},
				plotOptions: {
					series: {
						dataLabels: {
							enabled: true,
							format: '<b>{point.name}</b> ({point.y:,.0f})',
							color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
							softConnector: true
						},
						center: ['15%', '50%'],
						neckWidth: '30%',
						neckHeight: '35%',
						width: '30%'
					}
				},
				legend: {
					enabled: false
				},
				series: [{
					name: 'PKR',
					data: res
				}]
			});
		});
	</script>
	<script>
		function myFunctionCRV() {
			var data = $(".CRVdata").val();

			for (var i = 0; i < data.length; i++) {
				if (data.hasOwnProperty(i)) {
					data[i] = "'" + data[i] + "'";
				}
			}
			var salesman = data.toString();
			$.ajax({
				type: "POST",
				url: "dashboard/widgets/updated_widgets/outstandingTotalCRVUpdate.php",
				data: {
					salesman: salesman
				},
				success: function(data) {
					var data = JSON.parse(data);
					console.log(data);
					var res = data;

					Highcharts.chart('totaloutstandingcontcrv', {
						chart: {
							type: 'funnel',
							height: 250
						},
						title: {
							text: 'Total Outstanding (MT)'
						},
						plotOptions: {
							series: {
								dataLabels: {
									enabled: true,
									format: '<b>{point.name}</b> ({point.y:,.0f})',
									color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
									softConnector: true
								},
								center: ['15%', '50%'],
								neckWidth: '30%',
								neckHeight: '35%',
								width: '30%'
							}
						},
						legend: {
							enabled: false
						},
						series: [{
							name: 'PKR',
							data: res
						}]
					});
				}
			});
		};
	</script>
</div>