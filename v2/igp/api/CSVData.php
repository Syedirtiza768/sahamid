<?php
include('../../config1.php');
$Salesman = $_POST['salesmans'];
$sql = "select orderno from shopsale WHERE salesman = '" . $Salesman . "' AND 
payment = 'csv'  AND complete = '0' ";
$result = mysqli_query($conn, $sql); 
$results = [];
while ($myrow = mysqli_fetch_array($result)){
    $results[] = $myrow['orderno'];
}
echo json_encode($results);