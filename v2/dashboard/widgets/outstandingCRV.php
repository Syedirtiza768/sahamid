<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "outstandingTotalCRV";

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$res = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

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
		$response['totalBalance'] = (round(mysqli_fetch_assoc($res)['totalBalance'],2));

		$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) < 30';
			
		$res = mysqli_query($db, $SQL);
		$response['add'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) >= 30 
				AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) < 60';
			
		$res = mysqli_query($db, $SQL);
		$response['tdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) >= 60 
				AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) < 90';
			
		$res = mysqli_query($db, $SQL);
		$response['sdd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) >= 90 
				AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) < 120';
			
		$res = mysqli_query($db, $SQL);
		$response['ndd'] = (round(mysqli_fetch_assoc($res)['30daysdue'],2));
		
		$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
					SUM(ovamount-alloc) AS 30daysdue 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
				WHERE debtortrans.type=750 
				AND debtortrans.settled=0 
				AND shopsale.payment="crv"
				AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) > 120';
			
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

<div class="col-md-4 item" style="height:250px;" data-code="outstandingCRV">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div id="totaloutstandingcontcrv" class="item-content" style="background: white"></div>
	<script>
		$(document).ready(function(){
			res = <?php echo json_encode($res); ?>

			Highcharts.chart('totaloutstandingcontcrv', {
			    chart: {
			        type: 'funnel',
			        height: 250
			    },
			    title: {
			        text: 'Total Outstanding CRV'
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
