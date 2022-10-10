<?php 

	$active = "dashboard";

	include_once("config.php");
	
	if(isset($_GET['date'])){
		
		$startDate = date($_GET['date'].'-01');
		
		$currentMonth = date('m');
		$selectedMonth = explode("-",$_GET['date'])[1];
		
		$difference = 0;
		if($currentMonth > $selectedMonth) $difference = $currentMonth - $selectedMonth;
		
		if($difference > 0){
			$endDate = date('Y-m-t',strtotime((-1*$difference)." month",strtotime(date("F") . "1")));
		}else{
			$endDate = date($_GET['date'].'-t');
		}
		
	}else{
		$startDate = date('Y-m-01 00:00:00');
		$endDate = date('Y-m-t 23:59:59');
	}

$SQL = "SELECT transid_allocfrom as f,transid_allocto as t,datealloc as d,SUM(amt) as amt, dt.trandate as cd, 
			invoice.shopinvoiceno,invoiced.settled, invoiced.alloc as totalalloc, salesman.salesmanname,
			invoiced.ovamount as totalamt, debtorsmaster.name, debtorsmaster.debtorno,invoice.invoicedate,invoice.invoicesdate
			FROM custallocns,debtortrans as invoiced
			INNER JOIN invoice on invoice.invoiceno = invoiced.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoiced.debtorno
			INNER JOIN custbranch ON custbranch.branchcode = invoiced.branchcode
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			INNER JOIN debtortrans as dt ON dt.debtorno = invoiced.debtorno
			WHERE dt.trandate >= '$startDate'
			AND dt.trandate <= '$endDate'
			AND invoiced.id = transid_allocto
			AND dt.id = transid_allocfrom
			AND invoiced.type = 10
			AND invoiced.reversed = 0
			GROUP BY invoice.invoiceno";

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
		Monthly Recoveries <br>
		<form action="recoveries.php" method="get" class="col-md-4 col-md-offset-4" style="margin-bottom:20px; margin-top:15px;">
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
                                <th>Code</th>
		            			<th>Salesman</th>
								<th>From</th>
								<th>Invoice No</th>
								<th>Invoice Date</th>
		            			<th>Shop Invoice No</th>
								<th>Shop Invoice Date</th>
								<th>Settled</th>
								<th>Date</th>
								<th>Cheque Date</th>
								<th>Total Amt</th>
		            			<th>Alloc Amt</th>
								<th>Total Alloc</th>
							</tr>
		            	</thead>
		            	<tbody>
		            	<?php while($row = mysqli_fetch_assoc($res)){ ?>
						<tr>
							<td><?php ec($row['name']); ?></td>
                            <td><?php ec($row['debtorno']); ?></td>
							<td><?php ec($row['salesmanname']); ?></td>
							<td><?php ec($row['f']); ?></td>
							<td><?php ec($row['t']); ?></td>
							<td><?php ec($row['invoicedate']); ?></td>
							<td><?php ec($row['shopinvoiceno']); ?></td>
							<td><?php ec($row['invoicesdate']); ?></td>
							<td><?php ec($row['settled']); ?></td>
							<td><?php ec($row['d']); ?></td>
							<td><?php ec(date('Y-m-d', strtotime($row['cd']))); ?></td>
							<td><?php ec((int)$row['totalamt']); ?></td>
							<td><?php ec((int)$row['amt']); ?></td>
							<td><?php ec((int)$row['totalalloc']); ?></td>
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