<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "salesPersonSalesHistoryTotal";

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$response = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

		$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman  FROM invoice 
				INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
				INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
				INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
					AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
					AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
				WHERE invoice.returned = 0
				AND invoice.inprogress = 0
				AND invoice.invoicesdate <= '".date("Y-m-t")."'
				GROUP BY invoice.invoiceno";

		$res = mysqli_query($db, $SQL);

		$TopSalesPersons = [];

		while($row = mysqli_fetch_assoc($res)){

			if(!array_key_exists($row['salesman'], $TopSalesPersons)){
				$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='".$row['salesman']."'";
				$TopSalesPersons[$row['salesman']] = [];
				$TopSalesPersons[$row['salesman']]['code'] = $row['salesman'];
				$TopSalesPersons[$row['salesman']]['name'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['salesmanname'];
				$TopSalesPersons[$row['salesman']]['sales'] = 0;
			}

			$TopSalesPersons[$row['salesman']]['sales'] += $row['price'];

		}

		usort($TopSalesPersons, function ($a, $b){
		    if ($a['sales'] == $b['sales'])
		        return 0;
		    return ($a['sales'] > $b['sales']) ? -1 : 1;
		});

		$salesPersonData = [];

		$count = 1;
		foreach ($TopSalesPersons as $keys => $value) {

			$data = [];

			for($month = -5; $month <= 0; $month++){

				$startDate = date('Y-m-01',strtotime($month." month",strtotime(date("F") . "1")));
				$endDate = date('Y-m-t',strtotime($month." month",strtotime(date("F") . "1")));

				$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
								*invoicedetails.quantity)*invoiceoptions.quantity)as price
								,custbranch.salesman,invoice.gst,invoice.services  
						FROM invoice 
						INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
						INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
						INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
							AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
							AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
						WHERE invoice.returned = 0
						AND invoice.inprogress = 0
						AND custbranch.salesman = '".$value['code']."'
						AND invoice.invoicesdate >= '".$startDate."'
						AND invoice.invoicesdate <= '".$endDate."'
						GROUP BY invoice.invoiceno";

				$res = mysqli_query($db, $SQL);
				
				$price = 0;
				
				while($row = mysqli_fetch_assoc($res)){
					
					$percent = $row['services'] == 1 ? 1.16:1.17;
					
					if($row['gst'] == "inclusive"){
						$price += $row['price']/$percent;
					}else{
						$price += $row['price'];
					}
					
				}
				
				$data[] = ($price ? round($price):0);

			}

			$salesPersonData[] = [

				'name' => $value['name'],
				'data' => $data

			];

			$count++;

		}

		$months = [];
		for($month = -5; $month <= 0; $month++){

			$months[] = date("M-Y",strtotime($month." month",strtotime(date("F") . "1")));

		}
		
		$response = [
			'start' => (int)(date('m',strtotime('-5 month',strtotime(date("F") . "1")))),
			'months'  => $months,
			'data'  => $salesPersonData
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

<div class="col-md-8 item"  style="height:250px;" data-code="salesPersonSalesHistory">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div id="comparisonchart"
	 	 class="item-content"
		 style="width:100%; height:250px; background:white; display:flex; align-items:center; justify-content:center; cursor:pointer" data-loaded="false">
	</div>
	<script>
		$(document).ready(function(){
			res = <?php echo json_encode($response); ?>;

			Highcharts.chart('comparisonchart', {

				title: {
					text: 'Sales Person History'
				},

				chart: {
					height: 250
				},

				subtitle: {
					text: '-------------------------'
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
