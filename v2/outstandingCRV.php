<?php 

	$active = "reports";
	//$AllowAnyone = true;
	$PathPrefix='../';

	include_once("config.php");
		if(!userHasPermission($db, 'outstanding_CRV')) {

			
			header("Location: /sahamid/v2/reportLinks.php");
			exit;
		}
	if(isset($_POST['update'])){
		$column = $_POST['column'];
		$value = $_POST['value'];
		$invoiceno = $_POST['invoice'];

		if($column == "state"){
			$SQL = "UPDATE debtortrans SET state='$value' WHERE transno='$invoiceno' AND type=750";
		}else{
			$SQL = "UPDATE shopsale SET $column='$value' WHERE orderno='$invoiceno'";
		}
		DB_query($SQL,$db);

		echo "success";
		return;
	}

	if(isset($_GET['json'])){
		$SQL = "SELECT  debtorsmaster.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba,
				        shopsale.orderno as shopinvoiceno,
				        shopsale.orddate as invoicesdate,
				        shopsale.branchcode,
				        shopsale.orderno as invoiceno,
				        ROUND(debtortrans.ovamount) as total,
				        ROUND(debtortrans.alloc) as paid,
				        ROUND(debtortrans.ovamount - debtortrans.alloc) as remaining,
				        shopsale.salesman as salesperson,
				        shopsale.due,
				        shopsale.expected,
				        debtortrans.state
				FROM debtorsmaster
				INNER JOIN shopsale ON (shopsale.debtorno = debtorsmaster.debtorno
					AND shopsale.payment = 'crv')
				INNER JOIN debtortrans ON (debtortrans.transno = shopsale.orderno
                    AND debtortrans.type = 750
                   	AND debtortrans.reversed = 0
                  	AND debtortrans.settled = 0)";
        $res = mysqli_query($db, $SQL);
        $response = [];
        $today = time();
        while($row = mysqli_fetch_assoc($res)){
        	$debtorno = $row['debtorno'];
        	$branchcode = $row['branchcode'];

        	$invoiceDate = strtotime($row['invoicesdate']);
        	$row['age'] = floor( ($today-$invoiceDate) / (60 * 60 * 24));

        	$SQL = "SELECT ROUND(ABS(SUM(ovamount - alloc))) as unallocated FROM debtortrans WHERE settled=0 AND debtorno='$debtorno' AND type='12'";
        	$row['unallocated'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['unallocated'];
        	
        	$SQL = "SELECT salesman.salesmanname as salesperson
					FROM custbranch
					INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
					WHERE custbranch.branchcode = '$branchcode'";
        	$row['salesperson'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['salesperson'];

        	$response[] = $row;
        }

        echo json_encode($response);
		return;
	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>
<style>
	th,td{
		text-align: center;
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
		<h1 class="text-center">CRV Outstanding Report</h1>
    </section>

    <section class="content">
		<div class="row">
			<div class="col-md-12" style="overflow: scroll;">
				<div id="resultscontainer" class="" style=" background-color:#ecedf0">
					<table id="searchresults" width="100%" class="responsive table-striped">
						<thead>
							<tr style="background:#424242; color:white">
								<th class="fit">Debtor No</th>
								<th class="fit">Client</th>
								<th class="fit">DBA</th>
								<th class="fit">Order No</th>
								<th class="fit">Order Date</th>
								<th class="fit">Age</th>
								<th class="fit">SalesPerson</th>
								<th class="fit">UnAllocated</th>
								<th class="fit">Value</th>
								<th class="fit">Paid</th>
								<th class="fit">Remaining</th>
								<th class="fit">Due Date</th>
								<th class="fit">Expected Date</th>
								<th class="fit">Status</th>
							</tr>
						</thead>
						<tbody id="srb" style="color: black">
							
						</tbody>
						<tfoot>
							<tr style="background:#424242; color:white">
								<th class="fit">Debtor No</th>
								<th class="fit">Client</th>
								<th class="fit">DBA</th>
								<th class="fit">Shop Invoice No</th>
								<th class="fit">Shop Invoice Date</th>
								<th class="fit">Age</th>
								<th class="fit">SalesPerson</th>
								<th class="fit">UnAllocated</th>
								<th class="fit">Value</th>
								<th class="fit">Paid</th>
								<th class="fit">Remaining</th>
								<th class="fit">Due Date</th>
								<th class="fit">Expected Date</th>
								<th class="fit">Status</th>
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
	<script>
		tabel=null;
		table=$('#searchresults').DataTable({
			dom: 'Bfrtip',
		    buttons: [
		        'csv'
		    ],
			columns:[
				{"data":"debtorno"},
				{"data":"name"},
				{"data":"dba"},
				{"data":"shopinvoiceno"},
				{"data":"invoicesdate"},
				{"data":"age"},
				{"data":"salesperson"},
				{"data":"unallocated"},
				{"data":"total"},
				{"data":"paid"},
				{"data":"remaining"},
				{"data":"due"},
				{"data":"expected"},
				{"data":"state"},
			],
			"columnDefs": [
				{ 
					className: "fit center", 
					"render": function ( data, type, row ) {
						return `
							<input type="date" class="datechange" data-column="due" data-invoice="${row['invoiceno']}" value="${data}"/>
						`;
					},
					"targets": [ 11 ] 
				},
				{ 
					className: "fit center", 
					"render": function ( data, type, row ) {
						return `
							<input type="date" class="datechange" data-column="expected" data-invoice="${row['invoiceno']}" value="${data}"/>
						`;
					},
					"targets": [ 12 ] 
				},
				{ 
					className: "fit center", 
					"render": function ( data, type, row ) {
						return `
							<select class="datechange" data-column="state" data-invoice="${row['invoiceno']}">
								<option value="" ${(data == "") ? "selected":""}>
									Default
								</option>
								<option value="Total Loss" ${(data == "Total Loss") ? "selected":""}>
									Total Loss
								</option>
								<option value="Bad Debt Recoverable" ${(data == "Bad Debt Recoverable") ? "selected":""}>
									Bad Debt Recoverable
								</option>
								<option value="Bad Debt Less Recoverable" ${(data == "Bad Debt Less Recoverable") ? "selected":""}>
									Bad Debt Less Recoverable
								</option>
							</select>
						`;
					},
					"targets": [ 13 ] 
				},
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
		     			<th></th>
		     			<th></th>
		     			<th>${api.column(7,{search:`applied`}).data().sum()}</th>
		     			<th>${api.column(8,{search:`applied`}).data().sum()}</th>
		     			<th>${api.column(9,{search:`applied`}).data().sum()}</th>
		     			<th>${api.column(10,{search:`applied`}).data().sum()}</th>
		     			<th>--</th>
		     			<th>--</th>
		     			<th>--</th>
		     		</tr>`
		      	);
		    }
		});

		$.get("outstandingCRV.php?json", function(res, status){
			res = JSON.parse(res);
	    	table.clear().draw();	
	    	table.rows.add(res).draw();
		});
		
		$(document.body).on("change", ".datechange", function(){
			let ref = $(this);
			let column = ref.attr("data-column");
			let invoice = ref.attr("data-invoice");
			let value = ref.val();
			let update = true;
			$.post("outstandingCRV.php",{column,value,invoice,update}, function(res, status){
				if(res == "success"){
					alert("Updated Successfully");
				}
			});
		});

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