<?php 

	$PathPrefix='../';
    $AllowAnyone = true;
	include('../includes/session.inc');
	include('../includes/SQL_CommonFunctions.inc');
    //include('../dc/misc.php');
    $dcno = $_POST['dcno'];
    $salescaseref = $_POST['salescaseref'];
    $dcno = $_POST['dcno'];

    $debtorNo 	  = $_POST['debtorno'];
    $branchCode   = $_POST['branchcode'];


    $RootPath = explode("/dc",$RootPath)[0];
/*if(!(isset($_GET['salescaseref']) && isset($_GET['DebtorNo'])
    && isset($_GET['BranchCode'])
    && isset($_GET['NewOrder']))){

    returnErrorResult($RootPath,$salescaseref, "Missing Parameters");
    exit;

}*/



/*if(isIncorrectSalesCase($salescaseref)){

    returnErrorResult($RootPath,$salescaseref, "Invalid Salescase");
    exit;

}*/

//$db = createDBConnection();

$SQL = "SELECT custbranch.brname,
				custbranch.braddress1,
				custbranch.braddress2,
				custbranch.braddress3,
				custbranch.braddress4,
				custbranch.braddress5,
				custbranch.braddress6,
				custbranch.phoneno,
				custbranch.email,
				custbranch.defaultlocation,
				custbranch.defaultshipvia,
				custbranch.deliverblind,
				custbranch.specialinstructions,
				custbranch.estdeliverydays,
				custbranch.salesman
			FROM custbranch
			WHERE custbranch.branchcode='".$_POST['branchcode']."'
			AND custbranch.debtorno = '".$_POST['debtorno']."'";

$customerInfoResult = mysqli_query($db, $SQL);

/*if(mysqli_num_rows($customerInfoResult) == 0){

    header('Location: '.$RootPath."/salescase.php");
    exit;
    return;

}*/

$customerInfo = mysqli_fetch_assoc($customerInfoResult);


if(!userHasPermission($db, 'can_create_grb')){

		echo json_encode([

				'status' => 'error',
				'message' => 'Permission Denied.'

			]);
		return;
	
	}
	



	$SQL = 'SELECT * from dcs WHERE dcs.orderno = '.$dcno.' AND
            dcs.courierslipdate = "0000-00-00 00:00:00" AND dcs.invoicedate="0000-00-00 00:00:00" 
            AND dcs.grbdate="0000-00-00 00:00:00" AND dcs.orderno AND dcs.invoicegroupid IS NULL';



	$res = mysqli_query($db, $SQL);
	$row = mysqli_fetch_assoc($res);
	if(mysqli_num_rows($res)==0){

    echo json_encode([

        'status' => 'error',
        'message' => 'Already Invoiced',
        //'SQL' => $SQL

    ]);
    return;

}

$newGRBNo = GetNextTransNo(514,$db);
$SQL = "INSERT INTO `grb`(`orderno`, `dcno`, `salescaseref`, `debtorno`, `branchcode`, `orddate`, `shipvia`, `deladd1`, `deladd2`, `deladd3`, `deladd4`, `deladd5`, `deladd6`, `deliverto`, `fromstkloc`, `deliverydate`, `confirmeddate`, `quotedate`, `inprogress`,`quotation`) 
		VALUES (".$newGRBNo.",'".$dcno."','".$salescaseref."','".$debtorNo."','".$branchCode."','".date('Y-m-d')."',1,'".addslashes($customerInfo['braddress1'])."','".addslashes($customerInfo['braddress2'])."','".addslashes($customerInfo['braddress3'])."','".addslashes($customerInfo['braddress4'])."','".addslashes($customerInfo['braddress5'])."','".addslashes($customerInfo['braddress6'])."','".addslashes($customerInfo['brname'])."','".addslashes($customerInfo['defaultlocation'])."','".date('Y-m-d')."','".date('Y-m-d')."','".date('Y-m-d')."',0,1)";

$result = mysqli_query($db, $SQL);


echo json_encode([

			'status' => 'success',
			'grbno' => $newGRBNo

		]);
	return;




