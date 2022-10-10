<?php

	$PathPrefix = "../../../";
	include("../../../includes/session.inc");
	include('../../../includes/SQL_CommonFunctions.inc');
    $type=$_GET['type'];
    $from=$_POST['from'];
    $to=$_POST['to'];
    $salesperson=$_POST['salesperson'];
    if (!empty($salesperson))
        $salespersons=implode(",",$salesperson);
    if(isset($_POST['from'])) {

    if ($type == 604 && !userHasPermission($db, "list_receipt_voucher")) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    if ($type == 605 && !userHasPermission($db, "list_payment_voucher")) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        return;
    }
    if ($type == 604) {
        $SQL = "SELECT *,voucher.id as voucherid FROM voucher WHERE type=$type AND voucher.created_at BETWEEN '$from' AND '$to'";
        if (!userHasPermission($db, "executive_listing")) {
            $SQL .= ' AND (voucher.salesman ="' . $_SESSION['UsersRealName'] . '"
				OR  voucher.user_name ="' . $_SESSION['UsersRealName'] . '")';
        }
        else{

            if (!empty($salesperson)){
            $SQL.="AND (voucher.salesman IN (
                $salespersons
            )
            OR voucher.user_name IN (
                $salespersons
            ))";}


        }
        $res = mysqli_query($db, $SQL);
        $data = [];
    }
    if ($type == 605) {
        $SQL = "SELECT  *,voucher.id as voucherid FROM voucher  
                WHERE type=$type AND voucher.created_at BETWEEN '$from' AND '$to'";
        if (!userHasPermission($db, "executive_listing")) {
            $SQL .= 'AND (voucher.user_name ="' . $_SESSION['UsersRealName'] . '"
                      OR voucher.pid IN (
                        SELECT permission FROM vendor_permission WHERE
                         vendor_permission.userid ="' . $_SESSION['UserID'] . '"                        
                      ))';


        }
        else {
            if (!empty($salesperson)) {
                $SQL .= "AND (voucher.salesman IN (
                $salespersons
            )
            OR voucher.user_name IN (
                $salespersons
            ))";
            }


        }

        $data = [];
    }

//	$canEdit = userHasPermission($db,"edit_receipt_voucher")&&userHasPermission($db,"edit_payment_voucher");


        $res = mysqli_query($db, $SQL);
    while ($row = mysqli_fetch_assoc($res)) {


        $r = [];
        $r[] = explode("-", $row['voucherno'])[1];
        $r[] = $row['voucherno'];
        $r[] = $row['pid'];
        $r[] = $row['partyname'];
        $r[] = date("d/m/Y", strtotime($row['created_at']));
        $r[] = locale_number_format($row['amount'], 2);
        $r[] = $row['salesman'];
        $r[] = $row['user_name'];
        $voucherFilePath = glob("../../../" . $_SESSION['part_pics_dir'] . '/' . 'voucher_' . $row['voucherid'] . '*.pdf');
        $vouchers = "";
        $ind = 0;
        foreach ($voucherFilePath as $voucherFile) {
            $ind++;
            $vouchers .= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "' . $RootPath . '/' . $voucherFile . '">Attachment' . $ind . '</a>';
        }
        $r[] = "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
        <input type='hidden' name='FormID' value=' " . $_SESSION['FormID'] . " ' />
        <input type='file' id='attachFile" . $row['id'] . "' data-orderno='" . $row['voucherid'] . "' class='attachFile' name='voucher'>
        <input type='button' id='uploadFile' data-orderno='" . $row['voucherid'] . "' class='uploadFile' name='uploadFile' value='upload'>
        </form>" . $vouchers;

        $checked = (($row['booked'] == 1) ? "checked" : "");

        $r[] = (($checked == "") ? "
        <input type='checkbox' id='booked' data-orderno='" . $row['voucherid'] . "' class='booked' name='booked' $checked value=1>
        
        " : "Booked");


        $r[] = "<a class='btn btn-info' target='_blank' href='paymentVoucherPrint.php?orderno=" . $row['voucherid'] . "&supptrans=" . $row['supptransno'] . "'>Print</a>";
        $r[] = "<a class='btn btn-info' target='_blank' href='paymentVoucherPrint.php?orderno=" . $row['voucherid'] . "&supptrans=" . $row['supptransno'] . "&duplicate=1" . "'>Duplicate</a>";


        $data[] = $r;


    }

    echo json_encode($data);
}