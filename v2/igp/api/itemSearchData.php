<?php
include('../../config1.php');
session_start();
// $_SESSION['UsersRealName'];
$igp_type = $_POST['igp_type'];
$salesman = $_POST['salesman'];
$igp_salesperson_type = $_POST['igp_salesperson_type'];
$salescase = $_POST['salescase'];
$csv = $_POST['csv'];
$crv = $_POST['crv'];
$mpo = $_POST['mpo'];
$employee = $_POST['employee'];
// $stock_location = $_POST['stock_location'];
$stock_location = $_SESSION['UserStockLocation'];
$desti = $_POST['desti'];
$subStore = $_POST['subStore'];
$date = $_POST['date'];
$narative = $_POST['narative'];

if (empty($stock_location)) {
    $stock_location = $_SESSION['UserStockLocation'];
}

if ($igp_type == "s" || $igp_type == "e") {
    if ($igp_salesperson_type == "salescase") {
        $SQL5 = "SELECT DISTINCT stockmaster.stockid,
        stockmaster.description,
        stockmaster.mnfCode,
        stockmaster.longdescription,
        stockmaster.mnfCode,
        stockmaster.mnfpno,
        stockmaster.mbflag,
        stockmaster.discontinued,
        stockissuance.issued AS qoh,
        stockmaster.units,
        stockmaster.decimalplaces,
        ogpsalescaseref.salescaseref
            FROM stockmaster INNER JOIN stockissuance
            ON stockmaster.stockid=stockissuance.stockid
            INNER JOIN ogpsalescaseref
            ON stockmaster.stockid=ogpsalescaseref.stockid
            where stockissuance.salesperson = '" . $salesman . "'
            AND ogpsalescaseref.salescaseref = '" . $salescase . "'
            AND ogpsalescaseref.quantity != ''
            AND ogpsalescaseref.quantity != '0'
            and stockissuance.issued>0
            AND stockmaster.stockid NOT LIKE '%\t%'
            order by stockissuance.issued desc
            ";

        $UpdateResult = mysqli_query($conn, $SQL5);


        if ($UpdateResult) {
            $resultArray = array();
            while ($row = mysqli_fetch_assoc($UpdateResult)) {
                $resultArray[] = $row;
            }
            echo json_encode($resultArray);  // Convert the result array to JSON format
        } else {
            echo "Error: " . mysqli_error($conn);  // Output error if query fails
        }
    }

    // From csv 
    if ($igp_salesperson_type == "csv") {
        $SQL5 = "SELECT DISTINCT stockmaster.stockid,
    stockmaster.description,
    stockmaster.mnfCode,
    stockmaster.longdescription,
    stockmaster.mnfCode,
    stockmaster.mnfpno,
    stockmaster.mbflag,
    stockmaster.discontinued,
    stockissuance.issued  AS qoh,
    stockmaster.units,
    stockmaster.decimalplaces,
    ogpcsvref.csv
        FROM stockmaster INNER JOIN stockissuance
        ON stockmaster.stockid=stockissuance.stockid
        INNER JOIN ogpcsvref
        ON stockmaster.stockid=ogpcsvref.stockid
        where stockissuance.salesperson = '" . $salesman . "'
        AND ogpcsvref.csv = '" . $csv . "'
        AND ogpcsvref.quantity != ''
        AND ogpcsvref.quantity != '0'
        and stockissuance.issued>0
        AND stockmaster.stockid NOT LIKE '%\t%'
        order by stockissuance.issued desc
        ";

        $UpdateResult = mysqli_query($conn, $SQL5);


        if ($UpdateResult) {
            $resultArray = array();
            while ($row = mysqli_fetch_assoc($UpdateResult)) {
                $resultArray[] = $row;
            }
            echo json_encode($resultArray);  // Convert the result array to JSON format
        } else {
            echo "Error: " . mysqli_error($conn);  // Output error if query fails
        }
    }
    // From crv 
    if ($igp_salesperson_type == "crv") {
        $SQL5 = "SELECT DISTINCT stockmaster.stockid,
    stockmaster.description,
    stockmaster.mnfCode,
    stockmaster.longdescription,
    stockmaster.mnfCode,
    stockmaster.mnfpno,
    stockmaster.mbflag,
    stockmaster.discontinued,
    stockissuance.issued  AS qoh,
    stockmaster.units,
    stockmaster.decimalplaces,
    ogpcrvref.crv
        FROM stockmaster INNER JOIN stockissuance
        ON stockmaster.stockid=stockissuance.stockid
        INNER JOIN ogpcrvref
        ON stockmaster.stockid=ogpcrvref.stockid
        where stockissuance.salesperson = '" . $salesman . "'
        AND ogpcrvref.crv = '" . $crv . "'
        AND ogpcrvref.quantity != ''
        AND ogpcrvref.quantity != '0'
        and stockissuance.issued>0
        AND stockmaster.stockid NOT LIKE '%\t%'
        order by stockissuance.issued desc
        ";

        $UpdateResult = mysqli_query($conn, $SQL5);


        if ($UpdateResult) {
            $resultArray = array();
            while ($row = mysqli_fetch_assoc($UpdateResult)) {
                $resultArray[] = $row;
            }
            echo json_encode($resultArray);  // Convert the result array to JSON format
        } else {
            echo "Error: " . mysqli_error($conn);  // Output error if query fails
        }
    }
    // From mpo 
    if ($igp_salesperson_type == "mpo") {
        $SQL5 = "SELECT DISTINCT stockmaster.stockid,
    stockmaster.description,
    stockmaster.mnfCode,
    stockmaster.longdescription,
    stockmaster.mnfCode,
    stockmaster.mnfpno,
    stockmaster.mbflag,
    stockmaster.discontinued,
    stockissuance.issued  AS qoh,
    stockmaster.units,
    stockmaster.decimalplaces,
    ogpmporef.mpo
        FROM stockmaster INNER JOIN stockissuance
        ON stockmaster.stockid=stockissuance.stockid
        INNER JOIN ogpmporef
        ON stockmaster.stockid=ogpmporef.stockid
        where stockissuance.salesperson = '" . $salesman . "'
        AND ogpmporef.mpo = '" . $mpo . "'
        AND ogpmporef.quantity != ''
        AND ogpmporef.quantity != '0'
        and stockissuance.issued>0
        AND stockmaster.stockid NOT LIKE '%\t%'
        order by stockissuance.issued desc
        ";

        $UpdateResult = mysqli_query($conn, $SQL5);


        if ($UpdateResult) {
            $resultArray = array();
            while ($row = mysqli_fetch_assoc($UpdateResult)) {
                $resultArray[] = $row;
            }
            echo json_encode($resultArray);  // Convert the result array to JSON format
        } else {
            echo "Error: " . mysqli_error($conn);  // Output error if query fails
        }
    }
    // Fom Cart
    if ($igp_salesperson_type == "cart") {
        $SQL5 = "SELECT * FROM (
            SELECT stockmaster.stockid,
            stockmaster.description,
            stockmaster.mnfCode,
            stockmaster.longdescription,
            stockmaster.mnfpno,
            stockmaster.mbflag,
            stockmaster.discontinued,
            stockissuance.issued - (
            SELECT IFNULL(SUM(quantity), 0) 
            FROM `ogpsalescaseref` 
            WHERE salesman = '" . $salesman . "' 
                AND stockid = stockissuance.stockid
                AND dispatchid != ''
        ) - (
            SELECT IFNULL(SUM(quantity), 0) 
            FROM `ogpcsvref` 
            WHERE salesman = '" . $salesman . "' 
                AND stockid = stockissuance.stockid
                AND dispatchid != ''
        ) - (
            SELECT IFNULL(MIN(quantity), 0) 
            FROM `ogpcrvref` 
            WHERE salesman = '" . $salesman . "' 
                AND stockid = stockissuance.stockid
                AND dispatchid != ''
        ) - (
            SELECT IFNULL(SUM(quantity), 0) 
            FROM `ogpmporef` 
            WHERE salesman = '" . $salesman . "' 
                AND stockid = stockissuance.stockid
                AND dispatchid != ''
        ) AS qoh,
            stockmaster.units,
            stockmaster.decimalplaces
        FROM stockmaster INNER JOIN stockissuance
        ON stockmaster.stockid=stockissuance.stockid
        where stockissuance.salesperson = '" . $salesman . "'
        and stockissuance.issued > 0
        AND stockmaster.stockid NOT LIKE '%\t%'
        ) AS derived
        WHERE derived.qoh > 0
        ORDER BY derived.qoh DESC
        ";
        $UpdateResult = mysqli_query($conn, $SQL5);
        if ($UpdateResult) {
            $resultArray = array();
            while ($row = mysqli_fetch_assoc($UpdateResult)) {
                foreach ($row as $key => $value) {
                    if (is_string($value)) {
                        $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');  // Convert to UTF-8
                    }
                }
                $resultArray[] = $row;
            }
            $jsonResult = json_encode($resultArray);
            if ($jsonResult === false) {
                echo "JSON encode error: " . json_last_error_msg();
            } else {
                echo $jsonResult;
            }
        }
    }

    // Fom Employee Cart
    if ($employee != NULL) {
        $SQL5 = "SELECT * FROM (
            SELECT stockmaster.stockid,
            stockmaster.description,
            stockmaster.mnfCode,
            stockmaster.longdescription,
            stockmaster.mnfpno,
            stockmaster.mbflag,
            stockmaster.discontinued,
            stockissuance.issued - (
            SELECT IFNULL(SUM(quantity), 0) 
            FROM `ogpsalescaseref` 
            WHERE salesman = '" . $employee . "' 
                AND stockid = stockissuance.stockid
                AND dispatchid != ''
        ) - (
            SELECT IFNULL(SUM(quantity), 0) 
            FROM `ogpcsvref` 
            WHERE salesman = '" . $employee . "' 
                AND stockid = stockissuance.stockid
                AND dispatchid != ''
        ) - (
            SELECT IFNULL(MIN(quantity), 0) 
            FROM `ogpcrvref` 
            WHERE salesman = '" . $employee . "' 
                AND stockid = stockissuance.stockid
                AND dispatchid != ''
        ) - (
            SELECT IFNULL(SUM(quantity), 0) 
            FROM `ogpmporef` 
            WHERE salesman = '" . $employee . "' 
                AND stockid = stockissuance.stockid
                AND dispatchid != ''
        ) AS qoh,
            stockmaster.units,
            stockmaster.decimalplaces
        FROM stockmaster INNER JOIN stockissuance
        ON stockmaster.stockid=stockissuance.stockid
        where stockissuance.salesperson = '" . $employee . "'
        and stockissuance.issued > 0
        AND stockmaster.stockid NOT LIKE '%\t%'
        ) AS derived
        WHERE derived.qoh > 0
        ORDER BY derived.qoh DESC
        ";

        $UpdateResult = mysqli_query($conn, $SQL5);
        if ($UpdateResult) {
            $resultArray = array();
            while ($row = mysqli_fetch_assoc($UpdateResult)) {
                foreach ($row as $key => $value) {
                    if (is_string($value)) {
                        $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');  // Convert to UTF-8
                    }
                }
                $resultArray[] = $row;
            }
            $jsonResult = json_encode($resultArray);
            if ($jsonResult === false) {
                echo "JSON encode error: " . json_last_error_msg();
            } else {
                echo $jsonResult;
            }
        }
    }
}
if ($igp_type == "l" || $igp_type == "d") {

    $SQL5 = "SELECT 
    stockmaster.stockid, 
    stockmaster.description, 
    stockmaster.longdescription, 
    stockmaster.mnfCode, 
    stockmaster.mnfpno, 
    stockmaster.mbflag, 
    stockmaster.discontinued, 
    locstock.quantity AS qoh, 
    stockmaster.units, 
    stockmaster.decimalplaces 
FROM 
    stockmaster 
INNER JOIN 
    locstock ON stockmaster.stockid = locstock.stockid 
WHERE 
    (stockmaster.mnfCode LIKE '%%' OR stockmaster.stockid LIKE '%%') 
    AND locstock.loccode = '" . $_SESSION['UserStockLocation'] . "'
    AND stockmaster.stockid NOT LIKE '% %' 
    AND locstock.quantity > 0 
GROUP BY 
    stockmaster.stockid, 
    stockmaster.description, 
    stockmaster.longdescription, 
    stockmaster.mnfCode, 
    stockmaster.mnfpno, 
    stockmaster.mbflag, 
    stockmaster.discontinued, 
    locstock.quantity, 
    stockmaster.units, 
    stockmaster.decimalplaces 
ORDER BY 
    locstock.quantity DESC
    ";
    $UpdateResult = mysqli_query($conn, $SQL5);
    if ($UpdateResult) {
        $resultArray = array();
        while ($row = mysqli_fetch_assoc($UpdateResult)) {
            foreach ($row as $key => $value) {
                if (is_string($value)) {
                    $row[$key] = mb_convert_encoding($value, 'UTF-8', 'UTF-8');  // Convert to UTF-8
                }
            }
            $resultArray[] = $row;
        }
        $jsonResult = json_encode($resultArray);
        if ($jsonResult === false) {
            echo "JSON encode error: " . json_last_error_msg();
        } else {
            echo $jsonResult;
        }
    }
}
