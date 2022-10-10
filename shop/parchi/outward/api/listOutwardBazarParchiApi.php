<?php

	$PathPrefix = "../../../../";
	include("../../../../includes/session.inc");
	include('../../../../includes/SQL_CommonFunctions.inc');
	
	if(!userHasPermission($db,"list_outward_parchi")){
		echo json_encode([]);
		return;
	}
if ($_GET['filters']=='yes')
{
    $SQL = "SELECT bazar_parchi.*,supptrans.settled as settled2, supptrans.id as traid, www_users.realname,
			suppliers.suppname as name
			FROM bazar_parchi
			INNER JOIN www_users ON bazar_parchi.user_id = www_users.userid
			LEFT OUTER JOIN supptrans ON (bazar_parchi.transno = supptrans.transno
										AND supptrans.type=602)
			LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
			WHERE discarded=0
			AND (supptrans.type = 602 OR supptrans.type IS NULL)
			AND bazar_parchi.type = 602";

    if(isset($_GET['from']) && $_GET['from'] != ""){
        $from = DateTime::createFromFormat('Y-m-d', $_GET['from']);
        $formattedFrom = $from->format('Y-m-d');
        $SQL .= " AND bazar_parchi.created_at >= '".$formattedFrom."'";
    }

    if(isset($_GET['to']) && $_GET['to'] != ""){
        $to = DateTime::createFromFormat('Y-m-d', $_GET['to']);
        $formattedTo = $to->format('Y-m-d');
        $SQL .= " AND bazar_parchi.created_at <= '".$formattedTo." 23:59:59'";
    }
    if(isset($_GET['state']) && $_GET['state'] == "saved"){
        $SQL .= "AND bazar_parchi.inprogress = 0";
        $SQL .= " AND supptrans.settled = 0";

    }
    if(isset($_GET['state']) && $_GET['state'] == "settled"){
        $SQL .= " AND supptrans.settled = 1";
    }
    if(isset($_GET['state']) && $_GET['state'] == "inprogress"){
        $SQL .= " AND bazar_parchi.inprogress = 1";
    }
    if(isset($_GET['item']) && $_GET['item'] != ""){
        $SQL .= " AND bazar_parchi.parchino IN
                            (
                                SELECT DISTINCT bpitems.parchino FROM bpitems WHERE bpitems.stockid LIKE '%".$_GET['item']."%'
                            )
                           ";
    }
    if(isset($_GET['brand']) && $_GET['brand'] != "All"){
        $SQL .= " AND bazar_parchi.parchino IN
                                (
                                    SELECT DISTINCT bpitems.parchino FROM bpitems
                                    WHERE bpitems.stockid IN
                                    (
                                        SELECT stockid FROM stockmaster WHERE stockmaster.brand = '".$_GET['brand']."'
                                    ) 
                                    
                                )
                               ";
    }

}
else {
    if (!isset($_SESSION['filter'])) {
        $SQL = "SELECT bazar_parchi.*,supptrans.settled as settled2, www_users.realname,
                suppliers.suppname as name
                FROM bazar_parchi
                INNER JOIN www_users ON bazar_parchi.user_id = www_users.userid
                LEFT OUTER JOIN supptrans ON (bazar_parchi.transno = supptrans.transno
                                            AND supptrans.type=602)
                LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
                WHERE discarded=0
                AND (supptrans.type = 602 OR supptrans.type IS NULL)
                AND bazar_parchi.type = 602";
    }
    if ($_SESSION['filter'] == "none") {
        $SQL = "SELECT bazar_parchi.*,supptrans.settled as settled2, www_users.realname,
                    suppliers.suppname as name
                    FROM bazar_parchi
                    INNER JOIN www_users ON bazar_parchi.user_id = www_users.userid
                    LEFT OUTER JOIN supptrans ON (bazar_parchi.transno = supptrans.transno
                                                AND supptrans.type=602)
                    LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
                    WHERE discarded=0
                    AND (supptrans.type = 602 OR supptrans.type IS NULL)
                    AND bazar_parchi.type = 602
                    AND bazar_parchi.svid = '" . $_SESSION['svid'] . "'";
    }
    if ($_SESSION['filter'] == "inprogress") {
        $SQL = "SELECT bazar_parchi.*,supptrans.settled as settled2, www_users.realname,
                    suppliers.suppname as name
                    FROM bazar_parchi
                    INNER JOIN www_users ON bazar_parchi.user_id = www_users.userid
                    LEFT OUTER JOIN supptrans ON (bazar_parchi.transno = supptrans.transno
                                                AND supptrans.type=602)
                    LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
                    WHERE discarded=0
                    AND (supptrans.type = 602 OR supptrans.type IS NULL)
                    AND bazar_parchi.type = 602
                    AND bazar_parchi.inprogress = 1
                    AND bazar_parchi.svid = '" . $_SESSION['svid'] . "'";
    }
    if ($_SESSION['filter'] == "saved") {

        $SQL = "SELECT bazar_parchi.*,supptrans.settled as settled2, www_users.realname,
                    suppliers.suppname as name
                    FROM bazar_parchi
                    INNER JOIN www_users ON bazar_parchi.user_id = www_users.userid
                    LEFT OUTER JOIN supptrans ON (bazar_parchi.transno = supptrans.transno
                                                AND supptrans.type=602)
                    LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
                    WHERE discarded=0
                    AND (supptrans.type = 602 OR supptrans.type IS NULL)
                    AND bazar_parchi.type = 602
                    AND bazar_parchi.inprogress = 0
                    AND bazar_parchi.svid = '" . $_SESSION['svid'] . "'";
    }
    if ($_SESSION['filter'] == "settled") {

        $SQL = "SELECT bazar_parchi.*,supptrans.settled as settled2, www_users.realname,
                    suppliers.suppname as name
                    FROM bazar_parchi
                    INNER JOIN www_users ON bazar_parchi.user_id = www_users.userid
                    LEFT OUTER JOIN supptrans ON (bazar_parchi.transno = supptrans.transno
                                                AND supptrans.type=602)
                    LEFT OUTER JOIN suppliers ON bazar_parchi.svid = suppliers.supplierid
                    WHERE discarded=0
                    AND (supptrans.type = 602 OR supptrans.type IS NULL)
                    AND bazar_parchi.type = 602
                    AND supptrans.settled = 1
                    AND bazar_parchi.svid = '" . $_SESSION['svid'] . "'";
    }
}
	$res = mysqli_query($db, $SQL);

	$data = [];
	
	$canEdit = userHasPermission($db,"edit_outward_parchi");
	$canPrintInternal = userHasPermission($db,"outward_parchi_internal");

	while($row = mysqli_fetch_assoc($res)){

		if($row['name'] == "")
			$row['name'] = $row['temp_vendor'];
		
		$SQL = "SELECT SUM(amount) as advance FROM bpledger WHERE parchino='".$row['parchino']."'";
		$advance = mysqli_fetch_assoc(mysqli_query($db, $SQL))['advance'];
		
		$SQL = "SELECT SUM(quantity_received * price) as amount FROM bpitems WHERE parchino='".$row['parchino']."'";
		$amount = mysqli_fetch_assoc(mysqli_query($db, $SQL))['amount'];
		
		$r = [];
		$r[] = explode("-",$row['parchino'])[1];
		$r[] = $row['parchino']; 
		$r[] = $row['svid']; 
		$r[] = $row['name']; 
		$r[] = date("d/m/Y",strtotime($row['created_at'])); 
		$r[] = locale_number_format($amount,2);	
		$r[] = $advance;
		$r[] = ($row['discarded'] == 1 ? "Discarded":($row['settled2'] == 1 ? "Settled":($row['inprogress'] == 1 ? "InProgress":"Saved"))); 
		$r[] = $row['realname'];
		if($canEdit){
			$r[] = ($row['inprogress'] == 1 ? 
				"<a class='btn btn-warning' target='_blank' href='editOutwardParchi.php?parchi=".$row['parchino']."'>Edit</a>":""); 
		}
		
		$r[] = ($row['discarded'] == 1 ? "":
				"<a class='btn btn-info' target='_blank' href='outwardParchiPrint.php?parchi=".$row['parchino']."'>External</a>");
		
		if($canPrintInternal){
			$r[] = ($row['discarded'] == 1 ? "":
					"<a class='btn btn-info' target='_blank' href='outwardParchiPrint.php?parchi=".$row['parchino']."&internal'>Internal</a>");
		}
		
		$data[] = $r; 

	}

	echo json_encode($data);