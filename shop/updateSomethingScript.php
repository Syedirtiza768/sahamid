<?php 
return;
	$PathPrefix='../';
	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');
	
	$SQL = "SELECT realname FROM www_users WHERE defaultlocation='SR'";
	$res = mysqli_query($db, $SQL);
	
	$users = [];
	
	while($row = mysqli_fetch_assoc($res))	$users[] = $row['realname'];
	
	$SQL = "SELECT * FROM stockissuance WHERE salesperson IN ('".implode("','",$users)."') AND issued != 0";
	$res = mysqli_query($db, $SQL);
	
	echo mysqli_num_rows($res)." Enteries Will Be Effected.<br>";
	
	$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
	
	$count = 0;
	while($row = mysqli_fetch_assoc($res)){
		
		$salesman  = $row['salesperson'];
		$newIssued = 0;
		$adjusted  = $row['issued'];
		$stockID   = $row['stockid'];
		
		echo "<br><br>Updating<br>";
		
		echo $SQL = "UPDATE `stockissuance` SET issued='".$newIssued."',adjusted='".$adjusted."' 
				WHERE salesperson='".$salesman."'
				AND stockid='".$stockID."'";
		DB_query($SQL,$db,'','',false,false);
		
		echo "<br><br>Inserting Stock Movement...<br>";
		
		echo $SQL = "INSERT IGNORE INTO stockmoves(stockid,type,transno,loccode,trandate,reference,qty,prd,newqoh)
				VALUES ('".$stockID."','899','1','SR','".Date('Y-m-d')."','Mass Adjustment',
				'".$adjusted."','".$PeriodNo."','".$newIssued."')";
		DB_query($SQL,$db,'','',false,false);
		
		$count++;
		
	}
	
	echo "<br><br>$count Records Updated!!!";