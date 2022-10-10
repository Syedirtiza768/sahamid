<?php

	include("../misc.php");

	session_start();
	$salescaseref = $_POST['salescaseref'];
	$db = createDBConnection();
	$fname = 'enquiryfile';

	if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

		$result	= $_FILES['enquiryfile']['error'];
	 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
		$filename = "../../".$_SESSION['part_pics_dir'] . '/' . 'enquiryfile_'.$salescaseref.urlencode($_SESSION['UsersRealName']).'.pdf';
	
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
	if (	$result  =  move_uploaded_file($_FILES[$fname]['tmp_name'], $filename))
		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
		$enquirydate = date('Y-m-d h:i:s');
	
								
	
	$sqlA = "UPDATE salescase
						SET 
						
							enquirydate='" .$enquirydate. "'
							
					WHERE salescaseref='".$salescaseref."'";
	
	mysqli_query($db,$sqlA);
	}
}

	header('Location: ' . $_SERVER['HTTP_REFERER']);
?>