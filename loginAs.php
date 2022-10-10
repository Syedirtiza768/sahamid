<?php 

	include('includes/session.inc');

	if(!isset($_SESSION['orignalUserID'])){
		$_SESSION['orignalUserID'] = $_SESSION['UserID'];
		$_SESSION['orignalAccessLevel'] = $_SESSION['AccessLevel'];
	}

	if($_SESSION['orignalAccessLevel'] != 8 && $_SESSION['AccessLevel'] != 8 && $_SESSION['orignalAccessLevel'] != 10 && $_SESSION['AccessLevel'] != 10){
		header("Location: /sahamid/");
		return;
	}

	if(!isset($_GET['UserID']) || $_GET['UserID'] == ''){
		header("Location: /sahamid/");
		return;
	}

	$UserID = $_GET['UserID'];

	$SQL = "SELECT * FROM www_users WHERE userid='$UserID'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){
		header("Location: /sahamid/");
		return;
	}

	$myrow = mysqli_fetch_assoc($res);

	if ($myrow['blocked']==1){
		header("Location: /sahamid/");
		return;
	}

	$_SESSION['UserID'] = $myrow['userid'];
	$_SESSION['AttemptsCounter'] = 0;
	$_SESSION['AccessLevel'] = $myrow['fullaccess'];
	$_SESSION['CustomerID'] = $myrow['customerid'];
	$_SESSION['UserBranch'] = $myrow['branchcode'];
	$_SESSION['DefaultPageSize'] = $myrow['pagesize'];
	$_SESSION['UserStockLocation'] = $myrow['defaultlocation'];
	$_SESSION['UserEmail'] = $myrow['email'];
	$_SESSION['ModulesEnabled'] = explode(",", $myrow['modulesallowed']);
	$_SESSION['UsersRealName'] = $myrow['realname'];
	$_SESSION['Theme'] = $myrow['theme'];
	$_SESSION['Language'] = $myrow['language'];
	$_SESSION['SalesmanLogin'] = $myrow['salesman'];
	$_SESSION['CanCreateTender'] = $myrow['cancreatetender'];
	$_SESSION['AllowedDepartment'] = $myrow['department'];

	if (isset($myrow['pdflanguage'])) {
		$_SESSION['PDFLanguage'] = $myrow['pdflanguage'];
	} else {
		$_SESSION['PDFLanguage'] = '0'; //default to latin western languages
	}

	if ($myrow['displayrecordsmax'] > 0) {
		$_SESSION['DisplayRecordsMax'] = $myrow['displayrecordsmax'];
	} else {
		$_SESSION['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];  // default comes from config.php
	}

	$sql = "SELECT tokenid
			FROM securitygroups
			WHERE secroleid =  '" . $_SESSION['AccessLevel'] . "'";
	$Sec_Result = DB_query($sql, $db);
	$_SESSION['AllowedPageSecurityTokens'] = array();
	if (DB_num_rows($Sec_Result)==0){
		header("Location: /sahamid/");
		return;
	} else {
		$i=0;
		$UserIsSysAdmin = FALSE;
		while ($myrow = DB_fetch_row($Sec_Result)){
			if ($myrow[0] == 15){
				$UserIsSysAdmin = TRUE;
			}
			$_SESSION['AllowedPageSecurityTokens'][$i] = $myrow[0];
			$i++;
		}
	}

	header("Location: /sahamid/");