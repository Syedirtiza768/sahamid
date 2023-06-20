<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "top_items_report")){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['to'])){

		$from 	= $_POST['from'];
		$to 	= $_POST['to'];
		$Location	= $_POST['Location'];
		$SQL = 'SELECT stockmaster.stockid,stockmaster.mnfCode,stockmaster.mnfpno, stockmaster.description,manufacturers.manufacturers_name,
		AVG(shopsalesitems.rate) as materialcost,
		SUM(shopsalesitems.quantity) as itemQty,
		AVG(shopsale.discount*100) as averageInvoiceFactor,
			SUM((shopsalesitems.quantity*shopsalesitems.rate*(1-shopsale.discount/100))-shopsale.discountPKR) as 
			exclusivegsttotalamount from shopsalesitems 
		 INNER JOIN shopsale on shopsale.orderno = shopsalesitems.orderno
		 INNER JOIN stockmaster on stockmaster.stockid=shopsalesitems.stockid
	 	INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 	INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno

		 WHERE 
		 shopsale.orddate BETWEEN "'.$from.'" AND "'.$to.'"
		 AND debtorsmaster.debtorno LIKE "%'.$Location.'%"
		 GROUP BY shopsalesitems.stockid
		 ORDER BY exclusivegsttotalamount desc';
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
			<h1>Top Items</h1>
		</div>
		<label>From Date</label>
    	<input type="date" class="date fromDate">
		<label>To Date</label> 
    	<input type="date" class="date toDate">

    	<label style="margin-top: 15px;">Location: </label>
	      				<select name="Location" class="Location">
	      					<option value="">All</option>
	      					<option value="MT">MT</option>
	      					<option value="SR">SR</option>
	      					
	      				</select>
    	<button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="datatable">
			<thead>
				<tr>
							<th>Stock ID</th>
							<th>manufacturers Code</th>
							<th>Part No.</th>
							<th>Description</th>
							<th>Brand</th>
							<th>Quantiy</th>
							<th>Total Value</th>
						
				</tr>
			</thead>
			<tfoot>
				<tr>
							<th>Stock ID</th>
							<th>manufacturers Code</th>
							<th>Part No.</th>
							<th>Description</th>
							<th>Brand</th>
							<th>Quantiy</th>
							<th>Total Value</th>
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
			dom: 'Bflrtip',
			"lengthMenu": [ 10, 25, 50, 75, 100 ],
			buttons: [
	            'excelHtml5',
	            'csvHtml5',
	        ],
			language: {
		        search: "_INPUT_",
		        searchPlaceholder: "Search..."
		    },
		    				
			
		    columns:[
				{"data":"stockid"},
				{"data":"mnfCode"},
				{"data":"mnfpno"},
				{"data":"description"},
				{"data":"manufacturers_name"},
				{"data":"itemQty"},
				{"data":"exclusivegsttotalamount"},
			],
		/*	"columnDefs": [
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
	        */
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
	    	let Location  = $(".Location").val();
	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';

	    	table.clear().draw();
	    	$.post("topItems.php",{from,to,FormID,Location},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    	});
	    });

	});
</script>
<?php
	include_once("includes/foot.php");
?>