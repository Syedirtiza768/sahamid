<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Estimate Lists</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap + DataTables CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <style>
        body {
            background-color: #f1f2f6;
            font-family: 'Segoe UI', sans-serif;
        }

        .request-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background-color: #fff;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            font-weight: 900;
            color: #4a4a4a;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header-icon {
            height: 35px;
        }

        .table-section {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .btn-custom {
            padding: 5px 10px;
        }

        .dataTables_wrapper .dataTables_filter {
            float: right;
        }

        .dataTables_wrapper .dataTables_paginate {
            float: right;
        }

        .top-controls {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-inline {
            display: flex;
            gap: 10px;
        }

        .top-controls .form-inline {
            justify-content: center;
            flex-wrap: wrap;
        }

        .top-controls .form-inline label {
            margin-right: 5px;
        }

        .top-controls .form-inline button {
            margin-left: 10px;
        }

        .top-controls .btn-success {
            align-self: center;
        }
    </style>
</head>

<body>
    <div class="request-header">
        <img src="img/estimate.png" alt="Estimate Icon" class="header-icon">
        <span class="header-title">Estimate List</span>
    </div>

    <div class="container-fluid mt-3">
        <div class="table-section">

            <div class="top-controls">
                <form class="form-inline">
                    <label for="fromDate" class="mr-2">From</label>
                    <input type="date" id="fromDate" class="form-control mr-2">
                    <label for="toDate" class="mr-2">To</label>
                    <input type="date" id="toDate" class="form-control mr-2">
                    <button type="button" class="btn btn-primary btn-sm">Search</button>
                </form>

                <div>
                    <a href="estimate.php" target="_blank">
                        <button class="btn btn-success btn-sm" style="min-width: 100px; margin-bottom: -10px">New Estimate</button>
                    </a>
                </div>

            </div>

            <table id="estimateTable" class="table table-bordered table-striped table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>Sr #</th>
                        <th>CCode</th>
                        <th>Name</th>
                        <th>Salesman</th>
                        <th>Amount PKR</th>
                        <th>Advance PKR</th>
                        <th>Date</th>
                        <th>Add Items</th>
                        <th>Original</th>
                        <th>Print</th>
                        <th>Internal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>6208</td>
                        <td>SR-717</td>
                        <td>Ali Shabbar</td>
                        <td>Mubashar Tahir</td>
                        <td>20000</td>
                        <td>0</td>
                        <td>2025/04/15</td>
                        <td><button class="btn btn-warning btn-sm">Add Internal Items</button></td>
                        <td><button class="btn btn-danger btn-sm">Print</button></td>
                        <td><button class="btn btn-danger btn-sm">Print</button></td>
                        <td><button class="btn btn-info btn-sm">Internal Print</button></td>
                    </tr>
                    <!-- Add more dynamic rows if needed -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#estimateTable').DataTable({
                "pageLength": 5,
                "lengthChange": false,
                "ordering": true
            });
        });
    </script>
</body>

</html>