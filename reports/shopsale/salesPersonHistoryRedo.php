<?php 
	$AllowAnyone = true;

	$PathPrefix='../../';
	include_once('../../includes/session.inc');
	include_once('../../includes/SQL_CommonFunctions.inc');

	$salesman = $_POST['salesperson'];
	$fromDate = $_POST['startdate'];
	$toDate = 	$_POST['enddate'];
	include_once('SalesPersonHistory.php');
	
	$SQL = "SELECT salesmanname FROM salesman WHERE salesmancode='$salesman'";
	$salesman = mysqli_fetch_assoc(mysqli_query($db,$SQL))['salesmanname'];
	$data=[];
	
	ksort($arr, SORT_NUMERIC);
	$data=$arr;
	$fromDate = date('d/M/Y', strtotime($fromDate));
	$toDate = date('d/M/Y', strtotime($toDate));
//echo json_encode($data);
//	exit;
	?>

<!DOCTYPE html>
<html>
<head>

	<title>SalesPersonHistory</title>

	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

	<link rel="stylesheet" href="../../quotation/assets/vendor/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/font-awesome/css/font-awesome.css" />
	<link rel="stylesheet" href="../../quotation/assets/stylesheets/theme.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/sweetalert/sweetalert.css" />
	<link rel="stylesheet" href="../../quotation/assets/stylesheets/skins/default.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/select2/select2.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
	<link rel="stylesheet" href="../../v2/assets/datatables/datatables.min.css" />
	<link rel="stylesheet" href="../../v2/assets/datatables/buttons.datatables.min.css" />
	<link rel="stylesheet" href="../../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
	<script src="../../quotation/assets/vendor/modernizr/modernizr.js"></script>

	<style>
		@media print {
		  	.nonprint, .hidden-print{
		  		display: none;
		  	}
		}


	</style>
	<style type="text/css">
		.col-md-12{
			padding: 10px;
		}
		.col-md-3{
			font-weight:bold;
		}
	</style>

</head>
<body>
	
	<h1 style="text-align: center;">Salesperson History</h1>	
	<div class="container">
	
	</div>
	<script src="../../quotation/assets/vendor/jquery/jquery.js"></script>
	<script src="../../quotation/assets/vendor/bootstrap/js/bootstrap.js"></script>
	<script src="../../quotation/assets/vendor/nanoscroller/nanoscroller.js"></script>
	<script src="../../quotation/assets/javascripts/theme.js"></script>
	<script src="../../quotation/assets/vendor/sweetalert/sweetalert.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/TableTools/js/dataTables.tableTools.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>
	<script src="../../v2/assets/datatables/jquery.dataTables.min.js"></script>
	<script src="../../v2/assets/datatables/dataTables.buttons.min.js"></script>
	<script src="../../v2/assets/datatables/buttons.html5.min.js"></script>
	<script src="../../quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>
	
	<script>
		let salesman = '<?php echo $salesman; ?>';
		let data = <?php echo json_encode($data); ?>;
		let fromDate = '<?php echo $fromDate; ?>';
		let toDate = '<?php echo $toDate; ?>';
		let salespersonTotalInvoiceValue = '<?php echo $salespersonTotalInvoiceValue; ?>';
		let salespersonTotalInvoicePercentage = '<?php echo $salespersonTotalInvoicePercentage; ?>';

		function render(data){
			(function salesPersonDetail(salesman, fromDate, toDate,salespersonTotalInvoiceValue,salespersonTotalInvoicePercentage){
				let html = `
					<div class="col-md-12" style="border-style:solid; margin-bottom:10px;">
				<div class="row" >

  				<div class="col-md-3">Sales Person</div>
 				 <div class="col-md-3">`+salesman+`</div>
				</div>
				<div class="row">
  				<div class="col-md-3">From</div>
 				 <div class="col-md-3">${fromDate}</div>
				</div>
				<div class="row">
  				<div class="col-md-3">To</div>
 				 <div class="col-md-3">${toDate}</div>
				</div>
				<div class="row">
  				<div class="col-md-3">Salesperson Shopsale Volume</div>
 				 <div class="col-md-3">${parseInt(data['salespersonTotalInvoiceValue']).toLocaleString()}</div>
				</div>
				<div class="row">
  				<div class="col-md-3">Salesperson Shopsale percentage</div>
 				 <div class="col-md-3">${Math.round((data['salespersonTotalInvoicePercentage']*100))/100}</div>
				</div>
				</div>	
						<hr/>
					
				`;

				$(".container").append(html);
			})(salesman, fromDate, toDate);
			var indexing=[];
			Object.keys(data).sort(function(a,b){return data[b]['clientsTotalInvoiceValue']-data[a]['clientsTotalInvoiceValue']}).forEach(function(key){
			let FormID = "<?php echo $_SESSION['FormID']; ?>";
			if(data[key]['client'] === undefined){
						return;
					}
			$(".container").append(`
				
				<div class="col-md-12" style="border-style:solid;">
				
				<div class="row">

  				<div class="col-md-3">Client</div>
 				<div class="col-md-3">`+data[key]['client']+`</div>
				</div>
				<div class="row">
  				<div class="col-md-3">DBA</div>
 				<div class="col-md-3">`+data[key]['dba']+`</div>
				</div>
				<div class="row">
  				<div class="col-md-3">Shopsale Volume</div>
  				<div class="col-md-3">${parseInt(data[key]['clientsTotalInvoiceValue']).toLocaleString()}
 				</div>
 				</div>
				<div class="row">
  				<div class="col-md-3">Shopsale Value %(wrt salesperson)</div>
 				<div class="col-md-3">${Math.round((data[key]['clientsTotalInvoicePercentage']*100))/100}
 				</div></div>
				<div class="row">
  				<div class="col-md-3">Shopsale Value %(overall)</div>
 				<div class="col-md-3">${Math.round((data[key]['clientsOverallInvoicePercentage']*100))/100}
 				
 				</div>
				</div>
				<hr/>
				

				`);
   

				let commulative = 0,count=0,flag=0;
				Object.keys(data[key]).sort(function(a,b){return data[key][b]['invoiceValue']-data[key][a]['invoiceValue']}).forEach(function(brand){
					if(data[key][brand]['invoiceValue'] === undefined || data[key][brand]['invoiceValue'] <= 0){
						return;
					}
					count++;
					let value=[];
					value= data[key][brand];
					commulative = commulative + value['percentageSale'];

					let header = `
						<div style="padding-top:10px"></div>
						<h3><span>${count}.	</span>${value['brand']}</h3>
						<div class="col-md-12">
							<table width=100% class="table-striped" border=1>
								<tr>
									
									<td>Shopsale Value</td>
									<td>percentage Sale</td>
									<td>Commulative</td>
								</tr>
					`;
					
				//	if(commulative>90&&count>1)
				//	return;

					let body = getTableBody(value, brand, commulative,key);
			
					let footer = `
							</table>
						</div>
					`;

					$('.container').append(header+body+footer);


				});

			});
			//loopend
		}

		function getTableBody(value, brand, commulative,key){

		/*	if(value['invoiceValue'] === undefined || value['invoiceValue'] <= 0){
				return ;
			}*/

			return `
				<tr>
				<td>${parseInt(value['invoiceValue']).toLocaleString()}</td>
					<td>${Math.round((value['percentageSale'] * 100.0)) / 100.0}</td>
					<td>${Math.round(commulative * 100.0) / 100.0}</td>
				</tr>
				</table>
				
				
					<br/><br/><h3>Top Selling Items of [<b><i>${value['brand']}</b></i>]</h3>
			
				<div class="col-md-12">
					<table width=100% class="table-striped" border=1>
						<tr>
							<td>Stock ID</td>
							<td>manufacturers Code</td>
							<td>Part No.</td>
							<td>Description</td>
							
							<td>Discount Factor</td>
							<td>Average SP</td>
							<td>Quantiy</td>
							<td>Total Value</td>
						</tr>
						${getTopItems(value['topItems'])}
			`;
		}

		function getTopItems(items){
			let html = ``;
			
			items.forEach(function(item){
				html += `
					<tr>
						<td>${item['stockid']}</td>
						<td>${item['mnfCode']}</td>
						<td>${item['mnfpno']}</td>
						<td>${item['description']}</td>
						
						<td>${Math.round((item['discountpercent']))}</td>
						<td>${parseInt(item['exclusivegsttotalamount']/item['itemQty']).toLocaleString()}</td>
						<td>${item['itemQty']}</td>
						
						<td>${parseInt(item['exclusivegsttotalamount']).toLocaleString()}</td>
					</tr>
				`;
			});

			return html;
		}

		function doNotInclude(data){
			let include = true;

			Object.keys(data).forEach(function(key){
				if(include){
					if(!(data[key]['invoiceValue'] === undefined || data[key]['invoiceValue'] <= 0)){
						include = false;
					}
				}
			});

			return include;
		}

		$(document).ready(function(){	
			render(data);
		});
	</script>
</body>
</html>
