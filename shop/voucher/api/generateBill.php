<?php 

	$PathPrefix='../../../';
	include('../../../includes/session.inc');
	include('../../../includes/SQL_CommonFunctions.inc');
	include "../../../qrcode/qrlib.php";
	
/*	if(!userHasPermission($db, 'create_shop_sale')){
		echo json_encode([
				'status' => 'error',
				'message' => 'Permission Denied.'
			]);
		return;
	}*/
    $type=$_POST['type'];
	$amount  	= $_POST['amount'];
    $ref  	= $_POST['ref'];
    $description  	= $_POST['description'];
    $instrumentType  	= $_POST['instrumentType'];
    $instrumentNo  	= $_POST['instrumentNo'];
    $instrumentDate  	= $_POST['instrumentDate'];
    $pid  	= $_POST['pid'];
    $partyName  	= $_POST['partyName'];
    $partyType	= $_POST['partyType'];
    $dba  	= $_POST['dba'];
    $salesman  	= $_POST['salesman'];
    $address1  	= $_POST['address1'];
    $address2  	= $_POST['address2'];
    $address3  	= $_POST['address3'];
    $user_name=$_SESSION['UsersRealName'];
    $updated_at=date("Y-m-d H:i:s");









	$error = false;

	DB_Txn_Begin($db);









	//Create Bill Here
	$voucherId = GetNextTransNo($type,$db);
	if($type==604)
	$voucherNo='RV-'.$voucherId;
    if($type==605)
    $voucherNo='PV-'.$voucherId;
	$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$SQL="SELECT salesmanname FROM salesman WHERE salesmancode='$salesman'";
	$salesman=mysqli_fetch_assoc(mysqli_query($db,$SQL))['salesmanname'];

    $SQL = "INSERT INTO `voucher`(`type`, `transno`, `voucherno`, `amount`, `ref`, `description`, `instrumentType`,
            `instrumentNo`, `instrumentDate`, `pid`, `partyname`,`partytype`, `dba`, `salesman`,
             `address1`, `address2`, `address3`, `user_name`, `updated_at`)
                VALUES ('".$type."','".$voucherId."','".$voucherNo."',
                        '$amount','".$ref."','".$description."','".$instrumentType."',
                        '".$instrumentNo."','".$instrumentDate."','".$pid."','".$partyName."','".$partyType."','".$dba."','".$salesman."',
                        '$address1','$address2','$address3','$user_name','$updated_at')";

if(!mysqli_query($db,$SQL)){

		echo json_encode([
			'status' => 'error',
			'message' => 'Bill Creation Failed'
		]);
		return;

	}
$id=mysqli_insert_id($db);

	$totalValue = 0;



	DB_Txn_Commit($db);

	$data = [];
	$data['module'] = "voucher";
	$data['code'] = $voucherId;

	QRcode::png(json_encode($data), '../../../qrcodes/shopsale/'.$voucherId.'-shopSaleQR.png', 'L', 14, 2);
		
	echo json_encode(['status' => 'success','voucherID' => $id]);

	return;