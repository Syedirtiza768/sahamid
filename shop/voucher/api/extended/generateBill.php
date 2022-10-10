<?php 

	$PathPrefix='../../../../';
	include('../../../../includes/session.inc');
	include('../../../../includes/SQL_CommonFunctions.inc');
	include "../../../../qrcode/qrlib.php";


    $type=$_POST['type'];
	$amount  	= $_POST['amount'];
    $ref  	= $_POST['ref'];
    $narrative  	= $_POST['narrative'];
    $description  	= $_POST['description'];
    $instrumentType  	= $_POST['instrumentType'];
    $instrumentNo  	= $_POST['instrumentNo'];
    $instrumentDate  	= $_POST['instrumentDate'];
    $pid  	= $_POST['pid'];
    $bankaccount  	= $_POST['bankaccount'];
    $partyName  	= $_POST['partyName'];
    $partyType	= $_POST['partyType'];
    $dba  	= $_POST['dba'];
    $salesman  	= $_POST['salesman'];
    $address1  	= $_POST['address1'];
    $address2  	= $_POST['address2'];
    $address3  	= $_POST['address3'];
    $user_name=$_SESSION['UsersRealName'];
    $updated_at=date("Y-m-d H:i:s");
    $listNumber=[];
    $listNumber=$_POST['listNumber'];
    $listId=[];
    $listId=$_POST['listId'];
    $listRemaining=[];
    $listRemaining=$_POST['listRemaining'];
    $listToBeAllocated=[];
    $listToBeAllocated=$_POST['listToBeAllocated'];
    $error = false;

	DB_Txn_Begin($db);
    //payment entries
    $TransNo = GetNextTransNo(22, $db);
    $PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
    $SQL="
        INSERT INTO supptrans (transno, type, supplierno, trandate, inputdate,
        suppreference, rate, chequefilepath, chequedepositfilepath, cashfilepath,
        ovamount, transtext, bankaccount) values ('$TransNo', 22, '$pid', '$instrumentDate',
        '$instrumentDate', '$instrumentType', '1', '', '', '', '".-1*$amount."', '$instrumentNo', '$bankaccount' )
    
        ";

    if(!mysqli_query($db,$SQL)){

        echo json_encode([
            'status' => 'error',
            'message' => 'Bill Creation Failed'.$SQL
        ]);
        return;

    }
    $SQL="SELECT id from supptrans WHERE supptrans.transno='$TransNo' AND supptrans.type=22";
    $transId=mysqli_fetch_assoc(mysqli_query($db,$SQL))['id'];

    $SQL="
                INSERT INTO banktrans (transno, type, bankact, ref, exrate, functionalexrate, transdate,
                banktranstype, amount, currcode) 
                VALUES ('$TransNo', '22', '$bankaccount', '$instrumentNo', '1', '1', '$instrumentDate',
                '$instrumentType',  '".-1*$amount."', 'PKR')
        
            ";

    if(!mysqli_query($db,$SQL)){

        echo json_encode([
            'status' => 'error',
            'message' => 'Bill Creation Failed'.$SQL
        ]);
        return;

    }


    $SQL="          UPDATE suppliers
                        SET	lastpaiddate = '$instrumentDate',
                            lastpaid='$amount'
                        WHERE suppliers.supplierid='$pid'";

    if(!mysqli_query($db,$SQL)){

        echo json_encode([
            'status' => 'error',
            'message' => 'Bill Creation Failed'.$SQL
        ]);
        return;

    }
    if(is_array($listNumber))
        $count=count($listNumber);
    else $count=0;
    $sum=0;
    for ($i=0;$i<$count;$i++)
    {
        if ($listToBeAllocated[$i]==0)
            continue;
        $sum=$sum+$listToBeAllocated[$i];
        //check for settled
        $SQL="INSERT INTO suppallocs (datealloc, amt, transid_allocfrom, transid_allocto) VALUES
                ('$instrumentDate', '".$listToBeAllocated[$i]."', '$transId', '".$listId[$i]."')";
        if(!mysqli_query($db,$SQL)){

            echo json_encode([
                'status' => 'error',
                'message' => 'Bill Creation Failed '.$SQL
            ]);
            return;

        }

        $SQL="UPDATE supptrans SET processed='$transId' WHERE id = '".$listId[$i]."'";
        if(!mysqli_query($db,$SQL)){

            echo json_encode([
                'status' => 'error',
                'message' => 'Bill Creation Failed'.$SQL
            ]);
            return;

        }

        $SQL="INSERT INTO gltrans ( type, typeno, trandate, periodno, account, narrative, amount )
                      VALUES ( '601', '".$listNumber[$i]."', '$instrumentDate', '$PeriodNo', '$bankaccount', 'Against Payment ($transId)', '".-1*$listToBeAllocated[$i]."')";
        if(!mysqli_query($db,$SQL)){

            echo json_encode([
                'status' => 'error',
                'message' => 'Bill Creation Failed'.$SQL
            ]);
            return;

        }

        $SQL="UPDATE supptrans SET diffonexch='0', alloc = alloc + '".$listToBeAllocated[$i]."' WHERE id = '".$listId[$i]."'";


        if(!mysqli_query($db,$SQL)){

            echo json_encode([
                'status' => 'error',
                'message' => 'Bill Creation Failed'.$SQL
            ]);
            return;

        }

            $SQL = "UPDATE supptrans SET settled = '1' WHERE id = '" . $listId[$i] . "'
                      AND (ovamount+ovgst-alloc)<0.9";


        if(!mysqli_query($db,$SQL)){

            echo json_encode([
                'status' => 'error',
                'message' => 'Bill Creation Failed'.$SQL
            ]);
            return;

        }

        $SQL="INSERT INTO gltrans ( type, typeno, trandate, periodno, account, narrative, amount )
                      VALUES ( '601', '".$listNumber[$i]."', '$instrumentDate', '$PeriodNo', '2100', 'Against MPI (".$listNumber[$i].") and Payment ($transId)', '".$listToBeAllocated[$i]."' )";


        if(!mysqli_query($db,$SQL)){

            echo json_encode([
                'status' => 'error',
                'message' => 'Bill Creation Failed'.$SQL
            ]);
            return;

        }


    }
    $SQL="UPDATE supptrans SET diffonexch='0', alloc = alloc - '".$sum."' WHERE id = '".$transId."'";
    if(!mysqli_query($db,$SQL)){

        echo json_encode([
            'status' => 'error',
            'message' => 'Bill Creation Failed'.$SQL
        ]);
        return;

    }
    if ($sum==$amount) {
        $SQL = "UPDATE supptrans SET settled = '1' WHERE id = '" . $transId . "'
                          AND (((ovamount+ovgst))-alloc)<0.9";
        if (!mysqli_query($db, $SQL)) {

            echo json_encode([
                'status' => 'error',
                'message' => 'Bill Creation Failed' . $SQL
            ]);
            return;

        }
    }

//voucher record
	//Create Bill Here
	$voucherId = GetNextTransNo($type,$db);
    $PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);

    if($type==604)
	$voucherNo='RV-'.$voucherId;
    if($type==605)
    $voucherNo='PV-'.$voucherId;
	$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']), $db);
	$SQL="SELECT salesmanname FROM salesman WHERE salesmancode='$salesman'";
	$salesman=mysqli_fetch_assoc(mysqli_query($db,$SQL))['salesmanname'];

    $SQL = "INSERT INTO `voucher`(`type`, `transno`,`supptransno`, `voucherno`, `amount`, `ref`, `description`,`narrative`,`bankaccount`, `instrumentType`,
            `instrumentNo`, `instrumentDate`, `pid`, `partyname`,`partytype`, `dba`, `salesman`,
             `address1`, `address2`, `address3`, `user_name`, `updated_at`)
                VALUES ('".$type."','".$voucherId."','".$transId."','".$voucherNo."',
                        '$amount','".$ref."','".$description."','".$narrative."','".$bankaccount."','".$instrumentType."',
                        '".$instrumentNo."','".$instrumentDate."','".$pid."','".$partyName."','".$partyType."','".$dba."','".$salesman."',
                        '$address1','$address2','$address3','$user_name','$updated_at')";
    if(!mysqli_query($db,$SQL)){

        echo json_encode([
            'status' => 'error',
            'message' => 'Bill Creation Failed'.$SQL
        ]);
        return;

    }
    $id=mysqli_insert_id($db);

	$totalValue = 0;
    $data = [];
	$data['module'] = "voucher";
	$data['code'] = $voucherId;

	QRcode::png(json_encode($data), '../../../../qrcodes/shopsale/'.$voucherId.'-shopSaleQR.png', 'L', 14, 2);
DB_Txn_Commit($db);

//Upload Files Code Start

$chequefilepath = '';
$chequedepositfilepath = '';
$cashfilepath = '';
//print_r($_FILES);
$fname = 'chequefile';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

    $result	= $_FILES[$fname]['error'];
    $UploadTheFile = 'Yes'; //Assume all is well to start off with
    $filename = '../../../../companies/uploads/cheque_'.date('Ymdhis')."_".urlencode($_SESSION['UsersRealName']).'.pdf';
    $filelink ='../../../companies/uploads/cheque_'.date('Ymdhis')."_".urlencode($_SESSION['UsersRealName']).'.pdf';

    //But check for the worst
    if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
        prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
        $UploadTheFile ='No';
    } elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
        prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
        $UploadTheFile ='No';
    } elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
        prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
        $UploadTheFile ='No';
    } elseif (file_exists($filename)){
        prnMsg(_('Attempting to overwrite an existing file'),'warn');
        $result = unlink($filename);
        if (!$result){
            prnMsg(_('The existing file could not be removed'),'error');
            $UploadTheFile ='No';
        }
    }

    if ($UploadTheFile=='Yes'){
        $result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename);
        $message = ($result)? _('Something is wrong with uploading a file') : _('Something is wrong with uploading a file');
        $rppdate = date('Y-m-d');
        $chequefilepath = $filelink;
        $_SESSION['chequefilepath']=$chequefilepath;
        $SQL="UPDATE supptrans SET chequefilepath = '$filelink' WHERE id = '".$transId."'";
        mysqli_query($db,$SQL);

    }


}

$fname = 'cdrfile';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

    $result	= $_FILES[$fname]['error'];
    $UploadTheFile = 'Yes'; //Assume all is well to start off with
    $filename = '../../../../companies/uploads/cdr_'.date('Ymdhis')."_".urlencode($_SESSION['UsersRealName']).'.pdf';
    $filelink ='../../../companies/uploads/cdr_'.date('Ymdhis')."_".urlencode($_SESSION['UsersRealName']).'.pdf';

    //But check for the worst
    if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
        prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
        $UploadTheFile ='No';
    } elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
        prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
        $UploadTheFile ='No';
    } elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
        prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
        $UploadTheFile ='No';
    } elseif (file_exists($filename)){
        prnMsg(_('Attempting to overwrite an existing file'),'warn');
        $result = unlink($filename);
        if (!$result){
            prnMsg(_('The existing file could not be removed'),'error');
            $UploadTheFile ='No';
        }
    }

    if ($UploadTheFile=='Yes'){
        $result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename);
        $message = ($result)? _('Something is wrong with uploading a file') : _('Something is wrong with uploading a file');
        $rppdate = date('Y-m-d');
        $chequedepositfilepath = $filelink;
        $_SESSION['chequedepositfilepath']=$chequedepositfilepath;
        $SQL="UPDATE supptrans SET chequedepositfilepath = '$filelink' WHERE id = '".$transId."'";
        mysqli_query($db,$SQL);
    }


}

$fname = 'crvfile';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

    $result	= $_FILES[$fname]['error'];
    $UploadTheFile = 'Yes'; //Assume all is well to start off with
    $filename = '../../../../companies/uploads/crv_'.date('Ymdhis')."_".urlencode($_SESSION['UsersRealName']).'.pdf';
    $filelink ='../../../companies/uploads/crv_'.date('Ymdhis')."_".urlencode($_SESSION['UsersRealName']).'.pdf';

    //But check for the worst
    if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
        prnMsg(_('Only pdf files are supported - a file extension of .pdf is expected'),'warn');
        $UploadTheFile ='No';
    } elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
        prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
        $UploadTheFile ='No';
    } elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
        prnMsg( _('No tmp directory set. You must have a tmp directory set in your PHP for upload of files. '),'warn');
        $UploadTheFile ='No';
    } elseif (file_exists($filename)){
        prnMsg(_('Attempting to overwrite an existing file'),'warn');
        $result = unlink($filename);
        if (!$result){
            prnMsg(_('The existing file could not be removed'),'error');
            $UploadTheFile ='No';
        }
    }

    if ($UploadTheFile=='Yes'){
        $result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename);
        $message = ($result)? _('Something is wrong with uploading a file') : _('Something is wrong with uploading a file');
        $rppdate = date('Y-m-d');
        $cashfilepath = $filelink;
        $_SESSION['cashfilepath']=$cashfilepath;
        $SQL="UPDATE supptrans SET cashfilepath = '$filelink' WHERE id = '".$transId."'";
        mysqli_query($db,$SQL);
    }


}


echo json_encode(['status' => 'success','voucherID' => $id,'supptrans' => $transId]);

	return;
	?>