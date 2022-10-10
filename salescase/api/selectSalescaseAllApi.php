<?php 

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	//Quotation Values
	$SQL = 'SELECT salesorders.salescaseref, salesorderdetails.orderno, SUM(salesorderdetails.unitprice*
		(1 - salesorderdetails.discountpercent)*salesorderdetails.quantity*salesorderoptions.quantity) as value
		from salesorderdetails INNER JOIN salesorderoptions on (salesorderdetails.orderno = salesorderoptions.orderno AND salesorderdetails.orderlineno = salesorderoptions.lineno) 
		INNER JOIN salesorders on salesorders.orderno = salesorderdetails.orderno
		WHERE salesorderdetails.lineoptionno = 0 and salesorderoptions.optionno = 0
		GROUP BY salesorderdetails.orderno';
	
	$result = mysqli_query($db,$SQL);	
		
	$quotationValues = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$quotationValues[$row['orderno']] = $row['value'];
		
	}
	
	mysqli_free_result($result);

	//Delivery Chalans
	$SQL = 'SELECT salescaseref,orderno FROM dcs 
		WHERE (dcs.orderno IN (SELECT MAX(orderno) FROM dcs GROUP BY salescaseref)
		OR dcs.orderno IS NULL)';
		
	$result = mysqli_query($db,$SQL);	
		
	$deliveryChalans = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$deliveryChalans[$row['salescaseref']] = $row['orderno'];
		
	}
	
	mysqli_free_result($result);

	//Sales Orders
	$SQL = 'SELECT salescaseref,orderno FROM salesorders 
		WHERE (salesorders.orderno IN (SELECT MAX(orderno) FROM salesorders GROUP BY salescaseref)
		OR salesorders.orderno IS NULL)';
		
	$result = mysqli_query($db,$SQL);	
		
	$salesOrders = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$salesOrders[$row['salescaseref']] = $row['orderno'];
		
	}
	
	mysqli_free_result($result);

	//Sales Case Comments
	$SQL = 'SELECT salescaseref,commentcode FROM salescasecomments 
		WHERE (salescasecomments.time IN (SELECT MAX(time) FROM salescasecomments GROUP BY salescaseref)
		OR salescasecomments.time IS NULL)';

	$result = mysqli_query($db,$SQL);	
		
	$salesCaseComments = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$salesCaseComments[$row['salescaseref']] = $row['commentcode'];
		
	}
	
	mysqli_free_result($result);
	
	$SQL = "SELECT salescaseref FROM salescase_watchlist WHERE userid='".$_SESSION['UserID']."' AND deleted=0";
	$result = mysqli_query($db,$SQL);	
		
	$salesCaseWatchlist = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$salesCaseWatchlist[] = $row['salescaseref'];
		
	}

	mysqli_free_result($result);
	
	if($_SESSION['AccessLevel'] == 22 || $_SESSION['AccessLevel'] == 8 || $_SESSION['AccessLevel'] == 10){
		

		$SQL = 'SELECT `salescaseindex`,ocs.pono, salescase.salescaseref, `salescasedescription`,
				salescase.`salesman`,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
				`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
				`ocdocumentfile`, `ocdocumentdate`,debtorsmaster.dba, custbranch.defaultlocation,
				salescase.priority, salescase.priority_updated_by FROM salescase 
				INNER JOIN ocs ON  salescase.salescaseref =  ocs.salescaseref
				LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
				LEFT OUTER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno 
												AND salescase.branchcode = custbranch.branchcode)
				WHERE salescase.closed = 0';
	
	}else{

		$SQL = "SELECT can_access FROM salescase_permissions WHERE user='".$_SESSION['UserID']."'";
		$res = mysqli_query($db, $SQL);

		$canAccess = [];

		while($row = mysqli_fetch_assoc($res))
			$canAccess[] = $row['can_access'];

		$SQL = 'SELECT `salescaseindex`,ocs.pono, salescase.salescaseref, `salescasedescription`,
				salescase.salesman,`name`, salescase.`branchcode`, `commencementdate`,  `enquiryfile`,
				`enquirydate`,enquiryvalue, `lastquotationdate`,  `pofile`, salescase.`podate`,
				`ocdocumentfile`, `ocdocumentdate`, debtorsmaster.dba, custbranch.defaultlocation,
				salescase.priority, salescase.priority_updated_by FROM salescase
				INNER JOIN ocs ON salescase.salescaseref =  ocs.salescaseref
				INNER JOIN www_users ON www_users.realname = salescase.salesman
				LEFT OUTER JOIN debtorsmaster ON salescase.debtorno = debtorsmaster.debtorno 
				LEFT OUTER JOIN custbranch ON (salescase.debtorno = custbranch.debtorno 
				AND salescase.branchcode = custbranch.branchcode)
				WHERE salescase.closed = 0
				AND ( salescase.salesman ="'.$_SESSION['UsersRealName'].'"
				OR www_users.userid IN ("'.implode('","', $canAccess).'") )';	
	
	}
	
	if(isset($_GET['from_date']) || isset($_GET['to_date'])){

		if(isset($_GET['from_date']) && $_GET['from_date'] != ""){
			$SQL .= " AND salescase.commencementdate >= '".$_GET['from_date']." 00:00:00'";
		}
		
		if(isset($_GET['to_date']) && $_GET['to_date'] != ""){
			$SQL .= " AND salescase.commencementdate <= '".$_GET['to_date']." 23:59:59'";
		}
		
	}else if(isset($_GET['for_month'])){
		
		$SQL .= " AND salescase.commencementdate >= '".$_GET['for_month']."-01 00:00:00'";
		$SQL .= " AND salescase.commencementdate <= '".$_GET['for_month']."-31 23:59:59'";
		
	}else if(isset($_GET['for_year'])){
		
		$SQL .= " AND salescase.commencementdate >= '".$_GET['for_year']."-01-01 00:00:00'";
		$SQL .= " AND salescase.commencementdate <= '".$_GET['for_year']."-12-31 23:59:59'";
		
	}

	$SQL .= " ORDER BY salescaseindex DESC";

	mysqli_real_query($db,$SQL);
	
	$result = mysqli_use_result($db);
	
	$data = [];
	$toFilter = [];
	
	while($row = mysqli_fetch_assoc($result)){
		
		$d = [];

		$d['sno'] 			= $row['salescaseindex'];
		$d['salescaseref'] 	= $row['salescaseref'];
		$d['salesman'] 		= $row['salesman'];
		$d['pono'] 		= $row['pono'];
		$d['client'] 		= (html_entity_decode($row['name']));
		$d['commencement'] 	= date('Y/m/d',strtotime($row['commencementdate']));
		$d['description'] 	= stripslashes(html_entity_decode($row['salescasedescription']));
		$d['details']		= "";
		$d['ed']			= ($row['enquirydate'] == "0000-00-00 00:00:00") ? "":date('d/m/Y',strtotime($row['enquirydate']));
		$d['eds']			= ($row['enquirydate'] == "0000-00-00 00:00:00") ? "":date('Y/m/d',strtotime($row['enquirydate']));
		$d['ev']			= locale_number_format($row['enquiryvalue']);
		$d['hq']			= $salesOrders[$row['salescaseref']];
		$d['qv']			= locale_number_format($quotationValues[$salesOrders[$row['salescaseref']]],0);
		$d['pd']			= ($row['podate'] == "0000-00-00 00:00:00") ? "":date('d/m/Y',strtotime($row['podate']));
		$d['podate']		= ($row['podate'] == "0000-00-00 00:00:00") ? "":date('Y/m/d',strtotime($row['podate']));
		$d['dc']			= ($deliveryChalans[$row['salescaseref']] != "") ? $deliveryChalans[$row['salescaseref']]:"No";
		$d['action'] 		= "";	 
		$d['dba'] 			= $row['dba'];	 
		$d['wl'] 			= (in_array($row['salescaseref'], $salesCaseWatchlist));
		
		$data[] = $d;

		$d['loc'] 			= $row['defaultlocation'];	 
		$d['pub'] 			= $row['priority_updated_by'];	 
		$d['priority'] 		= $row['priority'];	 

		$toFilter[] = $d;
		
	}
	
	mysqli_free_result($result);

	if(!isset($_GET['filters'])){
		echo json_encode($data);
		return;
	}
	$sp 		= $_GET['salesperson'];
	$cl 		= $_GET['client'];
	$scr 		= $_GET['salescaseref'];
	$loc 		= $_GET['location'];
	$dir 		= $_GET['director'];
	$pri 		= $_GET['priority'];
	$dba 		= $_GET['dba'];
	$po 		= $_GET['po'];
	$dc 		= $_GET['dc'];
	$quot 		= $_GET['quot'];
	$pon= $toFilter['pon'];

	echo json_encode(array_values(array_filter($toFilter, function($val) use ($sp, $cl, $scr, $loc, $dir, $pri, $dba, $pon, $po, $dc, $quot){

		$keep = true;

		if(isset($sp) && $sp != ""){
			if(stripos($val['salesman'], $sp) === false)	
				$keep = false;
		}

		if($keep && isset($cl) && $cl != ""){
			if(stripos($val['client'], $cl) === false)	
				$keep = false;
		}

		if($keep && isset($scr) && $scr != ""){
			if(stripos($val['salescaseref'], $scr) === false)	
				$keep = false;
		}

		if($keep && isset($loc) && $loc != ""){
			if($val['loc'] != $loc)	
				$keep = false;
		}

		if($keep && isset($dir) && $dir != ""){
			if($val['pub'] != $dir)	
				$keep = false;
		}

		if($keep && isset($pri) && $pri != ""){
			if($val['priority'] != $pri)	
				$keep = false;
		}

		if($keep && isset($dba) && $dba != ""){
			if($val['dba'] != $dba)	
				$keep = false;
		}

		if($keep && isset($po) && $po == "yes"){
			if($val['pd'] == "")	
				$keep = false;
		}
		
		if($keep && isset($po) && $po == "no"){
			if($val['pd'] != "")	
				$keep = false;
		}
		
		if($keep && isset($dc) && $dc == "yes"){
			if($val['dc'] == "No")	
				$keep = false;
		}
		
		if($keep && isset($dc) && $dc == "no"){
			if($val['dc'] == "Yes")	
				$keep = false;
		}
		
		if($keep && isset($quot) && $quot == "yes"){
			if($val['hq'] == "")	
				$keep = false;
		}
		
		if($keep && isset($quot) && $quot == "no"){
			if($val['hq'] != "")	
				$keep = false;
		}

		return $keep;

	},ARRAY_FILTER_USE_BOTH)));

