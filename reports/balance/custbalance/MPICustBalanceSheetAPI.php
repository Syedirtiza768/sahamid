<?php
	$AllowAnyone = true;

	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');

	$SQL = 'SELECT bazar_parchi.on_behalf_of as salesmanname, bazar_parchi.svid as debtorno, suppliers.suppname as name,
					SUM(ovamount - alloc) as 30daysdue
			FROM supptrans
			INNER JOIN bazar_parchi ON bazar_parchi.transno = supptrans.transno
			INNER JOIN suppliers ON suppliers.supplierid = bazar_parchi.svid
			WHERE bazar_parchi.type = 601
			AND supptrans.type = 601
			AND supptrans.settled = 0
			AND DATEDIFF( "'.date('Y/m/d').'",DATE(bazar_parchi.created_at)) >= 30 
			AND DATEDIFF( "'.date('Y/m/d').'",DATE(bazar_parchi.created_at)) < 60
			GROUP BY bazar_parchi.svid';
	$res = mysqli_query($db, $SQL);

	$data = [];

	while($row = mysqli_fetch_assoc($res)){
		
		$data[$row['debtorno']] = $row;
		$data[$row['debtorno']]['30daysdue'] = round($row['30daysdue'],2);

	}

	$SQL = 'SELECT bazar_parchi.on_behalf_of as salesmanname, bazar_parchi.svid as debtorno, suppliers.suppname as name,
					SUM(ovamount - alloc) as 60daysdue
			FROM supptrans
			INNER JOIN bazar_parchi ON bazar_parchi.transno = supptrans.transno
			INNER JOIN suppliers ON suppliers.supplierid = bazar_parchi.svid
			WHERE bazar_parchi.type = 601
			AND supptrans.type = 601
			AND supptrans.settled = 0
			AND DATEDIFF( "'.date('Y/m/d').'",DATE(bazar_parchi.created_at)) >= 60 
			AND DATEDIFF( "'.date('Y/m/d').'",DATE(bazar_parchi.created_at)) < 90
			GROUP BY bazar_parchi.svid';
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		if(isset($data[$row['debtorno']])){
		
			$data[$row['debtorno']]['60daysdue'] = round($row['60daysdue'],2);
		
		}else{

			$data[$row['debtorno']] = $row;
		
		}

	}

	$SQL = 'SELECT bazar_parchi.on_behalf_of as salesmanname, bazar_parchi.svid as debtorno, suppliers.suppname as name,
					SUM(ovamount - alloc) as 90daysdue
			FROM supptrans
			INNER JOIN bazar_parchi ON bazar_parchi.transno = supptrans.transno
			INNER JOIN suppliers ON suppliers.supplierid = bazar_parchi.svid
			WHERE bazar_parchi.type = 601
			AND supptrans.type = 601
			AND supptrans.settled = 0
			AND DATEDIFF( "'.date('Y/m/d').'",DATE(bazar_parchi.created_at)) >= 90 
			AND DATEDIFF( "'.date('Y/m/d').'",DATE(bazar_parchi.created_at)) < 120
			GROUP BY bazar_parchi.svid';
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		if(isset($data[$row['debtorno']])){
		
			$data[$row['debtorno']]['90daysdue'] = round($row['90daysdue'],2);
		
		}else{

			$data[$row['debtorno']] = $row;
		
		}

	}

	$SQL = 'SELECT bazar_parchi.on_behalf_of as salesmanname, bazar_parchi.svid as debtorno, suppliers.suppname as name,
					SUM(ovamount - alloc) as 120daysdue
			FROM supptrans
			INNER JOIN bazar_parchi ON bazar_parchi.transno = supptrans.transno
			INNER JOIN suppliers ON suppliers.supplierid = bazar_parchi.svid
			WHERE bazar_parchi.type = 601
			AND supptrans.type = 601
			AND supptrans.settled = 0
			AND DATEDIFF( "'.date('Y/m/d').'",DATE(bazar_parchi.created_at)) >= 120
			GROUP BY bazar_parchi.svid';
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		if(isset($data[$row['debtorno']])){
		
			$data[$row['debtorno']]['120daysdue'] = round($row['120daysdue'],2);
		
		}else{

			$data[$row['debtorno']] = $row;
		
		}

	}

	$SQL = 'SELECT bazar_parchi.on_behalf_of as salesmanname, bazar_parchi.svid as debtorno, suppliers.suppname as name,
					SUM(ovamount - alloc) as curr
			FROM supptrans
			INNER JOIN bazar_parchi ON bazar_parchi.transno = supptrans.transno
			INNER JOIN suppliers ON suppliers.supplierid = bazar_parchi.svid
			WHERE bazar_parchi.type = 601
			AND supptrans.type = 601
			AND supptrans.settled = 0
			GROUP BY bazar_parchi.svid';
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		if(isset($data[$row['debtorno']])){
		
			$data[$row['debtorno']]['curr'] = round($row['curr'],2);
		
		}else{

			$data[$row['debtorno']] = $row;
		
		}

	}

	$response = [];

	foreach ($data as $customer) {

		if($customer['debtorno'] == "" || $customer['debtorno'] == null)
			continue;
		
		if(!isset($customer['30daysdue']))
			$customer['30daysdue'] = 0;

		if(!isset($customer['60daysdue']))
			$customer['60daysdue'] = 0;

		if(!isset($customer['90daysdue']))
			$customer['90daysdue'] = 0;

		if(!isset($customer['120daysdue']))
			$customer['120daysdue'] = 0;

		if(!isset($customer['curr']))
			$customer['curr'] = 0;
		else
			$customer['curr'] = round($customer['curr'],2);

		$res = [];

		$res[0] = "<form action='".$RootPath."/../suppstatement/SupplierStatement.php' method='post' target='_blank'>
						<input type='hidden' name='FormID' value='".$_SESSION['FormID']."' />
						<input type='hidden' name='cust' value='".$customer['debtorno']."' />
						<input type='submit' class='btn btn-info' style='width:100%' value='".$customer['debtorno']."' />
					</form>";
		$res[1] = $customer['name'];
		$res[2] = $customer['salesmanname'];
		$res[3] = locale_number_format(round($customer['curr'],2),2);
		$res[4] = locale_number_format(round($customer['30daysdue'],2),2);
		$res[5] = locale_number_format(round($customer['60daysdue'],2),2);
		$res[6] = locale_number_format(round($customer['90daysdue'],2),2);
		$res[7] = locale_number_format(round($customer['120daysdue'],2),2);

		$SQL = "SELECT SUM(ovamount-alloc) as total 
				FROM `supptrans` 
				WHERE `type` = 22 
				AND supplierno = '".$customer['debtorno']."'";

		$res[8] = abs(round(mysqli_fetch_assoc(mysqli_query($db, $SQL))['total']));
 
		$response[] = $res;

	}

	$SQL = "SELECT SUM(ovamount-alloc) as total FROM `supptrans` WHERE `type` = 22 ORDER BY `supptrans`.`alloc`";
	$res = mysqli_query($db, $SQL);
	$total = mysqli_fetch_assoc($res)['total'];

	echo json_encode([
			'res' => $response,
			'total' => round(abs($total))
		]);
	return;
