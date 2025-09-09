<?php
include('../../config1.php');
session_start();

$currentUser = $_SESSION['UsersRealName'];
$igp_type = $_POST['igp_type'] ?? null;
$salesperson = $_POST['salesperson'] ?? null;
$salesperson_igp_type = $_POST['salesperson_igp_type'] ?? null;
$salescase = $_POST['salescase'] ?? null;
$csv = $_POST['csv'] ?? null;
$crv = $_POST['crv'] ?? null;
$mpo = $_POST['mpo'] ?? null;
$employee = $_POST['employee'] ?? null;

// $stock_location = $_POST['stock_location'];
$stock_location = $_SESSION['UserStockLocation'] ?? null;
$destination = $_POST['destination'] ?? null;
$substore = $_POST['substore'] ?? null;
$narative = $_POST['narative'] ?? null;
$request_fulfil = $_POST['request_fulfil'] ?? null;
$requestid = $_POST['requestid'] ?? null;
$items = $_POST['items'] ?? [];
$date = date('Y-m-d');
if (empty($stock_location)) {
    $stock_location = $_SESSION['UserStockLocation'];
}
$stock_location1 = $_POST['stock_location'];
$location_code = $stock_location1; // Example location code
$stock_location1 = "";

switch ($location_code) {
    case "HO":
        $stock_location1 = "HO - Head Office";
        break;
    case "HOPS":
        $stock_location1 = "HOPS - Head Office PS";
        break;
    case "MT":
        $stock_location1 = "MT - Model Town";
        break;
    case "MTPS":
        $stock_location1 = "MTPS - Model Town PS";
        break;
    case "OS":
        $stock_location1 = "OS - Offset";
        break;
    case "SR":
        $stock_location1 = "SR - Show Room";
        break;
    case "SRPS":
        $stock_location1 = "SRPS - Show Room PS";
        break;
    case "VSR":
        $stock_location1 = "VSR - Virtual Store Show Room";
        break;
    case "WS":
        $stock_location1 = "WS - Workshop";
        break;
    default:
        $stock_location1 = "Unknown Location";
        break;
}

$RequestNo = GetNextTransNoss(38, $conn);
function GetNextTransNoss($TransType, $db)
{

    $SQL = "SELECT typeno FROM systypes WHERE typeid = '" . $TransType . "'";
    $GetTransNoResult = mysqli_query($db, $SQL);

    $myrow = mysqli_fetch_array($GetTransNoResult);

    $SQL = "UPDATE systypes SET typeno = '" . ($myrow[0] + 1) . "' WHERE typeid = '" . $TransType . "'";
    $UpdTransNoResult = mysqli_query($db, $SQL);

    mysqli_query($db, "UNLOCK TABLES");

    return $myrow[0] + 1;
}


$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $conn);
function GetPeriod($TransDate, &$db, $UseProhibit = true)
{

    /* Convert the transaction date into a unix time stamp.*/

    if (mb_strpos($TransDate, '/')) {
        $Date_Array = explode('/', $TransDate);
    } elseif (mb_strpos($TransDate, '-')) {
        $Date_Array = explode('-', $TransDate);
    } elseif (mb_strpos($TransDate, '.')) {
        $Date_Array = explode('.', $TransDate);
    }

    if (($_SESSION['DefaultDateFormat'] == 'd/m/Y') or ($_SESSION['DefaultDateFormat'] == 'd.m.Y')) {
        $TransDate = mktime(0, 0, 0, $Date_Array[1], $Date_Array[0], $Date_Array[2]);
    } elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
        $TransDate = mktime(0, 0, 0, $Date_Array[0], $Date_Array[1], $Date_Array[2]);
    } elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d' or $_SESSION['DefaultDateFormat'] == 'Y-m-d') {
        $TransDate = mktime(0, 0, 0, $Date_Array[1], $Date_Array[2], $Date_Array[0]);
    }

    if (Is_Date(ConvertSQLDate($_SESSION['ProhibitPostingsBefore'])) and $UseProhibit) { //then the ProhibitPostingsBefore configuration is set
        $Date_Array = explode('-', $_SESSION['ProhibitPostingsBefore']); //its in ANSI SQL format
        $ProhibitPostingsBefore = mktime(0, 0, 0, $Date_Array[1], $Date_Array[2], $Date_Array[0]);

        /* If transaction date is in a closed period use the month end of that period */
        if ($TransDate < $ProhibitPostingsBefore) {
            $TransDate = $ProhibitPostingsBefore;
        }
    }
    /* Find the unix timestamp of the last period end date in periods table */
    $sql = "SELECT MAX(lastdate_in_period), MAX(periodno) from periods";
    $result = mysqli_query($db, $sql);
    $myrow = mysqli_fetch_array($result);

    if (is_null($myrow[0])) { //then no periods are currently defined - so set a couple up starting at 0
        $InsertFirstPeriodResult = mysqli_query($db, "INSERT INTO periods VALUES (0,'" . Date('Y-m-d', mktime(0, 0, 0, Date('m') + 1, 0, Date('Y'))) . "')", _('Could not insert first period'));
        $InsertFirstPeriodResult = mysqli_query($db, "INSERT INTO periods VALUES (1,'" . Date('Y-m-d', mktime(0, 0, 0, Date('m') + 2, 0, Date('Y'))) . "')", _('Could not insert second period'));
        $LastPeriod = 1;
        $LastPeriodEnd = mktime(0, 0, 0, Date('m') + 2, 0, Date('Y'));
    } else {
        $Date_Array = explode('-', $myrow[0]);
        $LastPeriodEnd = mktime(0, 0, 0, $Date_Array[1] + 1, 0, (int)$Date_Array[0]);
        $LastPeriod = $myrow[1];
    }
    /* Find the unix timestamp of the first period end date in periods table */
    $sql = "SELECT MIN(lastdate_in_period), MIN(periodno) from periods";
    $result = mysqli_query($db, $sql);
    $myrow = mysqli_fetch_array($result);
    $Date_Array = explode('-', $myrow[0]);
    $FirstPeriodEnd = mktime(0, 0, 0, $Date_Array[1], 0, (int)$Date_Array[0]);
    $FirstPeriod = $myrow[1];

    /* If the period number doesn't exist */
    if (!PeriodExists($TransDate, $db)) {
        /* if the transaction is after the last period */

        if ($TransDate > $LastPeriodEnd) {

            $PeriodEnd = mktime(0, 0, 0, Date('m', $TransDate) + 1, 0, Date('Y', $TransDate));

            while ($PeriodEnd >= $LastPeriodEnd) {
                if (Date('m', $LastPeriodEnd) <= 13) {
                    $LastPeriodEnd = mktime(0, 0, 0, Date('m', $LastPeriodEnd) + 2, 0, Date('Y', $LastPeriodEnd));
                } else {
                    $LastPeriodEnd = mktime(0, 0, 0, 2, 0, Date('Y', $LastPeriodEnd) + 1);
                }
                $LastPeriod++;
                CreatePeriod($LastPeriod, $LastPeriodEnd, $db);
            }
        } else {
            /* The transaction is before the first period */
            $PeriodEnd = mktime(0, 0, 0, Date('m', $TransDate), 0, Date('Y', $TransDate));
            $Period = $FirstPeriod - 1;
            while ($FirstPeriodEnd > $PeriodEnd) {
                CreatePeriod($Period, $FirstPeriodEnd, $db);
                $Period--;
                if (Date('m', $FirstPeriodEnd) > 0) {
                    $FirstPeriodEnd = mktime(0, 0, 0, Date('m', $FirstPeriodEnd), 0, Date('Y', $FirstPeriodEnd));
                } else {
                    $FirstPeriodEnd = mktime(0, 0, 0, 13, 0, Date('Y', $FirstPeriodEnd));
                }
            }
        }
    } else if (!PeriodExists(mktime(0, 0, 0, Date('m', $TransDate) + 1, Date('d', $TransDate), Date('Y', $TransDate)), $db)) {
        /* Make sure the following months period exists */
        $sql = "SELECT MAX(lastdate_in_period), MAX(periodno) from periods";
        $result = mysqli_query($db, $sql);
        $myrow = mysqli_fetch_array($result);
        $Date_Array = explode('-', $myrow[0]);
        $LastPeriodEnd = mktime(0, 0, 0, $Date_Array[1] + 2, 0, (int)$Date_Array[0]);
        $LastPeriod = $myrow[1];
        CreatePeriod($LastPeriod + 1, $LastPeriodEnd, $db);
    }

    /* Now return the period number of the transaction */

    $MonthAfterTransDate = Mktime(0, 0, 0, Date('m', $TransDate) + 1, Date('d', $TransDate), Date('Y', $TransDate));
    $GetPrdSQL = "SELECT periodno
					FROM periods
					WHERE lastdate_in_period < '" . Date('Y-m-d', $MonthAfterTransDate) . "'
					AND lastdate_in_period >= '" . Date('Y-m-d', $TransDate) . "'";

    $ErrMsg = _('An error occurred in retrieving the period number');
    $GetPrdResult = mysqli_query($db, $GetPrdSQL);
    $myrow = mysqli_fetch_array($GetPrdResult);

    return $myrow[0];
}

function ConvertSQLDate($DateEntry)
{

    //for MySQL dates are in the format YYYY-mm-dd


    if (mb_strpos($DateEntry, '/')) {
        $Date_Array = explode('/', $DateEntry);
    } elseif (mb_strpos($DateEntry, '-')) {
        $Date_Array = explode('-', $DateEntry);
    } elseif (mb_strpos($DateEntry, '.')) {
        $Date_Array = explode('.', $DateEntry);
    } else {
        prnMsg(_('The date does not appear to be in a valid format. The date being converted from SQL format was:') . ' ' . $DateEntry, 'error');
        switch ($_SESSION['DefaultDateFormat']) {
            case 'd/m/Y':
                return '0/0/000';
                break;
            case 'd.m.Y':
                return '0.0.000';
                break;
            case 'm/d/Y':
                return '0/0/0000';
                break;
            case 'Y/m/d':
                return '0000/0/0';
                break;
            case 'Y-m-d':
                return '0000-0-0';
                break;
        }
    }

    if (mb_strlen($Date_Array[2]) > 4) {  /*chop off the time stuff */
        $Date_Array[2] = mb_substr($Date_Array[2], 0, 2);
    }

    if ($_SESSION['DefaultDateFormat'] == 'd/m/Y') {
        return $Date_Array[2] . '/' . $Date_Array[1] . '/' . $Date_Array[0];
    } elseif ($_SESSION['DefaultDateFormat'] == 'd.m.Y') {
        return $Date_Array[2] . '.' . $Date_Array[1] . '.' . $Date_Array[0];
    } elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
        return $Date_Array[1] . '/' . $Date_Array[2] . '/' . $Date_Array[0];
    } elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d') {
        return $Date_Array[0] . '/' . $Date_Array[1] . '/' . $Date_Array[2];
    } elseif ($_SESSION['DefaultDateFormat'] == 'Y-m-d') {
        return $Date_Array[0] . '-' . $Date_Array[1] . '-' . $Date_Array[2];
    }
} // end function ConvertSQLDate

function Is_date($DateEntry)
{

    $DateEntry = Trim($DateEntry);

    //echo '<BR>The date entered is ' . $DateEntry;

    if (mb_strpos($DateEntry, '/')) {
        $Date_Array = explode('/', $DateEntry);
    } elseif (mb_strpos($DateEntry, '-')) {
        $Date_Array = explode('-', $DateEntry);
    } elseif (mb_strpos($DateEntry, '.')) {
        $Date_Array = explode('.', $DateEntry);
    } elseif (mb_strlen($DateEntry) == 6) {
        $Date_Array[0] = mb_substr($DateEntry, 0, 2);
        $Date_Array[1] = mb_substr($DateEntry, 2, 2);
        $Date_Array[2] = mb_substr($DateEntry, 4, 2);
    } elseif (mb_strlen($DateEntry) == 8) {
        $Date_Array[0] = mb_substr($DateEntry, 0, 2);
        $Date_Array[1] = mb_substr($DateEntry, 2, 2);
        $Date_Array[2] = mb_substr($DateEntry, 4, 4);
    }

    if (!isset($Date_Array) or sizeof($Date_Array) < 3) {
        return 0;
    }

    if ((int)$Date_Array[2] > 9999) {
        return 0;
    }


    if (is_long((int)$Date_Array[0]) and is_long((int)$Date_Array[1]) and is_long((int)$Date_Array[2])) {

        if (($_SESSION['DefaultDateFormat'] == 'd/m/Y') or ($_SESSION['DefaultDateFormat'] == 'd.m.Y')) {

            if (checkdate((int)$Date_Array[1], (int)$Date_Array[0], (int)$Date_Array[2])) {
                return 1;
            } else {
                return 0;
            }
        } elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {

            if (checkdate((int)$Date_Array[0], (int)$Date_Array[1], (int)$Date_Array[2])) {
                return 1;
            } else {
                return 0;
            }
        } elseif ($_SESSION['DefaultDateFormat'] == 'Y/m/d') {

            if (checkdate((int)$Date_Array[1], (int)$Date_Array[2], (int)$Date_Array[0])) {
                return 1;
            } else {
                return 0;
            }
        } elseif ($_SESSION['DefaultDateFormat'] == 'Y-m-d') {
            if (checkdate((int)$Date_Array[1], (int)$Date_Array[2], (int)$Date_Array[0])) {
                return 1;
            } else {
                return 0;
            }
        } else { /*Can't be in an appropriate DefaultDateFormat */
            return 0;
        }
    }
}

function CreatePeriod($PeriodNo, $PeriodEnd, &$db)
{
    $GetPrdSQL = "INSERT INTO periods (periodno,
                                        lastdate_in_period)
                                    VALUES (
                                        '" . $PeriodNo . "',
                                        '" . Date('Y-m-d', $PeriodEnd) . "')";
    $ErrMsg = _('An error occurred in adding a new period number');
    $GetPrdResult = mysqli_query($db, $GetPrdSQL);
}

function PeriodExists($TransDate, &$db)
{

    /* Find the date a month on */
    $MonthAfterTransDate = Mktime(0, 0, 0, Date('m', $TransDate) + 1, Date('d', $TransDate), Date('Y', $TransDate));

    $GetPrdSQL = "SELECT periodno FROM periods WHERE lastdate_in_period < '" . Date('Y/m/d', $MonthAfterTransDate) . "' AND lastdate_in_period >= '" . Date('Y/m/d', $TransDate) . "'";

    $ErrMsg = _('An error occurred in retrieving the period number');
    $GetPrdResult = mysqli_query($db, $GetPrdSQL);

    if (mysqli_num_rows($GetPrdResult) == 0) {
        return false;
    } else {
        return true;
    }
}

function FormatDateForSQL($DateEntry)
{

    /* takes a date in a the format specified in $_SESSION['DefaultDateFormat']
    and converts to a yyyy/mm/dd format */
    $Date_Array = array();
    $DateEntry = trim($DateEntry);

    if (mb_strpos($DateEntry, '/')) {
        $Date_Array = explode('/', $DateEntry);
    } elseif (mb_strpos($DateEntry, '-')) {
        $Date_Array = explode('-', $DateEntry);
    } elseif (mb_strpos($DateEntry, '.')) {
        $Date_Array = explode('.', $DateEntry);
    } elseif (mb_strlen($DateEntry) == 6) {
        $Date_Array[0] = mb_substr($DateEntry, 0, 2);
        $Date_Array[1] = mb_substr($DateEntry, 2, 2);
        $Date_Array[2] = mb_substr($DateEntry, 4, 2);
    } elseif (mb_strlen($DateEntry) == 8) {
        $Date_Array[0] = mb_substr($DateEntry, 0, 4);
        $Date_Array[1] = mb_substr($DateEntry, 4, 2);
        $Date_Array[2] = mb_substr($DateEntry, 6, 2);
    }

    if ($_SESSION['DefaultDateFormat'] == 'Y/m/d' or $_SESSION['DefaultDateFormat'] == 'Y-m-d') {
        if (mb_strlen($Date_Array[0]) == 2) {
            if ((int)$Date_Array[0] <= 60) {
                $Date_Array[0] = '20' . $Date_Array[2];
            } elseif ((int)$Date_Array[0] > 60 and (int)$Date_Array[2] < 100) {
                $Date_Array[0] = '19' . $Date_Array[2];
            }
        }
        return $Date_Array[0] . '-' . $Date_Array[1] . '-' . $Date_Array[2];
    } elseif (($_SESSION['DefaultDateFormat'] == 'd/m/Y')
        or $_SESSION['DefaultDateFormat'] == 'd.m.Y'
    ) {
        if (mb_strlen($Date_Array[2]) == 2) {
            if ((int)$Date_Array[2] <= 60) {
                $Date_Array[2] = '20' . $Date_Array[2];
            } elseif ((int)$Date_Array[2] > 60 and (int)$Date_Array[2] < 100) {
                $Date_Array[2] = '19' . $Date_Array[2];
            }
        }
        /* echo '<BR>The date returned is ' . $Date_Array[2].'/'.$Date_Array[1].'/'.$Date_Array[0]; */
        return $Date_Array[2] . '-' . $Date_Array[1] . '-' . $Date_Array[0];
    } elseif ($_SESSION['DefaultDateFormat'] == 'm/d/Y') {
        if (mb_strlen($Date_Array[2]) == 2) {
            if ((int)$Date_Array[2] <= 60) {
                $Date_Array[2] = '20' . $Date_Array[2];
            } elseif ((int)$Date_Array[2] > 60 and (int)$Date_Array[2] < 100) {
                $Date_Array[2] = '19' . $Date_Array[2];
            }
        }
        return $Date_Array[2] . '-' . $Date_Array[0] . '-' . $Date_Array[1];
    }
}
echo $RequestNo;

$receivedfrom = "";
if ($salesperson != "") {
    $receivedfrom = $salesperson;
} elseif ($employee != "") {
    $receivedfrom = $employee;
} elseif ($stock_location1 != "") {
    $receivedfrom = $stock_location1;
}

if ($igp_type == "d") {

    $HeaderSQL = "INSERT INTO igp (dispatchid,
    loccode,
    despatchdate,
    receivedfrom,
    storemanager,
    narrative)
    VALUES(
'" . $RequestNo . "',
'" . $stock_location . "',
'" . $date . "',
'" . $destination . "',
'" . $currentUser . "',
'" . $narative . "'
)";



    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = mysqli_query($conn, $HeaderSQL);
} else {
    $HeaderSQL = "INSERT INTO igp (dispatchid,
                                        loccode,
                                        despatchdate,
                                        receivedfrom,
                                        storemanager,
                                        narrative)
                                        VALUES(
                                    '" . $RequestNo . "',
									'" . $stock_location . "',
									'" . $date . "',
									'" . $receivedfrom . "',
									'" . $currentUser . "',
									'" . $narative . "'
                                    )";



    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = mysqli_query($conn, $HeaderSQL);
}

if ($salescase != "") {
    $selectedItemsCode = NULL;
    foreach ($items as $LineItems) {
        $itemcode = "SELECT * FROM ogpsalescaseref WHERE salescaseref= '" . $salescase . "'	
            AND stockid = '" . $LineItems['Code'] . "' AND  salesman  = '" . $salesperson . "' AND quantity IS NOT NULL AND quantity > 0";
        $Result = mysqli_query($conn, $itemcode);

        if (mysqli_num_rows($Result) == 1) {
            $itemcode = "UPDATE ogpsalescaseref 
            SET quantity = quantity - '" . intval($LineItems['quantity']) . "' 
            WHERE salescaseref = '" . $salescase . "' 
            AND stockid = '" . $LineItems['Code'] . "' 
            AND salesman = '" . $salesperson . "' 
            AND quantity IS NOT NULL 
            AND quantity > 0 ";
            $Result = mysqli_query($conn, $itemcode);

            $HeaderSalescaserefSQL = "INSERT INTO ogpsalescaseref (dispatchid,
                                        salescaseref,
                                        requestedby,
                                        stockid,
                                        salesman
                                        )
                                    VALUES (
                                        '" . $RequestNo . "',
                                        '" . $salescase . "',
                                        '" . $_SESSION['UsersRealName'] . "',
                                        '" . $LineItems['Code'] . "',
                                        '" . $salesperson . "')";

            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
            $DbgMsg = _('The following SQL to insert the request header record was used');
            $Result = mysqli_query($conn, $HeaderSalescaserefSQL);
        }
        // else {
        //     $HeaderSalescaserefSQL = "INSERT INTO ogpsalescaseref (dispatchid,
        //                                 salescaseref,
        //                                 requestedby,
        //                                 stockid,
        //                                 salesman,
        //                                 quantity
        //                                 )
        //                             VALUES (
        //                                 '" . $RequestNo . "',
        //                                 '" . $salescase . "',
        //                                 '" . $_SESSION['UsersRealName'] . "',
        //                                 '" . $LineItems['Code'] . "',
        //                                 '" . $salesperson . "',
        //                                 '" . $LineItems['quantity'] . "')";
        //     $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
        //     $DbgMsg = _('The following SQL to insert the request header record was used');
        //     $Result = mysqli_query($conn, $HeaderSalescaserefSQL);++
        // }
    }
}

if ($csv != "") {
    $selectedItemsCode = NULL;
    foreach ($items as $LineItems) {

        $itemcode = "SELECT * FROM ogpcsvref WHERE csv= '" . $csv . "'	
            AND stockid = '" . $LineItems['Code'] . "' AND  salesman  = '" . $salesperson . "' AND quantity IS NOT NULL AND quantity > 0";
        $Result = mysqli_query($conn, $itemcode);

        if (mysqli_num_rows($Result) == 1) {
            $itemcode = "UPDATE ogpcsvref SET quantity =quantity -'" . $LineItems['quantity'] . "' WHERE csv= '" . $csv . "'
                    AND stockid = '" . $LineItems['Code'] . "' AND  salesman = '" . $salesperson . "' AND quantity IS NOT NULL AND quantity > 0";
            $Result = mysqli_query($conn, $itemcode);

            $HeaderSalescaserefSQL = "INSERT INTO ogpcsvref (dispatchid,
            csv,
            requestedby,
            stockid,
            salesman
            )
        VALUES (
            '" . $RequestNo . "',
            '" . $csv . "',
            '" . $_SESSION['UsersRealName'] . "',
            '" . $LineItems['Code'] . "',
            '" . $salesperson . "')";

            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
            $DbgMsg = _('The following SQL to insert the request header record was used');
            $Result = mysqli_query($conn, $HeaderSalescaserefSQL);
        }
    }
}

if ($crv != "") {
    $selectedItemsCode = NULL;
    $itemcode = "SELECT * FROM ogpcrvref WHERE crv = '" . $crv . "'	
            AND stockid = '" . $LineItems['Code'] . "' AND  salesman  = '" . $salesperson . "' AND quantity IS NOT NULL AND quantity > 0";
    $Result = mysqli_query($conn, $itemcode);

    if (mysqli_num_rows($Result) == 1) {
        $itemcode = "UPDATE ogpcrvref SET quantity =quantity -'" . $LineItems['quantity'] . "' WHERE crv= '" . $crv . "'
                    AND stockid = '" . $LineItems['Code'] . "' AND  salesman = '" . $salesperson . "' AND quantity IS NOT NULL AND quantity > 0";
        $Result = mysqli_query($conn, $itemcode);

        $HeaderSalescaserefSQL = "INSERT INTO ogpcrvref (dispatchid,
            crv,
            requestedby,
            stockid,
            salesman
            )
        VALUES (
            '" . $RequestNo . "',
            '" . $crv . "',
            '" . $_SESSION['UsersRealName'] . "',
            '" . $LineItems['Code'] . "',
            '" . $salesperson . "')";

        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
        $DbgMsg = _('The following SQL to insert the request header record was used');
        $Result = mysqli_query($conn, $HeaderSalescaserefSQL);
    }
}

if ($mpo != "") {
    $selectedItemsCode = NULL;
    $itemcode = "SELECT * FROM ogpmporef WHERE mpo = '" . $mpo . "'	
            AND stockid = '" . $LineItems['Code'] . "' AND  salesman  = '" . $salesperson . "' AND quantity IS NOT NULL AND quantity > 0";
    $Result = mysqli_query($conn, $itemcode);

    if (mysqli_num_rows($Result) == 1) {
        $itemcode = "UPDATE ogpmporef SET quantity =quantity -'" . $LineItems['quantity'] . "' WHERE mpo= '" . $mpo . "'
                    AND stockid = '" . $LineItems['Code'] . "' AND  salesman = '" . $salesperson . "' AND quantity IS NOT NULL AND quantity > 0";
        $Result = mysqli_query($conn, $itemcode);

        $HeaderSalescaserefSQL = "INSERT INTO ogpmporef (dispatchid,
            mpo,
            requestedby,
            stockid,
            salesman
            )
        VALUES (
            '" . $RequestNo . "',
            '" . $mpo . "',
            '" . $_SESSION['UsersRealName'] . "',
            '" . $LineItems['Code'] . "',
            '" . $salesperson . "')";

        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
        $DbgMsg = _('The following SQL to insert the request header record was used');
        $Result = mysqli_query($conn, $HeaderSalescaserefSQL);
    }
}


$i = 0;
foreach ($items as $LineItems) {

    $LineSQL = "INSERT INTO igpitems (dispatchitemsid,
    dispatchid,
    stockid,
    quantity,
    comments,
    decimalplaces,
    uom)
VALUES(
        '" . $i . "',
		'" . $RequestNo . "',
		'" . $LineItems['Code'] . "',
		'" . $LineItems['quantity'] . "',
        '',
        '0',
        '')";
    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
    $DbgMsg = _('The following SQL to insert the request header record was used');
    $Result = mysqli_query($conn, $LineSQL);

    $SQL = "SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $LineItems['Code'] . "'
						AND loccode= '" . $_SESSION['UserStockLocation'] . "'";

    $ResultQ = mysqli_query($conn, $SQL);
    if (mysqli_num_rows($ResultQ) == 1) {
        $LocQtyRow = mysqli_fetch_row($ResultQ);
        $QtyOnHandPrior = $LocQtyRow[0];
    } else {
        // There must actually be some error this should never happen
        $QtyOnHandPrior = 0;
    }

    if ($igp_type == "d") {
        $SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												reference,
												qty,
												prd,
												newqoh
												)
					
					VALUES (
						'" . $LineItems['Code'] . "',
						510,
						'" . $RequestNo . "',
						'" . $stock_location . "',
							'" . $date . "',
						'" . _('From') . ' ' . $destination . "'
						,'" . round($LineItems['quantity'], 0) . "'
						,'" . $PeriodNo . "'
						
						,'" . round($QtyOnHandPrior + $LineItems['quantity'], 0) . "'
						)";

        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
        $DbgMsg =  _('The following SQL to insert the stock movement record was used');
        $Result = mysqli_query($conn, $SQL);
    } else {
        $SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												reference,
												qty,
												prd,
												newqoh
												)
					
					VALUES (
						'" . $LineItems['Code'] . "',
						510,
						'" . $RequestNo . "',
						'" . $stock_location . "',
							'" . $date . "',
						'" . _('From') . ' ' . $receivedfrom . "'
						,'" . round($LineItems['quantity'], 0) . "'
						,'" . $PeriodNo . "'
						
						,'" . round($QtyOnHandPrior + $LineItems['quantity'], 0) . "'
						)";

        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the incoming stock cannot be added because');
        $DbgMsg =  _('The following SQL to insert the stock movement record was used');
        $Result = mysqli_query($conn, $SQL);
    }


    if ($igp_type == "s" or $igp_type == "e") {

        $SQL = "UPDATE stockissuance
					SET issued = issued - '" . round($LineItems['quantity'], 0) . "'
					WHERE stockid='" . $LineItems['Code'] . "'
					AND salesperson='" . $salesperson . "'";
        $Result = mysqli_query($conn, $SQL);
        $SQL = "UPDATE stockissuance
					SET returned = returned + '" . round($LineItems['quantity'], 0) . "'
					WHERE stockid='" . $LineItems['Code'] . "'
					AND salesperson='" . $salesperson . "'";
        $Result = mysqli_query($conn, $SQL);


        $SQL = "UPDATE locstock
					SET quantity = quantity + '" . round($LineItems['quantity'], 0) . "'
					WHERE stockid='" . $LineItems['Code'] . "'
					AND loccode='" . $stock_location . "'";
        $Result = mysqli_query($conn, $SQL);

        $SQL = "UPDATE substorestock
					SET quantity = quantity + '" . round($LineItems['quantity'], 0) . "'
					WHERE stockid='" . $LineItems['Code'] . "'
					AND substoreid='" .  $substore . "'";

        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
        $DbgMsg =  _('The following SQL to update the stock record was used');
        $Result = mysqli_query($conn, $SQL);
    }

    if ($igp_type == "l" or $igp_type == "d") {

        $SQL = "UPDATE locstock
					SET quantity = quantity + '" . round($LineItems['quantity'], 0) . "'
					WHERE stockid='" . $LineItems['Code'] . "'
					AND loccode='" . $stock_location . "'";

        $Result = mysqli_query($conn, $SQL);

        $SQL = "UPDATE substorestock
					SET quantity = quantity + '" . round($LineItems['quantity'], 0) . "'
					WHERE stockid='" . $LineItems['Code'] . "'
					AND substoreid='" . $substore . "'";

        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
        $DbgMsg =  _('The following SQL to update the stock record was used');
        $Result = mysqli_query($conn, $SQL);

        if ($request_fulfil == "yes") {

            $updateStatusSQL = "UPDATE submitted_ogp_items 
                       SET quantity = quantity - '" . round($LineItems['quantity'], 0) . "'
                       WHERE stockcode = '" . $LineItems['Code'] . "' 
                        AND notification_id = '" . $requestid . "' ";
            $updateStatusResult = mysqli_query($conn, $updateStatusSQL);

            // making notification detial page status changed
            $updateStatusSQL = "UPDATE ogp_notifications 
                       SET status = 'in_progress'
                       WHERE id = '" . $requestid . "' ";
            $updateStatusResult = mysqli_query($conn, $updateStatusSQL);



            $checkSQL = "SELECT quantity FROM submitted_ogp_items 
            WHERE stockcode = '" . $LineItems['Code'] . "' 
            AND notification_id = '" . $requestid . "' AND quantity = 0";

            $checkResult = mysqli_query($conn, $checkSQL);

            // If the quantity is zero, update the status to 'approved'
            if (mysqli_num_rows($checkResult) > 0) {
                $updateStatusSQL = "UPDATE submitted_ogp_items 
                       SET status = 'approved' 
                       WHERE stockcode = '" . $LineItems['Code'] . "' 
                        AND notification_id = '" . $requestid . "' AND quantity = 0";
                $updateStatusResult = mysqli_query($conn, $updateStatusSQL);
            }
        }
    }
    $i++;
}

// echo $RequestNo;
// echo $PeriodNo;
