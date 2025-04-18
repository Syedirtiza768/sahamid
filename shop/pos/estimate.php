<?php
include_once("../../v2/config1.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAHamid ERP</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f1f2f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            width: auto;
        }

        .main-container {
            padding: 20px;
        }

        .value-box {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            text-align: right;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .value-title {
            font-size: 0.85rem;
            color: #666;
        }

        .value-amount {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .item-form {
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .form-section {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
        }

        .custom-btn {
            width: 20%;
            min-width: 150px;
            padding: 12px 25px;
            background-color: #007bff;
            /* Bootstrap primary blue */
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
        }

        .custom-btn:active {
            transform: scale(0.98);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }

        .custom-btn:disabled {
            background-color: #cce0ff;
            color: #6c757d;
            cursor: not-allowed;
        }


        .custom-btn1 {
            width: 30%;
            min-width: 150px;
            padding: 12px 25px;
            background: linear-gradient(to right, #1d976c, #93f9b9);
            /* Green gradient */
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .custom-btn1:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.2);
        }

        .custom-btn1:active {
            transform: scale(0.98);
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body>
    <div class="request-header">
        <img src="img/estimate.png" alt="Estimate Icon" class="header-icon">
        <span class="header-title">Create New Estimate</span>
    </div>

    <div class="container-fluid main-container">
        <!-- Value Summary Boxes -->
        <div class="row mb-4">
            <div class="col-md">
                <div class="value-box">
                    <div class="value-title">Total Outstanding</div>
                    <div class="value-amount text-primary">0 PKR</div>
                </div>
            </div>
            <div class="col-md">
                <div class="value-box">
                    <div class="value-title">Document Total</div>
                    <div class="value-amount text-success">22000 PKR</div>
                </div>
            </div>
            <div class="col-md">
                <div class="value-box">
                    <div class="value-title">Credit Remaining</div>
                    <div class="value-amount text-danger">-21000 PKR</div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="item-form">
            <div class="form-section row justify-content-center">
                <div class="form-group col-md-3">
                    <label class="form-label">Customer Name:</label>
                    <input type="text" class="form-control" value="Ali Shabar">
                </div>
                <div class="form-group col-md-3">
                    <label class="form-label">Phone:</label>
                    <input type="number" class="form-control" placeholder="Phone">
                </div>
                <div class="form-group col-md-3">
                    <label class="form-label">Address:</label>
                    <input type="text" class="form-control" placeholder="Address">
                </div>
            </div>

            <div class="form-section row justify-content-center">
                <div class="form-group col-md-3">
                    <label class="form-label">DBA:</label>
                    <input type="text" class="form-control" value="SA HAMID AND COMPANY" readonly>
                </div>
                <div class="form-group col-md-3">
                    <label class="form-label">Salesman:</label>
                    <input type="text" class="form-control" value="Mubashar Tahir">
                </div>
            </div>
        </div>


        <!-- Add New Item -->
        <div class="item-form">
            <div class="form-group text-center">
                <button class="btn btn-primary mb-3 custom-btn" onclick="addItemRow()">+ Add New Item</button>
            </div>

            <div id="itemsContainer"></div>
        </div>

        <!-- Payment Info -->
        <div class="item-form">
            <div class="form-section row justify-content-end">
                <div class="form-group col-md-3">
                    <label class="form-label">Amount Paid:</label>
                    <div class="input-group">
                        <input type="text" class="form-control text-right" placeholder="0.00">
                        <div class="input-group-append">
                            <span class="input-group-text">PKR</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button class="btn btn-success custom-btn1">Proceed</button>
            </div>
        </div>
    </div>
</body>

<script>
    let itemId = 0;

    const uomOptions = `
    <option>meters</option>
    <option>kgs</option>
    <option>liters</option>
    <option>length</option>
    <option>hours</option>
    <option>feet</option>
    <option>packets</option>
`;

    function addItemRow() {
        itemId++;
        const container = document.getElementById('itemsContainer');

        const row = document.createElement('div');
        row.className = 'form-section row align-items-end item-row mb-3';
        row.setAttribute('id', `item-${itemId}`);

        row.innerHTML = `
        <div class="form-group col-md-3">
            <label class="form-label">Item Name:</label>
            <input type="text" class="form-control" placeholder="Enter item name">
        </div>
        <div class="form-group col-md-2">
            <label class="form-label">Model/Comments:</label>
            <input type="text" class="form-control" placeholder="Model or comment">
        </div>
        <div class="form-group col-md-1">
            <label class="form-label">Qty:</label>
            <input type="number" class="form-control qty" value="1" oninput="calculateSubtotal(${itemId})">
        </div>
        <div class="form-group col-md-2">
            <label class="form-label">UOM:</label>
            <select class="form-control">${uomOptions}</select>
        </div>
        <div class="form-group col-md-2">
            <label class="form-label">Unit Price:</label>
            <input type="number" class="form-control price" value="0" oninput="calculateSubtotal(${itemId})">
        </div>
        <div class="form-group col-md-1">
            <label class="form-label">Subtotal:</label>
            <input type="text" class="form-control subtotal" readonly>
        </div>
        <div class="form-group col-md-1 text-center">
            <button class="btn btn-danger btn-sm" onclick="removeItemRow('item-${itemId}')">-</button>
        </div>
    `;

        container.appendChild(row);
    }

    function calculateSubtotal(id) {
        const row = document.getElementById(`item-${id}`);
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.price').value) || 0;
        const subtotal = qty * price;
        row.querySelector('.subtotal').value = subtotal.toFixed(2);
    }

    function removeItemRow(rowId) {
        const row = document.getElementById(rowId);
        row.remove();
    }
</script>

</html>

columns: [{
                    "data": "stockid"
                },
                {
                    "data": "mnfCode"
                },
                {
                    "data": "mnfpno"
                },
                {
                    "data": "description"
                },
                {
                    "data": "manufacturers_name"
                },
                {
                    "data": "qohA"
                },
                {
                    "data": "dcitemQty"
                },
                {
                    "data": "dcexclusivegsttotalamount"
                },
                {
                    "data": "invoiceitemQty"
                },
                {
                    "data": "invoiceexclusivegsttotalamount"
                },
                {
                    "data": "averageInvoiceFactor"
                },
                {
                    "data": "itemQtyShopsale"
                },
                {
                    "data": "shopsaletotalamount"
                },
                {
                    "data": "averageShopSaleFactor"
                },
                {
                    "data": "qohB"
                },
                {
                    "data": "materialcost"
                }

            ],