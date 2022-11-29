<?php
require_once './config.php';

$id = $_POST['pc_id'];
$sql = "UPDATE panel_costing SET closed = '1' WHERE id = '" . $id . "'";
if (mysqli_query($conn, $sql)) {
    echo "done";
}
