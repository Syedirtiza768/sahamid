<?php

$AllowAnyone = true;
$PathPrefix='../../';
include('../../includes/session.inc');
include('../../includes/SQL_CommonFunctions.inc');
$fname = 'CommercialInvoice';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

    $result	= $_FILES['CommercialInvoice']['error'];
    $UploadTheFile = 'Yes'; //Assume all is well to start off with
    $filename = "../../".$_SESSION['part_pics_dir'] . '/' . 'CommercialInvoice_'.$_POST['orderno'].date('+his') .urlencode($_SESSION['UsersRealName']).'.pdf';

    //But check for the worst
    if (mb_strtoupper(mb_substr(trim($_FILES[$fname]['name']),mb_strlen($_FILES[$fname]['name'])-3))!='PDF'){
        echo 'Only pdf files are supported - a file extension of .pdf is expected';
        $UploadTheFile ='No';
        return;
    } elseif ( $_FILES[$fname]['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
        echo 'The file size is over the maximum allowed. The maximum size allowed in KB is' . ' ' . $_SESSION['MaxImageSize'];
        $UploadTheFile ='No';
        return;
    } elseif ( $_FILES[$fname]['error'] == 6 ) {  //upload temp directory check
        echo 'No tmp directory set. You must have a tmp directory set in your PHP for upload of files. ';
        $UploadTheFile ='No';
        return;
    } elseif (file_exists($filename)){
        echo 'Attempting to overwrite an existing file';
        $result = unlink($filename);
        if (!$result){
            echo 'The existing file could not be removed';
            $UploadTheFile ='No';
            return;
        }
    }

    if ($UploadTheFile=='Yes'){
        $result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename);
        $message = ($result)? _('Something is wrong with uploading a file') : _('Something is wrong with uploading a file');
        $CommercialInvoicedate = date('Y-m-d');



    }


}
$CommercialInvoiceFilePath = glob("../../".$_SESSION['part_pics_dir'] . '/' .'CommercialInvoice_'.$_POST['orderno'].'*.pdf');
$CommercialInvoice="";
$ind=0;
foreach($CommercialInvoiceFilePath as $CommercialInvoiceFile) {
    $ind++;
    if (strpos($CommercialInvoiceFile, "deleted")!=False)
        $CommercialInvoice.= '<br /><a target = "_blank" class="btn btn-danger col-md-12" style="margin:5px 0" href = "'.$_SERVER['SERVER_ADDR'].'/'.$CommercialInvoiceFile.'">Attachment'.$ind.'</a>';
    else
        $CommercialInvoice.= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$_SERVER['SERVER_ADDR'].'/'.$CommercialInvoiceFile.'">Attachment'.$ind."</a> <input type='button' id='removeFile' data-basepath='" . $CommercialInvoiceFile . "'  data-orderno='" . $_POST['orderno'] . "' class='btn btn-danger removeFile' name='removeFile' value='X'>";;
}
$updatedHtml= "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
        <input type='hidden' name='FormID' value=' ". $_SESSION['FormID'] ." ' />
        <input type='file' id='attachCommercialInvoiceFile".$_POST['orderno']."' data-orderno='".$_POST['orderno']."' class='attachCommercialInvoiceFile' name='CommercialInvoice'>
        <input type='button' id='uploadCommercialInvoiceFile' data-orderno='".$_POST['orderno']."' class='uploadCommercialInvoiceFile' name='uploadCommercialInvoiceFile' value='uploadCommercialInvoice'>
        </form>".$CommercialInvoice;


$invoicedate = date('y-m-d h:i:s');
$sql = "UPDATE dcs	SET invoicedate='" .$invoicedate. "' WHERE orderno='".$_POST['orderno']."'";

mysqli_query($db,$sql);



//echo json_encode(['status' => 'success','voucherID' => $voucherId]);
echo $updatedHtml;
//echo json_encode(['updatedHtml' =>$updatedHtml]);

	return;