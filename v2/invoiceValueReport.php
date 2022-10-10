<?php 

	$active = "dashboard";

	include_once("config.php");
	
	if(isset($_GET['json'])){
		
		$response = [];
		
		$SQL = "SELECT  invoice.invoiceno,
						invoice.shopinvoiceno,
						custbranch.brname,
						debtorsmaster.dba,
						salesman.salesmanname as salesman,
						invoice.invoicesdate,
						debtortrans.ovamount as amount
				FROM invoice 
				INNER JOIN debtortrans ON (debtortrans.type = 10
											AND debtortrans.transno = invoice.invoiceno
											AND debtortrans.reversed = 0)
				INNER JOIN custbranch ON invoice.branchcode = custbranch.branchcode
				INNER JOIN debtorsmaster ON invoice.debtorno = debtorsmaster.debtorno
				INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
				WHERE invoice.returned = 0";
				
		if(isset($_GET['month']) && $_GET['month'] != ""){
			
			$SQL .= " AND invoice.invoicesdate >= '".$_GET['month']."-01'";
			$SQL .= " AND invoice.invoicesdate <= '".$_GET['month']."-31'";
			
		}
			
		$res = mysqli_query($db, $SQL);
		
		$count = 1;
		while($row = mysqli_fetch_assoc($res)){
			$row['sr']  = $count++;
			$response[] = $row;
		}
		
		echo json_encode($response);
		return;
		
	}
	
	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>
<script>
	var datatablet = null;
</script>
<link rel="stylesheet" href="../salescase/assets/selectSalescase.css"/>
<div class="content-wrapper">
    <section class="content-header">
		<h2 style="text-align:center">Invoice Value Report</h2>
		<div style="display:flex; justify-content:space-between;">
			<div>
				<input type="month" id="selectedMonth" style="border:1px solid #ccc; border-radius:7px"> 
			</div>
			<div>
				<button class="btn btn-primary" id="searchResults">Submit</button>
			</div>
		</div>
    </section>

    <section class="content">
	    
		<div class="row">
			
			<div class="col-md-12">
				
				<table class="table table-bordered table-striped mb-none" id="datatable">
					<thead>
						<tr style="background-color: #424242; color: white">
							<th class="fit">Sr#</th>
							<th class="fit">Invoice#</th>
							<th class="fit">ShopInvoice#</th>
							<th class="fit">Client</th>
							<th class="fit">DBA</th>
							<th class="fit">Salesman</th>
							<th class="fit">Date</th>
							<th class="fit">Amount</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
					<tfoot>
						<tr style="background-color: #424242; color: white">
							<th class="fit">Sr#</th>
							<th class="fit">Invoice#</th>
							<th class="fit">ShopInvoice#</th>
							<th class="fit">Client</th>
							<th class="fit">DBA</th>
							<th class="fit">Salesman</th>
							<th class="fit">Date</th>
							<th class="fit">Amount</th>
						</tr>
					</tfoot>
				</table>
				
			</div>
		
		</div>

    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>
<script>
	
	var datatableInit = function() {
		
		datatablet = $('#datatable').DataTable({
			language: {
				search: "_INPUT_",
				searchPlaceholder: "Search..."
			},
			columns:[
				{ data: "sr"},
				{ data: "invoiceno"},
				{ data: "shopinvoiceno"},
				{ data: "brname"},
				{ data: "dba"},
				{ data: "salesman"},
				{ data: "invoicesdate"},
				{ data: "amount"}
			],
			"pageLength": 10
			/* <?php echo $_SESSION['DefaultDisplayRecordsMax']; ?>*/
		});

		//$('#datatable_length')
		//	.find("label")
		//	.html("<h3 style='margin:0; padding:0; font-variant: petite-caps;'>Invoice</h3>");


	};
	
	$("#searchResults").on("click",function(){
		
		//if($(this).attr("data-inprogress") == "true"){
		//	alert("Request In Progress.");
		//	return;
		//}
		
		$(this).attr("data-inprogress","true");
		
		let month = $("#selectedMonth").val();
		
		if(datatablet == null){
			datatableInit();
			$("tbody tr td").html("Loading...");
		}
		
		datatablet.clear().draw();
		$("tbody tr td").html("<h4 style='text-align:center;'>Loading...</h4>");
		
		$.get("invoiceValueReport.php?json&month="+month, function(res, status){
			datatablet.rows.add(JSON.parse(res)).draw(false);
			$(this).attr("data-inprogress","false");
		});
		
	});
	
</script>
<?php
	include_once("includes/foot.php");
?>