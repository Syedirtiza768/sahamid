<?php
	
	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');
	
	
	if(isset($_GET['salesmancode']) && trim($_GET['salesmancode']) != ""){
		
		$salesman = $_GET['salesmancode'];
		
	}else{
	
		$SQL = "SELECT salesmancode FROM salesman
				WHERE salesman.salesmanname='".$_SESSION['UsersRealName']."'";
		$res = mysqli_query($db, $SQL);
		
		$salesman 	= mysqli_fetch_assoc($res)['salesmancode']?:"gmhjfgmuygmtygmuy"; 
	
	}

	$allowed = [8,10,22];
	
	$startDate 	= date('Y-01-01');
	$endDate 	= date('Y-12-31');
	
	$SQL = "SELECT SUM(
				CASE WHEN (invoice.gst = '')
					THEN (ovamount)
				WHEN (invoice.services = 1) 
					THEN (ovamount/1.16)
				WHEN (invoice.services = 0)
					THEN (ovamount/1.17)
				END) as amount, debtortrans.branchcode FROM debtortrans
			INNER JOIN custbranch ON debtortrans.branchcode = custbranch.branchcode
			INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			WHERE debtortrans.reversed = 0
			AND debtortrans.type = 10
			AND invoice.inprogress = 0
			AND invoice.returned = 0";

	if(!isset($_GET['salesmancode']) && !in_array($_SESSION['AccessLevel'], $allowed)){
		$SQL .= " AND salesman.salesmancode = '$salesman'";
	}else if(isset($_GET['salesmancode'])){
		$SQL .= " AND salesman.salesmancode = '$salesman'";
	}

	$SQL .=	" AND invoice.invoicesdate >= '$startDate'
			AND invoice.invoicesdate <= '$endDate'
			GROUP BY branchcode";

	/*$SQL = "SELECT SUM(
				CASE WHEN (invoice.gst = 'inclusive' AND invoice.services = 1) 
					THEN (ovamount/1.16)
				WHEN (invoice.gst = 'inclusive' AND invoice.services = 0)
					THEN (ovamount/1.17)
				ELSE
					ovamount
				END) as amount, debtortrans.branchcode FROM debtortrans
			INNER JOIN custbranch ON debtortrans.branchcode = custbranch.branchcode
			INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			WHERE debtortrans.reversed = 0
			AND debtortrans.type = 10
			AND invoice.inprogress = 0
			AND invoice.returned = 0
			AND salesman.salesmancode = '$salesman'
			AND invoice.invoicesdate >= '$startDate'
			AND invoice.invoicesdate <= '$endDate'
			GROUP BY branchcode";*/
	$res = mysqli_query($db,$SQL);
	
	$achieved = [];
	
	while ($row=mysqli_fetch_assoc($res)){
		
		$achieved[$row['branchcode']] = $row['amount'];
		
	}
	
	$year = date('Y');
	
	$SQL = "SELECT customergroups.id, customergroups.alias, cgdetails.branchcode,cgassignments.target 
			FROM cgassignments
			INNER JOIN cgdetails ON cgassignments.cgid = cgdetails.cgid
			INNER JOIN customergroups ON customergroups.id =  cgdetails.cgid 
			WHERE year='$year'";
	
	if(!isset($_GET['salesmancode']) && !in_array($_SESSION['AccessLevel'], $allowed)){ 
		$SQL .=	"AND salesman='$salesman'";
	}else if(isset($_GET['salesmancode'])){
		$SQL .=	"AND salesman='$salesman'";
	}

	$res = mysqli_query($db,$SQL);
	
	$groups = [];
	$branches = [];
	$targets = [];
	
	while ($row = mysqli_fetch_assoc($res)){
		
		$groups[$row['id']] = $row['alias'];
		$branches[$row['branchcode']] = $row['id'];
		$targets[$row['id']] = $row['target'];
		
	}
	
	$nAchieved = [];
		
	foreach($achieved as $branch => $amount){
		
		if(isset($branches[$branch])) {
			
			if(!isset($nAchieved[$branches[$branch]])){
				$nAchieved[$branches[$branch]] = 0;
			}
			
			$nAchieved[$branches[$branch]] += $amount; 
			
		}
		
	}
	
	$yaxis = [];
	
	$dTargets = [];
	$dAchieved = [];

	foreach ($groups as $groupid => $groupname){
		
		$yaxis[] = $groupname;
	
		$dTargets[] = (int)$targets[$groupid];
		
		if(isset($nAchieved[$groupid])){
			$dAchieved[] = (int)$nAchieved[$groupid];
		}else{
			$dAchieved[] = 0;
		}
		
	}
	
	$data = [
		[
			'name' => 'Achieved',
			'data' => $dAchieved
		],
		[
			'name' => 'Target',
			'data' => $dTargets
		]
	];
	
	$response = [];
	$response['status'] = 'success';
	$response['data'] = [
		'yaxis' => $yaxis,
		'data'	=> $data
	];
	echo json_encode($response);
	
	
	