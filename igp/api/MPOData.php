<?php
include('../../configg.php');
$Salesman = $_POST['salesmans'];
$sql = "SELECT * FROM `bazar_parchi` WHERE `on_behalf_of` = '" . $Salesman . "' AND `inprogress` = '1' ";
$result = mysqli_query($conn, $sql); 
$results = [];
while ($myrow = mysqli_fetch_array($result)){
    $results[] = $myrow['bazar_parchi'];
}
echo json_encode($results);