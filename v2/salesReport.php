<?php
include_once("../v2/config.php");
if (isset($_POST['clientID'])) {
    $row1 = [];
    $row2 = [];
    $row3 = [];
    $row4 = [];
    $row5 = [];
    
    $sql = "SELECT DISTINCT stockissuance.stockid,
    stockmaster.description,
    stockmaster.mnfCode,
    stockmaster.mnfpno,
    manufacturers.manufacturers_name,
    stockissuance.issued as issued,
    stockissuance.returned as returned,
    stockissuance.dc as dc,
    stockmaster.materialcost,
    stockissuance.salesperson,
    stockmaster.discount,
    stockissuance.issued*stockmaster.materialcost*(1-stockmaster.discount) as totalValue,
    stockmaster.decimalplaces,
    stockmaster.serialised,
    stockmaster.controlled,
    '' as salescaseref
FROM stockissuance,
    stockmaster,
    locations,
    manufacturers,
    stockcategory
WHERE stockissuance.stockid=stockmaster.stockid
        AND stockmaster.brand = manufacturers.manufacturers_id
    AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')							
        
AND stockmaster.brand like '%%'
AND stockmaster.categoryid = stockcategory.categoryid
AND stockissuance.issued>0
AND stockcategory.categorydescription like '%%'
AND stockissuance.salesperson='" . $_POST['clientID'] . "'

";

    $sql .= 'AND stockissuance.salesperson IN 
(SELECT can_access FROM cart_report_access WHERE user = "' . $_SESSION['UserID'] . '"' . ') 
';

    $res = mysqli_query($db, $sql);
    $SQLdiscount = "SELECT stkcode,AVG(discountpercent) as discount from dcdetails GROUP by stkcode";
    $resdiscount = DB_query($SQLdiscount, $db, $ErrMsg, $DbgMsg);
    $responsediscount = [];
    while ($rowdiscount = mysqli_fetch_assoc($resdiscount)) {
        $responsediscount[$rowdiscount['stkcode']] = $rowdiscount;
    }

    $response = [];
    $sum = 0;
    while ($row = mysqli_fetch_assoc($res)) {
        $response[$row['stockid']] = $row;
        if ($responsediscount[$row['stockid']]['discount'] > 0)
        $row['discount'] = round($responsediscount[$row['stockid']]['discount'] * 100, 2);
        else
        $row['discount'] = 50;
        $row['totalValue'] = $row['issued'] * $row['materialcost'] * (1 - ($row['discount'] / 100));
        $sum = $sum + $row['totalValue'];
        $row['totalValue'] = locale_number_format($row['totalValue']);
        $row['adjustedValue'] = locale_number_format($sum);
        $row5[] = $row;
    }
    $row4['adjustedValue'] = $sum;
    if (empty($row5)) {
        $merged_results = [];
    } else {
        $merged_results = $row5;
    }
    echo json_encode($merged_results);
    return;
}
?>