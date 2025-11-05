<?php
// dcattachments.php
$active = "reports";
$AllowAnyone = true;

include_once("config.php");

// Main page permission check
if (!userHasPermission($db, "show_dc_attachments_view")) {
    header("Location: /sahamid");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage DC Attachments</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .date {
            padding: 10px;
            border-radius: 7px;
            border: 1px solid #dee2e6;
        }
        thead tr,
        tfoot tr {
            background-color: #424242 !important;
            color: white !important;
        }
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 20px 0;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 8px 8px;
        }
        .btn-custom {
            background: #667eea;
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            background: #764ba2;
            color: white;
        }
        .btn-file {
            background: #28a745;
            color: white;
        }
        .btn-file:hover {
            background: #218838;
            color: white;
        }
        .file-section {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
        }
        .file-section h6 {
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
            margin-bottom: 10px;
            color: #495057;
        }
        
        /* Fix for DataTable width */
        #datatable_wrapper {
            width: 100% !important;
        }
        #datatable {
            width: 100% !important;
            table-layout: auto !important;
        }
        .dataTables_scroll {
            width: 100% !important;
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
    </style>
</head>
<body>
    <!-- Alert Container for Notifications -->
    <div class="alert-container"></div>

    <div class="container-fluid p-0">
        <!-- Header Section -->
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-5 fw-bold">Manage DC Attachments</h1>
                        <p class="lead mb-0">Document management for delivery challans</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge bg-light text-dark fs-6">Professional Dashboard</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="container-fluid px-4">
            <div class="table-container">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">Filter Data</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <label class="col-form-label fw-bold">FROM</label>
                                    </div>
                                    <div class="col-auto">
                                        <input type="date" class="form-control date fromDate">
                                    </div>
                                    <div class="col-auto">
                                        <label class="col-form-label fw-bold">TO</label>
                                    </div>
                                    <div class="col-auto">
                                        <input type="date" class="form-control date toDate">
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-success btn-custom searchData">
                                            <i class="fas fa-search me-2"></i>Search Data
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DataTable Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-striped table-hover display nowrap" border="1" id="datatable" style="width:100%">
                                <thead class="table-dark">
                                    <tr>
                                        <th>DC No</th>
                                        <th>Date</th>
                                        <th>Salescaseref</th>
                                        <th>Customer Name</th>
                                        <th>DBA</th>
                                        <th>DC Internal</th>
                                        <th>DC External</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tfoot class="table-dark">
                                    <tr>
                                        <th>DC No</th>
                                        <th>Date</th>
                                        <th>Salescaseref</th>
                                        <th>Customer Name</th>
                                        <th>DBA</th>
                                        <th>DC Internal</th>
                                        <th>DC External</th>
                                        <th>Actions</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Details Modal -->
    <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileModalLabel">File Attachments - DC: <span id="modalDcNo"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="fileModalBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p>Loading file attachments...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

    <script>
        // Utility function to show alerts
        function showAlert(message, type = 'danger') {
            const alertContainer = $('.alert-container');
            const alertId = 'alert-' + Date.now();
            
            const alertHtml = `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            alertContainer.append(alertHtml);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                $('#' + alertId).alert('close');
            }, 5000);
        }

        $(document).ready(function() {
            let table = $('#datatable').DataTable({
                <?php if (userHasPermission($db, "show_dc_attachments_csv")): ?>
                dom: 'Bflrtip',
                buttons: [
                    'excelHtml5',
                    'csvHtml5',
                ],
                <?php endif; ?>
                scrollX: true,
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search..."
                },
                columns: [
                    {"data": "dcno"},
                    {"data": "date"},
                    {
                        "data": "salescaseref",
                        "render": function(data, type, row) {
                            return '<a href="../salescase/salescaseview.php?salescaseref=' + data + '" target="_blank" class="text-decoration-none">' + data + '</a>';
                        }
                    },
                    {"data": "client"},
                    {"data": "dba"},
                    {
                        "data": "dcno",
                        "render": function(data, type, row) {
                            return '<a target="_blank" href="../PDFDCh.php?DCNo=' + data + '" class="text-decoration-none">' + data + '</a>';
                        }
                    },
                    {
                        "data": "dcno",
                        "render": function(data, type, row) {
                            return '<a target="_blank" href="../PDFDChExternal.php?DCNo=' + data + '" class="text-decoration-none">' + data + '</a>';
                        }
                    },
                    {
                        "data": "dcno",
                        "render": function(data, type, row) {
                            return '<button class="btn btn-primary btn-sm view-files" data-dcno="' + data + '" title="View File Attachments">' +
                                   '<i class="fas fa-paperclip me-1"></i>Files</button>';
                        }
                    }
                ],
                initComplete: function() {
                    // Apply the search
                    this.api().columns().every(function() {
                        var that = this;
                        $('input', this.footer()).on('keyup change clear', function() {
                            if (that.search() !== this.value) {
                                that.search(this.value).draw();
                            }
                        });
                    });
                }
            });

            // Search functionality for footer
            $('#datatable tfoot th').each(function(i) {
                var title = $('#datatable thead th').eq($(this).index()).text();
                $(this).html('<input type="text" placeholder="Search ' + title + '" data-index="' + i + '" class="form-control form-control-sm" />');
            });

            // View files button click
            $(document).on('click', '.view-files', function() {
                var dcno = $(this).data('dcno');
                $('#modalDcNo').text(dcno);
                $('#fileModalBody').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p>Loading file attachments...</p></div>');
                
                // Show modal immediately
                $('#fileModal').modal('show');
                
                $.ajax({
                    url: 'api_get_dc_files.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        dcno: dcno,
                        FormID: '<?php echo $_SESSION['FormID']; ?>'
                    },
                    success: function(response) {
                        console.log('API Response:', response);
                        
                        if (response.error) {
                            throw new Error(response.error);
                        }
                        
                        if (!response.success) {
                            throw new Error('Failed to load files');
                        }
                        
                        var html = `
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="file-section">
                                        <h6><i class="fas fa-file-pdf text-danger me-2"></i>Purchase Orders</h6>
                                        ${response.polist || 'No files found'}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="file-section">
                                        <h6><i class="fas fa-truck text-warning me-2"></i>Courier Slips</h6>
                                        ${response.couriersliplist || 'No files found'}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="file-section">
                                        <h6><i class="fas fa-receipt text-success me-2"></i>Tax Invoices</h6>
                                        ${response.oldinvoicelist || 'No files found'}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="file-section">
                                        <h6><i class="fas fa-file-invoice-dollar text-info me-2"></i>Commercial Invoices</h6>
                                        ${response.oldcommercialinvoicelist || 'No files found'}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="file-section">
                                        <h6><i class="fas fa-clipboard-check text-primary me-2"></i>GRB Files</h6>
                                        ${response.oldgrblist || 'No files found'}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="file-section">
                                        <h6><i class="fas fa-cogs text-secondary me-2"></i>System Documents</h6>
                                        <strong>Invoices:</strong><br>${response.invoicelist || 'No invoices found'}<br><br>
                                        <strong>GRB:</strong><br>${response.grblist || 'No GRB found'}
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#fileModalBody').html(html);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        console.log('XHR Response:', xhr.responseText);
                        
                        let errorMessage = 'Unable to load files. ';
                        
                        if (xhr.responseText) {
                            try {
                                const errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse.error) {
                                    errorMessage = errorResponse.error;
                                }
                            } catch (e) {
                                if (xhr.responseText.includes('<!DOCTYPE') || xhr.responseText.includes('<html')) {
                                    errorMessage = 'Server returned an error page. Please check your session and try again.';
                                } else {
                                    errorMessage = 'Server error: ' + xhr.responseText.substring(0, 100);
                                }
                            }
                        }
                        
                        $('#fileModalBody').html(`
                            <div class="alert alert-danger">
                                <h5>Error Loading Files</h5>
                                <p>${errorMessage}</p>
                                <div class="mt-3">
                                    <button class="btn btn-warning btn-sm" onclick="$('.view-files[data-dcno=\"${dcno}\"]').click()">
                                        <i class="fas fa-redo me-1"></i>Try Again
                                    </button>
                                </div>
                            </div>
                        `);
                    }
                });
            });

            // File upload functionality
            $(document).on('click', '.uploadPOFile, .uploadCourierSlipFile, .uploadInvoiceFile, .uploadCommercialInvoiceFile, .uploadGRBFile', function() {
                var button = $(this);
                var orderno = button.data('orderno');
                var fileType = button.attr('class').match(/upload(\w+)File/)[1];
                var fd = new FormData();
                var fileInput = $('#attach' + fileType + 'File' + orderno)[0];
                
                if (!fileInput || fileInput.files.length === 0) {
                    showAlert('Please select a file first');
                    return;
                }
                
                fd.append(fileType, fileInput.files[0]);
                fd.append('orderno', orderno);
                fd.append('FormID', '<?php echo $_SESSION['FormID']; ?>');

                button.prop('disabled', true).text('Uploading...');
                
                $.ajax({
                    url: 'api/upload' + fileType + 'File.php',
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        button.prop('disabled', false).text('Upload');
                        if (res) {
                            showAlert('File uploaded successfully', 'success');
                            // Refresh the file modal content
                            $('.view-files[data-dcno="' + orderno + '"]').click();
                        } else {
                            showAlert('File upload failed');
                        }
                    },
                    error: function() {
                        button.prop('disabled', false).text('Upload');
                        showAlert('File upload failed');
                    }
                });
            });

            // Remove file functionality
            $(document).on('click', '.removeFile', function() {
                var button = $(this);
                var basepath = button.data('basepath');
                var orderno = button.data('orderno');
                
                if (!confirm('Are you sure you want to delete this file?')) {
                    return;
                }

                var fd = new FormData();
                fd.append('basepath', basepath);
                fd.append('orderno', orderno);
                fd.append('FormID', '<?php echo $_SESSION['FormID']; ?>');

                $.ajax({
                    url: 'api/removeFile.php',
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        if (res) {
                            showAlert('File deleted successfully', 'success');
                            // Refresh the file modal content
                            $('.view-files[data-dcno="' + orderno + '"]').click();
                        } else {
                            showAlert('File deletion failed');
                        }
                    },
                    error: function() {
                        showAlert('File deletion failed');
                    }
                });
            });

            // Search data
            $(".searchData").on("click", function() {
                let from = $(".fromDate").val();
                let to = $(".toDate").val();
                let FormID = '<?php echo $_SESSION['FormID']; ?>';

                if (!from || !to) {
                    showAlert('Please select both from and to dates');
                    return;
                }

                $(".searchData").prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Searching...');
                table.clear().draw();
                
                $.ajax({
                    url: "api_get_dc_list.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        from: from,
                        to: to,
                        FormID: FormID
                    },
                    success: function(response) {
                        $(".searchData").prop('disabled', false).html('<i class="fas fa-search me-2"></i>Search Data');
                        
                        if (response.error) {
                            throw new Error(response.error);
                        }
                        
                        table.rows.add(response).draw();
                        showAlert('Data loaded successfully', 'success');
                    },
                    error: function(xhr, status, error) {
                        $(".searchData").prop('disabled', false).html('<i class="fas fa-search me-2"></i>Search Data');
                        
                        let errorMessage = 'Error loading data: ' + error;
                        if (xhr.responseText) {
                            try {
                                const errorResponse = JSON.parse(xhr.responseText);
                                if (errorResponse.error) {
                                    errorMessage = errorResponse.error;
                                }
                            } catch (e) {
                                // Ignore parsing errors
                            }
                        }
                        
                        showAlert(errorMessage);
                    }
                });
            });
        });
    </script>
</body>
</html>