<?php
include_once("../v2/config.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAHamid ERP</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<script>
    $(document).ready(function() {
        $(".js-example-basic-single").select2();
    });
</script>
<style>
    body {
        background: #ecf0f5;
        color: black;

    }

    form {
        display: flex;
    }

    form>div {
        display: flex;
        flex-direction: column;
    }

    form>div:last-child {
        flex: 1;
    }

    label,
    input {
        height: 25px;
        padding: 5px;
        box-sizing: border-box;
    }

    .request-header {
        color: #848484;
        font-size: 1.8em;
        text-align: center;
        background: #e0e0e0;
        border-radius: 10px 10px 0 0;
        padding: 12px;
        margin-bottom: 3rem;
        font-family: Avantgarde, TeX Gyre Adventor, URW Gothic L, sans-serif;
    }

    .salesman-header {
        color: #848484;
        font-size: 1.5em;
        text-align: center;
        border-radius: 10px 10px 0 0;
        padding: 8px;
        margin-bottom: 1rem;
        font-family: Avantgarde, TeX Gyre Adventor, URW Gothic L, sans-serif;
    }


    tbody td {
        /* 1. Animate the background-color
     from transparent to white on hover */
        background-color: rgba(255, 255, 255, 1);
        transition: all 0.1s linear;
        transition-delay: 0.1s, 0s;
        /* 2. Animate the opacity on hover */
        opacity: 0.7;
    }

    tbody tr:hover td {
        background-color: #ecf0f5;
        transition-delay: 0s, 0s;
        opacity: 1;
        font-size: 1.2em;
    }

    td {
        /* 3. Scale the text using transform on hover */
        transform-origin: center left;
        transition-property: transform;
        transition-duration: 0.4s;
        transition-timing-function: ease-in-out;
    }

    tr:hover td {
        transform: scale(1.1);
    }

    /* Codepen styling */
    * {
        box-sizing: border-box
    }

    table {
        width: 90%;
        margin: 0 5%;
        border-collapse: collapse;
        text-align: left;
    }

    th {
        color: #848484;
    }

    th,
    td {
        padding: 0.5em;
    }

    thead tfoot {
        border: 1px solid #9a9da0;
        background-color: #e0e0e0;
        font-size: 1.2em;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    .tbutton {
        background-color: #4CAF50;
        /* Green */
        border: none;
        border-radius: 4px;
        color: white;
        padding: 10px 22px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        cursor: pointer;
    }

    input {

        margin-bottom: 5px;
        padding: 6px 3px;
        width: 209px;
        float: right;
        margin-right: 78px;
    }


    .modal {
        text-align: center;
        padding: 0 !important;

    }

    .modal:before {
        content: '';
        display: inline-block;
        height: 100%;
        vertical-align: middle;
        margin-right: -4px;
    }

    .modal-dialog {
        display: inline-block;
        width: 100%;
        text-align: left;
        vertical-align: middle;
    }

    .modal-content {
        border: 1px solid none;
        border-radius: 14px;
        background: #e0e0e0;
        /* background-image: url("asset/vendor/css/model.jpg"); */
    }

    #example {
        width: 90%;
    }

    #container {
        display: flex;
        margin: 10px;
        /* border: 1px solid #848484; */
        background: #e0e0e0;
    }

    #container section {
        padding: 10px;
    }

    .aboutus {
        width: 22%;
    }

    .tripinfo {
        background: #DDD;
        width: 78%;
    }

    .salesman {
        width: 239px;
        margin-top: 4px;
        /* color: #848484; */
        font-size: 0.9em;
        text-align: center;
        border: 1px solid #848484;
        border-radius: 10px;
        padding: 4px;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;

    }

    .salesman:hover {
        background-color: #4CAF50;
        color: white;
    }

    /* .client {
        margin-top: 92px;
    } */
    .hr {
        background-color: black;
    }

    /* #loader {
        position: absolute;
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;

        margin: auto;
    } */

    #loader img {
        position: relative;

        width: 220px;
        height: 50px;
    }

    /* #myDIV {
        height: 300px;
        background-color: #FFFFFF;
    } */

    #mymain {
        display: flex;
    }

    #mymain div {
        width: 50%;
        font-size: 0.7em;
        height: auto;
        font-weight: bold;
        font-family: Avantgarde, TeX Gyre Adventor, URW Gothic L, sans-serif;
        margin-bottom: 8px;
    }

    #mymain div label {
        height: 15px;
    }

    #first {
        display: flex;
    }

    #first div {
        width: 50%;
        font-size: 0.7em;
        height: auto;
        font-weight: bold;
        font-family: Avantgarde, TeX Gyre Adventor, URW Gothic L, sans-serif;
        margin-bottom: 8px;
    }

    #first div label {
        height: 15px;
    }

    #bluediv {
        width: 50%;
        height: auto;
        font-size: 0.7em;
        font-family: Avantgarde, TeX Gyre Adventor, URW Gothic L, sans-serif;
        margin-bottom: 8px;
    }

    #bluediv div label {
        height: 15px;
    }

    #second label {
        width: 50%;
        height: auto;
        font-size: 0.7em;
        font-family: Avantgarde, TeX Gyre Adventor, URW Gothic L, sans-serif;
        margin-bottom: 8px;
        height: 15px;
    }

    /* .modal-body {
        overflow: scroll;
        height: 300px;
    } */
</style>

<body>
    <h3 class="request-header">
        <b> CART REPORT </b>
        <div id='loader' style='display: none;'>
            <img src='asset/vendor/149.gif'>
        </div>
    </h3>

    <!-- Image loader -->

    <?php
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
    ?>
    <div id="container">

        <section class="aboutus" style="overflow:scroll; height:850px">
            <h3 class="salesman-header">
                <b> SALESMAN </b>
            </h3>
            <div class="client">
                <hr class="hr">
                <?php while ($clients = mysqli_fetch_assoc($res2)) { ?>
                    <button class="salesman" onclick="salespersonData('<?php echo $clients['salesperson']; ?>')"><?php echo $clients['salesperson']; ?> <small style="color:red"><?php echo "PKR " . locale_number_format(round($clients['totalValue'], 0)); ?></small></button>
                <?php } ?>
            </div>
        </section>

        <section class="tripinfo">
            <h3 class="salesman-header">
                <b> REPORT OF <span id="reportOf"></span> </b>
                <br /> Total Adjusted Value PKR <span id="totalAdjustedDisplay"></span>
            </h3>
            <hr class="hr">

            <table id="example" class="display nowrap">
                <thead class="header">
                    <tr>
                        <th>StockID</th>
                        <th>Quantity</th>
                        <th>Total Value</th>
                        <th>Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    // array_merge($row1, $row2, $row3, $row4, $row5)
                    if (empty($row5)) {
                        $merged_results = [];
                    } else {
                        $merged_results = $row5 + $row4;
                    }
                    ?>
                </tbody>
                <tfoot class="header">
                    <tr>
                        <th>StockID</th>
                        <th>Quantity</th>
                        <th>Total Value</th>
                        <th>Info</th>
                    </tr>
                </tfoot>
            </table>
        </section>
    </div>


    <!-- The Modal -->
    <div class="modal" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color:#dddddd">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title col-10 text-center" style="color: #848484; margin-left:40px">
                        Item Info
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <form class="form-inline">
                    <div class="modal-body">
                    </div>
                </form>

            </div>
        </div>
    </div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.datatables.net/plug-ins/1.12.1/api/sum().js">
<script>
    $(document).ready(function() {

        table = null;
        table = $('#example').DataTable({
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
            columns: [{
                    data: 'stock_code'
                },
                {
                    data: 'quantity'
                },
                {
                    data: 'total_value'
                },
                {
                    data: 'action'
                }
            ]
        });
    });

    function salespersonData(name) {
        var clientID = name;
        $("#reportOf").html(clientID);
        $('#example').DataTable().rows().remove().draw();
        $("#loader").show();
        $.ajax({
            type: 'POST',
            url: 'salesReport.php',
            data: {
                clientID: clientID
            },
            success: function(response) {
                res = JSON.parse(response);
                console.log(res);

                // $.get("cartreport.php?json&clientID=" + clientID, function(res, status) {
                // res = JSON.parse(res);
                // console.log(res);
                $('#example').DataTable().destroy();
                // alert(res.adjustedValue);
                var totalAdjusted = 0;
                for (var i = 0; i < res.length - 1; i++) {
                    var item = res[i];
                    var row = `<tr> 
                                <td>${item.stockid}</td>
                                <td>${item.issued}</td>
                                <td>${item.totalValue}</td>
                                <td>  <i class="fa fa-info-circle" data-toggle="modal" data-target="#myModal" onclick="myFunction('${ item.stockid}', '${ item.mnfCode}',
                                 '${ item.mnfpno}' , '${ item.manufacturers_name}','${ item.issued}','${ item.description}',
                                 '${ item.materialcost}','${ item.discount}','${ clientID}','${item.totalValue}')" style="font-size:48px;color:red"></i> </td>

                                </tr>`;
                 totalAdjusted = item.adjustedValue;

                    $('#example').find('tbody').append(row);
                    // $('#example').append(row);
                    // table.rows.add(row).draw();
                }
                $('#totalAdjustedDisplay').html(totalAdjusted);
                $('#example').DataTable().draw();
                $("#loader").hide();
            }
        });
    };


    function myFunction(item, mnfcode, partno, brand, qty, description, UnitListPrice, discount, client, totalval) {
        var title = item;
        var mncode = mnfcode;
        var partnumber = partno;
        var brands = brand;
        var quantity = qty;
        var descriptions = description;
        var UnitListPrices = UnitListPrice;
        var discounts = discount;
        var clients = client;
        var total = totalval;
        var htmlStr = '<div id="myDIV">';
        htmlStr += '<div id="mymain">';
        htmlStr += '<div>';
        htmlStr += '<label>Stock Id:</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>Manufecturer Code:</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>Part Number:</label>';
        htmlStr += '<hr>';
        htmlStr += '<label style="height:60px">Brand:</label>';
        htmlStr += '<hr>';
        htmlStr += '<label style="height:60px">Description:</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>Quantity:</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>Unit Price:</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>Discount:</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>Total Price:</label>';
        htmlStr += '<hr>';
        htmlStr += '</div>';

        htmlStr += '<div id="bluediv">';
        htmlStr += '<label>' + title + '</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>' + mncode + '</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>' + partnumber + '</label>';
        htmlStr += '<hr>';
        htmlStr += '<label style="height:60px">' + brands + '</label>';
        htmlStr += '<hr>';
        htmlStr += '<label style="height:60px">' + descriptions + '</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>' + quantity + '</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>' + UnitListPrices + '</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>' + discounts + '</label>';
        htmlStr += '<hr>';
        htmlStr += '<label>' + total + '</label>';
        htmlStr += '<hr>';
        htmlStr += '</div>';
        htmlStr += '<hr>';
        htmlStr += '</div>';
        $('.modal-body').html("");
        $.ajax({
            type: 'POST',
            url: 'itemInfo.php',
            data: {
                clientID: clients,
                Item: title

            },
            success: function(response) {
                var res = $.parseJSON(response);
                jQuery.each(res, function(index, value) {
                    console.log(res);
                    if (index == 'salescase') {
                        htmlStr += '<h4>SALESCASES</h4>';
                        htmlStr += '<hr>';
                        htmlStr += '<div id="mymain">';
                        htmlStr += '<div>';
                        for (var i = 0; i < value.length; i++) {
                            htmlStr += '<label>Salescaseref:</label><br>';
                            htmlStr += '<label>Quantity:</label>';
                            htmlStr += '<hr>';
                            i++;
                        }
                        htmlStr += '</div>';
                        htmlStr += '<div id="bluediv">';
                        for (var i = 0; i < value.length; i++) {
                            htmlStr += '<label>' + value[i] + '</label><br>';
                            htmlStr += '<label>' + value[i + 1] + '</label>';
                            i++;
                            htmlStr += '<hr>';
                        }
                        htmlStr += '</div>';
                        htmlStr += '</div>';
                    }

                    if (index == 'csv') {
                        htmlStr += '<h4>CSV</h4>';
                        htmlStr += '<hr>';
                        htmlStr += '<div id="mymain">';
                        htmlStr += '<div>';
                        for (var i = 0; i < value.length; i++) {
                            htmlStr += '<label>CSV ref:</label><br>';
                            htmlStr += '<label>Quantity:</label>';
                            htmlStr += '<hr>';
                            i++;
                        }
                        htmlStr += '</div>';
                        htmlStr += '<div id="bluediv">';
                        for (var i = 0; i < value.length; i++) {
                            htmlStr += '<label>' + value[i] + '</label><br>';
                            htmlStr += '<label>' + value[i + 1] + '</label>';
                            i++;
                            htmlStr += '<hr>';
                        }
                        htmlStr += '</div>';
                        htmlStr += '</div>';
                    }

                    if (index == 'crv') {
                        htmlStr += '<h4>CRV</h4>';
                        htmlStr += '<hr>';
                        htmlStr += '<div id="mymain">';
                        htmlStr += '<div>';
                        for (var i = 0; i < value.length; i++) {
                            htmlStr += '<label>CRV ref:</label><br>';
                            htmlStr += '<label>Quantity:</label>';
                            htmlStr += '<hr>';
                            i++;
                        }
                        htmlStr += '</div>';
                        htmlStr += '<div id="bluediv">';
                        for (var i = 0; i < value.length; i++) {
                            htmlStr += '<label>' + value[i] + '</label><br>';
                            htmlStr += '<label>' + value[i + 1] + '</label>';
                            i++;
                            htmlStr += '<hr>';
                        }
                        htmlStr += '</div>';
                        htmlStr += '</div>';
                    }

                    if (index == 'mpo') {
                        htmlStr += '<h4>MPO</h4>';
                        htmlStr += '<hr>';
                        htmlStr += '<div id="mymain">';
                        htmlStr += '<div>';
                        for (var i = 0; i < value.length; i++) {
                            htmlStr += '<label>MPO ref:</label><br>';
                            htmlStr += '<label>Quantity:</label>';
                            htmlStr += '<hr>';
                            i++;
                        }
                        htmlStr += '</div>';
                        htmlStr += '<div id="bluediv">';
                        for (var i = 0; i < value.length; i++) {
                            htmlStr += '<label>' + value[i] + '</label><br>';
                            htmlStr += '<label>' + value[i + 1] + '</label>';
                            i++;
                            htmlStr += '<hr>';
                        }
                        htmlStr += '</div>';
                        htmlStr += '</div>';
                    }
                });
                htmlStr += '</div>';
                $('.modal-body').html(htmlStr);
            }
        });
    }
</script>

</html>