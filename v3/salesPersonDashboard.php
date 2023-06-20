<?php 

	$active = "dashboard";

	include_once("config.php");
	
	include_once("includes/header.php");
	include_once("includes/sidebar.php");
	
	$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
	$res = mysqli_query($db, $SQL);

	$canAccess = [];

	while($row = mysqli_fetch_assoc($res))
		$canAccess[] = $row['can_access'];
	
	$SQL = "SELECT count(*) as count FROM salescase
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE commencementdate <= '".date("Y-m-t 23:59:59")."'
			AND commencementdate >= '".date("Y-m-01 00:00:00")."'
			AND ( salescase.salesman ='".$_SESSION['UsersRealName']."'
				OR www_users.userid IN ('".implode("','", $canAccess)."') )";
	$salescaseCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
	
	$SQL = "SELECT count(*) as count FROM salesorders 
			INNER JOIN salescase ON salescase.salescaseref = salesorders.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate <= '".date("Y-m-t")."'
			AND orddate >= '".date("Y-m-01")."'
			AND ( salescase.salesman ='".$_SESSION['UsersRealName']."'
				OR www_users.userid IN ('".implode("','", $canAccess)."') )";
	$quotationCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

	$SQL = "SELECT count(*) as count FROM ocs 
			INNER JOIN salescase ON salescase.salescaseref = ocs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate <= '".date("Y-m-t 23:59:59")."'
			AND orddate >= '".date("Y-m-01 00:00:00")."'
			AND ( salescase.salesman ='".$_SESSION['UsersRealName']."'
				OR www_users.userid IN ('".implode("','", $canAccess)."') )";
	$ocCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

	$SQL = "SELECT count(*) as count FROM dcs 
			INNER JOIN salescase ON salescase.salescaseref = dcs.salescaseref
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			WHERE orddate <= '".date("Y-m-t")."'
			AND orddate >= '".date("Y-m-01")."'
			AND ( salescase.salesman ='".$_SESSION['UsersRealName']."'
				OR www_users.userid IN ('".implode("','", $canAccess)."') )";
	$dcCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];
	
	$SQL = 'SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
				salescase.salesman,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
				`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
				`ocdocumentfile`, `ocdocumentdate`, debtorsmaster.dba,debtorsmaster.name as client, 
				custbranch.defaultlocation, salescase.priority, salescase.priority_updated_by 
			FROM salescase
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
			LEFT OUTER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno 
											AND salescase.branchcode = custbranch.branchcode)
			WHERE salescase.closed = 0
			AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
			OR www_users.userid IN ("'.implode('","', $canAccess).'") )
			ORDER BY `salescase`.`salescaseindex` DESC LIMIT 10';	

	$salescases = (mysqli_query($db, $SQL));
	
	$SQL = 'SELECT `salescaseindex`, salescase.salescaseref, `salescasedescription`,
				salescase.salesman,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
				`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
				`ocdocumentfile`, `ocdocumentdate`, debtorsmaster.dba,debtorsmaster.name as client, 
				custbranch.defaultlocation, salescase.priority, salescase.priority_updated_by 
			FROM salescase
			INNER JOIN www_users ON www_users.realname = salescase.salesman
			LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
			LEFT OUTER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno 
											AND salescase.branchcode = custbranch.branchcode)
			WHERE salescase.closed = 0
			AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
				OR www_users.userid IN ("'.implode('","', $canAccess).'") )
			AND salescase.priority = "high"
			ORDER BY `salescase`.`salescaseindex` DESC LIMIT 10';	

	$topSalescases = (mysqli_query($db, $SQL));
	
	$salesTarget = 12468;

?>

<div class="content-wrapper">
    <section class="content-header">
      
    </section>

    <section class="content">
	    
		<!--<div class="row">
			<div class="col-md-12">
				<div class="clearfix">
                    <span class="pull-left"><h3 style="display:inline-block">Sales Target</h3></span>
                    <small class="pull-right"><h3 style="display:inline-block"><span id="salesachieved">...</span> <sub>PKR</sub> /<?php ec(locale_number_format($salesTarget)); ?> <sub>PKR</sub></h3></small>
                </div>
				<div class="progress">
                    <div class="progress-bar progress-bar-green" style="width: 70%;"></div>
                </div>
			</div>
		</div>-->
		
		<div class="row">
			
			<div class="col-md-3 col-sm-6 col-xs-12">
				<a href="salesCasesThisMonth.php" target="_blank">
				<div class="info-box">
					<span class="info-box-icon bg-red"><i class="fa ion-briefcase"></i></span>
					<div class="info-box-content">
					  <span class="info-box-text">Salescases</span>
					  <span class="info-box-number"><?php echo $salescaseCount; ?></span>
					</div>
				</div>
				</a>
	        </div>

	        <div class="col-md-3 col-sm-6 col-xs-12">
	          <div class="info-box">
	            <span class="info-box-icon bg-aqua"><i class="ion ion-quote"></i></span>
	            <div class="info-box-content">
	              <span class="info-box-text">Quotations</span>
	              <span class="info-box-number"><?php echo $quotationCount; ?></span>
	            </div>
	          </div>
	        </div>

	        <!-- fix for small devices only -->
	        <div class="clearfix visible-sm-block"></div>

	        <div class="col-md-3 col-sm-6 col-xs-12">
	          <div class="info-box">
	            <span class="info-box-icon bg-green"><i class="ion ion-ios-checkmark-outline"></i></span>

	            <div class="info-box-content">
	              <span class="info-box-text">Order Confirmations</span>
	              <span class="info-box-number"><?php echo $ocCount; ?></span>
	            </div>
	            <!-- /.info-box-content -->
	          </div>
	          <!-- /.info-box -->
	        </div>
	        <!-- /.col -->
	        <div class="col-md-3 col-sm-6 col-xs-12">
	          <div class="info-box">
	            <span class="info-box-icon bg-yellow"><i class="ion ion-ios-cart-outline"></i></span>

	            <div class="info-box-content">
	              <span class="info-box-text">Delivery Chalans</span>
	              <span class="info-box-number"><?php echo $dcCount; ?></span>
	            </div>
				
	          </div>
	        </div>
			
		</div>
		
		<div class="row">
		
			<div class="col-md-12" style="margin-top: 15px;">
				<div class="box box-info" style="min-height: 250px">
	            <div class="box-header">
					<h3 class="box-title">High Priority Salescases
						<a  class="btn btn-info" 
							href="../salescase/selectSalescaseFilter.php" 
							style="padding-top:3px; padding-bottom:3px;"
							target="_blank">
							View All Salescases
						</a>
					</h3>
	            </div>
	            <!-- /.box-header -->
	            <div class="box-body no-padding">
	              <table class="table table-condensed table-striped">
	                <thead>
	                	<tr>
		                  <th style="">#</th>
		                  <th>Salescaseref</th>
						  <th>Start Date</th>
		                  <th>Client</th>
						  <th>Salesman</th>
						  <th>DBA</th>
						  <th></th>
		                </tr>
		            </thead>
		            <tbody>
						<?php foreach($topSalescases as $salescase){ ?>
						<tr>
							<td style="font-size:14px"><?php ec($salescase['salescaseindex']); ?></td>
							<td style="font-size:14px"><?php ec($salescase['salescaseref']); ?></td>
							<td><?php ec(date('Y-m-d',strtotime($salescase['commencementdate']))); ?></td>
							<td style="font-size:14px"><?php ec($salescase['client']); ?></td>
							<td style="font-size:14px"><?php ec($salescase['salesman']); ?></td>
							<td style="font-size:14px"><?php ec($salescase['dba']); ?></td>
							<td>
								<a  href="../salescase/salescaseview.php?salescaseref=<?php ec($salescase['salescaseref']); ?>"
									target="_blank"
									style="font-size:12px"
									class="btn btn-info"
								>
									View
								</a>
							</td>
						</tr>
						<?php } ?>
	              	</tbody>
	              </table>
	            </div>
	            <!-- /.box-body -->
	          </div>
			</div>
		
			<div class="col-md-12"  style="margin-top: 15px; height:600px">
				<div id="salesPersonCustomerT" style="width:100%; height:600px; background:white"></div>
			</div>
		
			<div class="col-md-12"  style="margin-top: 15px; height:250px">
				<div id="salespersonTargetHistory" style="width:100%; height:250px; background:white"></div>
			</div>
			
			<div class="col-md-6" style="margin-top: 15px;">
				<div class="box box-info" style="min-height: 250px">
	            <div class="box-header">
					<h3 class="box-title">
						Client 120+ Days Due&nbsp;&nbsp;&nbsp; 
						<!--<a  class="btn btn-info" 
							style="padding-top:3px; padding-bottom:3px;"
							target="_blank">
							View Full Over Due List
						</a>-->
					</h3>
	            </div>
	            <div class="box-body no-padding" style="height: 200px; overflow: scroll;">
	              <table class="table table-condensed table-striped">
	                <thead>
	                	<tr>
		                  <th style="">#</th>
		                  <th>Client</th>
						  <th style="width:1%; white-space:nowrap">Amount Due</th>
		                </tr>
		            </thead>
		            <tbody id="client120DaysDue">
		                
	              	</tbody>
	              </table>
	            </div>
	            <!-- /.box-body -->
	          </div>
			</div>
		
			<div class="col-md-6" style="margin-top: 15px;">
				<div class="box box-info" style="min-height: 250px">
	            <div class="box-header">
					<h3 class="box-title">
						Salescases To Be Reviewed Today&nbsp;&nbsp;&nbsp; 
						<a  class="btn btn-info" 
							href="../salescase/salescaseWatchlist.php" 
							style="padding-top:3px; padding-bottom:3px;"
							target="_blank">
							View Full Watchlist
						</a>
					</h3>
	            </div>
	            <!-- /.box-header -->
	            <div class="box-body no-padding">
	              <table class="table table-condensed table-striped">
	                <thead>
	                	<tr>
		                  <th style="">#</th>
		                  <th>Salescaseref</th>
		                  <th>Client</th>
						  <th>Salesman</th>
						  <th></th>
		                </tr>
		            </thead>
		            <tbody id="watchlistcontainer">
		                
	              	</tbody>
	              </table>
	            </div>
	            <!-- /.box-body -->
	          </div>
			</div>
		
		</div>

    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>

<script>

	$.get("api/getSalescaseWatchlist.php", function(res, status){
		
		res = JSON.parse(res);
		
		res.forEach(function(data){
			addSalescaseToWatchlist(data);
		});
	
	});
	
	$.get("api/overDue120Plus.php", function(res, status){
		
		res = JSON.parse(res);
		
		res.forEach(function(data){
			addClientOverDue(data);
		});
	
	});

	function addSalescaseToWatchlist(data){
		
		let html = "<tr>";
		html += "<td style='width: 1%; font-size:12px'>"+data.count+"</td>";
		html += "<td style='width: 10px; font-size:12px'>"+data.salescaseref+"</td>";
		html += "<td style='width: 10px; font-size:12px'>"+data.client+"</td>";
		html += "<td style='width: 10px; font-size:12px'>"+data.salesman+"</td>";
		html += "<td style='width: 10px; font-size:12px'>";
		html += "<a style='font-size:10px; padding:4px 8px' ";
		html += "href='../salescase/salescaseview.php?salescaseref="+data.salescaseref+"'";
		html += " class='btn-info' target='_blank'>View</a></td>";
		html += "</tr>";
		
		$("#watchlistcontainer").append(html);
		
	}
	
	function addClientOverDue(data){
		
		let html = "<tr>";
		html += "<td style='width: 1%; font-size:13px'>"+data.count+"</td>";
		html += `<td style='width: 10px; font-size:13px'>
					<form action="/sahamid/customerstatement.php" method="post" target="_blank">
						<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
						<input type="hidden" name="cust" value="${data.debtorno}">
						<input type="submit" style="width:100%; background:rgba(0,0,0,0); border:0;" value="${data.name}">
					</form>
				</td>`;
		html += "<td style='width: 1%; font-size:13px'>"+data.value+"<sub>PKR</sub></td>";
		html += "</tr>";
		
		$("#client120DaysDue").append(html);
		
	}
	
	Highcharts.setOptions({
		lang: {
			thousandsSep: ','
		},
		colors: ['#00ff00', '#ff0000'],
	});
	
	$.get("api/salesTargetApi.php", function(res, status){
		
		res = JSON.parse(res);
		
		Highcharts.chart('salespersonTargetHistory', {

		    title: {
		        text: 'Sales Target'
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
	
	$.get("api/salesmancustomertargets.php", function(res, status){
		
		res = JSON.parse(res);
		console.log(res);
		
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

</script>

<?php
	include_once("includes/foot.php");
?>