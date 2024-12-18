<?php include_once("../v2/config.php");
include('../v2/config1.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="//cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min.js" defer></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
  <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
  <link rel="stylesheet" href="ogp/tailwindOgp.css">
  <title>SAHamid ERP</title>
</head>

<body style="background-color: #dddddd">
  <nav style="background-color: #cccce5" class="block w-full max-w-screen-xl px-6 py-1 mx-auto text-white border shadow-md rounded-xl border-white/80 bg-opacity-80 backdrop-blur-2xl backdrop-saturate-200">
    <div class="flex items-center justify-between text-blue-gray-900">
      <a href="#" class="mr-4 block cursor-pointer py-1.5 font-sans text-base font-semibold leading-relaxed tracking-normal text-gray-700  antialiased">
        <b class="text-orange-500">SA Hamid ERP </b></a>
      <h1 class="text-lg font-bold text-gray-700 leading-tight text-center"><b>Inward Gate Pass - IGP</b></h1>
      <div class="hidden lg:block">
        <ul class="flex flex-col gap-2 my-2 lg:mb-0 lg:mt-0 lg:flex-row lg:items-center lg:gap-6">
          <li class="block p-1 font-sans text-sm antialiased font-medium leading-normal text-blue-gray-900">
            <button type="button" onClick="document.location.href='/sahamid/'" class="relative rounded-full p-1 text-orange-600 hover:text-black focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
              <span class="absolute -inset-1.5"></span>
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z" />
                <path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z" />
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
        <span class="stepIndicator flex-1 pb-8 relative">IGP Information</span>
        <span class="stepIndicator flex-1 pb-8 relative">Added Inventory Items</span>
        <span class="stepIndicator flex-1 pb-8 relative">Finalize IGP</span>
        <span class="stepIndicator flex-1 pb-8 relative">Print IGP</span>
      </div>
      <!-- end step indicators -->


      <!-- Success Popups -->
      <div id="confirmationPopup" class="popup">
        <div class="popup-content">
          <p id="popupMessage"></p>
        </div>
      </div>
      <!-- end Success Popups -->

      <!-- step one -->
      <div class="flex justify-center">
        <div class="step" style="width:400px">
          <!-- <p class="text-md text-gray-700 leading-tight text-center mt-4 mb-4">Add Your OGP Informnation</p> -->
          <div class="mb-6 mt-4">
            <label class="font-bold text-gray-700 text-sm ml-1">Select IGP Type</label>
            <select id="igp_type" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showDiv(this)">
              <option selected value="">Select IGP Type</option>
              <option value="s">Return From Salesperson</option>
              <option value="e">Return From Employee</option>
              <option value="l">Received From store location</option>
              <option value="d">Returned From External destination</option>
            </select>
          </div>

          <div id="salesperson" style="display:none;" class="m-12">
            <div class="mb-6">
              <?php $sql = "select salesmanname from salesman";
              $result = mysqli_query($conn, $sql); ?>
              <label class="font-bold text-gray-700 text-sm ml-1">Return From Sales Person</label>
              <select id="salesman" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="ogpSaleman(this)">
                <option selected value="">Select Salesperson</option>
                <?php while ($myrow = mysqli_fetch_array($result)) { ?>
                  <option value="<?php echo $myrow['salesmanname'] ?>"><?php echo $myrow['salesmanname'] ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="mb-6">
              <label class="font-bold text-gray-700 text-sm ml-1">Salesperson IGP Type</label>
              <select id="igp_salesperson_type" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showOGPDiv(this)">
                <option value="" selected>Salesperson IGP Type</option>
                <option value="salescase">Salescase</option>
                <option value="csv">CSV</option>
                <option value="crv">CRV</option>
                <option value="mpo">MPO</option>
                <option value="cart">CART</option>
              </select>
            </div>

            <div id="salescase_div" style="display:none;">
              <div class="mb-6">
                <label class="font-bold text-gray-700 text-sm ml-1">Return From salescase</label>
                <select id="salescases" class="salescases w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showSalescaseDiv(this)">
                  <option value="" selected>Return From salescase</option>
                </select>
              </div>
            </div>
            <div id="csv_div" style="display:none;">
              <div class="mb-6">
                <label class="font-bold text-gray-700 text-sm ml-1">Return From CSV</label>
                <select id="csv" class="csv w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showcsvDiv(this)">
                  <option value="" selected>Return From CSV</option>
                </select>
              </div>
            </div>
            <div id="crv_div" style="display:none;">
              <div class="mb-6">
                <label class="font-bold text-gray-700 text-sm ml-1">Return From CRV</label>
                <select id="crv" class="crv w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showcrvDiv(this)">
                  <option value="" selected>Return From CRV</option>
                </select>
              </div>
            </div>
            <div id="mpo_div" style="display:none;">
              <div class="mb-6">
                <label class="font-bold text-gray-700 text-sm ml-1">Return From MPO</label>
                <select id="mpo" class="mpo w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showmpoDiv(this)">
                  <option value="" selected>Return From MPO</option>
                </select>
              </div>
            </div>
          </div>

          <div id="employee_div" style="display:none;" class="m-12">
            <div class="mb-6">
              <?php $sql = "select realname from www_users";
              $result = mysqli_query($conn, $sql); ?>
              <label class="font-bold text-gray-700 text-sm ml-1">Return From Employee </label>
              <select id="employee" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showmemployeeDiv(this)">
                <option value="" selected>Select an employee</option>
                <?php while ($myrow = mysqli_fetch_array($result)) { ?>
                  <option value="<?php echo $myrow['realname'] ?>"><?php echo $myrow['realname'] ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div id="store" style="display:none;" class="m-12">
            <div class="mb-6">
              <label class="font-bold text-gray-700 text-sm ml-1">From Stock Location </label>
              <select id="stock_location" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showStoreDiv(this)">
                <option value="">Select a location</option>
                <option value="HO">HO - Head Office</option>
                <option value="HOPS">HOPS - Head Office PS</option>
                <option value="MT">MT - Model Town</option>
                <option value="MTPS">MTPS - Model Town PS</option>
                <option value="OS">OS - Offset</option>
                <option value="SR">SR - Show Room</option>
                <option value="SRPS">SRPS - Show Room PS</option>
                <option value="VSR">VSR - Virtual Store Show Room</option>
                <option value="WS">WS - Wordk Shop</option>
              </select>
            </div>
          </div>

          <div id="destination" style="display:none;" class="m-12">
            <div class="mb-6">
              <label class="font-bold text-gray-700 text-sm ml-1">External Destination</label>
              <textarea id="desti" rows="4" class="destination w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" placeholder="Write your destination's detail here..."></textarea>
            </div>
          </div>

          <div class="mb-6">
            <?php $sql = "SELECT substoreid , description FROM substores WHERE locid = '" . $_SESSION['UserStockLocation'] . "'";
            $result = mysqli_query($conn, $sql); ?>
            <label class="font-bold text-gray-700 text-sm ml-1">Substore</label>
            <select id="subStore" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" onchange="showSubStoreDiv(this)">
              <?php while ($myrow = mysqli_fetch_array($result)) { ?>
                <option value="<?php echo $myrow['substoreid'] ?>"><?php echo $myrow['description'] ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-6">
            <label class="font-bold text-gray-700 text-sm ml-1">Date</label>
            <input id="date" type="date" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" disabled></input>
          </div>
          <div class="mb-6">
            <label class="font-bold text-gray-700 text-sm ml-1">Narrative</label>
            <textarea id="narative" rows="4" class="w-full px-4 py-1 rounded-md text-gray-700 font-medium border-solid border-2 border-gray-200" placeholder="Write your Narrative here..."></textarea>
          </div>
        </div>
      </div>

      <!-- step two -->
      <div class="step">
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
                  Model No.
                </th>
                <th scope="col" class="px-6 py-3">
                  On Hand
                </th>
                <th scope="col" class="px-6 py-3">
                  Quantity
                </th>
                <th scope="col" class="px-6 py-3">
                  Action
                </th>
              </tr>
            </thead>
            <tbody id="tableBody" class="text-black">
              <div id="loader" style="display:none;">
                <img src="ogp/api/loadingERP.gif" class="loader">
              </div>
            </tbody>
          </table>
        </div>

        <div class="px-6 pt-4 pb-2">
          <div class="flex justify-center items-center">
            <h3 class="m-2 font-bold text-4">Items Selected</h3>
          </div>
          <table class="table-fixed text-center" id="example1">
            <thead>
              <tr>
                <!-- <th>No.</th> -->
                <th class="text-center">Item Code</th>
                <th class="text-center">Description</th>
                <th class="text-center">Required</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="tableBody">
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
                <h3 class="mt-4 text-blue-500 font-bold text-lg justify-center">Detail of Items Requested:</h3>
              </div>
              <div class="flex justify-center">
                <div id="salesmanDiv" class="px-6 py-1 mt-2 flex" style="display:none;">
                  <h5 class="salesperson text-gray-700 text-sm ml-6"></h5>
                </div>
              </div>
              <input type="text" id="salescaseref" hidden>
              <input type="text" id="csvref" hidden>
              <input type="text" id="crvref" hidden>
              <input type="text" id="mporef" hidden>
              <div class="flex justify-center">
                <div id="salescaserefDiv" class="px-6 py-1 mt-2 flex" style="display:none;">
                  <h5 class="salescaseref text-gray-700 text-sm ml-6"></h5>
                </div>
              </div>
              <div class="flex justify-center">
                <div id="csvDiv" class="px-6 py-1 mt-2 flex" style="display:none;">
                  <h5 class="csv text-gray-700 text-sm ml-6"></h5>
                </div>
              </div>
              <div class="flex justify-center">
                <div id="crvDiv" class="px-6 py-1 mt-2 flex" style="display:none;">
                  <h5 class="crv text-gray-700 text-sm ml-6"></h5>
                </div>
              </div>
              <div class="flex justify-center">
                <div id="mpoDiv" class="px-6 py-1 mt-2 flex" style="display:none;">
                  <h5 class="mpo text-gray-700 text-sm ml-6"></h5>
                </div>
              </div>
              <div class="flex justify-center">
                <div id="EmployeeDiv" class="px-6 py-1 mt-2 flex" style="display:none;">
                  <h5 class="employee text-gray-700 text-sm ml-6"></h5>
                </div>
              </div>
              <div class="flex justify-center">
                <div id="storeDiv" class="px-6 py-1 mt-2 flex" style="display:none;">
                  <h5 class="store text-gray-700 text-sm ml-6"></h5>
                </div>
              </div>
              <div class="flex justify-center">
                <div id="destinationDiv" class="px-6 py-1 mt-2 flex" style="display:none;">
                  <h5 class="showDestination text-gray-700 text-sm ml-6"></h5>
                </div>
              </div>
              <div class="flex justify-center">
                <div class="px-6 py-1 flex">
                  <div class="px-6 py-1 mt-2 flex">
                    <h5 class="text-gray-700 text-sm ml-6"><b>Date: </b><?php echo date('Y-m-d'); ?></h5>
                  </div>
                </div>
              </div>

              <div class="flex justify-center">
                <div class="px-6 py-1 flex">
                  <div id="SubstoreDiv" class="px-6 py-1 flex" style="display:none;">
                    <h5 class="subStore text-gray-700 text-sm ml-6"></h5>
                  </div>
                </div>
              </div>


            </div>
          </div>
        </div>
      </div>

      <div class="step">
        <div class="mb-6 flex justify-center">
          <div class="p-44">
            <div class="printDiv rounded overflow-hidden shadow-lg">
              <div class="flex justify-center mt-6">
                <h3 class="mt-4 text-gray-700 font-semibold text-lg justify-center">Print The OGP:</h3>
              </div>
              <div class="flex justify-center mt-2">
                <button type="button" class="py-3 px-8 inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-blue-500 text-white hover:bg-blue-600">
                  <a id="PrintOGP" href="#">Print the IGP</a>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- start previous / next buttons -->
      <div class="form-footer flex gap-3">
        <button type="button" id="prevBtn" class="flex-1 focus:outline-none border border-gray-300 py-1 px-5 rounded-lg shadow-sm text-center text-gray-700 text-lg" onclick="nextPrev(-1)">Previous</button>
        <button type="button" id="nextBtn" class="flex-1 border border-transparent focus:outline-none p-3 rounded-md text-center text-white bg-indigo-600 hover:bg-indigo-700 text-lg" onclick="nextPrev(1)">Next</button>
        <button type="button" id="newOGPBtn" class="flex-1 border border-transparent focus:outline-none p-3 rounded-md text-center text-white bg-indigo-600 hover:bg-indigo-700 text-lg" onclick="window.location.reload()">Create New IGP</button>
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
<script src="//cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="igp/javascriptingss.js"></script>

</html>