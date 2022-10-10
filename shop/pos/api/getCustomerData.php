<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$branchCode = $_GET['branchCode'];

	$SQL = "SELECT  debtorsmaster.dba,
		 			custbranch.brname,
		 			debtorsmaster.typeid,
					custbranch.salesman,
					custbranch.branchcode,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.debtorno,
					debtorsmaster.creditlimit
			FROM custbranch
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = custbranch.debtorno 
			WHERE branchcode='".$branchCode."'";
	$res = mysqli_query($db, $SQL);

	if(mysqli_num_rows($res) == 0){
		echo json_encode([
				'status' => 'error',
				'message' => 'branch not found.'
			]);
		return;
	}

	if(mysqli_num_rows($res) != 1){
		echo json_encode([
				'status' => 'error',
				'message' => 'multiple branches with the same branchcode found.'
			]);
		return;
	}

	$res = mysqli_fetch_assoc($res);
	$SQL = "SELECT SUM(
                            CASE WHEN GSTwithhold = 0 AND WHT = 0 
                                THEN ovamount - alloc
                            WHEN GSTwithhold = 0 AND WHT = 1 
                                THEN ovamount - alloc - WHTamt
                            WHEN GSTwithhold = 1 AND WHT = 0 
                                THEN ovamount - alloc - GSTamt
                            WHEN GSTwithhold = 1 AND WHT = 1 
                                THEN ovamount - alloc - GSTamt - WHTamt
                            END
                        ) AS credit  
                FROM debtortrans 
                WHERE reversed = 0 
                AND settled = 0
                AND(type = 750 OR type=10)
                AND debtorno = '".$branchCode."'";
    $resp = mysqli_query($db, $SQL);
    $credit = mysqli_fetch_assoc($resp)['credit'] ?:0;
    $res['credit']=$credit;
    $res['formid'] = $_SESSION['FormID'];


echo json_encode([
			'status' => 'success',
			'data' => $res
		]);