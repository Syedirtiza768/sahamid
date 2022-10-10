<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "outstandingTotal";

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$res = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

		$response = [];
		
		$SQL = 'SELECT debtorsmaster.debtorno,debtorsmaster.name,
					SUM(
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
				WHERE debtortrans.type=10 
				AND debtortrans.settled=0
				AND debtortrans.reversed=0'; 
		
		$res = mysqli_query($db, $SQL);

		$response['status'] = 'success';
		$response['totalBalance'] = (round(mysqli_fetch_assoc($res)['totalBalance'],2));

		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
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
				WHERE debtortrans.type=10 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 30';
			
		$res = mysqli_query($db, $SQL);
		$response['add'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
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
				WHERE debtortrans.type=10 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 30 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 60';
			
		$res = mysqli_query($db, $SQL);
		$response['tdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
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
				WHERE debtortrans.type=10 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 60 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 90';
			
		$res = mysqli_query($db, $SQL);
		$response['sdd'] = (round(mysqli_fetch_assoc($res)['60daysdue'],2));
		
		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
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
				WHERE debtortrans.type=10 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) >= 90 
				AND DATEDIFF( "'.date('Y/m/d').'",invoice.invoicesdate) < 120';
			
		$res = mysqli_query($db, $SQL);
		$response['ndd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT salesman.salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(CASE WHEN GSTwithhold = 0 AND WHT = 0 
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
				WHERE debtortrans.type=10 
				AND debtortrans.settled=0 
				AND debtortrans.reversed=0 
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

<div class="col-md-4 item" style="height:250px;" data-code="outstandingTotal">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div id="totaloutstandingcont" class="item-content" style="background: white"></div>
	<script>
		$(document).ready(function(){
			res = <?php echo json_encode($res); ?>

			Highcharts.chart('totaloutstandingcont', {
			    chart: {
			        type: 'funnel',
			        height: 250
			    },
			    title: {
			        text: 'Total Outstanding'
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
</div>
