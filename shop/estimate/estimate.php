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

            .card {
                background-color: #fff;
                border-radius: 0.5rem;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
                padding: 1rem;
                /* reduced padding */
                font-size: 0.9rem;
                /* slightly smaller font overall */
            }

            #subtotalDisplay {
                font-size: 1.25rem;
                /* reduce from h4 default size */
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

            .option-cards {
                display: flex;
                gap: 10px;
            }

            .option-cards input[type="radio"] {
                display: none;
            }

            .option-card {
                flex: 1;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 8px;
                text-align: center;
                cursor: pointer;
                transition: 0.3s ease;
                background: #f9f9f9;
                font-weight: 500;
            }

            .option-cards input[type="radio"]:checked+.option-card {
                background: #28a745;
                color: white;
                border-color: #28a745;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            }

            .section-heading {
                background: #f8f9fa;
                /* light gray background */
                border-left: 4px solid #007bff;
                /* blue accent */
                padding: 10px 15px;
                border-radius: 6px;
            }

            .section-heading h5 {
                font-weight: 600;
                color: #343a40;
                /* dark text */
            }

            .section-heading h5 {
                font-weight: 600;
                color: #343a40;
                display: inline-block;
                padding: 6px 20px;
                border-radius: 6px;
                background: #f8f9fa;
                /* light gray background */
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            }

            .sticky-subtotal {
                position: fixed;
                top: 120px;
                right: 40px;
                z-index: 1050;
            }

            .subtotal-card {
                background: linear-gradient(135deg, #7dc580, #28a745);
                color: white;
                padding: 18px 25px;
                border-radius: 12px;
                box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
                text-align: right;
                min-width: 320px;
                min-height: 120px;
                transition: transform 0.2s ease, box-shadow 0.3s ease;
                text-align: center;
            }

            .subtotal-card:hover {
                transform: translateY(-3px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            }

            .subtotal-label {
                font-size: 22px;
                font-weight: 500;
                opacity: 0.9;
                margin-bottom: 8px;
                display: block;
                color: rgb(0, 0, 0);
            }

            .subtotal-value {
                font-size: 28px;
                font-weight: 700;
            }
        </style>
    </head>

    <body>
        <div class="request-header">
            <img src="img/estimate.png" alt="Estimate Icon" class="header-icon">
            <span class="header-title">Create New Estimate</span>
        </div>

        <div class="container-fluid main-container">

            <?php
            // Assuming $conn is your active database connection
            $salesmen = [];

            $query = "SELECT salesmanname FROM salesman";
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $salesmen[] = $row['salesmanname'];
                }
            }
            ?>

            <!-- Sticky Subtotal Card -->
            <div class="sticky-subtotal">
                <div class="subtotal-card">
                    <div class="subtotal-label">Subtotal</div>
                    <div class="subtotal-value" id="subtotalDisplay">PKR 0.00</div>
                </div>
            </div>


            <!-- Customer Info -->
            <div class="item-form">
                <div class="section-heading mb-5 text-center">
                    <h5 class="mb-0">👤 User Information</h5>
                </div>


                <div class="form-section row justify-content-center">
                    <div class="form-group col-md-3">
                        <label class="form-label">Customer Name:</label>
                        <input type="text" id="customername" class="form-control" placeholder="Customer Name">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="form-label">Phone:</label>
                        <input type="number" id="phone" class="form-control" placeholder="Phone">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="form-label">Address:</label>
                        <input type="text" id="address1" class="form-control" placeholder="Address">
                    </div>
                </div>

                <div class="form-section row justify-content-center">
                    <div class="form-group col-md-3">
                        <label class="form-label">DBA:</label>
                        <select class="form-control" id="dbalist">
                            <option value="SA HAMID AND COMPANY" selected>SA HAMID AND COMPANY</option>
                            <option value="CESCO INTERNATIONAL">CESCO INTERNATIONAL</option>
                            <option value="SAH">SAH</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="form-label">Salesman:</label>
                        <select class="form-control" id="salesman">
                            <option value="">Select Salesman</option>
                            <option value="Ashfaq Ahmad (N)">Ashfaq Ahmad (N)</option>
                            <option value="Jalal Nasir">Jalal Nasir</option>


                            <!-- <?php foreach ($salesmen as $salesman): ?>
                                <option value="<?= htmlspecialchars($salesman) ?>">
                                    <?= htmlspecialchars($salesman) ?>
                                </option>
                            <?php endforeach; ?> -->
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="form-label">Customer Reference:</label>
                        <input type="text" id="reference" class="form-control" placeholder="Customer Reference">
                    </div>
                </div>
            </div>


            <!-- Add New Item -->
            <div class="item-form">
                <div class="section-heading mb-5 text-center">
                    <h5 class="mb-0">📦 Items Information</h5>
                </div>

                <div class="form-group text-center">
                    <button class="btn btn-primary mb-3 custom-btn" onclick="addItemRow()">+ Add New Item</button>
                </div>

                <div id="itemsContainer"></div>
            </div>

            <!-- Payment Info -->
            <div class="item-form">
                <div class="section-heading mb-5 text-center">
                    <h5 class="mb-0">💳 Payment Information</h5>
                </div>

                <div class="form-section row justify-content-center">
                    <!-- Advance Percentage -->
                    <div class="form-group col-md-3">
                        <label class="form-label">Advance (%):</label>
                        <div class="input-group">
                            <input type="number" id="advance" class="form-control text-right" placeholder="0.00">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-3">
                        <label class="form-label">On Delivery (%):</label>
                        <div class="input-group">
                            <input type="number" id="ondelivery" class="form-control text-right" placeholder="0.00">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Commissioning Percentage -->
                    <div class="form-group col-md-3">
                        <label class="form-label">Commissioning (%):</label>
                        <div class="input-group">
                            <input type="number" id="commision" class="form-control text-right" placeholder="0.00">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Commissioning Percentage -->
                    <!-- <div class="form-group col-md-3">
                        <label class="form-label">Payment Term (Days):</label>
                        <div class="input-group">
                            <input type="number" id="paymentin" class="form-control text-right" placeholder="0">
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>

            <!-- Payment Info -->
            <div class="item-form">
                <div class="section-heading mb-5 text-center">
                    <h5 class="mb-0">📑 Finalization & Discounts</h5>
                </div>

                <div class="form-section row justify-content-center">
                    <!-- Discount Amount -->
                    <div class="form-group col-md-3">
                        <label class="form-label">Discount (Amount):</label>
                        <div class="input-group">
                            <input type="number" id="discountPKR" class="form-control text-right" placeholder="0.00">
                            <div class="input-group-append">
                                <span class="input-group-text">PKR</span>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Percentage -->
                    <div class="form-group col-md-3">
                        <label class="form-label">Discount (%):</label>
                        <div class="input-group">
                            <input type="number" id="discount" class="form-control text-right" placeholder="0.00">
                            <div class="input-group-append">
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Percentage -->
                    <!-- <div class="form-group col-md-3">
                        <label class="form-label">Delivery Status:</label>
                        <div class="input-group">
                            <input type="number" id="expected" class="form-control text-right" placeholder="0">
                            <div class="input-group-append">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                    </div> -->

                    <!-- Show Total Choice -->
                    <div class="form-group col-md-3">
                        <label class="form-label d-block">Total on Receipt:</label>
                        <div class="option-cards">
                            <input type="radio" id="totalShow" name="showTotal" value="yes" checked>
                            <label for="totalShow" class="option-card">Show</label>

                            <input type="radio" id="totalHide" name="showTotal" value="no">
                            <label for="totalHide" class="option-card">Hide</label>
                        </div>
                    </div>
                </div>
                <div class="text-center">
                    <button class="btn btn-success custom-btn1" id="proceed">Proceed</button>
                </div>
            </div>
        </div>
    </body>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script>
        let showTotalOption = "yes";

        document.querySelectorAll("input[name='showTotal']").forEach((el) => {
            el.addEventListener("change", function() {
                showTotalOption = this.value;
            });
        });



        let itemId = 0;

        const uomOptions = `
        <option>each</option>
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
            row.className = 'form-section item-row mb-3';
            row.setAttribute('id', `item-${itemId}`);

            row.innerHTML = `
        <div class="row align-items-end">
            <div class="form-group col-md-2">
                <label class="form-label">Item Name:</label>
                <input type="text" class="form-control" placeholder="Enter item name">
            </div>
            <div class="form-group col-md-2">
                <label class="form-label">Model/Comments:</label>
                <input type="text" class="form-control" placeholder="Model or comment">
            </div>
            <div class="form-group col-md-1">
                <label class="form-label">Qty:</label>
                <input type="number" class="form-control qty" value="1">
            </div>
            <div class="form-group col-md-1">
                <label class="form-label">UOM:</label>
                <select class="form-control">${uomOptions}</select>
            </div>
            <div class="form-group col-md-2">
                <label class="form-label">Unit Price:</label>
                <input type="number" class="form-control price" value="0">
            </div>
            <div class="form-group col-md-2">
                <label class="form-label">Delivery Status:</label>
                <input type="text" class="form-control delivery-status" placeholder="Mention days">
            </div>
            <div class="form-group col-md-1">
                <label class="form-label">Subtotal:</label>
                <input type="text" class="form-control subtotal" readonly>
            </div>
            <div class="form-group col-md-0.5 text-center">
                <button class="btn btn-danger btn-sm" onclick="removeItemRow('item-${itemId}')">-</button>
            </div>
        </div>
        <hr class="my-3">
    `;

            container.appendChild(row);

            // Add event listeners to auto-update subtotal
            const qtyInput = row.querySelector('.qty');
            const priceInput = row.querySelector('.price');

            qtyInput.addEventListener('input', () => calculateSubtotal(itemId));
            priceInput.addEventListener('input', () => calculateSubtotal(itemId));

            // Trigger initial calculation
            calculateSubtotal(itemId);

            updateSubtotal();
        }


        function updateSubtotal() {
            let total = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty').value) || 0;
                const price = parseFloat(row.querySelector('.price').value) || 0;
                const subtotal = qty * price;

                row.querySelector('.subtotal').value = subtotal.toFixed(2);
                total += subtotal;
            });

            // Apply Discount Amount (PKR)
            let discountPKR = parseFloat(document.getElementById('discountPKR').value) || 0;

            // Apply Discount Percentage
            let discountPercent = parseFloat(document.getElementById('discount').value) || 0;
            if (discountPercent > 100) discountPercent = 100; // avoid negative totals

            let discountFromPercent = (total * discountPercent) / 100;

            // Final total after discounts
            let finalTotal = total - discountPKR - discountFromPercent;
            if (finalTotal < 0) finalTotal = 0;

            document.getElementById('subtotalDisplay').innerText = `PKR ${finalTotal.toFixed(2)}`;
        }


        // Recalculate subtotal when values change
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
                updateSubtotal();
            }
        });


        function calculateSubtotal(id) {
            const row = document.getElementById(`item-${id}`);
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const subtotal = qty * price;
            row.querySelector('.subtotal').value = subtotal.toFixed(2);
        }

        function removeItemRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.remove();
                updateSubtotal(); // Update subtotal after removal
            }
        }

        $("#proceed").on("click", function() {

            let ref = $(this);
            ref.prop("disabled", true);

            let client = {};

            // Validate Customer Info
            if ($("#customername").val().trim() === "") {
                swal("Error", "Enter New Customer Name", "error");
                ref.prop("disabled", false);
                return;
            }

            if ($("#phone").val().trim() === "") {
                swal("Error", "Enter Phone Number", "error");
                ref.prop("disabled", false);
                return;
            }

            if ($("#salesman").val() === "") {
                swal("Error", "Select Customer Salesman", "error");
                ref.prop("disabled", false);
                return;
            }

            if ($("#address1").val().trim() === "") {
                swal("Error", "Enter Customer Street Address", "error");
                ref.prop("disabled", false);
                return;
            }

            client["name"] = $("#customername").val();
            client["salesman"] = $("#salesman").val();
            client["reference"] = $("#reference").val();
            client["dba"] = $("#dbalist").val();
            client["ctype"] = "Miscellaneous";
            client["address1"] = $("#address1").val();
            client["phone"] = $("#phone").val();

            // Collect Items
            let count = 0;
            let pass = true;
            let message = "";
            let items = {};

            $("#itemsContainer").find(".item-row").each(function() {
                let quantity = parseFloat($(this).find(".qty").val()) || 0;
                if (quantity <= 0) {
                    message = "Item with 0 Quantity Found. Please enter a quantity greater than zero.";
                    pass = false;
                    return false; // exit each loop
                }

                let price = parseFloat($(this).find(".price").val()) || 0;
                let desc = $(this).find(".form-control[placeholder='Enter item name']").val() || "";
                let note = $(this).find(".form-control[placeholder='Model or comment']").val() || "";

                // UOM select
                let uom = $(this).find("select").not(".delivery-status").val() || "";

                // Delivery Status select
                let deliveryStatus = $(this).find(".delivery-status").val() || "Instant";

                if (pass) {
                    if (quantity <= 0) {
                        message = "Item with 0 Quantity Found.";
                        pass = false;
                    } else if (price <= 0) {
                        message = "Item with 0 Price Found.";
                        pass = false;
                    } else if (desc.trim() === "") {
                        message = "Item without details found.";
                        pass = false;
                    }
                }

                items[count] = {
                    quantity: quantity,
                    price: price,
                    desc: desc.replace("\n", "&#10;"),
                    note: note.replace("\n", "&#10;"),
                    uom: uom,
                    deliveryStatus: deliveryStatus // <-- added here
                };

                count++;
            });


            if (!pass) {
                swal("Error", message, "error");
                ref.prop("disabled", false);
                return;
            }

            if (count === 0) {
                swal("Error", "No Items Added.", "error");
                ref.prop("disabled", false);
                return;
            }

            let payment = "estimate";

            if (!payment) {
                swal("Error", "Payment Type Not Selected!!!", "error");
                ref.prop("disabled", false);
                return;
            }

            // let advance = parseFloat($("#advance-payment").val()) || 0;
            let name = "none";
            let discount = parseFloat($("#discount").val()) || 0;
            let discountPKR = parseFloat($("#discountPKR").val()) || 0;


            let salesman = "none";

            let dispatchvia = "none";
            let customerref = "none";
            let advance = parseFloat($("#advance").val()) || 0;
            let ondelivery = parseFloat($("#ondelivery").val()) || 0;
            let commision = parseFloat($("#commision").val()) || 0;
            let paymentin = parseFloat($("#paymentin").val()) || 0;
            let expected = parseFloat($("#expected").val()) || 0;
            let paid = 0;

            // Call backend
            generateBill(client, items, payment, advance, ondelivery, commision, paymentin, expected, name, discount, discountPKR, showTotalOption, salesman, dispatchvia, customerref, paid);
        });


        function generateBill(client, items, payment, advance, ondelivery, commision, paymentin, expected, name, discount, discountPKR, showTotalOption, salesman, dispatchvia, creferance, paid) {
            $.post("api/generateBillEstimate.php", {
                client: client,
                items: items,
                payment: payment,
                advance: advance,
                ondelivery: ondelivery,
                commision: commision,
                paymentin: paymentin,
                expected: expected,
                name: name,
                discount: discount,
                discountPKR: discountPKR,
                showTotalOption: showTotalOption,
                salesman: salesman,
                dispatchvia: dispatchvia,
                creferance: creferance,
                paid: paid
            }, function(res) {
                try {
                    console.log(res);
                    res = JSON.parse(res);

                    if (res.status === "success") {
                        window.location = "../pos/shopSalePrint.php?orderno=" + res.code;
                        // window.location = "estimateList.php?orderno=" + res.code;
                    } else {
                        // Handle error case
                        alert("Error: " + (res.message || "Something went wrong."));
                    }
                } catch (e) {
                    alert("Invalid response from server.");
                    console.error("Parse error:", e, "Raw response:", res);
                }
            });
        }

        document.getElementById('discountPKR').addEventListener('input', updateSubtotal);
        document.getElementById('discount').addEventListener('input', updateSubtotal);
    </script>

    </html>