<?php
include_once __DIR__ . '/../../db_config.php'; 
$table = "permissions";
$primaryKey = "id";
$columns = array(
	array('db' => 'id', 'dt'=>0),
	array('db' => 'name', 'dt'=>1),
	array('db' => 'description', 'dt'=>2),
	array('db' => 'slug', 'dt'=>3)
);
	$conn = array(
		'user' => 'irtiza',
		'pass' => SAHAMID_DB_PASSWORD,
		'db' => 'sahamid',
		'host' => 'localhost'
	);
	require ('../../ssp.class.php');
	echo json_encode(
		SSP::simple($_GET, $conn,$table, $primaryKey, $columns));
?>