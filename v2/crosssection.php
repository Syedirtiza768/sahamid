<?php
$active = "reports";
$AllowAnyone = true;

include_once("config.php");

if (!userHasPermission($db, "top_items_quotation_report")) {
    header("Location: /sahamid");
    return;
}

if (isset($_POST['to'])) {
    set_time_limit(300); // Increase timeout for large datasets
    
    $response = [];
    $from = $_POST['from'];
    $to = $_POST['to'];
    $location = isset($_POST['Location']) ? $_POST['Location'] : '';
    
    // Validate location input
    $valid_locations = ['HO', 'MT', 'SR'];
    $location_condition = empty($location) ? "AND loccode IN ('HO','MT','SR')" : "AND loccode = '$location'";

    // Create optimized temporary tables with proper indexes
    DB_query("CREATE TEMPORARY TABLE stock_summary (
                stockid VARCHAR(50) NOT NULL PRIMARY KEY,
                mnfCode VARCHAR(50),
                mnfpno VARCHAR(50),
                description VARCHAR(100),
                materialcost DECIMAL(20,4),
                manufacturers_name VARCHAR(100),
                qohA DECIMAL(20,4) DEFAULT 0,
                qohB DECIMAL(20,4) DEFAULT 0,
                averageInvoiceFactor DECIMAL(20,4) DEFAULT 0,
                invoiceitemQty DECIMAL(20,4) DEFAULT 0,
                invoiceexclusivegsttotalamount DECIMAL(20,4) DEFAULT 0,
                averageShopSaleFactor DECIMAL(20,4) DEFAULT 0,
                itemQtyShopsale DECIMAL(20,4) DEFAULT 0,
                shopsaletotalamount DECIMAL(20,4) DEFAULT 0,
                averageDCFactor DECIMAL(20,4) DEFAULT 0,
                dcitemQty DECIMAL(20,4) DEFAULT 0,
                dcexclusivegsttotalamount DECIMAL(20,4) DEFAULT 0
              ) ENGINE=InnoDB", $db);

    // First get all stock items with basic info - including materialcost from stockmaster
    DB_query("INSERT INTO stock_summary (stockid, mnfCode, mnfpno, description, materialcost, manufacturers_name)
              SELECT sm.stockid, sm.mnfCode, sm.mnfpno, sm.description, sm.materialcost, m.manufacturers_name
              FROM stockmaster sm
              LEFT JOIN manufacturers m ON m.manufacturers_id = sm.brand
              WHERE sm.mbflag IN ('B', 'M')", $db);

    // Create temporary table for opening stock moves
    DB_query("CREATE TEMPORARY TABLE opening_moves (
                stockid VARCHAR(50) NOT NULL,
                loccode VARCHAR(10) NOT NULL,
                stkmoveno INT NOT NULL,
                PRIMARY KEY (stockid, loccode)
              ) ENGINE=InnoDB", $db);

    // Find latest opening moves for each item/location
    DB_query("INSERT INTO opening_moves
              SELECT stockid, loccode, MAX(stkmoveno) as stkmoveno
              FROM stockmoves
              WHERE trandate <= '$from'
              AND trandate >= '2021-01-01'
              $location_condition
              GROUP BY stockid, loccode", $db);

    // Update opening quantities
    DB_query("UPDATE stock_summary ss
              JOIN (
                  SELECT sm.stockid, SUM(sm.newqoh) as qohA
                  FROM stockmoves sm
                  JOIN opening_moves om ON sm.stockid = om.stockid 
                                      AND sm.loccode = om.loccode
                                      AND sm.stkmoveno = om.stkmoveno
                  GROUP BY sm.stockid
              ) q ON ss.stockid = q.stockid
              SET ss.qohA = q.qohA", $db);

    // Create temporary table for closing stock moves
    DB_query("CREATE TEMPORARY TABLE closing_moves (
                stockid VARCHAR(50) NOT NULL,
                loccode VARCHAR(10) NOT NULL,
                stkmoveno INT NOT NULL,
                PRIMARY KEY (stockid, loccode)
              ) ENGINE=InnoDB", $db);

    // Find latest closing moves for each item/location
    DB_query("INSERT INTO closing_moves
              SELECT stockid, loccode, MAX(stkmoveno) as stkmoveno
              FROM stockmoves
              WHERE trandate <= '$to'
              AND trandate >= '2021-01-01'
              $location_condition
              GROUP BY stockid, loccode", $db);

    // Update closing quantities (qohB)
    DB_query("UPDATE stock_summary ss
              JOIN (
                  SELECT sm.stockid, SUM(sm.newqoh) as qohB
                  FROM stockmoves sm
                  JOIN closing_moves cm ON sm.stockid = cm.stockid 
                                      AND sm.loccode = cm.loccode
                                      AND sm.stkmoveno = cm.stkmoveno
                  GROUP BY sm.stockid
              ) q ON ss.stockid = q.stockid
              SET ss.qohB = q.qohB", $db);

    // Get invoice data (filter by location if selected)
    $invoice_location_join = empty($location) ? "" : "JOIN debtorsmaster dm ON dm.debtorno = sc.debtorno AND dm.debtorno LIKE '%$location%'";
    DB_query("UPDATE stock_summary ss
              JOIN (
                  SELECT stkcode as stockid,
                         AVG(unitprice*(1-discountpercent)) as averageInvoiceFactor,
                         SUM(quantity) as invoiceitemQty,
                         SUM(CASE WHEN i.gst LIKE '%inclusive%' THEN  
                             (unitprice * (1 - discountpercent) * quantity)
                             ELSE (unitprice * (1 - discountpercent) * quantity)*1.17 END) as invoiceexclusivegsttotalamount
                  FROM invoicedetails id
                  JOIN invoice i ON i.invoiceno = id.invoiceno
                  JOIN salescase sc ON sc.salescaseref = i.salescaseref
                  $invoice_location_join
                  WHERE i.inprogress = 0
                  AND i.returned = 0
                  AND i.invoicesdate BETWEEN '$from' AND '$to'
                  GROUP BY stkcode
              ) inv ON ss.stockid = inv.stockid
              SET ss.averageInvoiceFactor = inv.averageInvoiceFactor,
                  ss.invoiceitemQty = inv.invoiceitemQty,
                  ss.invoiceexclusivegsttotalamount = inv.invoiceexclusivegsttotalamount", $db);

    // Get shop sale data (filter by location if selected)
    $shop_location_condition = empty($location) ? "" : "AND dm.debtorno LIKE '%$location%'";
    DB_query("UPDATE stock_summary ss
              JOIN (
                  SELECT s.stockid,
                         AVG(s.rate) as averageShopSaleFactor,
                         SUM(s.quantity) as itemQtyShopsale,
                         SUM((s.quantity * s.rate * (1 - s.discountpercent/100))) as shopsaletotalamount
                  FROM shopsalesitems s
                  JOIN shopsale ss ON ss.orderno = s.orderno
                  JOIN debtorsmaster dm ON dm.debtorno = ss.debtorno
                  WHERE ss.orddate BETWEEN '$from' AND '$to'
                  $shop_location_condition
                  GROUP BY s.stockid
              ) shop ON ss.stockid = shop.stockid
              SET ss.averageShopSaleFactor = shop.averageShopSaleFactor,
                  ss.itemQtyShopsale = shop.itemQtyShopsale,
                  ss.shopsaletotalamount = shop.shopsaletotalamount", $db);

    // Get DC data (all locations)
    DB_query("UPDATE stock_summary ss
              JOIN (
                  SELECT d.stkcode as stockid,
                         AVG(d.unitprice*(1-d.discountpercent)) as averageDCFactor,
                         SUM(d.quantity) as dcitemQty,
                         SUM(CASE WHEN dc.gst LIKE '%inclusive%' THEN  
                             (d.unitprice * (1 - d.discountpercent) * d.quantity)
                             ELSE (d.unitprice * (1 - d.discountpercent) * d.quantity)*1.17 END) as dcexclusivegsttotalamount
                  FROM dcdetails d
                  JOIN dcs dc ON dc.orderno = d.orderno
                  WHERE dc.orddate BETWEEN '$from' AND '$to'
                  GROUP BY d.stkcode
              ) dc ON ss.stockid = dc.stockid
              SET ss.averageDCFactor = dc.averageDCFactor,
                  ss.dcitemQty = dc.dcitemQty,
                  ss.dcexclusivegsttotalamount = dc.dcexclusivegsttotalamount", $db);

    // Clean up temporary tables
    DB_query("DROP TEMPORARY TABLE IF EXISTS opening_moves", $db);
    DB_query("DROP TEMPORARY TABLE IF EXISTS closing_moves", $db);

    // Fetch the final result - including the original materialcost from stockmaster
    $result = DB_query("SELECT * FROM stock_summary", $db);
    while ($row = DB_fetch_array($result)) {
        $response[] = $row;
    }

    echo json_encode($response);
    return;
}

include_once("includes/header.php");
include_once("includes/sidebar.php");
?>

<style>
    .date {
        padding: 10px;
        border-radius: 7px;
    }
    thead tr, tfoot tr {
        background-color: #424242;
        color: white;
    }
    .dataTables_wrapper {
        overflow-x: auto;
    }
    #loadingMessage {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 20px;
        border-radius: 5px;
        z-index: 1000;
        display: none;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="col-md-12">
            <h1>Cross Sectional Stock Analysis</h1>
        </div>
        <label>From Date</label>
        <input type="date" class="date fromDate">
        <label>To Date</label>
        <input type="date" class="date toDate">

        <!-- <label style="margin-top: 15px;">Location: </label>
        <select name="Location" class="Location">
            <option value="">All</option>
            <option value="MT">MT</option>
            <option value="HO">HO</option>
            <option value="SR">SR</option>
        </select> -->
        <button class="btn btn-success date searchData">Search</button>
    </section>

    <div id="loadingMessage">Loading data... This may take a few minutes for large date ranges.</div>

    <section class="content">
        <table class="table table-striped table-responsive" border="1" id="datatable">
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Manufacturer Code</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>Int. Ref. Quantity</th>
                    <th>DC Quantity</th>
                    <th>DC Value</th>
                    <th>Inv. Quantity</th>
                    <th>Inv. Value</th>
                    <th>Inv. Factor</th>
                    <th>Shop Sale Quantity</th>
                    <th>Shop Sale Value</th>
                    <th>Shop Sale Factor</th>
                    <th>Final Ref. Quantity</th>
                    <th>Current List Price</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Stock ID</th>
                    <th>Manufacturer Code</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>Int. Ref. Quantity</th>
                    <th>DC Quantity</th>
                    <th>DC Value</th>
                    <th>Inv. Quantity</th>
                    <th>Inv. Value</th>
                    <th>Inv. Factor</th>
                    <th>Shop Sale Quantity</th>
                    <th>Shop Sale Value</th>
                    <th>Shop Sale Factor</th>
                    <th>Final Ref. Quantity</th>
                    <th>Current List Price</th>
                </tr>
            </tfoot>
        </table>
    </section>
</div>

<?php include_once("includes/footer.php"); ?>

<script>
$(document).ready(function() {
    let table = $('#datatable').DataTable({
        dom: 'Bflrtip',
        lengthMenu: [10, 25, 50, 75, 100],
        buttons: [
            'copy',
            {
                text: 'Download CSV',
                action: function() {
                    let from = $('.fromDate').val();
                    let to = $('.toDate').val();
                    let Location = $(".Location").val();

                    if (!from || !to) {
                        alert("Please select both From and To dates.");
                        return;
                    }

                    let exportUrl = `export_stock_analysis.php?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&Location=${encodeURIComponent(Location)}`;
                    window.location.href = exportUrl;
                }
            },
            'excel'
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search..."
        },
        columns: [
            { data: "stockid" },
            { data: "mnfCode" },
            { data: "mnfpno" },
            { data: "description" },
            { data: "manufacturers_name" },
            { data: "qohA", render: $.fn.dataTable.render.number(',', '.', 0) },
            { data: "dcitemQty", render: $.fn.dataTable.render.number(',', '.', 0) },
            { data: "dcexclusivegsttotalamount", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "invoiceitemQty", render: $.fn.dataTable.render.number(',', '.', 0) },
            { data: "invoiceexclusivegsttotalamount", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "averageInvoiceFactor", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "itemQtyShopsale", render: $.fn.dataTable.render.number(',', '.', 0) },
            { data: "shopsaletotalamount", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "averageShopSaleFactor", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "qohB", render: $.fn.dataTable.render.number(',', '.', 0) },
            { data: "materialcost", render: $.fn.dataTable.render.number(',', '.', 2) }
        ],
        initComplete: function() {
            // Add search inputs to footer
            this.api().columns().every(function() {
                var column = this;
                if (column.header().textContent !== "Amount" && column.header().textContent !== "Statement") {
                    $('<input type="text" placeholder="Search '+column.header().textContent+'" />')
                        .appendTo($(column.footer()).empty())
                        .on('keyup change', function() {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                }
            });
        }
    });

    $(".searchData").on("click", function() {
        let from = $(".fromDate").val();
        let to = $(".toDate").val();
        let Location = $(".Location").val();
        
        if (!from || !to) {
            alert("Please select both From and To dates.");
            return;
        }

        $("#loadingMessage").show();
        table.clear().draw();
        
        $.ajax({
            url: "crosssection.php",
            method: "POST",
            data: { from, to, Location },
            dataType: "json",
            success: function(response) {
                table.rows.add(response).draw();
                $("#loadingMessage").hide();
            },
            error: function(xhr, status, error) {
                alert("Error loading data: " + error);
                $("#loadingMessage").hide();
            }
        });
    });
});
</script>

<?php include_once("includes/foot.php"); ?>