<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "cart_report")){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['FormID'])){

		$from 	= $_POST['from'];
		$to 	= $_POST['to'];
		$Location	= $_POST['Location'];
        $sql = "SELECT distinct stockissuance.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						stockissuance.issued as issued,
						stockissuance.returned as returned,
						stockissuance.dc as dc,
						stockmaster.materialcost,
						stockissuance.salesperson,
						stockmaster.discount,
						stockissuance.issued*stockmaster.materialcost*(1-stockmaster.discount) as totalValue,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled
					FROM stockissuance,
						stockmaster,
						locations,
						manufacturers,
						stockcategory
					WHERE stockissuance.stockid=stockmaster.stockid
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						
							
							
					AND stockmaster.brand like '%" . $_GET['brand'] . "%'
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockissuance.issued>0
					AND stockcategory.categorydescription like "."'%".$_GET['StockCat']."%'"."
		
					
					ORDER BY stockissuance.issued desc";



        $res = DB_query($sql, $db, $ErrMsg, $DbgMsg);


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
			<h1>Cart Report</h1>
		</div>

    	<button class="btn btn-success date searchData">Search</button>
  </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="datatable">
			<thead>
				<tr>

                            <th>Salesperson</th>
							<th>StockID</th>
							<th>Manufacturer Code</th>
							<th>Part Number</th>
							<th>Brand</th>
                            <th>Description</th>
							<th>Quantiy</th>
							<th>Unit list price</th>
                            <th>Discount</th>
                            <th>Total Value</th>


						
				</tr>
			</thead>
			<tfoot>
				<tr>
                            <th>Salesperson</th>
                            <th>StockID</th>
                            <th>Manufacturer Code</th>
                            <th>Part Number</th>
                            <th>Brand</th>
                            <th>Description</th>
                            <th>Quantiy</th>
                            <th>Unit list price</th>
                            <th>Discount</th>
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
		        {"data":"salesperson"},
				{"data":"stockid"},
				{"data":"mnfCode"},
				{"data":"mnfpno"},
                {"data":"manufacturers_name"},
				{"data":"description"},
                {"data":"materialcost"},
				{"data":"issued"},
                {"data":"discount"},
				{"data":"totalValue"},
			],
		"columnDefs": [
	        	{
            		"render": function ( data, type, row ) {
                		
                		return parseInt(data);
            		},
            		className: 'text-center',
            		"targets": 7
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

	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';

	    	table.clear().draw();
	    	$.post("cartreport.php",{FormID},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    	});
	    });

	});
</script>
<?php
	include_once("includes/foot.php");
?>