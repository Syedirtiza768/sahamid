<?php

$PathPrefix = '../../../';
include('../../../includes/session.inc');
include('../../../includes/SQL_CommonFunctions.inc');

$SQL = 'SELECT * FROM suppliers';
$result = mysqli_query($db, $SQL);

$response = [];

// Assuming you have already fetched the supplier data and stored it in $result.

while ($customer = mysqli_fetch_assoc($result)) {
    $res = [];

    $res[0] = $customer['supplierid'];
    $res[1] = "<form action='" . $RootPath . "/../suppstatement/SupplierStatement.php' method='post' target='_blank'>
                    <input type='hidden' name='FormID' value='" . $_SESSION['FormID'] . "' />
                    <input type='hidden' name='cust' value='" . $customer['supplierid'] . "' />
                    <input type='submit' class='btn btn-info' style='width:100%' value='" . $customer['supplierid'] . "' />
                </form>";

    $res[2] = $customer['suppname'];
    
    // New query to fetch the value of "curr" where available
    $SQL = 'SELECT suppliers.supplierid as debtorno,
                SUM(ovamount - alloc) as curr  
            FROM supptrans 
            LEFT OUTER JOIN suppliers ON suppliers.supplierid = supptrans.supplierno 
            WHERE supptrans.type = 601
            AND supptrans.settled = 0
            AND supptrans.reversed = 0
            AND suppliers.supplierid = "' . $customer['supplierid'] . '"
            GROUP BY suppliers.supplierid'; 

    $result_curr = mysqli_query($db, $SQL);
    $row_curr = mysqli_fetch_assoc($result_curr);
    $res[3] = $row_curr['curr'] ?? 0; // If "curr" is not available, set it to 0.
    $res[4] = locale_number_format($res[3], 2) . "<sub>PKR</sub>";
    
    $res[5] = '<a href="../../../Payments.php?SupplierID=' . $customer['supplierid'] . '" class="btn btn-info" target="_blank" style="font-size:11px; white-space: nowrap;">Enter Payment</a>';
    
    $selectMenu = '<select name="ledgerstatus" class="form-control ledgerstatus" data-tablename="suppliers" data-colname="ledgerstatus" data-supplierid=' . $customer['supplierid'] . '>
                  <option value="showroom mismanaged" ' . ($customer['ledgerstatus'] === 'showroom mismanaged' ? 'selected' : '') . '>showroom mismanaged</option>
                  <option value="showroom corrected" ' . ($customer['ledgerstatus'] === 'showroom corrected' ? 'selected' : '') . '>showroom corrected</option>
                  <option value="mto mismanaged" ' . ($customer['ledgerstatus'] === 'mto mismanaged' ? 'selected' : '') . '>mto mismanaged</option>
                  <option value="mto corrected" ' . ($customer['ledgerstatus'] === 'mto corrected' ? 'selected' : '') . '>mto corrected</option>
              </select>';
    // $res[4] = $selectMenu;
    $res[6] = $customer['ledgerstatus'];
    $res[7] = $selectMenu;

    $response[] = $res;
}

echo json_encode($response);
return;
?>
