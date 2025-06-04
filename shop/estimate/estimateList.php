<?php
// Connect to DB
// Replace with your actual DB connection
include_once("../../v2/config1.php");
// Get date filters from GET or default to current month start and today
$from = isset($_GET['fromDate']) ? $_GET['fromDate'] : date('Y-m-01');
$to = isset($_GET['toDate']) ? $_GET['toDate'] : date('Y-m-d');
?>

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
            flex-wrap: wrap;
        }

        .form-inline {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .form-inline label {
            margin-right: 5px;
        }

        .form-inline button {
            margin-left: 10px;
        }

        .btn-success {
            min-width: 100px;
            margin-bottom: -10px;
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
                <form class="form-inline" method="GET" action="">
                    <label for="fromDate" class="mr-2">From</label>
                    <input type="date" id="fromDate" name="fromDate" class="form-control mr-2" value="<?php echo htmlspecialchars($from); ?>">
                    <label for="toDate" class="mr-2">To</label>
                    <input type="date" id="toDate" name="toDate" class="form-control mr-2" value="<?php echo htmlspecialchars($to); ?>">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                </form>

                <div>
                    <a href="estimate.php" target="_blank">
                        <button class="btn btn-success btn-sm">New Estimate</button>
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
                        <th>Created By</th>
                        <th>Amount PKR</th>
                        <th>Date</th>
                        <th>Add Items</th>
                        <th>Original</th>
                        <th>Print</th>
                        <th>Internal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $SQL = "
            SELECT
                estimateshopsale.orderno as sr,
                estimateshopsale.mp,
                estimateshopsale.payment,
                estimateshopsale.complete,
                estimateshopsale.orddate,
                estimateshopsale.advance,
                estimateshopsale.branchcode,
                estimateshopsale.crname,
                estimateshopsale.salesman,
                estimateshopsale.created_by,
                estimateshopsale.paid,
                estimateshopsale.complete,
                estimatecustbranch.brname as name,
                (SUM(estimateshopsalelines.price * estimateshopsalelines.quantity) * (1 - (estimateshopsale.discount / 100))) - estimateshopsale.discountPKR as amt
            FROM estimateshopsale
            INNER JOIN estimateshopsalelines ON estimateshopsale.orderno = estimateshopsalelines.orderno
            INNER JOIN estimatecustbranch ON estimateshopsale.branchcode = estimatecustbranch.branchcode
            WHERE estimateshopsale.orddate >= '$from'
              AND estimateshopsale.orddate <= '$to'
            GROUP BY estimateshopsalelines.orderno
            ORDER BY estimateshopsale.orderno DESC
        ";

                    $res = mysqli_query($conn, $SQL);

                    if ($res && mysqli_num_rows($res) > 0) {
                        while ($row = mysqli_fetch_assoc($res)) {
                            $orddate = date('Y/m/d', strtotime($row['orddate']));
                            $name = ($row['branchcode'] == "WALKIN01") ? html_entity_decode($row['crname']) : $row['name'];
                            $advance = number_format($row['advance'], 2);
                            $amt = number_format($row['amt'], 2);
                            $paid = number_format($row['paid'], 2);

                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['sr']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['branchcode']) . "</td>";
                            echo "<td>" . htmlspecialchars($name) . "</td>";
                            echo "<td>" . htmlspecialchars($row['salesman']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['created_by']) . "</td>";
                            echo "<td>" . $amt . "</td>";
                            echo "<td>" . $orddate . "</td>";
                            if ($row['complete'] == 1) {
                                echo "<td></td>";
                            } else {
                                echo "<td><a href='../estimate/editShopSale.php?orderno=" . urlencode($row['sr']) . "&orignal' target='_blank' class='btn btn-success btn-sm'>Add Items</a></td>";
                            }
                            echo "<td><a href='../pos/shopSalePrint.php?orderno=" . urlencode($row['sr']) . "&orignal' target='_blank' class='btn btn-danger btn-sm'>Print</a></td>";
                            echo "<td><a href='../pos/shopSalePrint.php?orderno=" . urlencode($row['sr']) . "' target='_blank' class='btn btn-danger btn-sm'>Print</a></td>";
                            echo "<td><a href='../pos/shopSalePrintInternal.php?orderno=" . urlencode($row['sr']) . "' target='_blank' class='btn btn-info btn-sm'>Internal</a></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12' class='text-center'>No estimates found for selected date range.</td></tr>";
                    }
                    ?>
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
                "pageLength": 10,
                "lengthChange": false,
                "ordering": true
            });
        });
    </script>
</body>

</html>