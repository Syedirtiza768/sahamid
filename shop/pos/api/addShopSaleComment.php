<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	if(!isset($_POST['orderno']) || !isset($_POST['comment'])){

		echo json_encode([

				'status' 	=> 'error',
				'message' 	=> 'Missing Parms...'

			]);
		return;

	}

	$orderno 	= $_POST['orderno'];
	$comment 	= htmlentities($_POST['comment']);
	$username 	= $_SESSION['UsersRealName'];
	$userID 	= $_SESSION['UserID'];

	$SQL = "INSERT INTO shopsalecomments(orderno,comment,userid,username)
			VALUES ($orderno,'$comment','$userID','$username')";
	DB_query($SQL, $db);

	echo json_encode([

			'status' 	=> 'success',
			'comment' 	=> [

				'comment' => $comment,
				'username' => $username,
				'time' => date('d/m/Y h:i A')

			]

		]);
	return;