<?php 

	include('../misc.php');
    session_start();

	$response = [];




	$db = createDBConnection();

	$SQL = "SELECT * FROM
                debtorsmaster inner join  salescase ON salescase.debtorno=debtorsmaster.debtorno
				WHERE salescase.salescaseref='".$_POST['salescaseref']."'
				";

	$result = mysqli_query($db, $SQL);


	$details = mysqli_fetch_assoc($result);


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
			AND type = 10
			AND debtorno = '".$details['debtorno']."'";
	$res = mysqli_query($db, $SQL);
	$credit = mysqli_fetch_assoc($res)['credit'] ?:0;

//	closeDBConnection($db);


	$response['status'] = "success";
	$response['formid'] = $_SESSION['FormID'];
    $SQL='SELECT debtorsmaster.debtorno FROM debtorsmaster WHERE debtorsmaster.debtorno IN 
                    (SELECT can_access FROM statement_access WHERE user = "'.$_SESSION['UserID'].'"'.') 
                    AND debtorsmaster.debtorno="'.$details['debtorno'].'"';
    $result=mysqli_query($db,$SQL);
    if (mysqli_num_rows($result)>=1) {
        $response['flag'] = "on";
    }
    $response['credit'] = $credit;
	$response['data']	= $details;

	utf8_encode_deep($response);
	echo json_encode($response);
	return;


?>