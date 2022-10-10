<?php
$AllowAnyone = true;
	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');



//	$allowed = [8,10];
	

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


	$SQL .=	" AND invoice.invoicesdate >= '2019-01-01'
			AND invoice.invoicesdate <= '2019-12-31'
			GROUP BY branchcode";
$res = mysqli_query($db,$SQL);
	
	$achieved = [];
	
	while ($row=mysqli_fetch_assoc($res)){
		
		$achieved[$row['branchcode']] = $row['amount'];
		
	}
	
$year=2019;
	$SQL = "SELECT customergroups.id, customergroups.alias, cgdetails.branchcode,cgassignments.target 
			FROM cgassignments
			INNER JOIN cgdetails ON cgassignments.cgid = cgdetails.cgid
			INNER JOIN customergroups ON customergroups.id =  cgdetails.cgid 
			WHERE year='$year'";

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
			$dAchieved[$groupid] = (int)$nAchieved[$groupid];
		}else{
			$dAchieved[$groupid] = 0;
		}
		
	}
//print_r($dAchieved);
    $i=0;
	foreach ($dAchieved as $key=>$value)
    {
   $SQL="SELECT customergroups.*,cgassignments.*,salesman.salesmanname from customergroups INNER JOIN cgassignments ON customergroups.id=cgassignments.cgid 
        INNER  JOIN salesman ON salesman.salesmancode = cgassignments.salesman WHERE customergroups.id=$key";
        $row=mysqli_fetch_assoc(mysqli_query($db,$SQL));
        $data[]=$row;
        $data[$i]['target']=locale_number_format($row['target'],0);
        $data[$i]['achieved']=locale_number_format($value,0);
        $data[$i]['percentage']=round($value/$row['target']*100,2);
        $data[$i]['nextTarget']=locale_number_format($value*1.5,0);
        $i++;
        $cgid=$row['cgid'];
        $salesman=$row['salesman'];
        $SQLA="INSERT INTO cgassignments(`cgid`, `salesman`, `target`, `year`)VALUES ('cgid','$salesman',$value*1.5,'2019')";

        mysqli_query($db,$SQLA);



    }
$response = [];
$response=$data;
echo json_encode($response);


