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

        /* Style for unit price input */
        .unit-price-input {
            width: 100px;
            padding: 5px 8px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .unit-price-input:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            outline: none;
        }

        .unit-price-input.edited {
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
            background: rgba(0,0,0,0.7);
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

                    <!-- Import/Export Workflow Zone -->
                    <div class="row px-4 mb-3">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-sync-alt mr-2"></i>Bulk Price Update Workflow</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="text-center p-3 border-right">
                                                <span class="badge badge-info badge-pill p-2 mb-2">Step 1</span>
                                                <h6><i class="fas fa-download text-info mr-2"></i>Download Current Prices</h6>
                                                <p class="text-muted small">Click the "Download CSV Report" button above to export current prices</p>
                                                <div class="workflow-hint">
                                                    <i class="fas fa-arrow-right"></i> Edit the "Adjust Unit Price" column in Excel
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="text-center p-3">
                                                <span class="badge badge-success badge-pill p-2 mb-2">Step 2</span>
                                                <h6><i class="fas fa-upload text-success mr-2"></i>Import Updated File</h6>
                                                <p class="text-muted small">Upload the same CSV file with your price changes</p>
                                                <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#importModal">
                                                    <i class="fas fa-upload mr-2"></i>Import Updated CSV
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Import Modal -->
                    <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title" id="importModalLabel">
                                        <i class="fas fa-upload mr-2"></i>Import Updated CSV File
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <strong>Quick Tip:</strong> Upload the same CSV file you downloaded. 
                                        The system will look for the <strong>"Adjust Unit Price"</strong> column to update prices.
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6 class="text-info">Expected CSV Format (matches your download):</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered bg-white">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th>Stock ID</th>
                                                                    <th>Brand</th>
                                                                    <th>Category</th>
                                                                    <th>Condition</th>
                                                                    <th>Total Quantity</th>
                                                                    <th>Total Price</th>
                                                                    <th>Unit Price</th>
                                                                    <th class="text-success font-weight-bold">Adjust Unit Price</th>
                                                                    <th>Qty × Unit Price</th>
                                                                    <th>List Price</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>ABC123</td>
                                                                    <td>Brand A</td>
                                                                    <td>Category X</td>
                                                                    <td>New</td>
                                                                    <td>10</td>
                                                                    <td>1250.50</td>
                                                                    <td>125.05</td>
                                                                    <td class="text-success font-weight-bold">150.00</td>
                                                                    <td>1500.00</td>
                                                                    <td>120.00</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <p class="text-muted small mt-2">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Only the <strong class="text-success">Adjust Unit Price</strong> column will be read and updated. 
                                                        Other columns are ignored during import.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="csvFile" class="font-weight-bold">Select the CSV file you edited:</label>
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

                                    <!-- Upload Progress Display (replaces alerts) -->
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
                                                    <small>Success</small>
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
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered" id="previewTable">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Stock ID</th>
                                                        <th>New Adjust Unit Price</th>
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
                                    <button type="button" class="btn btn-success" id="processImport" disabled>
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
                                    <th>Condition</th>
                                    <th style="color:rgb(53, 69, 85);">Qty</th>
                                    <th>Total Price</th>
                                    <th>Unit Price</th>
                                    <th style="color:rgb(26, 128, 231)">Adjust Unit Price</th>
                                    <th>Qty × Unit Price</th>
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
        
        // Store custom unit prices - THIS WILL PERSIST ALL CUSTOM PRICES
        let customUnitPrices = {};

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

        // ✅ Notification function (kept for other notifications, but import uses display)
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

        // ✅ Update Qty × Unit Price column based on custom input
        function updateQtyTimesPrice(rowId, totalQty, customPrice) {
            const cell = $(`#qty-times-price-${rowId}`);
            if (cell.length) {
                const qtyTimesPrice = totalQty * customPrice;
                cell.html(`
                    <div class="price-result dynamic-value edited">
                        <span class="text-primary">${numberFormat(qtyTimesPrice)}</span>
                        <br>
                        <small class="text-muted">
                            ${totalQty} units × ${numberFormat(customPrice)}
                        </small>
                    </div>
                `);
            }
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
                    // Use the persistent customUnitPrices object instead of DOM elements
                    // This includes ALL custom prices, even for items with zero quantity
                    
                    // Convert to base64 to pass via URL
                    const customPricesJson = JSON.stringify(customUnitPrices);
                    const customPricesBase64 = btoa(unescape(encodeURIComponent(customPricesJson)));
                    
                    // Get current filter
                    const filterParam = currentFilter ? `&filter=${currentFilter}` : '';
                    
                    // Show notification that export is starting
                    showNotification('Preparing CSV with ' + Object.keys(customUnitPrices).length + ' custom prices...', 'info');
                    
                    // Redirect with custom prices
                    window.location.href = 'export.php?export=csv' + filterParam + '&custom_prices=' + encodeURIComponent(customPricesBase64);
                }
            }],
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
                // TOTAL PRICE COLUMN
                {
                    data: "total_bpitems_price",
                    className: "text-success font-weight-bold",
                    render: function(data, type, row) {
                        const totalQty = calculateTotalQty(row);

                        if (totalQty > 0) {
                            if (data > 0) {
                                return `
                                    <div class="price-result">
                                        <span class="text-success"> ${numberFormat(data)}</span>
                                        <br>
                                        <small class="text-muted">
                                            for ${row.total_quantity || totalQty} units
                                        </small>
                                    </div>`;
                            } else {
                                return `
                                    <div class="price-result">
                                        <span class="text-muted"> 0.00</span>
                                        <br>
                                        <small class="text-muted">
                                            No parchino data
                                        </small>
                                    </div>`;
                            }
                        } else {
                            return `
                                <div class="price-result">
                                    <span class="text-muted"> 0.00</span>
                                    <br>
                                    <small class="text-muted">
                                        Out of stock
                                    </small>
                                </div>`;
                        }
                    }
                },
                // UNIT PRICE COLUMN (original, read-only)
                {
                    data: "weighted_unit_price",
                    className: "text-info font-weight-bold",
                    render: function(data, type, row) {
                        const totalQty = calculateTotalQty(row);

                        if (totalQty > 0) {
                            if (data > 0) {
                                return `
                                    <div class="price-result">
                                        <span class="text-info"> ${numberFormat(data)}</span>
                                        <br>
                                        <small class="text-muted">
                                            weighted average
                                        </small>
                                    </div>`;
                            } else {
                                return `
                                    <div class="price-result">
                                        <span class="text-muted"> 0.00</span>
                                        <br>
                                        <small class="text-muted">
                                            No parchino data
                                        </small>
                                    </div>`;
                            }
                        } else {
                            return `
                                <div class="price-result">
                                    <span class="text-muted"> 0.00</span>
                                    <br>
                                    <small class="text-muted">
                                        Out of stock
                                    </small>
                                </div>`;
                        }
                    }
                },
                // ADJUST UNIT PRICE COLUMN (input box)
                {
                    data: null,
                    className: "text-center",
                    render: function(data, type, row) {
                        const stockId = row.stockid;
                        const totalQty = calculateTotalQty(row);
                        const originalPrice = parseFloat(row.weighted_unit_price) || 0;
                        
                        // Get custom price from persistent storage if exists
                        const customPrice = customUnitPrices[stockId] !== undefined ? 
                            customUnitPrices[stockId] : originalPrice;
                        
                        // Only show input if there's quantity
                        if (totalQty > 0) {
                            return `<input type="number" 
                                    class="unit-price-input ${customPrice !== originalPrice ? 'edited' : ''}" 
                                    data-stockid="${stockId}"
                                    data-original="${originalPrice}"
                                    value="${customPrice}"
                                    min="0" 
                                    step="0.01">`;
                        } else {
                            // Even though we don't show input, we still store custom prices for zero quantity items
                            // Just show a dash, but the price is still stored in customUnitPrices
                            return `<span class="text-muted" title="Custom price: ${customPrice !== originalPrice ? 'PKR ' + customPrice : 'Not set'}">-</span>`;
                        }
                    }
                },
                // QTY × UNIT PRICE COLUMN (dynamic)
                {
                    data: null,
                    className: "text-primary font-weight-bold",
                    render: function(data, type, row) {
                        const totalQty = calculateTotalQty(row);
                        const stockId = row.stockid;
                        
                        // Use custom price from persistent storage if available
                        const unitPrice = customUnitPrices[stockId] !== undefined ? 
                            customUnitPrices[stockId] : (parseFloat(row.weighted_unit_price) || 0);
                        
                        const qtyTimesUnitPrice = totalQty * unitPrice;
                        
                        // Generate unique ID for this cell
                        const cellId = `qty-times-price-${stockId.replace(/[^a-zA-Z0-9]/g, '_')}`;

                        if (totalQty > 0 && unitPrice > 0) {
                            return `
                                <div class="price-result" id="${cellId}">
                                    <span class="text-primary">${numberFormat(qtyTimesUnitPrice)}</span>
                                    <br>
                                    <small class="text-muted">
                                        ${totalQty} units × ${numberFormat(unitPrice)}
                                    </small>
                                </div>`;
                        } else if (totalQty > 0 && unitPrice === 0) {
                            return `
                                <div class="price-result" id="${cellId}">
                                    <span class="text-muted"> 0.00</span>
                                    <br>
                                    <small class="text-muted">
                                        No unit price
                                    </small>
                                </div>`;
                        } else {
                            return `
                                <div class="price-result" id="${cellId}">
                                    <span class="text-muted"> 0.00</span>
                                    <br>
                                    <small class="text-muted">
                                        Out of stock
                                    </small>
                                </div>`;
                        }
                    }
                },
                // LIST PRICE COLUMN
                {
                    data: "materialcost",
                    className: "text-danger font-weight-bold",
                    render: function(data) {
                        return data > 0 ? 'PKR ' + numberFormat(data) : '<span class="text-muted">PKR 0.00</span>';
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
            const newPrice = parseFloat($input.val()) || 0;
            const originalPrice = parseFloat($input.data('original')) || 0;
            
            // Store in persistent object (this works for ALL items, even zero quantity ones)
            if (newPrice !== originalPrice) {
                customUnitPrices[stockId] = newPrice;
                $input.addClass('edited');
                
                // Show notification for first few custom prices
                if (Object.keys(customUnitPrices).length <= 3) {
                    showNotification(`Custom price set for ${stockId}: PKR ${newPrice}`, 'success');
                }
            } else {
                delete customUnitPrices[stockId];
                $input.removeClass('edited');
            }
            
            // Update the custom price count display
            updateCustomPriceCount();
            
            // Find the row and update the Qty × Unit Price column
            const row = datatable.row($input.closest('tr')).data();
            if (row) {
                const totalQty = calculateTotalQty(row);
                const safeStockId = stockId.replace(/[^a-zA-Z0-9]/g, '_');
                updateQtyTimesPrice(safeStockId, totalQty, newPrice);
            }
        });

        // Function to update custom price count display
        function updateCustomPriceCount() {
            const customCount = Object.keys(customUnitPrices).length;
            $('#customPriceCount').remove();
            if (customCount > 0) {
                $('.btn-professional').after(`
                    <span id="customPriceCount" class="badge badge-info ml-2 p-2" style="font-size: 0.9rem;">
                        <i class="fas fa-tag mr-1"></i>${customCount} custom price${customCount > 1 ? 's' : ''} set
                    </span>
                `);
            }
        }

        // Handle input blur
        $('#datatable tbody').on('blur', '.unit-price-input', function() {
            const $input = $(this);
            const stockId = $input.data('stockid');
            const currentValue = parseFloat($input.val()) || 0;
            
            // Ensure at least 0
            if (currentValue < 0) {
                $input.val(0);
                $input.trigger('input');
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
                        
                        // Clear any previous custom prices when loading new data?
                        // Comment this out if you want to keep custom prices across reloads
                        // customUnitPrices = {};
                        
                        updateStatistics(allData);
                        applyQuantityFilter(currentFilter);

                        const itemsWithPrice = allData.filter(item => item.total_bpitems_price > 0).length;
                        showNotification(`✅ Loaded ${response.count} products with prices (${itemsWithPrice} have parchino data)`, 'success');
                        updateCustomPriceCount();

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

            $('#totalBrands').text(brands.length);
            $('#totalCategories').text(categories.length);
            $('#inStockItems').text(inStockItems);

            updateFilterCounts();
        }

        // Add reset button for custom prices
        $(document).on('click', '#resetCustomPrices', function() {
            if (Object.keys(customUnitPrices).length > 0) {
                if (confirm('Reset all custom unit prices to original values?')) {
                    customUnitPrices = {};
                    $('#customPriceCount').remove();
                    
                    // Reload current filter to refresh display
                    applyQuantityFilter(currentFilter);
                    showNotification('All custom prices have been reset', 'success');
                }
            } else {
                showNotification('No custom prices to reset', 'info');
            }
        });

        // Optional: Add a button to show custom prices summary
        $(document).on('click', '#showCustomPrices', function() {
            const count = Object.keys(customUnitPrices).length;
            if (count > 0) {
                let message = `You have ${count} custom price(s) set:\n`;
                let shown = 0;
                for (let [stockId, price] of Object.entries(customUnitPrices)) {
                    if (shown++ < 10) {
                        message += `\n${stockId}: PKR ${price}`;
                    } else {
                        message += `\n... and ${count - 10} more`;
                        break;
                    }
                }
                alert(message);
            } else {
                showNotification('No custom prices set', 'info');
            }
        });

        // ============ IMPORT FUNCTIONALITY WITH TIMER DISPLAY ============

        // Update file input label and show file info
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
            
            // Enable process button
            $('#processImport').prop('disabled', false);
            
            // Parse and preview CSV
            parseCSVFile(file);
        });

        // Cancel import
        $('#cancelImport').on('click', function() {
            if (confirm('Are you sure you want to cancel the import process?')) {
                importCancelled = true;
                resetImportDisplay();
                showNotification('⛔ Import cancelled by user', 'error');
            }
        });

        // Reset import display
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

        // Parse CSV file
        function parseCSVFile(file) {
            if (!file) return;
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const content = e.target.result;
                const lines = content.split('\n');
                const previewData = [];
                
                // Update rows count
                $('#fileRows').text(lines.length - 1);
                
                // Clear previous preview
                $('#previewTable tbody').empty();
                
                // Parse headers (first line)
                const headers = lines[0].split(',').map(h => h.trim().replace(/["']/g, ''));
                
                // Look for "Adjust Unit Price" column
                const priceColumnIndex = headers.findIndex(h => 
                    h.toLowerCase().includes('adjust unit price') || 
                    h.toLowerCase().includes('our price') ||
                    h.toLowerCase() === 'adjust unit price'
                );
                
                // Look for Stock ID column
                const stockIdIndex = headers.findIndex(h => 
                    h.toLowerCase().includes('stock id') || 
                    h.toLowerCase() === 'stock id' ||
                    h.toLowerCase() === 'stockid'
                );
                
                if (stockIdIndex === -1) {
                    showNotification('Could not find "Stock ID" column in the CSV file', 'error');
                    $('#importPreview').hide();
                    $('#processImport').prop('disabled', true);
                    return;
                }
                
                if (priceColumnIndex === -1) {
                    showNotification('Could not find "Adjust Unit Price" column in the CSV file', 'error');
                    $('#importPreview').hide();
                    $('#processImport').prop('disabled', true);
                    return;
                }
                
                // Parse data rows (skip header)
                let validCount = 0;
                let invalidCount = 0;
                
                for (let i = 1; i < Math.min(lines.length, 11); i++) {
                    if (!lines[i].trim()) continue;
                    
                    const values = parseCSVLine(lines[i]);
                    
                    if (values.length <= Math.max(stockIdIndex, priceColumnIndex)) continue;
                    
                    const stockId = values[stockIdIndex]?.trim();
                    const priceStr = values[priceColumnIndex]?.trim();
                    const price = extractNumericPrice(priceStr);
                    
                    let status = '';
                    let statusClass = '';
                    
                    if (!stockId) {
                        status = '❌ Missing Stock ID';
                        statusClass = 'text-danger';
                        invalidCount++;
                    } else if (isNaN(price) || price < 0) {
                        status = '❌ Invalid Price';
                        statusClass = 'text-danger';
                        invalidCount++;
                    } else {
                        const stockExists = allData.some(item => item.stockid === stockId);
                        if (stockExists) {
                            status = '✅ Valid';
                            statusClass = 'text-success';
                            validCount++;
                        } else {
                            status = '❌ Stock ID not found';
                            statusClass = 'text-danger';
                            invalidCount++;
                        }
                    }
                    
                    previewData.push({
                        stockId: stockId || '-',
                        price: !isNaN(price) ? price : priceStr,
                        status: status,
                        statusClass: statusClass
                    });
                }
                
                // Show preview summary
                $('#previewTable tbody').append(`
                    <tr class="table-info">
                        <td colspan="3" class="text-center">
                            <strong>Preview Summary:</strong> ${validCount} valid, ${invalidCount} invalid (showing first 10 rows)
                        </td>
                    </tr>
                `);
                
                previewData.forEach(item => {
                    $('#previewTable tbody').append(`
                        <tr>
                            <td>${item.stockId}</td>
                            <td>${item.price}</td>
                            <td class="${item.statusClass} font-weight-bold">${item.status}</td>
                        </tr>
                    `);
                });
                
                if (lines.length > 11) {
                    $('#previewTable tbody').append(`
                        <tr>
                            <td colspan="3" class="text-muted text-center">
                                <i class="fas fa-ellipsis-h mr-2"></i>${lines.length - 11} more rows...
                            </td>
                        </tr>
                    `);
                }
                
                $('#importPreview').show();
                
                // Store parsed data for processing
                $('#importModal').data('csvData', {
                    headers: headers,
                    stockIdIndex: stockIdIndex,
                    priceIndex: priceColumnIndex,
                    lines: lines,
                    totalRows: lines.length - 1
                });
            };
            
            reader.readAsText(file);
        }

        // Helper function to parse CSV line (handles quoted values)
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

        // Helper function to extract numeric price from string
        function extractNumericPrice(priceStr) {
            if (!priceStr) return NaN;
            const numericStr = priceStr.replace(/[^0-9.-]/g, '');
            return parseFloat(numericStr);
        }

        // Start timer for import
        function startImportTimer() {
            importStartTime = Date.now();
            importTimerInterval = setInterval(function() {
                if (importCancelled) return;
                const elapsedSeconds = (Date.now() - importStartTime) / 1000;
                $('#timer').text(formatTime(elapsedSeconds));
            }, 100);
        }

        // Update import progress
        function updateImportProgress(processed, total, success, errors) {
            const percent = Math.round((processed / total) * 100);
            $('#uploadProgressBar').css('width', percent + '%').text(percent + '%');
            $('#uploadStatus').html(`Processed ${processed} of ${total} rows`);
            $('#processedRows').text(processed);
            $('#successRows').text(success);
            $('#errorRows').text(errors);
            $('#uploadStats').show();
        }

        // Process import
        $('#processImport').on('click', function() {
            const csvData = $('#importModal').data('csvData');
            if (!csvData) return;
            
            const { headers, stockIdIndex, priceIndex, lines, totalRows } = csvData;
            
            // Reset import state
            importCancelled = false;
            importResults = {
                success: [],
                errors: []
            };
            
            // Show progress display
            $('#importPreview').hide();
            $('#fileInfo').hide();
            $('#uploadProgress').show();
            $('#processImport').prop('disabled', true);
            $('#csvFile').prop('disabled', true);
            
            // Start timer
            startImportTimer();
            
            let processed = 0;
            let success = 0;
            let errors = 0;
            
            // Process in batches
            function processBatch(startIndex, batchSize) {
                if (importCancelled) {
                    resetImportDisplay();
                    return;
                }
                
                const endIndex = Math.min(startIndex + batchSize, lines.length);
                
                for (let i = startIndex; i < endIndex; i++) {
                    if (!lines[i] || !lines[i].trim()) {
                        processed++;
                        continue;
                    }
                    
                    const values = parseCSVLine(lines[i]);
                    
                    if (values.length <= Math.max(stockIdIndex, priceIndex)) {
                        importResults.errors.push({
                            stockId: 'Unknown',
                            error: 'Invalid row format'
                        });
                        errors++;
                        processed++;
                        continue;
                    }
                    
                    const stockId = values[stockIdIndex]?.trim();
                    const priceStr = values[priceIndex]?.trim();
                    const price = extractNumericPrice(priceStr);
                    
                    if (!stockId) {
                        importResults.errors.push({
                            stockId: 'Missing',
                            error: 'Stock ID is required'
                        });
                        errors++;
                    } else if (isNaN(price) || price < 0) {
                        importResults.errors.push({
                            stockId: stockId,
                            error: `Invalid price: ${priceStr}`
                        });
                        errors++;
                    } else {
                        const stockExists = allData.some(item => item.stockid === stockId);
                        
                        if (stockExists) {
                            customUnitPrices[stockId] = price;
                            importResults.success.push({
                                stockId: stockId,
                                price: price
                            });
                            success++;
                            
                            // Update in datatable if visible
                            updateRowPrice(stockId, price);
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
                
                // Update progress
                updateImportProgress(processed, totalRows, success, errors);
                
                if (processed < totalRows && !importCancelled) {
                    // Process next batch
                    setTimeout(() => {
                        processBatch(endIndex, batchSize);
                    }, 10);
                } else if (!importCancelled) {
                    // Import complete
                    clearInterval(importTimerInterval);
                    showImportResults(importResults);
                }
            }
            
            // Start processing with batch size of 100 rows
            processBatch(1, 100);
        });

        function updateRowPrice(stockId, newPrice) {
            // Update in customUnitPrices
            customUnitPrices[stockId] = newPrice;
            
            // Find and update the row in the table
            const rows = $('#datatable').DataTable().rows().data();
            for (let i = 0; i < rows.length; i++) {
                if (rows[i].stockid === stockId) {
                    const row = $('#datatable').DataTable().row(i);
                    const totalQty = calculateTotalQty(rows[i]);
                    
                    // Update the input if visible
                    const $cell = $(row.node()).find('td:eq(7)');
                    const $input = $cell.find('.unit-price-input');
                    if ($input.length) {
                        $input.val(newPrice).addClass('edited');
                    }
                    
                    // Update Qty × Price column
                    const safeStockId = stockId.replace(/[^a-zA-Z0-9]/g, '_');
                    updateQtyTimesPrice(safeStockId, totalQty, newPrice);
                    
                    break;
                }
            }
        }

        function showImportResults(results) {
            // Reset modal display
            resetImportDisplay();
            $('#csvFile').val('');
            $('.custom-file-label').html('Choose file...');
            
            // Calculate elapsed time
            const elapsedSeconds = importStartTime ? ((Date.now() - importStartTime) / 1000).toFixed(1) : 0;
            
            // Show results
            $('#successCount').text(results.success.length);
            $('#errorCount').text(results.errors.length);
            
            // Add time info to results
            $('.modal-header.bg-success').after(`
                <div class="alert alert-info text-center mb-0">
                    <i class="fas fa-clock mr-2"></i>
                    Processing completed in ${elapsedSeconds} seconds
                    (${(results.success.length / elapsedSeconds).toFixed(1)} rows/sec)
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
            
            // Show results modal
            $('#importModal').modal('hide');
            $('#importResultsModal').modal('show');
            
            // Update custom price count
            updateCustomPriceCount();
        }

        // Handle results modal close
        $('#importResultsModal').on('hidden.bs.modal', function() {
            $('.modal-header.bg-success').next('.alert-info').remove();
            applyQuantityFilter(currentFilter);
        });

        // Add keyboard shortcut for import (Ctrl+I)
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