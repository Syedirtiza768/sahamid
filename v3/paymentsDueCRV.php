<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "payments_due_report_crv")){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['to'])){

		$from 	= $_POST['from'];
		$to 	= $_POST['to'];

		$SQL = "SELECT  debtorsmaster.debtorno,
						debtorsmaster.name as customerName,
						debtorsmaster.dba,
						salesman.salesmanname as salesman,
						ROUND(SUM(debtortrans.ovamount - debtortrans.alloc)) as amount 
				FROM debtortrans
				INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
				INNER JOIN custbranch ON custbranch.branchcode = debtortrans.branchcode
				INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
				WHERE debtortrans.type = 750
				AND shopsale.due <= '$to'
				AND shopsale.payment = 'crv'
				AND debtortrans.reversed = 0
				GROUP BY debtortrans.debtorno";
		$res = mysqli_query($db, $SQL);

		// AND invoice.due >= '$from'

		$response = [];
		while($row = mysqli_fetch_assoc($res)){
			$response[] = $row;
		}

		echo json_encode($response);
		return;

	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>

<style>
	.date{
		padding:10px;
		border-radius: 7px;
	}
	thead tr, tfoot tr{
		background-color: #424242;
		color:white;
	}
</style>

<div class="content-wrapper">
    
	<section class="content-header">
		<div class="col-md-12">
			<h1>Payments Due CRV</h1>
		</div>
		<!-- <label>From Date</label>
    	<input type="date" class="date fromDate">
		<label>To Date</label> -->
    	<input type="date" class="date toDate">
    	<button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="datatable">
			<thead>
				<tr>
					<th>Debtor No</th>
					<th>Customer Name</th>
					<th>DBA</th>
					<th>Salesman</th>
					<th>Amount</th>
					<th>Statement</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Debtor No</th>
					<th>Customer Name</th>
					<th>DBA</th>
					<th>Salesman</th>
					<th>Amount</th>
					<th>Statement</th>
				</tr>
			</tfoot>
		</table>
	
    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>
<script>
	$(document).ready(function(){
		let table = $('#datatable').DataTable({
			dom: 'Bfrtip',
			buttons: [
	            'excelHtml5',
	            'csvHtml5',
	        ],
			language: {
		        search: "_INPUT_",
		        searchPlaceholder: "Search..."
		    },
		    columns:[
				{"data":"debtorno"},
				{"data":"customerName"},
				{"data":"dba"},
				{"data":"salesman"},
				{"data":"amount"},
				{"data":"amount"},
			],
			"columnDefs": [
	        	{
            		"render": function ( data, type, row ) {
                		let html = `
							<form action="/sahamid/reports/balance/custstatement/../../../customerstatement.php" method="post" target="_blank">
								<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>">
								<input type="hidden" name="cust" value="${row['debtorno']}">
								<input type="submit" value="Customer Statement" class="btn-info" style="padding: 8px; border:1px #424242 solid;">
							</form>
                		`;
                		return html;
            		},
            		className: 'text-center',
            		"targets": 5
        		}
	        ]
		});

		$('#datatable tfoot th').each( function (i) {
	        var title = $('#datatable thead th').eq( $(this).index() ).text(); 
	        if(title != "Amount" && title != "Statement"){
	        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />' );  
	        } 
	    });

	    table.columns().every( function () {
	        var that = this;
	        $('input', this.footer()).on('keyup change', function (){
	            if(that.search() !== this.value){
	                that.search(this.value).draw();
	            }
	        });
	    });

	    $(".searchData").on("click", function(){
	    	let from  = $(".fromDate").val();
	    	let to  = $(".toDate").val();
	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';

	    	table.clear().draw();
	    	$.post("paymentsDueCRV.php",{from,to,FormID},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    	});
	    });

	});
</script>
<?php
	include_once("includes/foot.php");
?>