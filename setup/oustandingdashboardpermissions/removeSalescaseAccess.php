<?php
	
	$PathPrefix='../../';

	include('../../includes/SQL_CommonFunctions.inc');
	include('../../includes/session.inc');

	$user 		= $_GET['user'];
	$can_access = $_GET['can_access'];

	if(!isset($user) || !isset($can_access)){
		header("Location: indexpermission.php");
		return;
	}

	$SQL = "DELETE FROM salescase_permissions 
			WHERE user='".$user."'
			AND can_access='".$can_access."'";
	$res = mysqli_query($db, $SQL);

	header('Location: ' . $_SERVER['HTTP_REFERER']);