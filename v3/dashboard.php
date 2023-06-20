<?php 

	$active = "dashboard";

	include_once("config.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

	$allowed = [8,10,22];
	if(!in_array($_SESSION['AccessLevel'], $allowed) || in_array($_SESSION['UserID'],['arif','iftikhar_hussain'])){
		
		if(in_array($_SESSION['UserID'],['ali_imran','muhammad_mohsin','muhammad_sarfraz','us_help','ahmad_zaheer','admin']))
			echo "<script> window.location.href = 'techAdminDashboard.php'; </script>";
		else if(in_array($_SESSION['UserID'],['ejaz_ahmed','syed_nadeem','muhammad_bilal','muhammad_shehzad','muhammad_ali','muhammad_arif','irfan_nasar','sajjad_ahmed','usman_sarwar','ahsan_qureshi','ammar_hafeez']))
			echo "<script> window.location.href = 'salesPersonDashboard.php'; </script>";
		else
			echo "<script> window.location.href = 'defaultDashboard.php'; </script>";
		return;
		
	}

	$SQL = "SELECT count(*) as count FROM salescase 
			WHERE commencementdate <= '".date("Y-m-t 23:59:59")."'
			AND commencementdate >= '".date("Y-m-01 00:00:00")."'";
	$salescaseCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

	$SQL = "SELECT count(*) as count FROM salesorders 
			WHERE orddate <= '".date("Y-m-t")."'
			AND orddate >= '".date("Y-m-01")."'";
	$quotationCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

	$SQL = "SELECT count(*) as count FROM ocs 
			WHERE orddate <= '".date("Y-m-t 23:59:59")."'
			AND orddate >= '".date("Y-m-01 00:00:00")."'";
	$ocCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

	$SQL = "SELECT count(*) as count FROM dcs 
			WHERE orddate <= '".date("Y-m-t")."'
			AND orddate >= '".date("Y-m-01")."'";
	$dcCount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['count'];

	$SQL = "SELECT * FROM `salescase` 
			INNER JOIN custbranch ON (custbranch.debtorno = salescase.debtorno
				AND custbranch.branchcode = salescase.branchcode)
			ORDER BY `salescase`.`salescaseindex` DESC LIMIT 10";
	$salescases = (mysqli_query($db, $SQL));

	//TopSalesPersons
	$SQL = "SELECT SUM((((invoicedetails.unitprice / 100) * ((1-invoicedetails.discountpercent)*100))
						*invoicedetails.quantity)*invoiceoptions.quantity) as price,
						custbranch.salesman, invoice.gst, invoice.services
						FROM invoice 
			INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
			INNER JOIN invoiceoptions ON invoice.invoiceno = invoiceoptions.invoiceno
			INNER JOIN invoicedetails ON (invoice.invoiceno = invoicedetails.invoiceno
				AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno
				AND invoicedetails.invoiceoptionno = invoiceoptions.invoiceoptionno)
			WHERE invoice.returned = 0
			AND invoice.inprogress = 0
			AND invoice.invoicesdate >= '".date("Y-m-01")."'
			AND invoice.invoicesdate <= '".date("Y-m-t")."'
			GROUP BY invoice.invoiceno";

	$res = mysqli_query($db, $SQL);

	$TopSalesPersons = [];

	while($row = mysqli_fetch_assoc($res)){

		if(!array_key_exists($row['salesman'], $TopSalesPersons)){
			$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='".$row['salesman']."'";
			$TopSalesPersons[$row['salesman']] = [];
			$TopSalesPersons[$row['salesman']]['name'] =
			mysqli_fetch_assoc(mysqli_query($db, $SQL))['salesmanname'];
			$TopSalesPersons[$row['salesman']]['sales'] = 0;
		}
		
		$percent = $row['services'] == 1 ? 1.16:1.17;
			
		$price = 0;
			
		if($row['gst'] == "inclusive"){
			$price += $row['price']/$percent;
		}else{
			$price += $row['price'];
		}

		$TopSalesPersons[$row['salesman']]['sales'] += $price;

	}

	usort($TopSalesPersons, "cmp");

	function cmp($a, $b){
	    if ($a['sales'] == $b['sales'])
	        return 0;
	    return ($a['sales'] > $b['sales']) ? -1 : 1;
	}
	
	$SQL = "SELECT * FROM salesman";
	$salesPersons = mysqli_query($db, $SQL);

?>

<div class="content-wrapper">
    <section class="content-header">
      
    </section>

    <section class="content">
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
	            <!-- /.info-box-content -->
	          </div>
	          <!-- /.info-box -->
	        </div>
	        <!-- /.col -->
		</div>
		<div class="row">

			<div class="col-md-6" id="funnelcont" style="height:250px; background:white; display:flex; align-items:center; justify-content:center; cursor:pointer" data-loaded="false">
				Click To Load Sales Funnel
			</div>
			<div class="col-md-6" id="totaloutstandingcont" style="height:250px; background:white"></div>

			<div class="col-md-4" style="margin-top: 15px;">
				<div class="box box-info" style="min-height: 250px">
	            <div class="box-header">
	              <h3 class="box-title">Top 5 Sales Persons (This Month)</h3>
	            </div>
	            <!-- /.box-header -->
	            <div class="box-body no-padding">
	              <table class="table table-condensed table-striped">
	                <thead>
	                	<tr>
		                  <th style="width: 10px">#</th>
		                  <th>Salesman</th>
		                  <th>Amount</th>
		                </tr>
		            </thead>
		            <tbody>
		                <?php $count = 1; foreach ($TopSalesPersons as $key => $value) { if($count > 5) break; ?>
		                <tr>
		                  <td style="width: 10px"><?php echo $count; ?></td>
		                  <td><?php echo $value['name']; ?></td>
		                  <td><?php echo locale_number_format($value['sales'],2); ?> <sub>PKR</sub></td>
		                </tr>
		                <?php $count++; } ?>
	              	</tbody>
	              </table>
	            </div>
	            <!-- /.box-body -->
	          </div>
			</div>

			<div class="col-md-8"  style="margin-top: 15px; height:250px">
				<div id="comparisonchart" style="width:100%; height:250px; background:white; display:flex; align-items:center; justify-content:center; cursor:pointer" data-loaded="false">
					Click To Load Sales Person Sales History
				</div>
			</div>
			
			<div class="col-md-12"  style="margin-top: 15px; height:250px">
				<div id="detailscomparisonchart" style="width:100%; height:250px; background:white; display:flex; align-items:center; justify-content:center; cursor:pointer" data-loaded="false">
					Click to load company details
				</div>
			</div>
			
			<div class="col-md-6" style="margin-top: 15px;">
				<div class="box box-info" style="min-height: 250px">
	            <div class="box-header">
					<h3 class="box-title">
						Top 5 Customers&nbsp;&nbsp;&nbsp;
						<!--<a  class="btn btn-info"
							style="padding-top:3px; padding-bottom:3px;"
							target="_blank">
							View All Customers
						</a>-->
					</h3>
	            </div>
	            <!-- /.box-header -->
	            <div class="box-body no-padding">
	              <table class="table table-condensed table-striped">
	                <thead>
	                	<tr>
		                  <th style="">#</th>
		                  <th>Name</th>
		                  <th>Sales Total</th>
						  <th>3 Months</th>
						  <th>Current</th>
		                </tr>
		            </thead>
		            <tbody id="topCustomer">
		                
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
			
			<div class="col-md-12" style="margin-top: 15px;">
				<div class="box box-info" style="min-height: 250px">
	            <div class="box-header">
					<h3 class="box-title">
						SalesPerson Target <span id="selectedSalesPerson"></span>
						<select id="salesPersonList">
							<option value="">Select SalesPerson</option>
							<?php while($row = mysqli_fetch_assoc($salesPersons)){ ?>
								<option value="<?php ec($row['salesmancode']); ?>">
									<?php ec($row['salesmanname']); ?>
								</option>
							<?php } ?>
						</select>
					</h3>
	            </div>
	            <!-- /.box-header -->
	            <div class="box-body no-padding">
					<div class="col-md-12"  style="margin-top: 15px; height:600px">
						<div id="salesPersonCustomerT" style="width:100%; height:600px; background:white; display:flex; align-items:center; justify-content:center; cursor:pointer">Select SalesPerson From List...</div>
					</div>
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

	$("#comparisonchart").on("click", function(){
		
		if($(this).attr("data-loaded") == "true")
			return;
		
		$(this).attr("data-loaded","true");
		
		$("#comparisonchart").text("Loading...");
		
		$.get("api/salesPersonComparison.php", function(res, status){

			res = JSON.parse(res);

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
		
	});
	
	$("#detailscomparisonchart").on("click",function(){
		
		if($(this).attr("data-loaded") == "true")
			return;
		
		$(this).attr("data-loaded","true");
		
		$("#detailscomparisonchart").text("Loading...");
		
		$.get("api/detailsComparison.php", function(res, status){

			res = JSON.parse(res);

			Highcharts.chart('detailscomparisonchart', {

				title: {
					text: 'Company Analysis'
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
		
	});

	$.get("api/getSalescaseWatchlist.php", function(res, status){
		
		res = JSON.parse(res);
		
		res.forEach(function(data){
			addSalescaseToWatchlist(data);
		});
	
	});
	
	$.get("api/topCustomers.php?count=5", function(res, status){
		
		res = JSON.parse(res);
		
		res.forEach(function(data){
			addTopCustomerToList(data);
		});
	
	});

	$("#funnelcont").on("click", function(){
		
		if($(this).attr("data-loaded") == "true")
			return;
		
		$(this).attr("data-loaded","true");
		
		$("#funnelcont").text("Loading...");
		
		$.get("api/salesPipeline.php", function(res, status){

			res = JSON.parse(res);
			
			Highcharts.setOptions({
				lang: {
					thousandsSep: ','
				}
			});

			Highcharts.chart('funnelcont', {
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
		});
		
	});

	$.get("api/totalOutstanding.php", function(res, status){

		res = JSON.parse(res);

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
	
	function addTopCustomerToList(data){
		
		let html = "<tr>";
		html += "<td style='width: 1%; font-size:12px'>"+data.count+"</td>";
		html += "<td style='width: 10px; font-size:12px'>"+data.name+"</td>";
		html += "<td style='width: 1%; font-size:12px'>"+data.value+"<sub>PKR</sub></td>";
		html += "<td style='width: 1%; font-size:12px'>"+data.tMonths+"<sub>PKR</sub></td>";
		html += "<td style='width: 1%; font-size:12px'>"+data.thisMonth+"<sub>PKR</sub></td>";
		html += "</tr>";
		
		$("#topCustomer").append(html);
		
	}
	
	$(document).ready(function(){
		
		$("#salesPersonList").on("change",function(){
			
			let selectedName = `( ${$(this).find(":selected").text()} )`;
			
			$.get("api/salesmancustomertargets.php?salesmancode="+$(this).val(), function(res, status){
		
				res = JSON.parse(res);
				
				$("#selectedSalesPerson").html(selectedName);

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
			
		});
		
	});
	
</script>

<?php
	include_once("includes/foot.php");
?>