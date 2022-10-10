<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 9/5/2019
 * Time: 6:10 PM
 */

$db=mysqli_connect('localhost','irtiza','netetech321','sahamid');

echo $SQL = "SELECT * FROM stockmoves WHERE type IN (602,513,899)";

$res=mysqli_query($db,$SQL);
$count=0;
while($row=mysqli_fetch_assoc($res))
    {
        print_r($row);

        $count++;
        $stockid=$row['stockid'];
        $loccode=$row['loccode'];
        $qty=$row['qty'];
        $type=$row['type'];
        $stkmoveno=$row['stkmoveno'];


 echo       $SQLA = "SELECT * FROM stockmoves WHERE stockid='$stockid' AND loccode='$loccode' AND stkmoveno<$stkmoveno AND type IN (17,111,510,511,512,514,750)
                ORDER BY stkmoveno DESC LIMIT 1";

        $resA=mysqli_query($db,$SQLA);
        if (mysqli_num_rows($resA)>0) {
            $rowA = mysqli_fetch_assoc($resA);
            $qtyprior = $rowA['newqoh'];
        } else $qtyprior = 0;
        if($type==602)
            $newqoh=$qtyprior;
        if($type==513)
            $newqoh=$qtyprior-$qty;
        if($type==899)
            $newqoh=$qtyprior;

  echo      $SQLB = "UPDATE stockmoves SET newqoh=$newqoh WHERE stkmoveno=$stkmoveno";

        mysqli_query($db,$SQLB);


    }
    echo "<h1>DONE</h1>";

    echo $count;

?>