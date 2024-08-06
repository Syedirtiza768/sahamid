<?php

$active = "reports";
$AllowAnyone = true;

include_once("config.php");

if (!userHasPermission($db, "show_dc_attachments_view")) {
    header("Location: /sahamid");
    return;
}


if (isset($_POST['to'])) {
    $from = $_POST['from'];
    $to = $_POST['to'];
    $userId = $_SESSION['UserID'];
    $realName = $_SESSION['UsersRealName'];
    $rootPath = "../" . $_SESSION['part_pics_dir'] . '/';

    // Build SQL query based on permissions
    $permissionCondition = userHasPermission($db, "show_all_dcs_attachments")
        ? ""
        : ' AND dcs.orderno IN (
                SELECT dcs.orderno 
                FROM dcs 
                INNER JOIN salescase ON dcs.salescaseref = salescase.salescaseref
                INNER JOIN salesman ON salescase.salesman = salesman.salesmanname
                WHERE salesman.salesmanname = "' . $realName . '"
                OR salesman.salesmancode IN (
                    SELECT salescase_permissions.user 
                    FROM salescase_permissions
                    WHERE salescase_permissions.can_access = "' . $userId . '"
                )
            )';

    $sqlDcs = 'SELECT dcs.orderno AS dcno, dcs.orddate AS date, dcs.salescaseref, debtorsmaster.name AS client, 
                       debtorsmaster.dba, dcs.invoicegroupid, dcs.dcstatus
               FROM dcs
               INNER JOIN debtorsmaster ON debtorsmaster.debtorno = dcs.debtorno
               WHERE dcs.orddate >= "' . $from . '" AND dcs.orddate <= "' . $to . '"' . $permissionCondition;

    $resDcs = mysqli_query($db, $sqlDcs);

    $response = [];
    $fileTypes = ['PO', 'CourierSlip', 'GRB', 'Invoice', 'CommercialInvoice'];
    $filePaths = [];

    foreach ($fileTypes as $type) {
        $filePaths[$type] = glob($rootPath . $type . '_*.pdf');
    }

    while ($rowDcs = mysqli_fetch_assoc($resDcs)) {
        $dcno = $rowDcs['dcno'];
        $attachments = [];

        foreach ($fileTypes as $type) {
            $attachments[$type] = [];
            foreach ($filePaths[$type] as $file) {
                if (strpos($file, $dcno) !== false) {
                    $isDeleted = strpos($file, 'deleted') !== false;
                    $attachments[$type][] = [
                        'url' => $rootPath . basename($file),
                        'deleted' => $isDeleted
                    ];
                }
            }
        }

        $response[] = [
            'dcno' => $dcno,
            'date' => $rowDcs['date'],
            'salescaseref' => $rowDcs['salescaseref'],
            'client' => $rowDcs['client'],
            'dba' => $rowDcs['dba'],
            'invoicegroupid' => $rowDcs['invoicegroupid'],
            'dcstatus' => $rowDcs['dcstatus'],
            'sendtoinvoice' => empty($rowDcs['dcstatus']) ? "<input type='checkbox' class='booked' name='booked' value=1>" : $rowDcs['dcstatus'],
            'dcinvoiced' => empty($rowDcs['dcstatus']) ? "<input type='checkbox' class='booked' name='booked' value=1>" : $rowDcs['dcstatus'],
            'polist' => generateForm('PO', $dcno),
            'couriersliplist' => generateForm('CourierSlip', $dcno),
            'oldgrblist' => generateForm('GRB', $dcno),
            'oldinvoicelist' => generateForm('Invoice', $dcno),
            'oldcommercialinvoicelist' => generateForm('CommercialInvoice', $dcno),
            'attachments' => $attachments
        ];
    }

    echo json_encode($response);
    return;
}

function generateForm($type, $dcno) {
    return "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
                <input type='hidden' name='FormID' value='" . htmlspecialchars($_SESSION['FormID']) . "' />
                <input type='file' id='attach" . htmlspecialchars($type) . "File" . htmlspecialchars($dcno) . "' data-orderno='" . htmlspecialchars($dcno) . "' class='attach" . htmlspecialchars($type) . "File' name='" . htmlspecialchars($type) . "'>
                <input type='button' id='upload" . htmlspecialchars($type) . "File' data-orderno='" . htmlspecialchars($dcno) . "' class='upload" . htmlspecialchars($type) . "File' name='upload" . htmlspecialchars($type) . "File' value='upload" . htmlspecialchars($type) . "'>
            </form>";
}


include_once("includes/header.php");
include_once("includes/sidebar.php");

?>

<style>
    .date {
        padding: 10px;
        border-radius: 7px;
    }

    thead tr,
    tfoot tr {
        background-color: #424242;
        color: white;
    }
</style>

<div class="content-wrapper" style="overflow: scroll;">

    <section class="content-header">
        <div class="col-md-12">
            <h1>Manage DC Attachments</h1>
        </div>
        <!-- <label>From Date</label>
    	<input type="date" class="date fromDate">
		<label>To Date</label> -->
        FROM <input type="date" class="date fromDate">
        TO <input type="date" class="date toDate">
        <button class="btn btn-success date searchData">Search</button>
    </section>

    <section class="content">

        <table class="table table-striped table-responsive" border="1" id="datatable">
            <thead>
                <tr>
                    <th>DC No</th>
                    <th>Date</th>
                    <th>Salescaseref</th>
                    <th>Customer Name</th>
                    <th>DBA</th>
                    <th>DC Internal</th>
                    <th>DC External</th>
                    <!-- <th>Send For Invoice</th>
					<th>Invoice DC</th>-->
                    <th>Purchase Order</th>
                    <th>Courier Slip</th>
                    <th>Tax Invoice</th>
                    <th>Commercial Invoice</th>
                    <th>GRB</th>
                    <th>System Invoice</th>
                    <th>System GRB</th>

                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th>DC No</th>
                    <th>Date</th>
                    <th>Salescaseref</th>
                    <th>Customer Name</th>
                    <th>DBA</th>
                    <th>DC Internal</th>
                    <th>DC External</th>
                    <!-- <th>Send For Invoice</th>
                    <th>Invoice DC</th>-->
                    <th>Purchase Order</th>
                    <th>Courier Slip</th>
                    <th>Tax Invoice</th>
                    <th>Commercial Invoice</th>
                    <th>GRB</th>
                    <th>System Invoice</th>
                    <th>System GRB</th>
                </tr>
            </tfoot>
        </table>

    </section>

</div>

<?php
include_once("includes/footer.php");
?>
<script>
    $(document).ready(function() {
        let table = $('#datatable').DataTable({
            <?php
            if (userHasPermission($db, "show_dc_attachments_csv")) {
                echo "dom: 'Bflrtip',
        buttons: [
                'excelHtml5',
                'csvHtml5',
            ],";
            } ?>
            // dom: 'Bflrtip',
            /*buttons: [
                'excelHtml5',
                'csvHtml5',
            ],*/
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search..."
            },
            columns: [{
                    "data": "dcno"
                },
                {
                    "data": "date"
                },
                {
                    "data": "salescaseref"
                },
                {
                    "data": "client"
                },
                {
                    "data": "dba"
                },
                {
                    "data": "dcno"
                },
                {
                    "data": "dcno"
                },
                /* {"data":"sendtoinvoice"},
                 {"data":"dcinvoiced"},*/
                {
                    "data": "polist"
                },
                {
                    "data": "couriersliplist"
                },
                {
                    "data": "oldinvoicelist"
                },
                {
                    "data": "oldcommercialinvoicelist"
                },
                {
                    "data": "oldgrblist"
                },
                {
                    "data": "invoicelist"
                },
                {
                    "data": "grblist"
                },
            ],
            "columnDefs": [{
                    "render": function(data, type, row) {
                        let html = '<a href="../salescase/salescaseview.php?salescaseref=' + data + '" target="_blank">' + data + '</a>';

                        return html;


                    },
                    "targets": [2]
                },

                {
                    "render": function(data, type, row) {
                        let html = '<a target="_blank" href = "../PDFDCh.php?DCNo=' + data + '">' + data + '</a>';
                        return html;

                    },
                    "targets": [5]
                },
                {
                    "render": function(data, type, row) {
                        let html = '<a target="_blank" href = "../PDFDChExternal.php?DCNo=' + data + '">' + data + '</a>';
                        return html;

                    },
                    "targets": [6]
                },

            ]
        });

        $('#datatable tfoot th').each(function(i) {
            var title = $('#datatable thead th').eq($(this).index()).text();

            $(this).html('<input type="text" placeholder="Search ' + title + '" data-index="' + i + '" style="border:1px solid #424242; border-radius:7px; color:black; text-align:center" />');

        });

        table.columns().every(function() {
            var that = this;
            $('input', this.footer()).on('keyup change', function() {
                if (that.search() !== this.value) {
                    that.search(this.value).draw();
                }
            });
        });
        $('#datatable').on('click', '.uploadPOFile', function() {
            var ref = $(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachPOFile' + orderno)[0].files[0];

            fd.append('PO', files);
            fd.append('orderno', orderno);

            $.ajax({
                url: 'api/uploadPOFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res) {


                        ref.parent().parent().html(res);
                    } else {
                        alert('file not uploaded');
                    }
                },
            });
        });
        $('#datatable').on('click', '.uploadCourierSlipFile', function() {
            var ref = $(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachCourierSlipFile' + orderno)[0].files[0];

            fd.append('CourierSlip', files);
            fd.append('orderno', orderno);

            $.ajax({
                url: 'api/uploadCourierSlipFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res) {

                        ref.parent().parent().html(res);
                    } else {
                        alert('file not uploaded');
                    }
                },
            });
        });

        $('#datatable').on('click', '.uploadInvoiceFile', function() {
            var ref = $(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachInvoiceFile' + orderno)[0].files[0];

            fd.append('Invoice', files);
            fd.append('orderno', orderno);

            $.ajax({
                url: 'api/uploadInvoiceFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res) {

                        ref.parent().parent().html(res);
                    } else {
                        alert('file not uploaded');
                    }
                },
            });
        });
        $('#datatable').on('click', '.uploadCommercialInvoiceFile', function() {
            var ref = $(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachCommercialInvoiceFile' + orderno)[0].files[0];

            fd.append('CommercialInvoice', files);
            fd.append('orderno', orderno);

            $.ajax({
                url: 'api/uploadCommercialInvoiceFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res) {

                        ref.parent().parent().html(res);
                    } else {
                        alert('file not uploaded');
                    }
                },
            });
        });

        $('#datatable').on('click', '.uploadGRBFile', function() {
            var ref = $(this);
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();


            var files = $('#attachGRBFile' + orderno)[0].files[0];

            fd.append('GRB', files);
            fd.append('orderno', orderno);

            $.ajax({
                url: 'api/uploadGRBFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res) {

                        ref.parent().parent().html(res);
                    } else {
                        alert('file not uploaded');
                    }
                },
            });
        });
        $('#datatable').on('click', '.removeFile', function() {
            var ref = $(this);
            var basepath = $(this).attr('data-basepath');
            var orderno = $(this).attr('data-orderno');
            var fd = new FormData();
            fd.append('basepath', basepath);
            fd.append('orderno', orderno);

            $.ajax({
                url: 'api/removeFile.php',
                type: 'post',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    if (res) {

                        ref.prev().css("background-color", "red");
                        ref.remove();
                    } else {
                        alert('file not uploaded');
                    }
                },
            });
        });

        $(".searchData").on("click", function() {
            let from = $(".fromDate").val();
            let to = $(".toDate").val();
            let FormID = '<?php echo $_SESSION['FormID']; ?>';

            table.clear().draw();
            $("tbody tr td").html("<h3>Loading ... (This may take a few minutes) </h3>");
            $.post("dcattachments.php", {
                from,
                to,
                FormID
            }, function(res, status) {
            console.log(res);
                res = JSON.parse(res);
                table.rows.add(res).draw();
            });
        });

    });
</script>
<?php
include_once("includes/foot.php");
?>