<?php

	function createDBConnection(){
	
			$db = mysqli_connect('localhost','irtiza','netetech321','sahamid');
			mysqli_set_charset($db,"utf8");
			return $db;
	}

	function closeDBConnection($db){

		mysqli_close($db);

	}

	function validHeaders(){

		session_start();
		
		if(isset($_SESSION['UsersRealName'])){
			return true;
		}
		return false;

	}

	function generateRandom(){

		$firstRand = rand();
		$secondRand = rand();
		$thirdRand = rand($firstRand, $secondRand);

		return $thirdRand/rand(1,200);

	}

	function isNewQuotation(){

		return ((isset($_GET['NewOrder'])) && $_GET['NewOrder'] == 'Yes');
	
	}
	
	function checkParms(){
		
		if(isset($_GET['salescaseref']) && isset($_GET['selectedcustomer'])
			&& isset($_GET['DebtorNo']) && isset($_GET['BranchCode'])){
			
			return true;

		}else{
			return false;
		}

	}

	function lineExists($lineno){

		$db = createDBConnection();

		$SQL = "SELECT * FROM salesorderlinesip WHERE lineindex='".$lineno."'";

		$result = mysqli_query($db, $SQL);

		if(mysqli_num_rows($result) == 1)
			return true;
		return false;

	}

	function isInValidSalesCase($salescaseref,$orderno){

		$db = createDBConnection();

		$SQL = "SELECT count(*) as count FROM salesordersip 
					WHERE salescaseref='".$salescaseref."' 
					AND orderno='".$orderno."'";

		$result = mysqli_query($db, $SQL);

		$row = mysqli_fetch_assoc($result);

		closeDBConnection($db);

		if($row['count'] != 1)
			return true;
		else
			return false;

	}

	function isIncorrectSalesCase(){

		$db = createDBConnection();

		$SQL = "SELECT count(*) as count FROM salescase 
				WHERE salescaseref = \"".$_GET['salescaseref']."\"";

		$result = mysqli_query($db, $SQL);

		$row = mysqli_fetch_assoc($result);

		closeDBConnection($db);

		if($row['count'] == 1)
			return false;
		return true;

	}

	function isIncorrectSalesCasePOST($salescaseref){

		$db = createDBConnection();

		$SQL = "SELECT count(*) as count FROM salescase 
				WHERE salescaseref = \"".$salescaseref."\"";

		$result = mysqli_query($db, $SQL);

		$row = mysqli_fetch_assoc($result);

		closeDBConnection($db);

		if($row['count'] == 1)
			return false;
		return true;

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
	
	function my_json_encode($arr){
        //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
        array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
        return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
	}


?>