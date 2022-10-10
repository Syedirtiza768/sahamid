<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "shopSale";

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$response = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

		$months = [];
		//$salescases = [];
		$crvs = [];
		$csvs = [];
		$recoveries = [];

		for($month = -5; $month <= 0; $month++){

			$startDate = date('Y-m-01',strtotime($month." month",strtotime(date("F") . "1")));
			$endDate = date('Y-m-t',strtotime($month." month",strtotime(date("F") . "1")));
			
			//Salescases
			/*
			$SQL = "SELECT * FROM salescase 
					WHERE commencementdate >= '".date('Y-m-01 00:00:00',strtotime($month." month",strtotime(date("F") . "1")))."'
					AND commencementdate <= '".date('Y-m-t 23:23:59',strtotime($month." month",strtotime(date("F") . "1")))."'";
			$salescases [] = mysqli_num_rows(mysqli_query($db, $SQL));
			*/
			//CRV Values
			$SQL = "SELECT 
				SUM(debtortrans.ovamount) as value
				
			FROM shopsale 
			INNER JOIN custbranch ON shopsale.branchcode = custbranch.branchcode
			INNER JOIN debtorsmaster ON shopsale.debtorno = debtorsmaster.debtorno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			INNER JOIN debtortrans ON (debtortrans.type = 750
											AND debtortrans.transno = shopsale.orderno
											AND debtortrans.reversed = 0)
			WHERE shopsale.complete = 1
			AND shopsale.payment='crv'
			AND shopsale.orddate >= '".$startDate."'
			AND shopsale.orddate <= '".$endDate."'";	
					$row=mysqli_fetch_assoc(mysqli_query($db, $SQL));
			$crv = $row['value'];
		//	$crv = mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'];
			$crvs[] = ($crv ? round($crv):0);
		
			//CSV Values
			$SQL =$SQL = "SELECT 
				SUM(debtortrans.ovamount) as value
				
			FROM shopsale 
			INNER JOIN custbranch ON shopsale.branchcode = custbranch.branchcode
			INNER JOIN debtorsmaster ON shopsale.debtorno = debtorsmaster.debtorno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			INNER JOIN debtortrans ON (debtortrans.type = 750
											AND debtortrans.transno = shopsale.orderno
											AND debtortrans.reversed = 0)
			WHERE shopsale.complete = 1
			AND shopsale.payment='CSV'
			AND shopsale.orddate >= '".$startDate."'
			AND shopsale.orddate <= '".$endDate."'";	
					$row=mysqli_fetch_assoc(mysqli_query($db, $SQL));
			$csv = $row['value'];
		//	$csv = mysqli_fetch_assoc(mysqli_query($db, $SQL))['value'];
			$csvs[] = ($csv ? round($csv):0);
	
	/*
	$SQL = "SELECT SUM(custallocns.amt) as amt FROM custallocns
					INNER JOIN debtortrans ON debtortrans.id = custallocns.transid_allocto
					INNER JOIN shopsale ON debtortrans.transno=shopsale.orderno
					WHERE custallocns.datealloc >= '".$startDate." 00:00:00'
					AND debtortrans.type = 750
					AND shopsale.payment='crv'
					AND debtortrans.trandate <= '".$endDate." 23:59:59'";
			$recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];
	*/
          /*  $SQL = "SELECT SUM(custallocns.amt) as amt FROM custallocns
					INNER JOIN debtortrans ON debtortrans.id = custallocns.transid_allocfrom
					INNER JOIN shopsale ON debtortrans.transno=shopsale.orderno
					WHERE debtortrans.trandate >= '".$startDate." 00:00:00'
					AND debtortrans.type = 750
					AND shopsale.payment='crv'
					AND debtortrans.trandate <= '".$endDate." 23:59:59'";
            $recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];

        */    $SQL = "SELECT transid_allocfrom as f,transid_allocto as t,datealloc as d,SUM(amt) as amt, dt.trandate as cd, 
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
			AND invoiced.debtorno != 'WALKIN01'
			AND dt.id = transid_allocfrom
			AND invoiced.type = 750
			AND invoiced.reversed = 0";
            $recovery = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amt'];

			$recoveries[] = ($recovery ? round($recovery):0);
			
			$months[] = date("M-Y",strtotime($month." month", strtotime(date("F") . "1")));

		}

		$response = [

			'months'  => $months,
			'data'  => [
				[
					'name' => 'CRV',
					'data' => $crvs
				],
				[
					'name' => 'CSV',
					'data' => $csvs
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

<div class="col-md-8 item"  style="height:250px;" data-code="shopSale">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div id="detailscomparisonchartShopSale" 
		 class="item-content" 
		 style="width:100%; height:250px; background:white; display:flex; align-items:center; justify-content:center; cursor:pointer" 
		 data-loaded="false">
	</div>
	<script>
		$(document).ready(function(){
			res = <?php echo json_encode($response) ?>;

			Highcharts.chart('detailscomparisonchartShopSale', {

				title: {
					text: 'Shop Sale'
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
</div>