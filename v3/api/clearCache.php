<?php
	
	$AllowAnyone = true;

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	$SQL = "DELETE FROM cache WHERE id>0";
	DB_query($SQL,$db);

	header('Location: ' . $_SERVER['HTTP_REFERER']);
	return;
