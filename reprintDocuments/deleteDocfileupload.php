
<!-- $filepath = "images/" . $_FILES["file"]["name"];

<?php
include('../configg.php'); 
// error_reporting(0);


$filename = $_POST['image'];
$dispatchid = $_POST['id'];
if($filename){

   /* Getting file name */

   $sql = "DELETE FROM reprintdocimage WHERE dispatchid = '".$dispatchid."' AND filenames = '".$filename."'";
 
//     // Execute query
	if(mysqli_query($conn, $sql)){
        $location = "images/$filename";
        if (file_exists($location)) 
               {
                 unlink($location);
                  echo "File Successfully Delete."; 
                  exit;
              }
              else
              {
               echo "File does not exists"; 
               exit;
              }
    }
   
}

echo 0;


?>
