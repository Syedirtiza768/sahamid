<?php
$PathPrefix = "";
include("includes/session.inc");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Price List & Inventory - <?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

    <!-- CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #e74c3c;
            --light-bg: #f8f9fa;
            --success: #28a745;
        }

        /* Zero price ki style */
        .price-result .text-muted {
            color: #6c757d !important;
            font-size: 0.9em;
        }

        .price-result .text-danger {
            color: #dc3545 !important;
        }

        /* Info notification ki style */
        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        /* Eye button style */
        .btn-eye-calculate {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-eye-calculate:hover {
            background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%);
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(52, 152, 219, 0.3);
        }

        .btn-eye-calculate i {
            font-size: 0.85rem;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 70px;
        }

        .zone-header {
            background: linear-gradient(135deg, var(--primary) 0%, #34495e 100%);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .zone-main-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin: 20px 0;
            overflow: hidden;
        }

        .zone-card-header {
            background: linear-gradient(135deg, var(--secondary) 0%, #2980b9 100%);
            border: none;
            padding: 1.5rem;
        }

        .zone-table-container {
            background: white;
            border-radius: 8px;
        }

        .zone-footer {
            background: linear-gradient(135deg, var(--primary) 0%, #34495e 100%);
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--secondary);
            margin-bottom: 15px;
        }

        .table th {
            background: linear-gradient(135deg, var(--light-bg) 0%, #e9ecef 100%);
            color: var(--primary);
            font-weight: 600;
            border: none;
            padding: 15px 12px !important;
            text-align: center;
        }

        .table td {
            padding: 12px !important;
            vertical-align: middle;
            border-color: #f1f3f4;
            text-align: center;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        .btn-custom {
            border-radius: 6px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 8px;
        }

        .filter-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001;
            border-radius: 8px;
        }

        .badge-stock {
            padding: 6px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85em;
        }

        .search-box {
            border-radius: 20px;
            border: 2px solid #e9ecef;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }

        .search-box:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .dataTables_wrapper {
            padding: 0 15px 15px 15px;
        }

        .dataTables_wrapper .dt-buttons {
            text-align: center !important;
            margin: 15px 0;
        }

        .dataTables_wrapper .dt-buttons .btn {
            margin: 0 5px;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }

        .total-qty {
            background: linear-gradient(135deg, rgb(78, 225, 112) 0%, rgb(128, 204, 134) 100%);
            color: white;
            font-weight: bold;
        }

        .zero-qty {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            font-weight: bold;
        }

        .stock-id-link {
            color: rgb(26, 128, 231) !important;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .stock-id-link:hover {
            color: #1a252f !important;
            text-decoration: underline;
            transform: translateX(2px);
        }

        .table td,
        .table th {
            text-align: center;
        }

        .text-left {
            text-align: left !important;
        }

        .btn-professional {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            border: 2px solid #2c3e50;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-professional:hover {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            transform: scale(1.05);
            color: white;
        }

        .qty-filter-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #3498db;
            position: relative;
        }

        .qty-filter-btn {
            border-radius: 25px;
            font-weight: 600;
            padding: 10px 20px;
            margin: 0 5px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .qty-filter-btn.active {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .qty-filter-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .qty-filter-btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .btn-non-zero {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
        }

        .btn-non-zero:hover,
        .btn-non-zero.active {
            background: linear-gradient(135deg, #229954 0%, #27ae60 100%);
            border-color: #27ae60;
            color: white;
        }

        .btn-zero {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .btn-zero:hover,
        .btn-zero.active {
            background: linear-gradient(135deg, #cb4335 0%, #b03a2e 100%);
            border-color: #e74c3c;
            color: white;
        }

        .btn-both {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .btn-both:hover,
        .btn-both.active {
            background: linear-gradient(135deg, #2980b9 0%, #2471a3 100%);
            border-color: #3498db;
            color: white;
        }

        .filter-badge {
            font-size: 0.7em;
            margin-left: 5px;
            background: rgba(255, 255, 255, 0.3);
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .filter-loading-text {
            font-weight: 600;
            color: #2c3e50;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <!-- Zone 1: Header Navigation -->
    <header class="zone-header text-white py-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <!-- Left: Company Info -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <img src="includes/ERP.png" alt="S A Hamid and Company"
                            style="width: 55px; height: 55px; object-fit: contain; border-radius: 6px; margin-right: 15px; border: 1.5px solid rgba(255,255,255,0.25);">
                        <div>
                            <h5 class="mb-0 font-weight-bold text-white">S A Hamid and Company</h5>
                        </div>
                    </div>
                </div>

                <!-- Center: Page Title -->
                <div class="col-md-4 text-center">
                    <h3 class="mb-1 font-weight-bold">
                        Inventory Price List Dashboard
                    </h3>
                </div>

                <!-- Right: User Info and Buttons -->
                <div class="col-md-4">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="mr-3 text-right">
                            <div class="font-weight-bold">Ali Shabbar</div>
                            <small class="opacity-75">Welcome back</small>
                        </div>
                        <div class="btn-group">
                            <a href="<?php echo $RootPath; ?>/index.php" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-home mr-1"></i> Main Menu
                            </a>
                            <a class="btn btn-outline-light btn-sm" href="<?php echo $RootPath; ?>/Logout.php">
                                <i class="fas fa-sign-out-alt mr-1"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Zone 2: Main Content Area -->
    <div class="container-fluid mt-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="zone-main-content">

                    <!-- Statistics Zone -->
                    <div class="row p-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Products</h6>
                                        <h3 class="mb-0 text-primary" id="totalProducts">-</h3>
                                    </div>
                                    <i class="fas fa-boxes text-primary fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Active Brands</h6>
                                        <h3 class="mb-0 text-success" id="totalBrands">-</h3>
                                    </div>
                                    <i class="fas fa-tags text-success fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Categories</h6>
                                        <h3 class="mb-0 text-warning" id="totalCategories">-</h3>
                                    </div>
                                    <i class="fas fa-layer-group text-warning fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="stats-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">In Stock Items</h6>
                                        <h3 class="mb-0 text-info" id="inStockItems">-</h3>
                                    </div>
                                    <i class="fas fa-warehouse text-info fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity Filter Zone -->
                    <div class="row px-4">
                        <div class="col-12">
                            <div class="qty-filter-container text-center">
                                <h5 class="mb-3 text-dark">
                                    <i class="fas fa-filter mr-2"></i>Filter by Stock Quantity
                                </h5>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-non-zero qty-filter-btn active" data-filter="non-zero">
                                        <i class="fas fa-boxes mr-2"></i>In Stock
                                        <span class="badge filter-badge" id="nonZeroCount">-</span>
                                    </button>
                                    <button type="button" class="btn btn-zero qty-filter-btn" data-filter="zero">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>Out of Stock
                                        <span class="badge filter-badge" id="zeroCount">-</span>
                                    </button>
                                    <button type="button" class="btn btn-both qty-filter-btn" data-filter="both">
                                        <i class="fas fa-list-alt mr-2"></i>Show All
                                        <span class="badge filter-badge" id="bothCount">-</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table Zone -->
                    <div class="zone-table-container m-4 position-relative">
                        <div class="loading-overlay" id="loadingOverlay">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                                <h5 class="text-muted">Loading Inventory Data</h5>
                                <p class="text-muted">Please wait while we fetch the latest information</p>
                            </div>
                        </div>

                        <div class="filter-loading-overlay" id="filterLoadingOverlay" style="display: none;">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" style="width: 2rem; height: 2rem;"></div>
                                <h6 class="filter-loading-text" id="filterLoadingText">Applying Filter...</h6>
                            </div>
                        </div>

                        <table class="table table-bordered table-striped table-hover" id="datatable">
                            <thead class="thead-dark">
                                <tr>
                                    <th style="color:rgb(26, 128, 231)">Stock ID</th>
                                    <th>Brand</th>
                                    <th>Category</th>
                                    <th>Condition</th>
                                    <th style="color:rgb(53, 69, 85);">Qty</th>
                                    <th>Total Price</th>
                                    <th>Unit Price</th>
                                    <th>List Price</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Zone 3: Footer -->
    <footer class="zone-footer text-white text-center py-3 fixed-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-12 text-center">
                    <span class="text-light">Powered by </span>
                    <span class="font-weight-bold text-warning">Compresol Technologies</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.0/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            let allData = [];
            let currentFilter = 'non-zero';
            let isCalculatingPrices = false;

            // ✅ Number format function
            function numberFormat(number) {
                if (number === null || number === undefined || isNaN(number)) return '0.00';
                return parseFloat(number).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            }

            // ✅ Calculate total quantity function
            function calculateTotalQty(row) {
                return (parseInt(row.qtyHO) || 0) +
                    (parseInt(row.qtyMT) || 0) +
                    (parseInt(row.qtySR) || 0) +
                    (parseInt(row.qtyOS) || 0) +
                    (parseInt(row.qtyVSR) || 0) +
                    (parseInt(row.qtyWS) || 0);
            }

            // ✅ Notification function
            function showNotification(message, type) {
                $('.alert-dismissible').remove();

                const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' : 'alert-info';
                const icon = type === 'success' ? 'fa-check-circle' :
                    type === 'error' ? 'fa-exclamation-triangle' : 'fa-info-circle';

                const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show m-4" role="alert" 
                 style="border-radius: 8px; position: fixed; top: 100px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${icon} mr-2"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

                $('body').append(alertHtml);

                setTimeout(() => {
                    $('.alert').alert('close');
                }, 3000);
            }

            var datatable = $('#datatable').DataTable({
                dom: '<"row"<"col-sm-12 text-center"B>>' +
                    '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [{
                    extend: 'csv',
                    className: 'btn btn-professional',
                    text: '<i class="fas fa-download mr-2"></i> Download CSV Report',
                    action: function(e, dt, button, config) {
                        const filterParam = currentFilter ? `&filter=${currentFilter}` : '';
                        window.location.href = 'export.php?export=csv' + filterParam;
                    }
                }],
                lengthMenu: [
                    [10, 25, 50, 100],
                    ["10", "25", "50", "100"]
                ],
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search products, brands, categories...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No records available",
                    zeroRecords: "No matching records found"
                },
                columns: [{
                        data: "stockid",
                        className: "font-weight-bold",
                        render: function(data, type, row) {
                                    return `<a href="../SelectProduct.php?Select=${data}" class="stock-id-link" 
                                    title="${data}" target="_blank">
                                    ${data}
                                    </a>`;
                        }
                    },
                    {
                        data: "manufacturers_name",
                        className: "text-dark"
                    },
                    {
                        data: "categorydescription",
                        className: "text-muted"
                    },
                    {
                        data: "abbreviation",
                        className: "text-warning font-weight-bold"
                    },
                    {
                        data: null,
                        className: "font-weight-bold",
                        render: function(data, type, row) {
                            const totalQty = calculateTotalQty(row);
                            if (totalQty > 0) {
                                return '<span class="badge total-qty badge-stock">' + totalQty + '</span>';
                            } else {
                                return '<span class="badge zero-qty badge-stock">0</span>';
                            }
                        }
                    },
                    // ✅ TOTAL PRICE COLUMN - Shows calculated price directly
                    {
                        data: "total_bpitems_price",
                        className: "text-success font-weight-bold",
                        render: function(data, type, row) {
                            const totalQty = calculateTotalQty(row);

                            if (totalQty > 0) {
                                if (data > 0) {
                                    return `
                                <div class="price-result">
                                    <span class="text-success">PKR ${numberFormat(data)}</span>
                                    <br>
                                    <small class="text-muted">
                                        for ${row.total_quantity || totalQty} units
                                    </small>
                                </div>`;
                                } else {
                                    // If price is 0 but has quantity
                                    return `
                                <div class="price-result">
                                    <span class="text-muted">PKR 0.00</span>
                                    <br>
                                    <small class="text-muted">
                                        No parchino data
                                    </small>
                                </div>`;
                                }
                            } else {
                                // Zero quantity
                                return `
                            <div class="price-result">
                                <span class="text-muted">PKR 0.00</span>
                                <br>
                                <small class="text-muted">
                                    Out of stock
                                </small>
                            </div>`;
                            }
                        }
                    },
                    // ✅ UNIT PRICE COLUMN - Shows calculated price directly
                    {
                        data: "weighted_unit_price",
                        className: "text-info font-weight-bold",
                        render: function(data, type, row) {
                            const totalQty = calculateTotalQty(row);

                            if (totalQty > 0) {
                                if (data > 0) {
                                    return `
                                <div class="price-result">
                                    <span class="text-info">PKR ${numberFormat(data)}</span>
                                    <br>
                                    <small class="text-muted">
                                        weighted average
                                    </small>
                                </div>`;
                                } else {
                                    return `
                                <div class="price-result">
                                    <span class="text-muted">PKR 0.00</span>
                                    <br>
                                    <small class="text-muted">
                                        No parchino data
                                    </small>
                                </div>`;
                                }
                            } else {
                                return `
                            <div class="price-result">
                                <span class="text-muted">PKR 0.00</span>
                                <br>
                                <small class="text-muted">
                                    Out of stock
                                </small>
                            </div>`;
                            }
                        }
                    },
                    {
                        data: "materialcost",
                        className: "text-danger font-weight-bold",
                        render: function(data) {
                            return data > 0 ? 'PKR  ' + numberFormat(data) : '<span class="text-muted">PKR 0.00</span>';
                        }
                    }
                ],
                initComplete: function() {
                    loadData();
                }
            });

            // Quantity filter button handlers
            $('.qty-filter-btn').on('click', function() {
                const filter = $(this).data('filter');
                const filterText = getFilterText(filter);

                showFilterLoading(filterText);
                $('.qty-filter-btn').addClass('loading');

                setTimeout(() => {
                    $('.qty-filter-btn').removeClass('active');
                    $(this).addClass('active');
                    applyQuantityFilter(filter);
                    $('.qty-filter-btn').removeClass('loading');
                }, 300);
            });

            function getFilterText(filter) {
                switch (filter) {
                    case 'non-zero':
                        return 'Filtering In Stock Items...';
                    case 'zero':
                        return 'Filtering Out of Stock Items...';
                    case 'both':
                        return 'Showing All Items...';
                    default:
                        return 'Applying Filter...';
                }
            }

            function showFilterLoading(message) {
                $('#filterLoadingText').text(message);
                $('#filterLoadingOverlay').fadeIn(300);
            }

            function hideFilterLoading() {
                $('#filterLoadingOverlay').fadeOut(300);
            }

            // Update loadData() function:
            function loadData() {
                isCalculatingPrices = true;

                // Show enhanced loading screen
                $('#loadingOverlay').html(`
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <h5 class="text-muted">Calculating Prices for All Products</h5>
            <div class="progress mt-3" style="height: 20px;">
                <div id="loadingProgress" class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%">0%</div>
            </div>
            <p class="text-muted mt-2" id="loadingStatus">
                Processing 45,000+ products with parchi data...
            </p>
            <small class="text-muted">This may take 30-60 seconds</small>
        </div>
    `).fadeIn(300);

                $.ajax({
                    type: 'GET',
                    url: 'index.php',
                    dataType: "json",
                    success: function(response) {
                        isCalculatingPrices = false;

                        if (response.status === 'success' && response.data) {
                            allData = response.data;
                            updateStatistics(allData);
                            applyQuantityFilter(currentFilter);

                            const itemsWithPrice = allData.filter(item => item.total_bpitems_price > 0).length;
                            showNotification(`✅ Loaded ${response.count} products with prices (${itemsWithPrice} have parchino data)`, 'success');

                            $('#loadingOverlay').fadeOut(300);
                        } else {
                            showNotification('Error: ' + (response.error || 'Unable to load data'), 'error');
                            $('#loadingOverlay').fadeOut(300);
                        }
                    },
                    error: function(xhr, status, error) {
                        isCalculatingPrices = false;
                        $('#loadingOverlay').fadeOut(300);

                        let errorMsg = 'Failed to load data. ';
                        if (status === 'timeout') {
                            errorMsg = 'Request took too long. Try refreshing or use the nightly pre-calculated option.';
                        } else if (status === 'parsererror') {
                            errorMsg = 'Memory error. Try the nightly pre-calculated option for better performance.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server memory limit exceeded. Please use the nightly calculation option.';
                        } else {
                            errorMsg += 'Please check your connection.';
                        }

                        showNotification(errorMsg, 'error');

                        // Suggest alternative
                        setTimeout(() => {
                            if (confirm('Large dataset detected. Would you like to use pre-calculated nightly prices instead?')) {
                                window.location.href = '?use_cached=1';
                            }
                        }, 2000);
                    }
                });
            }

            function applyQuantityFilter(filter) {
                currentFilter = filter;
                let filteredData = [];

                switch (filter) {
                    case 'non-zero':
                        filteredData = allData.filter(item => calculateTotalQty(item) > 0);
                        break;
                    case 'zero':
                        filteredData = allData.filter(item => calculateTotalQty(item) === 0);
                        break;
                    case 'both':
                        filteredData = allData;
                        break;
                }

                // Show filtered results
                datatable.clear();
                datatable.rows.add(filteredData).draw();
                updateFilterCounts();
                hideFilterLoading();

                // Show filter summary
                const priceItems = filteredData.filter(item => item.total_bpitems_price > 0).length;
                showNotification(`Filter applied: ${getFilterSuccessText(filter)} (${filteredData.length} items, ${priceItems} with prices)`, 'success');
            }

            function getFilterSuccessText(filter) {
                switch (filter) {
                    case 'non-zero':
                        return 'In Stock Items';
                    case 'zero':
                        return 'Out of Stock Items';
                    case 'both':
                        return 'All Items';
                    default:
                        return 'Filtered Items';
                }
            }

            function updateFilterCounts() {
                const nonZeroCount = allData.filter(item => calculateTotalQty(item) > 0).length;
                const zeroCount = allData.filter(item => calculateTotalQty(item) === 0).length;
                const bothCount = allData.length;

                $('#nonZeroCount').text(nonZeroCount);
                $('#zeroCount').text(zeroCount);
                $('#bothCount').text(bothCount);
            }

            function updateStatistics(data) {
                $('#totalProducts').text(data.length);

                const brands = [...new Set(data.map(item => item.manufacturers_name))];
                const categories = [...new Set(data.map(item => item.categorydescription))];
                const inStockItems = data.filter(item => calculateTotalQty(item) > 0).length;

                // Calculate total value from pre-calculated prices
                const totalBPItemsPrice = data.reduce((sum, item) => {
                    return sum + (item.total_bpitems_price || 0);
                }, 0);

                const itemsWithPrice = data.filter(item => item.total_bpitems_price > 0).length;
                const avgUnitPrice = itemsWithPrice > 0 ?
                    data.reduce((sum, item) => sum + (item.weighted_unit_price || 0), 0) / itemsWithPrice :
                    0;

                $('#totalBrands').text(brands.length);
                $('#totalCategories').text(categories.length);
                $('#inStockItems').text(inStockItems);

                // Update stats display
                if ($('#totalBPItemsPrice').length) {
                    $('#totalBPItemsPrice').text('PKR ' + numberFormat(totalBPItemsPrice));
                }
                if ($('#avgUnitPrice').length) {
                    $('#avgUnitPrice').text('PKR ' + numberFormat(avgUnitPrice));
                }
                if ($('#itemsWithPrice').length) {
                    $('#itemsWithPrice').text(itemsWithPrice);
                }

                updateFilterCounts();
            }

            // Add refresh button functionality
            $(document).on('click', '#refreshData', function() {
                loadData();
            });
        });
    </script>
</body>

</html>