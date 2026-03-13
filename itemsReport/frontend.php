<?php
$PathPrefix = "";
include("includes/session.inc");

if (!isset($_SESSION['UsersRealName'])) {
    header("Location: " . $RootPath . "/index.php");
    exit;
}

// Rest of your page code would go here...
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
            --date-color: #9b59b6;
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
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .stats-card small {
            font-size: 0.85rem;
            display: block;
            margin-top: 5px;
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
            border-radius: 6px !important;
            font-weight: 600 !important;
            padding: 10px 20px !important;
        }

        .btn-outline-secondary {
            background: white;
            border: 2px solid #6c757d;
            color: #6c757d;
        }

        .btn-outline-secondary:hover {
            background: #6c757d;
            color: white;
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

        .date-filter-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #9b59b6;
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

        .btn-date-today {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
        }

        .btn-date-week {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .btn-date-month {
            background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%);
            color: white;
        }

        .btn-date-quarter {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            color: white;
        }

        .btn-date-all {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
        }

        .btn-date-today:hover,
        .btn-date-today.active,
        .btn-date-week:hover,
        .btn-date-week.active,
        .btn-date-month:hover,
        .btn-date-month.active,
        .btn-date-quarter:hover,
        .btn-date-quarter.active,
        .btn-date-all:hover,
        .btn-date-all.active {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            filter: brightness(1.1);
        }

        .filter-badge {
            font-size: 0.7em;
            margin-left: 5px;
            background: rgba(255, 255, 255, 0.3);
        }

        /* Style for unit price input and landing factor input */
        .unit-price-input,
        .landing-factor-input {
            width: 100px;
            padding: 5px 8px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .unit-price-input:focus,
        .landing-factor-input:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            outline: none;
        }

        .unit-price-input.edited,
        .landing-factor-input.edited {
            border-color: var(--success);
            background-color: #f0fff4;
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

        /* Style for the dynamic calculation */
        .dynamic-value {
            transition: all 0.3s ease;
        }

        .dynamic-value.edited {
            background-color: #fff3cd;
            padding: 3px 6px;
            border-radius: 4px;
        }

        /* Import styles */
        .custom-file-input:lang(en)~.custom-file-label::after {
            content: "Browse";
        }

        .custom-file-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #importPreview table {
            font-size: 0.9rem;
        }

        #importPreview .text-success {
            color: #28a745 !important;
            font-weight: 600;
        }

        #importPreview .text-danger {
            color: #dc3545 !important;
            font-weight: 600;
        }

        .modal-header.bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        }

        .modal-header.bg-success {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
        }

        .modal-header.bg-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
        }

        #importProgressBar {
            transition: width 0.3s ease;
        }

        #importStatus {
            font-size: 0.9rem;
            color: #666;
        }

        /* Keyboard shortcut hint */
        .keyboard-hint {
            position: fixed;
            bottom: 80px;
            right: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            z-index: 999;
            opacity: 0.5;
            transition: opacity 0.3s;
        }

        .keyboard-hint:hover {
            opacity: 1;
        }

        .keyboard-hint kbd {
            background: white;
            color: black;
            padding: 2px 6px;
            border-radius: 4px;
            margin: 0 2px;
        }

        /* Export/Import workflow hint */
        .workflow-hint {
            background: #e8f4fd;
            border-radius: 20px;
            padding: 5px 15px;
            display: inline-block;
            margin-left: 15px;
        }

        .workflow-hint i {
            color: #3498db;
        }

        /* Timer and progress styles */
        .timer-display {
            font-family: 'Courier New', monospace;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .phase-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
        }

        .progress {
            height: 25px;
            margin: 10px 0;
        }

        .progress-bar {
            line-height: 25px;
            font-size: 0.9rem;
        }

        .stats-row {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin: 10px 0;
        }

        .cancel-import {
            cursor: pointer;
        }

        .cancel-import:hover {
            color: #dc3545;
            text-decoration: underline;
        }

        /* Upload progress indicator */
        .upload-progress {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .file-info {
            background: #e9ecef;
            border-radius: 6px;
            padding: 8px;
            font-size: 0.9rem;
        }

        /* Date badge styles */
        .badge-date-today {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .badge-date-week {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .badge-date-month {
            background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%);
            color: white;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .badge-date-older {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 20px;
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
                            <div class="font-weight-bold"><?php echo $_SESSION['UsersRealName']; ?></div>
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

                    <!-- Status Statistics Zone -->
                    <div class="row px-4 mb-3">
                        <div class="col-md-3">
                            <div class="stats-card" style="border-left-color: #28a745;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Fast Moving</h6>
                                        <h3 class="mb-0 text-success" id="runningCount">-</h3>
                                        <small class="text-success" id="runningSum"> 0.00</small>
                                    </div>
                                    <i class="fas fa-check-circle text-success fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="border-left-color: #ffc107;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Slow Moving</h6>
                                        <h3 class="mb-0 text-warning" id="slowCount">-</h3>
                                        <small class="text-warning" id="slowSum"> 0.00</small>
                                    </div>
                                    <i class="fas fa-exclamation-triangle text-warning fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="border-left-color: #dc3545;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Dead Stock</h6>
                                        <h3 class="mb-0 text-danger" id="deadCount">-</h3>
                                        <small class="text-danger" id="deadSum"> 0.00</small>
                                    </div>
                                    <i class="fas fa-skull-crossbones text-danger fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card" style="border-left-color: #343a40;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Extremely Dead / Never Used</h6>
                                        <h3 class="mb-0 text-dark" id="extremelyDeadCount">-</h3>
                                        <small class="text-dark" id="extremelyDeadSum"> 0.00</small>
                                    </div>
                                    <i class="fas fa-skull text-dark fa-2x opacity-50"></i>
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

                    <!-- Import/Export Workflow Zone - SIMPLIFIED to only Simple Template -->
                    <!-- Import/Export Workflow Zone - UPDATED to accept downloaded CSV -->
                    <div class="row px-4 mb-3">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-upload mr-2"></i>Update Prices from Downloaded CSV</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="text-center p-3">
                                                <h6><i class="fas fa-file-csv text-info mr-2"></i>Upload CSV File</h6>
                                                <p class="text-muted small">Upload the CSV file you downloaded using <strong>"Download CSV Report"</strong> or <strong>"Download All Data"</strong>. Only <strong>Adjust Unit Price & Landing Factor</strong> column will be processed.</p>
                                                <button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#importModal">
                                                    <i class="fas fa-upload mr-2"></i>Upload CSV File
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-12 text-center">
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-info-circle mr-2"></i>
                                                <strong>How it works:</strong> Upload the exact CSV file downloaded from this page. The system will find the <strong>Adjust Unit Price</strong> column and only update rows where the price has changed.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Import Modal - UPDATED to handle full downloaded CSV format -->
                    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title" id="importModalLabel">
                                        <i class="fas fa-upload mr-2"></i>Upload Downloaded CSV File
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle mr-2"></i>
                                        <strong>Expected Format:</strong> Upload the CSV file exactly as downloaded from this page (using <strong>"Download CSV Report"</strong> or <strong>"Download All Data"</strong>).
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card bg-light">
                                                <div class="card-header bg-secondary text-white">
                                                    <h6 class="mb-0"><i class="fas fa-file-csv mr-2"></i>Expected CSV Format (14 columns)</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered bg-white">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th>Stock ID</th>
                                                                    <th>Brand</th>
                                                                    <th>Category</th>
                                                                    <th>Model #</th>
                                                                    <th>Part #</th>
                                                                    <th>Qty</th>
                                                                    <th>Total Price</th>
                                                                    <th>Unit Price</th>
                                                                    <th class="text-success font-weight-bold">Adjust Unit Price</th>
                                                                    <th>Landing Factor</th>
                                                                    <th>Adjusted Price After Multiplication</th>
                                                                    <th>Qty × Adjusted Price</th>
                                                                    <th>List Price</th>
                                                                    <th>Stock Status</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>ABC123</td>
                                                                    <td>Brand A</td>
                                                                    <td>Category X</td>
                                                                    <td>MODEL1</td>
                                                                    <td>PART1</td>
                                                                    <td>10</td>
                                                                    <td>5,000.00</td>
                                                                    <td>500.00</td>
                                                                    <td class="text-success font-weight-bold">550.00</td>
                                                                    <td>1.00</td>
                                                                    <td>550.00</td>
                                                                    <td>5,500.00</td>
                                                                    <td>600.00</td>
                                                                    <td>MOVING</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <p class="text-muted small mt-2">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        The system will only read <strong>Stock ID</strong> and <strong>Adjust Unit Price</strong> columns. All other columns are ignored.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="csvFile" class="font-weight-bold">Select your downloaded CSV file:</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="csvFile" accept=".csv, text/csv">
                                            <label class="custom-file-label" for="csvFile">Choose file...</label>
                                        </div>
                                    </div>

                                    <!-- File Info Display -->
                                    <div id="fileInfo" class="file-info mt-2" style="display: none;">
                                        <i class="fas fa-file-csv mr-2"></i>
                                        <span id="fileName"></span> -
                                        <span id="fileSize"></span> -
                                        <span id="fileRows"></span> rows detected
                                    </div>

                                    <!-- Upload Progress Display -->
                                    <div id="uploadProgress" style="display: none;" class="upload-progress">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span><i class="fas fa-clock mr-2"></i>Processing Import</span>
                                            <span class="timer-display" id="timer">00:00</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                role="progressbar"
                                                style="width: 0%"
                                                id="uploadProgressBar">0%</div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small id="uploadStatus">Initializing...</small>
                                            <small class="cancel-import text-warning" id="cancelImport">
                                                <i class="fas fa-times-circle mr-1"></i>Cancel
                                            </small>
                                        </div>
                                        <div class="stats-row mt-2" id="uploadStats" style="display: none;">
                                            <div class="row text-center">
                                                <div class="col-4">
                                                    <div id="processedRows">0</div>
                                                    <small>Processed</small>
                                                </div>
                                                <div class="col-4">
                                                    <div id="successRows">0</div>
                                                    <small>Updated</small>
                                                </div>
                                                <div class="col-4">
                                                    <div id="errorRows">0</div>
                                                    <small>Errors</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Preview Section -->
                                    <div id="importPreview" style="display: none;">
                                        <hr>
                                        <h6 class="text-primary">Preview of Changes:</h6>
                                        <div class="table-responsive" style="max-height: 300px;">
                                            <table class="table table-sm table-bordered" id="previewTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Stock ID</th>
                                                        <th>Current Adjust Price</th>
                                                        <th>New Adjust Price</th>
                                                        <th>Change</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times mr-2"></i>Cancel
                                    </button>
                                    <button type="button" class="btn btn-info" id="processImport" disabled>
                                        <i class="fas fa-play mr-2"></i>Update Prices from CSV
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Import Results Modal -->
                    <div class="modal fade" id="importResultsModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">
                                        <i class="fas fa-check-circle mr-2"></i>Import Results
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-3">
                                        <div class="display-4 text-success" id="successCount">0</div>
                                        <p class="text-muted">Prices Successfully Updated</p>
                                    </div>
                                    <div class="text-center mb-3">
                                        <div class="display-4 text-danger" id="errorCount">0</div>
                                        <p class="text-muted">Failed to Update</p>
                                    </div>
                                    <div id="errorDetails" style="display: none;">
                                        <hr>
                                        <h6 class="text-danger">Error Details:</h6>
                                        <div class="table-responsive" style="max-height: 200px;">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Stock ID</th>
                                                        <th>Error</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="errorList"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary" data-dismiss="modal">
                                        <i class="fas fa-check mr-2"></i>OK
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
                                <p class="text-muted">Please wait while we fetch the latest information. It may take one to two minutes only.</p>
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
                                    <th>Model #</th>
                                    <th>Part #</th>
                                    <th style="color:rgb(53, 69, 85);">Qty</th>
                                    <th>Total Price</th>
                                    <th>Unit Price</th>
                                    <th style="color:rgb(26, 128, 231)">Adjust Unit Price</th>
                                    <th style="color: #65b9c6">Landing Factor</th>
                                    <th>Adjusted Price After Multiplication</th>
                                    <th>Qty × Adjusted Price</th>
                                    <th>List Price</th>
                                    <th style="color: #9b59b6">Stock Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Keyboard shortcut hint -->
    <div class="keyboard-hint" title="Press Ctrl+I to import">
        <i class="fas fa-keyboard mr-1"></i>
        <kbd>Ctrl</kbd> + <kbd>I</kbd> to import
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
        let currentStatusFilter = 'all';
        let isCalculatingPrices = false;

        // Store custom unit prices - THIS WILL PERSIST ALL CUSTOM PRICES
        let customUnitPrices = {};

        // Store landing factors - default is 1
        let landingFactors = {};

        // Track original values from database
        let originalPrices = {};
        let originalFactors = {};

        // Auto-save timeout references
        let autoSaveTimeout = null;

        // Import variables
        let importStartTime = null;
        let importTimerInterval = null;
        let importCancelled = false;
        let importResults = {
            success: [],
            errors: []
        };

        // ✅ Number format function
        function numberFormat(number) {
            if (number === null || number === undefined || isNaN(number)) return '0.00';
            return parseFloat(number).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // ✅ Format time function
        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
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

        // ✅ Get stock status based on transaction date
        function getStockStatus(dateString) {
            if (!dateString) return {
                status: 'EXTREMELY DEAD',
                class: 'badge-dark',
                icon: 'fa-skull',
                days: null
            };

            const today = new Date();
            const transactionDate = new Date(dateString);
            const diffTime = Math.abs(today - transactionDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays <= 180) {
                return {
                    status: 'FAST MOVING',
                    class: 'badge-success',
                    icon: 'fa-check-circle',
                    days: diffDays
                };
            } else if (diffDays > 180 && diffDays <= 360) {
                return {
                    status: 'SLOW MOVING',
                    class: 'badge-warning',
                    icon: 'fa-exclamation-triangle',
                    days: diffDays
                };
            } else if (diffDays > 360 && diffDays <= 1000) {
                return {
                    status: 'DEAD STOCK',
                    class: 'badge-danger',
                    icon: 'fa-skull-crossbones',
                    days: diffDays
                };
            } else {
                return {
                    status: 'EXTREMELY DEAD',
                    class: 'badge-dark',
                    icon: 'fa-skull',
                    days: diffDays
                };
            }
        }

        // ✅ Format date for tooltip
        function formatDateTooltip(dateString) {
            if (!dateString) return 'No transaction history';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-PK', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
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

        // Function to update row calculations directly
        function updateRowCalculations(stockId) {
            const rows = datatable.rows().data();
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].stockid === stockId) {
                    const rowNode = datatable.row(i).node();
                    const rowData = rows[i];
                    const totalQty = calculateTotalQty(rowData);
                    
                    const unitPrice = parseFloat(rowData.weighted_unit_price) || 0;
                    const adjustPrice = customUnitPrices[stockId] !== undefined ? customUnitPrices[stockId] : 0;
                    const effectivePrice = unitPrice > 0 ? unitPrice : (parseFloat(adjustPrice) || 0);
                    const factor = landingFactors[stockId] !== undefined ? landingFactors[stockId] : 1;
                    const adjustedPrice = effectivePrice * factor;
                    const qtyTimesAdjustedPrice = totalQty * adjustedPrice;
                    
                    // Update Adjusted Price After Multiplication column (index 10)
                    const adjustedPriceCell = $(rowNode).find('td:eq(10)');
                    adjustedPriceCell.html(`<span class="text-primary">${numberFormat(adjustedPrice)}</span>`);
                    
                    // Update Qty × Adjusted Price column (index 11)
                    const qtyTimesCell = $(rowNode).find('td:eq(11)');
                    qtyTimesCell.html(`<span class="text-danger">${numberFormat(qtyTimesAdjustedPrice)}</span>`);
                    
                    break;
                }
            }
        }

        // AUTO-SAVE FUNCTION - Modified to handle batch saves
        function autoSaveToDatabase(stockId, field, value, isBatch = false) {
            if (isBatch) {
                $.ajax({
                    type: 'POST',
                    url: 'save_parchino.php',
                    data: {
                        action: 'save_single',
                        stockid: stockId,
                        field: field,
                        value: value
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            if (field === 'adjust_unit_price') {
                                originalPrices[stockId] = value;
                            } else if (field === 'landing_factor') {
                                originalFactors[stockId] = value;
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(`Auto-save error for ${stockId}:`, error);
                    }
                });
            } else {
                if (autoSaveTimeout) {
                    clearTimeout(autoSaveTimeout);
                }

                autoSaveTimeout = setTimeout(function() {
                    $.ajax({
                        type: 'POST',
                        url: 'save_parchino.php',
                        data: {
                            action: 'save_single',
                            stockid: stockId,
                            field: field,
                            value: value
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                if (field === 'adjust_unit_price') {
                                    originalPrices[stockId] = value;
                                    showNotification(`✓ Saved price for ${stockId}`, 'success');
                                } else if (field === 'landing_factor') {
                                    originalFactors[stockId] = value;
                                    showNotification(`✓ Saved factor for ${stockId}`, 'success');
                                }
                            } else {
                                showNotification(`Failed to auto-save: ${response.message}`, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            showNotification(`Auto-save error: ${error}`, 'error');
                        }
                    });
                }, 1000);
            }
        }

        // Load saved customizations
        function loadSavedCustomizations() {
            $.ajax({
                type: 'GET',
                url: 'save_parchino.php',
                data: {
                    action: 'get'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success' && response.data) {
                        Object.keys(response.data).forEach(stockId => {
                            const data = response.data[stockId];
                            if (data.adjust_unit_price !== 0) {
                                customUnitPrices[stockId] = data.adjust_unit_price;
                                originalPrices[stockId] = data.adjust_unit_price;
                            }
                            if (data.landing_factor !== 1) {
                                landingFactors[stockId] = data.landing_factor;
                                originalFactors[stockId] = data.landing_factor;
                            }
                        });

                        applyFilters();
                        showNotification(`Loaded ${Object.keys(response.data).length} saved customizations`, 'success');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load saved customizations:', error);
                }
            });
        }

        // Update status statistics with counts and sums
function updateStatusStatistics(data) {
    let running = 0,
        slow = 0,
        dead = 0,
        extremelyDead = 0;

    let runningSum = 0,
        slowSum = 0,
        deadSum = 0,
        extremelyDeadSum = 0;

    data.forEach(item => {
        const totalQty = calculateTotalQty(item);
        
        // Only process items with quantity > 0 for the sums
        if (totalQty > 0) {
            const status = getStockStatus(item.latest_trandate);
            
            // Calculate the value for this item
            const unitPrice = parseFloat(item.weighted_unit_price) || 0;
            const adjustPrice = customUnitPrices[item.stockid] !== undefined ? customUnitPrices[item.stockid] : 0;
            
            // Use unit price if available, otherwise use adjust price
            let effectivePrice = unitPrice;
            if (unitPrice === 0 && adjustPrice > 0) {
                effectivePrice = adjustPrice;
            } else if (unitPrice === 0) {
                effectivePrice = 0;
            }
            
            const factor = landingFactors[item.stockid] !== undefined ? landingFactors[item.stockid] : 1;
            const adjustedPrice = effectivePrice * factor;
            const itemValue = totalQty * adjustedPrice;

            switch (status.status) {
                case 'FAST MOVING':
                    running++;
                    runningSum += itemValue;
                    break;
                case 'SLOW MOVING':
                    slow++;
                    slowSum += itemValue;
                    break;
                case 'DEAD STOCK':
                    dead++;
                    deadSum += itemValue;
                    break;
                case 'EXTREMELY DEAD':
                    extremelyDead++;
                    extremelyDeadSum += itemValue;
                    break;
            }
        }
    });

    // Update counts
    $('#runningCount').text(running);
    $('#slowCount').text(slow);
    $('#deadCount').text(dead);
    $('#extremelyDeadCount').text(extremelyDead);
    
    // Update sums (the small text below the counts)
    $('#runningSum').text(numberFormat(runningSum));
    $('#slowSum').text(numberFormat(slowSum));
    $('#deadSum').text(numberFormat(deadSum));
    $('#extremelyDeadSum').text(numberFormat(extremelyDeadSum));
}

        // Export to CSV function
        function exportToCSV(dt, filteredOnly) {
            try {
                let data;
                if (filteredOnly) {
                    data = dt.rows({ search: 'applied' }).data();
                } else {
                    data = dt.rows().data();
                }
                
                const headers = [
                    'Stock ID', 'Brand', 'Category', 'Model #', 'Part #', 'Qty',
                    'Total Price', 'Unit Price', 'Adjust Unit Price', 'Landing Factor',
                    'Adjusted Price After Multiplication', 'Qty × Adjusted Price',
                    'List Price', 'Stock Status'
                ];
                
                let csvContent = headers.join(',') + '\n';
                
                for (let i = 0; i < data.length; i++) {
                    const row = data[i];
                    const rowData = [];
                    
                    rowData.push(`"${row.stockid || ''}"`);
                    rowData.push(`"${row.manufacturers_name || ''}"`);
                    rowData.push(`"${row.categorydescription || ''}"`);
                    rowData.push(`"${row.mnfCode || ''}"`);
                    rowData.push(`"${row.mnfpno || ''}"`);
                    
                    const totalQty = calculateTotalQty(row);
                    rowData.push(totalQty);
                    
                    rowData.push(parseFloat(row.total_bpitems_price || 0).toFixed(2));
                    rowData.push(parseFloat(row.weighted_unit_price || 0).toFixed(2));
                    
                    const adjustPrice = customUnitPrices[row.stockid] !== undefined ? customUnitPrices[row.stockid] : '';
                    rowData.push(adjustPrice !== '' ? parseFloat(adjustPrice).toFixed(2) : '');
                    
                    const factor = landingFactors[row.stockid] !== undefined ? landingFactors[row.stockid] : 1;
                    rowData.push(parseFloat(factor).toFixed(2));
                    
                    const unitPrice = parseFloat(row.weighted_unit_price) || 0;
                    const effectivePrice = unitPrice > 0 ? unitPrice : (parseFloat(adjustPrice) || 0);
                    const adjustedPrice = effectivePrice * factor;
                    rowData.push(adjustedPrice.toFixed(2));
                    
                    const qtyTimesAdjustedPrice = totalQty * adjustedPrice;
                    rowData.push(qtyTimesAdjustedPrice.toFixed(2));
                    
                    rowData.push(parseFloat(row.materialcost || 0).toFixed(2));
                    
                    const status = getStockStatus(row.latest_trandate);
                    rowData.push(`"${status.status}"`);
                    
                    csvContent += rowData.join(',') + '\n';
                }
                
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                
                const filename = filteredOnly ? 
                    'inventory_filtered_' + new Date().toISOString().slice(0,10) + '.csv' : 
                    'inventory_all_' + new Date().toISOString().slice(0,10) + '.csv';
                
                link.setAttribute('href', url);
                link.setAttribute('download', filename);
                link.style.display = 'none';
                
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                URL.revokeObjectURL(url);
                
                showNotification(`✅ CSV downloaded successfully with ${data.length} records`, 'success');
                
            } catch (error) {
                console.error('Export error:', error);
                showNotification('Error generating CSV: ' + error.message, 'error');
            }
        }

        var datatable = $('#datatable').DataTable({
            dom: '<"row"<"col-sm-12 text-center"B>>' +
                '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                '<"row"<"col-sm-12"tr>>' +
                '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    text: '<i class="fas fa-download mr-2"></i> Download CSV Report',
                    className: 'btn btn-professional',
                    action: function(e, dt, node, config) {
                        showNotification('Preparing CSV download...', 'info');
                        setTimeout(function() {
                            exportToCSV(dt, true);
                        }, 100);
                    }
                },
                {
                    text: '<i class="fas fa-download mr-2"></i> Download All Data',
                    className: 'btn btn-outline-secondary',
                    action: function(e, dt, node, config) {
                        showNotification('Preparing CSV download...', 'info');
                        setTimeout(function() {
                            exportToCSV(dt, false);
                        }, 100);
                    }
                }
            ],
            lengthMenu: [
                [10, 25, 50, 100, -1],
                ["10", "25", "50", "100", "All"]
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
                        if (type === 'display') {
                            return `<a href="../SelectProduct.php?Select=${data}" class="stock-id-link" 
                                    title="${data}" target="_blank">
                                    ${data}
                                </a>`;
                        }
                        return data;
                    }
                },
                {
                    data: "manufacturers_name",
                    className: "text-dark",
                    render: function(data, type, row) {
                        return data || '';
                    }
                },
                {
                    data: "categorydescription",
                    className: "text-muted",
                    render: function(data, type, row) {
                        return data || '';
                    }
                },
                {
                    data: "mnfCode",
                    className: "text-info font-weight-bold",
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return data ? data : '<span class="text-muted">-</span>';
                        }
                        return data || '';
                    }
                },
                {
                    data: "mnfpno",
                    className: "text-primary",
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return data ? data : '<span class="text-muted">-</span>';
                        }
                        return data || '';
                    }
                },
                {
                    data: null,
                    className: "font-weight-bold",
                    render: function(data, type, row) {
                        const totalQty = calculateTotalQty(row);
                        if (type === 'display') {
                            if (totalQty > 0) {
                                return '<span class="badge total-qty badge-stock">' + totalQty + '</span>';
                            } else {
                                return '<span class="badge zero-qty badge-stock">0</span>';
                            }
                        }
                        return totalQty || 0;
                    }
                },
                {
                    data: "total_bpitems_price",
                    className: "text-success font-weight-bold",
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return `<span class="text-success">${numberFormat(data || 0)}</span>`;
                        }
                        return data || 0;
                    }
                },
                {
                    data: "weighted_unit_price",
                    className: "text-info font-weight-bold",
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return `<span class="text-info">${numberFormat(data || 0)}</span>`;
                        }
                        return data || 0;
                    }
                },
                {
                    data: null,
                    className: "text-center",
                    render: function(data, type, row) {
                        const stockId = row.stockid;
                        const originalPrice = parseFloat(row.weighted_unit_price) || 0;
                        const customPrice = customUnitPrices[stockId] !== undefined ? customUnitPrices[stockId] : '';

                        if (type === 'display') {
                            return `<input type="number" 
                                        class="unit-price-input ${customPrice !== '' && customPrice !== originalPrice ? 'edited' : ''}" 
                                        data-stockid="${stockId}"
                                        data-original="${originalPrice}"
                                        value="${customPrice}"
                                        min="0" 
                                        step="0.01"
                                        placeholder="Enter price"
                                        style="width:100px;">`;
                        }
                        return customPrice !== '' ? customPrice : '';
                    }
                },
                {
                    data: null,
                    className: "text-center",
                    render: function(data, type, row) {
                        const stockId = row.stockid;
                        const factor = landingFactors[stockId] !== undefined ? landingFactors[stockId] : 1;

                        if (type === 'display') {
                            return `<input type="number" 
                                        class="landing-factor-input ${factor !== 1 ? 'edited' : ''}" 
                                        data-stockid="${stockId}"
                                        value="${factor}"
                                        min="0.01" 
                                        step="0.01"
                                        style="width:100px;">`;
                        }
                        return factor;
                    }
                },
                {
                    data: null,
                    className: "text-primary font-weight-bold",
                    render: function(data, type, row) {
                        const totalQty = calculateTotalQty(row);
                        const stockId = row.stockid;

                        const unitPrice = parseFloat(row.weighted_unit_price) || 0;
                        const adjustPrice = customUnitPrices[stockId] !== undefined ? customUnitPrices[stockId] : 0;
                        const effectivePrice = unitPrice > 0 ? unitPrice : (parseFloat(adjustPrice) || 0);
                        const factor = landingFactors[stockId] !== undefined ? landingFactors[stockId] : 1;
                        const adjustedPrice = effectivePrice * factor;

                        if (type === 'display') {
                            return `<span class="text-primary">${numberFormat(adjustedPrice)}</span>`;
                        }
                        return adjustedPrice || 0;
                    }
                },
                {
                    data: null,
                    className: "text-danger font-weight-bold",
                    render: function(data, type, row) {
                        const totalQty = calculateTotalQty(row);
                        const stockId = row.stockid;

                        const unitPrice = parseFloat(row.weighted_unit_price) || 0;
                        const adjustPrice = customUnitPrices[stockId] !== undefined ? customUnitPrices[stockId] : 0;
                        const effectivePrice = unitPrice > 0 ? unitPrice : (parseFloat(adjustPrice) || 0);
                        const factor = landingFactors[stockId] !== undefined ? landingFactors[stockId] : 1;
                        const adjustedPrice = effectivePrice * factor;
                        const qtyTimesAdjustedPrice = totalQty * adjustedPrice;

                        if (type === 'display') {
                            return `<span class="text-danger">${numberFormat(qtyTimesAdjustedPrice)}</span>`;
                        }
                        return qtyTimesAdjustedPrice || 0;
                    }
                },
                {
                    data: "materialcost",
                    className: "text-warning font-weight-bold",
                    render: function(data, type, row) {
                        if (type === 'display') {
                            return numberFormat(data || 0);
                        }
                        return data || 0;
                    }
                },
                {
                    data: "latest_trandate",
                    className: "text-center",
                    render: function(data, type, row) {
                        const status = getStockStatus(data);

                        if (type === 'sort') {
                            if (!data) return 999999;
                            const today = new Date();
                            const transDate = new Date(data);
                            return today - transDate;
                        }

                        if (type === 'display') {
                            return `<span class="badge ${status.class}" style="font-size: 0.85rem; padding: 8px 12px;">
                                    <i class="fas ${status.icon} mr-1"></i> ${status.status}
                                </span>`;
                        }
                        
                        return status.status;
                    }
                }
            ],
            initComplete: function() {
                loadData();
            }
        });

        // Handle unit price input changes
        $('#datatable tbody').on('input', '.unit-price-input', function() {
            const $input = $(this);
            const stockId = $input.data('stockid');
            const originalPrice = parseFloat($input.data('original')) || 0;

            const inputValue = $input.val();
            const newPrice = inputValue === '' ? '' : parseFloat(inputValue) || 0;

            if (newPrice === '') {
                delete customUnitPrices[stockId];
                $input.removeClass('edited');
            } else if (newPrice !== originalPrice) {
                customUnitPrices[stockId] = newPrice;
                $input.addClass('edited');
            } else {
                delete customUnitPrices[stockId];
                $input.removeClass('edited');
            }

            updateRowCalculations(stockId);
            updateCustomizationsCount();
            updateStatusStatistics(allData);
        });

        // Auto-save on blur for unit price
        $('#datatable tbody').on('blur', '.unit-price-input', function() {
            const $input = $(this);
            const stockId = $input.data('stockid');
            const currentValue = $input.val();

            if (currentValue === '') {
                autoSaveToDatabase(stockId, 'adjust_unit_price', 0, false);
            } else {
                const numericValue = parseFloat(currentValue) || 0;
                if (numericValue < 0) {
                    $input.val(0);
                    autoSaveToDatabase(stockId, 'adjust_unit_price', 0, false);
                } else {
                    autoSaveToDatabase(stockId, 'adjust_unit_price', numericValue, false);
                }
            }
            updateRowCalculations(stockId);
        });

        // Handle landing factor input changes
        $('#datatable tbody').on('input', '.landing-factor-input', function() {
            const $input = $(this);
            const stockId = $input.data('stockid');
            const newFactor = parseFloat($input.val()) || 1;

            if (newFactor < 0.01) {
                $input.val(0.01);
                landingFactors[stockId] = 0.01;
            } else {
                landingFactors[stockId] = newFactor;
            }

            if (newFactor !== 1) {
                $input.addClass('edited');
            } else {
                $input.removeClass('edited');
            }

            updateRowCalculations(stockId);
            updateCustomizationsCount();
            updateStatusStatistics(allData);
        });

        // Auto-save on blur for landing factor
        $('#datatable tbody').on('blur', '.landing-factor-input', function() {
            const $input = $(this);
            const stockId = $input.data('stockid');
            const value = parseFloat($input.val()) || 1;

            if (value < 0.01) {
                $input.val(0.01);
                autoSaveToDatabase(stockId, 'landing_factor', 0.01, false);
            } else {
                autoSaveToDatabase(stockId, 'landing_factor', value, false);
            }
            updateRowCalculations(stockId);
        });

        // Handle input blur for validation
        $('#datatable tbody').on('blur', '.unit-price-input', function() {
            const $input = $(this);
            const currentValue = $input.val();

            if (currentValue === '') {
                return;
            }

            const numericValue = parseFloat(currentValue) || 0;

            if (numericValue < 0) {
                $input.val(0);
                $input.trigger('input');
            }
        });

        $('#datatable tbody').on('blur', '.landing-factor-input', function() {
            const $input = $(this);
            const value = parseFloat($input.val()) || 1;

            if (value < 0.01) {
                $input.val(0.01);
                $input.trigger('input');
            }
        });

        // Function to update customizations count display
        function updateCustomizationsCount() {
            const customPriceCount = Object.keys(customUnitPrices).length;
            const factorCount = Object.keys(landingFactors).length;

            $('#customizationsCount').remove();
            if (customPriceCount > 0 || factorCount > 0) {
                $('.btn-professional').after(`
                    <span id="customizationsCount" class="badge badge-info ml-2 p-2" style="font-size: 0.9rem;">
                        <i class="fas fa-tag mr-1"></i>
                        ${customPriceCount} custom price${customPriceCount !== 1 ? 's' : ''}, 
                        ${factorCount} factor${factorCount !== 1 ? 's' : ''}
                    </span>
                `);
            }
        }

        // Quantity filter button handlers
        $('.qty-filter-btn[data-filter]').on('click', function() {
            const filter = $(this).data('filter');
            const filterText = getFilterText(filter);

            showFilterLoading(filterText);
            $('.qty-filter-btn[data-filter]').addClass('loading');

            setTimeout(() => {
                $('.qty-filter-btn[data-filter]').removeClass('active');
                $(this).addClass('active');
                currentFilter = filter;
                applyFilters();
                $('.qty-filter-btn[data-filter]').removeClass('loading');
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

        // Apply both quantity and status filters
        function applyFilters() {
            let filteredData = allData;

            switch (currentFilter) {
                case 'non-zero':
                    filteredData = filteredData.filter(item => calculateTotalQty(item) > 0);
                    break;
                case 'zero':
                    filteredData = filteredData.filter(item => calculateTotalQty(item) === 0);
                    break;
                case 'both':
                    break;
            }

            datatable.clear();
            datatable.rows.add(filteredData).draw();
            updateFilterCounts();
            hideFilterLoading();

            const priceItems = filteredData.filter(item => item.total_bpitems_price > 0).length;
            let filterMessage = `Filter applied: `;
            if (currentFilter === 'non-zero') filterMessage += 'In Stock, ';
            else if (currentFilter === 'zero') filterMessage += 'Out of Stock, ';

            if (currentStatusFilter === 'running') filterMessage += 'Fast Moving';
            else if (currentStatusFilter === 'slow') filterMessage += 'Slow Moving';
            else if (currentStatusFilter === 'dead') filterMessage += 'Dead Stock';
            else if (currentStatusFilter === 'extremely-dead') filterMessage += 'Extremely Dead';
            else filterMessage += 'All Status';

            filterMessage += ` (${filteredData.length} items, ${priceItems} with prices)`;
            showNotification(filterMessage, 'success');
        }

        // Load data function
        function loadData() {
            isCalculatingPrices = true;

            $('#loadingOverlay').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <h5 class="text-muted">Loading Inventory Data</h5>
                    <p class="text-muted">Please wait while we fetch the latest information. It may take one to two minutes only.</p>
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
                        updateStatusStatistics(allData);
                        applyFilters();

                        const itemsWithPrice = allData.filter(item => item.total_bpitems_price > 0).length;
                        showNotification(`✅ Loaded ${response.count} products with prices (${itemsWithPrice} have parchino data)`, 'success');

                        loadSavedCustomizations();
                        updateCustomizationsCount();

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
                        errorMsg = 'Request took too long.';
                    } else {
                        errorMsg += 'Please check your connection.';
                    }

                    showNotification(errorMsg, 'error');
                }
            });
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

            $('#totalBrands').text(brands.length);
            $('#totalCategories').text(categories.length);
            $('#inStockItems').text(inStockItems);

            updateFilterCounts();
        }

        // ============ IMPORT FUNCTIONALITY ============

        $('#csvFile').on('change', function() {
            const file = this.files[0];
            if (!file) {
                $('#processImport').prop('disabled', true);
                $('#importPreview').hide();
                $('#fileInfo').hide();
                return;
            }

            const fileName = file.name;
            const fileSize = (file.size / 1024).toFixed(2) + ' KB';

            $(this).next('.custom-file-label').html(fileName);
            $('#fileName').text(fileName);
            $('#fileSize').text(fileSize);
            $('#fileInfo').show();

            $('#processImport').prop('disabled', false);
            parseCSVFile(file);
        });

        $('#cancelImport').on('click', function() {
            if (confirm('Are you sure you want to cancel the import process?')) {
                importCancelled = true;
                resetImportDisplay();
                showNotification('⛔ Import cancelled by user', 'error');
            }
        });

        function resetImportDisplay() {
            $('#uploadProgress').hide();
            $('#importPreview').show();
            $('#processImport').prop('disabled', false);
            $('#csvFile').prop('disabled', false);
            $('#uploadStats').hide();
            $('#timer').text('00:00');
            $('#uploadProgressBar').css('width', '0%').text('0%');
            $('#uploadStatus').text('Processing...');

            if (importTimerInterval) {
                clearInterval(importTimerInterval);
                importTimerInterval = null;
            }
        }

        function parseCSVFile(file) {
            if (!file) return;

            const reader = new FileReader();

            reader.onload = function(e) {
                const content = e.target.result;
                const lines = content.split('\n');
                const previewData = [];

                const dataRows = lines.filter((line, index) => index > 0 && line.trim() && !line.startsWith('#')).length;
                $('#fileRows').text(dataRows);
                $('#previewTable tbody').empty();

                const headers = lines[0].split(',').map(h => h.trim().replace(/["']/g, ''));

                const stockIdIndex = headers.findIndex(h =>
                    h.toLowerCase().includes('stock id') ||
                    h.toLowerCase() === 'stock id' ||
                    h.toLowerCase() === 'stockid' ||
                    h.toLowerCase() === 'stock_id'
                );

                const adjustPriceIndex = headers.findIndex(h =>
                    h.toLowerCase().includes('adjust unit price') ||
                    h.toLowerCase().includes('adjust') ||
                    h.toLowerCase() === 'adjust unit price' ||
                    h.toLowerCase() === 'adjust price'
                );

                const landingFactorIndex = headers.findIndex(h =>
                    h.toLowerCase().includes('landing factor') ||
                    h.toLowerCase() === 'landing factor' ||
                    h.toLowerCase() === 'landing_factor'
                );

                if (stockIdIndex === -1) {
                    showNotification('Could not find "Stock ID" column in the CSV file', 'error');
                    $('#importPreview').hide();
                    $('#processImport').prop('disabled', true);
                    return;
                }

                // Add preview header with column info
                $('#previewTable tbody').append(`
                    <tr class="table-info">
                        <td colspan="5" class="text-center">
                            <strong>Found columns:</strong> 
                            Stock ID ✓ 
                            ${adjustPriceIndex !== -1 ? '| Adjust Price ✓' : '| Adjust Price ✗'} 
                            ${landingFactorIndex !== -1 ? '| Landing Factor ✓' : '| Landing Factor ✗'}
                        </td>
                    </tr>
                `);

                let changedCount = 0;
                let factorChangedCount = 0;
                let invalidCount = 0;

                for (let i = 1; i < Math.min(lines.length, 21); i++) {
                    const line = lines[i].trim();
                    if (!line || line.startsWith('#')) continue;

                    const values = parseCSVLine(line);
                    if (values.length <= Math.max(stockIdIndex, adjustPriceIndex, landingFactorIndex)) continue;

                    const stockId = values[stockIdIndex]?.trim().replace(/["']/g, '');

                    // Parse Adjust Unit Price
                    let newPrice = '';
                    if (adjustPriceIndex !== -1 && values[adjustPriceIndex]) {
                        const priceStr = values[adjustPriceIndex]?.trim().replace(/["']/g, '');
                        if (priceStr && priceStr.trim() !== '') {
                            const numericStr = priceStr.replace(/[^0-9.-]/g, '');
                            newPrice = parseFloat(numericStr);
                            if (isNaN(newPrice)) newPrice = '';
                        }
                    }

                    // Parse Landing Factor
                    let newFactor = '';
                    if (landingFactorIndex !== -1 && values[landingFactorIndex]) {
                        const factorStr = values[landingFactorIndex]?.trim().replace(/["']/g, '');
                        if (factorStr && factorStr.trim() !== '') {
                            const numericStr = factorStr.replace(/[^0-9.-]/g, '');
                            newFactor = parseFloat(numericStr);
                            if (isNaN(newFactor)) newFactor = '';
                        }
                    }

                    let status = '';
                    let statusClass = '';
                    let currentPrice = '';
                    let currentFactor = '';
                    let changeIcon = '';
                    let changes = [];

                    if (!stockId) {
                        status = 'Missing Stock ID';
                        statusClass = 'text-danger';
                        invalidCount++;
                    } else {
                        const stockExists = allData.some(item => item.stockid === stockId);

                        if (stockExists) {
                            const stockItem = allData.find(item => item.stockid === stockId);
                            
                            // Check price change
                            currentPrice = customUnitPrices[stockId] !== undefined ?
                                customUnitPrices[stockId] :
                                (parseFloat(stockItem?.weighted_unit_price) || 0);
                            
                            if (newPrice !== '' && parseFloat(newPrice) !== parseFloat(currentPrice)) {
                                changes.push('Price');
                                changedCount++;
                            }

                            // Check factor change
                            currentFactor = landingFactors[stockId] !== undefined ?
                                landingFactors[stockId] : 1;
                            
                            if (newFactor !== '' && parseFloat(newFactor) !== parseFloat(currentFactor)) {
                                changes.push('Factor');
                                factorChangedCount++;
                            }

                            if (changes.length > 0) {
                                status = 'Will update: ' + changes.join(' + ');
                                statusClass = 'text-success';
                                changeIcon = '✅';
                            } else {
                                status = 'No changes';
                                statusClass = 'text-muted';
                                changeIcon = '➡️';
                            }
                        } else {
                            status = 'Stock ID not found';
                            statusClass = 'text-danger';
                            invalidCount++;
                        }
                    }

                    previewData.push({
                        stockId: stockId || '-',
                        currentPrice: currentPrice !== '' ? numberFormat(currentPrice) : '-',
                        newPrice: newPrice !== '' ? numberFormat(newPrice) : '(empty)',
                        currentFactor: currentFactor !== '' ? currentFactor.toFixed(2) : '1.00',
                        newFactor: newFactor !== '' ? newFactor.toFixed(2) : '(empty)',
                        changeIcon: changeIcon,
                        status: status,
                        statusClass: statusClass
                    });
                }

                // Add summary row
                $('#previewTable tbody').append(`
                    <tr class="table-warning">
                        <td colspan="5" class="text-center">
                            <strong>Preview Summary:</strong> 
                            ${changedCount} price changes, 
                            ${factorChangedCount} factor changes, 
                            ${invalidCount} errors 
                            (showing first 20 rows)
                        </td>
                    </tr>
                `);

                previewData.forEach(item => {
                    $('#previewTable tbody').append(`
                        <tr>
                            <td>${item.stockId}</td>
                            <td>${item.currentPrice}<br><small class="text-muted">Factor: ${item.currentFactor}</small></td>
                            <td>${item.newPrice}<br><small class="text-muted">Factor: ${item.newFactor}</small></td>
                            <td class="text-center">${item.changeIcon}</td>
                            <td class="${item.statusClass} font-weight-bold">${item.status}</td>
                        </tr>
                    `);
                });

                if (lines.length - 1 > 20) {
                    $('#previewTable tbody').append(`
                        <tr>
                            <td colspan="5" class="text-muted text-center">
                                <i class="fas fa-ellipsis-h mr-2"></i>${lines.length - 1 - 20} more rows...
                            </td>
                        </tr>
                    `);
                }

                $('#importPreview').show();

                $('#importModal').data('csvData', {
                    stockIdIndex: stockIdIndex,
                    priceIndex: adjustPriceIndex,
                    factorIndex: landingFactorIndex,
                    lines: lines,
                    totalRows: lines.length - 1
                });

                $('#processImport').prop('disabled', changedCount === 0 && factorChangedCount === 0);

                if (changedCount === 0 && factorChangedCount === 0 && lines.length > 1) {
                    showNotification('No changes detected in the uploaded file', 'info');
                }
            };

            reader.readAsText(file);
        }

        function parseCSVLine(line) {
            const result = [];
            let current = '';
            let inQuotes = false;

            for (let i = 0; i < line.length; i++) {
                const char = line[i];

                if (char === '"') {
                    inQuotes = !inQuotes;
                } else if (char === ',' && !inQuotes) {
                    result.push(current);
                    current = '';
                } else {
                    current += char;
                }
            }

            result.push(current);
            return result;
        }

        function extractNumericPrice(priceStr) {
            if (!priceStr) return NaN;
            const numericStr = priceStr.replace(/[^0-9.-]/g, '');
            return parseFloat(numericStr);
        }

        function startImportTimer() {
            importStartTime = Date.now();
            importTimerInterval = setInterval(function() {
                if (importCancelled) return;
                const elapsedSeconds = (Date.now() - importStartTime) / 1000;
                $('#timer').text(formatTime(elapsedSeconds));
            }, 100);
        }

        function updateImportProgress(processed, total, success, errors) {
            const percent = Math.round((processed / total) * 100);
            $('#uploadProgressBar').css('width', percent + '%').text(percent + '%');
            $('#uploadStatus').html(`Processed ${processed} of ${total} rows`);
            $('#processedRows').text(processed);
            $('#successRows').text(success);
            $('#errorRows').text(errors);
            $('#uploadStats').show();
        }

        $('#processImport').on('click', function() {
            const csvData = $('#importModal').data('csvData');
            if (!csvData) return;

            const {
                stockIdIndex,
                priceIndex,
                factorIndex,
                lines,
                totalRows
            } = csvData;

            importCancelled = false;
            importResults = {
                success: [],
                errors: []
            };

            $('#importPreview').hide();
            $('#fileInfo').hide();
            $('#uploadProgress').show();
            $('#processImport').prop('disabled', true);
            $('#csvFile').prop('disabled', true);

            startImportTimer();

            let processed = 0;
            let success = 0;
            let errors = 0;
            let unchanged = 0;
            let priceUpdates = 0;
            let factorUpdates = 0;

            function processBatch(startIndex, batchSize) {
                if (importCancelled) {
                    resetImportDisplay();
                    return;
                }

                const endIndex = Math.min(startIndex + batchSize, lines.length);

                for (let i = startIndex; i < endIndex; i++) {
                    const line = lines[i].trim();
                    if (!line || line.startsWith('#')) {
                        if (!line.startsWith('#')) processed++;
                        continue;
                    }

                    const values = parseCSVLine(line);

                    if (values.length <= Math.max(stockIdIndex, priceIndex, factorIndex)) {
                        importResults.errors.push({
                            stockId: 'Unknown',
                            error: 'Invalid row format'
                        });
                        errors++;
                        processed++;
                        continue;
                    }

                    const stockId = values[stockIdIndex]?.trim().replace(/["']/g, '');
                    
                    // Parse Adjust Unit Price
                    let newPrice = '';
                    if (priceIndex !== -1 && values[priceIndex]) {
                        const priceStr = values[priceIndex]?.trim().replace(/["']/g, '');
                        if (priceStr && priceStr.trim() !== '') {
                            const numericStr = priceStr.replace(/[^0-9.-]/g, '');
                            newPrice = parseFloat(numericStr);
                            if (isNaN(newPrice)) newPrice = '';
                        }
                    }

                    // Parse Landing Factor
                    let newFactor = '';
                    if (factorIndex !== -1 && values[factorIndex]) {
                        const factorStr = values[factorIndex]?.trim().replace(/["']/g, '');
                        if (factorStr && factorStr.trim() !== '') {
                            const numericStr = factorStr.replace(/[^0-9.-]/g, '');
                            newFactor = parseFloat(numericStr);
                            if (isNaN(newFactor)) newFactor = '';
                        }
                    }

                    if (!stockId) {
                        importResults.errors.push({
                            stockId: 'Missing',
                            error: 'Stock ID is required'
                        });
                        errors++;
                    } else {
                        const stockExists = allData.some(item => item.stockid === stockId);

                        if (stockExists) {
                            const stockItem = allData.find(item => item.stockid === stockId);
                            const currentPrice = customUnitPrices[stockId] !== undefined ?
                                customUnitPrices[stockId] :
                                (parseFloat(stockItem?.weighted_unit_price) || 0);
                            
                            const currentFactor = landingFactors[stockId] !== undefined ?
                                landingFactors[stockId] : 1;

                            let hasChanges = false;

                            // Update price if changed
                            if (newPrice !== '' && parseFloat(newPrice) !== parseFloat(currentPrice)) {
                                customUnitPrices[stockId] = newPrice;
                                autoSaveToDatabase(stockId, 'adjust_unit_price', newPrice, true);
                                updateRowPrice(stockId, newPrice);
                                updateRowCalculations(stockId);
                                priceUpdates++;
                                hasChanges = true;
                            } else if (newPrice === '' && customUnitPrices[stockId] !== undefined) {
                                delete customUnitPrices[stockId];
                                autoSaveToDatabase(stockId, 'adjust_unit_price', 0, true);
                                updateRowPrice(stockId, null);
                                updateRowCalculations(stockId);
                                priceUpdates++;
                                hasChanges = true;
                            }

                            // Update factor if changed
                            if (newFactor !== '' && parseFloat(newFactor) !== parseFloat(currentFactor)) {
                                landingFactors[stockId] = newFactor;
                                autoSaveToDatabase(stockId, 'landing_factor', newFactor, true);
                                updateRowFactor(stockId, newFactor);
                                updateRowCalculations(stockId);
                                factorUpdates++;
                                hasChanges = true;
                            } else if (newFactor === '' && landingFactors[stockId] !== 1) {
                                delete landingFactors[stockId];
                                autoSaveToDatabase(stockId, 'landing_factor', 1, true);
                                updateRowFactor(stockId, null);
                                updateRowCalculations(stockId);
                                factorUpdates++;
                                hasChanges = true;
                            }

                            if (hasChanges) {
                                importResults.success.push({
                                    stockId: stockId,
                                    priceChanged: newPrice !== '' && parseFloat(newPrice) !== parseFloat(currentPrice),
                                    factorChanged: newFactor !== '' && parseFloat(newFactor) !== parseFloat(currentFactor)
                                });
                                success++;
                            } else {
                                unchanged++;
                            }
                        } else {
                            importResults.errors.push({
                                stockId: stockId,
                                error: 'Stock ID not found'
                            });
                            errors++;
                        }
                    }

                    processed++;
                }

                updateImportProgress(processed, totalRows, success, errors);

                if (processed < totalRows && !importCancelled) {
                    setTimeout(() => {
                        processBatch(endIndex, batchSize);
                    }, 50);
                } else if (!importCancelled) {
                    clearInterval(importTimerInterval);
                    showNotification(`✅ Import complete: ${priceUpdates} price updates, ${factorUpdates} factor updates, ${errors} errors`, 'success');
                    showImportResults(importResults, unchanged, priceUpdates, factorUpdates);
                    updateStatusStatistics(allData);
                }
            }

            processBatch(1, 50);
        });

        function updateRowPrice(stockId, newPrice) {
            if (newPrice === null || newPrice === '') {
                delete customUnitPrices[stockId];
            } else {
                customUnitPrices[stockId] = newPrice;
            }

            const rows = $('#datatable').DataTable().rows().data();
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].stockid === stockId) {
                    const row = $('#datatable').DataTable().row(i);
                    const $priceCell = $(row.node()).find('td:eq(8)');
                    const $priceInput = $priceCell.find('.unit-price-input');
                    if ($priceInput.length) {
                        if (newPrice === null || newPrice === '') {
                            $priceInput.val('').removeClass('edited');
                        } else {
                            $priceInput.val(newPrice).addClass('edited');
                        }
                    }
                    break;
                }
            }
        }

        function updateRowFactor(stockId, newFactor) {
            if (newFactor === null || newFactor === '') {
                delete landingFactors[stockId];
            } else {
                landingFactors[stockId] = newFactor;
            }

            const rows = $('#datatable').DataTable().rows().data();
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].stockid === stockId) {
                    const row = $('#datatable').DataTable().row(i);
                    const $factorCell = $(row.node()).find('td:eq(9)');
                    const $factorInput = $factorCell.find('.landing-factor-input');
                    if ($factorInput.length) {
                        if (newFactor === null || newFactor === '') {
                            $factorInput.val(1).removeClass('edited');
                        } else {
                            $factorInput.val(newFactor).addClass('edited');
                        }
                    }
                    break;
                }
            }
        }

        function showImportResults(results, unchanged = 0, priceUpdates = 0, factorUpdates = 0) {
            resetImportDisplay();
            $('#csvFile').val('');
            $('.custom-file-label').html('Choose file...');

            const elapsedSeconds = importStartTime ? ((Date.now() - importStartTime) / 1000).toFixed(1) : 0;

            $('#successCount').text(results.success.length);
            $('#errorCount').text(results.errors.length);

            $('.modal-header.bg-info').next('.alert-info').remove();
            $('.modal-header.bg-info').after(`
                <div class="alert alert-info text-center mb-0">
                    <i class="fas fa-clock mr-2"></i>
                    Processing completed in ${elapsedSeconds} seconds
                    <br>
                    <strong>${priceUpdates} price updates</strong>, 
                    <strong>${factorUpdates} factor updates</strong>, 
                    ${unchanged} unchanged, 
                    ${results.errors.length} errors
                </div>
            `);

            if (results.errors.length > 0) {
                $('#errorDetails').show();
                $('#errorList').empty();

                results.errors.slice(0, 10).forEach(error => {
                    $('#errorList').append(`
                        <tr>
                            <td>${error.stockId}</td>
                            <td class="text-danger">${error.error}</td>
                        </tr>
                    `);
                });

                if (results.errors.length > 10) {
                    $('#errorList').append(`
                        <tr>
                            <td colspan="2" class="text-muted text-center">
                                ... and ${results.errors.length - 10} more errors
                            </td>
                        </tr>
                    `);
                }
            } else {
                $('#errorDetails').hide();
            }

            $('#importModal').modal('hide');
            $('#importResultsModal').modal('show');

            updateCustomizationsCount();
        }

        $('#importResultsModal').on('hidden.bs.modal', function() {
            $('.modal-header.bg-success').next('.alert-info').remove();
            applyFilters();
        });

        $(document).on('keydown', function(e) {
            if (e.ctrlKey && e.key === 'i') {
                e.preventDefault();
                $('#importModal').modal('show');
            }
        });
    });
</script>
</body>

</html>