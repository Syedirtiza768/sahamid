<?php


	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "salesperson_daily_activity_report")){
		header("Location: /sahamid");
		return;
	}

    if(isset($_POST['client'])){

//Indexing the dates

        $startDate = $_POST['from'];

        $endDate = $_POST['to'];
        $client=$_POST['client'];
        $client=implode(",",$client);
        $sdate = strtotime($startDate);
        $sdate = strtotime("-1 month", $sdate);
        $sdate1=date('Y-m-d', $sdate);
        $edate = strtotime($endDate);
        $rows = [];

        while ($sdate<$edate) {
            $sdate1=date('Y-m-d', $sdate);
            $sdate = strtotime("+1 month", $sdate);
            $sdate2=date('Y-m-d', $sdate);
            $month=date('Y-m', $sdate);
            $rows[$month]['month']=date('Y-m', $sdate);
            $SQL = "SELECT debtorsmaster.debtorno,debtorsmaster.dba,debtorsmaster.name as client, SUM(ovamount) as invoiceamt  
                FROM debtortrans 
                INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
                INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno 
                WHERE debtortrans.type=10 
                AND debtortrans.reversed=0
                
                AND invoice.invoicesdate >= '".$month."-01'
                AND invoice.invoicesdate <= '".$month."-31'
                AND debtorsmaster.debtorno IN ($client)
                GROUP BY debtorsmaster.debtorno";

            $res = mysqli_query($db, $SQL);
            while ($row=mysqli_fetch_assoc($res))
            {
                $rows[$month]['invoiceamt']=$row['invoiceamt'];


            }
            $SQL = 'SELECT dcs.orderno as dcno,dcs.orddate as date,dcs.salescaseref,debtorsmaster.name as client,
                salescase.salesman,debtorsmaster.dba,debtorsmaster.debtorno,dcs.invoicegroupid,SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
                ) as totalamount,dcs.gst, CASE  WHEN  dcs.gst LIKE "%inclusive%" THEN SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
                )*0.83 ELSE SUM(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity
                )   END as exclusivegsttotalamount from dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 
            INNER JOIN dcs on dcs.orderno = dcdetails.orderno
            INNER JOIN salescase on salescase.salescaseref=dcs.salescaseref
            INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
            
            AND dcs.orddate BETWEEN "'.$month."-01".'" AND "'.$month."-31".'"
            AND dcs.grbdate LIKE "0000-00-00 00:00:00"
            
            WHERE dcdetails.lineoptionno = 0  
                and dcoptions.optionno = 0
                 AND debtorsmaster.debtorno IN ('.$client.') 
            GROUP BY debtorsmaster.debtorno';
            $res = mysqli_query($db, $SQL);
            while ($row=mysqli_fetch_assoc($res))
            {
                $rows[$month]['dcamount']=$row['exclusivegsttotalamount'];

            }
           $SQL = "SELECT transid_allocfrom as f,transid_allocto as t,datealloc as d,SUM(amt) as amt, dt.trandate as cd,
                invoice.shopinvoiceno,invoiced.settled, invoiced.alloc as totalalloc, salesman.salesmanname,
                invoiced.ovamount as totalamt, debtorsmaster.name as client, debtorsmaster.dba, debtorsmaster.debtorno,invoice.invoicedate,invoice.invoicesdate
                FROM custallocns,debtortrans as invoiced
                INNER JOIN invoice on invoice.invoiceno = invoiced.transno
                INNER JOIN debtorsmaster ON debtorsmaster.debtorno = invoiced.debtorno
                INNER JOIN custbranch ON custbranch.branchcode = invoiced.branchcode
                INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
                INNER JOIN debtortrans as dt ON dt.debtorno = invoiced.debtorno
                WHERE dt.trandate >= '$month-01'
                AND dt.trandate <= '$month-31'
                AND invoiced.id = transid_allocto
                AND dt.id = transid_allocfrom
                AND invoiced.type = 10
                AND invoiced.reversed = 0
                 AND debtorsmaster.debtorno IN ($client)
                GROUP BY debtorsmaster.debtorno";

            $res = mysqli_query($db, $SQL);
            while ($row=mysqli_fetch_assoc($res))
            {
                $rows[$month]['receivedamt']=$row['amt'];
            }

            $SQL = 'SELECT DISTINCT  debtorsmaster.debtorno,debtorsmaster.dba,debtorsmaster.name as client,
                    SUM(
                        CASE WHEN GSTwithhold = 0 AND WHT = 0 
                            THEN ovamount - alloc
                        WHEN GSTwithhold = 0 AND WHT = 1 
                            THEN ovamount - alloc - WHTamt
                        WHEN GSTwithhold = 1 AND WHT = 0 
                            THEN ovamount - alloc - GSTamt
                        WHEN GSTwithhold = 1 AND WHT = 1 
                            THEN ovamount - alloc - GSTamt - WHTamt
                        END
                    ) AS outstanding  
                FROM debtortrans 
                INNER JOIN invoice ON invoice.invoiceno=debtortrans.transno 
                INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno 
                WHERE debtortrans.type=10 
                AND debtortrans.settled=0
                AND debtortrans.reversed=0
                AND debtorsmaster.debtorno IN ('.$client.')
                AND debtortrans.trandate<"'.$month."-01".'"
                GROUP BY debtorsmaster.debtorno';


            $res = mysqli_query($db, $SQL);
            //Array for complete data
            $dataraw=[];
            while ($row=mysqli_fetch_assoc($res))
            {
                $rows[$month]['outstanding']=$row['outstanding'];
            }




        }

        //

/////////////////////////////////////////
        $response=[];
        foreach($rows as $key=>$value)
        {
              $response[$key]['month']=$rows[$key]['month'];
              $response[$key]['outstanding']=round($rows[$key]['outstanding'],0);
              $response[$key]['dcamount']=round($rows[$key]['dcamount'],0);
              $response[$key]['invoiceamt']=round($rows[$key]['invoiceamt'],0);
              $response[$key]['receivedamt']=round($rows[$key]['receivedamt']);
              $response[$key]['client']=$client;
        }



        $finalresponse=[];
        foreach ($response as $key => $value)
        {
            $finalresponse[]=$value;
        }

        echo json_encode($finalresponse);
        //echo json_encode($invoicesdatewise);


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
			<h1>Clientwise Monthly Recovery Report</h1>
		</div>
        <label>From Date</label>
        <input type="date" class="date fromDate">
        <label>To Date</label>
        <input type="date" class="date toDate">



        <label style="margin-top: 15px;">Client </label>
        <select name="client" class="client" id="client" multiple>
            <?php
            $sql = "SELECT debtorno,name FROM debtorsmaster";
            $ErrMsg = _('The stock categories could not be retrieved because');
            $DbgMsg = _('The SQL used to retrieve stock categories and failed was');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
            echo '<option value="">Select Client</option>';
            while ($myrow=DB_fetch_array($result)) {

                    echo '<option value="' . "'".$myrow['debtorno'] ."'". '">' . $myrow['name'] . '</option>';

            }
            ?>
        </select>
    	<button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="searchresults">
			<thead>
				<tr>
                    <th>Month</th>
                    <th>Total Outstanding Balance</th>
                    <th>DCamount(Month)</th>
                    <th>Invoice Amount(Month)</th>
                    <th>Amount Received(Month)</th>
                </tr>
			</thead>
			<tfoot>
            <tr>
                <th>Month</th>
                <th>Total Outstanding Balance</th>
                <th>DCamount(Month)</th>
                <th>Invoice Amount(Month)</th>
                <th>Amount Received(Month)</th>
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

        let salesmanListSelect = $("#client").select2();
        let table = $('#searchresults').DataTable({
            "scrollY": "500px",
            "scrollX": true,
			dom: 'Bflrtip',
			"lengthMenu": [[ 10, 25, 50, 75, 100,500,1000 ], [10, 25, 50, 75, 100,500,1000 ]],
            buttons: [
                'csv'
            ],
			language: {
		        search: "_INPUT_",
		        searchPlaceholder: "Search..."
		    },

		    columns:[
				{"data":"month"},
                {"data":"outstanding"},
                {"data":"dcamount"},
                {"data":"invoiceamt"},
                {"data":"receivedamt"},


            ],
		"columnDefs": [


            {
                    "render": function ( data, type, row ) {
                       return data > 0  ?
                           data:
                           0;
                    },
                    className: 'text-center',
                    "targets": [1,2,3,4]
                },



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
	    	let client = $(".client").val();
	    	let from  = $(".fromDate").val();
            let to  = $(".toDate").val();
	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';

	    	table.clear().draw();
	    	$.post("clientwisemonthlyrecoveryreport.php",{client,from,to,FormID},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    	});
	    });


    });
</script>
<?php
	include_once("includes/foot.php");
?>