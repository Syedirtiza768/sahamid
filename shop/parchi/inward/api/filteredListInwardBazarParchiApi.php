<?php

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db,"list_inward_parchi")){
		echo json_encode([]);
		return;
	}
	if ($_GET['filters']=='yes') {
        $SQL = "SELECT bazar_parchi.*,supptrans.settled as settled2, supptrans.id as traid,
			suppliers.suppname as name
			FROM bazar_parchi
			LEFT OUTER JOIN supptrans ON (bazar_parchi.transno = supptrans.transno
										AND supptrans.type=601)
			LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
			WHERE discarded=0
			AND (supptrans.type = 601 OR supptrans.type IS NULL)
			AND bazar_parchi.type = 601
			AND bazar_parchi.svid='" . $_GET['vendor'] . "'";


        $SQL .= " AND bazar_parchi.inprogress = 1";
    }


$res = mysqli_query($db, $SQL);

	$data = [];
	
	$canEdit = userHasPermission($db,"edit_inward_parchi");
	$canPrintInternal = userHasPermission($db,"inward_parchi_internal");

	while($row = mysqli_fetch_assoc($res)){

		if($row['name'] == "")
			$row['name'] = $row['temp_vendor'];
		
		$SQL = "SELECT SUM(amount) as advance FROM bpledger WHERE parchino='".$row['parchino']."'";
		$advance = mysqli_fetch_assoc(mysqli_query($db, $SQL))['advance'];
		
		$SQL = "SELECT SUM(quantity_received * price) as amount FROM bpitems WHERE parchino='".$row['parchino']."'
		AND stockid!=''
		AND deleted_by=''";
		$amount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amount'];
		
		$r = [];
		$r[] = explode("-",$row['parchino'])[1];
		$r[] = $row['parchino']; 
		$r[] = $row['svid']; 
		$r[] = $row['name']; 
		$r[] = date("d/m/Y",strtotime($row['created_at'])); 

		if(userHasVendorPermission($db, $row['svid'])){
		    if ($amount<=10000)
			$r[] = '<span style="background-color:yellow;">'.locale_number_format($amount,2).'</span>';
            if ($amount>10000 && $amount<=50000)
                $r[] = '<span style="background-color:blue;color: white;">'.locale_number_format($amount,2).'</span>';
            if ($amount>50000 && $amount<=100000)
                $r[] = '<span style="background-color:darkblue;color: white;">'.locale_number_format($amount,2).'</span>';
            if ($amount>100000 && $amount<=200000)
                $r[] = '<span style="background-color:deeppink;color: white;">'.locale_number_format($amount,2).'</span>';
            if ($amount>200000)
                $r[] = '<span style="background-color:red;color: white;">'.locale_number_format($amount,2).'</span>';
        }else{
			$r[] = "";
		}

		if ($row['gstinvoice']=="none")
		    $r[]="";
        if ($row['gstinvoice']=="e")
            $r[]="Exclusive";
        if ($row['gstinvoice']=="i")
            $r[]="Inclusive";


		if(userHasVendorPermission($db, $row['svid'])){
			$r[] = $advance;
		}else{
			$r[] = "";
		}

		$SQL = "SELECT * FROM suppallocs WHERE transid_allocto = '".$row['traid']."' ORDER BY id DESC";
		$uFk = mysqli_query($db, $SQL);

		if(mysqli_num_rows($uFk) > 0){
			$r[] = mysqli_fetch_assoc($uFk)['datealloc'];
		}else{
			$r[] = "";	
		}
		
		$r[] = ($row['discarded'] == 1 ? "Discarded":($row['settled2'] == 1 ? "Settled":($row['inprogress'] == 1 ? "InProgress":"Saved"))); 
		$r[] = $row['realname'];
		if($canEdit){
			$r[] = ($row['inprogress'] == 1 ? 
				"<a class='btn btn-warning' target='_blank' href='editInwardParchi.php?parchi=".$row['parchino']."'>Edit</a>":
                "<a class='btn btn-success' target='_blank' href='addfilesInwardParchi.php?parchi=".$row['parchino']."'>Edit Aux.</a>");
		}
		
		$r[] = ($row['igp_created'] == 0 ? "":
				"<a class='btn btn-info' target='_blank' href='../../../PDFIGP.php?RequestNo=".$row['igp_id']."'>IGP</a>");
		
		$r[] = ($row['discarded'] == 1 ? "":
				"<a class='btn btn-info' target='_blank' href='inwardParchiPrint.php?parchi=".$row['parchino']."'>External</a>");
		
		if($canPrintInternal){
			$r[] = ($row['discarded'] == 1 ? "":
					"<a class='btn btn-info' target='_blank' href='inwardParchiPrint.php?parchi=".$row['parchino']."&internal'>Internal</a>");
		}
		
		$data[] = $r; 

	}

	echo json_encode($data);