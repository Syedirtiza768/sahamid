<?php


	$PathPrefix='../';
    $AllowAnyone=true;
	include_once('../includes/session.inc');
	
	$NewRootPath = "/sahamid/";

	$salesPersonsToSwitch = [];
	$SQL = "SELECT www_users.realname,www_users.userid FROM salesman
			INNER JOIN www_users ON salesman.salesmanname = www_users.realname";
	$res = mysqli_query($db, $SQL);
	while ($row = mysqli_fetch_assoc($res)) {
		$salesPersonsToSwitch[] = $row;
	}
	
	function ec($toEcho){
		echo $toEcho;
		return;
	}

	function ecif($condition, $true, $false=""){
		echo ($condition ? $true:$false);
		return;
	}