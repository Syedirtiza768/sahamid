<?php

	include("../misc.php");
	session_start();
	$db = createDBConnection();
	$salescaseref = $_POST['salescaseref'];

	$fname = 'pofile';
	$poname = $_POST['poname'];

	if (isset($_FILES[$fname]) AND $_FILES[$fname]['name'] !='') {

		$result	= $_FILES['pofile']['error'];
	 	$UploadTheFile = 'Yes'; //Assume all is well to start off with
		$filename = "../../".$_SESSION['part_pics_dir'] . '/' . 'pofile_'.$salescaseref.date('+his').$salescaseref.urlencode($_SESSION['UsersRealName']).'.pdf';
		
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
			$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
			$podate = date('Y-m-d H:i:s');
			
			$sqlC = "UPDATE salescase SET podate='" .$podate. "'
					WHERE salescaseref='".$salescaseref."'";
			
			$sqlpomaxcount = 'select count(*) as countmax from salescasepo where salescaseref = "'.$salescaseref.'"';
			$resultmaxcount = mysqli_query($db, $sqlpomaxcount);
			$rowresultmaxcount=mysqli_fetch_assoc($resultmaxcount);
			$max = $rowresultmaxcount['countmax']+1;
			$sqlpo = 'INSERT INTO salescasepo(salescaseref,pocount,pono) 
						values ("'.$salescaseref.'",'.$max .',"'.$poname.'")';

			mysqli_query($db,$sqlpo);
			mysqli_query($db,$sqlC);
		
		}
		
	}

	header('Location: ' . $_SERVER['HTTP_REFERER']."#rppbutton");

?>