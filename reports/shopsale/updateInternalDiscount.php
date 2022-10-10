<?php
$AllowAnyone = true;

$PathPrefix='../../';
include_once('../../includes/session.inc');
include_once('../../includes/SQL_CommonFunctions.inc');
echo "Hello";
$SQL="SELECT *,SUM(rate*quantity) as internalAmount FROM shopsalesitems GROUP  BY lineno";
$result=mysqli_query($db,$SQL);
while($row=mysqli_fetch_assoc($result))
{

    $lineno=$row['lineno'];
    $orderNo=$row['orderno'];
    $SQL="SELECT * FROM shopsale WHERE orderno='$orderNo'";
    $res = mysqli_query($db, $SQL);
    $shopSale=mysqli_fetch_assoc($res);
    $SQL = "SELECT SUM(quantity * price) as total FROM shopsalelines WHERE orderno='$orderNo'";
    $res = mysqli_query($db, $SQL);

    $subTotal = mysqli_fetch_assoc($res)['total'];

  echo  $discount = $shopSale['discount'];
 echo   $discountPKR = $shopSale['discountPKR'];

    $total = $subTotal;

    if($discount != 0){
        $total = $total * (1 - $discount/100);
    }

    if($discountPKR != 0){
        $total -= $discountPKR;
    }
    $overallDiscount=$subTotal-$total;
    $SQL = "SELECT (quantity * price) as total,quantity FROM shopsalelines WHERE id='$lineno'";
    $res = mysqli_query($db, $SQL);

    $line=mysqli_fetch_assoc($res);
    $lineSubTotal = $line['total'];
    $lineQty=$line['quantity'];
    if($subTotal==0)
        continue;
    else
           $lineTotal=$lineSubTotal-($overallDiscount*$lineSubTotal/$subTotal);
     $internalAmount=$row['internalAmount']*$lineQty;
    if($internalAmount==0)
        continue;
    else
        $discount=(1-$lineTotal/$internalAmount)*100;

    $SQL="UPDATE shopsalesitems SET discountpercent=$discount WHERE lineno=$lineno ";
    mysqli_query($db,$SQL);

}





?>