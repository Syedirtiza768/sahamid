<?php 

	$active = "dashboard";

	include_once("config.php");

	/*if(isset($_GET['date'])){
		
		$startDate = date($_GET['date'].'-01');
		
		$currentMonth = date('m');
		$selectedMonth = explode("-",$_GET['date'])[1];
		
		$difference = 0;
		if($currentMonth > $selectedMonth) $difference = $currentMonth - $selectedMonth;
		
		if($difference > 0){
			$endDate = date('Y-m-31',strtotime((-1*$difference)." month",strtotime(date("F") . "1")));
		}else{
			$endDate = date($_GET['date'].'-31');
		}
		
	}else{
		$startDate = date('Y-m-01');
		$endDate = date('Y-m-31');
	}*/
	
	if(isset($_GET['date'])){
		$startDate = date($_GET['date'].'-01');
		$endDate = date($_GET['date'].'-31');
	}else{
		$startDate = date('Y-m-01');
		$endDate = date('Y-m-31');
	}
	

	$SQL = "SELECT 
				debtortrans.ovamount as price,
				salesman.salesmanname,
				invoice.invoiceno,
				invoice.shopinvoiceno,
				invoice.invoicedate,
				invoice.invoicesdate,
				debtorsmaster.name as client, 
				debtorsmaster.dba,
				invoice.services,
				invoice.gst,
				debtortrans.alloc,
				debtortrans.settled
			FROM invoice 
			INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
			INNER JOIN debtorsmaster ON invoice.debtorno = debtorsmaster.debtorno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			INNER JOIN debtortrans ON (debtortrans.type = 10
											AND debtortrans.transno = invoice.invoiceno
											AND debtortrans.reversed = 0)
			WHERE invoice.returned = 0
			AND invoice.inprogress = 0
			AND invoice.invoicesdate >= '".$startDate."'
			AND invoice.invoicesdate <= '".$endDate."'";

	$res = mysqli_query($db, $SQL);

	include_once("includes/header.php");
	include_once("includes/sidebar.php");	
	
?>
<style>
	.table-head{position: relative; top:60px; background: #424242; left: 0; color: white;} .table-head th{border: 1px solid white;} .center-fit{width: 1%; white-space: nowrap; text-align: center;} .modal{display:none;position:fixed;z-index:1000;left:0;top: 0;width: 100%;height:100%;overflow:auto;background-color:rgb(0,0,0);background-color:rgba(0,0,0,0.4);}.modal-content{background-color:#fefefe;margin: 5% auto;padding: 20px;border: 1px solid #888;width: 80%;}.close {color: #aaa;float: right;font-size: 28px;font-weight: bold;}.close:hover,.close:focus{color: black;text-decoration: none;cursor: pointer;}.tasktitle{border:1px solid #424242; border-radius: 7px; padding: 5px; width: 100%}.taskdescription{border:1px solid #424242; border-radius: 7px;padding: 5px; width: 100%; max-width: 100%; min-width: 100%;min-height: 100px;max-height: 100px;}#saveNewTask{margin-top:15px;}.warningsbox{display:none; margin-top: 15px;}.dontFBreak{width: 1%; white-space: nowrap;}td{vertical-align: middle !important;}.tooltip {position: relative;display: inline-block;border-bottom: 1px dotted black;visibility: visible !important;opacity: 1 !important;z-index: 998 !important;}.tooltip .tooltiptext {visibility: hidden;width: 400px;background-color: black;color: #fff;text-align: center;border-radius: 6px;padding: 10px;white-space: pre-wrap;position: absolute;top: -17px;left: 105%;}.tooltip:hover .tooltiptext {visibility: visible;background: #424242;}.dataTables_wrapper .dataTables_filter input{border:1px solid #424242; border-radius: 7px; padding:6px;} #datatb_wrapper{padding-top:5px;padding-bottom: 10px;}#datatb_info{padding-left:10px}
</style>
<link rel="stylesheet" href="assets/datatables/datatables.min.css" />
<link rel="stylesheet" href="assets/datatables/buttons.datatables.min.css" />
<link rel="stylesheet" href="../quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
<div class="content-wrapper">
    <section class="content-header">
      
    </section>
	
	<h2 style="text-align:center">
		Sales Person Monthly Invoices
		<form action="salesPersonInvoices.php" method="get" class="col-md-4 col-md-offset-4" style="margin-bottom:20px; margin-top:15px;">
			<input type="month" name="date" class="form-control">
			<button type="submit" class="btn btn-success form-control">Submit</button>
		</form>
	</h2>

    <section class="content">
	    
		<div class="row">
			<div class="col-md-12">
				<div class="box box-primary">
		            <table class="table table-striped" id="datatb" style="margin-top:5px">
		            	<thead>
		            		<tr style="background-color: #424242; color: white;">
		            			<th>Client</th>
								<th>DBA</th>
		            			<th>Invoice No</th>
		            			<th>Invoice Date</th>
		            			<th>Shop Invoice No</th>
		            			<th>Shop Invoice Date</th>
		            			<th>Value</th>
								<th>Paid</th>
								<th>Remaining</th>
								<th>Settled</th>
								<th>SalesPerson</th>
							</tr>
		            	</thead>
		            	<tbody>
		            	<?php while($row = mysqli_fetch_assoc($res)){ ?>
						<tr>
					<!--		<?php
/*								$percent = $row['services'] == 1 ? 1.16:1.17;
								if($row['gst'] == "inclusive"){
									$row['price'] = $row['price']/$percent;
								}
							*/?>
						-->	<td><?php ec($row['client']); ?></td>
							<td><?php ec($row['dba']); ?></td>
							<td><?php ec($row['invoiceno']); ?></td>
							<td><?php ec($row['invoicedate']); ?></td>
							<td><?php ec($row['shopinvoiceno']); ?></td>
							<td><?php ec($row['invoicesdate']); ?></td>
							<td><?php ec((int)$row['price']); ?></td>
							<td><?php ec((int)$row['alloc']); ?></td>
							<td><?php ec((int)$row['price'] - (int)$row['alloc']); ?></td>
							<td><?php ec($row['settled']); ?></td>
							<td><?php ec($row['salesmanname']); ?></td>
						</tr>
		            	<?php } ?>	
		            	</tbody>
		            </table>
	        	</div>
			</div>
		</div>

    </section>

</div>

<?php
	include_once("includes/footer.php");
?>
<script src="assets/datatables/jquery.dataTables.min.js"></script>
<script src="assets/datatables/dataTables.buttons.min.js"></script>
<script src="assets/datatables/buttons.html5.min.js"></script>
<script>
	$(document).ready(function(){
		let datatable = $('#datatb').DataTable({
					dom: 'Bfrtip',
					buttons: [
			            'excelHtml5',
			            'csvHtml5',
			        ],
					language: {
				        search: "_INPUT_",
				        searchPlaceholder: "Search..."
				    }
				});
	});
</script>
<?php
	include_once("includes/foot.php");
?>