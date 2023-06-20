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

        $startDate = $_POST['from'];

        $endDate = $_POST['to'];
        $salesperson=$_POST['salesperson'];
        $stockid=$_POST['stockid'];
        $rows = [];

        $sql = "SELECT stockmoves.stockid,
				stockmaster.mnfCode,
				stockmaster.mnfpno,
        		systypes.typename,
        		stockmoves.type,
        		stockmoves.transno,
        		stockmoves.trandate,
        		stockmoves.debtorno,
        		stockmoves.branchcode,
        		stockmoves.qty,
        		stockmoves.reference,
        		stockmoves.price,
        		stockmoves.discountpercent,
        		stockmoves.newqoh,
        		stockmaster.decimalplaces
        	FROM stockmoves
        	INNER JOIN systypes ON stockmoves.type=systypes.typeid
        	INNER JOIN stockmaster ON stockmoves.stockid=stockmaster.stockid
        	WHERE  stockmoves.trandate >= '". $startDate . "'
        	AND stockmoves.trandate <= '" . $endDate . "'
        	AND stockmoves.stockid = '" . $StockID . "'
        	AND hidemovt=0
        	ORDER BY stkmoveno DESC";


/////////////////////////////////////////
        $response=[];
        foreach($rows as $key=>$value)
        {

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
			<h1>Salespersonwise Stock Movements</h1>
		</div>
        <label>From Date</label>
        <input type="date" class="date fromDate">
        <label>To Date</label>
        <input type="date" class="date toDate">


        <label style="margin-top: 15px;">Salesperson </label>
        <select name="salesperson" class="salesperson" id="salesperson">
            <?php
            $sql = "SELECT www_users.realname FROM www_users";
            $ErrMsg = _('The stock categories could not be retrieved because');
            $DbgMsg = _('The SQL used to retrieve stock categories and failed was');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
            echo '<option value="">Select salesperson</option>';
            while ($myrow=DB_fetch_array($result)) {

                echo '<option value="' . "'".$myrow['realname'] ."'". '">' . $myrow['realname'] . '</option>';

            }
            ?>
        </select>


        <label style="margin-top: 15px;">Itemcode </label>
        <select name="itemcode" class="itemcode" id="itemcode">
            <?php
            $sql = "SELECT DISTINCT stockmoves.stockid FROM stockmoves";
            $ErrMsg = _('The stock categories could not be retrieved because');
            $DbgMsg = _('The SQL used to retrieve stock categories and failed was');
            $result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
            echo '<option value="">Select Item</option>';
            while ($myrow=DB_fetch_array($result)) {

                    echo '<option value="' . "'".$myrow['stockid'] ."'". '">' . $myrow['stockid'] . '</option>';

            }
            ?>
        </select>
    	<button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content">
	
		<table class="table table-striped table-responsive" border="1" id="searchresults">
			<thead>
				<tr>
                    <th>Type</th>
                    <th>Number</th>
                    <th>Date</th>
                    <th>Quantity</th>
                    <th>Reference</th>
                    <th>New Qty</th>
                </tr>
			</thead>
			<tfoot>
            <tr>
                <th>Type</th>
                <th>Number</th>
                <th>Date</th>
                <th>Quantity</th>
                <th>Reference</th>
                <th>New Qty</th>
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
        let itemcodeListSelect = $("#itemcode").select2();
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
	    	let salesperson = $(".salesperson").val();
	    	let from  = $(".fromDate").val();
            let to  = $(".toDate").val();
	    	let FormID = '<?php echo $_SESSION['FormID']; ?>';

	    	table.clear().draw();
	    	$.post("salespersonwisestockmovements.php",{salesperson,from,to,FormID},function(res, status){
	    		res = JSON.parse(res);
	    		table.rows.add(res).draw();
	    	});
	    });


    });
</script>
<?php
	include_once("includes/foot.php");
?>