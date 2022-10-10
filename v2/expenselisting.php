<?php 

	$active = "reports";
	$AllowAnyone = true;

	include_once("config.php");

    if(isset($_GET['tab'])){
        $tab=$_GET['tab'];
        $SQL="SELECT SUM(amount) as unauthorized FROM pcashdetails WHERE tabcode='$tab' AND authorized='0000-00-00'";
        $unauthorized=-1*mysqli_fetch_assoc(mysqli_query($db,$SQL))['unauthorized'];
        $SQL="SELECT SUM(amount) as balance FROM pcashdetails WHERE tabcode='$tab'";
        $balance=mysqli_fetch_assoc(mysqli_query($db,$SQL))['balance'];

        echo "<div style='margin-left: 25%;'><table><tr><td>Title: $tab</td></tr><tr><td>Unauthorized: PKR $unauthorized </td></tr><tr><td>Balance: PKR $balance</td></tr></table></div>";
        return;
            }


	if(isset($_GET['json'])){
	    $tabcode=$_GET['tabcode'];
		$SQL = "SELECT * FROM pcashdetails INNER JOIN pcexpenses ON pcashdetails.codeexpense=pcexpenses.codeexpense
        WHERE pcashdetails.tabcode = '$tabcode'
		
		";
		$user=$_SESSION['UserID'];

            $SQL.="AND pcashdetails.tabcode IN 
            (SELECT can_access FROM expense_listing_access WHERE user = '$user') 
            ORDER BY date desc";

        $res = mysqli_query($db, $SQL);
        $response = [];
        $today = time();
        
        while($row = mysqli_fetch_assoc($res)){
        	$response[] = $row;
        }

        echo json_encode($response);
		return;
	}
        $user=$_SESSION['UserID'];
        $SQL2 = "SELECT * FROM pcashdetails
            WHERE pcashdetails.tabcode IN 
            (SELECT can_access FROM expense_listing_access WHERE user = '$user')
            GROUP BY tabcode
            ORDER BY date desc ";



        $res2 = mysqli_query($db, $SQL2);

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>
<style>
	th,td{
		text-align: left;

	}

	#searchresults_length label select {
		color: black;

	}

	#searchresults_length,#searchresults_info{
		color: #efefef;
	}

	#searchresults_filter label,.datatables-footer,.datatables-header{
		width: 100%;
        float: right;
        

	}
    input{
        float: right;
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
		border: 1px black solid;
		width: 1%;
	}
    .request-container{
        display:flex;
        justify-content: center;
        margin: 15px;
        margin-top: 15px;
        margin-bottom: 50px;
    }

    .footer{
        background:#efefef;
        bottom:0; width:100%;
        text-align:center;
        padding: 5px;
        position: fixed;
    }

    .form{
        float: left;
        display: flex;
        flex-direction: column;

        width: 300px;
        height: 400px;

        overflow: scroll;
    }

    .request-header{
        text-align: center;
        background: #e0e0e0;
        border-radius: 10px 10px 0 0;
        padding: 5px;
        margin-bottom: 0;
    }

    .request-header button{
        padding: 1px 10px !important;
    }

    .request-body{
        background: white;
        padding: 25px 15px;
        display: flex;
        flex-direction: column;
    }

    .request-body label{
        margin: 10px 0;
    }

    .input{
        width: 100%;
        border: 1px solid #ccc;
        padding: 5px;
        
    }

    .request-submit{
        border-radius: 0 0 7px 7px;
    }

    .existing-requests{
        flex: 1;
        margin-left: 10px;
        height: calc(100vh - 150px);
        overflow: hidden;
        overflow-y: scroll;
    }

    .request{
        display: flex;
        background: #efefef;
        margin-bottom: 5px;
    }

    .request .details{
        flex: 1;
        padding: 5px;
    }

    .request .details p{
        margin-bottom: 0;
        display: inline-block;
    }

    .tabcode{
        background: #efefef;
        padding: 7px;
        float:left;
        border: 1px solid #ccc;
        border-radius: 7px;
        display: flex;
        justify-content: space-between;
        cursor: pointer;
    }

    .tabcode:hover{

        background: green;
        color: white;

    }

    .selected{
        background: #efefef;
        color: green;
    }

    .qtyInput{
        width: 80px;
        border-radius: 7px;
        padding: 4px;
    }

	#scrollUp{
		position: fixed;
		bottom: 50px;
		right: 0;
		padding:10px;
		color: white;
		background: #efefef;
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
		width: 2%;
	}
	.datechange{
	    width: 135px;
	    padding: 5px;
	    border-radius: 7px;
	}

</style>
<input type="hidden" value="<?php ec($_SESSION['FormID']); ?>" id="FormID">
<div class="content-wrapper">
    

    <section class="content" id="content">
        <h3 class="request-header">
            Expense Listing
        </h3>
        <h4 class="tab-title" style="text-align: center; color: green">

        </h4>
        <div class="form" id="form">


            <div class="request-body">

                    <div class="search" ><input class="tabcodesearch" style="width: 100%; border: lightgrey;" placeholder="Search" type = "text"></div>

                <?php while($tabcodes = mysqli_fetch_assoc($res2)){ ?>
                    <div class="tabcode" data-tabcode="<?php echo $tabcodes['tabcode']; ?>">
                        <span  id="<?php echo $tabcodes['tabcode']; ?>"><?php echo '<span hidden>'.strtolower($tabcodes['tabcode'])."</span> ".$tabcodes['tabcode']; ?></span>


                    </div>

                <?php } ?>
            </div>

        </div>
        <div class="request-container">





            <div class= "data" style="float: left; width:100%;" id="data">


                <div id="resultscontainer" class="" style=" background-color:#ecedf0;float: left;">

                    <table id="searchresults" width="100%" class="responsive table-striped">
                        <thead>
                        <tr style="background:#efefef; color:black">

                            <th class="fit">Date</th>
                            <th class="fit">Description</th>
                            <th class="fit">Amount</th>
                            <th class="fit">Authorized</th>
                            <th class="fit">Last Reading</th>
                            <th class="fit">Meter Reading</th>

                            <th class="fit">Average</th>
                            <th class="fit">Notes</th>
                            <th class="fit">Receipt</th>
                        </tr>
                        </thead>
                        <tbody id="srb" style="color: black">

                        </tbody>
                        <tfoot>
                        <tr style="background:#efefef; color:white">


                            <th class="fit">Date</th>
                            <th class="fit">Description</th>
                            <th class="fit">Amount</th>
                            <th class="fit">Authorized</th>
                            <th class="fit">Last Reading</th>
                            <th class="fit">Meter Reading</th>

                            <th class="fit">Average</th>
                            <th class="fit">Notes</th>
                            <th class="fit">Receipt</th>

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
            "pageLength": -1,
            <?php
            if(userHasPermission($db, "can_print_csv")){
                echo "dom: 'Bfrtip',
                
        buttons: [
                
            ],";}?>



			columns:[

				{"data":"date"},
                {"data":"description"},
				{"data":"amount"},
                {"data":"authorized"},
                {"data":"lastreading"},
                {"data":"meterreading"},
                {"data":"meterreading"},
                {"data":"notes"},
                {"data":"receipt"},


			],
			"columnDefs": [



                {
                    className: "fit center",
                    "render": function ( data, type, row ) {

                        let html=data;
                        if(row['lastreading']==0)
                        html="N/A";

                        return html;
                    },
                    "targets": [ 4 ]
                },
                {
                    className: "fit center",
                    "render": function ( data, type, row ) {

                        let html=data;
                        if(row['meterreading']==0)
                            html="N/A";

                        return html;
                    },
                    "targets": [ 5 ]
                },

                {
                    className: "fit center",
                    "render": function ( data, type, row ) {
                        let average="N/A";
                        if(row['meterreading']>0)
                        average=parseFloat(row['amount'])/(parseFloat(row['lastreading'])-parseFloat(row['meterreading']));

                        let html=`${average}`;

                        return html;
                    },
                    "targets": [ 6 ]
                },
                {
                    className: "fit center",
                    "render": function ( data, type, row ) {
                        let html="";
                        let receipt = row['counterindex'];
                                html+='<a href = "../companies/sahamid/EDI_Incoming_Orders/receipt_'+receipt+'.pdf" target="_blank">'+receipt+'</a><br/>';
                        return html;
                    },
                    "targets": [ 8 ]
                },
                /*{
                    className: "fit center",
                    "render": function ( data, type, row ) {

                        return parseInt(data);
                    },
                    "targets": [ 4 ]
                },
*/

			],
			drawCallback: function () {
		      	let api = this.api();
		     	$( api.table().footer() ).html(
		     		`<tr style="background-color:#efefef; color:white">
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>





		     		</tr>`
		      	);
		    }
		});

		$.get("expenselisting.php?json", function(res, status){
			res = JSON.parse(res);
	    	table.clear().draw();	
	    	table.rows.add(res).draw();
		});
        $(document.body).on("keyup", ".tabcodesearch", function(){
            let ref=$(this).val();
            //console.log(ref);

            $('.tabcode').hide();

            let r=ref.toLowerCase();
            console.log(r);
            $( `span:contains('${r}')` ).parent().show();



        });



        table.columns().every( function () {
	        let that = this;
	        $('input', this.footer()).on('keyup change', function (){
	            if(that.search() !== this.value){
	                that.search(this.value).draw();
	            }
	        });
	    });
        var FormID = '<?php echo $_SESSION['FormID']?>';
        var tabcode="";

        $(".tabcode").on("click", function(){

            let ref = $(this);

            if(ref.hasClass("selected")){
                return;
            }

            $(".tabcode").each(function(){
                $(this).removeClass("selected");
            });


            ref.addClass("selected");

            let getUserRequests = true;

            if (typeof ref.attr("data-tabcode") === 'undefined') {
               tabcode="";
            }
            else {
                tabcode = ref.attr("data-tabcode");
            }

                $.get("expenselisting.php?tab="+tabcode, function(res, status){
                    $(".tab-title").each(function(){
                    $(this).html(res);
                    });
                });


            $.get("expenselisting.php?json&tabcode="+tabcode, function(res, status){
                res = JSON.parse(res);
                table.clear().draw();
                table.rows.add(res).draw();
            });


        });


    </script>



<?php
	include_once("includes/foot.php");
?>