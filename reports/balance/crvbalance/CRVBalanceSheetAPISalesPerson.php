<?php

	$PathPrefix='../../../';

	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
    $allowed=[8,10,22,23];
	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(ovamount-alloc) AS 30daysdue 
			FROM debtortrans
			INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			WHERE debtortrans.type=750 
			AND debtortrans.settled=0 
			AND shopsale.payment="crv"';
            if (!in_array($_SESSION['AccessLevel'], $allowed))
            {
                $SQL.=' AND shopsale.salesman ="'.$_SESSION['UsersRealName'].'"';
            }

	if(isset($_GET['location']) && $_GET['location'] != ""){
		$SQL .=	' AND debtortrans.debtorno LIKE "%'.$_GET['location'].'%"';
	}

	$SQL .=	' AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) >= 30 
			AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) < 60
			GROUP BY debtortrans.debtorno'; //FormatDateForSQL
		
	$res = mysqli_query($db, $SQL);

	$data = [];

	while($row = mysqli_fetch_assoc($res)){
		
		$data[$row['debtorno']] = $row;
		$data[$row['debtorno']]['30daysdue'] = round($row['30daysdue'],2);

	}

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(ovamount-alloc) AS 60daysdue  
			FROM debtortrans 
			INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno 
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			INNER JOIN salesman ON salesman.salesmancode = custbranch.salesman
			WHERE debtortrans.type=750 
			AND debtortrans.settled=0 
			AND shopsale.payment="crv"';
            if (!in_array($_SESSION['AccessLevel'], $allowed))
            {
                $SQL.=' AND shopsale.salesman ="'.$_SESSION['UsersRealName'].'"';
            }
			if(isset($_GET['location']) && $_GET['location'] != ""){
				$SQL .=	' AND debtortrans.debtorno LIKE "%'.$_GET['location'].'%"';
			}

			$SQL .= ' AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) >= 60 
			AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) < 90
			GROUP BY debtortrans.debtorno'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		if(isset($data[$row['debtorno']])){
		
			$data[$row['debtorno']]['60daysdue'] = round($row['60daysdue'],2);
		
		}else{

			$data[$row['debtorno']] = $row;
		
		}

	}

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(ovamount-alloc) AS 90daysdue  
			FROM debtortrans 
			INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno 
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			WHERE debtortrans.type=750 
			AND debtortrans.settled=0 
			AND shopsale.payment="crv"';
            if (!in_array($_SESSION['AccessLevel'], $allowed))
            {
                $SQL.=' AND shopsale.salesman ="'.$_SESSION['UsersRealName'].'"';
            }
			if(isset($_GET['location']) && $_GET['location'] != ""){
				$SQL .=	' AND debtortrans.debtorno LIKE "%'.$_GET['location'].'%"';
			}

			$SQL .= ' AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) >= 90 
			AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) < 120
			GROUP BY debtortrans.debtorno'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		if(isset($data[$row['debtorno']])){
		
			$data[$row['debtorno']]['90daysdue'] = round($row['90daysdue'],2);
		
		}else{

			$data[$row['debtorno']] = $row;
		
		}

	}

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtorsmaster.debtorno,debtorsmaster.name,
				SUM(ovamount - alloc) AS 120daysdue  
			FROM debtortrans 
			INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno 
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			WHERE debtortrans.type=750 
			AND debtortrans.settled=0 
			AND shopsale.payment="crv"';
            if (!in_array($_SESSION['AccessLevel'], $allowed))
            {
                $SQL.=' AND shopsale.salesman ="'.$_SESSION['UsersRealName'].'"';
            }
			if(isset($_GET['location']) && $_GET['location'] != ""){
				$SQL .=	' AND debtortrans.debtorno LIKE "%'.$_GET['location'].'%"';
			}

			$SQL .= ' AND DATEDIFF( "'.date('Y/m/d').'",shopsale.orddate) >= 120
			GROUP BY debtortrans.debtorno'; //FormatDateForSQL
	
	$res = mysqli_query($db, $SQL);

	while($row = mysqli_fetch_assoc($res)){

		if(isset($data[$row['debtorno']])){
		
			$data[$row['debtorno']]['120daysdue'] = round($row['120daysdue'],2);
		
		}else{

			$data[$row['debtorno']] = $row;
		
		}

	}

	$SQL = 'SELECT shopsale.salesman as salesmanname,debtortrans.debtorno,debtorsmaster.name,
				SUM(ovamount - alloc) as curr
			FROM debtortrans
			INNER JOIN shopsale ON shopsale.orderno = debtortrans.transno
			INNER JOIN debtorsmaster ON debtorsmaster.debtorno = debtortrans.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND custbranch.branchcode=(SELECT MAX(branchcode) as branchcode FROM custbranch WHERE debtorno=debtorsmaster.debtorno))
			WHERE debtortrans.type=750 
			AND debtortrans.settled=0 
			AND shopsale.payment="crv"';
            if (!in_array($_SESSION['AccessLevel'], $allowed))
            {
                $SQL.=' AND shopsale.salesman ="'.$_SESSION['UsersRealName'].'"';
            }
			if(isset($_GET['location']) && $_GET['location'] != ""){
				$SQL .=	' AND debtortrans.debtorno LIKE "%'.$_GET['location'].'%"';
			}

			$SQL .= ' GROUP BY debtortrans.debtorno'; 
	
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
        $debtorno=$customer['debtorno'];

       $SQL = "SELECT SUM(amt) as allocated FROM custallocns WHERE transid_allocfrom IN 
                    (SELECT debtortrans.id FROM debtortrans WHERE debtorno='$debtorno') 
                    AND transid_allocto IN 
                    (SELECT debtortrans.id FROM debtortrans WHERE debtorno='$debtorno' AND type=750) 
                    
                    ";



            $SQL = "SELECT -1*SUM(ovamount-alloc+ovdiscount) as unallocated FROM `debtortrans` WHERE `debtorno` LIKE '$debtorno' AND type=12 ORDER BY `ovamount` ASC";


          $customer['unallocated'] = mysqli_fetch_assoc(mysqli_query($db, $SQL))['unallocated'];


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

		$res[0] = "<form action='".$RootPath."/../../../customerstatementSalesPerson.php' method='post' target='_blank'>
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
        $res[8] = locale_number_format(round($customer['unallocated'],2),2);
		$response[] = $res;

	}

	echo json_encode($response);
	return;
