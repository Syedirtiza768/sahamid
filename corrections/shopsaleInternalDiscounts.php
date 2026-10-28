<?php
include_once __DIR__ . '/../db_config.php';
$conn=mysqli_connect(SAHAMID_DB_HOST, SAHAMID_DB_USER, SAHAMID_DB_PASSWORD,'sahamid');
$SQL="SELECT * FROM shopsalesitems group by lineno";
mysqli_query($conn,$SQL);
while()





?>