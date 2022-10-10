<?php 

	$PathPrefix='../../';

	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	if(!userHasPermission($db, 'create_quotation_revision')){
		header("Location: ../../index.php");
		return;
	}

	$orderno = $_GET['orderno'];

	//Sales Order
	$SQL = "SELECT * FROM salesorders WHERE orderno='".$orderno."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) != 1){

		header("Location: ".$_SERVER['HTTP_REFERER']);
		return;

	}

	$so = mysqli_fetch_assoc($result);

	$SQL = "SELECT * FROM salesorders WHERE revision_for='".$orderno."'";
	$result = mysqli_query($db, $SQL);
	$revision = "R".((mysqli_num_rows($result))+1);
	
	$salescaseref = $so['salescaseref'];

	//Check InProgress
	$SQL = "SELECT orderno,buyername,debtorno,branchcode FROM salesordersip WHERE salescaseref='".$salescaseref."'";

	$result = mysqli_query($db, $SQL);

	if(mysqli_num_rows($result) == 1){

		$row = mysqli_fetch_assoc($result);

		header('Location: /sahamid/makequotation.php?orderno='.$row['orderno'].'&salescaseref='.$salescaseref.'&selectedcustomer='.$row['buyername'].'&DebtorNo='.$row['debtorno'].'&BranchCode='.$row['branchcode']);    

		return;
	}

	$SQL = "INSERT INTO `salesordersip`(existing,orddate,eorderno,`salescaseref`,`debtorno`,
	`branchcode`, `customerref`, `buyername`, `comments`, 
	`ordertype`, `shipvia`, `deladd1`, `deladd2`, `deladd3`,
	`deladd4`, `deladd5`, `deladd6`, `contactperson`,`contactphone`, 
	`contactemail`, `deliverto`, `deliverblind`, `freightcost`, 
	`advance`, `delivery`, `commisioning`, `after`, `gst`, 
	`afterdays`, `fromstkloc`, `deliverydate`,`confirmeddate`,
	`printedpackingslip`, `datepackingslipprinted`, `quotation`,
	`quotedate`, `poplaced`, `salesperson`,`GSTadd`,`services`,`WHT`,`umqd`,`validity`,`quickQuotation`,`revision`,`revision_for`,`rate_clause`,`rate_validity`) 
	VALUES (0,'".str_replace("'","\'",$so['orddate'])."','','".str_replace("'","\'",$so['salescaseref'])."','".str_replace("'","\'",$so['debtorno'])."',
	'".str_replace("'","\'",$so['branchcode'])."','".str_replace("'","\'",$so['customerref'])."','".str_replace("'","\'",$so['buyername'])."','".str_replace("'","\'",$so['comments'])."',
	'".str_replace("'","\'",$so['ordertype'])."','".str_replace("'","\'",$so['shipvia'])."','".str_replace("'","\'",$so['deladd1'])."','".str_replace("'","\'",$so['deladd2'])."','".str_replace("'","\'",$so['deladd3'])."',
	'".str_replace("'","\'",$so['deladd4'])."','".str_replace("'","\'",$so['deladd5'])."','".str_replace("'","\'",$so['deladd6'])."','".str_replace("'","\'",$so['contactperson'])."','".str_replace("'","\'",$so['contactphone'])."',
	'".str_replace("'","\'",$so['contactemail'])."','".str_replace("'","\'",$so['deliverto'])."','".str_replace("'","\'",$so['deliverblind'])."','".str_replace("'","\'",$so['freightcost'])."',
	'".str_replace("'","\'",$so['advance'])."','".str_replace("'","\'",$so['delivery'])."','".str_replace("'","\'",$so['commisioning'])."','".str_replace("'","\'",$so['after'])."','".str_replace("'","\'",$so['gst'])."',
	'".str_replace("'","\'",$so['afterdays'])."','".str_replace("'","\'",$so['fromstkloc'])."', '".str_replace("'","\'",$so['deliverydate'])."','".str_replace("'","\'",$so['confirmeddate'])."',
	'".str_replace("'","\'",$so['printedpackingslip'])."','".str_replace("'","\'",$so['datepackingslipprinted'])."','".str_replace("'","\'",$so['quotation'])."',
	'".str_replace("'","\'",$so['quotedate'])."','".str_replace("'","\'",$so['poplaced'])."','".str_replace("'","\'",$so['salesperson'])."','".str_replace("'","\'",$so['GSTadd'])."','".str_replace("'","\'",$so['services'])."',
	'".str_replace("'","\'",$so['WHT'])."','".$so['umqd']."','".$so['validity']."','".$so['quickQuotation']."','".$revision."','".$orderno."','".$so['rate_clause']."','".$so['rate_validity']."')";

	$result =  mysqli_query($db, $SQL);

	$newOrderNo = mysqli_insert_id($db);
    $SQL = "SELECT max(id) as id FROM exchange_rate";
    $res = mysqli_query($db, $SQL);
    $id = mysqli_fetch_assoc($res)['id'];

    $SQL = "SELECT * FROM exchange_rate WHERE id=$id";
    $res = mysqli_query($db, $SQL);
    $rates = mysqli_fetch_assoc($res);

    $rates = json_encode($rates);

    $SQL = "UPDATE salesordersip 
                SET clause_rates = '$rates'
                WHERE orderno = $newOrderNo";
    mysqli_query($db, $SQL);

	//Sales Order Lines
	$SQL = "SELECT `orderno`, `lineno`, `clientrequirements` FROM salesorderlines
			WHERE orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	$linesArray = [];

	while($row = mysqli_fetch_assoc($result)){

		$SQL = "INSERT INTO `salesorderlinesip`(`orderno`, `clientrequirements`)
				VALUES ('".$newOrderNo."','".str_replace("'","\'",$row['clientrequirements'])."')";

		mysqli_query($db, $SQL);

		$linesArray[$row['lineno']] = mysqli_insert_id($db);

	}

	//Sales Order Options
	$SQL = "SELECT  `orderno`, `lineno`, `optionno`, `optiontext`, `stockstatus`,`quantity`,`uom`,`price` 
			FROM salesorderoptions WHERE orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	$optionsArray = [];

	while($row = mysqli_fetch_assoc($result)){

		$SQL = "INSERT INTO `salesorderoptionsip`
				(`orderno`, `lineno`, `optiontext`, `stockstatus`,`quantity`,`uom`,`price`)
				VALUES ('".$newOrderNo."','".$linesArray[$row['lineno']]."','".str_replace("'","\'",$row['optiontext'])."',
				'".str_replace("'","\'",$row['stockstatus'])."','".$row['quantity']."','".str_replace("'","\'",$row['uom'])."','".$row['price']."')";

		mysqli_query($db, $SQL);

		$optionsArray[$row['lineno']][$row['optionno']] = mysqli_insert_id($db);

	}

	//Sales Order Details
	$SQL = "SELECT  `orderlineno`, `orderno`, `lineoptionno`, 
	`optionitemno`, `internalitemno`, `stkcode`, `qtyinvoiced`, `unitprice`,`unitrate`, `quantity`,
	`estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`,
	`poline` FROM salesorderdetails
	where orderno = ".$orderno."";

	$result = mysqli_query($db, $SQL);

	while($sod = mysqli_fetch_assoc($result)){

		$SQL = "INSERT INTO `salesorderdetailsip` (`orderlineno`, `orderno`, `lineoptionno`, 
		`optionitemno`, `internalitemno`, `stkcode`, `qtyinvoiced`, `unitprice`,`unitrate`, `quantity`,
		`estimate`, `discountpercent`, `actualdispatchdate`, `completed`, `narrative`, `itemdue`,
		`poline`) VALUES ('".$linesArray[$sod['orderlineno']]."','".$newOrderNo."',
		'".$optionsArray[$sod['orderlineno']][$sod['lineoptionno']]."','".$sod['optionitemno']."',
		'".$sod['internalitemno']."','".$sod['stkcode']."','".$sod['qtyinvoiced']."',
		'".$sod['unitprice']."','".$sod['unitrate']."','".$sod['quantity']."','".$sod['estimate']."',
		'".$sod['discountpercent']."','".$sod['actualdispatchdate']."','".$sod['completed']."',
		'".$sod['narrative']."','".$sod['itemdue']."','".$sod['poline']."')";

		mysqli_query($db, $SQL);

	}
$SQL="UPDATE salesorderdetailsip
            INNER JOIN stockmaster ON salesorderdetailsip.stkcode=stockmaster.stockid
            SET salesorderdetailsip.lastcostupdate=stockmaster.lastcostupdate,
            salesorderdetailsip.lastupdatedby=stockmaster.lastupdatedby
            WHERE salesorderdetailsip.orderno=$newOrderNo
            ";

    mysqli_query($db, $SQL);

	header('Location: /sahamid/makequotation.php?orderno='.$newOrderNo.'&salescaseref='.$salescaseref.'&selectedcustomer='.$so['buyername'].'&DebtorNo='.$so['debtorno'].'&BranchCode='.$so['branchcode']);  
