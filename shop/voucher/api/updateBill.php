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









	//Update Bill Here
    $orderno = $_GET['orderno'];
/*      `pid`='$pid',
  `partyname`='$partyname',
  `partytype`='$partytype',
  `dba`='$dba',
  `salesman`='$salesman',
  `address1`='$address1',
  `address2`='$address2',
  `address3`='$address3',*/
	$SQL="UPDATE voucher SET 
      `amount`='$amount',
      `ref`='$ref', 
      `description`='$description',
      `instrumentType`='$instrumentType',
      `instrumentNo`='$instrumentNo',
      `instrumentDate`='$instrumentDate',

      `updated_at`='$updated_at'
      WHERE id=$orderno
      
      ";


if(!DB_query($SQL,$db)){

		echo json_encode([
			'status' => 'error',
			'message' => 'Bill Creation Failed'
		]);
		return;

	}

	$totalValue = 0;



	DB_Txn_Commit($db);

	$data = [];
	$data['module'] = "voucher";
	$data['code'] = $voucherId;

	QRcode::png(json_encode($data), '../../../qrcodes/shopsale/'.$voucherId.'-shopSaleQR.png', 'L', 14, 2);
		
	echo json_encode(['status' => 'success','voucherID' => $voucherId]);

	return;