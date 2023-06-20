<?php 

	$active = "reports";
//	$AllowAnyone = true;

	include_once("config.php");
		if(!userHasPermission($db, 'outstanding_invoices')) {

			
			header("Location: /sahamid/v2/reportLinks.php");
			exit;
		}
	if(isset($_POST['update'])){
		$column = $_POST['column'];
		$value = $_POST['value'];
		$invoiceno = $_POST['invoice'];

		if($column == "state"){
			$SQL = "UPDATE debtortrans SET state='$value' WHERE transno='$invoiceno' AND type=10";
		}else{
		echo	$SQL = "UPDATE invoice SET $column='$value' WHERE invoiceno='$invoiceno'";
		}
		DB_query($SQL,$db);

		echo "success";
		return;
	}

	if(isset($_GET['json'])){
		$SQL = "SELECT  debtorsmaster.debtorno,
						debtorsmaster.name,
						debtorsmaster.dba,
				        invoice.shopinvoiceno,
				        invoice.invoicesdate,
				        invoice.branchcode,
				        invoice.invoiceno,
				        invoice.comment,
				        ROUND(debtortrans.ovamount) as total,
				        ROUND(debtortrans.alloc) as paid,
				        (
						CASE WHEN GSTwithhold = 0 AND WHT = 0 
							THEN ovamount - alloc
						WHEN GSTwithhold = 0 AND WHT = 1 
							THEN ovamount - alloc - WHTamt
						WHEN GSTwithhold = 1 AND WHT = 0 
							THEN ovamount - alloc - GSTamt
						WHEN GSTwithhold = 1 AND WHT = 1 
							THEN ovamount - alloc - GSTamt - WHTamt
						END
					) AS remaining  ,
				        salescase.salesman as salesperson,
				        invoice.salescaseref,
				        invoice.due,
				        invoice.expected,
				        debtortrans.state,
				        dcgroups.dcnos
				FROM debtorsmaster
				INNER JOIN invoice ON (invoice.debtorno = debtorsmaster.debtorno
                  	AND invoice.returned = 0)
                INNER JOIN salescase ON invoice.salescaseref=salescase.salescaseref
              	INNER JOIN dcgroups ON dcgroups.id = invoice.groupid
				INNER JOIN debtortrans ON (debtortrans.transno = invoice.invoiceno";
                $SQL.=" AND debtortrans.type = 10
                                AND debtortrans.reversed = 0
                                AND debtortrans.settled = 0)";
                if(!userHasPermission($db, "executive_listing")) {
                    $SQL.= ' INNER JOIN www_users ON salescase.salesman = www_users.realname ';

                }
                if(!userHasPermission($db, "executive_listing")) {
                    $SQL.= 'AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
                        OR  www_users.userid
                                IN (SELECT salescase_permissions.can_access FROM salescase_permissions
                                WHERE salescase_permissions.user = "'.$_SESSION['UserID'].'"'.')) ';

                }

        $res = mysqli_query($db, $SQL);
        $response = [];
        $today = time();
        
        while($row = mysqli_fetch_assoc($res)){
        	$debtorno = $row['debtorno'];
        	$branchcode = $row['branchcode'];
    		$transno=$row['invoiceno'];

        	$invoiceDate = strtotime($row['invoicesdate']);
        	$row['age'] = floor( ($today-$invoiceDate) / (60 * 60 * 24));
            $dcnos=[];
            $dcnos=explode(',',$row['dcnos']);

            $row['invoicelinks'].= '';
            $invoiceFiles=[];
            foreach ($dcnos as $dcno) {
                if($dcno != '')
                {
                    //echo '../' . $_SESSION['part_pics_dir'] . '/Invoice_' . $dcno . "*";
                    $invoiceFiles = glob('../' . $_SESSION['part_pics_dir'] . '/Invoice_' . $dcno . "*.pdf");
                    //print_r($invoiceFiles);
                    $index = 0;
                    foreach ($invoiceFiles as $invoiceFile) {
                        $index++;

                        $InvoiceFilePath = explode("../", $invoiceFile)[1];
                        $row['invoicelinks'].='<br /><a id="viewenquiry" style="width: available;" class="btn btn-warning" href = "' . $RootPath . '/' . $invoiceFile . '" target = "_blank" >' .  $row['shopinvoiceno'] . '</a>';

                    }
                }
            }


            $SQL = "SELECT ROUND(ABS(SUM(ovamount - alloc))) as unallocated FROM debtortrans WHERE settled=0 AND debtorno='$debtorno'
        	AND type='12'";


        	$row['unallocated'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['unallocated'];
        


      /*  	
        	$SQL = "SELECT salesman.salesmanname as salesperson
					FROM custbranch
					INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
					WHERE custbranch.branchcode = '$branchcode'";
        	$row['salesperson'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['salesperson'];
		*/
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
		<h1 class="text-center">Invoices Outstanding Report</h1>
    </section>

    <section class="content">
		<div class="row">
			<div class="col-md-12" style="overflow: scroll;">
				<div id="resultscontainer" class="" style=" background-color:#ecedf0">
					<table id="searchresults" width="100%" class="responsive table-striped">
						<thead>
							<tr style="background:#424242; color:white">
								<th class="fit">Debtor No</th>
								<th class="fit">Salescaseref</th>
								<th class="fit">Client</th>
								<th class="fit">DBA</th>
								<th class="fit">Shop Invoice No</th>
								<th class="fit">DC Nos</th>
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
                                <th class="fit">comment</th>
							</tr>
						</thead>
						<tbody id="srb" style="color: black">
							
						</tbody>
						<tfoot>
							<tr style="background:#424242; color:white">
								<th class="fit">Debtor No</th>
								<th class="fit">Salescaseref</th>
								<th class="fit">Client</th>
								<th class="fit">DBA</th>
								<th class="fit">Shop Invoice No</th>
								<th class="fit">DC Nos</th>
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
                                <th class="fit">comment</th>
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
		table=null;
		table=$('#searchresults').DataTable({

            <?php
            if(userHasPermission($db, "can_print_csv")){
                echo "dom: 'Bflrtip',
        buttons: [
                'excelHtml5',
                'csvHtml5',
            ],";}?>



			columns:[
				{"data":"debtorno"},
				{"data":"salescaseref"},
				{"data":"name"},
				{"data":"dba"},
				{"data":"invoicelinks"},
				{"data":"dcnos"},
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
                {"data":"comment"},
			],
			"columnDefs": [
				{ 
					className: "fit center", 
					"render": function ( data, type, row ) {
						let html='<a href="../salescase/salescaseview.php?salescaseref='+data+'" target="_blank">'+data+'</a>';
					return html;
					},
					"targets": [ 1 ] 
				},
				/*	{
					className: "fit center", 
					"render": function ( data, type, row ) {
					let html="";
					let dcs = row['dcnos'].split(",");
					dcs.forEach(function(dcno) {
					if(dcno!="")
  					html+='<a href = "../companies/sahamid/EDI_Incoming_Orders/Invoice_'+dcno+'.pdf" target="_blank">'+data+'</a><br/>';
					});
					return html;
					},
					"targets": [ 4 ] 
				},*/

				{ 
					className: "fit center", 
					"render": function ( data, type, row ) {
						return `
							<input type="date" class="datechange" data-column="due" data-invoice="${row['invoiceno']}" value="${data}"/>
							<span style="display:none">${data}</span>
						`;
					},
					"targets": [ 13 ] 
				},
				{ 
					className: "fit center", 
					"render": function ( data, type, row ) {
						return `
							<input type="date" class="datechange" data-column="expected" data-invoice="${row['invoiceno']}" value="${data}"/>
							<span style="display:none">${data}</span>
						`;
					},
					"targets": [ 14 ] 
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
					"targets": [ 15 ] 
				},
                {
                    className: "fit center",
                    "render": function ( data, type, row ) {
                        return `

							<textarea class="comment" data-column="comment" data-invoice="${row['invoiceno']}" value="${row['comment']}">${row['comment']}</textarea>

						`;
                    },
                    "targets": [ 16 ]
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
		     			<th></th>
		     			<th></th>
		     			<th>${api.column(9,{search:`applied`}).data().unique().sum()}</th>
		     			<th>${api.column(10,{search:`applied`}).data().sum()}</th>
		     			<th>${api.column(11,{search:`applied`}).data().sum()}</th>
		     			<th>${api.column(12,{search:`applied`}).data().sum()}</th>
		     			<th>--</th>
		     			<th>--</th>
                        <th>--</th>
		     			<th>--</th>
		     		</tr>`
		      	);
		    }
		});

		$.get("outstanding.php?json", function(res, status){
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
			$.post("outstanding.php",{column,value,invoice,update}, function(res, status){
				if(res == "success"){
					alert("Updated Successfully");
				}
			});
		});
        $(document.body).on("change", ".comment", function(){
            let ref = $(this);
            let column = ref.attr("data-column");
            let invoice = ref.attr("data-invoice");
            let value = ref.val();
            let update = true;
            $.post("outstanding.php",{column,value,invoice,update}, function(res, status){
                if(res == "success"){
                    alert("Updated Successfully");
                }
            });
        });

		// $('#searchresults tfoot th').each( function (i) {
	 //        let title = $('#searchresults thead th').eq( $(this).index() ).text(); 
	 //        if(title != "Action"){
	 //        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />' );  
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