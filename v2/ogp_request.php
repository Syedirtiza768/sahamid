<?php
include_once("config1.php");

// Fetch data from the database
$query = "SELECT id, request_no, defaultlocation, to_location, status FROM ogp_notifications"; // Replace with your table name and column names
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    // Step 2: Check the quantity of all items related to this notification in the 'submitted_ogp_items' table
    $notification_id = $row['id']; // Get the notification_id

    // Query to check if all items have quantity 0
    $checkItemsSQL = "SELECT quantity FROM submitted_ogp_items WHERE notification_id = ?";
    $checkItemsStmt = $conn->prepare($checkItemsSQL);
    $checkItemsStmt->bind_param("i", $notification_id);
    $checkItemsStmt->execute();
    $checkItemsResult = $checkItemsStmt->get_result();

    // Step 3: Check if all items have quantity 0
    $allZero = true; // Assume all quantities are zero initially
    while ($item = $checkItemsResult->fetch_assoc()) {
        if ($item['quantity'] > 0) {
            $allZero = false; // If any quantity is greater than zero, set to false
            break;
        }
    }

    // Step 4: If all items' quantities are zero, update the status to 'completed'
    if ($allZero) {
        $updateStatusSQL = "UPDATE ogp_notifications SET status = 'completed' WHERE id = ?";
        $updateStatusStmt = $conn->prepare($updateStatusSQL);
        $updateStatusStmt->bind_param("i", $notification_id);
        $updateStatusStmt->execute();
    }
}

$query = "SELECT id, request_no, defaultlocation, to_location, status FROM ogp_notifications"; // Replace with your table name and column names
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OGP Request List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        /* Global Styles */
        body {
            background-color: #dddddd;
            font-family: Arial, sans-serif;
        }

        nav {
            background-color: #cccce5;
        }

        .request-hover:hover {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }

        .dataTables_filter {
            margin: 1rem;
            padding-left: 10px;
        }

        .modal-content {
            background-color: #f9f9f9;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        table {
            background-color: #cccce5;
            /* Same color as navbar */
        }

        table th {
            background-color: #4c51bf;
            /* Maintain header color contrast */
            color: #ffffff;
        }

        table tbody tr:hover {
            background-color: #e4e5f1;
            /* Slightly lighter shade on hover */
        }

        /* Modal Styles (Remove custom positioning) */
        .modal-dialog {
            max-width: 800px;
            margin: 1.75rem auto;
            /* Center modal */
        }

        /* Modal Backdrop */
        .modal-backdrop {
            z-index: 1040;
            /* Ensure backdrop stays below modal */
        }
    </style>
</head>

<body style="background-color: #dddddd" class="bg-gray-100 text-gray-900">
    <!-- Page Header -->
    <nav style="background-color: #cccce5"
        class="block w-full max-w-screen-xl px-6 py-3 mx-auto text-white border shadow-md rounded-xl border-white/80 bg-opacity-80 backdrop-blur-2xl backdrop-saturate-200">
        <div class="flex items-center justify-between text-blue-gray-900">
            <a href="#" class="mr-4 block cursor-pointer py-1.5 font-sans text-base font-semibold leading-relaxed tracking-normal text-gray-700 antialiased">
                <b class="text-orange-500">SA Hamid ERP</b></a>
            <h1 class="text-lg text-gray-600 leading-tight text-center"><b>OGP Request Details</b></h1>
            <div class="hidden lg:block">
                <ul class="flex flex-col gap-2 my-2 lg:mb-0 lg:mt-0 lg:flex-row lg:items-center lg:gap-6">
                    <li class="block p-1 font-sans text-sm antialiased font-medium leading-normal text-blue-gray-900">
                        <button type="button" onClick="document.location.href='/sahamid/'"
                            class="relative rounded-full p-1 text-orange-600 hover:text-black focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                            <span class="absolute -inset-1.5"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                class="bi bi-house" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z" />
                                <path fill-rule="evenodd"
                                    d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z" />
                            </svg>
                        </button>
                    </li>
                    <li class="block p-1 font-sans text-sm antialiased font-medium leading-normal text-blue-gray-900">
                        <button type="button" onClick="logout()" class="relative rounded-full p-1 text-orange-600 hover:text-black focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                            <span class="absolute -inset-1.5"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Main Content -->
    <main class="container my-4">
        <div class="bg-white p-4 shadow-xl rounded-xl">
            <!-- Main Table -->
            <div class="overflow-x-auto ">
                <table id="ogpTable" class="table-auto w-full text-left rounded-xl shadow-md bg-[#cccce5]">
                    <thead class="bg-[#4c51bf] text-white">
                        <tr>
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Requested ID</th>
                            <th class="px-4 py-2">Requested Location</th>
                            <th class="px-4 py-2">Item Location</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-800">
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr class='hover:bg-gray-100 transition-all'>";
                                echo "<td class='px-4 py-2'>{$row['id']}</td>";
                                echo "<td class='request-hover px-4 py-2 cursor-pointer' onclick='fetchData({$row['id']}, this)'>{$row['request_no']}</td>";
                                echo "<td class='px-4 py-2'>{$row['defaultlocation']}</td>";
                                echo "<td class='px-4 py-2'>{$row['to_location']}</td>";
                                echo "<td class='px-4 py-2'>";
                                if ($row['status'] == 'pending') {
                                    echo "<span class='badge bg-warning text-dark'>Pending</span>";
                                } elseif ($row['status'] == 'in_progress') {
                                    echo "<span class='badge bg-info'>In Progress</span>";
                                } elseif ($row['status'] == 'completed') {
                                    echo "<span class='badge bg-success'>Fulfilled</span>";
                                }
                                echo "</td>";
                                echo "<td class='text-center'>";
                                echo "<button class='btn btn-primary btn-sm' onclick='openOGPModal({$row['id']}, \"{$row['request_no']}\")'>Proceed For OGP</button>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center px-4 py-2'>No records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>



    <!-- Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Bootstrap centers modal by default -->
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-xl font-semibold text-center text-gray-800 py-4">
                        Request Items
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBodyContent">
                    <!-- Content will be loaded dynamically -->
                    Loading...
                </div>
            </div>
        </div>
    </div>


    <!-- OGP Modal -->
    <div class="modal fade" id="ogpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-xl font-semibold text-center text-gray-800 py-4">
                        Request Items
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBodyContent1">
                    <!-- Content will be loaded dynamically -->
                    Loading...
                </div>

                <div class="modal-footer" id="modalFooter">
                    <!-- The main submit button will be added dynamically -->
                </div>
            </div>
        </div>
    </div>







    <!-- DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Tooltip initialization
            document.addEventListener('DOMContentLoaded', function() {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });

            // Check URL parameters and pre-fill the table search box if 'request_no' exists
            var queryString = window.location.search;
            var urlParams = new URLSearchParams(queryString);
            var requestNo = urlParams.get('request_no'); // Extract request_no from the URL

            const table = $('#ogpTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                language: {
                    search: "Search:",
                    lengthMenu: "Display _MENU_ records per page",
                }
            });

            // If request_no is present, pre-fill the search box with the request_no value
            if (requestNo) {
                table.search(requestNo).draw();
            }
        });

        // Array to store confirmed items
        let items = [];

        function openOGPModal(requestId, requestNo) {
            items = [];
            // Fetch data from the server
            fetch(`notifications/fetch_ogp_related_data.php?id=${requestId}`)
                .then((response) => response.json())
                .then((data) => {

                    // Check if related_info exists and has items
                    const relatedInfo = data.related_info;
                    const modalBody = document.getElementById('modalBodyContent1');
                    const modalFooter = document.getElementById('modalFooter');

                    if (relatedInfo && relatedInfo.length > 0) {
                        // Populate modal with data for each item in related_info array
                        let tableRows = '';

                        // Loop through each related_info item and create a table row
                        relatedInfo.forEach((item, index) => {
                            // Check if quantity is zero to show "Delivered" span and hide quantity input
                            let quantityColumn = '';
                            let confirmButton = '';
                            let deliveredSpan = '';

                            if (item.quantity === 0) {
                                deliveredSpan = `<span class="text-success">Delivered</span>`; // Delivered colored text
                            } else {
                                quantityColumn = `
                            <input type="number" id="quantity_${index}" name="quantity" class="form-control form-control-sm" min="1" max="${item.quantity}" value="0" required oninput="validateInput(this, '${item.stockcode}')">

                        `;
                                confirmButton = `
                            <button type="button" class="btn btn-success btn-sm" onclick="confirmItemQuantity(${index})">Confirm</button>
                        `;
                            }

                            // Add table rows with dynamic data for each item
                            tableRows += `
                        <tr>
                            <td class="text-xs" id="stockcode_${index}">${item.stockcode}</td>
                            <td class="text-xs" id="quantity_${index}_value">${item.quantity}</td>
                            <td class="text-xs" id="status_${index}">${item.status}</td>
                            <td class="text-xs" id="storeid_${index}">${item.storeid}</td>
                            <td class="text-xs" id="substore_${index}">${item.substore_description || 'N/A'}</td>
                            <!-- Hidden field for substoreid -->
                            <td class="text-xs" style="display: none;" id="substoreid_${index}">${item.substoreid}</td>
                            <td class="text-xs" style="display: none;" id="requestid_${index}">${item.notification_id}</td>
                            <td class="text-xs" style="display: none;" id="OGPrequestid_${index}">${requestNo}</td>
                            <td class="text-xs">
                                ${quantityColumn}
                            </td>
                            <td class="text-xs">
                                ${deliveredSpan}
                                ${confirmButton}
                            </td>
                        </tr>`;
                        });

                        // Insert the table rows into the modal body
                        modalBody.innerHTML = `
                    <table class="table table-bordered" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-xs">Stock Code</th>
                                <th class="text-xs">Required Quantity</th>
                                <th class="text-xs">Status</th>
                                <th class="text-xs">Store ID</th>
                                <th class="text-xs">Substore Description</th>
                                <th class="text-xs">Assign Quantity</th>
                                <th class="text-xs">Confirm</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                `;

                        // Add a main submit button in the modal footer
                        modalFooter.innerHTML = `
                    <button type="button" class="btn btn-primary bg-blue-500 text-white rounded-md" onclick="submitIGP()">Submit IGP</button>
                `;
                    } else {
                        // Handle case where related_info is not available or empty
                        modalBody.innerHTML = "No Item details available";
                        modalFooter.innerHTML = ''; // Hide the submit button if no items are found
                    }

                    // Open the modal
                    const myModal = new bootstrap.Modal(document.getElementById('ogpModal'));
                    myModal.show();
                })
                .catch((error) => {
                    console.error('Error fetching OGP related data:', error);
                });
        }

        // Function to handle confirming individual item quantities (you can adjust this for your needs)


        function confirmItemQuantity(index) {
            const inputField = document.getElementById(`quantity_${index}`);
            const confirmButton = document.querySelector(`button[onclick="confirmItemQuantity(${index})"]`);

            // Check if the entered value is valid
            const enteredValue = inputField.value;
            if (enteredValue && enteredValue > 0) {
                // Disable the input field
                inputField.setAttribute('disabled', true);

                // Hide the Confirm button
                confirmButton.style.display = 'none';

                // Add a Cancel button
                const cancelButton = document.createElement('button');
                cancelButton.textContent = 'Cancel';
                cancelButton.className = 'btn btn-danger btn-sm';
                cancelButton.onclick = () => cancelItemQuantity(index);
                cancelButton.setAttribute('id', `cancel_${index}`);
                confirmButton.parentNode.appendChild(cancelButton);

                // Add the item to the items array
                const stockCode = document.getElementById(`stockcode_${index}`).innerText; // Get stock code
                const storeId = document.getElementById(`storeid_${index}`).innerText; // Get store ID
                const substoreDesc = document.getElementById(`substore_${index}`).innerText; // Get substore description

                items.push({
                    Code: stockCode,
                    quantity: enteredValue,
                    storeid: storeId,
                    substore_description: substoreDesc
                });
            } else {
                alert('Please enter a valid quantity.');
            }
        }

        function cancelItemQuantity(index) {
            const inputField = document.getElementById(`quantity_${index}`);
            const confirmButton = document.querySelector(`button[onclick="confirmItemQuantity(${index})"]`);
            const cancelButton = document.getElementById(`cancel_${index}`);

            // Reenable the input field
            inputField.removeAttribute('disabled');

            // Show the Confirm button
            confirmButton.style.display = 'inline-block';

            // Remove the Cancel button
            cancelButton.remove();

            // Remove the item from the items array
            const stockCode = document.getElementById(`stockcode_${index}`).innerText;
            items = items.filter(item => item.Code !== stockCode);
        }

        // Ristrict user to exceed required quantity
        function validateInput(input, code) {
            const max = parseInt(input.max, 10); // Get the max value
            const value = parseInt(input.value, 10); // Get the current input value

            if (value > max) {
                alert(`The value cannot exceed the maximum limit of ${max} fro ${code}.`);
                input.value = max; // Reset to the maximum allowed value
            }
        }


        // Function to handle the main submit action
        function submitIGP() {
            if (items.length === 0) {
                alert('No items have been selected.');
                return;
            }

            // Assuming storeid and substoreid are consistent across all items
            const stock_location = document.getElementById('storeid_0').innerText; // Get from the first row
            const substore = document.getElementById('substoreid_0').innerText; // Get from the first row
            const requestid = document.getElementById('requestid_0').innerText; // Get from the first row
            const OGPrequestid = document.getElementById('OGPrequestid_0').innerText; // Get from the first row
            var narative = "OGP request # <b>" + OGPrequestid + "</b>";

            // Send the data to the server
            $.ajax({
                type: "POST",
                url: "igp/api/submitIgpDatas.php",
                data: {
                    igp_type: "l",
                    narative: narative,
                    requestid: requestid,
                    stock_location: stock_location,
                    substore: substore,
                    request_fulfil: "yes",
                    items: items
                },
                success: function(data) {
                    console.log('OGP submitted successfully:', data);

                    // Get the modal body and footer elements
                    const modalBody = document.getElementById('modalBodyContent1');
                    const modalFooter = document.getElementById('modalFooter');

                    // Hide the existing items (table or content) in the modal
                    modalBody.innerHTML = ''; // Clear any existing content

                    // Add a "Print Receipt" button centered in the modal
                    const printButton = document.createElement('button');
                    printButton.textContent = 'Print Receipt';
                    printButton.className = 'btn btn-primary';
                    printButton.style.display = 'block';
                    printButton.style.margin = '0 auto'; // Center the button horizontally
                    printButton.onclick = function() {
                        // Redirect to the PDF generation page
                        window.location.href = `/sahamid/PDFIGP.php?RequestNo=${data}`;
                    };

                    // Add the print button to the modal body
                    modalBody.appendChild(printButton);

                    // Clear the modal footer, as we no longer need other buttons
                    modalFooter.innerHTML = '';

                    // Optionally, you can add a close button to the modal if needed:
                    const closeButton = document.createElement('button');
                    closeButton.textContent = 'Close';
                    closeButton.className = 'btn btn-secondary';
                    closeButton.onclick = function() {
                        const myModal = new bootstrap.Modal(document.getElementById('ogpModal'));
                        myModal.hide();
                    };
                    modalFooter.appendChild(closeButton);
                    items = [];
                },
                error: function(error) {
                    console.error('Error submitting OGP:', error);
                    alert('Failed to submit OGP. Please try again.');
                }
            });

        }



        function fetchData(id, element) {
            // Check if the data is already loaded
            if (element.getAttribute('data-loaded') === 'true') {
                return; // Skip if already loaded
            }

            // Fetch data from the server
            fetch(`notifications/fetch_ogp_related_data.php?id=${id}`)
                .then((response) => response.json())
                .then((data) => {
                    console.log(data);

                    // Check if related_info exists and has items
                    const relatedInfo = data.related_info;
                    const modalBody = document.getElementById('modalBodyContent');

                    if (relatedInfo && relatedInfo.length > 0) {
                        // Populate modal with data for each item in related_info array
                        let tableRows = '';

                        // Loop through each related_info item and create a table row
                        relatedInfo.forEach(item => {
                            tableRows += `
                        <tr>
                            <td class="text-xs" style="width: auto;">${item.stockcode}</td>
                            <td class="text-xs" style="width: auto;">${item.quantity}</td>
                            <td class="text-xs" style="width: auto;">${item.status}</td>
                            <td class="text-xs" style="width: auto;">${item.storeid}</td>
                            <td class="text-xs" style="width: auto;">${item.substore_description || 'N/A'}</td>
                        </tr>
                    `;
                        });

                        // Insert the table rows into the modal body
                        modalBody.innerHTML = `
                    <table class="table table-bordered" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-xs">Stock Code</th>
                                <th class="text-xs">Required Quantity</th>
                                <th class="text-xs">Status</th>
                                <th class="text-xs">Store ID</th>
                                <th class="text-xs">Substore Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows}
                        </tbody>
                    </table>
                `;
                    } else {
                        // Handle case where related_info is not available or empty
                        modalBody.innerHTML = "No Items details available";
                    }

                    // Trigger the modal to show using Bootstrap's modal functionality
                    const myModal = new bootstrap.Modal(document.getElementById('detailsModal'));
                    myModal.show();
                })
                .catch((error) => {
                    console.error('Error fetching data:', error);
                });
        }


        function logout() {
            const confirmation = confirm("Are you sure to logout?");
            if (confirmation) {
                location.href = "/sahamid/Logout.php";
            }
        }
    </script>

</body>

</html>