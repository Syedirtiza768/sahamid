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
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.0.0-rc.4/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.0.0-rc.4/dist/js/tom-select.complete.min.js"></script>

</head>
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
        font-size: 1.4em;
        text-align: center;
        background: #e0e0e0;
        color: #848484;
        border-radius: 10px 10px 0 0;
        padding: 8px;
        margin-bottom: 3rem;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
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

    .header {
        background: #e0e0e0;
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
    }

    #example {
        width: 90%;
    }
</style>

<body>
    <h3 class="request-header">
        <img src="img/change_item.png" style="height:40px">
        Change Item Reference (<?php echo $_SESSION['UsersRealName'] ?>)
    </h3>
    <table id="example" class="display nowrap">
        <thead class="header">
            <tr>
                <th>Item Code</th>
                <th>Quantity</th>
                <th>Salescase</th>
                <th>CSV</th>
                <th>CRV</th>
                <th>MPO</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $row1 = [];
            $row2 = [];
            $row3 = [];
            $row4 = [];
            $row5 = [];
            $sql = "SELECT  
    ogpsalescaseref.stockid,
    SUM(ogpsalescaseref.quantity) AS quantity,
    ogpsalescaseref.dispatchid,
    ogpsalescaseref.salescaseref
FROM ogpsalescaseref
INNER JOIN stockissuance 
    ON stockissuance.stockid = ogpsalescaseref.stockid
WHERE ogpsalescaseref.salesman = '" . $_SESSION['UsersRealName'] . "'
  AND ogpsalescaseref.quantity != '0'
  
  AND stockissuance.issued > 0
  AND stockissuance.salesperson = '" . $_SESSION['UsersRealName'] . "'
GROUP BY 
    ogpsalescaseref.stockid,
    ogpsalescaseref.dispatchid,
    ogpsalescaseref.salescaseref
";
            $res = mysqli_query($db, $sql);
            while ($row = mysqli_fetch_assoc($res)) {
                $row1[] = $row;
            }


            $sql = "SELECT ogpcsvref.stockid,
                SUM(ogpcsvref.quantity) as quantity,
                ogpcsvref.dispatchid,
                ogpcsvref.csv
                FROM ogpcsvref
                WHERE salesman = '" . $_SESSION['UsersRealName'] . "'
                AND quantity != '0'
                GROUP BY ogpcsvref.stockid,ogpcsvref.csv";
            $res = mysqli_query($db, $sql);
            while ($row = mysqli_fetch_assoc($res)) {
                $row2[] = $row;
            }
            $sql = "SELECT  ogpcrvref.stockid,
                SUM(ogpcrvref.quantity) as quantity,
                ogpcrvref.dispatchid,
                ogpcrvref.crv
                FROM ogpcrvref
                WHERE salesman = '" . $_SESSION['UsersRealName'] . "'
                AND quantity != '0'
                GROUP BY ogpcrvref.stockid,ogpcrvref.crv";
            $res = mysqli_query($db, $sql);
            while ($row = mysqli_fetch_assoc($res)) {
                $row3[] = $row;
            }
            $sql = "SELECT  ogpmporef.stockid,
                SUM(ogpmporef.quantity) as quantity,
                ogpmporef.dispatchid,
                ogpmporef.mpo
                FROM ogpmporef
                WHERE salesman = '" . $_SESSION['UsersRealName'] . "'
                AND quantity != '0'
                GROUP BY ogpmporef.stockid,ogpmporef.mpo";
            $res = mysqli_query($db, $sql);
            while ($row = mysqli_fetch_assoc($res)) {
                $row4[] = $row;
            }
            $sql = "SELECT DISTINCT stockissuance.stockid,
            stockissuance.issued - (SELECT IFNULL(SUM(quantity),0) FROM `ogpsalescaseref` WHERE salesman = '" . $_SESSION['UsersRealName'] . "' AND stockid =  stockissuance.stockid)- 
            (SELECT IFNULL(SUM(quantity),0) FROM `ogpcsvref` WHERE salesman = '" . $_SESSION['UsersRealName'] . "' AND stockid =  stockissuance.stockid)-
            (SELECT IFNULL(SUM(quantity),0) FROM `ogpcrvref` WHERE salesman = '" . $_SESSION['UsersRealName'] . "' AND stockid =  stockissuance.stockid)-
            (SELECT IFNULL(SUM(quantity),0) FROM `ogpmporef` WHERE salesman = '" . $_SESSION['UsersRealName'] . "' AND stockid =  stockissuance.stockid) as quantity
        FROM stockissuance,
            stockmaster,
            locations,
            manufacturers,
            stockcategory
        WHERE stockissuance.stockid=stockmaster.stockid
                AND stockmaster.brand = manufacturers.manufacturers_id
            AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')		
        AND stockmaster.categoryid = stockcategory.categoryid
        AND stockissuance.issued>0
        AND stockissuance.salesperson='" . $_SESSION['UsersRealName'] . "'";
            $res = mysqli_query($db, $sql);
            while ($row = mysqli_fetch_assoc($res)) {
                $row5[] = $row;
            }
            $merged_results = array_merge($row1, $row2, $row3, $row4, $row5);
            $checkStock = 0;
            foreach ($merged_results as $row) {
            ?>
                <?php if ($checkStock != $row['stockid']) {
                    if ($row['quantity'] != "0") { ?>
                        <tr>
                            <td><?php echo $row['stockid'] ?></td>
                            <td><?php echo abs($row['quantity']) ?></td>
                            <td><?php echo $row['salescaseref'] ?></td>
                            <td><?php echo $row['csv'] ?></td>
                            <td><?php echo $row['crv'] ?></td>
                            <td><?php echo $row['mpo'] ?></td>
                            <td>
                                <?php if ($row['salescaseref']) { ?>
                                    <button type="button" data-toggle="modal" data-target="#myModal" onclick="myFunction('<?php echo $row['stockid'] ?>', <?php echo $row['quantity'] ?>, 'salescase' , '<?php echo $row['salescaseref'] ?>')" class="tbutton">Change Ref</button>
                            </td>
                        <?php } else if ($row['csv']) { ?>
                            <button type="button" data-toggle="modal" data-target="#myModal" onclick="myFunction('<?php echo $row['stockid'] ?>', <?php echo $row['quantity'] ?>, 'CSV' , '<?php echo $row['csv'] ?>')" class="tbutton">Change Ref</button></td>

                        <?php } else if ($row['crv']) { ?>
                            <button type="button" data-toggle="modal" data-target="#myModal" onclick="myFunction('<?php echo $row['stockid'] ?>', <?php echo $row['quantity'] ?>, 'CRV' , '<?php echo $row['crv'] ?>')" class="tbutton">Change Ref</button></td>

                        <?php } else if ($row['mpo']) { ?>
                            <button type="button" data-toggle="modal" data-target="#myModal" onclick="myFunction('<?php echo $row['stockid'] ?>', <?php echo $row['quantity'] ?>, 'MPO' , '<?php echo $row['mpo'] ?>')" class="tbutton">Change Ref</button></td>
                        <?php } else { ?>
                            <button type="button" data-toggle="modal" data-target="#myModal" onclick="myFunction('<?php echo $row['stockid'] ?>', <?php echo $row['quantity'] ?>, 'NOT' , '')" class="tbutton">Change Ref</button></td>
                        <?php } ?>
                        <?php if (!$row['salescaseref'] && !$row['csv'] && !$row['crv'] && !$row['mpo']) {
                            $checkStock = $row['stockid'];
                        }
                        ?>
                        </tr>
            <?php }
                }
            } ?>
        </tbody>
        <tfoot class="header">
            <tr>
                <th>Item Code</th>
                <th>Quantity</th>
                <th>Salescase</th>
                <th>CSV</th>
                <th>CRV</th>
                <th>MPO</th>
                <th>Action</th>
            </tr>
        </tfoot>
    </table>

    <!-- The Modal -->
    <div class="modal" id="myModal">
        <div class="modal-dialog">
            <div class="modal-content" style="background-color:#dddddd">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h4 class="modal-title col-10 text-center" style="color: #848484;">
                        <img src="img/change_item.png" style="height:40px"> Change Item Reference
                    </h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Modal body -->
                <form class="form-inline">
                    <div class="modal-body">
                    </div>
                    <div class="modal-footer border-top-0 d-flex justify-content-center">
                        <button type="button" id="btnSubmit" class="btn btn-success" style="width: 100%;">Submit</button>
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
<script>
    $(document).ready(function() {
        new DataTable('#example', {
            columns: [{
                    data: 'item_code'
                },
                {
                    data: 'quantity'
                },
                {
                    data: 'salescase'
                },
                {
                    data: 'csv'
                },
                {
                    data: 'crv'
                },
                {
                    data: 'mpo'
                },
                {
                    data: 'action'
                }
            ]
        });
    });

    $("#btnSubmit").click(function() {
        var old_sales = $("#old_sales").val();
        var old_csv = $("#old_csv").val();
        var old_crv = $("#old_crv").val();
        var old_mpo = $("#old_mpo").val();
        var item = $("#item").val();
        var old_quantity = $("#quantity").val();

        var new_sales = $("#new_sales").val();
        var new_sales_q = $("#new_sales_q").val();
        var new_csv = $("#new_csv").val();
        var new_csv_q = $("#new_csv_q").val();
        var new_crv = $("#new_crv").val();
        var new_crv_q = $("#new_crv_q").val();
        var new_mpo = $("#new_mpo").val();
        var new_mpo_q = $("#new_mpo_q").val();
        var salesman = "<?php echo $_SESSION['UsersRealName'] ?>";

        if (new_sales != "" || new_csv != "" || new_crv != "" || new_mpo != "") {
            if (new_sales != "") {
                if (new_sales_q == "") {
                    alert("Please assign Item quantity to new salescaseref")
                }
            }
            if (new_csv != "") {
                if (new_csv_q == "") {
                    alert("Please assign Item quantity to new CSV")
                }
            }
            if (new_crv != "") {
                if (new_crv_q == "") {
                    alert("Please assign Item quantity to new CRV")
                }
            }
            if (new_mpo != "") {
                if (new_mpo_q == "") {
                    alert("Please assign Item quantity to new MPO")
                }
            }

            var totalItems = Number(new_sales_q) + Number(new_csv_q) + Number(new_crv_q) + Number(new_mpo_q);

            if (totalItems > old_quantity) {
                alert("Your seleced quantities are greater. Please select in range of " + old_quantity);
            } else if (totalItems < 0) {
                alert("Your seleced quantities are in negative. Please select in range of " + old_quantity);
            } else {
                $("#btnSubmit").prop("disabled", true);
                $.ajax({
                    type: 'POST',
                    url: 'insert.php',
                    data: {
                        item: item,

                        old_sales: old_sales,
                        old_csv: old_csv,
                        old_crv: old_crv,
                        old_mpo: old_mpo,
                        old_quantity: old_quantity,

                        new_sales: new_sales,
                        new_sales_q: new_sales_q,
                        new_csv: new_csv,
                        new_csv_q: new_csv_q,
                        new_crv: new_crv,
                        new_crv_q: new_crv_q,
                        new_mpo: new_mpo,
                        new_mpo_q: new_mpo_q,

                        salesman: salesman
                    },
                    success: function(response) {
                        location.reload();
                    }
                });
            }
        } else {
            alert("You have't selected any reference yet")
        }

    });

    function myFunction(item, qty, type, ref) {
        const title = item;
        const quantity = qty;
        const typ = type;
        const refer = ref;
        var htmlStr = '<div class="form-group">';
        htmlStr += '<label for="a">Item Name</label>';
        htmlStr += ' <input type="text" id="item" value="' + title + '"  style="margin-left: 18px;" disabled>';
        htmlStr += '</div>';
        if (typ == "salescase") {
            htmlStr += ' <div class="form-group">';
            htmlStr += ' <label for="email1">Old Salescase</label>';
            htmlStr += ' <input type="text" id="old_sales" value="' + refer + '"  style="margin-right: 10px;" disabled>';
            htmlStr += ' </div>';
            htmlStr += ' <div class="form-group">';
            htmlStr += ' <label for="password1">Item Qty</label>';
            htmlStr += ' <input type="number" id="quantity" style="margin-left: 36px;" value="' + quantity + '" disabled>';
            htmlStr += ' <small style="color:red"><b>Note:</b> You can only share above item count to other references.</small>';
            htmlStr += ' </div><br><br>';
        } else if (typ == "CSV") {
            htmlStr += ' <div class="form-group">';
            htmlStr += ' <label for="email1">Old CSV</label>';
            htmlStr += '<input type="text" id="old_csv" value="' + refer + '"  style="margin-left: 38px;" disabled>';
            htmlStr += '  </div>';
            htmlStr += '<div class="form-group">';
            htmlStr += ' <label for="password1">Item Qty</label>';
            htmlStr += '<input type="number"  id="quantity" style="margin-left: 36px;" value="' + quantity + '" disabled>';
            htmlStr += ' <small style="color:red"><b>Note:</b> You can only share above item count to other references.</small>';
            htmlStr += ' </div><br><br>';
        } else if (typ == "CRV") {
            htmlStr += ' <div class="form-group">';
            htmlStr += ' <label for="email1">Old CRV</label>';
            htmlStr += '<input type="text" id="old_crv" value="' + refer + '"  style="margin-left: 38px;" disabled>';
            htmlStr += '  </div>';
            htmlStr += '<div class="form-group">';
            htmlStr += ' <label for="password1">Item Qty</label>';
            htmlStr += '<input type="number" id="quantity" style="margin-left: 36px;" value="' + quantity + '" disabled>';
            htmlStr += ' <small style="color:red"><b>Note:</b> You can only share above item count to other references.</small>';
            htmlStr += ' </div><br><br>';
        } else if (typ == "MPO") {
            htmlStr += ' <div class="form-group">';
            htmlStr += ' <label for="email1">Old MPO</label>';
            htmlStr += '<input type="text" id="old_mpo" value="' + refer + '" style="margin-left: 38px;" disabled>';
            htmlStr += '  </div>';
            htmlStr += '<div class="form-group">';
            htmlStr += ' <label for="password1">Item Qty</label>';
            htmlStr += '<input type="number" id="quantity" style="margin-left: 36px;" value="' + quantity + '" disabled>';
            htmlStr += ' <small style="color:red"><b>Note:</b> You can only share above item count to other references.</small>';
            htmlStr += ' </div><br><br>';
        } else {
            htmlStr += ' <div class="form-group">';
            htmlStr += ' <label for="email1">No Reference</label>';
            htmlStr += '<input type="text" id="old_mpo" value="' + refer + '" style="margin-left: 2px;" disabled>';
            htmlStr += '  </div>';
            htmlStr += '<div class="form-group">';
            htmlStr += ' <label for="password1">Item Qty</label>';
            htmlStr += '<input type="number" id="quantity" style="margin-left: 36px;" value="' + quantity + '" disabled>';
            htmlStr += ' <small style="color:red"><b>Note:</b> You can only share above item count to other references.</small>';
            htmlStr += ' </div><br><br>';

        }
        htmlStr += '<div class="form-group new_reference">';
        htmlStr += ' <label for="email1">New Salescase</label>';
        htmlStr += '<select class="select_state" id="new_sales" >';
        htmlStr += '<option value="">--- Choose Salescase ---</option>';
        <?php
        $sql = "select salescaseref from salescase WHERE salesman = '" . $_SESSION['UsersRealName'] . "' AND closed = '0' ";
        $result = mysqli_query($db, $sql);
        while ($row = mysqli_fetch_assoc($result)) { ?>
            htmlStr += '<option value="<?php echo $row["salescaseref"] ?>"><?php echo htmlspecialchars($row["salescaseref"], ENT_QUOTES, 'UTF-8') ?></option>';
        <?php
        }
        ?>
        htmlStr += '</select>';
        htmlStr += ' </div>';
        htmlStr += '<div class="form-group">';
        htmlStr += '<label for="password1">Item Qty</label>';
        htmlStr += '<input type="number" id="new_sales_q" style="margin-left:60px;">';
        htmlStr += '</div><br>';
        htmlStr += '<div class="form-group new_reference">';
        htmlStr += ' <label for="email1">New CSV</label>';
        htmlStr += '<select class="select_state1" id="new_csv" style="margin-left:37px;" id="csv">';
        htmlStr += '<option value="">--- Choose CSV ---</option>';
        <?php
        $sql = "select orderno from shopsale WHERE salesman = '" . $_SESSION['UsersRealName'] . "' AND 
            payment = 'csv'  AND complete = '0' ";
        $result = mysqli_query($db, $sql);
        while ($row = mysqli_fetch_assoc($result)) { ?>
            htmlStr += '<option value="<?php echo $row["orderno"] ?>"><?php echo htmlspecialchars($row["orderno"], ENT_QUOTES, 'UTF-8') ?></option>';
        <?php
        }
        ?>
        htmlStr += '</select>';
        htmlStr += ' </div>';
        htmlStr += '<div class="form-group">';
        htmlStr += '<label for="password1">Item Qty</label>';
        htmlStr += '<input type="number" id="new_csv_q" style="margin-left:40px;">';
        htmlStr += '</div><br>';
        htmlStr += '<div class="form-group new_reference">';
        htmlStr += ' <label for="email1">New CRV</label>';
        htmlStr += '<select class="select_state2" id="new_crv" style="margin-left:35px;" id="crv">';
        htmlStr += '<option value="">--- Choose CRV ---</option>';
        <?php
        $sql = "select orderno from shopsale WHERE salesman = '" . $_SESSION['UsersRealName'] . "' AND 
            payment = 'crv'  AND complete = '0' ";
        $result = mysqli_query($db, $sql);
        while ($row = mysqli_fetch_assoc($result)) { ?>
            htmlStr += '<option value="<?php echo $row["orderno"] ?>"><?php echo htmlspecialchars($row["orderno"], ENT_QUOTES, 'UTF-8') ?></option>';
        <?php
        }
        ?>
        htmlStr += '</select>';
        htmlStr += ' </div>';
        htmlStr += '<div class="form-group">';
        htmlStr += '<label for="password1">Item Qty</label>';
        htmlStr += '<input type="number" id="new_crv_q" style="margin-left:40px;">';
        htmlStr += '</div> <br>';
        htmlStr += '<div class="form-group new_reference">';
        htmlStr += ' <label for="email1">New MPO</label>';
        htmlStr += '<select class="select_state3" id="new_mpo" style="margin-left:30px;" id="mpo">';
        htmlStr += '<option value="">--- Choose MPO ---</option>';
        <?php
        $sql = "SELECT * FROM `bazar_parchi` WHERE `on_behalf_of` = '" . $_SESSION['UsersRealName'] . "' AND `inprogress` = '1' ";
        $result = mysqli_query($db, $sql);
        while ($row = mysqli_fetch_assoc($result)) { ?>
            htmlStr += '<option value="<?php echo $row["parchino"] ?>"><?php echo htmlspecialchars($row["parchino"], ENT_QUOTES, 'UTF-8') ?></option>';
        <?php
        }
        ?>
        htmlStr += '</select>';
        htmlStr += ' </div>';
        htmlStr += '<div class="form-group">';
        htmlStr += '<label for="password1"></label>';
        htmlStr += '<input type="number" id="new_mpo_q" style="margin-left:40px">';
        htmlStr += '</div>';

        // alert(htmlStr);
        // adding the data to the modal
        $('.modal-body').html(htmlStr);
        new TomSelect(".select_state", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
        new TomSelect(".select_state1", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
        new TomSelect(".select_state2", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
        new TomSelect(".select_state3", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
        //         $('.js-example-basic-single').select2({
        //     dropdownParent: $('.modal-body', '#myModal')
        //   });
        // $('.modal-body input').text(title);
        // $('#quantity').text(qty);
    }
</script>

</html>