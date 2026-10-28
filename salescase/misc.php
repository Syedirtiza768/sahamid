<?php
include_once __DIR__ . '/../db_config.php';


	function createDBConnection(){

		$db = mysqli_connect(SAHAMID_DB_HOST, SAHAMID_DB_USER, SAHAMID_DB_PASSWORD,'sahamid');
		if ($db) {
			mysqli_set_charset($db, 'utf8');
		}
		return $db;

	}
	
	function generateRandom(){

		$firstRand = rand();
		$secondRand = rand();
		$thirdRand = rand($firstRand, $secondRand);

		return $thirdRand/rand(1,200);

	}
    function utf8_encode_deep(&$input) {
        if (is_string($input)) {
            $input = utf8_encode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
                utf8_encode_deep($value);
            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                utf8_encode_deep($input->$var);
            }
        }
    }

?>