<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "WHT_cumulative")){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['to'])){

		$from 	= $_POST['from'];
		$to 	= $_POST['to'];
		$Location	= $_POST['Location'];
        $SQL = 'SELECT debtortrans.id,
					debtortrans.debtorno,
					debtortrans.branchcode, 
					debtortrans.transno, 
					debtortrans.type, 
					DATE(debtortrans.trandate) as trandate, 
					debtortrans.reference, 
					debtortrans.order_, 
					-debtortrans.ovamount as ovamount, 
					debtortrans.alloc,
					debtortrans.WHT, 
					debtortrans.GSTwithhold, 
					debtortrans.WHTamt, 
					debtortrans.GSTamt, 
					debtortrans.GSTtotalamt, 
					debtortrans.settled, 
					debtortrans.invtext as chequeno,
					debtortrans.processed,
					debtortrans.reversed,
					debtortrans.reverseHistory,					
					dcgroups.dcnos,
					shopsale.payment,
					invoice.invoiceno,
					invoice.invoicedate,
					invoice.customerref,
					invoice.podate,
					invoice.invoicesdate,
					invoice.due,
					invoice.shopinvoiceno
			FROM debtortrans 
			LEFT OUTER JOIN invoice ON ( invoice.invoiceno = debtortrans.transno AND debtortrans.type=10 )
			LEFT OUTER JOIN shopsale ON ( shopsale.orderno = debtortrans.transno AND debtortrans.type=750 )
			LEFT OUTER JOIN bazar_parchi ON ( bazar_parchi.transno = debtortrans.transno 
				AND debtortrans.type=602 
				AND bazar_parchi.type=602 )
			LEFT OUTER JOIN dcgroups ON ( invoice.groupid = dcgroups.id )
			WHERE trandate >= "'.($from).'" 
			AND trandate <= "'.($to).'" 
			AND debtortrans.type=12
			ORDER BY debtortrans.id';

        $res = mysqli_query($db, $SQL);




        // AND invoice.due >= '$from'

		$response = [];
		while($row = mysqli_fetch_assoc($res)){
            $row['billNo'] = "(";
            if($row['type'] == 12){
                $customerStatement['unallocated'] += abs($row['ovamount'] - $row['alloc']);
                $SQL = "SELECT invoice.shopinvoiceno,debtortrans.type,debtortrans.transno 
					FROM custallocns 
					INNER JOIN debtortrans ON custallocns.transid_allocto = debtortrans.id
					INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
					WHERE custallocns.transid_allocfrom = '".$row['id']."'";
                $reSsS = mysqli_query($db, $SQL);
                while($resRow = mysqli_fetch_array($reSsS)){
                    if($resRow['type'] == 10)
                        $row['billNo'] .= $resRow['shopinvoiceno'] . ", ";
                    else
                        $row['billNo'] .= $resRow['transno'] . ", ";
                }
                $row['billNo'] .= ")";
            }
            $SQL = "SELECT SUM(WHTamt) as WHTamt FROM debtortrans WHERE processed='".$row['id']."' AND WHT=1";
            $r 	 = mysqli_query($db, $SQL);
            $row['WHTamtc']  = round((mysqli_fetch_assoc($r)['WHTamt'] ?:0),2);



            $SQL = "SELECT SUM(GSTamt) as GSTamt FROM debtortrans WHERE processed='".$row['id']."' AND GSTwithhold=1";
            $r 	 = mysqli_query($db, $SQL);
            $row['GSTamtc']  = round((mysqli_fetch_assoc($r)['GSTamt'] ?:0),2);

            $SQL = "SELECT name,dba FROM debtorsmaster WHERE debtorno='".$row['debtorno']."'";
            $r 	 = mysqli_query($db, $SQL);
            $rd=mysqli_fetch_assoc($r);
            $row['name']  = $rd['name'];
            $row['dba']  = $rd['dba'];
            $row['chqamount']= -(-$row['ovamount']- $row['WHTamtc']-$row['GSTamtc']);
            $SQL = "SELECT salesmanname FROM custbranch INNER JOIN salesman ON salesman.salesmancode=custbranch.salesman WHERE custbranch.debtorno='".$row['debtorno']."'";
            $r 	 = mysqli_query($db, $SQL);
            $rd=mysqli_fetch_assoc($r);
            $row['salesmanname']  = $rd['salesmanname'];
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
			<h1>WHT Cumulative</h1>
		</div>
		<label>From Date</label>
    	<input type="date" class="date fromDate">
		<label>To Date</label> 
    	<input type="date" class="date toDate">

    	<label hidden style="margin-top: 15px;">Location: </label>
	      				<select hidden name="Location" class="Location">
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
							<th>Date</th>
							<th>Client</th>
                            <th>DBA</th>
                            <th>Salesman</th>
							<th>Invoice#</th>
                            <th>Cheque#</th>
							<th>Cheque Amount</th>
							<th>WHT Amount</th>
                            <th>GST Witheld Amount</th>

						
				</tr>
			</thead>
			<tfoot>
				<tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>DBA</th>
                    <th>Salesman</th>
                    <th>Invoice#</th>
                    <th>Cheque#</th>
                    <th>Cheque Amount</th>
                    <th>WHT Amount</th>
                    <th>GST Witheld Amount</th>
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
				{"data":"trandate"},
				{"data":"name"},
                {"data":"dba"},
                {"data":"salesmanname"},
				{"data":"billNo"},

                {"data":"chequeno"},
				{"data":"chqamount"},
				{"data":"WHTamtc"},
                {"data":"GSTamtc"}
			],
            drawCallback: function () {
                var api = this.api();
                $( api.table().footer() ).html(
                    '<tr style="background-color:#424242; color:white">'+
                    '<th>--</th><th>--</th><th>--</th><th>--</th><th>--</th><th>--</th>'+
                    '<th>'+api.column(6,{search:'applied'}).data().sum()+'</th>'+
                    '<th>'+api.column(7,{search:'applied'}).data().sum()+'</th>'+
                    '<th>'+api.column(8,{search:'applied'}).data().sum()+'</th></tr>'               );
            }
/*		"columnDefs": [
	        	{
            		"render": function ( data, type, row ) {
                		
                		return parseInt(data).toLocaleString();
            		},
            		className: 'text-center',
            		"targets": 7
        		}
	        ]*/
	        
		});

		/*$('#datatable tfoot th').each( function (i) {
	        var title = $('#datatable thead th').eq( $(this).index() ).text(); 
	        if(title != "Amount" && title != "Statement"){
	        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />' );  
	        } 
	    });*/

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
            $("tbody tr td").html("<h3>Loading ... (This may take a few minutes) </h3>");
	    	$.post("WHTcumulative.php",{from,to,FormID,Location},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    	});
	    });

	});
</script>
<?php
	include_once("includes/foot.php");
?>