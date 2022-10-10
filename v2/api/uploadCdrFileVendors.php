<?php

session_start();
$fname = 'cdr';
//need to update the database  accordingly and replicate it to cdr
$db=mysqli_connect('localhost','irtiza','netetech321','sahamid');
if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

    $result	= $_FILES['cdr']['error'];
    $UploadTheFile = 'Yes'; //Assume all is well to start off with
    $filename = "../../companies/uploads/cdr_".date('Ymdhis')."_".urlencode($_SESSION['UsersRealName']).'.pdf';
    $filelink = "../companies/uploads/cdr_".date('Ymdhis')."_".urlencode($_SESSION['UsersRealName']).'.pdf';
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
        $voucherdate = date('Y-m-d');



    }


}
$files="";
$ind=0;
$ind++;
$files.= '<a target = "_blank" class="btn btn-info" href = "'.$filelink.'">Download</a>';

$updatedHtml= $files;

$SQL="UPDATE supptrans SET chequedepositfilepath = '$filelink' WHERE id = '".$_POST['orderno']."'";
mysqli_query($db,$SQL);


//echo json_encode(['status' => 'success','voucherID' => $voucherId]);
echo $updatedHtml;
//echo json_encode(['updatedHtml' =>$updatedHtml]);

return;