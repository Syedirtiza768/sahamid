
var items = [];
$(document).ready(function () {
    $('#employee').select2();
    $('#salescases').select2();
    $('#csv').select2();
    $('#crv').select2();
    $('#mpo').select2();
    $('#StockCat').select2();
    $('#brand').select2();
});
var globelVariable = 0;
var nxtBtnVariable = 0;
// search item function
function itemSearch() {
    var subStore = $('#subStore').val();
    var brand = $('#brand').val();
    var StockCat = $('#StockCat').val();
    var StockCode = $('#StockCode').val();
    document.getElementById("loader").style.display = "block";
    $.ajax({
        type: "POST",
        url: "ogp/api/itemSearchData.php",
        data: {
            subStore: subStore,
            brand: brand,
            StockCat: StockCat,
            StockCode: StockCode
        },
        success: function (response) {
            console.log(response);
            try {
                var jsonData = JSON.parse(response);
                // Clear the table by resetting it with an empty array
                var table = $('#example').DataTable();
                table.clear().draw(); // Clears the table completely

                // Populate the table with new data
                populateTable(jsonData);

                // Now you can work with the parsed JSON data
            } catch (error) {
                var table = $('#example').DataTable();
                table.clear().draw(); // Clears the table completely
                console.error("Error parsing JSON:", error);
            }
            document.getElementById("loader").style.display = "none";
        },
    });
}

function populateTable(data) {
    var table = $('#example').DataTable();

    // Clear existing data without redrawing
    table.clear().draw(false);

    // Create an array to hold the rows
    var rows = [];

    // Loop through the data and prepare each row
    for (var i = 0; i < data.length; i++) {
        var rowData = [
            '<td class="text-blue-800 underline"><a href="../SelectProduct.php?Select=' + data[i].Code + '" target="_blank">' + data[i].Code + '</a></td>',
            '<td>' + data[i].Description + '</td>',
            '<td>' + data[i].QOH + '</td>',
            '<td>' + data[i].OnDemand + '</td>',
            '<td>' + data[i].OnOrder + '</td>',
            '<td>' + data[i].Available + '</td>',
            '<td><input class="w-16 h-6 text-center" id="' + data[i].Code + '"></input></td>',
            '<td><button type="button" id="btn' + data[i].Code + '" class="bg-green-500 w-16 h-6 border rounded-md" onclick="ItemAdd(\'' + data[i].Code + '\', \'' + data[i].Description + '\', \'' + data[i].QOH + '\');"><i class="fa-solid fa-check text-white"></i></button></td>'
        ];

        rows.push(rowData);
    }

    // Add the rows in bulk
    table.rows.add(rows).draw();
}


function ItemAdd(code, description, qoh) {
    var Itemcode = code;
    var Itemdescription = description;
    var Itemquantity = $('#' + code).val();
    var qoh = parseInt(qoh);
    if (Itemquantity == "" || Itemquantity > qoh) {
        alert("EMPTY OR GIVEN QUANTITY IS GREATER THEN QUANTITY ON HAND");
    }
    else {
        items.push({ Code: Itemcode, description: Itemdescription, quantity: Itemquantity, totalQuantity: qoh });
        showItems(items);
        $('#confirmationPopup').fadeIn();
        document.getElementById('popupMessage').textContent = 'Item Quantity Saved successfuly 😊';
        // Hide the confirmation popup after 3 seconds (3000 milliseconds)
        setTimeout(function () {
            $('#confirmationPopup').fadeOut();
        }, 3000);
        globelVariable = 1;
        document.getElementById("nextBtn").style.display = "inline";
        document.getElementById("btn" + code).style.display = "none";
    }
}

function showItems(itemslist) {
    var tbody = document.getElementById('example1').getElementsByTagName('tbody')[0];
    tbody.innerHTML = ''; // Clear table body

    for (var i = 0; i < itemslist.length; i++) {
        var rowData = '<td class="border border-gray-400 text-blue-800 underline"><a href="../SelectProduct.php?Select=' + itemslist[i].Code + '" target="_blank">' + itemslist[i].Code + '</a></td>';
        var rowData2 = '<td class="border border-gray-400">' + itemslist[i].description + '</td>';
        var rowData3 = '<td class="border border-gray-400"><div class="flex justify-center"><h5 id="Quantity' + itemslist[i].Code + '">' + itemslist[i].quantity + '</h5> <input class="w-16 h-6 text-center" id="newQuantity' + itemslist[i].Code + '" style="display:none"></input></div></td>';
        var rowData4 = '<td class="border border-gray-400"><div class="flex justify-center"><button type="button" style="display:none" id="saveBtn' + itemslist[i].Code + '" class="bg-green-800 w-10 h-6 border rounded-md" onclick="saveItem(\'' + itemslist[i].Code + '\');"><i class="fa-solid fas fa-save text-white"></i></button><button type="button" id="editBtn' + itemslist[i].Code + '" class="bg-orange-500 w-10 h-6 border rounded-md" onclick="editItem(\'' + itemslist[i].Code + '\');"><i class="fa-solid fas fa-edit text-white"></i></button><button type="button" id="deleteBtn' + itemslist[i].Code + '" class="bg-red-800 w-10 h-6 border rounded-md" onclick="deleteItem(\'' + itemslist[i].Code + '\');"><i class="fa-solid fa-trash text-white"></i></button></div></td>';

        var row = document.createElement('tr');
        row.innerHTML = rowData + rowData2 + rowData3 + rowData4;
        tbody.appendChild(row);
    }
}

function editItem(code) {
    for (var i = 0; i < items.length; i++) {
        if (items[i].Code == code) {
            document.getElementById("editBtn" + items[i].Code).style.display = "none";
            document.getElementById("Quantity" + items[i].Code).style.display = "none";
            document.getElementById("newQuantity" + items[i].Code).style.display = "block";
            document.getElementById("saveBtn" + items[i].Code).style.display = "block";
        }
    }
}

function saveItem(code) {
    var newQuantity = $('#newQuantity' + code).val();
    for (var i = 0; i < items.length; i++) {
        if (items[i].Code == code) {
            if (items[i].totalQuantity < newQuantity || newQuantity == "") {
                alert("EMPTY OR GIVEN QUANTITY IS GREATER.  PLEASE SELECT IN A RANGE OF " + items[i].totalQuantity);
            }
            else {
                items[i].quantity = newQuantity;
                console.log(items[i]);
                document.getElementById("editBtn" + items[i].Code).style.display = "block";
                document.getElementById("Quantity" + items[i].Code).style.display = "block";
                document.getElementById("newQuantity" + items[i].Code).style.display = "none";
                document.getElementById("saveBtn" + items[i].Code).style.display = "none";

                var tbody = document.getElementById('example1').getElementsByTagName('tbody')[0];
                tbody.innerHTML = ''; // Clear table body
                for (var i = 0; i < items.length; i++) {
                    var rowData = '<td class="text-blue-800 underline"><a href = "../SelectProduct.php?Select=' + items[i].Code + '" target = "_blank" > ' + items[i].Code + '</a></td>';
                    var rowData2 = '<td>' + items[i].description + '</td>';
                    var rowData3 = '<td><div class="flex justify-center"><h5 id="Quantity' + items[i].Code + '">' + items[i].quantity + '</h5> <input class="w-16 h-6 text-center" id="newQuantity' + items[i].Code + '" style="display:none"> </input></div> </td>';
                    var rowData4 = '<td><div class="flex justify-center"><button type="button" style="display:none" id="saveBtn' + items[i].Code + '" class="bg-green-800 w-10 h-6 border rounded-md" onclick = "saveItem(\'' + items[i].Code + '\');" > <i class="fa-solid fas fa-save text-white"></i> </button> <button type="button" id="editBtn' + items[i].Code + '" class="bg-orange-500 w-10 h-6 border rounded-md" onclick = "editItem(\'' + items[i].Code + '\');" > <i class="fa-solid fas fa-edit text-white"></i> </button><button type="button" id="deleteBtn' + items[i].Code + '" class="bg-red-800 w-10 h-6 border rounded-md" onclick = "deleteItem(\'' + items[i].Code + '\');" > <i class="fa-solid fa-trash text-white"></i> </button></div></td>';

                    var row = document.createElement('tr');
                    row.innerHTML = rowData + rowData2 + rowData3 + rowData4;
                    tbody.appendChild(row);

                    // table.row.add([rowData, rowData2, rowData3, rowData4]).draw();
                    // document.getElementById(items[i].Code).style.display = "none";
                    // document.getElementById("btn" + items[i].Code).style.display = "none";

                    $('#confirmationPopup').fadeIn();
                    document.getElementById('popupMessage').textContent = 'Edited Quantity Saved successfuly 😊';
                    // Hide the confirmation popup after 3 seconds (3000 milliseconds)
                    setTimeout(function () {
                        $('#confirmationPopup').fadeOut();
                    }, 3000);
                }
            }
        }
    }
}


function deleteItem(itemCode) {
    for (var i = 0; i < items.length; i++) {
        if (items[i].Code === itemCode) {
            if (confirm('Are you sure?')) {
                items.splice(i, 1); // Delete the array at index i
                $('#confirmationPopup').fadeIn();
                document.getElementById('popupMessage').textContent = 'Item deleted successfuly 🗑️';
                // Hide the confirmation popup after 3 seconds (3000 milliseconds)
                setTimeout(function () {
                    $('#confirmationPopup').fadeOut();
                }, 3000);

                document.getElementById("btn" + itemCode).style.display = "inline";

                break;
            }
        }
    }
    var tbody = document.getElementById('example1').getElementsByTagName('tbody')[0];
    tbody.innerHTML = ''; // Clear table body
    for (var i = 0; i < items.length; i++) {
        var rowData = '<td class="border border-gray-400 text-blue-800 underline"><a href="../SelectProduct.php?Select=' + items[i].Code + '" target="_blank">' + items[i].Code + '</a></td>';
        var rowData2 = '<td class="border border-gray-400">' + items[i].description + '</td>';
        var rowData3 = '<td class="border border-gray-400"><div class="flex justify-center"><h5 id="Quantity' + items[i].Code + '">' + items[i].quantity + '</h5> <input class="w-16 h-6 text-center" id="newQuantity' + items[i].Code + '" style="display:none"></input></div></td>';
        var rowData4 = '<td class="border border-gray-400"><div class="flex justify-center"><button type="button" style="display:none" id="saveBtn' + items[i].Code + '" class="bg-green-800 w-10 h-6 border rounded-md" onclick="saveItem(\'' + items[i].Code + '\');"><i class="fa-solid fas fa-save text-white"></i></button><button type="button" id="editBtn' + items[i].Code + '" class="bg-orange-500 w-10 h-6 border rounded-md" onclick="editItem(\'' + items[i].Code + '\');"><i class="fa-solid fas fa-edit text-white"></i></button><button type="button" id="deleteBtn' + items[i].Code + '" class="bg-red-800 w-10 h-6 border rounded-md" onclick="deleteItem(\'' + items[i].Code + '\');"><i class="fa-solid fa-trash text-white"></i></button></div></td>';

        var row = document.createElement('tr');
        row.innerHTML = rowData + rowData2 + rowData3 + rowData4;
        tbody.appendChild(row);
    }

}



var currentTab = 0; // Current tab is set to be the first tab (0)
showTab(currentTab); // Display the current tab

function showTab(n) {
    // This function will display the specified tab of the form...
    var x = document.getElementsByClassName("step");
    x[n].style.display = "block";
    //... and fix the Previous/Next buttons:
    document.getElementById("newOGPBtn").style.display = "none";
    if (n == 0) {
        document.getElementById("nextBtn").style.display = "inline";
        document.getElementById("prevBtn").style.display = "none";
    } else {
        document.getElementById("prevBtn").style.display = "inline";
    }
    if (n == (x.length - 2)) {
        document.getElementById("nextBtn").innerHTML = "Submit";
    } else {
        document.getElementById("nextBtn").innerHTML = "Next";
    }

    if (n == 1) {
        if (items.length > 0) {
            document.getElementById("nextBtn").style.display = "inline";
        }
        else {
            document.getElementById("nextBtn").style.display = "none";
        }
    }

    if (n == 2) {
        document.getElementById("nextBtn").style.display = "inline";
    }

    if (n == 3) {
        document.getElementById("nextBtn").style.display = "none";
        document.getElementById("prevBtn").style.display = "none";

        document.getElementById("newOGPBtn").style.display = "inline";
    }
    //... and run a function that will display the correct step indicator:
    fixStepIndicator(n)
}

function nextPrev(n) {

    var destination = $('.destination').val();
    if (destination != "") {
        document.getElementById('salescaserefDiv').style.display = 'none';
        document.getElementById('csvDiv').style.display = 'none';
        document.getElementById('crvDiv').style.display = 'none';
        document.getElementById('mpoDiv').style.display = 'none';
        document.getElementById('EmployeeDiv').style.display = 'none';
        document.getElementById('storeDiv').style.display = 'none';
        document.getElementById('destinationDiv').style.display = 'block';
        $('.showDestination').html("<b>Externel Destination:     </b>" + destination)
    }

    if ($('#subStore').val() != window.subStore) {
        var table = $('#example').DataTable();
        $('#example tbody').empty();
        table.clear().draw(); // Clears the table completely

        var tbody = document.getElementById('example1').getElementsByTagName('tbody')[0];
        tbody.innerHTML = ''; // Clear table body
        items = [];
    }

    window.subStore = $('#subStore').val();

    Substore = $('#subStore').val();
    if (Substore == '4') {
        Substore = 'Flat Store'
    }
    if (Substore == '5') {
        Substore = 'Office Store'
    }
    if (Substore == '6') {
        Substore = 'Umar Market Store'
    }
    if (Substore == '7') {
        Substore = 'Teer Andaz Market Store'
    }
    if (Substore == '8') {
        Substore = 'Khawaja Market Store'
    }
    if (Substore == '9') {
        Substore = 'Show Room'
    }
    if (Substore == '10') {
        Substore = 'Main Store Head Office'
    }
    if (Substore == '11') {
        Substore = 'Virtual Store Show Room HO'
    }
    if (Substore == '12') {
        Substore = 'Work Shop General Store'
    }
    if (Substore == '13') {
        Substore = 'Umar Market Store PS'
    }
    if (Substore == '14') {
        Substore = 'Teer Andaz Market Store PS'
    }
    if (Substore == '15') {
        Substore = 'Khawaja Market Store PS'
    }
    if (Substore == '16') {
        Substore = 'Main Store Head Office PS'
    }
    if (Substore == '17') {
        Substore = 'Flat Store PS'
    }
    if (Substore == '18') {
        Substore = 'Office Store PS'
    }
    if (Substore == '19') {
        Substore = 'Show Room PS'
    }
    if (Substore == '20') {
        Substore = 'OffsetS'
    }

    document.getElementById('SubstoreDiv').style.display = 'block';
    $('.subStore').html("<b>Sub Store:     </b>" + Substore)

    // This function will figure out which tab to display
    var x = document.getElementsByClassName("step");
    // Exit the function if any field in the current tab is invalid:
    if (n == 1 && !validateForm()) return false;
    // Hide the current tab:
    x[currentTab].style.display = "none";
    if (currentTab == '0') {

        var subStore = $('#subStore').val();
        if (subStore != "") {

            var ogp_type = $('#ogp_type').val();
            if (ogp_type != "") {
                if (ogp_type == 's') {
                    var salesman = $('#salesman').val();
                    var ogp_salesperson_type = $('#ogp_salesperson_type').val();
                    if (salesman !== "" && ogp_salesperson_type !== "" && ogp_salesperson_type !== "Salesperson OGP Type") {
                        currentTab = currentTab + n;
                    } else {
                        alert("Please select sales person related all information first.");
                    }
                }
                else if (ogp_type == 'e') {
                    var employee = $('#employee').val();
                    if (employee !== "") {
                        currentTab = currentTab + n;
                    } else {
                        alert("Please select Employee related all information first.");
                    }
                }
                else if (ogp_type == 'l') {
                    var stock_location = $('#stock_location').val();
                    if (stock_location !== "") {
                        currentTab = currentTab + n;
                    } else {
                        alert("Please select Stock Location related all information first.");
                    }
                }
                else if (ogp_type == 'd') {
                    var desti = $('#desti').val();
                    if (desti !== "") {
                        currentTab = currentTab + n;
                    } else {
                        alert("Please Add External Destination Information First.");
                    }
                }
            }
            else {
                alert("Please select All values first");
            }

        }
        else {
            alert("Please select sub store and all value first.");
        }
    }
    else {
        // Increase or decrease the current tab by 1:
        currentTab = currentTab + n;
    }

    // if you have reached the end of the form...
    if (currentTab >= x.length - 1) {

        // Check if tbody is empty
        if (items == "") {
            currentTab = currentTab - n;
            alert('Please Add Items to proceed . .');
        } else {
            submitForm();
        }
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
        document.getElementById('employee_div').style.display = 'none';
        document.getElementById('store').style.display = 'none';
        document.getElementById('destination').style.display = 'none';
    } else if (ogp_type == 'e') {
        document.getElementById('salesperson').style.display = 'none';
        document.getElementById('employee_div').style.display = 'block';
        document.getElementById('store').style.display = 'none';
        document.getElementById('destination').style.display = 'none';
    } else if (ogp_type == 'l') {
        document.getElementById('salesperson').style.display = 'none';
        document.getElementById('employee_div').style.display = 'none';
        document.getElementById('store').style.display = 'block';
        document.getElementById('destination').style.display = 'none';
    } else if (ogp_type == 'd') {
        document.getElementById('salesperson').style.display = 'none';
        document.getElementById('employee_div').style.display = 'none';
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

function showSalescaseDiv(name) {
    var salescase = name.value;
    document.getElementById('salescaserefDiv').style.display = 'block';
    document.getElementById('csvDiv').style.display = 'none';
    document.getElementById('crvDiv').style.display = 'none';
    document.getElementById('mpoDiv').style.display = 'none';
    document.getElementById('EmployeeDiv').style.display = 'none';
    document.getElementById('storeDiv').style.display = 'none';
    document.getElementById('destinationDiv').style.display = 'none';
    $('.salescaseref').html("<b>Salescase Reference:     </b>" + salescase);
    document.getElementById('salescaseref').value = salescase;
}

function showcsvDiv(name) {
    var salescase = name.value;
    document.getElementById('salescaserefDiv').style.display = 'none';
    document.getElementById('csvDiv').style.display = 'block';
    document.getElementById('crvDiv').style.display = 'none';
    document.getElementById('mpoDiv').style.display = 'none';
    document.getElementById('EmployeeDiv').style.display = 'none';
    document.getElementById('storeDiv').style.display = 'none';
    document.getElementById('destinationDiv').style.display = 'none';
    $('.csv').html("<b>CSV Refrence:     </b>" + salescase);
    document.getElementById('csvref').value = salescase;
}

function showcrvDiv(name) {
    var salescase = name.value;
    document.getElementById('salescaserefDiv').style.display = 'none';
    document.getElementById('csvDiv').style.display = 'none';
    document.getElementById('crvDiv').style.display = 'block';
    document.getElementById('mpoDiv').style.display = 'none';
    document.getElementById('EmployeeDiv').style.display = 'none';
    document.getElementById('storeDiv').style.display = 'none';
    document.getElementById('destinationDiv').style.display = 'none';
    $('.crv').html("<b>CRV Refrence:     </b>" + salescase);
    document.getElementById('crveref').value = salescase;
}

function showmpoDiv(name) {
    var salescase = name.value;
    document.getElementById('salescaserefDiv').style.display = 'none';
    document.getElementById('csvDiv').style.display = 'none';
    document.getElementById('crvDiv').style.display = 'none';
    document.getElementById('mpoDiv').style.display = 'block';
    document.getElementById('EmployeeDiv').style.display = 'none';
    document.getElementById('storeDiv').style.display = 'none';
    document.getElementById('destinationDiv').style.display = 'none';
    $('.mpo').html("<b>MPO Refrence:     </b>" + salescase);
    document.getElementById('mporef').value = salescase;
}

function showmemployeeDiv(name) {
    var employee = name.value;
    document.getElementById('salescaserefDiv').style.display = 'none';
    document.getElementById('csvDiv').style.display = 'none';
    document.getElementById('crvDiv').style.display = 'none';
    document.getElementById('mpoDiv').style.display = 'none';
    document.getElementById('EmployeeDiv').style.display = 'block';
    document.getElementById('storeDiv').style.display = 'none';
    document.getElementById('destinationDiv').style.display = 'none';
    $('.employee').html("<b>Employee:     </b>" + employee)
}

function showStoreDiv(name) {
    var store = name.value;
    document.getElementById('salescaserefDiv').style.display = 'none';
    document.getElementById('csvDiv').style.display = 'none';
    document.getElementById('crvDiv').style.display = 'none';
    document.getElementById('mpoDiv').style.display = 'none';
    document.getElementById('EmployeeDiv').style.display = 'none';
    document.getElementById('storeDiv').style.display = 'block';
    document.getElementById('destinationDiv').style.display = 'none';
    $('.store').html("<b>Store:     </b>" + store)
}

function ogpSaleman(name) {
    var salesman = name.value;
    document.getElementById('salesmanDiv').style.display = 'block';
    $('.salesperson').html("<b>Sales Person:     </b>" + salesman);
    // if salesperson name changes
    $('#ogp_salesperson_type')
        .find('option')
        .remove()
        .end()
        .append('<option selected>Salesperson OGP Type </option><option value="salescase">Salescase</option><option value="csv">CSV</option><option value="crv">CRV</option><option value="mpo">MPO</option>.<option value="cart">CART</option>');
    document.getElementById('salescase_div').style.display = 'none';
    document.getElementById('csv_div').style.display = 'none';
    document.getElementById('crv_div').style.display = 'none';
    document.getElementById('mpo_div').style.display = 'none';

    $.ajax({
        type: "POST",
        url: "ogp/api/salesmanData.php",
        data: {
            salesmans: salesman
        },
        success: function (data) {
            var dataList = JSON.parse(data);

            $('#salescases')
                .find('option')
                .remove()
                .end()
                .append('<option selected>select one salescase </option>');
            for (var i in dataList) {
                $('#salescases').append(new Option(dataList[i], dataList[i]));
            }
            $('#salescases').select2();
        }
    });

    $.ajax({
        type: "POST",
        url: "ogp/api/CSVData.php",
        data: {
            salesmans: salesman
        },
        success: function (data) {
            var dataList = JSON.parse(data);
            $('#csv')
                .find('option')
                .remove()
                .end()
                .append('<option selected>select one csv </option>');
            for (var i in dataList) {
                $('#csv').append(new Option(dataList[i], dataList[i]));
            }
        }
    });

    $.ajax({
        type: "POST",
        url: "ogp/api/CRVData.php",
        data: {
            salesmans: salesman
        },
        success: function (data) {
            var dataList = JSON.parse(data);

            $('#crv')
                .find('option')
                .remove()
                .end()
                .append('<option selected>select one crv </option>');
            for (var i in dataList) {
                $('#crv').append(new Option(dataList[i], dataList[i]));
            }
        }
    });

    $.ajax({
        type: "POST",
        url: "ogp/api/MPOData.php",
        data: {
            salesmans: salesman
        },
        success: function (data) {
            var dataList = JSON.parse(data);
            $('#mpo')
                .find('option')
                .remove()
                .end()
                .append('<option selected>select one mpo </option>');
            for (var i in dataList) {
                $('#mpo').append(new Option(dataList[i], dataList[i]));
            }
        }
    });
}

function submitForm() {
    var ogp_type = $('#ogp_type').val();
    var salesperson = $('#salesman').val();
    var salesperson_ogp_type = $('#ogp_salesperson_type').val();
    var salescase = $('#salescaseref').val();
    var csv = $('#csvref').val();
    var crv = $('#crvref').val();
    var mpo = $('#mporef').val();
    var employee = $('#employee').val();
    var stock_location = $('#stock_location').val();
    var destination = $('#desti').val();
    var substore = $('#subStore').val();
    var narative = $('#narative').val();
    var date = $('#date').val();

    $.ajax({
        type: "POST",
        url: "ogp/api/submitOgpDatas.php",
        data: {
            ogp_type: ogp_type,
            salesperson: salesperson,
            date: date,
            salesperson_ogp_type: salesperson_ogp_type,
            salescase: salescase,
            csv: csv,
            crv: crv,
            mpo: mpo,
            employee: employee,
            stock_location: stock_location,
            destination: destination,
            substore: substore,
            narative: narative,
            items: items
        },
        success: function (data) {
            console.log(data);
            var PrintOGP = document.getElementById("PrintOGP");
            PrintOGP.href = "/sahamid/PDFOGP.php?RequestNo=" + data;
        }
    });
}

function logout() {
    var confirmation = confirm("Are you sure to logout?");
    if (confirmation) {
        location.href = "/sahamid/Logout.php";
    }
}