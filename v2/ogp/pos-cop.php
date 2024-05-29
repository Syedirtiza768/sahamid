<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">
    <link rel="stylesheet" href="tailwindss.css">
    <title>SAHamid ERP</title>
</head>

<body style="background-color: #dddddd">
    <nav style="background-color: #cccce5" class="block w-full max-w-screen-xl px-6 py-1 mx-auto text-white border shadow-md rounded-xl border-white/80 bg-opacity-80 backdrop-blur-2xl backdrop-saturate-200">
        <div class="flex items-center justify-between text-blue-gray-900">
            <a href="#" class="mr-4 block cursor-pointer py-1.5 font-sans text-base font-semibold leading-relaxed tracking-normal text-gray-700  antialiased">
                <b class="text-orange-500">SA Hamid ERP </b></a>
            <h1 class="text-lg font-bold text-gray-700 leading-tight text-center">Outward Gate Pass - OGP</h1>
            <div class="hidden lg:block">
                <ul class="flex flex-col gap-2 my-2 lg:mb-0 lg:mt-0 lg:flex-row lg:items-center lg:gap-6">
                    <li class="block p-1 font-sans text-sm antialiased font-medium leading-normal text-blue-gray-900">
                        <button type="button" class="relative rounded-full p-1 text-black hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">View notifications</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z" />
                                <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z" />
                            </svg>
                        </button>
                    </li>
                    <li class="block p-1 font-sans text-sm antialiased font-medium leading-normal text-blue-gray-900">
                        <button type="button" class="relative rounded-full p-1 text-black hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                            <span class="absolute -inset-1.5"></span>
                            <span class="sr-only">View notifications</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </button>
                    </li>
                </ul>
            </div>
            <button class="relative ml-auto h-6 max-h-[40px] w-6 max-w-[40px] select-none rounded-lg text-center align-middle font-sans text-xs font-medium uppercase  transition-all hover:bg-transparent focus:bg-transparent active:bg-transparent disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none lg:hidden" type="button">
                <span class="absolute transform -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2 text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true" class="w-6 h-6 text-gray-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"></path>
                    </svg>
                </span>
            </button>
        </div>
    </nav>
    <div>
        <form id="signUpForm" class="p-12 w-80 shadow-md rounded-2xl mx-auto border-solid border-2 border-transparent mb-8" style="margin-top: 10px; background-color: #cccce5" action="#!">
            <!-- start step indicators -->
            <div class="form-header flex gap-3 mb-4 text-xs text-center ">
                <span class="stepIndicator flex-1 pb-8 relative">OGP Information</span>
                <span class="stepIndicator flex-1 pb-8 relative">Add Inventory Items</span>
                <span class="stepIndicator flex-1 pb-8 relative">Finalize OGP</span>
            </div>
            <!-- end step indicators -->

            <!-- step one -->
            <div class="flex justify-center">
                <div class="step" style="width:400px">
                    <!-- <p class="text-md text-gray-700 leading-tight text-center mt-4 mb-4">Add Your OGP Informnation</p> -->
                    <div class="mb-6 mt-4">
                        <label class="font-bold text-gray-700 text-sm ml-1">Select OGP Type</label>
                        <select id="ogp_type" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showDiv(this)">
                            <option selected>Select OGP Type</option>
                            <option value="s">Issue to Salesperson</option>
                            <option value="e">Issue to Employee</option>
                            <option value="l">Deleivered to store location</option>
                            <option value="d">External destination</option>
                        </select>
                    </div>
                    <div id="salesperson" style="display:none;" class="m-12">
                        <div class="mb-6">
                            <label class="font-bold text-gray-700 text-sm ml-1">Issued to Sales Person</label>
                            <select id="salesman" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                                <option selected>Select a sales Person</option>
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="FR">France</option>
                                <option value="DE">Germany</option>
                            </select>
                        </div>
                        <div class="mb-6">
                            <label class="font-bold text-gray-700 text-sm ml-1">Salesperson OGP Type</label>
                            <select id="ogp_type" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showOGPDiv(this)">
                                <option selected>Salesperson OGP Type</option>
                                <option value="salescase">Salescase</option>
                                <option value="csv">CSV</option>
                                <option value="crv">CRV</option>
                                <option value="mpo">MPO</option>
                                <option value="cart">CART</option>
                            </select>
                        </div>

                        <div id="salescase_div" style="display:none;">
                            <div class="mb-6">
                                <label class="font-bold text-gray-700 text-sm ml-1">Issue agaist salescase</label>
                                <select id="salescase" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                                    <option selected>Issue Against salescase</option>
                                    <option value="salescase">Select one</option>
                                    <option value="csv">A</option>
                                    <option value="crv">B</option>
                                    <option value="mpo">C</option>
                                </select>
                            </div>
                        </div>
                        <div id="csv_div" style="display:none;">
                            <div class="mb-6">
                                <label class="font-bold text-gray-700 text-sm ml-1">Issue Against CSV</label>
                                <select id="csv" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                                    <option selected>Issue Against CSV</option>
                                    <option value="salescase">Select one</option>
                                    <option value="csv">A</option>
                                    <option value="crv">B</option>
                                    <option value="mpo">C</option>
                                </select>
                            </div>
                        </div>
                        <div id="crv_div" style="display:none;">
                            <div class="mb-6">
                                <label class="font-bold text-gray-700 text-sm ml-1">Issue Against CRV</label>
                                <select id="crv" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                                    <option selected>Issue Against CRV</option>
                                    <option value="salescase">Select one</option>
                                    <option value="csv">A</option>
                                    <option value="crv">B</option>
                                    <option value="mpo">C</option>
                                </select>
                            </div>
                        </div>
                        <div id="mpo_div" style="display:none;">
                            <div class="mb-6">
                                <label class="font-bold text-gray-700 text-sm ml-1">Issue Against MPO</label>
                                <select id="mpo" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                                    <option selected>Issue Against MPO</option>
                                    <option value="salescase">Select one</option>
                                    <option value="A">A</option>
                                    <option value="cv">B</option>
                                    <option value="mpo">C</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div id="employee" style="display:none;" class="m-12">
                        <div class="mb-6">
                            <label class="font-bold text-gray-700 text-sm ml-1">Issued to Employee </label>
                            <select id="employee" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                                <option selected>Select an employee</option>
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="FR">France</option>
                                <option value="DE">Germany</option>
                            </select>
                        </div>
                    </div>
                    <div id="store" style="display:none;" class="m-12">
                        <div class="mb-6">
                            <label class="font-bold text-gray-700 text-sm ml-1">To Stock Location </label>
                            <select id="store" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                                <option selected>Select a location</option>
                                <option value="HO">HO - Head Office</option>
                                <option value="CA">HOPS - Head Office PS</option>
                                <option value="MT">MT - Model Town</option>
                                <option value="MT">MTPS - Model Town PS</option>
                                <option value="DE">OS - Offset</option>
                                <option value="DE">SR - Show Room</option>
                                <option value="DE">SRPS - Show Room PS</option>
                                <option value="DE">VSR - Virtual Store Show Room</option>
                                <option value="WS">WS - Wordk Shop</option>
                            </select>
                        </div>
                    </div>
                    <div id="destination" style="display:none;" class="m-12">
                        <div class="mb-6">
                            <label class="font-bold text-gray-700 text-sm ml-1">External Destination</label>
                            <textarea id="message" rows="4" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" placeholder="Write your destination's detail here..."></textarea>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="font-bold text-gray-700 text-sm ml-1">Substore</label>
                        <select id="countries" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                            <option value="flat store" selected>Flat Store</option>
                            <option value="office store">Office Store</option>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="font-bold text-gray-700 text-sm ml-1">Date</label>
                        <input id="message" type="date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" disabled></input>
                    </div>
                    <div class="mb-6">
                        <label class="font-bold text-gray-700 text-sm ml-1">Narrative</label>
                        <textarea id="message" rows="4" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" placeholder="Write your Narrative here..."></textarea>
                    </div>
                </div>
            </div>

            <!-- step two -->
            <div class="step">
                <div class="flex">
                    <div class="flex-none w-44 ...">
                        <label class="font-bold text-gray-700 text-sm ml-1">
                            In Stock Category</label>
                        <select id="countries" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                            <option selected>All</option>
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <option value="FR">France</option>
                            <option value="DE">Germany</option>
                        </select>
                    </div>
                    <div class="flex-1 w-24 ml-1">
                        <label class="font-bold text-gray-700 text-sm ml-1">
                            Brand</label>
                        <select id="countries" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200">
                            <option selected>All</option>
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <option value="FR">France</option>
                            <option value="DE">Germany</option>
                        </select>
                    </div>
                    <div class="flex-1 w-32 ml-1">
                        <label class="font-bold text-gray-700 text-sm ml-1">
                            Code</label>
                        <input type="text" placeholder="Insert Code Here . . ." class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" oninput="this.className = 'w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200'" />
                    </div>
                </div>
                <div class="flex justify-center">
                    <button type="button" class="w-56 mt-2 mb-2 focus:outline-none border border-gray-300 py-1 px-5 rounded-lg shadow-sm text-center text-gray-700 bg-gray-100 hover:bg-green-500 hover:text-white text-lg" onclick="">Search Now</button>
                </div>

                <div class="overflow-x-auto mb-4 mt-4">
                    <table class="display text-sm text-left text-gray-500" id="example" style="width:100%">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-300">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Code
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    On Hand
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    On Demand
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    On Order
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Available
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Quantity
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-white border-b">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    Rajah Armstrong
                                </th>
                                <td class="px-6 py-4">
                                    rajah.armstrong@yahoo.com
                                </td>
                                <td class="px-6 py-4">
                                    1425
                                </td>
                                <td class="px-6 py-4">
                                    0
                                </td>
                                <td class="px-6 py-4">
                                    45
                                </td>
                                <td class="px-6 py-4">
                                    1416
                                </td>
                                <td class="px-6 py-4">
                                    0
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- step three -->
            <div class="step">
                <div class="mb-6 flex justify-center">
                    <div class="p-10">
                        <div class="w-full rounded overflow-hidden shadow-lg">
                            <div class="flex justify-center">
                                <h5 class="mt-4 text-gray-800 font-bold text-lg justify-center">Detail of Items Requested:</h5>
                            </div>
                            <div class="px-6 py-1 mt-2 flex">
                                <h5 class="text-gray-700 font-bold text-sm">Sales person:</h5>
                                <h5 class="text-gray-700 text-sm ml-6">
                                    Adnan Karim Baksh
                                </h5>
                            </div>
                            <div class="px-6 py-1 flex">
                                <h5 class="text-gray-700 font-bold text-sm">Salescase:</h5>
                                <h5 class="text-gray-700 text-sm ml-6">
                                    MT-2089-2023-12-15--044046
                                </h5>
                            </div>
                            <div class="px-6 py-1 flex">
                                <h5 class="text-gray-700 font-bold text-sm">Date:</h5>
                                <h5 class="text-gray-700 text-sm ml-6">
                                    25/10/2024
                                </h5>
                            </div>
                            <div class="px-6 pt-4 pb-2">
                                <table class="table-fixed">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th> Item Code</th>
                                            <th>Item Description</th>
                                            <th>Quantity Required</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>0</td>
                                            <td class="text-xs text-center">3061TESTdummyITEM</td>
                                            <td class="text-xs text-center">DUMMY ITEM TO CREATE DC FOR SHOW ROOM</td>
                                            <td>24</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- start previous / next buttons -->
            <div class="form-footer flex gap-3">
                <button type="button" id="prevBtn" class="flex-1 focus:outline-none border border-gray-300 py-1 px-5 rounded-lg shadow-sm text-center text-gray-700 bg-white hover:bg-gray-100 text-lg" onclick="nextPrev(-1)">Previous</button>
                <button type="button" id="nextBtn" class="flex-1 border border-transparent focus:outline-none p-3 rounded-md text-center text-white bg-indigo-600 hover:bg-indigo-700 text-lg" onclick="nextPrev(1)">Next</button>
            </div>
            <!-- end previous / next buttons -->
        </form>
    </div>

    <!-- tailwind css -->
    <link rel="stylesheet" href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" />
    <!-- google font -->
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</body>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        // Setup - add a text input to each footer cell
        $('#example tfoot th').each(function() {
            var title = $(this).text();
            $(this).html('<input type="text" placeholder="Search ' + title + '" />');
        });

        // DataTable
        var table = $('#example').DataTable({
            initComplete: function() {
                // Apply the search
                this.api().columns().every(function() {
                    var that = this;

                    $('input', this.footer()).on('keyup change clear', function() {
                        if (that.search() !== this.value) {
                            that
                                .search(this.value)
                                .draw();
                        }
                    });
                });
            }
        });

        $('#example tfoot tr').appendTo('#example thead');
    });

    var currentTab = 0; // Current tab is set to be the first tab (0)
    showTab(currentTab); // Display the current tab

    function showTab(n) {
        // This function will display the specified tab of the form...
        var x = document.getElementsByClassName("step");
        x[n].style.display = "block";
        //... and fix the Previous/Next buttons:
        if (n == 0) {
            document.getElementById("prevBtn").style.display = "none";
        } else {
            document.getElementById("prevBtn").style.display = "inline";
        }
        if (n == (x.length - 1)) {
            document.getElementById("nextBtn").innerHTML = "Submit";
        } else {
            document.getElementById("nextBtn").innerHTML = "Next";
        }
        //... and run a function that will display the correct step indicator:
        fixStepIndicator(n)
    }

    function nextPrev(n) {
        // This function will figure out which tab to display
        var x = document.getElementsByClassName("step");
        // Exit the function if any field in the current tab is invalid:
        if (n == 1 && !validateForm()) return false;
        // Hide the current tab:
        x[currentTab].style.display = "none";
        // Increase or decrease the current tab by 1:
        currentTab = currentTab + n;
        // if you have reached the end of the form...
        if (currentTab >= x.length) {
            // ... the form gets submitted:
            document.getElementById("signUpForm").submit();
            return false;
        }
        // Otherwise, display the correct tab:
        showTab(currentTab);
    }

    function validateForm() {
        // This function deals with validation of the form fields
        var x, y, i, valid = true;
        x = document.getElementsByClassName("step");
        y = x[currentTab].getElementsByTagName("input");
        // A loop that checks every input field in the current tab:
        // for (i = 0; i < y.length; i++) {
        //     // If a field is empty...
        //     if (y[i].value == "") {
        //         // add an "invalid" class to the field:
        //         y[i].className += " invalid";
        //         // and set the current valid status to false
        //         valid = true;
        //     }
        // }
        // // If the valid status is true, mark the step as finished and valid:
        // if (valid) {
        document.getElementsByClassName("stepIndicator")[currentTab].className += " finish";
        // }
        return valid; // return the valid status
    }

    function fixStepIndicator(n) {
        // This function removes the "active" class of all steps...
        var i, x = document.getElementsByClassName("stepIndicator");
        for (i = 0; i < x.length; i++) {
            x[i].className = x[i].className.replace(" active", "");
        }
        //... and adds the "active" class on the current step:
        x[n].className += " active";
    }

    function showDiv(divId) {
        // document.getElementById(divId).style.display = element.value == 1 ? 'block' : 'none';
        var ogp_type = divId.value;
        if (ogp_type == 's') {
            document.getElementById('salesperson').style.display = 'block';
            document.getElementById('employee').style.display = 'none';
            document.getElementById('store').style.display = 'none';
            document.getElementById('destination').style.display = 'none';
        } else if (ogp_type == 'e') {
            document.getElementById('salesperson').style.display = 'none';
            document.getElementById('employee').style.display = 'block';
            document.getElementById('store').style.display = 'none';
            document.getElementById('destination').style.display = 'none';
        } else if (ogp_type == 'l') {
            document.getElementById('salesperson').style.display = 'none';
            document.getElementById('employee').style.display = 'none';
            document.getElementById('store').style.display = 'block';
            document.getElementById('destination').style.display = 'none';
        } else if (ogp_type == 'd') {
            document.getElementById('salesperson').style.display = 'none';
            document.getElementById('employee').style.display = 'none';
            document.getElementById('store').style.display = 'none';
            document.getElementById('destination').style.display = 'block';
        }
    }

    function showOGPDiv(divId) {
        var ogp_salesperson_type = divId.value;
        if (ogp_salesperson_type == 'salescase') {
            document.getElementById('salescase_div').style.display = 'block';
            document.getElementById('csv_div').style.display = 'none';
            document.getElementById('crv_div').style.display = 'none';
            document.getElementById('mpo_div').style.display = 'none';
        } else if (ogp_salesperson_type == 'csv') {
            document.getElementById('salescase_div').style.display = 'none';
            document.getElementById('csv_div').style.display = 'block';
            document.getElementById('crv_div').style.display = 'none';
            document.getElementById('mpo_div').style.display = 'none';
        } else if (ogp_salesperson_type == 'crv') {
            document.getElementById('salescase_div').style.display = 'none';
            document.getElementById('csv_div').style.display = 'none';
            document.getElementById('crv_div').style.display = 'block';
            document.getElementById('mpo_div').style.display = 'none';
        } else if (ogp_salesperson_type == 'mpo') {
            document.getElementById('salescase_div').style.display = 'none';
            document.getElementById('csv_div').style.display = 'none';
            document.getElementById('crv_div').style.display = 'none';
            document.getElementById('mpo_div').style.display = 'block';
        } else if (ogp_salesperson_type == 'cart') {
            document.getElementById('salescase_div').style.display = 'none';
            document.getElementById('csv_div').style.display = 'none';
            document.getElementById('crv_div').style.display = 'none';
            document.getElementById('mpo_div').style.display = 'none';
        }
    }
</script>

</html>