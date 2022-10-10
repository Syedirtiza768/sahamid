<?php

	$PathPrefix = "../../../../";
    $AllowAnyone=true;
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');

	/*if(!userHasPermission($db,"master_market_list")){
		echo json_encode([]);
		return;
	}*/
/*<th>SR#</th>
                            <th>SVID</th>
                            <th>Business</th>
                            <th>MPO Count</th>
	      					<th>MPI Count</th>
                            <th>MPO I/P</th>
                            <th>MPI I/P</th>
                            <th>MPO Saved</th>
                            <th>MPI Saved</th>
                            <th>MPO Settled</th>
                            <th>MPI Settled</th>
                            <th>A/R</th>
                            <th>A/P</th>*/
	/*$SQL = "SELECT bazar_parchi.*,count(bazar_parchi.*),supptrans.settled as settled2,
	        supptrans.id as traid, www_users.realname,
			suppliers.suppname as name
			FROM bazar_parchi
			INNER JOIN www_users ON bazar_parchi.user_id = www_users.userid
			LEFT OUTER JOIN supptrans ON (bazar_parchi.transno = supptrans.transno
										AND supptrans.type=601)
			LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
			WHERE discarded=0
			AND (supptrans.type = 601 OR supptrans.type IS NULL)
			AND bazar_parchi.type = 601
			GROUP BY svid";
	$res = mysqli_query($db, $SQL);*/
$SQL = "SELECT bazar_parchi.svid,	        
			suppliers.suppname as name
			FROM bazar_parchi
			INNER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
			WHERE discarded=0
			GROUP BY svid";
$res = mysqli_query($db, $SQL);

	$data = [];
	

	while(($row = mysqli_fetch_assoc($res))){

		if($row['name'] == "")
			$row['name'] = $row['temp_vendor'];
		$SQL="SELECT COUNT(*) as mpocount FROM bazar_parchi WHERE  svid='".$row['svid']."' AND TYPE=602";
		$mpocount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['mpocount'];
        $SQL="SELECT COUNT(*) as mpicount FROM bazar_parchi WHERE  svid='".$row['svid']."' AND TYPE=601";
        $mpicount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['mpicount'];
        $SQL="SELECT COUNT(*) as mposettled FROM bazar_parchi INNER JOIN supptrans ON bazar_parchi.transno = supptrans.transno
        WHERE  svid='".$row['svid']."' AND supptrans.type=602 AND supptrans.settled=1";
        $mposettled = mysqli_fetch_assoc(mysqli_query($db, $SQL))['mposettled'];
        $SQL="SELECT COUNT(*) as mpisettled FROM bazar_parchi INNER JOIN supptrans
        ON bazar_parchi.transno = supptrans.transno
        WHERE  svid='".$row['svid']."' AND supptrans.type=601 AND supptrans.settled=1";
        $mpisettled = mysqli_fetch_assoc(mysqli_query($db, $SQL))['mpisettled'];

        $SQL="SELECT COUNT(*) as mpoip FROM bazar_parchi WHERE  svid='".$row['svid']."' 
        AND TYPE=602 AND settled=0 AND inprogress=1";
        $mpoip = mysqli_fetch_assoc(mysqli_query($db, $SQL))['mpoip'];
        $SQL="SELECT COUNT(*) as mpiip FROM bazar_parchi WHERE  svid='".$row['svid']."' 
        AND TYPE=601 AND settled=0 AND inprogress=1";
        $mpiip = mysqli_fetch_assoc(mysqli_query($db, $SQL))['mpiip'];
        $SQL="SELECT COUNT(*) as mposaved FROM bazar_parchi WHERE  svid='".$row['svid']."' 
        AND TYPE=602 AND settled=0 AND inprogress=0";
        $mposaved = mysqli_fetch_assoc(mysqli_query($db, $SQL))['mposaved'];
        $SQL="SELECT COUNT(*) as mpisaved FROM bazar_parchi WHERE  svid='".$row['svid']."' 
        AND TYPE=601 AND settled=0 AND inprogress=0";
        $mpisaved = mysqli_fetch_assoc(mysqli_query($db, $SQL))['mpisaved'];
        $SQL="SELECT SUM(ovamount) as payable FROM supptrans WHERE  supplierno='".$row['svid']."'";
        $payable = mysqli_fetch_assoc(mysqli_query($db, $SQL))['payable'];
        $SQL="SELECT SUM(ovamount) as receivable FROM debtortrans WHERE  debtorno='".$row['svid']."'";
        $receivable = mysqli_fetch_assoc(mysqli_query($db, $SQL))['receivable'];





        $r = [];

		$r[] = $row['svid'];

		$r[] = $row['name'];
        $r[] = '<a target="_blank" href="../outward/listOutwardBazarParchiSimple.php?svid='.$row['svid'].'&filter=none">'.$mpocount.'</a>';
        $r[] = '<a target="_blank" href="../inward/listInwardBazarParchiSimple.php?svid='.$row['svid'].'&filter=none"">'.$mpicount.'</a>';
        $r[] = '<a target="_blank" href="../outward/listOutwardBazarParchiSimple.php?svid='.$row['svid'].'&filter=inprogress">'.$mpoip.'</a>';
        $r[] = '<a target="_blank" href="../inward/listInwardBazarParchiSimple.php?svid='.$row['svid'].'&filter=inprogress">'.$mpiip.'</a>';
        $r[] = '<a target="_blank" href="../outward/listOutwardBazarParchiSimple.php?svid='.$row['svid'].'&filter=saved">'.$mposaved.'</a>';
        $r[] ='<a target="_blank" href="../inward/listInwardBazarParchiSimple.php?svid='.$row['svid'].'&filter=saved">'. $mpisaved.'</a>';
        $r[] = '<a target="_blank" href="../outward/listOutwardBazarParchiSimple.php?svid='.$row['svid'].'&filter=settled">'.$mposettled.'</a>';
        $r[] = '<a target="_blank" href="../inward/listInwardBazarParchiSimple.php?svid='.$row['svid'].'&filter=settled">'.$mpisettled.'</a>';
        $r[] = locale_number_format($receivable,2);
        $r[] = locale_number_format($payable,2);
        $r[] =			'<form target="_blank" action="../../../reports/balance/suppstatement/SupplierStatement.php" method="post"><table><tr><td>
							<input type="hidden" name="FormID" value="'.$_SESSION['FormID'].'">
							<input type="hidden" name="cust" value="'.$row['svid'].'">
							<input type="date" name="fromdate" 
							style="padding: 4px; margin: 0; border: 1px #424242 solid;
							 border-radius: 6px; line-height: initial !important;">
						</td>
						<td>
							<input type="date" name="todate" style="padding: 4px; margin: 0; border: 1px #424242 solid; border-radius: 6px; line-height: initial !important;">
						</td>
						<td class="btn-info">
							<input type="submit" value="Supplier Statement" class="btn-info" style="width: 100%; padding: 4px; border:1px #424242 solid;">
							</form>
						</td>
						<td class="btn-info">
							<form target="_blank" action="/sahamid/reports/balance/custstatement/../../../customerstatement.php" method="post" target="_blank">
							<input type="hidden" name="FormID" value="'.$_SESSION['FormID'].'">
							<input type="hidden" name="cust" value="'.$row['svid'].'">
							<input type="submit" value="Customer Statement" class="btn-info" style="padding: 8px; border:1px #424242 solid;">
							</form>
						</td>
  					</tr></table>';










        if(userHasVendorPermission($db, $row['svid']))
        $data[] = $r;


	}

	echo json_encode($data);