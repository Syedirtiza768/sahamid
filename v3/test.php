<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 8/28/2019
 * Time: 12:00 PM
 */
$conn=mysqli_connect('localhost','irtiza','netetech321','sahamid');
$SQL="CREATE VIEW psis AS SELECT stockmaster.stockid,loccode,MIN(newqoh) as newqoh 
        FROM stockmaster LEFT OUTER JOIN stockmoves ON stockmaster.stockid=stockmoves.stockid  WHERE trandate='2018-09-02'
        AND loccode IN ('HO','MT','SR')  GROUP BY stockid,loccode";
mysqli_query($conn, $SQL);
  $SQL="SELECT stockid, SUM(newqoh) FROM psis GROUP BY stockid";
 $res = mysqli_query($conn, $SQL);
  mysqli_num_rows($res);



$response1 = [];
while($row = mysqli_fetch_assoc($res)){
    $stkcde=$row['stockid'];
    $response1[$stkcde] = $row;
}

$show_json = json_encode($response1 , JSON_FORCE_OBJECT);
if ( json_last_error_msg()=="Malformed UTF-8 characters, possibly incorrectly encoded" ) {
    $show_json = json_encode($response1, JSON_PARTIAL_OUTPUT_ON_ERROR );
}
if ( $show_json !== false ) {
    echo($show_json);
} else {
    die("json_encode fail: " . json_last_error_msg());
}

$SQL="DROP VIEW psis";
mysqli_query($conn, $SQL);