<?php

	function createDBConnection(){
	
			return mysqli_connect('localhost','irtiza','netetech321','sahamid');

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

	function lineExists($lineno){

		$db = createDBConnection();

		$SQL = "SELECT * FROM salesorderlinesip WHERE lineindex='".$lineno."'";

		$result = mysqli_query($db, $SQL);

		if(mysqli_num_rows($result) == 1)
			return true;
		return false;

	}

	function isIncorrectSalesCase($salescaseref){

		$db = createDBConnection();

		$SQL = "SELECT * FROM salescase 
				WHERE salescaseref = \"".$salescaseref."\"";

		$result = mysqli_query($db, $SQL);

		if(mysqli_num_rows($result) > 0){

			$row = mysqli_fetch_assoc($result);

			if($row['closed'] == 0){

				return false;

			}

			return true;

		}

		return true;
		
	}

	function returnErrorResult($RootPath,$salescaseref,$message){

		header('Location: '.$RootPath."/salescase.php?salescaseref=".$salescaseref."&error=".$message);

	}

	function returnSuccessResult($RootPath,$salescaseref,$message){

		header('Location: '.$RootPath."/salescase.php?salescaseref=".$salescaseref."&success=".$message);

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
	
	function utf8_replace_array(&$input) {
		if (is_string($input)) {
			$input = utf8_encode($input);
			$input = str_replace("'","\'",$input);
		} else if (is_array($input)) {
			foreach ($input as &$value) {
				utf8_replace_array($value);
			}
			
			unset($value);
		} else if (is_object($input)) {
			$vars = array_keys(get_object_vars($input));
			
			foreach ($vars as $var) {
				utf8_replace_array(str_replace("'","\'",$input->$var));
			}
		}
	}

	function reorderlines($orderno, $db){

		$SQL = "SELECT * FROM dclines WHERE orderno='".$orderno."'";

		$result = mysqli_query($db, $SQL);

		$newLineIndex = 0;

		while($row = mysqli_fetch_assoc($result)){

			//update line index
			$SQL = "UPDATE dclines SET lineno='".$newLineIndex."'
					WHERE lineindex='".$row['lineindex']."'";

			mysqli_query($db, $SQL);

			//GET Line Options
			$SQL = "SELECT * FROM dcoptions 
					WHERE orderno='".$orderno."'
					AND lineno='".$row['lineno']."'";

			$lineOptionsResult = mysqli_query($db, $SQL);

			while($optionsRow = mysqli_fetch_assoc($lineOptionsResult)){

				//update items line index for option
				$SQL = "UPDATE dcdetails SET orderlineno='".$newLineIndex."'
						WHERE orderno='".$orderno."'
						AND orderlineno='".$row['lineno']."'
						AND lineoptionno='".$optionsRow['optionno']."'";

				mysqli_query($db, $SQL);

			}

			//update line index for options under the line
			$SQL = "UPDATE dcoptions SET lineno='".$newLineIndex."'
					WHERE orderno='".$orderno."'
					AND lineno='".$row['lineno']."'";

			mysqli_query($db, $SQL);

			//increment new line index
			$newLineIndex += 1;

		}

	}

	function reorderoptions($orderno, $lineno, $db){

		//GET Line Options
		$SQL = "SELECT * FROM ocoptions 
				WHERE orderno='".$orderno."'
				AND lineno='".$lineno."'";

		$lineOptionsResult = mysqli_query($db, $SQL);

		$newOptionNo = 0;

		while($optionsRow = mysqli_fetch_assoc($lineOptionsResult)){

			//update items line index for option
			$SQL = "UPDATE ocdetails SET lineoptionno='".$newOptionNo."'
					WHERE orderno='".$orderno."'
					AND orderlineno='".$lineno."'
					AND lineoptionno='".$optionsRow['optionno']."'";

			mysqli_query($db, $SQL);

			//update otionno in ocoptions
			$SQL = "UPDATE ocoptions SET optionno='".$newOptionNo."'
					WHERE orderno='".$orderno."'
					AND optionno='".$optionsRow['optionno']."'
					AND lineno='".$lineno."'";

			mysqli_query($db, $SQL);

			$newOptionNo += 1;

		}

	}


?>