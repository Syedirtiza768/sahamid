<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "unallocatedSR";

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$res = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

		$response = [];
		
		$SQL = 'SELECT debtorsmaster.debtorno,debtorsmaster.name,
					-1*SUM(
						CASE WHEN GSTwithhold = 0 AND WHT = 0 
							THEN ovamount - alloc
						WHEN GSTwithhold = 0 AND WHT = 1 
							THEN ovamount - alloc - WHTamt
						WHEN GSTwithhold = 1 AND WHT = 0 
							THEN ovamount - alloc - GSTamt
						WHEN GSTwithhold = 1 AND WHT = 1 
							THEN ovamount - alloc - GSTamt - WHTamt
						END
					) AS totalBalance  
				FROM debtortrans 
				INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno 
				WHERE debtortrans.type=12 
				AND debtortrans.settled=0
				AND debtortrans.debtorno LIKE "SR%"
				AND debtortrans.reversed=0'; 
		
		$res = mysqli_query($db, $SQL);

		$response['status'] = 'success';
		$response['totalBalance'] = (round(mysqli_fetch_assoc($res)['totalBalance'],2));

		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					-1*SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
							THEN ovamount-alloc
						WHEN GSTwithhold = 0 AND WHT = 1 
							THEN ovamount - alloc - WHTamt
						WHEN GSTwithhold = 1 AND WHT = 0 
							THEN ovamount - alloc - GSTamt
						WHEN GSTwithhold = 1 AND WHT = 1 
							THEN ovamount - alloc - GSTamt - WHTamt
						END
					) AS 30daysdue 
				FROM debtortrans 
				INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
				WHERE debtortrans.type=12 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND debtortrans.debtorno LIKE "SR%"
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 30';
			
		$res = mysqli_query($db, $SQL);
		$response['add'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					-1*SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
							THEN ovamount-alloc
						WHEN GSTwithhold = 0 AND WHT = 1 
							THEN ovamount - alloc - WHTamt
						WHEN GSTwithhold = 1 AND WHT = 0 
							THEN ovamount - alloc - GSTamt
						WHEN GSTwithhold = 1 AND WHT = 1 
							THEN ovamount - alloc - GSTamt - WHTamt
						END
					) AS 30daysdue 
				FROM debtortrans 
				INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
				WHERE debtortrans.type=12 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND debtortrans.debtorno LIKE "SR%"
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 30 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 60';
			
		$res = mysqli_query($db, $SQL);
		$response['tdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					-1*SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
							THEN ovamount-alloc
						WHEN GSTwithhold = 0 AND WHT = 1 
							THEN ovamount - alloc - WHTamt
						WHEN GSTwithhold = 1 AND WHT = 0 
							THEN ovamount - alloc - GSTamt
						WHEN GSTwithhold = 1 AND WHT = 1 
							THEN ovamount - alloc - GSTamt - WHTamt
						END
					) AS 60daysdue 
				FROM debtortrans 
				INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
				WHERE debtortrans.type=12 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND debtortrans.debtorno LIKE "SR%"
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 60 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 90';
			
		$res = mysqli_query($db, $SQL);
		$response['sdd'] = (round(mysqli_fetch_assoc($res)['60daysdue'],2));
		
		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					-1*SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
							THEN ovamount-alloc
						WHEN GSTwithhold = 0 AND WHT = 1 
							THEN ovamount - alloc - WHTamt
						WHEN GSTwithhold = 1 AND WHT = 0 
							THEN ovamount - alloc - GSTamt
						WHEN GSTwithhold = 1 AND WHT = 1 
							THEN ovamount - alloc - GSTamt - WHTamt
						END
					) AS 30daysdue 
				FROM debtortrans 
				INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
				WHERE debtortrans.type=12 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND debtortrans.debtorno LIKE "SR%"
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 90 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 120';
			
		$res = mysqli_query($db, $SQL);
		$response['ndd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					-1*SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
							THEN ovamount-alloc
						WHEN GSTwithhold = 0 AND WHT = 1 
							THEN ovamount - alloc - WHTamt
						WHEN GSTwithhold = 1 AND WHT = 0 
							THEN ovamount - alloc - GSTamt
						WHEN GSTwithhold = 1 AND WHT = 1 
							THEN ovamount - alloc - GSTamt - WHTamt
						END
					) AS 30daysdue 
				FROM debtortrans 
				INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
				WHERE debtortrans.type=12 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND debtortrans.debtorno LIKE "SR%"
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 120';
			
		$res = mysqli_query($db, $SQL);
		$response['otdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));

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
<div class="col-md-4 item" style="height:250px; overflow:auto; width:45%" data-code="unallocatedSR">
	<div style="position: relative; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; width:100%;">
	<?php
		if ($permission == "*") {
		?>
			<select class="js-example-basic-multiple dataUASR" name="states[]" multiple="multiple" style="width:90%;">
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
			<select class="js-example-basic-multiple dataUASR" name="states[]" multiple="multiple" style="width:90%;">
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
		<span class="store-data" onclick="myFunctionUASR()"><i style="color:red;" class="fa fa-search" aria-hidden="true"></i></span>
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div id="totalunallocatedcontsr" class="item-content" style="background: white; width:98.3%"></div>
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
			res = <?php echo json_encode($res); ?>

			Highcharts.chart('totalunallocatedcontsr', {
			    chart: {
			        type: 'funnel',
			        height: 250
			    },
			    title: {
			        text: 'Total unallocated SR'
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
		function myFunctionUASR() {
			var data = $(".dataUASR").val();
			for (var i = 0; i < data.length; i++) {
				if (data.hasOwnProperty(i)) {
					data[i] = "'" + data[i] + "'";
				}
			}
			var salesman = data.toString();
			$.ajax({
				type: "POST",
				url: "dashboard/widgets/updated_widgets/unallocatedSRUpdate.php",
				data: {
					salesman: salesman
				},
				success: function(data) {
					var data = JSON.parse(data);
					console.log(data);
					var res = data;

					Highcharts.chart('totalunallocatedcontsr', {
			    chart: {
			        type: 'funnel',
			        height: 250
			    },
			    title: {
			        text: 'Total Unallocated'
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
