<?php


	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "salesperson_daily_activity_report")){
		header("Location: /sahamid");
		return;
	}

    if(isset($_POST['salesperson'])){

//Indexing the dates
        $from=date_create($_POST['from']);
        $to=date_create($_POST['to']);
        $salesperson=$_POST['salesperson'];
        $salespersons=implode(",",$salesperson);
        $diff=date_diff($from,$to);
        $datediff = $diff->format('%a');
        $response1=[];
        $dateformat=date_format($from,"Y-m-d");
        $dateformatFrom=date_format($from,"Y-m-d");
        $dateformatTo=date_format($to,"Y-m-d");
        $response1[$dateformat]['date'] = $dateformat;

        for ($i=0;$i<$datediff;$i++){
        for ($i=0;$i<$datediff;$i++){
           $from->modify('+1 day');

            $dateformat=date_format($from,"Y-m-d");
            $response1[$dateformat]['date'] = $dateformat;

        }

        //salescase indexing
        $SQL1="SELECT salescaseref,salesman FROM salescase WHERE salesman IN ($salespersons)";
        $res1=mysqli_query($db,$SQL1);
        $availablesalescases=[];
        while ($row1=mysqli_fetch_assoc($res1))
        {
            $availablesalescases[]="'".$row1['salescaseref']."'";

        }

        }
        $salescases=implode(",",$availablesalescases);

        //invoices
        $SQL2 = "SELECT invoice.invoiceno, invoice.invoicesdate FROM invoice 
                 WHERE invoice.returned=0 AND invoice.inprogress=0
                 AND invoice.invoicesdate BETWEEN  '$dateformatFrom' AND '$dateformatTo'
                 AND invoice.salescaseref IN ($salescases)";
        $res2 = mysqli_query($db, $SQL2);

        $response2= [];
        $invoicesdatewise=[];
        $availableinvoices=[];
        while($row2 = mysqli_fetch_assoc($res2)){
            $invoicedate=$row2['invoicesdate'];
            $invoicesdatewise[$invoicedate][]=$row2;
            $availableinvoices[]=$row2['invoiceno'];

        }
        $invoices=implode(",",$availableinvoices);
        //Invoice count and links
        $SQL4a = "SELECT invoicedetails.stkcode,invoicedetails.invoiceno,
                  invoicedetails.invoicelineno,invoicedetails.quantity as
                   itemQty,invoicedetails.unitprice,(1-invoicedetails.discountpercent) as discount
                from invoicedetails   
		        WHERE invoicedetails.invoiceno IN ($invoices)";

        $res4a = mysqli_query($db, $SQL4a);

        $response4a = [];
        while($row4a = mysqli_fetch_assoc($res4a)){
            $invoiceno=$row4a['invoiceno'];
            $response4a[$invoiceno][] = $row4a;
        }
        $SQL4b = "SELECT invoiceoptions.invoiceno,invoiceoptions.invoicelineno,
                  invoiceoptions.quantity
                  from invoiceoptions   
		          WHERE invoiceoptions.invoiceno IN ($invoices)";

        $res4b = mysqli_query($db, $SQL4b);

        $response4b = [];
        while($row4b = mysqli_fetch_assoc($res4b)){
            $invoiceno=$row4b['invoiceno'];
            $invoicelineno=$row4b['invoicelineno'];

            $response4b[$invoiceno][$invoicelineno] = $row4b;

        }


        foreach($response4a as $key=>$value)
        {
            $temparr=$response4a[$key];
            $count=sizeof($temparr);
            $response4a[$key]['invoiceValue']=0;
            for ($i=0;$i<$count;$i++)
            {
                $invoiceno=$response4a[$key][$i]['invoiceno'];
                $invoicelineno=$response4a[$key][$i]['invoicelineno'];
                $response4a[$key]['invoiceValue']+=
                    $response4a[$key][$i]['itemQty']*$response4a[$key][$i]['unitprice']*
                    $response4a[$key][$i]['discount']*$response4b[$invoiceno][$invoicelineno]['quantity'];

            }
        }

        foreach($invoicesdatewise as $key=>$value)
        {
            $temparr=$invoicesdatewise[$key];
            $count=sizeof($temparr);
            $invoicesdatewise[$key]['invoiceCount']=$count;
            $invoicesdatewise[$key]['invoiceLinks'].="";
            $invoicesdatewise[$key]['invoiceValue']=0;
            for ($i=0;$i<=$count;$i++)
            {
                $orderno=$invoicesdatewise[$key][$i]['invoiceno'];
                $invoicesdatewise[$key]['invoiceLinks'].=
                    "<a href='../PDFInvoice.php?InvoiceNo=$orderno'>".$invoicesdatewise[$key][$i]['invoiceno']."</a><br/>";
                $invoicesdatewise[$key]['invoiceValue']+=
                    $response4a[$invoicesdatewise[$key][$i]['invoiceno']]['invoiceValue'];

            }



        }




        //quotations
        $SQL3 = "SELECT salesorders.orderno, salesorders.orddate FROM salesorders 
                 WHERE salesorders.orddate BETWEEN  '$dateformatFrom' AND '$dateformatTo'
                 AND salesorders.salescaseref IN ($salescases)";
        $res3 = mysqli_query($db, $SQL3);

        $response3= [];
        $quotationsdatewise=[];
        $availablequotations=[];
        while($row3 = mysqli_fetch_assoc($res3)){
            $quotationsdate=$row3['orddate'];
            $quotationsdatewise[$quotationsdate][]=$row3;
            $availablequotations[]=$row3['orderno'];

        }
        $quotations=implode(",",$availablequotations);
        //Quotation count and links
        $SQL5a = "SELECT salesorderdetails.stkcode,salesorderdetails.orderno,
                  salesorderdetails.orderlineno,salesorderdetails.quantity as
                   itemQty,salesorderdetails.unitprice,(1-salesorderdetails.discountpercent) as discount
                from salesorderdetails   
		        WHERE salesorderdetails.orderno IN ($quotations)";

        $res5a = mysqli_query($db, $SQL5a);

        $response5a = [];
        while($row5a = mysqli_fetch_assoc($res5a)){
            $orderno=$row5a['orderno'];
            $response5a[$orderno][] = $row5a;
        }
        $SQL5b = "SELECT salesorderoptions.orderno,salesorderoptions.lineno,
                  salesorderoptions.quantity
                  from salesorderoptions   
		          WHERE salesorderoptions.optionno=0
		          AND salesorderoptions.orderno IN ($quotations)";

        $res5b = mysqli_query($db, $SQL5b);

        $response5b = [];
        while($row5b = mysqli_fetch_assoc($res5b)){
            $orderno=$row5b['orderno'];
            $orderlineno=$row5b['lineno'];

            $response5b[$orderno][$orderlineno] = $row5b;

        }


        foreach($response5a as $key=>$value)
        {
            $temparr=$response5a[$key];
            $count=sizeof($temparr);
            $response5a[$key]['quotationValue']=0;
            for ($i=0;$i<$count;$i++)
            {
                $orderno=$response5a[$key][$i]['orderno'];
                $orderlineno=$response5a[$key][$i]['orderlineno'];
                $response5a[$key]['quotationValue']+=
                    $response5a[$key][$i]['itemQty']*$response5a[$key][$i]['unitprice']*
                    $response5a[$key][$i]['discount']*$response5b[$orderno][$orderlineno]['quantity'];

            }
        }

        foreach($quotationsdatewise as $key=>$value)
        {
            $temparr=$quotationsdatewise[$key];
            $count=sizeof($temparr);
            $quotationsdatewise[$key]['quoteCount']=$count;
            $quotationsdatewise[$key]['quoteLinks'].="";
            $quotationsdatewise[$key]['quotationValue']=0;
            for ($i=0;$i<=$count;$i++)
            {
                $orderno=$quotationsdatewise[$key][$i]['orderno'];
                $quotationsdatewise[$key]['quoteLinks'].=
                    "<a href='../PDFQuotation.php?QuotationNo=$orderno'>".$quotationsdatewise[$key][$i]['orderno']."</a><br/>";
                $quotationsdatewise[$key]['quotationValue']+=
                    $response5a[$quotationsdatewise[$key][$i]['orderno']]['quotationValue'];

            }

        }
        //oc
        $SQL4 = "SELECT ocs.orderno, DATE_FORMAT(ocs.orddate,'%Y-%m-%d') as orddate FROM ocs 
                 WHERE ocs.orddate BETWEEN  '$dateformatFrom' AND '$dateformatTo'
                 AND ocs.salescaseref IN ($salescases)";
        $res4 = mysqli_query($db, $SQL4);

        $response4= [];
        $ocsdatewise=[

        ];
        $availableocs=[];
        while($row4 = mysqli_fetch_assoc($res4)){
            $ocsdate=$row4['orddate'];
            $ocsdatewise[$ocsdate][]=$row4;
            $availableocs[]=$row4['orderno'];

        }
        $ocs=implode(",",$availableocs);
        //OC count and links
        $SQL6a = "SELECT ocdetails.stkcode,ocdetails.orderno,
                  ocdetails.orderlineno,ocdetails.quantity as
                   itemQty,ocdetails.unitprice,(1-ocdetails.discountpercent) as discount
                from ocdetails   
		        WHERE ocdetails.orderno IN ($ocs)";

        $res6a = mysqli_query($db, $SQL6a);

        $response6a = [];
        while($row6a = mysqli_fetch_assoc($res6a)){
            $orderno=$row6a['orderno'];
            $response6a[$orderno][] = $row6a;

        }
        $SQL6b = "SELECT ocoptions.orderno,ocoptions.lineno,
                  ocoptions.quantity
                  from ocoptions   
		          WHERE ocoptions.optionno=0
		          AND ocoptions.orderno IN ($ocs)";

        $res6b = mysqli_query($db, $SQL6b);

        $response6b = [];
        while($row6b = mysqli_fetch_assoc($res6b)){
            $orderno=$row6b['orderno'];
            $orderlineno=$row6b['lineno'];

            $response6b[$orderno][$orderlineno] = $row6b;

        }


        foreach($response6a as $key=>$value)
        {
            $temparr=$response6a[$key];
            $count=sizeof($temparr);
            $response6a[$key]['ocValue']=0;
            for ($i=0;$i<$count;$i++)
            {
                $orderno=$response6a[$key][$i]['orderno'];
                $orderlineno=$response6a[$key][$i]['orderlineno'];
                $response6a[$key]['ocValue']+=
                    $response6a[$key][$i]['itemQty']*$response6a[$key][$i]['unitprice']*
                    $response6a[$key][$i]['discount']*$response6b[$orderno][$orderlineno]['quantity'];

            }
        }

        foreach($ocsdatewise as $key=>$value)
        {
            $temparr=$ocsdatewise[$key];
            $count=sizeof($temparr);
            $ocsdatewise[$key]['ocCount']=$count;
            $ocsdatewise[$key]['ocLinks'].="";
            $ocsdatewise[$key]['ocValue']=0;
            for ($i=0;$i<=$count;$i++)
            {
                $orderno=$ocsdatewise[$key][$i]['orderno'];
                $ocsdatewise[$key]['ocLinks'].=
                    "<a href='../PDFOC.php?OrderConfirmationNo=$orderno'>".$ocsdatewise[$key][$i]['orderno']."</a><br/>";
                $ocsdatewise[$key]['ocValue']+=
                    $response6a[$ocsdatewise[$key][$i]['orderno']]['ocValue'];

            }

        }
        //dc
        $SQL5 = "SELECT dcs.orderno, dcs.orddate FROM dcs 
                 WHERE dcs.orddate BETWEEN  '$dateformatFrom' AND '$dateformatTo'
                 AND dcs.salescaseref IN ($salescases)";
        $res5 = mysqli_query($db, $SQL5);

        $response5= [];
        $dcsdatewise=[];
        $availabledcs=[];
        while($row5 = mysqli_fetch_assoc($res5)){
            $dcsdate=$row5['orddate'];
            $dcsdatewise[$dcsdate][]=$row5;
            $availabledcs[]=$row5['orderno'];

        }
        $dcs=implode(",",$availabledcs);
        //DC count and links
        $SQL7a = "SELECT dcdetails.stkcode,dcdetails.orderno,
                  dcdetails.orderlineno,dcdetails.quantity as
                   itemQty,dcdetails.unitprice,(1-dcdetails.discountpercent) as discount
                from dcdetails   
		        WHERE dcdetails.orderno IN ($dcs)";

        $res7a = mysqli_query($db, $SQL7a);

        $response7a = [];
        while($row7a = mysqli_fetch_assoc($res7a)){
            $orderno=$row7a['orderno'];
            $response7a[$orderno][] = $row7a;

        }
        $SQL7b = "SELECT dcoptions.orderno,dcoptions.lineno,
                  dcoptions.quantity
                  from dcoptions   
		          WHERE dcoptions.optionno=0
		          AND dcoptions.orderno IN ($dcs)";

        $res7b = mysqli_query($db, $SQL7b);

        $response7b = [];
        while($row7b = mysqli_fetch_assoc($res7b)){
            $orderno=$row7b['orderno'];
            $orderlineno=$row7b['lineno'];

            $response7b[$orderno][$orderlineno] = $row7b;

        }


        foreach($response7a as $key=>$value)
        {
            $temparr=$response7a[$key];
            $count=sizeof($temparr);
            $response7a[$key]['dcValue']=0;
            for ($i=0;$i<$count;$i++)
            {
                $orderno=$response7a[$key][$i]['orderno'];
                $orderlineno=$response7a[$key][$i]['orderlineno'];
                $response7a[$key]['dcValue']+=
                    $response7a[$key][$i]['itemQty']*$response7a[$key][$i]['unitprice']*
                    $response7a[$key][$i]['discount']*$response7b[$orderno][$orderlineno]['quantity'];

            }
        }

        foreach($dcsdatewise as $key=>$value)
        {
            $temparr=$dcsdatewise[$key];
            $count=sizeof($temparr);
            $dcsdatewise[$key]['dcCount']=$count;
            $dcsdatewise[$key]['dcLinks'].="";
            $dcsdatewise[$key]['dcValue']=0;
            for ($i=0;$i<=$count;$i++)
            {
                $orderno=$dcsdatewise[$key][$i]['orderno'];
                $dcsdatewise[$key]['dcLinks'].=
                    "<a href='../PDFDCh.php?DCNo=$orderno'>".$dcsdatewise[$key][$i]['orderno']."</a><br/>";
                $dcsdatewise[$key]['dcValue']+=
                    $response7a[$dcsdatewise[$key][$i]['orderno']]['dcValue'];

            }

        }

/////////////////////////////////////////
        foreach($response1 as $key=>$value)
        {
            $response[$key]['date']=$response1[$key]['date'];
            $response[$key]['invoiceCount']=$invoicesdatewise[$key]['invoiceCount'];
            $response[$key]['invoiceLinks']=$invoicesdatewise[$key]['invoiceLinks'];
            $response[$key]['quoteCount']=$quotationsdatewise[$key]['quoteCount'];
            $response[$key]['invoiceValue']=round($invoicesdatewise[$key]['invoiceValue'],0);
            $response[$key]['quoteLinks']=$quotationsdatewise[$key]['quoteLinks'];
            $response[$key]['quotationValue']=round($quotationsdatewise[$key]['quotationValue'],0);




            $response[$key]['ocCount']=$ocsdatewise[$key]['ocCount'];
            $response[$key]['ocLinks']=$ocsdatewise[$key]['ocLinks'];
            $response[$key]['ocValue']=round($ocsdatewise[$key]['ocValue'],0);

            $response[$key]['dcCount']=$dcsdatewise[$key]['dcCount'];
            $response[$key]['dcLinks']=$dcsdatewise[$key]['dcLinks'];
            $response[$key]['dcValue']=round($dcsdatewise[$key]['dcValue'],0);
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
			<h1>Salesperson Daily Activity Report</h1>
		</div>
        <label>From Date</label>
        <input type="date" class="date fromDate">
        <label>To Date</label>
        <input type="date" class="date toDate">



        <label style="margin-top: 15px;">Salesperson </label>
        <select name="salesperson" class="salesperson" id="salesperson" multiple>
            <?php
            $sql = "SELECT salesmanname FROM salesman";
            $ErrMsg = _('The stock categories could not be retrieved because');
            $DbgMsg = _('The SQL used to retrieve stock categories and failed was');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
            echo '<option value="">Select salesman</option>';
            while ($myrow=DB_fetch_array($result)) {

                    echo '<option value="' . "'".$myrow['salesmanname'] ."'". '">' . $myrow['salesmanname'] . '</option>';

            }
            ?>
        </select>
    	<button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="searchresults">
			<thead>
				<tr>
                    <th>Date</th>
                    <th>Quotation Value</th>
                    <th>No. of Quotations</th>
                    <th>Quotation #</th>
                    <th>DC Value</th>
                    <th>No. of DCs</th>
                    <th>DC #</th>
                    <th>OC Value</th>
                    <th>No. of OCs</th>
                    <th>OC #</th>
                    <th>Invoice Value</th>
                    <th>No. of Invoices</th>
                    <th>Invoice #</th>
                </tr>
			</thead>
			<tfoot>
            <tr>
                <th>Date</th>
                <th>Quotation Value</th>
                <th>No. of Quotations</th>
                <th>Quotation #</th>
                <th>DC Value</th>
                <th>No. of DCs</th>
                <th>DC #</th>
                <th>OC Value</th>
                <th>No. of OCs</th>
                <th>OC #</th>
                <th>Invoice Value</th>
                <th>No. of Invoices</th>
                <th>Invoice #</th>

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

        let salesmanListSelect = $("#salesperson").select2();
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
				{"data":"date"},
                {"data":"quotationValue"},
                {"data":"quoteCount"},
                {"data":"quoteLinks"},
                {"data":"dcValue"},
                {"data":"dcCount"},
                {"data":"dcLinks"},
                {"data":"ocValue"},
                {"data":"ocCount"},
                {"data":"ocLinks"},
                {"data":"invoiceValue"},
                {"data":"invoiceCount"},
                {"data":"invoiceLinks"},



            ],
		"columnDefs": [


            {
                    "render": function ( data, type, row ) {
                       return data > 0  ?
                           data:
                           0;
                    },
                    className: 'text-center',
                    "targets": [1,2,4,5,7,8,11]
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
	    	let salesperson = $(".salesperson").val();
	    	let from  = $(".fromDate").val();
            let to  = $(".toDate").val();
	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';

	    	table.clear().draw();
	    	$.post("salespersondailyactivityreport.php",{salesperson,from,to,FormID},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    	});
	    });


    });
</script>
<?php
	include_once("includes/foot.php");
?>