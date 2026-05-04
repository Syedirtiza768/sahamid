<?php
$active = "reports";
$AllowAnyone = true;

// Change to v2/ directory so config.php's relative includes resolve correctly
chdir(__DIR__ . '/..');
include_once("config.php");

ini_set('memory_limit', '1024M');
set_time_limit(600);

if (!userHasPermission($db, "top_items_quotation_report")) {
    header("Location: /sahamid");
    return;
}

if (isset($_POST['to'])) {
    set_time_limit(300);
    ob_clean();
    header('Content-Type: application/json');

    // Connection defaults to utf8; DB default for TEMPORARY tables is often latin1 — then UTF-8 in
    // stockmaster.description cannot be stored. Use utf8mb4 for this request and for temp tables.
    mysqli_set_charset($db, 'utf8mb4');

    $fromInput = trim($_POST['from'] ?? '');
    $toInput = trim($_POST['to'] ?? '');
    $fromDate = DateTime::createFromFormat('Y-m-d', $fromInput);
    $toDate = DateTime::createFromFormat('Y-m-d', $toInput);
    $fromValid = $fromDate && $fromDate->format('Y-m-d') === $fromInput;
    $toValid = $toDate && $toDate->format('Y-m-d') === $toInput;
    if (!$fromValid || !$toValid) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format. Expected YYYY-MM-DD.']);
        exit;
    }
    if ($fromDate > $toDate) {
        http_response_code(400);
        echo json_encode(['error' => 'From date must be on or before To date.']);
        exit;
    }
    $from = mysqli_real_escape_string($db, $fromInput);
    $to = mysqli_real_escape_string($db, $toInput);

    // Helper: run query without DB_query (which outputs HTML on error)
    function run_sql($sql, $conn) {
        $r = mysqli_query($conn, $sql);
        if (!$r) {
            ob_clean();
            echo json_encode(['error' => mysqli_error($conn), 'sql' => substr($sql, 0, 200)]);
            exit;
        }
        return $r;
    }

    // Aligned with itemsReport/index.php pricing method
    function calculatePriceForStock($parchinos, $requested_qty)
    {
        if ($requested_qty <= 0 || empty($parchinos)) {
            return [
                'total_bpitems_price' => 0,
                'weighted_unit_price' => 0,
                'total_quantity' => 0
            ];
        }

        $remaining_qty = $requested_qty;
        $total_allocated_qty = 0;
        $total_weighted_price = 0;

        foreach ($parchinos as $parchino) {
            if ($remaining_qty <= 0) break;

            $available_qty = (float)$parchino['quantity'];

            // Use adjust_unit_price if available and > 0, otherwise use original price
            $unit_price = (float)($parchino['adjust_unit_price'] ?? 0);
            if ($unit_price <= 0) {
                $unit_price = (float)$parchino['price'];
            }

            // Apply landing factor exactly like itemsReport
            $landing_factor = (float)($parchino['landing_factor'] ?? 1);
            if ($landing_factor <= 0) {
                $landing_factor = 1;
            }
            $effective_price = $unit_price * $landing_factor;

            $allocated_qty = min($available_qty, $remaining_qty);
            if ($allocated_qty > 0) {
                $allocated_price = $allocated_qty * $effective_price;
                $total_weighted_price += $allocated_price;
                $total_allocated_qty += $allocated_qty;
                $remaining_qty -= $allocated_qty;
            }
        }

        $weighted_unit_price = $total_allocated_qty > 0
            ? $total_weighted_price / $total_allocated_qty
            : 0;

        return [
            'total_bpitems_price' => round($total_weighted_price, 2),
            'weighted_unit_price' => round($weighted_unit_price, 2),
            'total_quantity' => round($total_allocated_qty, 2)
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // STEP 1: Create temp table with all stock items in one shot
    // ═══════════════════════════════════════════════════════════
    run_sql("CREATE TEMPORARY TABLE tmp_report (
        stockid VARCHAR(50) NOT NULL PRIMARY KEY,
        mnfCode VARCHAR(50),
        mnfpno VARCHAR(50),
        description VARCHAR(255),
        manufacturers_name VARCHAR(100),
        qohA DECIMAL(20,4) DEFAULT 0,
        qohB DECIMAL(20,4) DEFAULT 0
    ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);

    // Omit service/test SKUs from this report (shared list with v2/crosssection.php).
    $crosssection_excluded_stockids = include __DIR__ . '/../crosssection_excluded_skus.php';
    $crosssection_excluded_sql = implode(',', array_map(function ($id) use ($db) {
        return "'" . mysqli_real_escape_string($db, $id) . "'";
    }, $crosssection_excluded_stockids));
    run_sql("INSERT INTO tmp_report (stockid, mnfCode, mnfpno, description, manufacturers_name)
              SELECT sm.stockid, sm.mnfCode, sm.mnfpno, sm.description, m.manufacturers_name
              FROM stockmaster sm
              LEFT JOIN manufacturers m ON m.manufacturers_id = sm.brand
              WHERE sm.mbflag IN ('B','M')
                AND sm.stockid NOT IN ($crosssection_excluded_sql)", $db);

    // ═══════════════════════════════════════════════════════════
    // STEP 2: Opening stock — one bulk query using temp tables
    // ═══════════════════════════════════════════════════════════
    run_sql("CREATE TEMPORARY TABLE tmp_open_moves (
        stockid VARCHAR(50) NOT NULL,
        loccode VARCHAR(10) NOT NULL,
        stkmoveno INT NOT NULL,
        PRIMARY KEY (stockid, loccode)
    ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);

    run_sql("INSERT INTO tmp_open_moves
              SELECT stockid, loccode, MAX(stkmoveno)
              FROM stockmoves sm
              LEFT JOIN systypes st ON sm.type = st.typeid
              WHERE trandate <= '$from'
                AND NOT (
                    LOWER(COALESCE(st.typename, '')) = 'dc'
                    OR LOWER(COALESCE(st.typename, '')) LIKE '%shop sale%'
                )
              GROUP BY stockid, loccode", $db);

    run_sql("UPDATE tmp_report r
              INNER JOIN (
                  SELECT sm.stockid, SUM(sm.newqoh) AS qohA
                  FROM stockmoves sm
                  INNER JOIN tmp_open_moves om
                      ON sm.stockid = om.stockid
                     AND sm.loccode = om.loccode
                     AND sm.stkmoveno = om.stkmoveno
                  GROUP BY sm.stockid
              ) q ON r.stockid = q.stockid
              SET r.qohA = q.qohA", $db);

    run_sql("DROP TEMPORARY TABLE tmp_open_moves", $db);

    // ═══════════════════════════════════════════════════════════
    // STEP 3: Closing stock — one bulk query using temp tables
    // ═══════════════════════════════════════════════════════════
    run_sql("CREATE TEMPORARY TABLE tmp_close_moves (
        stockid VARCHAR(50) NOT NULL,
        loccode VARCHAR(10) NOT NULL,
        stkmoveno INT NOT NULL,
        PRIMARY KEY (stockid, loccode)
    ) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci", $db);

    run_sql("INSERT INTO tmp_close_moves
              SELECT stockid, loccode, MAX(stkmoveno)
              FROM stockmoves sm
              LEFT JOIN systypes st ON sm.type = st.typeid
              WHERE trandate <= '$to'
                AND NOT (
                    LOWER(COALESCE(st.typename, '')) = 'dc'
                    OR LOWER(COALESCE(st.typename, '')) LIKE '%shop sale%'
                )
              GROUP BY stockid, loccode", $db);

    run_sql("UPDATE tmp_report r
              INNER JOIN (
                  SELECT sm.stockid, SUM(sm.newqoh) AS qohB
                  FROM stockmoves sm
                  INNER JOIN tmp_close_moves cm
                      ON sm.stockid = cm.stockid
                     AND sm.loccode = cm.loccode
                     AND sm.stkmoveno = cm.stkmoveno
                  GROUP BY sm.stockid
              ) q ON r.stockid = q.stockid
              SET r.qohB = q.qohB", $db);

    run_sql("DROP TEMPORARY TABLE tmp_close_moves", $db);

    // ═══════════════════════════════════════════════════════════
    // STEP 4: Load igp_parchi pricing & calculate weighted prices
    // ═══════════════════════════════════════════════════════════
    $parchinoData = [];
    $sqlParchi = "SELECT p.stockid, p.quantity, p.price, p.adjust_unit_price, p.landing_factor
                  FROM igp_parchi p
                  INNER JOIN tmp_report r ON p.stockid = r.stockid
                  ORDER BY p.stockid, p.pdate DESC, p.id DESC";
    $resParchi = run_sql($sqlParchi, $db);
    while ($r = DB_fetch_array($resParchi)) {
        $sid = $r['stockid'];
        if (!isset($parchinoData[$sid])) {
            $parchinoData[$sid] = [];
        }
        $parchinoData[$sid][] = [
            'quantity'           => (float)$r['quantity'],
            'price'              => (float)$r['price'],
            'adjust_unit_price'  => (float)($r['adjust_unit_price'] ?? 0),
            'landing_factor'     => (float)($r['landing_factor'] ?? 1)
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // STEP 5: Fetch tmp_report and compute final values in PHP
    // ═══════════════════════════════════════════════════════════
    $response = [];
    $result = run_sql("SELECT * FROM tmp_report", $db);
    while ($item = DB_fetch_array($result)) {
        $openQty  = (float)$item['qohA'];
        $closeQty = (float)$item['qohB'];
        $sid      = $item['stockid'];

        // Weighted unit price from igp_parchi (same method as itemsReport)
        $qtyForPrice = max($openQty, $closeQty);
        $priceData = calculatePriceForStock($parchinoData[$sid] ?? [], $qtyForPrice);
        $unitPrice = (float)$priceData['weighted_unit_price'];

        // Pickup per-SKU landing factor exactly from latest igp_parchi row,
        // same source used by itemsReport frontend payload.
        $latestAdjust = 0;
        $latestLandingFactor = 1;
        if (!empty($parchinoData[$sid])) {
            $latest = $parchinoData[$sid][0]; // ORDER BY pdate DESC, id DESC
            $latestAdjust = (float)($latest['adjust_unit_price'] ?? 0);
            $latestLandingFactor = (float)($latest['landing_factor'] ?? 1);
        }
        if ($latestLandingFactor <= 0) {
            // If latest row has 0/blank factor, pick nearest valid factor for this SKU.
            if (!empty($parchinoData[$sid])) {
                foreach ($parchinoData[$sid] as $pRow) {
                    $candidateFactor = (float)($pRow['landing_factor'] ?? 0);
                    if ($candidateFactor > 0) {
                        $latestLandingFactor = $candidateFactor;
                        break;
                    }
                }
            }
            if ($latestLandingFactor <= 0) {
                $latestLandingFactor = 1;
            }
        }

        // Match itemsReport/frontend.php valuation fallback:
        // weighted_unit_price first, else adjust_unit_price.
        $effectiveUnitForValuation = $unitPrice > 0 ? $unitPrice : $latestAdjust;

        $item['adjust_unit_price'] = $latestAdjust;
        $item['landing_factor']    = $latestLandingFactor;
        // Apply adjusted-price fallback for the exposed unit cost as well.
        $item['unitPriceCost']     = round($effectiveUnitForValuation, 2);
        $item['totalAmountFrom']   = round($openQty * $effectiveUnitForValuation * $latestLandingFactor, 2);
        $item['totalAmountTo']     = round($closeQty * $effectiveUnitForValuation * $latestLandingFactor, 2);

        $response[] = $item;
        unset($parchinoData[$sid]);
    }

    mysqli_query($db, "DROP TEMPORARY TABLE IF EXISTS tmp_report");

    echo json_encode($response);
    exit;
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
            <h1>Cross Sectional Stock Analysis (v2)</h1>
        </div>
        <label>From Date</label>
        <input type="date" class="date fromDate">
        <label>To Date</label>
        <input type="date" class="date toDate">
        <button class="btn btn-success date searchData">Search</button>
    </section>

    <div id="loadingMessage">Loading data... This may take a few minutes for large date ranges.</div>

    <section class="content">
        <div class="row" id="stockValueSummaryRow" style="margin-bottom: 10px;">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="ion ion-cash"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="cardTotalStartLabel">Total stock value (start date)</span>
                        <span class="info-box-number" id="cardTotalStartValue">—</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="ion ion-cash"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="cardTotalEndLabel">Total stock value (end date)</span>
                        <span class="info-box-number" id="cardTotalEndValue">—</span>
                    </div>
                </div>
            </div>
        </div>
        <table class="table table-striped table-responsive" border="1" id="datatable">
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Manufacturers Code</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th id="thQtyFrom">Int. Ref. Quantity</th>
                    <th>Unit Price Cost</th>
                    <th id="thQtyTo">Qty</th>
                    <th id="thTotalFrom">Total Amount @From</th>
                    <th id="thTotalTo">Total Amount @To</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>Stock ID</th>
                    <th>Manufacturers Code</th>
                    <th>Part No.</th>
                    <th>Description</th>
                    <th>Brand</th>
                    <th>Int. Ref. Quantity</th>
                    <th>Unit Price Cost</th>
                    <th>Qty</th>
                    <th>Total Amount @From</th>
                    <th>Total Amount @To</th>
                </tr>
            </tfoot>
        </table>
    </section>
</div>

<?php include_once("includes/footer.php"); ?>

<script>
$(document).ready(function() {
    function formatAmount2(n) {
        var x = Math.round((parseFloat(n) + Number.EPSILON) * 100) / 100;
        var parts = x.toFixed(2).split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }

    function updateStockValueCards(response, fromDate, toDate) {
        var totalFrom = 0, totalTo = 0;
        for (var i = 0; i < response.length; i++) {
            var row = response[i] || {};
            var unit = parseFloat(row.unitPriceCost) || 0;
            var qFrom = parseFloat(row.qohA) || 0;
            var qTo = parseFloat(row.qohB) || 0;
            totalFrom += qFrom * unit;
            totalTo += qTo * unit;
        }
        $('#cardTotalStartLabel').text('Total stock value on ' + fromDate + ' (start)');
        $('#cardTotalEndLabel').text('Total stock value on ' + toDate + ' (end)');
        $('#cardTotalStartValue').text(formatAmount2(totalFrom));
        $('#cardTotalEndValue').text(formatAmount2(totalTo));
    }

    let table = $('#datatable').DataTable({
        dom: 'Bflrtip',
        lengthMenu: [10, 25, 50, 75, 100],
        deferRender: true,
        buttons: [
            'copy',
            {
                text: 'Download CSV',
                action: function() {
                    let from = $('.fromDate').val();
                    let to = $('.toDate').val();

                    if (!from || !to) {
                        alert("Please select both From and To dates.");
                        return;
                    }

                    let data = table.rows().data();
                    let headers = ['Stock ID','Manufacturers Code','Part No.','Description','Brand',
                        'Int. Ref. Quantity ' + from, 'Unit Price Cost',
                        'Qty ' + to, 'Total Amount @' + from, 'Total Amount @' + to];
                    let csv = headers.join(',') + '\n';

                    for (let i = 0; i < data.length; i++) {
                        let r = data[i];
                        let row = [
                            '"' + (r.stockid || '') + '"',
                            '"' + (r.mnfCode || '') + '"',
                            '"' + (r.mnfpno || '') + '"',
                            '"' + (r.description || '').replace(/"/g, '""') + '"',
                            '"' + (r.manufacturers_name || '') + '"',
                            parseFloat(r.qohA || 0),
                            parseFloat(r.unitPriceCost || 0).toFixed(2),
                            parseFloat(r.qohB || 0),
                            parseFloat(r.totalAmountFrom || 0).toFixed(2),
                            parseFloat(r.totalAmountTo || 0).toFixed(2)
                        ];
                        csv += row.join(',') + '\n';
                    }

                    let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    let link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'cross_section_v2_' + from + '_' + to + '.csv';
                    link.click();
                    URL.revokeObjectURL(link.href);
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
            { data: "qohA", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "unitPriceCost", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "qohB", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "totalAmountFrom", render: $.fn.dataTable.render.number(',', '.', 2) },
            { data: "totalAmountTo", render: $.fn.dataTable.render.number(',', '.', 2) }
        ],
        initComplete: function() {
            this.api().columns().every(function() {
                var column = this;
                $('<input type="text" placeholder="Search '+column.header().textContent+'" />')
                    .appendTo($(column.footer()).empty())
                    .on('keyup change', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
            });
        }
    });

    var searchDebounceTimer = null;
    var searchActiveXhr = null;
    var searchRequestSeq = 0;
    var SEARCH_DEBOUNCE_MS = 500;

    function dedupeRowsByStockId(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            return rows;
        }
        var seen = Object.create(null);
        var out = [];
        for (var i = 0; i < rows.length; i++) {
            var raw = rows[i] && rows[i].stockid != null ? String(rows[i].stockid) : '';
            var key = raw !== '' ? raw.toLowerCase() : '';
            if (key !== '' && seen[key]) {
                continue;
            }
            if (key !== '') {
                seen[key] = true;
            }
            out.push(rows[i]);
        }
        return out;
    }

    function executeCrossSectionSearch() {
        var from = $(".fromDate").val();
        var to = $(".toDate").val();

        if (!from || !to) {
            alert("Please select both From and To dates.");
            $(".searchData").prop("disabled", false);
            return;
        }

        var mySeq = ++searchRequestSeq;

        if (searchActiveXhr) {
            searchActiveXhr.abort();
            searchActiveXhr = null;
        }

        $(".searchData").prop("disabled", true);

        $('#thQtyFrom').text('Int. Ref. Quantity ' + from);
        $('#thQtyTo').text('Qty ' + to);
        $('#thTotalFrom').text('Total Amount @' + from);
        $('#thTotalTo').text('Total Amount @' + to);

        $("#loadingMessage").show();
        $('#cardTotalStartValue, #cardTotalEndValue').text('…');
        table.clear().draw(false);

        var thisXhr = $.ajax({
            url: "index.php",
            method: "POST",
            data: { from: from, to: to },
            dataType: "json",
            timeout: 300000,
            success: function(response) {
                if (mySeq !== searchRequestSeq || thisXhr !== searchActiveXhr) {
                    return;
                }
                if (Array.isArray(response)) {
                    var rows = dedupeRowsByStockId(response);
                    table.clear();
                    table.rows.add(rows).draw();
                    updateStockValueCards(rows, from, to);
                } else if (response && response.error) {
                    alert("Server error: " + (response.error || "unknown"));
                    $('#cardTotalStartValue, #cardTotalEndValue').text('—');
                } else {
                    $('#cardTotalStartValue, #cardTotalEndValue').text('—');
                }
            },
            error: function(xhr, status, error) {
                if (mySeq !== searchRequestSeq || thisXhr !== searchActiveXhr) {
                    return;
                }
                if (status === 'abort') {
                    return;
                }
                if (status === 'timeout') {
                    alert("Request timed out. Try a smaller date range.");
                } else {
                    alert("Error loading data: " + error + "\n" + (xhr.responseText || '').substring(0, 200));
                }
                $('#cardTotalStartValue, #cardTotalEndValue').text('—');
            },
            complete: function(xhr, status) {
                if (mySeq !== searchRequestSeq || thisXhr !== searchActiveXhr) {
                    return;
                }
                searchActiveXhr = null;
                $("#loadingMessage").hide();
                $(".searchData").prop("disabled", false);
            }
        });
        searchActiveXhr = thisXhr;
    }

    $(".searchData").on("click", function() {
        var $btn = $(".searchData");
        if ($btn.prop("disabled")) {
            return;
        }
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function() {
            executeCrossSectionSearch();
        }, SEARCH_DEBOUNCE_MS);
    });
});
</script>

<?php include_once("includes/foot.php"); ?>
