<?php 

	$active = "reports";
    $AllowAnyone = true;

	include_once("config.php");
		if(!userHasPermission($db, 'outstanding_invoices')) {

			
			header("Location: /sahamid/v2/reportLinks.php");
			exit;
		}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>
<style>
	th,td{

	}

	#searchresults_length label select {
		color: black;
	}

	#searchresults_length,#searchresults_info{
		color: black;
	}

	#searchresults_filter label,.datatables-footer,.datatables-header{
		width: 100%;
	}

	#searchresults thead th{
		border: 1px white solid;
		border-bottom: 0px;
	}

	#searchresults tfoot th{
		border: 1px white solid;
		border-top: 0px;
	}

	#searchresults td{
		border: 1px #424242 solid;
		width: 1%;
	}

	#scrollUp{
		position: fixed;
		bottom: 50px;
		right: 0;
		padding:10px;
		color: white;
		background: #424242;
	}

	.inp{
	    border: 1px solid #E5E7E9;
		border-radius: 6px;
		height: 46px;
		padding: 12px;
		outline: none;
	}

	.actinf{
		font-size: 10px;
	}

	.fit{
		width: 1%;
	}
	.datechange{
	    width: 135px;
	    padding: 5px;
	    border-radius: 7px;
	}
</style>
<input type="hidden" value="<?php ec($_SESSION['FormID']); ?>" id="FormID">
<div class="content-wrapper">
    
	<section class="content-header">
		<h1 class="text-center">Salesperson Targets Report</h1>
    </section>

    <section class="content">
		<div class="row">
			<div class="col-md-12" style="overflow: scroll;">
				<div id="resultscontainer" class="" style=" background-color:#ecedf0">
					<table id="searchresults" width="100%" class="responsive table-striped">
						<thead>

							<tr style="background:#424242; color:white">
								<th class="fit">Customer Group</th>
								<th class="fit">Salesman</th>
								<th class="fit">Target 2018</th>
								<th class="fit">Achieved</th>
								<th class="fit">Percentage</th>
                                <th class="fit">Target 2019</th>
                            </tr>
						</thead>
						<tbody id="srb" style="color: black">
							
						</tbody>
						<tfoot>
							<tr style="background:#424242; color:white">
                                <th class="fit">Customer Group</th>
                                <th class="fit">Salesman</th>
                                <th class="fit">Target 2018</th>
                                <th class="fit">Achieved</th>
                                <th class="fit">Percentage</th>
                                <th class="fit">Target 2019</th>
                            </tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>
    <th class="fit">Customer Group</th>
    <th class="fit">Salesman</th>
    <th class="fit">Target</th>
    <th class="fit">Achieved</th>
    <th class="fit">Percentage</th>
	<script>
		tabel=null;
		table=$('#searchresults').DataTable({
			dom: 'Bfrtip',
		    buttons: [
		        'csv'
		    ],
			columns:[
				{"data":"alias"},
				{"data":"salesmanname"},
				{"data":"target"},
				{"data":"achieved"},
				{"data":"percentage"},
                {"data":"nextTarget"},

			],
			drawCallback: function () {
		      	let api = this.api();
		     	$( api.table().footer() ).html(
		     		`<tr style="background-color:#424242; color:white">
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     		</tr>`
		      	);
		    }
		});

		$.get("api/salesmancustomertargetsreport.php?json", function(res, status){
			res = JSON.parse(res);
	    	table.clear().draw();	
	    	table.rows.add(res).draw();
		});
		


		// $('#searchresults tfoot th').each( function (i) {
	 //        let title = $('#searchresults thead th').eq( $(this).index() ).text(); 
	 //        if(title != "Action"){
	 //        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black;" />' );
	 //        } 
	 //    });

	    table.columns().every( function () {
	        let that = this;
	        $('input', this.footer()).on('keyup change', function (){
	            if(that.search() !== this.value){
	                that.search(this.value).draw();
	            }
	        });
	    });
	</script>
<?php
	include_once("includes/foot.php");
?>