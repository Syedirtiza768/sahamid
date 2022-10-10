<?php

	$PathPrefix = "../../../";
	include("../../../includes/session.inc");
	include('../../../includes/SQL_CommonFunctions.inc');
    $type=$_GET['type'];

    if($type==604 && !userHasPermission($db,"list_receipt_voucher")){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    if($type==605 && !userHasPermission($db,"list_payment_voucher")){
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
	$SQL = "SELECT * FROM voucher WHERE type=$type";
	$res = mysqli_query($db, $SQL);
    $data = [];
	
//	$canEdit = userHasPermission($db,"edit_receipt_voucher")&&userHasPermission($db,"edit_payment_voucher");

	while($row = mysqli_fetch_assoc($res)){


		$r = [];
		$r[] = explode("-",$row['voucherno'])[1];
		$r[] = $row['voucherno'];
		$r[] = $row['pid'];
		$r[] = $row['partyname'];
		$r[] = date("d/m/Y",strtotime($row['created_at'])); 
		$r[] = locale_number_format($row['amount'],2);
        $r[] = $row['salesman'];
        $r[] = $row['user_name'];
        $voucherFilePath = glob("../../../".$_SESSION['part_pics_dir'] . '/' .'voucher_'.$row['id'].'*.pdf');
        $vouchers="";
        $ind=0;
        foreach($voucherFilePath as $voucherFile) {
            $ind++;
            $vouchers.= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$RootPath.'/'.$voucherFile.'">Attachment'.$ind.'</a>';
        }
        $r[]= "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
        <input type='hidden' name='FormID' value=' ". $_SESSION['FormID'] ." ' />
        <input type='file' id='attachFile".$row['id']."' data-orderno='".$row['id']."' class='attachFile' name='voucher'>
        <input type='button' id='uploadFile' data-orderno='".$row['id']."' class='uploadFile' name='uploadFile' value='upload'>
        </form>".$vouchers;

        $checked = (($row['booked']==1) ? "checked":"");

        $r[] = (($checked=="")?"
        <input type='checkbox' id='booked' data-orderno='".$row['id']."' class='booked' name='booked' $checked value=1>
        
        ":"Booked");
	//	if($canEdit){
        if ($row['type']==604)
            $type="rv";
        if ($row['type']==605)
            $type="pv";
        $r[] =   "<a class='btn btn-warning' target='_blank' href='editVoucher.php?orderno=".$row['id']."&type=".$type."'>Edit</a>";

	//	}
		
		$r[] = "<a class='btn btn-info' target='_blank' href='voucherPrint.php?orderno=".$row['id']."'>Print</a>";
        $r[] = "<a class='btn btn-info' target='_blank' href='voucherPrint.php?orderno=".$row['id']."&duplicate=1"."'>Duplicate</a>";


		
		$data[] = $r;


	}

	echo json_encode($data);