<?php

	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$respond = function($status, $message) {
		echo json_encode([
			'status' => $status,
			'message' => $message
		]);
		exit;
	};

	$mpino = isset($_GET['mpino']) ? trim($_GET['mpino']) : '';
	if($mpino === '' || !preg_match('/^\d+$/', $mpino)){
		$respond('error', 'A valid market slip number is required');
	}

	$runQuery = function($SQL) use ($db) {
		$result = DB_query($SQL, $db, '', '', false, false);
		if($result === false){
			throw new Exception('The return could not be saved because a database operation failed');
		}
		return $result;
	};

	$escapedMpino = mysqli_real_escape_string($db, $mpino);
	DB_Txn_Begin($db);

	try {
		/* Lock the source slip and its single active supplier transaction. */
		$SQL = "SELECT bazar_parchi.transno,
						bazar_parchi.returned,
						bazar_parchi.inprogress,
						bazar_parchi.settled,
						supptrans.id,
						supptrans.supplierno,
						supptrans.ovamount,
						supptrans.processed,
						supptrans.reversed
				FROM bazar_parchi
				INNER JOIN supptrans ON supptrans.transno=bazar_parchi.transno
				WHERE bazar_parchi.transno='".$escapedMpino."'
				AND bazar_parchi.type=601
				AND supptrans.type=601
				FOR UPDATE";
		$res = $runQuery($SQL);

		if(mysqli_num_rows($res) === 0){
			throw new Exception('The market slip was not found');
		}
		if(mysqli_num_rows($res) !== 1){
			throw new Exception('The market slip has multiple supplier transactions and needs review');
		}

		$trans = mysqli_fetch_assoc($res);

		/* A return is a one-time state transition. */
		if((int)$trans['returned'] === 1 || (int)$trans['reversed'] === 1){
			throw new Exception('This market slip has already been returned');
		}

		$SQL = "SELECT id FROM supptrans
				WHERE transno='".$escapedMpino."' AND type=14
				FOR UPDATE";
		$res = $runQuery($SQL);
		if(mysqli_num_rows($res) > 0){
			throw new Exception('This market slip already has a return reversal');
		}

		if((string)$trans['processed'] !== '-1'){
			throw new Exception('Allocations already made; reverse the allocation before returning this slip');
		}

		/* Validate the two original GL legs before changing any ledger rows. */
		$SQL = "SELECT account, narrative, amount
				FROM gltrans
				WHERE type=601
				AND typeno='".$escapedMpino."'
				AND ((account=2100 AND amount<0) OR (account=4 AND amount>0))
				AND narrative NOT LIKE '%RE%'
				AND narrative NOT LIKE '%Reverse%'
				ORDER BY counterindex DESC
				LIMIT 0,2";
		$res = $runQuery($SQL);
		$originalGlRows = [];
		while($row = mysqli_fetch_assoc($res)){
			$originalGlRows[] = $row;
		}
		if(count($originalGlRows) !== 2){
			throw new Exception('The market slip does not have the expected GL entries and needs review');
		}

		$updatedBy = mysqli_real_escape_string($db, $_SESSION['UsersRealName']);
		$now = date('Y-m-d H:i:s');
		$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

		$SQL = "UPDATE supptrans
				SET reversed=1, updated_by='".$updatedBy."'
				WHERE id='".(int)$trans['id']."' AND reversed=0";
		$runQuery($SQL);

		$SQL = "INSERT INTO supptrans
				(transno,type,supplierno,trandate,inputdate,ovamount,rate,updated_by,processed)
				VALUES ('".$escapedMpino."',14,'".mysqli_real_escape_string($db, $trans['supplierno'])."',
						'".$now."','".$now."','".(-1*$trans['ovamount'])."','1','".$updatedBy."','-1')";
		$runQuery($SQL);

		foreach($originalGlRows as $row){
			$narrative = mysqli_real_escape_string($db, $row['narrative']);
			$SQL = "INSERT INTO gltrans (type,typeno,trandate,periodno,account,narrative,amount)
					VALUES (601,'".$escapedMpino."','".$now."','".$PeriodNo."',
						'".(int)$row['account']."','RE ".$narrative."','".(-1*$row['amount'])."')";
			$runQuery($SQL);
		}

		$SQL = "UPDATE bazar_parchi
				SET returned=1,
					inprogress=1,
					settled=0,
					updated_at='".$now."'
				WHERE transno='".$escapedMpino."' AND type=601";
		$runQuery($SQL);

		DB_Txn_Commit($db);
		$respond('success', 'Return processed successfully');
	} catch (Exception $exception) {
		DB_Txn_Rollback($db);
		$respond('error', $exception->getMessage());
	}
