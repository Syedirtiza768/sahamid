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
            try {
                var jsonData = JSON.parse(response);
                populateTable(jsonData);

                // Now you can work with the parsed JSON data
            } catch (error) {
                console.error("Error parsing JSON:", error);
            }
            document.getElementById("loader").style.display = "none";
        },
    });
}

function populateTable(data) {
    $('#example tbody').empty();
    var table = $('#example').DataTable();
    table.clear().draw();
    for (var i = 0; i < data.length; i++) {
        // var rowData = '<tr class="text-gray-800"><td class="text-blue-800 underline"><a href = "SelectProduct.php?Select=' + data[i].Code + '" target = "_blank" > ' + data[i].Code + '</a></td><td>' + data[i].Description + '</td><td>' + data[i].QOH + '</td><td>' + data[i].OnDemand + '</td><td>' + data[i].OnOrder + '</td><td>' + data[i].Available + '</td></tr>';
        var rowData = '<td class="text-blue-800 underline"><a href = "SelectProduct.php?Select=' + data[i].Code + '" target = "_blank" > ' + data[i].Code + '</a></td>';
        var rowData2 = '<td>' + data[i].Description + '</td>';
        var rowData3 = '<td>' + data[i].QOH + '</td>';
        var rowData4 = '<td>' + data[i].OnDemand + '</td>';
        var rowData5 = '<td>' + data[i].OnOrder + '</td>';
        var rowData6 = '<td>' + data[i].QOH + '</td>';
        var rowData7 = '<td> <input class="w-16 h-6 text-center" id="' + data[i].Code + '"> </input> </td>';
        var rowData8 = '<td> <button type="button" id="btn' + data[i].Code + '" class="bg-green-500 w-16 h-6 border rounded-md" onclick = "ItemAdd((\'' + data[i].Code + '\') , (\'' + data[i].Description + '\'), (\'' + data[i].QOH + '\'));" > <i class="fa-solid fa-check text-white"></i> </button></td>';
        table.row.add([rowData, rowData2, rowData3, rowData4, rowData5, rowData6, rowData7, rowData8]).draw();
    }
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
    }
}

function showItems(itemslist) {
    $('#example1 tbody').empty();
    var table = $('#example1').DataTable();
    table.clear().draw();
    for (var i = 0; i < itemslist.length; i++) {
        var rowData = '<td class="text-blue-800 underline"><a href = "SelectProduct.php?Select=' + itemslist[i].Code + '" target = "_blank" > ' + itemslist[i].Code + '</a></td>';
        var rowData2 = '<td>' + itemslist[i].description + '</td>';
        var rowData3 = '<td><div class="flex justify-center"><h5 id="Quantity' + itemslist[i].Code + '">' + itemslist[i].quantity + '</h5> <input class="w-16 h-6 text-center" id="newQuantity' + itemslist[i].Code + '" style="display:none"> </input></div> </td>';
        var rowData4 = '<td><div class="flex justify-center"><button type="button" style="display:none" id="saveBtn' + itemslist[i].Code + '" class="bg-green-800 w-10 h-6 border rounded-md" onclick = "saveItem(\'' + itemslist[i].Code + '\');" > <i class="fa-solid fas fa-save text-white"></i> </button> <button type="button" id="editBtn' + itemslist[i].Code + '" class="bg-orange-500 w-10 h-6 border rounded-md" onclick = "editItem(\'' + itemslist[i].Code + '\');" > <i class="fa-solid fas fa-edit text-white"></i> </button><button type="button" id="deleteBtn' + itemslist[i].Code + '" class="bg-red-800 w-10 h-6 border rounded-md" onclick = "deleteItem(\'' + itemslist[i].Code + '\');" > <i class="fa-solid fa-trash text-white"></i> </button></div></td>';
        table.row.add([rowData, rowData2, rowData3, rowData4]).draw();
        if (document.getElementById(itemslist[i].Code)) {
            document.getElementById(itemslist[i].Code).style.display = "none";
            document.getElementById("btn" + itemslist[i].Code).style.display = "none";
        }
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
                console.log(items[i]);
                items[i].quantity = newQuantity;
                console.log(items[i]);
                document.getElementById("editBtn" + items[i].Code).style.display = "block";
                document.getElementById("Quantity" + items[i].Code).style.display = "block";
                document.getElementById("newQuantity" + items[i].Code).style.display = "none";
                document.getElementById("saveBtn" + items[i].Code).style.display = "none";

                $('#example1 tbody').empty();
                var table = $('#example1').DataTable();
                table.clear().draw();
                for (var i = 0; i < items.length; i++) {
                    var rowData = '<td class="text-blue-800 underline"><a href = "SelectProduct.php?Select=' + items[i].Code + '" target = "_blank" > ' + items[i].Code + '</a></td>';
                    var rowData2 = '<td>' + items[i].description + '</td>';
                    var rowData3 = '<td><div class="flex justify-center"><h5 id="Quantity' + items[i].Code + '">' + items[i].quantity + '</h5> <input class="w-16 h-6 text-center" id="newQuantity' + items[i].Code + '" style="display:none"> </input></div> </td>';
                    var rowData4 = '<td><div class="flex justify-center"><button type="button" style="display:none" id="saveBtn' + items[i].Code + '" class="bg-green-800 w-10 h-6 border rounded-md" onclick = "saveItem(\'' + items[i].Code + '\');" > <i class="fa-solid fas fa-save text-white"></i> </button> <button type="button" id="editBtn' + items[i].Code + '" class="bg-orange-500 w-10 h-6 border rounded-md" onclick = "editItem(\'' + items[i].Code + '\');" > <i class="fa-solid fas fa-edit text-white"></i> </button><button type="button" id="deleteBtn' + items[i].Code + '" class="bg-red-800 w-10 h-6 border rounded-md" onclick = "deleteItem(\'' + items[i].Code + '\');" > <i class="fa-solid fa-trash text-white"></i> </button></div></td>';
                    table.row.add([rowData, rowData2, rowData3, rowData4]).draw();
                    document.getElementById(items[i].Code).style.display = "none";
                    document.getElementById("btn" + items[i].Code).style.display = "none";

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
            if (confirm('Are you sure?')) { // Check if the array meets the condition
                // document.getElementById(items[i].Code).style.display = "block";
                // document.getElementById("btn" + items[i].Code).style.display = "block";
                items.splice(i, 1); // Delete the array at index i
                $('#confirmationPopup').fadeIn();
                document.getElementById('popupMessage').textContent = 'Item deleted successfuly 🗑️';
                // Hide the confirmation popup after 3 seconds (3000 milliseconds)
                setTimeout(function () {
                    $('#confirmationPopup').fadeOut();
                }, 3000);
                break; // Break the loop once the array is deleted

            }
        }
    }
    $('#example1 tbody').empty();
    var table = $('#example1').DataTable();
    table.clear().draw();
    for (var i = 0; i < items.length; i++) {
        var rowData = '<td class="text-blue-800 underline"><a href = "SelectProduct.php?Select=' + items[i].Code + '" target = "_blank" > ' + items[i].Code + '</a></td>';
        var rowData2 = '<td>' + items[i].description + '</td>';
        var rowData3 = '<td>' + items[i].quantity + '</td>';
        var rowData4 = '<td><button type="button" id="deleteBtn' + items[i].Code + '" class="bg-red-800 w-16 h-6 border rounded-md" onclick = "deleteItem(\'' + items[i].Code + '\');" > <i class="fa-solid fa-trash text-white"></i> </button></td>';
        table.row.add([rowData, rowData2, rowData3, rowData4]).draw();
        document.getElementById(items[i].Code).style.display = "none";
        document.getElementById("btn" + items[i].Code).style.display = "none";
    }
}



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
    if (n == (x.length - 2)) {
        document.getElementById("nextBtn").innerHTML = "Submit";
    } else {
        document.getElementById("nextBtn").innerHTML = "Next";
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

    Substore = $('#subStore').val();
    if (Substore == '5') {
        Substore = 'Office Store'
    }
    if (Substore == '4') {
        Substore = 'Flat Store'
    }
    document.getElementById('SubstoreDiv').style.display = 'block';
    $('.subStore').html("<b>Sub Store:     </b>" + Substore)

    // This function will figure out which tab to display
    var x = document.getElementsByClassName("step");
    // Exit the function if any field in the current tab is invalid:
    if (n == 1 && !validateForm()) return false;
    // Hide the current tab:
    x[currentTab].style.display = "none";
    // Increase or decrease the current tab by 1:
    currentTab = currentTab + n;
    // if you have reached the end of the form...
    if (currentTab >= x.length - 1) {
        submitForm();
        // ... the form gets submitted:
        // document.getElementById("signUpForm").submit();
        // return false;
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
            items:items
        },
        success: function (data) {
            console.log(data);
            
        }
    });
}

function logout() {
    var confirmation = confirm("Are you sure to logout?");
    if (confirmation) {
        location.href = "/sahamid/Logout.php";
    }
}