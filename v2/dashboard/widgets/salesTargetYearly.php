<?php
	
	if(!isset($db)){
		session_start();
		$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
	}

	if(!isset($_SESSION['UserID'])){
		return;
	}

	$key = "salesTargetYearlyTotal-".$_SESSION['UserID'];

	$SQL = "SELECT value FROM cache WHERE unique_key = '$key' AND refreshed_at > '".date('Y-m-d')." 00:00:01'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 1){
		$response = json_decode(mysqli_fetch_assoc($res)['value']);
	}else{

		$allowed = [8,10,22];

		if(in_array((int)$_SESSION['AccessLevel'], $allowed)){
			$SQL = "SELECT SUM(target) as target FROM salesman";
		}else{
			$SQL = "SELECT target FROM salesman WHERE salesmanname='".$_SESSION['UsersRealName']."'";
		}
		
		$yearlySalesTarget = mysqli_fetch_assoc(mysqli_query($db, $SQL))['target'];
		
		$months = [];
		$sales = [];
		
		for($i=1; $i<=12; $i++){
			
			$month = 0;
			if($i <= 9)
				$month .= $i;
			else
				$month = $i;
			
			$startDate = date('Y-'.$month.'-01');
			$endDate = date('Y-'.$month.'-31');
			
			$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
							*invoicedetails.quantity)*invoiceoptions.quantity) as price,custbranch.salesman  FROM invoice 
					INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
					INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
					INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
					INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
						AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
						AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
					WHERE invoice.returned = 0
					AND invoice.inprogress = 0";

			if(!in_array($_SESSION['AccessLevel'], $allowed)){
				$SQL .=	" AND salesman.salesmanname = '".$_SESSION['UsersRealName']."'";
			}

			$SQL .=	" AND invoice.invoicesdate >= '".$startDate."'
					AND invoice.invoicesdate <= '".$endDate."'";
					
			$sale = mysqli_fetch_assoc(mysqli_query($db, $SQL))['price'];
					
			$sales[]  = ($i > (int)(date('m'))) ? null:((int)($sale?:0));
			$months[] = date("M",strtotime($startDate));
			
		}

		$targets = [];

		$monthsRemaining = 12;
		$salesTotal 	 = 0;
		
		$totalVal = 0;
		$i = 1;
		foreach($sales as $sale){
			
			$target = ($yearlySalesTarget - $salesTotal) / $monthsRemaining;
			
			$targets[] = (int)$target;
			
			$salesTotal += $sale;
			
			if($i <= (int)(date('m')))
				$monthsRemaining--;
			
			$i++;
			
		}
		
		$response = [
			'months' => $months,
			'data' => [
				[
					'name' => "Sales",
					'data' => $sales
				],
				[
					'name' => "Target",
					'data' => $targets
				]
			],
			'total' => $salesTotal
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

<div class="col-md-12 item"  style="height:250px" data-code="salesTargetYearly">
	<div style="position: absolute; padding: 5px; background: white; color: black; cursor: pointer; z-index: 15; right: 15px;">
		<i class="fa fa-trash removeWidget"></i>
	</div>
	<div id="salespersonTargetHistory" class="item-content" style="width:100%; height:250px; background:white"></div>
	<script>
		$(document).ready(function(){
			res = <?php echo json_encode($response); ?>;
		
			Highcharts.chart('salespersonTargetHistory', {

			    title: {
			        text: 'Sales Target Yearly'
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