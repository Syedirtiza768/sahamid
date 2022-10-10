<?php 

	$active = "reports";
//	$AllowAnyone = true;

	include_once("config.php");

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
		        $clientID=$_GET['clientID'];
                $SQL.=" AND debtortrans.type = 10
                                AND debtortrans.reversed = 0
                                AND debtortrans.settled = 0)
                                AND invoice.branchcode LIKE '%$clientID%'";
            /*    if(!userHasPermission($db, "executive_listing")) {
                    $SQL.= ' INNER JOIN www_users ON salescase.salesman = www_users.realname ';

                }
                if(!userHasPermission($db, "executive_listing")) {
                    $SQL.= 'AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
                        OR  www_users.userid
                                IN (SELECT recovery_permissions.can_access FROM recovery_permissions
                                WHERE salescase_permissions.user = "'.$_SESSION['UserID'].'"'.')) ';

                }
        */
            $SQL.='AND debtorsmaster.debtorno IN 
            (SELECT can_access FROM recovery_access WHERE user = "'.$_SESSION['UserID'].'"'.') ';

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
        



        	$response[] = $row;
        }

        echo json_encode($response);
		return;
	}
        $SQL2 = "SELECT  debtorsmaster.debtorno,
                                debtorsmaster.name,
                                debtorsmaster.dba,
                                invoice.shopinvoiceno,
                                invoice.invoicesdate,
                                invoice.branchcode,
                                invoice.invoiceno,
                                count(*) as count,
                                invoice.comment,
                                ROUND(debtortrans.ovamount) as total,
                                ROUND(debtortrans.alloc) as paid,
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
        $SQL2.=" AND debtortrans.type = 10
                                        AND debtortrans.reversed = 0
                                        AND debtortrans.settled = 0)";
      /*  if(!userHasPermission($db, "executive_listing")) {
            $SQL2.= ' INNER JOIN www_users ON salescase.salesman = www_users.realname ';

        }
        if(!userHasPermission($db, "executive_listing")) {
            $SQL2.= 'AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
                                OR  www_users.userid
                                        IN (SELECT salescase_permissions.can_access FROM salescase_permissions
                                        WHERE salescase_permissions.user = "'.$_SESSION['UserID'].'"'.')) ';

        }*/
$SQL2.='AND debtorsmaster.debtorno IN 
            (SELECT can_access FROM recovery_access WHERE user = "'.$_SESSION['UserID'].'"'.') ';

$SQL2.="GROUP BY debtorsmaster.name
        ORDER BY count desc";

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

    .client{
        background: #efefef;
        padding: 7px;
        float:left;
        border: 1px solid #ccc;
        border-radius: 7px;
        display: flex;
        justify-content: space-between;
        cursor: pointer;
    }

    .client:hover{

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
    

    <section class="content" id="content">
        <h3 class="request-header">
            Recovery Alert
        </h3>
        <div class="form" id="form">


            <div class="request-body">
                <div class="client" data-client="">
                    <span>All</span>
                    <span></span>
                </div>
                <?php while($clients = mysqli_fetch_assoc($res2)){ ?>
                    <div class="client" data-client="<?php echo $clients['branchcode']; ?>">
                        <span><?php echo $clients['name']; ?></span>

                    </div>
                    <table style="background-color: lightgoldenrodyellow"><tr>

                            <td><?php echo "PKR ".locale_number_format(round($clients['remaining'],0)); ?></td>
                            <td style="text-align: right;"><?php echo $clients['count']; ?></td>
                        </tr>
                    </table>
                <?php } ?>
            </div>
        </div>
        <div class="request-container">





            <div class= "data" style="float: left; width:100%;" id="data">


                <div id="resultscontainer" class="" style=" background-color:#ecedf0;float: left;">

                    <table id="searchresults" width="100%" class="responsive table-striped">
                        <thead>
                        <tr style="background:#efefef; color:black">

                            <th class="fit">Client</th>
                            <th class="fit">SalesPerson</th>
                            <th class="fit">Shop Invoice No</th>
                            <th class="fit">Age</th>
                            <th class="fit">Remaining</th>

                        </tr>
                        </thead>
                        <tbody id="srb" style="color: black">

                        </tbody>
                        <tfoot>
                        <tr style="background:#efefef; color:white">

                            <th class="fit">Client</th>
                            <th class="fit">SalesPerson</th>
                            <th class="fit">Shop Invoice No</th>
                            <th class="fit">Age</th>
                            <th class="fit">Remaining</th>

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

				{"data":"name"},
                {"data":"salesperson"},
				{"data":"invoicelinks"},
                {"data":"age"},
                {"data":"remaining"},


			],
			"columnDefs": [



                /*  {
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
                      "targets": [ 2 ]
                  },*/
                {
                    className: "fit center",
                    "render": function ( data, type, row ) {

                        return parseInt(data);
                    },
                    "targets": [ 4 ]
                },


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



		     		</tr>`
		      	);
		    }
		});

		$.get("outstandingdashboard.php?json", function(res, status){
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
			$.post("outstandingdashboard.php",{column,value,invoice,update}, function(res, status){
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
            $.post("outstandingdashboard.php",{column,value,invoice,update}, function(res, status){
                if(res == "success"){
                    alert("Updated Successfully");
                }
            });
        });

		// $('#searchresults tfoot th').each( function (i) {
	 //        let title = $('#searchresults thead th').eq( $(this).index() ).text(); 
	 //        if(title != "Action"){
	 //        	$(this).html( '<input type="text" placeholder="Search '+title+'" data-index="'+i+'" style="border:1px solid #efefef; border-radius:7px; color:black; text-align:center" />' );
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
        var FormID = '<?php echo $_SESSION['FormID']?>';
        var clientID="";

        $(".client").on("click", function(){

            let ref = $(this);

            if(ref.hasClass("selected")){
                return;
            }

            $(".client").each(function(){
                $(this).removeClass("selected");
            });

            ref.addClass("selected");

            let getUserRequests = true;

            if (typeof ref.attr("data-client") === 'undefined') {
               clientID="";
            }
            else {
                clientID = ref.attr("data-client");
            }
            $.get("outstandingdashboard.php?json&clientID="+clientID, function(res, status){
                res = JSON.parse(res);
                table.clear().draw();
                table.rows.add(res).draw();
            });


        });


    </script>
    <script>
        $(document).ready(function () {
            let form = document.getElementById('form').style.maxHeight;
            let content = document.getElementById('content').style.height;
            if(form>content)
            {
                document.getElementById('form').style.maxHeight=content;
            }
            else
            {
                document.getElementById('content').style.height=form;
            }

        })

    </script>
<?php
	include_once("includes/foot.php");
?>