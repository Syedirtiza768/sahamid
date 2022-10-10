<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$vouchertype= $_GET['type'];
	$branchCode = $_GET['branchCode'];

    if ($vouchertype=="rv") {
        $SQL = "SELECT  debtorsmaster.dba,
		 			custbranch.brname,
		 			debtorsmaster.typeid,
					custbranch.salesman,
					custbranch.branchcode,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3
			FROM custbranch
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = custbranch.debtorno 
			WHERE branchcode='" . $branchCode . "'";
        $res = mysqli_query($db, $SQL);

        if (mysqli_num_rows($res) == 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'branch not found.'
            ]);
            return;
        }

        if (mysqli_num_rows($res) != 1) {
            echo json_encode([
                'status' => 'error',
                'message' => 'multiple branches with the same branchcode found.'
            ]);
            return;
        }

        $res = mysqli_fetch_assoc($res);

        echo json_encode([
            'status' => 'success',
            'data' => $res
        ]);
    }
    if ($vouchertype=="pv") {

        $SQL = "SELECT  name as brname, vid as branchcode
			FROM shop_vendors
			
			WHERE vid='" . $branchCode . "'";
        $res = mysqli_query($db, $SQL);

        if (mysqli_num_rows($res) == 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'branch not found.'
            ]);
            return;
        }

        if (mysqli_num_rows($res) != 1) {
            echo json_encode([
                'status' => 'error',
                'message' => 'multiple branches with the same branchcode found.'
            ]);
            return;
        }

        $res = mysqli_fetch_assoc($res);

        echo json_encode([
            'status' => 'success',
            'data' => $res
        ]);


    }