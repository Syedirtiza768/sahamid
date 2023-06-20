<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

	if(!userHasPermission($db, "top_items_quotation_report")){
		header("Location: /sahamid");
		return;
	}

	if(isset($_POST['to'])) {

        $from = $_POST['from'];
        $to = $_POST['to'];
        $Location = $_POST['Location'];
       $SQL = "CREATE VIEW refA AS SELECT stockid,MAX(stkmoveno) as stkmoves,loccode FROM stockmoves 
        
        WHERE trandate<='$from' AND trandate>='2016-01-01' AND loccode IN ('HO','MT','SR') GROUP BY stockid,loccode";
        mysqli_query($db, $SQL);
        $SQL="SELECT stockmaster.stockid,manufacturers.manufacturers_name,
        stockmaster.description,stockmaster.materialcost,
        stockmaster.mnfCode,stockmaster.mnfpno,loccode,SUM(newqoh) as qohA FROM stockmoves INNER JOIN stockmaster
        ON stockmaster.stockid=stockmoves.stockid  INNER JOIN manufacturers 
        ON manufacturers.manufacturers_id=stockmaster.brand 
        WHERE stkmoveno IN (SELECT stkmoves FROM refA)
        GROUP BY stockid";

        $res = mysqli_query($db, $SQL);



        $response1 = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $stkcde = $row['stockid'];
            $response1[$stkcde] = $row;
        }

        $SQL="DROP VIEW refA";
        mysqli_query($db, $SQL);

      $SQL = 'SELECT stockmaster.stockid,
		AVG(invoicedetails.unitprice*(1-invoicedetails.discountpercent)) as invoicecost,
		AVG(invoicedetails.discountpercent*100) as averageInvoiceFactor,SUM(invoicedetails.quantity*invoiceoptions.quantity) as invoiceitemQty,

	 	SUM(CASE WHEN invoice.gst LIKE "%inclusive%" THEN  
	 	(invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity )
	 	 ELSE (invoicedetails.unitprice* (1 - invoicedetails.discountpercent)*invoicedetails.quantity*invoiceoptions.quantity)*1.17 END) 
	 	 as invoiceexclusivegsttotalamount from invoicedetails INNER JOIN invoiceoptions on (invoicedetails.invoiceno = invoiceoptions.invoiceno 
	 	 AND invoicedetails.invoicelineno = invoiceoptions.invoicelineno) INNER JOIN invoice on invoice.invoiceno = invoicedetails.invoiceno
	 	  INNER JOIN salescase on salescase.salescaseref=invoice.salescaseref INNER JOIN stockmaster on stockmaster.stockid=invoicedetails.stkcode
	 	   INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 	    INNER JOIN debtorsmaster on debtorsmaster.debtorno=salescase.debtorno
	 	    
		 WHERE invoice.inprogress = 0
	 	AND invoice.returned = 0
		
		 AND invoice.invoicesdate > "'.$from.'" 
		
		  AND debtorsmaster.debtorno LIKE "%'.$Location.'%"
		 GROUP BY invoicedetails.stkcode';
        $res = mysqli_query($db, $SQL);

        // AND invoice.due >= '$from'

        $response2 = [];
        while($row = mysqli_fetch_assoc($res)){
            $stkcde=$row['stockid'];
            $response2[$stkcde] = $row;
        }
        foreach ($response1 as $key=>$value)
        {

            $response1[$key]['averageInvoiceFactor']=$response2[$key]['averageInvoiceFactor'];
            $response1[$key]['invoiceitemQty']=$response2[$key]['invoiceitemQty'];
            $response1[$key]['invoiceexclusivegsttotalamount']=$response2[$key]['invoiceexclusivegsttotalamount'];
        }
        $SQL = 'SELECT stockmaster.stockid,stockmaster.mnfCode,stockmaster.mnfpno, stockmaster.materialcost as listprice, stockmaster.description,manufacturers.manufacturers_name,
		AVG(shopsalesitems.rate) as materialcost,
		SUM(shopsalesitems.quantity*shopsalelines.quantity) as itemQtyShopsale,
		AVG(shopsale.discount*100) as averageShopSaleFactor,
			SUM((shopsalesitems.quantity*shopsalesitems.rate*(1-shopsalesitems.discountpercent/100))*shopsalelines.quantity) as 
			shopsaletotalamount from shopsalesitems 
		INNER JOIN shopsalelines on (shopsalelines.orderno = shopsalesitems.orderno AND shopsalelines.id=shopsalesitems.lineno)
		 INNER JOIN shopsale on shopsale.orderno = shopsalesitems.orderno
		 INNER JOIN stockmaster on stockmaster.stockid=shopsalesitems.stockid
	 	INNER JOIN manufacturers on manufacturers.manufacturers_id=stockmaster.brand
	 	INNER JOIN debtorsmaster on debtorsmaster.debtorno=shopsale.debtorno

		 WHERE 
		 shopsale.orddate BETWEEN "'.$from.'" AND "'.$to.'"
		 AND debtorsmaster.debtorno LIKE "%'.$Location.'%"
		 GROUP BY shopsalesitems.stockid';
        $res = mysqli_query($db, $SQL);
        $response3 = [];
        while($row = mysqli_fetch_assoc($res)){
            $stkcde=$row['stockid'];
            $response3[$stkcde] = $row;
        }
        foreach ($response1 as $key=>$value)
        {

            $response1[$key]['averageShopSaleFactor']=$response3[$key]['averageShopSaleFactor'];
            $response1[$key]['itemQtyShopsale']=$response3[$key]['itemQtyShopsale'];
            $response1[$key]['shopsaletotalamount']=$response3[$key]['shopsaletotalamount'];
        }
        $SQL = "CREATE VIEW refB AS SELECT stockid,MAX(stkmoveno) as stkmoves,loccode FROM stockmoves 
        
        WHERE trandate<='$to' AND trandate>='2016-01-01' AND loccode IN ('HO','MT','SR') GROUP BY stockid,loccode";
        mysqli_query($db, $SQL);
        $SQL="SELECT stockid,SUM(newqoh) as qohB FROM stockmoves
        WHERE stkmoveno IN (SELECT stkmoves FROM refB)
        GROUP BY stockid";

        $res = mysqli_query($db, $SQL);



        $response4 = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $stkcde = $row['stockid'];
            $response4[$stkcde] = $row;
        }

        $SQL="DROP VIEW refB";
        mysqli_query($db, $SQL);
        foreach ($response1 as $key=>$value)
        {

            $response1[$key]['qohB']=$response4[$key]['qohB'];

        }
        $SQL = 'SELECT stockmaster.stockid,
		AVG(dcdetails.unitprice*(1-dcdetails.discountpercent)) as dccost,
		AVG(dcdetails.discountpercent*100) as averageDCFactor,SUM(dcdetails.quantity*dcoptions.quantity) as dcitemQty,

	 	SUM(CASE WHEN dcs.gst LIKE "%inclusive%" THEN  
	 	(dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity )
	 	 ELSE (dcdetails.unitprice* (1 - dcdetails.discountpercent)*dcdetails.quantity*dcoptions.quantity)*1.17 END) 
	 	 as dcexclusivegsttotalamount from dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno 
	 	 AND dcdetails.orderlineno = dcoptions.lineno) INNER JOIN dcs on dcs.orderno = dcdetails.orderno
	 	  INNER JOIN stockmaster on stockmaster.stockid=dcdetails.stkcode
	 	   AND dcs.orddate BETWEEN "'.$from.'" AND "'.$to.'"
		  		 GROUP BY dcdetails.stkcode';
        $res = mysqli_query($db, $SQL);

        // AND invoice.due >= '$from'

        $response5 = [];
        while($row = mysqli_fetch_assoc($res)){
            $stkcde=$row['stockid'];
            $response5[$stkcde] = $row;
        }
        foreach ($response1 as $key=>$value)
        {

            $response1[$key]['dcitemQty']=$response5[$key]['dcitemQty'];
            $response1[$key]['dcexclusivegsttotalamount']=$response5[$key]['dcexclusivegsttotalamount'];
        }

        $response=[];
        foreach ($response1 as $key=>$value)
        {
            $response[]=$value;
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

<div class="content-wrapper" style="overflow: scroll;">
    
	<section class="content-header">
		<div class="col-md-12">
			<h1>Cross Sectional Stock Analysis</h1>
		</div>
		<label>From Date</label>
    	<input type="date" class="date fromDate">
		<label>To Date</label> 
    	<input type="date" class="date toDate">

    	<label hidden style="margin-top: 15px;">Location: </label>
	      				<select hidden name="Location" class="Location">
	      					<option value="">All</option>
	      					<option value="MT">MT</option>
                            <option value="HO">HO</option>
	      					<option value="SR">SR</option>
	      					
	      				</select>
    	<button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content"  >
	
		<table class="table table-striped table-responsive" border="1" id="datatable">
			<thead>
				<tr>
							<th>Stock ID</th>
							<th>manufacturers Code</th>
							<th>Part No.</th>
							<th>Description</th>
							<th>Brand</th>
                            <th>Int. Ref. Quantiy</th>
                            <th>DC Quantiy</th>
                            <th>DC Value</th>
                            <th>Inv. Quantiy</th>
                            <th>Inv. Value</th>
                            <th>Inv. Factor</th>
                            <th>Shop Sale Quantiy</th>
                            <th>Shop Sale Value</th>
                            <th>Shop Sale Factor</th>
                            <th>Final Ref. Quantity</th>
                            <th>Current List Price</th>
						
				</tr>
			</thead>
			<tfoot>
            <tr>
                <th>Stock ID</th>
                <th>manufacturers Code</th>
                <th>Part No.</th>
                <th>Description</th>
                <th>Brand</th>
                <th>Int. Ref. Quantiy</th>
                <th>DC Quantiy</th>
                <th>DC Value</th>
                <th>Inv. Quantiy</th>
                <th>Inv. Value</th>
                <th>Inv. Factor</th>
                <th>Shop Sale Quantiy</th>
                <th>Shop Sale Value</th>
                <th>Shop Sale Factor</th>
                <th>Final Ref. Quantity</th>
                <th>Current List Price</th>

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
                {"data":"qohA"},
                {"data":"dcitemQty"},
                {"data":"dcexclusivegsttotalamount"},
                {"data":"invoiceitemQty"},
                {"data":"invoiceexclusivegsttotalamount"},
                {"data":"averageInvoiceFactor"},
                 {"data":"itemQtyShopsale"},
                {"data":"shopsaletotalamount"},
                {"data":"averageShopSaleFactor"},
                {"data":"qohB"},
                {"data":"materialcost"}

			],
		"columnDefs": [
            {
                "render": function ( data, type, row ) {
                    data = data ? data : 0;
                    return parseInt(data).toLocaleString();

                },
                className: 'text-center',
                "targets": 6
            },
                {
                    "render": function ( data, type, row ) {
                        data = data ? data : 0;
                        return parseInt(data).toLocaleString();

                    },
                    className: 'text-center',
                    "targets": 7
                },
                {
                    "render": function ( data, type, row ) {
                        data = data ? data : 0;
                        return parseInt(data).toLocaleString();

                    },
                    className: 'text-center',
                    "targets": 8
                },
                {
                    "render": function ( data, type, row ) {
                        data = data ? data : 0;
                        return parseInt(data).toLocaleString();

                    },
                    className: 'text-center',
                    "targets": 9
                },
                {
                    "render": function ( data, type, row ) {
                        data = data ? data : 0;
                        return parseInt(data).toLocaleString();

                    },
                    className: 'text-center',
                    "targets": 10
                },
                {
                    "render": function ( data, type, row ) {
                        data = data ? data : 0;
                        return parseInt(data).toLocaleString();

                    },
                    className: 'text-center',
                    "targets": 11
                },
                {
                    "render": function ( data, type, row ) {
                        data = data ? data : 0;
                        return parseInt(data).toLocaleString();

                    },
                    className: 'text-center',
                    "targets": 12
                },
            {
                "render": function ( data, type, row ) {
                    data = data ? data : 0;
                    return parseInt(data).toLocaleString();

                },
                className: 'text-center',
                "targets": 13
            },
            {
                "render": function ( data, type, row ) {
                    data = data ? data : 0;
                    return parseInt(data).toLocaleString();

                },
                className: 'text-center',
                "targets": 14
            },
            {
                "render": function ( data, type, row ) {
                    data = data ? data : 0;
                    return parseInt(data).toLocaleString();

                },
                className: 'text-center',
                "targets": 15
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
	    	let from  = $(".fromDate").val();
	    	let to  = $(".toDate").val();
	    	let Location  = $(".Location").val();
	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';

	    	table.clear().draw();
            $("tbody tr td").html("<h3>Loading ... (This may take a few minutes) </h3>");
	    	$.post("crosssection.php",{from,to,FormID,Location},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    	});
	    });

    });
</script>
<?php
	include_once("includes/foot.php");
?>