<?php

$AllowAnyone = true;
$PathPrefix='../../';
include('../../includes/session.inc');
include('../../includes/SQL_CommonFunctions.inc');
$fname = 'GRB';
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

    $result	= $_FILES['GRB']['error'];
    $UploadTheFile = 'Yes'; //Assume all is well to start off with
    $filename = "../../".$_SESSION['part_pics_dir'] . '/' . 'GRB_'.$_POST['orderno'].date('+his') .urlencode($_SESSION['UsersRealName']).'.pdf';

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
        $GRBdate = date('Y-m-d');



    }


}
$GRBFilePath = glob("../../".$_SESSION['part_pics_dir'] . '/' .'GRB_'.$_POST['orderno'].'*.pdf');
$GRB="";
$ind=0;
foreach($GRBFilePath as $GRBFile) {
    $ind++;
    if (strpos($GRBFile, "deleted")!=False)
        $GRB.= '<br /><a target = "_blank" class="btn btn-danger col-md-12" style="margin:5px 0" href = "'.$_SERVER['SERVER_ADDR'].'/'.$GRBFile.'">Attachment'.$ind.'</a>';
    else
        $GRB.= '<br /><a target = "_blank" class="btn btn-primary col-md-12" style="margin:5px 0" href = "'.$_SERVER['SERVER_ADDR'].'/'.$GRBFile.'">Attachment'.$ind."</a> <input type='button' id='removeFile' data-basepath='" . $GRBFile . "' data-orderno='" . $_POST['orderno'] . "' class='btn btn-danger removeFile' name='removeFile' value='X'>";;
}
$updatedHtml= "<form id='attachmentForm' action='' enctype='multipart/form-data' method='post'>
        <input type='hidden' name='FormID' value=' ". $_SESSION['FormID'] ." ' />
        <input type='file' id='attachGRBFile".$_POST['orderno']."' data-orderno='".$_POST['orderno']."' class='attachGRBFile' name='GRB'>
        <input type='button' id='uploadGRBFile' data-orderno='".$_POST['orderno']."' class='uploadGRBFile' name='uploadGRBFile' value='uploadGRB'>
        </form>".$GRB;

$grbdate = date('y-m-d h:i:s');
$sql = "UPDATE dcs	SET grbdate='" .$grbdate. "' WHERE orderno='".$_POST['orderno']."'";

mysqli_query($db,$sql);





//echo json_encode(['status' => 'success','voucherID' => $voucherId]);
echo $updatedHtml;
//echo json_encode(['updatedHtml' =>$updatedHtml]);

	return;