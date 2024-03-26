<?php

$active = "reports";


include_once("config.php");
if (!userHasPermission($db, "cart_report")) {
    header("Location: /sahamid");
    return;
}
if (isset($_POST['update'])) {
    $column = $_POST['column'];
    $value = $_POST['value'];
    $invoiceno = $_POST['invoice'];

    if ($column == "state") {
        $SQL = "UPDATE debtortrans SET state='$value' WHERE transno='$invoiceno' AND type=10";
    } else {
        echo    $SQL = "UPDATE invoice SET $column='$value' WHERE invoiceno='$invoiceno'";
    }
    DB_query($SQL, $db);

    echo "success";
    return;
}

if (isset($_GET['json']) && $_GET['clientID'] != '') {
    $sql = "SELECT DISTINCT stockissuance.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						stockissuance.issued- (SELECT IFNULL(SUM(quantity),0) FROM `ogpsalescaseref` WHERE salesman = '" . $_GET['clientID'] . "' AND stockid =  stockissuance.stockid
                        AND dispatchid != '')- 
            (SELECT IFNULL(SUM(quantity),0) FROM `ogpcsvref` WHERE salesman = '" . $_GET['clientID'] . "' AND stockid =  stockissuance.stockid
            AND dispatchid != '')- 
            (SELECT IFNULL(MIN(quantity),0) FROM `ogpcrvref` WHERE salesman = '" . $_GET['clientID'] . "' AND stockid =  stockissuance.stockid
            AND dispatchid != '')- 
            (SELECT IFNULL(SUM(quantity),0) FROM `ogpmporef` WHERE salesman = '" . $_GET['clientID'] . "' AND stockid =  stockissuance.stockid
            AND dispatchid != '')  as issued,
						stockissuance.returned as returned,
						stockissuance.dc as dc,
						stockmaster.materialcost,
						stockissuance.salesperson,
						stockmaster.discount,
						stockissuance.issued*stockmaster.materialcost*(1-stockmaster.discount) as totalValue,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled,
                        '' as salescaseref,
                        '' as csv,
                        '' as crv,
                        '' as mpo
					FROM stockissuance,
						stockmaster,
						locations,
						manufacturers,
						stockcategory
					WHERE stockissuance.stockid=stockmaster.stockid
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')							
							
					AND stockmaster.brand like '%" . $_GET['brand'] . "%'
					AND stockmaster.categoryid = stockcategory.categoryid
					AND stockissuance.issued>0
					AND stockcategory.categorydescription like " . "'%" . $_GET['StockCat'] . "%'" . "
		            AND stockissuance.salesperson='" . $_GET['clientID'] . "'
					
					";

    $sql .= 'AND stockissuance.salesperson IN 
            (SELECT can_access FROM cart_report_access WHERE user = "' . $_SESSION['UserID'] . '"' . ') 
            ';

    $res = mysqli_query($db, $sql);
    $SQLdiscount = "SELECT stkcode,AVG(discountpercent) as discount from dcdetails GROUP by stkcode";
    $resdiscount = DB_query($SQLdiscount, $db, $ErrMsg, $DbgMsg);
    $responsediscount = [];
    while ($rowdiscount = mysqli_fetch_assoc($resdiscount)) {
        $responsediscount[$rowdiscount['stkcode']] = $rowdiscount;
    }

    $response = [];
    $sum = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $response[$row['stockid']] = $row;
        if ($responsediscount[$row['stockid']]['discount'] > 0)
            $response[$row['stockid']]['discount'] = round($responsediscount[$row['stockid']]['discount'] * 100, 2);
        else
            $response[$row['stockid']]['discount'] = 50;
        $response[$row['stockid']]['totalValue'] = $row['issued'] * $row['materialcost'] * (1 - ($response[$row['stockid']]['discount'] / 100));
        $sum = $sum + $response[$row['stockid']]['totalValue'];
        $response[$row['stockid']]['totalValue'] = locale_number_format($response[$row['stockid']]['totalValue']);
        
        
    }



    $sqlsecond = "SELECT DISTINCT stockissuance.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						ogpsalescaseref.quantity as issued,
						stockissuance.returned as returned,
						stockissuance.dc as dc,
						stockmaster.materialcost,
						stockissuance.salesperson,
						stockmaster.discount,
						ogpsalescaseref.quantity*stockmaster.materialcost*(1-stockmaster.discount) as totalValue,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled,
                        ogpsalescaseref.salescaseref,
                        '' as csv,
                        '' as crv,
                        '' as mpo
					FROM stockissuance,
						stockmaster,
						locations,
						manufacturers,
						stockcategory,
                        ogpsalescaseref
					WHERE stockissuance.stockid=stockmaster.stockid
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						
							
                        AND ogpsalescaseref.stockid = stockissuance.stockid	
                        AND ogpsalescaseref.quantity > 0
                        AND ogpsalescaseref.dispatchid != ''
					AND stockmaster.brand like '%%'
					AND stockmaster.categoryid = stockcategory.categoryid
                    AND ogpsalescaseref.salesman = '" . $_GET['clientID'] . "'
					AND stockissuance.issued>0
					AND stockcategory.categorydescription like '%%'
		            AND stockissuance.salesperson='" . $_GET['clientID'] . "'
					
					";
    $res = mysqli_query($db, $sqlsecond);
    while ($row = mysqli_fetch_assoc($res)) {
        $response[$row['issued']] = $row;
        if ($responsediscount[$row['issued']]['discount'] > 0)
            $response[$row['issued']]['discount'] = round($responsediscount[$row['issued']]['discount'] * 100, 2);
        else
            $response[$row['issued']]['discount'] = 50;
        $response[$row['issued']]['totalValue'] = $row['issued'] * $row['materialcost'] * (1 - ($response[$row['issued']]['discount'] / 100));
        $sum = $sum + $response[$row['issued']]['totalValue'];
        $response[$row['issued']]['totalValue'] = round($response[$row['issued']]['totalValue'], 2);
    }

    $sqlthird = "SELECT DISTINCT stockissuance.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						ogpcsvref.quantity as issued,
						stockissuance.returned as returned,
						stockissuance.dc as dc,
						stockmaster.materialcost,
						stockissuance.salesperson,
						stockmaster.discount,
						ogpcsvref.quantity*stockmaster.materialcost*(1-stockmaster.discount) as totalValue,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled,
                        ogpcsvref.csv,
                        '' as crv,
                        '' as mpo,
                        '' as salescaseref
					FROM stockissuance,
						stockmaster,
						locations,
						manufacturers,
						stockcategory,
                        ogpcsvref
					WHERE stockissuance.stockid=stockmaster.stockid
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						
                        AND ogpcsvref.quantity != ''
                        AND ogpcsvref.quantity > 0
                        AND ogpcsvref.dispatchid != NULL
                        AND ogpcsvref.stockid = stockissuance.stockid	
					AND stockmaster.brand like '%%'
					AND stockmaster.categoryid = stockcategory.categoryid
                    AND ogpcsvref.salesman = '" . $_GET['clientID'] . "'
					AND stockissuance.issued>0
					AND stockcategory.categorydescription like '%%'
		            AND stockissuance.salesperson='" . $_GET['clientID'] . "'
					
					";
    $res = mysqli_query($db, $sqlthird);
    while ($row = mysqli_fetch_assoc($res)) {
        $response[$row['issued']] = $row;
        if ($responsediscount[$row['issued']]['discount'] > 0)
            $response[$row['issued']]['discount'] = round($responsediscount[$row['issued']]['discount'] * 100, 2);
        else
            $response[$row['issued']]['discount'] = 50;
        $response[$row['issued']]['totalValue'] = $row['issued'] * $row['materialcost'] * (1 - ($response[$row['issued']]['discount'] / 100));
        $sum = $sum + $response[$row['issued']]['totalValue'];
        $response[$row['issued']]['totalValue'] = locale_number_format($response[$row['issued']]['totalValue']);
    }

    $sqlthird = "SELECT DISTINCT stockissuance.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						ogpcrvref.quantity as issued,
						stockissuance.returned as returned,
						stockissuance.dc as dc,
						stockmaster.materialcost,
						stockissuance.salesperson,
						stockmaster.discount,
						ogpcrvref.quantity*stockmaster.materialcost*(1-stockmaster.discount) as totalValue,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled,
                        ogpcrvref.crv,
                        '' as csv,
                        '' as mpo,
                        '' as salescaseref
					FROM stockissuance,
						stockmaster,
						locations,
						manufacturers,
						stockcategory,
                        ogpcrvref
					WHERE stockissuance.stockid=stockmaster.stockid
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						
							
                        AND ogpcrvref.stockid = stockissuance.stockid
                        AND ogpcrvref.quantity != ''	
                        AND ogpcrvref.quantity > 0	
                        AND ogpcrvref.dispatchid != NULL
					AND stockmaster.brand like '%%'
					AND stockmaster.categoryid = stockcategory.categoryid
                    AND ogpcrvref.salesman = '" . $_GET['clientID'] . "'
					AND stockissuance.issued>0
					AND stockcategory.categorydescription like '%%'
		            AND stockissuance.salesperson='" . $_GET['clientID'] . "'
					
					";
    $res = mysqli_query($db, $sqlthird);
    while ($row = mysqli_fetch_assoc($res)) {
        $response[$row['issued']] = $row;
        if ($responsediscount[$row['issued']]['discount'] > 0)
            $response[$row['issued']]['discount'] = round($responsediscount[$row['issued']]['discount'] * 100, 2);
        else
            $response[$row['issued']]['discount'] = 50;
        $response[$row['issued']]['totalValue'] = $row['issued'] * $row['materialcost'] * (1 - ($response[$row['issued']]['discount'] / 100));
        $sum = $sum + $response[$row['issued']]['totalValue'];
        $response[$row['issued']]['totalValue'] = locale_number_format($response[$row['issued']]['totalValue']);
    }

    $sqlthird = "SELECT DISTINCT stockissuance.stockid,
						stockmaster.description,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						manufacturers.manufacturers_name,
						ogpmporef.quantity as issued,
						stockissuance.returned as returned,
						stockissuance.dc as dc,
						stockmaster.materialcost,
						stockissuance.salesperson,
						stockmaster.discount,
						ogpmporef.quantity*stockmaster.materialcost*(1-stockmaster.discount) as totalValue,
						stockmaster.decimalplaces,
						stockmaster.serialised,
						stockmaster.controlled,
                        ogpmporef.mpo,
                        '' as csv,
                        '' as crv,
                        '' as salescaseref
					FROM stockissuance,
						stockmaster,
						locations,
						manufacturers,
						stockcategory,
                        ogpmporef
					WHERE stockissuance.stockid=stockmaster.stockid
							AND stockmaster.brand = manufacturers.manufacturers_id
						AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
						
							
                        AND ogpmporef.stockid = stockissuance.stockid	
                        AND ogpmporef.quantity != ''
                        AND ogpmporef.quantity > 0
                        AND ogpmporef.dispatchid != NULL
					AND stockmaster.brand like '%%'
					AND stockmaster.categoryid = stockcategory.categoryid
                    AND ogpmporef.salesman = '" . $_GET['clientID'] . "'
					AND stockissuance.issued>0
					AND stockcategory.categorydescription like '%%'
		            AND stockissuance.salesperson='" . $_GET['clientID'] . "'
					
					";
    $res = mysqli_query($db, $sqlthird);
    while ($row = mysqli_fetch_assoc($res)) {
        $response[$row['issued']] = $row;
        if ($responsediscount[$row['issued']]['discount'] > 0)
            $response[$row['issued']]['discount'] = round($responsediscount[$row['issued']]['discount'] * 100, 2);
        else
            $response[$row['issued']]['discount'] = 50;
        $response[$row['issued']]['totalValue'] = $row['issued'] * $row['materialcost'] * (1 - ($response[$row['issued']]['discount'] / 100));
        $sum = $sum + $response[$row['issued']]['totalValue'];
        $response[$row['issued']]['totalValue'] = locale_number_format($response[$row['issued']]['totalValue']);
    }

    $resFinal = [];
    foreach ($response as $key => $value) {

        $resFinal[] = $value;
    }

    echo json_encode($resFinal);
    return;
}
$sql2 = "SELECT      stockissuance.salesperson,
                    SUM(stockissuance.issued*stockmaster.materialcost) as totalValue
						
					FROM stockissuance,
						stockmaster
						
					WHERE stockissuance.stockid=stockmaster.stockid
						
		           
		            ";
$sql2 .= 'AND stockissuance.salesperson IN 
            (SELECT can_access FROM cart_report_access WHERE user = "' . $_SESSION['UserID'] . '"' . ') 
             GROUP BY stockissuance.salesperson
            ORDER BY totalValue desc
            ';
$res2 = mysqli_query($db, $sql2);



include_once("includes/header.php");
include_once("includes/sidebar.php");

?>

<style>
    th,
    td {
        text-align: left;

    }

    #searchresults_length label select {
        color: black;

    }

    #searchresults_length,
    #searchresults_info {
        color: #efefef;
    }

    #searchresults_filter label,
    .datatables-footer,
    .datatables-header {
        width: 100%;
        float: right;


    }

    input {
        float: right;
    }

    #searchresults thead th {
        border: 1px white solid;
        border-bottom: 0px;
    }

    #searchresults tfoot th {
        border: 1px white solid;
        border-top: 0px;
    }

    #searchresults td {
        border: 1px black solid;
        width: 1%;
    }

    .request-container {
        display: flex;
        justify-content: center;
        margin: 15px;
        margin-top: 15px;
        margin-bottom: 50px;
    }

    .footer {
        background: #efefef;
        bottom: 0;
        width: 100%;
        text-align: center;
        padding: 5px;
        position: fixed;
    }

    .form {
        float: left;
        display: flex;
        flex-direction: column;

        width: 300px;
        height: 400px;

        overflow: scroll;
    }

    .request-header {
        text-align: center;
        background: #e0e0e0;
        border-radius: 10px 10px 0 0;
        padding: 5px;
        margin-bottom: 0;
    }

    .request-header button {
        padding: 1px 10px !important;
    }

    .request-body {
        background: white;
        padding: 25px 15px;
        display: flex;
        flex-direction: column;
    }

    .request-body label {
        margin: 10px 0;
    }

    .input {
        width: 100%;
        border: 1px solid #ccc;
        padding: 5px;

    }

    .request-submit {
        border-radius: 0 0 7px 7px;
    }

    .existing-requests {
        flex: 1;
        margin-left: 10px;
        height: calc(100vh - 150px);
        overflow: hidden;
        overflow-y: scroll;
    }

    .request {
        display: flex;
        background: #efefef;
        margin-bottom: 5px;
    }

    .request .details {
        flex: 1;
        padding: 5px;
    }

    .request .details p {
        margin-bottom: 0;
        display: inline-block;
    }

    .client {
        background: #efefef;
        padding: 7px;
        float: left;
        border: 1px solid #ccc;
        border-radius: 7px;
        display: flex;
        justify-content: space-between;
        cursor: pointer;
    }

    .client:hover {

        background: green;
        color: white;

    }

    .selected {
        background: #efefef;
        color: green;
    }

    .qtyInput {
        width: 80px;
        border-radius: 7px;
        padding: 4px;
    }

    #scrollUp {
        position: fixed;
        bottom: 50px;
        right: 0;
        padding: 10px;
        color: white;
        background: #efefef;
    }

    .inp {
        border: 1px solid #E5E7E9;
        border-radius: 6px;
        height: 46px;
        padding: 12px;
        outline: none;
    }

    .actinf {
        font-size: 10px;
    }

    .fit {
        width: 1%;
    }

    .datechange {
        width: 135px;
        padding: 5px;
        border-radius: 7px;
    }
</style>
<input type="hidden" value="<?php ec($_SESSION['FormID']); ?>" id="FormID">
<div class="content-wrapper">


    <section class="content" id="content">
        <h3 class="request-header">
            Cart Report
        </h3>
        <h4 align="center"><span id="salespersonName" style="color: green;"></span><br /> Total Adjusted Value PKR <span id="totalAdjustedDisplay"></span></h4>
        <div class="form" id="form">


            <div class="request-body">

                <?php while ($clients = mysqli_fetch_assoc($res2)) { ?>
                    <div class="client" data-client="<?php echo $clients['salesperson']; ?>">
                        <span><?php echo $clients['salesperson']; ?></span>

                    </div>
                    <table style="background-color: lightgoldenrodyellow">
                        <tr>

                            <td><?php echo "PKR " . locale_number_format(round($clients['totalValue'], 0)); ?></td>

                        </tr>
                    </table>
                <?php } ?>
            </div>
        </div>
        <div class="request-container">





            <div class="data" style="float: left; width:100%;" id="data">


                <div id="resultscontainer" class="" style=" background-color:#ecedf0;float: left;">

                    <table id="searchresults" width="100%" class="responsive table-striped">
                        <thead>
                            <tr style="background:#efefef; color:black">


                                <th>StockID</th>
                                <th>Manufacturer Code</th>
                                <th>Part Number</th>
                                <th>Brand</th>
                                <th>Description</th>
                                <th>Salescaseref</th>
                                <th>CSV</th>
                                <th>CRV</th>
                                <th>MPO</th>
                                <th>Quantiy</th>
                                <th>Unit list price</th>
                                <th>Discount</th>
                                <th>Total Value</th>

                            </tr>
                        </thead>
                        <tbody id="srb" style="color: black">

                        </tbody>
                        <tfoot>
                            <tr style="background:#efefef; color:white">


                                <th>StockID</th>
                                <th>Manufacturer Code</th>
                                <th>Part Number</th>
                                <th>Brand</th>
                                <th>Description</th>
                                <th>Salescaseref</th>
                                <th>CSV</th>
                                <th>CRV</th>
                                <th>MPO</th>
                                <th>Quantiy</th>
                                <th>Unit list price</th>
                                <th>Discount</th>
                                <th>Total Value</th>

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
    table = null;
    table = $('#searchresults').DataTable({
        "pageLength": 100,
        dom: 'Bflrtip',
        "lengthMenu": [
            [10, 25, 50, 75, 100, -1],
            [10, 25, 50, 75, 100, "All"]
        ],
        buttons: [
            'excelHtml5',
            'csvHtml5',
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        },



        columns: [

            {
                "data": "stockid"
            },
            {
                "data": "mnfCode"
            },
            {
                "data": "mnfpno"
            },
            {
                "data": "manufacturers_name"
            },
            {
                "data": "description"
            },
            {
                "data": "salescaseref"
            },
            {
                "data": "csv"
            },
            {
                "data": "crv"
            },
            {
                "data": "mpo"
            },
            {
                "data": "issued"
            },
            {
                "data": "materialcost"
            },

            {
                "data": "discount"
            },
            {
                "data": "totalValue"
            },
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


        ],

        drawCallback: function() {
            let api = this.api();
            $(api.table().footer()).html(
                '<tr style="background-color:#efefef; font-size: 32; color:black">' +
                '<th></th><th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th> <th></th>' +
                '<th id="totalAdjusted">' + api.column(12, {
                    search: 'applied'
                }).data().sum() + '</th>' +



                '</tr>'
            );
            $('#salespersonName').html($('.selected').html());
            $('#totalAdjustedDisplay').html($('#totalAdjusted').html());
        }
    });

    $.get("cartreport.php?json", function(res, status) {

        res = JSON.parse(res);
        table.clear().draw();
        table.rows.add(res).draw();
    });

    $(document.body).on("change", ".datechange", function() {
        let ref = $(this);
        let column = ref.attr("data-column");
        let invoice = ref.attr("data-invoice");
        let value = ref.val();
        let update = true;
        $.post("cartreport.php", {
            column,
            value,
            invoice,
            update
        }, function(res, status) {
            if (res == "success") {
                alert("Updated Successfully");
            }
        });
    });
    $(document.body).on("change", ".comment", function() {
        let ref = $(this);
        let column = ref.attr("data-column");
        let invoice = ref.attr("data-invoice");
        let value = ref.val();
        let update = true;
        $.post("cartreport.php", {
            column,
            value,
            invoice,
            update
        }, function(res, status) {
            if (res == "success") {
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

    table.columns().every(function() {
        let that = this;
        $('input', this.footer()).on('keyup change', function() {
            if (that.search() !== this.value) {
                that.search(this.value).draw();
            }
        });
    });
    var FormID = '<?php echo $_SESSION['FormID'] ?>';
    var clientID = "";

    $(".client").on("click", function() {

        let ref = $(this);

        if (ref.hasClass("selected")) {
            return;
        }

        $(".client").each(function() {
            $(this).removeClass("selected");
        });

        ref.addClass("selected");

        let getUserRequests = true;

        if (typeof ref.attr("data-client") === 'undefined') {
            clientID = "";
        } else {
            clientID = ref.attr("data-client");
        }
        $.get("cartreport.php?json&clientID=" + clientID, function(res, status) {
            console.log(res);
            res = JSON.parse(res);
            table.clear().draw();
            table.rows.add(res).draw();
        });


    });
</script>
<script>
    $(document).ready(function() {
        let form = document.getElementById('form').style.maxHeight;
        let content = document.getElementById('content').style.height;
        if (form > content) {
            document.getElementById('form').style.maxHeight = content;
        } else {
            document.getElementById('content').style.height = form;
        }

    })
</script>
<?php
include_once("includes/foot.php");
?>