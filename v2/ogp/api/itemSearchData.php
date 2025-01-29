<?php
include('../../config1.php');
session_start();
// $_SESSION['UsersRealName'];
$StockCat = "All";
$StockCode =  $_POST['StockCode'];
$brand = $_POST['brand'];
$subStore = $_SESSION['UserStockLocation'];
$realsubStore = $_POST['subStore'];



if (isset($StockCode)) {
    //insert wildcard characters in spaces
    if ($StockCode == "") {
        $SearchString2 = '%%';
    } else {
        $SearchString2 = '%' . $StockCode . '%';
    }
    // $SearchString2 = '%' . str_replace(' ', '%', $StockCode) . '%';
    // echo $SearchString2;
    //    return false;
    if ($StockCat == 'All') {
        if ($brand == 'All') {
            $SQLA = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.mnfCode,
						stockmaster.mnfpno,
						stockmaster.mbflag,
						stockmaster.discontinued,
						sum(substorestock.quantity) AS qoh,
						stockmaster.units,
						stockmaster.decimalplaces
						FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
                        INNER JOIN substorestock
						ON stockmaster.stockid=substorestock.stockid
						WHERE (stockmaster.mnfCode  LIKE  '%" . $SearchString2 . "%'
					or stockmaster.stockid LIKE  '%" . $SearchString2 . "%')
					and locstock.loccode = '" . $subStore . "'
					AND stockmaster.stockid NOT LIKE '%\t%'
                    and substorestock.loccode = '" . $subStore . "'
                    and substorestock.substoreid = '" . $realsubStore . "'
                    and substorestock.quantity !=0
						GROUP BY stockmaster.stockid,
							stockmaster.description,
							stockmaster.longdescription,
							stockmaster.units,
							stockmaster.mbflag,
							stockmaster.discontinued,
							stockmaster.decimalplaces
						ORDER BY substorestock.quantity desc";
                        echo $SQLA;
            $result = mysqli_query($conn, $SQLA);
            $results = array();
            $ItemData = array();

            $i = 0;
            while ($myrow = mysqli_fetch_array($result)) {

                $data[$i]['Code'] = $myrow['stockid'];
                $data[$i]['Description'] = $myrow['description'];

                // Quantity On hand
                $QOH = $myrow['qoh'];
                $data[$i]['QOH'] = $QOH;
                // for OnDemand
                $QOHSQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            FROM salesorderdetails INNER JOIN salesorders
            ON salesorders.orderno = salesorderdetails.orderno
            WHERE salesorders.fromstkloc='" . $subStore . "'
            AND salesorderdetails.completed=0
            AND salesorders.quotation=0
            AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
                $DemandResult =  mysqli_query($conn, $QOHSQL);
                $DemandRow = mysqli_fetch_array($DemandResult);
                if ($DemandRow[0] != null) {
                    $DemandQty =  $DemandRow[0];
                } else {
                    $DemandQty = '0';
                }
                $data[$i]['OnDemand'] = $DemandQty;

                // Onorder Value
                $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
            FROM purchorderdetails LEFT JOIN purchorders
               ON purchorderdetails.orderno=purchorders.orderno
            WHERE purchorderdetails.completed=0
            AND purchorders.status<>'Cancelled'
            AND purchorders.status<>'Rejected'
            AND purchorders.status<>'Completed'
           AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

                $ErrMsg = _('The order details for this product cannot be retrieved because');
                $PurchResult = mysqli_query($conn, $sql);

                $PurchRow = mysqli_fetch_array($PurchResult);
                if ($PurchRow[0] != null) {
                    $PurchQty =  $PurchRow[0];
                } else {
                    $PurchQty = 0;
                }
                $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
          FROM woitems
          WHERE stockid='" . $myrow['stockid'] . "'";
                $ErrMsg = _('The order details for this product cannot be retrieved because');
                $WoResult = mysqli_query($conn, $sql);

                $WoRow = mysqli_fetch_array($WoResult);
                if ($WoRow[0] != null) {
                    $WoQty =  $WoRow[0];
                } else {
                    $WoQty = 0;
                }
                $OnOrder = $PurchQty + $WoQty;
                $data[$i]['OnOrder'] = "$OnOrder";

                // for available
                $Available = $QOH;
                $data[$i]['Available'] = $Available;


                $i++;
            }


            // Initialize an empty array to store JSON strings
            $jsonStrings = array();

            // Loop through each subarray and convert to JSON string
            
            foreach ($data as $subArray) {
                // Convert subarray to JSON string
                $jsonString = json_encode($subArray);
                if ($jsonString) {
                    // Add JSON string to the array
                    $jsonStrings[] = $jsonString;
                }
            }

            // Combine JSON strings with commas
            $result = implode(',', $jsonStrings);

            // Output the result
            echo '[' . $result . ']';


            return false;
            // error_log("Data: " . print_r($results, true));

        } else
            $SQLA = "SELECT stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.mnfCode,
                stockmaster.mnfpno,
                stockmaster.mbflag,
                stockmaster.discontinued,
                locstock.quantity AS qohand,
                stockmaster.units,
                stockmaster.decimalplaces
            FROM stockmaster INNER JOIN locstock
            ON stockmaster.stockid=locstock.stockid
            WHERE brand ='" . $brand . "'
            and locstock.loccode = '" . $subStore . "'
            AND stockmaster.stockid NOT LIKE '%\t%'
            GROUP BY stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.units,
                stockmaster.mbflag,
                stockmaster.discontinued,
                stockmaster.decimalplaces
            ORDER BY locstock.quantity desc";

        $result = mysqli_query($conn, $SQLA);
        $results = array();
        $ItemData = array();

        $i = 0;
        while ($myrow = mysqli_fetch_array($result)) {

            $data[$i]['Code'] = $myrow['stockid'];
            $data[$i]['Description'] = $myrow['description'];
            $data[$i]['QOH'] = $myrow['qohand'];
            // for OnDemand
            $QOHSQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            FROM salesorderdetails INNER JOIN salesorders
            ON salesorders.orderno = salesorderdetails.orderno
            WHERE salesorders.fromstkloc='" . $subStore . "'
            AND salesorderdetails.completed=0
            AND salesorders.quotation=0
            AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
            $DemandResult =  mysqli_query($conn, $QOHSQL);
            $DemandRow = mysqli_fetch_array($DemandResult);
            if ($DemandRow[0] != null) {
                $DemandQty =  $DemandRow[0];
            } else {
                $DemandQty = '0';
            }
            $data[$i]['OnDemand'] = $DemandQty;

            // Onorder Value
            $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
            FROM purchorderdetails LEFT JOIN purchorders
               ON purchorderdetails.orderno=purchorders.orderno
            WHERE purchorderdetails.completed=0
            AND purchorders.status<>'Cancelled'
            AND purchorders.status<>'Rejected'
            AND purchorders.status<>'Completed'
           AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

            $ErrMsg = _('The order details for this product cannot be retrieved because');
            $PurchResult = mysqli_query($conn, $sql);

            $PurchRow = mysqli_fetch_array($PurchResult);
            if ($PurchRow[0] != null) {
                $PurchQty =  $PurchRow[0];
            } else {
                $PurchQty = 0;
            }
            $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
          FROM woitems
          WHERE stockid='" . $myrow['stockid'] . "'";
            $ErrMsg = _('The order details for this product cannot be retrieved because');
            $WoResult = mysqli_query($conn, $sql);

            $WoRow = mysqli_fetch_array($WoResult);
            if ($WoRow[0] != null) {
                $WoQty =  $WoRow[0];
            } else {
                $WoQty = 0;
            }
            $OnOrder = $PurchQty + $WoQty;
            $data[$i]['OnOrder'] = "$OnOrder";

            // for available
            $Available = $myrow['qohand'] - $DemandQty + $OnOrder;
            $data[$i]['Available'] = $Available;


            $i++;
        }


        // Initialize an empty array to store JSON strings
        $jsonStrings = array();

        // Loop through each subarray and convert to JSON string
        foreach ($data as $subArray) {
            // Convert subarray to JSON string
            $jsonString = json_encode($subArray);
            if ($jsonString) {
                // Add JSON string to the array
                $jsonStrings[] = $jsonString;
            }
        }

        // Combine JSON strings with commas
        $result = implode(',', $jsonStrings);

        // Output the result
        echo '[' . $result . ']';


        return false;
    } else {
        if ($brand == 'All') {
            $SQLA = "SELECT stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.mnfCode,
                stockmaster.mnfpno,
                stockmaster.mbflag,
                stockmaster.discontinued,
                locstock.quantity AS qohand,
                stockmaster.units,
                stockmaster.decimalplaces
                FROM stockmaster INNER JOIN locstock
                ON stockmaster.stockid=locstock.stockid
                WHERE 
                categoryid='" . $StockCat . "'
                and locstock.loccode = '" . $subStore . "'
                AND stockmaster.stockid NOT LIKE '%\t%'
                GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.longdescription,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.discontinued,
                    stockmaster.decimalplaces
                ORDER BY locstock.quantity desc";

            $result = mysqli_query($conn, $SQLA);
            $results = array();
            $ItemData = array();

            $i = 0;
            while ($myrow = mysqli_fetch_array($result)) {

                $data[$i]['Code'] = $myrow['stockid'];
                $data[$i]['Description'] = $myrow['description'];
                $data[$i]['QOH'] = $myrow['qohand'];
                // for OnDemand
                $QOHSQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            FROM salesorderdetails INNER JOIN salesorders
            ON salesorders.orderno = salesorderdetails.orderno
            WHERE salesorders.fromstkloc='" . $subStore . "'
            AND salesorderdetails.completed=0
            AND salesorders.quotation=0
            AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
                $DemandResult =  mysqli_query($conn, $QOHSQL);
                $DemandRow = mysqli_fetch_array($DemandResult);
                if ($DemandRow[0] != null) {
                    $DemandQty =  $DemandRow[0];
                } else {
                    $DemandQty = '0';
                }
                $data[$i]['OnDemand'] = $DemandQty;

                // Onorder Value
                $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
            FROM purchorderdetails LEFT JOIN purchorders
               ON purchorderdetails.orderno=purchorders.orderno
            WHERE purchorderdetails.completed=0
            AND purchorders.status<>'Cancelled'
            AND purchorders.status<>'Rejected'
            AND purchorders.status<>'Completed'
           AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

                $ErrMsg = _('The order details for this product cannot be retrieved because');
                $PurchResult = mysqli_query($conn, $sql);

                $PurchRow = mysqli_fetch_array($PurchResult);
                if ($PurchRow[0] != null) {
                    $PurchQty =  $PurchRow[0];
                } else {
                    $PurchQty = 0;
                }
                $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
          FROM woitems
          WHERE stockid='" . $myrow['stockid'] . "'";
                $ErrMsg = _('The order details for this product cannot be retrieved because');
                $WoResult = mysqli_query($conn, $sql);

                $WoRow = mysqli_fetch_array($WoResult);
                if ($WoRow[0] != null) {
                    $WoQty =  $WoRow[0];
                } else {
                    $WoQty = 0;
                }
                $OnOrder = $PurchQty + $WoQty;
                $data[$i]['OnOrder'] = "$OnOrder";

                // for available
                $Available = $myrow['qohand'] - $DemandQty + $OnOrder;
                $data[$i]['Available'] = $Available;


                $i++;
            }


            // Initialize an empty array to store JSON strings
            $jsonStrings = array();

            // Loop through each subarray and convert to JSON string
            foreach ($data as $subArray) {
                // Convert subarray to JSON string
                $jsonString = json_encode($subArray);
                if ($jsonString) {
                    // Add JSON string to the array
                    $jsonStrings[] = $jsonString;
                }
            }

            // Combine JSON strings with commas
            $result = implode(',', $jsonStrings);

            // Output the result
            echo '[' . $result . ']';


            return false;
        } else {

            $SQL = "SELECT stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.mnfCode,
                stockmaster.mnfpno,
                stockmaster.mbflag,
                stockmaster.discontinued,
                locstock.quantity AS qohand,
                stockmaster.units,
                stockmaster.decimalplaces
                FROM stockmaster INNER JOIN locstock
                ON stockmaster.stockid=locstock.stockid
                WHERE brand='" . $brand . "'
                AND categoryid='" . $StockCat . "'
                and locstock.loccode = '" . $subStore . "'
                AND stockmaster.stockid NOT LIKE '%\t%'
            
            GROUP BY stockmaster.stockid,
                    stockmaster.description,
                    stockmaster.longdescription,
                    stockmaster.units,
                    stockmaster.mbflag,
                    stockmaster.discontinued,
                    stockmaster.decimalplaces
                ORDER BY locstock.quantity desc";

            $result = mysqli_query($conn, $SQL);
            $results = array();
            $ItemData = array();

            $i = 0;
            while ($myrow = mysqli_fetch_array($result)) {

                $data[$i]['Code'] = $myrow['stockid'];
                $data[$i]['Description'] = $myrow['description'];
                $data[$i]['QOH'] = $myrow['qohand'];
                // for OnDemand
                $QOHSQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            FROM salesorderdetails INNER JOIN salesorders
            ON salesorders.orderno = salesorderdetails.orderno
            WHERE salesorders.fromstkloc='" . $subStore . "'
            AND salesorderdetails.completed=0
            AND salesorders.quotation=0
            AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
                $DemandResult =  mysqli_query($conn, $QOHSQL);
                $DemandRow = mysqli_fetch_array($DemandResult);
                if ($DemandRow[0] != null) {
                    $DemandQty =  $DemandRow[0];
                } else {
                    $DemandQty = '0';
                }
                $data[$i]['OnDemand'] = $DemandQty;

                // Onorder Value
                $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
            FROM purchorderdetails LEFT JOIN purchorders
               ON purchorderdetails.orderno=purchorders.orderno
            WHERE purchorderdetails.completed=0
            AND purchorders.status<>'Cancelled'
            AND purchorders.status<>'Rejected'
            AND purchorders.status<>'Completed'
           AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

                $ErrMsg = _('The order details for this product cannot be retrieved because');
                $PurchResult = mysqli_query($conn, $sql);

                $PurchRow = mysqli_fetch_array($PurchResult);
                if ($PurchRow[0] != null) {
                    $PurchQty =  $PurchRow[0];
                } else {
                    $PurchQty = 0;
                }
                $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
          FROM woitems
          WHERE stockid='" . $myrow['stockid'] . "'";
                $ErrMsg = _('The order details for this product cannot be retrieved because');
                $WoResult = mysqli_query($conn, $sql);

                $WoRow = mysqli_fetch_array($WoResult);
                if ($WoRow[0] != null) {
                    $WoQty =  $WoRow[0];
                } else {
                    $WoQty = 0;
                }
                $OnOrder = $PurchQty + $WoQty;
                $data[$i]['OnOrder'] = "$OnOrder";

                // for available
                $Available = $myrow['qohand'] - $DemandQty + $OnOrder;
                $data[$i]['Available'] = $Available;


                $i++;
            }


            // Initialize an empty array to store JSON strings
            $jsonStrings = array();

            // Loop through each subarray and convert to JSON string
            foreach ($data as $subArray) {
                // Convert subarray to JSON string
                $jsonString = json_encode($subArray);
                if ($jsonString) {
                    // Add JSON string to the array
                    $jsonStrings[] = $jsonString;
                }
            }

            // Combine JSON strings with commas
            $result = implode(',', $jsonStrings);

            // Output the result
            echo '[' . $result . ']';


            return false;
        }
    }
} elseif (isset($StockCode)) {
    $StockCode = mb_strtoupper($StockCode);

    $SQLA = "SELECT stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.mnfCode,
                stockmaster.mnfpno,
                stockmaster.mbflag,
                stockmaster.discontinued,
                locstock.quantity AS qohand,
                stockmaster.units,
                stockmaster.decimalplaces
            FROM stockmaster INNER JOIN locstock
            ON stockmaster.stockid=locstock.stockid
            WHERE (stockmaster.stockid  LIKE  '%" . $SearchString2 . "%'
            or stockmaster.mnfCode LIKE  '%" . $SearchString2 . "%')
            and locstock.loccode = '" . $subStore . "'
            AND stockmaster.stockid NOT LIKE '%\t%'
            GROUP BY stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.units,
                stockmaster.mbflag,
                stockmaster.discontinued,
                stockmaster.decimalplaces
            ORDER BY locstock.quantity desc";

    $result = mysqli_query($conn, $SQLA);
    $results = array();
    $ItemData = array();

    $i = 0;
    while ($myrow = mysqli_fetch_array($result)) {

        $data[$i]['Code'] = $myrow['stockid'];
        $data[$i]['Description'] = $myrow['description'];
        $data[$i]['QOH'] = $myrow['qohand'];
        // for OnDemand
        $QOHSQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            FROM salesorderdetails INNER JOIN salesorders
            ON salesorders.orderno = salesorderdetails.orderno
            WHERE salesorders.fromstkloc='" . $subStore . "'
            AND salesorderdetails.completed=0
            AND salesorders.quotation=0
            AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
        $DemandResult =  mysqli_query($conn, $QOHSQL);
        $DemandRow = mysqli_fetch_array($DemandResult);
        if ($DemandRow[0] != null) {
            $DemandQty =  $DemandRow[0];
        } else {
            $DemandQty = '0';
        }
        $data[$i]['OnDemand'] = $DemandQty;

        // Onorder Value
        $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
            FROM purchorderdetails LEFT JOIN purchorders
               ON purchorderdetails.orderno=purchorders.orderno
            WHERE purchorderdetails.completed=0
            AND purchorders.status<>'Cancelled'
            AND purchorders.status<>'Rejected'
            AND purchorders.status<>'Completed'
           AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

        $ErrMsg = _('The order details for this product cannot be retrieved because');
        $PurchResult = mysqli_query($conn, $sql);

        $PurchRow = mysqli_fetch_array($PurchResult);
        if ($PurchRow[0] != null) {
            $PurchQty =  $PurchRow[0];
        } else {
            $PurchQty = 0;
        }
        $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
          FROM woitems
          WHERE stockid='" . $myrow['stockid'] . "'";
        $ErrMsg = _('The order details for this product cannot be retrieved because');
        $WoResult = mysqli_query($conn, $sql);

        $WoRow = mysqli_fetch_array($WoResult);
        if ($WoRow[0] != null) {
            $WoQty =  $WoRow[0];
        } else {
            $WoQty = 0;
        }
        $OnOrder = $PurchQty + $WoQty;
        $data[$i]['OnOrder'] = "$OnOrder";

        // for available
        $Available = $myrow['qohand'] - $DemandQty + $OnOrder;
        $data[$i]['Available'] = $Available;


        $i++;
    }


    // Initialize an empty array to store JSON strings
    $jsonStrings = array();

    // Loop through each subarray and convert to JSON string
    foreach ($data as $subArray) {
        // Convert subarray to JSON string
        $jsonString = json_encode($subArray);
        if ($jsonString) {
            // Add JSON string to the array
            $jsonStrings[] = $jsonString;
        }
    }

    // Combine JSON strings with commas
    $result = implode(',', $jsonStrings);

    // Output the result
    echo '[' . $result . ']';


    return false;
}
if ($StockCat == 'All') {
    $SQLA = "SELECT stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.mnfCode,
                stockmaster.mnfpno,
                stockmaster.mbflag,
                stockmaster.discontinued,
                locstock.quantity AS qohand,
                stockmaster.units,
                stockmaster.decimalplaces
            FROM stockmaster INNER JOIN locstock
            ON stockmaster.stockid=locstock.stockid
            WHERE brand='" . $brand . "'
            and locstock.loccode = '" . $subStore . "'
            AND stockmaster.stockid NOT LIKE '%\t%'
            
            GROUP BY stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.units,
                stockmaster.mbflag,
                stockmaster.discontinued,
                stockmaster.decimalplaces
            ORDER BY locstock.quantity desc";

    $result = mysqli_query($conn, $SQLA);
    $results = array();
    $ItemData = array();

    $i = 0;
    while ($myrow = mysqli_fetch_array($result)) {

        $data[$i]['Code'] = $myrow['stockid'];
        $data[$i]['Description'] = $myrow['description'];
        $data[$i]['QOH'] = $myrow['qohand'];
        // for OnDemand
        $QOHSQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            FROM salesorderdetails INNER JOIN salesorders
            ON salesorders.orderno = salesorderdetails.orderno
            WHERE salesorders.fromstkloc='" . $subStore . "'
            AND salesorderdetails.completed=0
            AND salesorders.quotation=0
            AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
        $DemandResult =  mysqli_query($conn, $QOHSQL);
        $DemandRow = mysqli_fetch_array($DemandResult);
        if ($DemandRow[0] != null) {
            $DemandQty =  $DemandRow[0];
        } else {
            $DemandQty = '0';
        }
        $data[$i]['OnDemand'] = $DemandQty;

        // Onorder Value
        $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
            FROM purchorderdetails LEFT JOIN purchorders
               ON purchorderdetails.orderno=purchorders.orderno
            WHERE purchorderdetails.completed=0
            AND purchorders.status<>'Cancelled'
            AND purchorders.status<>'Rejected'
            AND purchorders.status<>'Completed'
           AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

        $ErrMsg = _('The order details for this product cannot be retrieved because');
        $PurchResult = mysqli_query($conn, $sql);

        $PurchRow = mysqli_fetch_array($PurchResult);
        if ($PurchRow[0] != null) {
            $PurchQty =  $PurchRow[0];
        } else {
            $PurchQty = 0;
        }
        $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
          FROM woitems
          WHERE stockid='" . $myrow['stockid'] . "'";
        $ErrMsg = _('The order details for this product cannot be retrieved because');
        $WoResult = mysqli_query($conn, $sql);

        $WoRow = mysqli_fetch_array($WoResult);
        if ($WoRow[0] != null) {
            $WoQty =  $WoRow[0];
        } else {
            $WoQty = 0;
        }
        $OnOrder = $PurchQty + $WoQty;
        $data[$i]['OnOrder'] = "$OnOrder";

        // for available
        $Available = $myrow['qohand'] - $DemandQty + $OnOrder;
        $data[$i]['Available'] = $Available;


        $i++;
    }


    // Initialize an empty array to store JSON strings
    $jsonStrings = array();

    // Loop through each subarray and convert to JSON string
    foreach ($data as $subArray) {
        // Convert subarray to JSON string
        $jsonString = json_encode($subArray);
        if ($jsonString) {
            // Add JSON string to the array
            $jsonStrings[] = $jsonString;
        }
    }

    // Combine JSON strings with commas
    $result = implode(',', $jsonStrings);

    // Output the result
    echo '[' . $result . ']';


    return false;
} else {
    $SQL = "SELECT stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.mnfCode,
                stockmaster.mnfpno,
                stockmaster.mbflag,
                stockmaster.discontinued,
                locstock.quantity AS qohand,
                stockmaster.units,
                stockmaster.decimalplaces
            FROM stockmaster INNER JOIN locstock
            ON stockmaster.stockid=locstock.stockid
            WHERE categoryid like'" . $StockCat . "'
            AND brand like '" . $brand . "'
            and locstock.loccode = '" . $subStore . "'
            AND stockmaster.stockid NOT LIKE '%\t%'
            GROUP BY stockmaster.stockid,
                stockmaster.description,
                stockmaster.longdescription,
                stockmaster.units,
                stockmaster.mbflag,
                stockmaster.discontinued,
                stockmaster.decimalplaces
            ORDER BY locstock.quantity desc";

    $result = mysqli_query($conn, $SQL);
    $results = array();
    $ItemData = array();

    $i = 0;
    while ($myrow = mysqli_fetch_array($result)) {

        $data[$i]['Code'] = $myrow['stockid'];
        $data[$i]['Description'] = $myrow['description'];
        $data[$i]['QOH'] = $myrow['qohand'];
        // for OnDemand
        $QOHSQL = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
            FROM salesorderdetails INNER JOIN salesorders
            ON salesorders.orderno = salesorderdetails.orderno
            WHERE salesorders.fromstkloc='" . $subStore . "'
            AND salesorderdetails.completed=0
            AND salesorders.quotation=0
            AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
        $DemandResult =  mysqli_query($conn, $QOHSQL);
        $DemandRow = mysqli_fetch_array($DemandResult);
        if ($DemandRow[0] != null) {
            $DemandQty =  $DemandRow[0];
        } else {
            $DemandQty = '0';
        }
        $data[$i]['OnDemand'] = $DemandQty;

        // Onorder Value
        $sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
            FROM purchorderdetails LEFT JOIN purchorders
               ON purchorderdetails.orderno=purchorders.orderno
            WHERE purchorderdetails.completed=0
            AND purchorders.status<>'Cancelled'
            AND purchorders.status<>'Rejected'
            AND purchorders.status<>'Completed'
           AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

        $ErrMsg = _('The order details for this product cannot be retrieved because');
        $PurchResult = mysqli_query($conn, $sql);

        $PurchRow = mysqli_fetch_array($PurchResult);
        if ($PurchRow[0] != null) {
            $PurchQty =  $PurchRow[0];
        } else {
            $PurchQty = 0;
        }
        $sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
          FROM woitems
          WHERE stockid='" . $myrow['stockid'] . "'";
        $ErrMsg = _('The order details for this product cannot be retrieved because');
        $WoResult = mysqli_query($conn, $sql);

        $WoRow = mysqli_fetch_array($WoResult);
        if ($WoRow[0] != null) {
            $WoQty =  $WoRow[0];
        } else {
            $WoQty = 0;
        }
        $OnOrder = $PurchQty + $WoQty;
        $data[$i]['OnOrder'] = "$OnOrder";

        // for available
        $Available = $myrow['qohand'];
        $data[$i]['Available'] = $Available;


        $i++;
    }


    // Initialize an empty array to store JSON strings
    $jsonStrings = array();

    // Loop through each subarray and convert to JSON string
    foreach ($data as $subArray) {
        // Convert subarray to JSON string
        $jsonString = json_encode($subArray);
        if ($jsonString) {
            // Add JSON string to the array
            $jsonStrings[] = $jsonString;
        }
    }

    // Combine JSON strings with commas
    $result = implode(',', $jsonStrings);

    // Output the result
    echo '[' . $result . ']';


    return false;
}
