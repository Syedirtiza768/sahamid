<?php
include_once("../v2/config.php");
if (isset($_POST['clientID']) && isset($_POST['Item'])) {
    $arr = [];
    $sql = "select salescaseref,quantity from ogpsalescaseref WHERE salesman = '"  . $_POST['clientID'] . "' AND stockid = '"  . $_POST['Item'] . "' ";
    $result = mysqli_query($db, $sql);
    if ($result != NULL) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arr['salescase'][] = $row['salescaseref'];
            $arr['salescase'][] = $row['quantity'];
        }
    }
    $sql = "select csv,quantity from ogpcsvref WHERE salesman = '"  . $_POST['clientID'] . "' AND stockid = '"  . $_POST['Item'] . "' ";
    $result = mysqli_query($db, $sql);
    if ($result != NULL) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arr['csv'][] = $row['csv'];
            $arr['csv'][] = $row['quantity'];
        }
    }
    $sql = "select crv,quantity from ogpcrvref WHERE salesman = '"  . $_POST['clientID'] . "' AND stockid = '"  . $_POST['Item'] . "' ";
    $result = mysqli_query($db, $sql);
    if ($result != NULL) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arr['crv'][] = $row['crv'];
            $arr['crv'][] = $row['quantity'];
        }
    }
    $sql = "select mpo,quantity from ogpmporef WHERE salesman = '"  . $_POST['clientID'] . "' AND stockid = '"  . $_POST['Item'] . "' ";
    $result = mysqli_query($db, $sql);
    if ($result != NULL) {
        while ($row = mysqli_fetch_assoc($result)) {
            $arr['mpo'][] = $row['mpo'];
            $arr['mpo'][] = $row['quantity'];
        }
    }
    echo json_encode($arr);
    return;
}
