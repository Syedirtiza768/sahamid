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

	$SQL = "INSERT INTO salescase_permissions (user,can_access) VALUES ('".$user."','".$can_access."')";
	$res = mysqli_query($db, $SQL);

	header('Location: editSalescasePermission.php?userid='. $user);