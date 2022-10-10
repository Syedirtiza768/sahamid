<?php 

	$PathPrefix='../../../../';
	include('../../../../includes/session.inc');
	include('../../../../includes/SQL_CommonFunctions.inc');

	$text = $_POST["comment"];
	$parchino = $_POST['parchino'];
	$text = htmlspecialchars($text);

	$hasAudio = 0;
	$audioPath = "";

	if(isset($_FILES['audio'])){
		
		$hasAudio = 1;

		$audioPath = "../audiocomments/".Date("dmYHis").".wav";
		
		move_uploaded_file($_FILES["audio"]['tmp_name'], $audioPath);

	}

	$SQL = "INSERT INTO bpcomments (parchino,comment,author,hasAudio,audioPath,created_at)
			VALUES('".$parchino."','".$text."','".$_SESSION['UsersRealName']."',
			'".$hasAudio."','".$audioPath."','".date('Y-m-d H:i:s')."')";

	DB_query($SQL, $db);

	$response = [];
	$response['hasAudio'] = $hasAudio;
	$response['username'] = $_SESSION['UsersRealName'];
	$response['audioPath'] = $audioPath;
	$response['comment'] = $text;
	$response['time'] = date('d/m/Y h:i A',time());
	echo json_encode($response);
	return;
