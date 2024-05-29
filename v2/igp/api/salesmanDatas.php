<?php
include('../../config1.php');
$Salesman = $_POST['salesmans'];
$sql = "select salescaseref,salescaseindex from salescase WHERE salesman = '" . $Salesman . "' AND closed = '0' ";
$result = mysqli_query($conn, $sql); 
$results = [];
while ($myrow = mysqli_fetch_array($result)){
    $results[] = $myrow['salescaseref'];
}
echo json_encode($results);