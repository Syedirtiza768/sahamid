
<!-- $filepath = "images/" . $_FILES["file"]["name"];

<?php
include('../configg.php'); 
// error_reporting(0);


if(isset($_FILES['file']['name'])){

   /* Getting file name */
   $filename = $_FILES['file']['name'];
   $dispatchid = $_POST['dispatchid'];

   $sql = "INSERT INTO reprintdocimage (filenames , dispatchid) VALUES ('$filename', '$dispatchid')";
 
//     // Execute query
	mysqli_query($conn, $sql);

   /* Location */
   $location = "images/$filename";
   $imageFileType = pathinfo($location,PATHINFO_EXTENSION);
   $imageFileType = strtolower($imageFileType);

   /* Valid extensions */
   $valid_extensions = array("jpg","jpeg","png");

   $response = 0;
   /* Check file extension */
   if(in_array(strtolower($imageFileType), $valid_extensions)) {
      /* Upload file */
      if(move_uploaded_file($_FILES['file']['tmp_name'],$location)){
         $response = "reprintDocuments/$location";
      }
   }

   echo $response;
   exit;
}

echo 0;




// $msg = "";
 
// // If upload button is clicked ...
// if (isset($_POST['upload'])) {
//     $dispatchid = $_POST['dispatchid'];
//     $filename = $_FILES["uploadfile"]["name"];
//     $tempname = $_FILES["uploadfile"]["tmp_name"];
//     $folder = "./images/" . $filename;
 
//     // Get all the submitted data from the form
//     $sql = "INSERT INTO reprintdocimage (filenames , dispatchid) VALUES ('$filename', '$dispatchid')";
 
//     // Execute query
//     $ErrMsg =  _('The stock held at each location cannot be retrieved because');
// 	$DbgMsg = _('The SQL that failed was');
// 	DB_query($sql, $db, $ErrMsg, $DbgMsg);

 
//     // Now let's move the uploaded image into the folder: image
//     if (move_uploaded_file($tempname, $folder)) {
//         echo "<h3>  Image uploaded successfully!</h3>";
//     } else {
//         echo "<h3>  Failed to upload image!</h3>";
//     }
// }
// ?>
